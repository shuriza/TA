<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

/**
 * SIAKAD Portal Simulator
 * 
 * Simulates SIAKAD portal with "Connect to LMS" button
 * This demonstrates the SSO flow from SIAKAD → LMS
 */
class SIAKADSimulatorController extends Controller
{
    /**
     * Show SIAKAD dashboard with LMS connect button
     */
    public function dashboard(Request $request)
    {
        // Simulate SIAKAD user data
        $role = $request->query('role', 'mahasiswa');
        
        $userData = $this->getSimulatedUserData($role);
        
        // Generate SSO token
        $token = Str::random(64);
        $timestamp = time();
        
        // Generate signature (SIAKAD would do this)
        $encryptedData = base64_encode(json_encode($userData));
        $signature = hash_hmac(
            'sha256',
            $encryptedData . $timestamp,
            config('services.siakad.shared_secret')
        );
        
        // Cache token untuk validasi nanti
        Cache::put('sso_token_' . $token, $userData, now()->addMinutes(5));
        
        // SSO URL to LMS
        $ssoUrl = route('siakad.sso.callback');
        
        return view('siakad-simulator', [
            'nama' => $userData['nama'],
            'identifier' => $userData['nim'] ?? $userData['nip'],
            'email' => $userData['email'],
            'prodi' => $userData['prodi'] ?? '-',
            'role' => $role,
            'ssoUrl' => $ssoUrl,
            'token' => $token,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'encryptedData' => $encryptedData,
        ]);
    }

    /**
     * Get simulated user data based on role
     */
    protected function getSimulatedUserData(string $role): array
    {
        if ($role === 'dosen') {
            return [
                'nip' => '198001012000',
                'nama' => 'Dr. Budi Santoso, M.Kom',
                'email' => '198001012000@polinema.ac.id',
                'role' => 'dosen',
                'prodi' => null,
            ];
        }
        
        // Default: mahasiswa
        return [
            'nim' => '2341760001',
            'nama' => 'Ahmad Rizki Pratama',
            'email' => '2341760001@student.polinema.ac.id',
            'prodi' => 'D4 Teknik Informatika',
            'role' => 'mahasiswa',
        ];
    }

    /**
     * Generate direct SSO link (for testing)
     */
    public function generateLink(Request $request)
    {
        $role = $request->query('role', 'mahasiswa');
        $userData = $this->getSimulatedUserData($role);
        
        // Generate token
        $token = Str::random(64);
        $timestamp = time();
        
        // Cache token
        Cache::put('sso_token_' . $token, $userData, now()->addMinutes(5));
        
        // Generate SSO URL
        $ssoUrl = route('siakad.sso.callback') . '?' . http_build_query([
            'token' => $token,
            'timestamp' => $timestamp,
        ]);
        
        return response()->json([
            'message' => 'SSO Link Generated',
            'sso_url' => $ssoUrl,
            'user_data' => $userData,
            'expires_in' => 300,
            'instructions' => [
                '1. Copy SSO URL',
                '2. Paste di browser baru',
                '3. Akan auto-login ke LMS',
            ],
        ]);
    }
}
