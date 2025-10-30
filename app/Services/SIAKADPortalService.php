<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

/**
 * SIAKAD Portal Integration via Web Scraping
 * 
 * This service handles authentication and data extraction from SIAKAD web portal
 * when API is not available.
 */
class SIAKADPortalService
{
    protected $baseUrl;
    protected $loginUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.siakad.url', 'https://siakad.polinema.ac.id');
        $this->loginUrl = $this->baseUrl . '/login';
        $this->timeout = config('services.siakad.timeout', 30);
    }

    /**
     * Login to SIAKAD portal and get session
     * 
     * @param string $username NIM or NIP
     * @param string $password Password
     * @return array|null Returns session data or null on failure
     */
    public function login(string $username, string $password): ?array
    {
        try {
            // Step 1: Get login page to extract CSRF token
            $loginPageResponse = Http::timeout($this->timeout)
                ->withOptions(['verify' => false]) // Disable SSL verification for local testing
                ->get($this->loginUrl);

            if (!$loginPageResponse->successful()) {
                Log::warning('Failed to load SIAKAD login page', [
                    'status' => $loginPageResponse->status(),
                ]);
                return null;
            }

            // Parse HTML to get CSRF token and form action
            $crawler = new Crawler($loginPageResponse->body());
            
            $csrfToken = null;
            $formAction = $this->loginUrl;

            // Try to find CSRF token (common field names)
            try {
                $csrfInput = $crawler->filter('input[name="_token"], input[name="csrf_token"], input[name="authenticity_token"]')->first();
                if ($csrfInput->count() > 0) {
                    $csrfToken = $csrfInput->attr('value');
                }
            } catch (\Exception $e) {
                Log::info('No CSRF token found in login form');
            }

            // Try to get form action URL
            try {
                $form = $crawler->filter('form')->first();
                if ($form->count() > 0) {
                    $action = $form->attr('action');
                    if ($action) {
                        $formAction = $this->resolveUrl($action);
                    }
                }
            } catch (\Exception $e) {
                Log::info('Using default form action');
            }

            // Step 2: Submit login form
            $loginData = [
                'username' => $username,
                'password' => $password,
            ];

            if ($csrfToken) {
                $loginData['_token'] = $csrfToken;
            }

            // Detect field names from actual form
            $usernameField = $this->detectFieldName($crawler, ['username', 'email', 'nim', 'nip', 'user']);
            $passwordField = $this->detectFieldName($crawler, ['password', 'pass', 'pwd']);

            if ($usernameField && $passwordField) {
                $loginData = [
                    $usernameField => $username,
                    $passwordField => $password,
                ];
                if ($csrfToken) {
                    $loginData['_token'] = $csrfToken;
                }
            }

            $loginResponse = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->asForm()
                ->post($formAction, $loginData);

            // Step 3: Check if login successful
            $cookies = $loginResponse->cookies();
            $body = $loginResponse->body();

            // Success indicators
            $isSuccess = $this->checkLoginSuccess($body, $loginResponse->status());

            if (!$isSuccess) {
                Log::warning('SIAKAD login failed', [
                    'username' => $username,
                    'status' => $loginResponse->status(),
                ]);
                return null;
            }

            // Step 4: Extract user data from dashboard/profile page
            $userData = $this->extractUserData($body, $username, $cookies);

            if ($userData) {
                // Cache session for reuse
                $sessionKey = 'siakad_session_' . md5($username);
                Cache::put($sessionKey, [
                    'cookies' => $cookies->toArray(),
                    'user_data' => $userData,
                ], now()->addHours(2));

                return $userData;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('SIAKAD portal login error: ' . $e->getMessage(), [
                'username' => $username,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Extract user data from SIAKAD dashboard HTML
     */
    protected function extractUserData(string $html, string $username, $cookies): ?array
    {
        try {
            $crawler = new Crawler($html);

            // Try to extract common data patterns
            $data = [
                'nim' => null,
                'nip' => null,
                'nama' => null,
                'email' => null,
                'prodi' => null,
                'role' => 'mahasiswa', // default
            ];

            // Pattern 1: Find profile info section
            try {
                // Look for profile/user info container
                $profileText = $crawler->filter('.profile, .user-info, .student-info, .lecturer-info')->text();
                
                // Extract NIM/NIP (common patterns: 12-15 digits)
                if (preg_match('/(?:NIM|nim)[\s:]*(\d{10,15})/', $profileText, $matches)) {
                    $data['nim'] = $matches[1];
                    $data['role'] = 'mahasiswa';
                } elseif (preg_match('/(?:NIP|nip)[\s:]*(\d{10,15})/', $profileText, $matches)) {
                    $data['nip'] = $matches[1];
                    $data['role'] = 'dosen';
                }

                // Extract name (common patterns)
                if (preg_match('/(?:Nama|nama|Name)[\s:]*([A-Za-z\s\.]+)/', $profileText, $matches)) {
                    $data['nama'] = trim($matches[1]);
                }

                // Extract prodi/program studi
                if (preg_match('/(?:Prodi|Program Studi|prodi)[\s:]*([A-Za-z0-9\s\-]+)/', $profileText, $matches)) {
                    $data['prodi'] = trim($matches[1]);
                }

            } catch (\Exception $e) {
                Log::info('Profile extraction method 1 failed');
            }

            // Pattern 2: Find by meta tags or header
            try {
                $title = $crawler->filter('title')->text();
                if (stripos($title, 'mahasiswa') !== false) {
                    $data['role'] = 'mahasiswa';
                } elseif (stripos($title, 'dosen') !== false) {
                    $data['role'] = 'dosen';
                }
            } catch (\Exception $e) {
                // Ignore
            }

            // Pattern 3: Fallback - use username
            if (!$data['nim'] && !$data['nip']) {
                // Detect from username format
                if (preg_match('/^\d{10,15}$/', $username)) {
                    if (strlen($username) <= 10) {
                        $data['nim'] = $username;
                    } else {
                        $data['nip'] = $username;
                        $data['role'] = 'dosen';
                    }
                }
            }

            // Generate email if not found
            if (!$data['email']) {
                $identifier = $data['nim'] ?? $data['nip'] ?? $username;
                $domain = $data['role'] === 'mahasiswa' ? 'student.polinema.ac.id' : 'polinema.ac.id';
                $data['email'] = strtolower($identifier) . '@' . $domain;
            }

            // Use name from extraction or generate from username
            if (!$data['nama']) {
                $data['nama'] = 'User ' . ($data['nim'] ?? $data['nip'] ?? $username);
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Failed to extract user data from SIAKAD HTML', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if login was successful
     */
    protected function checkLoginSuccess(string $body, int $statusCode): bool
    {
        // Success indicators
        $successIndicators = [
            'dashboard',
            'beranda',
            'logout',
            'keluar',
            'profil',
            'profile',
        ];

        // Failure indicators
        $failureIndicators = [
            'login gagal',
            'invalid credentials',
            'username atau password salah',
            'incorrect username',
            'login failed',
        ];

        $bodyLower = strtolower($body);

        // Check for failure first
        foreach ($failureIndicators as $indicator) {
            if (stripos($bodyLower, $indicator) !== false) {
                return false;
            }
        }

        // Check for success
        foreach ($successIndicators as $indicator) {
            if (stripos($bodyLower, $indicator) !== false) {
                return true;
            }
        }

        // If redirected (3xx), consider success
        if ($statusCode >= 300 && $statusCode < 400) {
            return true;
        }

        return false;
    }

    /**
     * Detect form field name from crawler
     */
    protected function detectFieldName(Crawler $crawler, array $possibleNames): ?string
    {
        foreach ($possibleNames as $name) {
            try {
                $field = $crawler->filter("input[name=\"{$name}\"]")->first();
                if ($field->count() > 0) {
                    return $name;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * Resolve relative URL to absolute
     */
    protected function resolveUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        
        if (str_starts_with($url, '/')) {
            return rtrim($this->baseUrl, '/') . $url;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Get courses for authenticated user
     */
    public function getCourses(string $username): ?array
    {
        $sessionKey = 'siakad_session_' . md5($username);
        $session = Cache::get($sessionKey);

        if (!$session) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->withCookies($session['cookies'], parse_url($this->baseUrl, PHP_URL_HOST))
                ->get($this->baseUrl . '/courses'); // Adjust URL as needed

            if (!$response->successful()) {
                return null;
            }

            $crawler = new Crawler($response->body());
            
            // Extract courses from HTML
            $courses = [];
            // Implementation depends on actual SIAKAD HTML structure
            
            return $courses;

        } catch (\Exception $e) {
            Log::error('Failed to get courses from SIAKAD', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if SIAKAD portal is accessible
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => false])
                ->get($this->baseUrl);
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
