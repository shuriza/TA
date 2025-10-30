<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SIAKADAuthService;
use App\Services\SIAKADPortalService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SIAKADAuthController extends Controller
{
    protected $siakadAuth;
    protected $siakadPortal;

    public function __construct(SIAKADAuthService $siakadAuth, SIAKADPortalService $siakadPortal)
    {
        $this->siakadAuth = $siakadAuth;
        $this->siakadPortal = $siakadPortal;
    }

    /**
     * Handle SIAKAD SSO login
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = $request->username;
        $password = $request->password;

        // Try SIAKAD Portal Login (Web Scraping)
        if (config('services.siakad.use_portal', true)) {
            $siakadData = $this->siakadPortal->login($username, $password);

            if ($siakadData) {
                // Get or create user from SIAKAD data
                $user = $this->siakadAuth->getOrCreateUser($siakadData);

                // Login the user
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                Log::info('User logged in via SIAKAD Portal', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'method' => 'portal_scraping',
                ]);

                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('success', 'Login berhasil! Selamat datang di LMS Cerdas.');
            }
        }

        // Fallback: Try SIAKAD API (if available)
        if ($this->siakadAuth->isAvailable()) {
            $siakadData = $this->siakadAuth->authenticate($username, $password);

            if ($siakadData) {
                $user = $this->siakadAuth->getOrCreateUser($siakadData);
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                Log::info('User logged in via SIAKAD API', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'method' => 'api',
                ]);

                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('success', 'Login berhasil! Selamat datang di LMS Cerdas.');
            }
        }

        // Fallback to local authentication
        $user = $this->siakadAuth->fallbackAuthenticate($username, $password);
        
        if ($user) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            Log::info('User logged in via local authentication', [
                'user_id' => $user->id,
                'email' => $user->email,
                'method' => 'local_fallback',
            ]);

            return redirect()->intended(route('dashboard', absolute: false))
                ->with('warning', 'Login menggunakan database lokal (SIAKAD tidak tersedia).');
        }

        // Authentication failed
        throw ValidationException::withMessages([
            'username' => 'Username atau password salah. Pastikan Anda menggunakan kredensial SIAKAD yang benar.',
        ]);
    }

    /**
     * Handle SIAKAD SSO callback (if using OAuth)
     */
    public function callback(Request $request): RedirectResponse
    {
        // This can be used if SIAKAD implements OAuth/SAML
        // For now, we're using direct portal authentication
        
        $token = $request->query('token');
        
        if (!$token) {
            return redirect()->route('login')->with('error', 'Invalid SSO token');
        }

        // Validate token and get user data from SIAKAD
        // Implementation depends on SIAKAD's SSO mechanism
        
        return redirect()->route('dashboard');
    }
}
