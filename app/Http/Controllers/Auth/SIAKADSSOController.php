<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SIAKADAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

/**
 * SIAKAD SSO Callback Handler
 * 
 * Handles SSO authentication from SIAKAD Portal
 * Flow: SIAKAD → LMS (with token/session)
 */
class SIAKADSSOController extends Controller
{
    protected $siakadAuth;

    public function __construct(SIAKADAuthService $siakadAuth)
    {
        $this->siakadAuth = $siakadAuth;
    }

    /**
     * Handle SSO callback from SIAKAD
     * 
     * URL: /auth/siakad/callback?token=xxx&timestamp=xxx&signature=xxx
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            // Method 1: Token-based SSO
            if ($request->has('token')) {
                return $this->handleTokenAuth($request);
            }

            // Method 2: Session-based SSO (shared session)
            if ($request->has('siakad_session')) {
                return $this->handleSessionAuth($request);
            }

            // Method 3: Signed URL with user data
            if ($request->has('data') && $request->has('signature')) {
                return $this->handleSignedAuth($request);
            }

            // No valid SSO parameters
            Log::warning('SSO callback without valid parameters', [
                'params' => $request->all(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Link SSO tidak valid. Silakan login manual.');

        } catch (\Exception $e) {
            Log::error('SSO callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan saat SSO. Silakan coba lagi.');
        }
    }

    /**
     * Handle token-based authentication
     * 
     * SIAKAD generates JWT/random token, sends to LMS
     * LMS validates token with SIAKAD API
     */
    protected function handleTokenAuth(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        $timestamp = $request->query('timestamp', time());

        // Check token expiry (valid for 5 minutes)
        if (time() - $timestamp > 300) {
            return redirect()->route('login')
                ->with('error', 'Link SSO sudah kadaluarsa. Silakan login ulang dari SIAKAD.');
        }

        // Validate token with SIAKAD
        $userData = $this->validateSSOToken($token);

        if (!$userData) {
            return redirect()->route('login')
                ->with('error', 'Token SSO tidak valid.');
        }

        // Create/update user and login
        $user = $this->siakadAuth->getOrCreateUser($userData);
        Auth::login($user, true); // Remember me
        $request->session()->regenerate();

        Log::info('SSO login success (token-based)', [
            'user_id' => $user->id,
            'email' => $user->email,
            'method' => 'token',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil melalui SIAKAD SSO!');
    }

    /**
     * Handle session-based authentication
     * 
     * Shared session/cookie between SIAKAD and LMS
     */
    protected function handleSessionAuth(Request $request): RedirectResponse
    {
        $sessionId = $request->query('siakad_session');

        // Validate session with SIAKAD
        $userData = $this->validateSSOSession($sessionId);

        if (!$userData) {
            return redirect()->route('login')
                ->with('error', 'Session SIAKAD tidak valid.');
        }

        $user = $this->siakadAuth->getOrCreateUser($userData);
        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('SSO login success (session-based)', [
            'user_id' => $user->id,
            'email' => $user->email,
            'method' => 'session',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil melalui SIAKAD SSO!');
    }

    /**
     * Handle signed data authentication
     * 
     * SIAKAD sends encrypted user data with signature
     * LMS verifies signature and decrypts data
     */
    protected function handleSignedAuth(Request $request): RedirectResponse
    {
        $encryptedData = $request->query('data');
        $signature = $request->query('signature');
        $timestamp = $request->query('timestamp', time());

        // Check expiry
        if (time() - $timestamp > 300) {
            return redirect()->route('login')
                ->with('error', 'Link SSO sudah kadaluarsa.');
        }

        // Verify signature
        $expectedSignature = hash_hmac(
            'sha256',
            $encryptedData . $timestamp,
            config('services.siakad.shared_secret')
        );

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid SSO signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);

            return redirect()->route('login')
                ->with('error', 'Signature SSO tidak valid.');
        }

        // Decrypt and parse user data
        try {
            $userData = json_decode(base64_decode($encryptedData), true);
            
            if (!$userData || !isset($userData['email'])) {
                throw new \Exception('Invalid user data structure');
            }

            $user = $this->siakadAuth->getOrCreateUser($userData);
            Auth::login($user, true);
            $request->session()->regenerate();

            Log::info('SSO login success (signed-data)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'method' => 'signed',
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Login berhasil melalui SIAKAD SSO!');

        } catch (\Exception $e) {
            Log::error('Failed to decrypt SSO data: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', 'Data SSO tidak valid.');
        }
    }

    /**
     * Validate SSO token with SIAKAD API
     */
    protected function validateSSOToken(string $token): ?array
    {
        try {
            // Check cache first (avoid duplicate API calls)
            $cacheKey = 'sso_token_' . $token;
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData) {
                Cache::forget($cacheKey); // One-time use token
                return $cachedData;
            }

            // Validate with SIAKAD API
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->post(config('services.siakad.api_url') . '/sso/validate', [
                    'token' => $token,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            if (!($data['success'] ?? false)) {
                return null;
            }

            return $data['user'] ?? null;

        } catch (\Exception $e) {
            Log::error('SSO token validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate SSO session with SIAKAD
     */
    protected function validateSSOSession(string $sessionId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get(config('services.siakad.api_url') . '/sso/session/' . $sessionId);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            return $data['user'] ?? null;

        } catch (\Exception $e) {
            Log::error('SSO session validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate SSO link to SIAKAD
     * (For testing purposes or reverse flow)
     */
    public function redirect(): RedirectResponse
    {
        $returnUrl = route('siakad.sso.callback');
        $siakadSSOUrl = config('services.siakad.url') . '/sso/login';

        $ssoUrl = $siakadSSOUrl . '?' . http_build_query([
            'return_url' => $returnUrl,
            'app' => 'lms',
            'timestamp' => time(),
        ]);

        return redirect($ssoUrl);
    }

    /**
     * Generate one-time SSO token for testing
     * (Simulates SIAKAD generating token for this user)
     */
    public function generateTestToken(Request $request)
    {
        if (!app()->environment('local')) {
            abort(403, 'Only available in local environment');
        }

        $userData = [
            'nim' => '2341760001',
            'nama' => 'Test Student',
            'email' => '2341760001@student.polinema.ac.id',
            'prodi' => 'D4 Teknik Informatika',
            'role' => 'mahasiswa',
        ];

        // Generate token
        $token = Str::random(64);
        $timestamp = time();
        
        // Cache token data (5 minutes)
        Cache::put('sso_token_' . $token, $userData, now()->addMinutes(5));

        // Generate SSO URL
        $ssoUrl = route('siakad.sso.callback') . '?' . http_build_query([
            'token' => $token,
            'timestamp' => $timestamp,
        ]);

        return response()->json([
            'sso_url' => $ssoUrl,
            'token' => $token,
            'expires_in' => 300,
            'user_data' => $userData,
            'instructions' => 'Copy URL dan paste di browser untuk test SSO',
        ]);
    }
}
