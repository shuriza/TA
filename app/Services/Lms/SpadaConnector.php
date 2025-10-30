<?php

namespace App\Services\Lms;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SPADA Polinema Connector
 * 
 * LMS SPADA Polinema menggunakan Moodle dengan authentication melalui SIAKAD.
 * Flow:
 * 1. Login ke SIAKAD (https://siakad.polinema.ac.id/beranda)
 * 2. Redirect ke SPADA dengan session cookie
 * 3. Akses SPADA API/scraping (https://slc.polinema.ac.id/spada/)
 */
class SpadaConnector implements LmsConnectorInterface
{
    private Client $client;
    private string $spadaUrl;
    private string $siakadUrl;
    private ?CookieJar $cookieJar = null;

    public function __construct()
    {
        $this->spadaUrl = config('services.spada.url');
        $this->siakadUrl = config('services.spada.siakad_url');
        
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // Set true in production with proper SSL
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);
        
        $this->cookieJar = new CookieJar();
    }

    /**
     * Authenticate ke SIAKAD kemudian redirect ke SPADA
     */
    public function authenticate(string $username, string $password): bool
    {
        try {
            // Step 1: Login ke SIAKAD
            $response = $this->client->post($this->siakadUrl . '/login', [
                'form_params' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'cookies' => $this->cookieJar,
                'allow_redirects' => true,
            ]);

            // Step 2: Verify login success (check for specific element or redirect)
            $body = (string) $response->getBody();
            
            if (str_contains($body, 'Dashboard') || str_contains($body, 'beranda')) {
                // Login success, save session
                $this->saveSession();
                
                // Step 3: Access SPADA with SIAKAD session
                $this->accessSpada();
                
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SPADA Authentication Failed', [
                'error' => $e->getMessage(),
                'username' => $username,
            ]);
            
            return false;
        }
    }

    /**
     * Akses SPADA menggunakan session dari SIAKAD
     */
    private function accessSpada(): bool
    {
        try {
            $response = $this->client->get($this->spadaUrl, [
                'cookies' => $this->cookieJar,
                'allow_redirects' => true,
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('Failed to access SPADA', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get list of courses dari SPADA
     */
    public function getCourses(): array
    {
        try {
            // SPADA biasanya Moodle-based, cek endpoint API
            // Jika tidak ada API, gunakan web scraping
            
            // Try Moodle API endpoint
            $response = $this->client->get($this->spadaUrl . '/webservice/rest/server.php', [
                'query' => [
                    'wstoken' => config('services.spada.csrf_token'),
                    'wsfunction' => 'core_enrol_get_users_courses',
                    'moodlewsrestformat' => 'json',
                    'userid' => 0, // Get from authenticated user
                ],
                'cookies' => $this->cookieJar,
            ]);

            $courses = json_decode($response->getBody(), true);
            
            return $this->normalizeCourses($courses);
        } catch (\Exception $e) {
            Log::error('Failed to fetch SPADA courses', ['error' => $e->getMessage()]);
            
            // Fallback: Web scraping
            return $this->scrapeCourses();
        }
    }

    /**
     * Get assignments dari course tertentu
     */
    public function getAssignments(string $courseId): array
    {
        try {
            // Try Moodle API
            $response = $this->client->get($this->spadaUrl . '/webservice/rest/server.php', [
                'query' => [
                    'wstoken' => config('services.spada.csrf_token'),
                    'wsfunction' => 'mod_assign_get_assignments',
                    'moodlewsrestformat' => 'json',
                    'courseids[0]' => $courseId,
                ],
                'cookies' => $this->cookieJar,
            ]);

            $assignments = json_decode($response->getBody(), true);
            
            return $this->normalizeAssignments($assignments);
        } catch (\Exception $e) {
            Log::error('Failed to fetch SPADA assignments', [
                'error' => $e->getMessage(),
                'course_id' => $courseId,
            ]);
            
            // Fallback: Web scraping
            return $this->scrapeAssignments($courseId);
        }
    }

    /**
     * Scrape courses dari halaman SPADA (fallback jika API tidak tersedia)
     */
    private function scrapeCourses(): array
    {
        try {
            $response = $this->client->get($this->spadaUrl . '/my/', [
                'cookies' => $this->cookieJar,
            ]);

            $html = (string) $response->getBody();
            
            // Parse HTML untuk extract courses
            // Gunakan DOMDocument atau simple_html_dom
            // Contoh sederhana:
            preg_match_all('/<div class="course-title">(.+?)<\/div>/', $html, $matches);
            
            $courses = [];
            foreach ($matches[1] ?? [] as $index => $title) {
                $courses[] = [
                    'id' => 'course_' . $index,
                    'name' => trim(strip_tags($title)),
                    'shortname' => '',
                ];
            }
            
            return $courses;
        } catch (\Exception $e) {
            Log::error('Failed to scrape SPADA courses', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Scrape assignments (fallback)
     */
    private function scrapeAssignments(string $courseId): array
    {
        // Implementation for web scraping assignments
        // Similar to scrapeCourses()
        return [];
    }

    /**
     * Normalize courses data ke format standar
     */
    private function normalizeCourses(array $courses): array
    {
        return collect($courses)->map(function ($course) {
            return [
                'external_id' => $course['id'] ?? null,
                'code' => $course['idnumber'] ?? $course['shortname'] ?? '',
                'name' => $course['fullname'] ?? $course['displayname'] ?? '',
                'semester' => $this->extractSemester($course['fullname'] ?? ''),
                'description' => $course['summary'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Normalize assignments data
     */
    private function normalizeAssignments(array $assignments): array
    {
        $normalized = [];
        
        foreach ($assignments['courses'] ?? [] as $course) {
            foreach ($course['assignments'] ?? [] as $assignment) {
                $normalized[] = [
                    'external_id' => $assignment['id'],
                    'course_external_id' => $course['id'],
                    'title' => $assignment['name'],
                    'description' => strip_tags($assignment['intro'] ?? ''),
                    'due_at' => isset($assignment['duedate']) && $assignment['duedate'] > 0
                        ? \Carbon\Carbon::createFromTimestamp($assignment['duedate'])
                        : null,
                    'allow_late_submission' => !($assignment['cutoffdate'] ?? false),
                    'lms_url' => $this->spadaUrl . '/mod/assign/view.php?id=' . $assignment['cmid'],
                ];
            }
        }
        
        return $normalized;
    }

    /**
     * Extract semester dari nama course
     * Contoh: "Pemrograman Web - Ganjil 2024/2025"
     */
    private function extractSemester(string $courseName): string
    {
        if (preg_match('/(Ganjil|Genap)\s*(\d{4}\/\d{4})/', $courseName, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        }
        
        return 'Unknown';
    }

    /**
     * Save session cookie to cache
     */
    private function saveSession(): void
    {
        $cookies = $this->cookieJar->toArray();
        Cache::put('spada_session', $cookies, now()->addHours(6));
    }

    /**
     * Load session from cache
     */
    public function loadSession(): bool
    {
        $cookies = Cache::get('spada_session');
        
        if ($cookies) {
            $this->cookieJar = CookieJar::fromArray($cookies, parse_url($this->spadaUrl, PHP_URL_HOST));
            return true;
        }
        
        return false;
    }

    /**
     * Check if session is still valid
     */
    public function isAuthenticated(): bool
    {
        if (!$this->loadSession()) {
            return false;
        }

        try {
            $response = $this->client->get($this->spadaUrl . '/my/', [
                'cookies' => $this->cookieJar,
                'allow_redirects' => false,
            ]);

            // If redirected to login, session expired
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get materials dari course
     */
    public function getMaterials(string $courseId): array
    {
        try {
            $response = $this->client->get($this->spadaUrl . '/webservice/rest/server.php', [
                'query' => [
                    'wstoken' => config('services.spada.csrf_token'),
                    'wsfunction' => 'core_course_get_contents',
                    'moodlewsrestformat' => 'json',
                    'courseid' => $courseId,
                ],
                'cookies' => $this->cookieJar,
            ]);

            $contents = json_decode($response->getBody(), true);
            
            return $this->normalizeMaterials($contents);
        } catch (\Exception $e) {
            Log::error('Failed to fetch SPADA materials', [
                'error' => $e->getMessage(),
                'course_id' => $courseId,
            ]);
            
            return [];
        }
    }

    /**
     * Normalize materials data
     */
    private function normalizeMaterials(array $contents): array
    {
        $materials = [];
        
        foreach ($contents as $section) {
            foreach ($section['modules'] ?? [] as $module) {
                if (in_array($module['modname'], ['resource', 'url', 'page', 'folder'])) {
                    $materials[] = [
                        'external_id' => $module['id'],
                        'title' => $module['name'],
                        'type' => $module['modname'],
                        'url' => $module['url'] ?? null,
                        'description' => strip_tags($module['description'] ?? ''),
                    ];
                }
            }
        }
        
        return $materials;
    }
}
