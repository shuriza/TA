<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class SIAKADAuthService
{
    protected $siakadUrl;
    protected $timeout;

    public function __construct()
    {
        $this->siakadUrl = config('services.siakad.url');
        $this->timeout = config('services.siakad.timeout', 30);
    }

    /**
     * Authenticate user via SIAKAD portal
     * 
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $username, string $password): ?array
    {
        try {
            // Attempt to login to SIAKAD portal
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->siakadUrl . '/api/login', [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $userData = $response->json();
                
                // Expected response structure from SIAKAD:
                // {
                //   "success": true,
                //   "data": {
                //     "nim": "2341760001",
                //     "nama": "John Doe",
                //     "email": "john@student.polinema.ac.id",
                //     "prodi": "D4 Teknik Informatika",
                //     "role": "mahasiswa"
                //   },
                //   "token": "..."
                // }
                
                if ($userData['success'] ?? false) {
                    return $userData['data'];
                }
            }

            Log::warning('SIAKAD authentication failed', [
                'username' => $username,
                'status' => $response->status(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('SIAKAD authentication error: ' . $e->getMessage(), [
                'username' => $username,
            ]);

            return null;
        }
    }

    /**
     * Get or create user from SIAKAD data
     * 
     * @param array $siakadData
     * @return User
     */
    public function getOrCreateUser(array $siakadData): User
    {
        $identifier = $siakadData['nim'] ?? $siakadData['nip'] ?? $siakadData['email'];
        $email = $siakadData['email'];

        $user = User::where('email', $email)
            ->orWhere('nim', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $siakadData['nama'] ?? $siakadData['name'],
                'email' => $email,
                'nim' => $siakadData['nim'] ?? null,
                'nip' => $siakadData['nip'] ?? null,
                'prodi' => $siakadData['prodi'] ?? null,
                'password' => Hash::make(uniqid()), // Random password for SSO users
                'email_verified_at' => now(), // Auto-verify SSO users
            ]);

            // Assign role based on SIAKAD data
            $role = $this->mapRole($siakadData['role'] ?? 'mahasiswa');
            $user->assignRole($role);

            Log::info('New user created from SIAKAD', [
                'email' => $email,
                'role' => $role,
            ]);
        } else {
            // Update user data from SIAKAD
            $user->update([
                'name' => $siakadData['nama'] ?? $siakadData['name'],
                'prodi' => $siakadData['prodi'] ?? $user->prodi,
            ]);
        }

        return $user;
    }

    /**
     * Map SIAKAD role to LMS role
     * 
     * @param string $siakadRole
     * @return string
     */
    protected function mapRole(string $siakadRole): string
    {
        $roleMapping = [
            'mahasiswa' => 'mahasiswa',
            'student' => 'mahasiswa',
            'dosen' => 'dosen',
            'lecturer' => 'dosen',
            'admin' => 'admin',
            'staff' => 'admin',
        ];

        return $roleMapping[strtolower($siakadRole)] ?? 'mahasiswa';
    }

    /**
     * Fallback authentication using local database
     * 
     * @param string $username
     * @param string $password
     * @return User|null
     */
    public function fallbackAuthenticate(string $username, string $password): ?User
    {
        // Try to find user by email, nim, or nip
        $user = User::where('email', $username)
            ->orWhere('nim', $username)
            ->orWhere('nip', $username)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Check if SIAKAD service is available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->siakadUrl . '/api/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
