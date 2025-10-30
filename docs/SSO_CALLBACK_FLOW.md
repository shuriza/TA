# SSO Callback Flow - Smart LMS ↔ SIAKAD Polinema

**Versi:** 2.0  
**Tanggal:** Januari 2025  
**Status:** Testing Ready ✅

## 📋 Daftar Isi

1. [Overview](#overview)
2. [SSO Flow Diagram](#sso-flow-diagram)
3. [Authentication Methods](#authentication-methods)
4. [Implementation](#implementation)
5. [Testing Guide](#testing-guide)
6. [Security](#security)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

### Apa itu SSO (Single Sign-On)?

SSO adalah metode autentikasi yang memungkinkan user login ke multiple aplikasi dengan satu set kredensial. Di sistem Polinema:

- **Login sekali** di SIAKAD → **Akses otomatis** ke LMS
- **Tidak perlu input ulang** username/password
- **Token-based authentication** dengan signature verification

### Sistem yang Ada di Polinema

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│   SIAKAD    │ ───> │ Smart LMS   │ ───> │    SPADA    │
│ (Main Auth) │      │ (SSO Client)│      │ (SSO Client)│
└─────────────┘      └─────────────┘      └─────────────┘
      ↑                     ↑                     ↑
      └─────────────────────┴─────────────────────┘
              Single Login - Multiple Access
```

### User Flow

1. **Mahasiswa/Dosen** login ke **SIAKAD** dengan NIM/NIP & Password
2. Di dashboard SIAKAD, klik tombol **"Connect to LMS"** atau **"Akses LMS"**
3. **Otomatis redirect** ke Smart LMS dengan **token**
4. Smart LMS **validasi token** dengan SIAKAD
5. User **langsung masuk** ke LMS tanpa login ulang

---

## 🔄 SSO Flow Diagram

### Sequence Diagram

```
┌─────────┐         ┌─────────┐         ┌─────────┐         ┌─────────┐
│  User   │         │ SIAKAD  │         │   LMS   │         │ Database│
└────┬────┘         └────┬────┘         └────┬────┘         └────┬────┘
     │                   │                   │                   │
     │  1. Login         │                   │                   │
     ├──────────────────>│                   │                   │
     │                   │                   │                   │
     │  2. Success       │                   │                   │
     │<──────────────────┤                   │                   │
     │                   │                   │                   │
     │  3. Click "LMS"   │                   │                   │
     ├──────────────────>│                   │                   │
     │                   │                   │                   │
     │                   │ 4. Generate Token │                   │
     │                   ├───────────────────┤                   │
     │                   │                   │                   │
     │  5. Redirect to LMS with Token        │                   │
     │<──────────────────┴──────────────────>│                   │
     │                                       │                   │
     │                                       │ 6. Validate Token │
     │                                       ├──────────────────>│
     │                                       │                   │
     │                                       │ 7. User Data      │
     │                                       │<──────────────────┤
     │                                       │                   │
     │                                       │ 8. Create Session │
     │                                       ├───────────────────┤
     │                                       │                   │
     │  9. Dashboard (Logged In)             │                   │
     │<──────────────────────────────────────┤                   │
     │                                       │                   │
```

### Technical Flow

```
SIAKAD Portal                    Smart LMS
─────────────                    ─────────
     │
     │ User Profile: 
     │ - NIM: 2341760001
     │ - Nama: Ahmad Rizki
     │ - Email: 2341760001@student.polinema.ac.id
     │
     │ [Connect to LMS Button]
     │         │
     │         ▼
     │   Generate Token:
     │   - token = random(64)
     │   - timestamp = time()
     │   - signature = hmac(token + timestamp, secret)
     │         │
     │         ▼
     │   Redirect to:
     │   https://lms.polinema.ac.id/auth/siakad/callback?
     │       token=abc123...&timestamp=1704067200
     │
     └──────────────────────────────>  │
                                       │
                                  Callback Handler:
                                       │
                                       ▼
                                  Validate Token:
                                  - Check expiry (5 min)
                                  - Verify signature
                                  - Call SIAKAD API
                                       │
                                       ▼
                                  Get User Data:
                                  - NIM/NIP
                                  - Nama
                                  - Email
                                  - Role
                                  - Prodi
                                       │
                                       ▼
                                  Find/Create User:
                                  - Check by NIM/NIP
                                  - Create if not exist
                                  - Update data
                                  - Mark as SSO user
                                       │
                                       ▼
                                  Login User:
                                  - Create session
                                  - Set cookies
                                  - Regenerate token
                                       │
                                       ▼
                                  Redirect to Dashboard
                                  ✅ User logged in!
```

---

## 🔐 Authentication Methods

Smart LMS mendukung **3 metode SSO** untuk fleksibilitas:

### 1️⃣ Token-Based Authentication (Primary)

**Cara Kerja:**
```php
// SIAKAD generates token
$token = Str::random(64);
$timestamp = time();

// Cache user data with token
Cache::put('sso_token_' . $token, $userData, 5 * 60);

// Redirect to LMS
redirect("https://lms.polinema.ac.id/auth/siakad/callback?token={$token}&timestamp={$timestamp}");
```

**LMS validates:**
```php
// Check token exists
$userData = Cache::get('sso_token_' . $token);

// Check expiry
if ((time() - $timestamp) > 300) {
    throw new Exception('Token expired');
}

// Validate with SIAKAD API
$response = Http::timeout(10)
    ->post(config('services.siakad.api_url') . '/sso/validate', [
        'token' => $token,
    ]);

// Create/login user
$user = User::firstOrCreate([
    'nim' => $userData['nim'],
], $userData);

Auth::login($user);
```

**Keuntungan:**
- ✅ Paling aman
- ✅ Token one-time use
- ✅ Auto-expiry (5 menit)
- ✅ Mudah di-revoke

### 2️⃣ Session-Based Authentication

**Cara Kerja:**
```php
// SIAKAD sets shared cookie
setcookie('siakad_session', $sessionId, [
    'domain' => '.polinema.ac.id',  // Shared domain
    'secure' => true,
    'httponly' => true,
]);

// LMS reads cookie
$sessionId = $_COOKIE['siakad_session'];

// Validate with SIAKAD
$response = Http::get(config('services.siakad.api_url') . '/sso/session/' . $sessionId);
```

**Keuntungan:**
- ✅ Seamless experience
- ✅ No query parameters
- ✅ Persistent session

**Kekurangan:**
- ⚠️ Requires same domain (.polinema.ac.id)

### 3️⃣ Signed Data Authentication

**Cara Kerja:**
```php
// SIAKAD encrypts & signs data
$userData = [
    'nim' => '2341760001',
    'nama' => 'Ahmad Rizki',
    // ...
];

$encryptedData = base64_encode(json_encode($userData));
$timestamp = time();
$signature = hash_hmac('sha256', $encryptedData . $timestamp, $sharedSecret);

// Redirect with signed data
redirect("https://lms.polinema.ac.id/auth/siakad/callback?" . http_build_query([
    'data' => $encryptedData,
    'timestamp' => $timestamp,
    'signature' => $signature,
]));
```

**LMS validates:**
```php
// Verify signature
$expectedSignature = hash_hmac('sha256', $data . $timestamp, $sharedSecret);

if (!hash_equals($expectedSignature, $signature)) {
    throw new Exception('Invalid signature');
}

// Decrypt & use data
$userData = json_decode(base64_decode($data), true);
```

**Keuntungan:**
- ✅ Self-contained (no API call)
- ✅ Works offline
- ✅ Cryptographically secure

**Kekurangan:**
- ⚠️ Data visible in URL (meski encrypted)

---

## 💻 Implementation

### File Structure

```
app/
├── Http/Controllers/Auth/
│   ├── SIAKADSSOController.php      ← Main SSO controller
│   └── SIAKADAuthController.php     ← Legacy auth
├── Http/Controllers/
│   └── SIAKADSimulatorController.php ← Testing simulator
│
config/
└── services.php                      ← SIAKAD config
    └── siakad.shared_secret
│
routes/
├── auth.php                          ← SSO routes
│   ├── /auth/siakad/callback
│   ├── /auth/siakad/redirect
│   └── /auth/siakad/test-token
└── web.php                           ← Simulator routes
    └── /siakad-demo
│
resources/views/
└── siakad-simulator.blade.php        ← SIAKAD portal mockup
│
.env
└── SIAKAD_SHARED_SECRET=...          ← Secret key
```

### Routes

```php
// SSO Callback (Primary endpoint)
Route::get('auth/siakad/callback', [SIAKADSSOController::class, 'callback'])
    ->name('siakad.sso.callback');

// Redirect to SIAKAD (Not used if SIAKAD initiates)
Route::get('auth/siakad/redirect', [SIAKADSSOController::class, 'redirect'])
    ->name('siakad.sso.redirect');

// Generate test token (Development only)
Route::get('auth/siakad/test-token', [SIAKADSSOController::class, 'generateTestToken'])
    ->name('siakad.sso.test');
    
// SIAKAD Simulator (Testing)
Route::get('/siakad-demo', [SIAKADSimulatorController::class, 'dashboard'])
    ->name('siakad.simulator');
```

### Configuration

**.env**
```env
SIAKAD_URL=https://siakad.polinema.ac.id
SIAKAD_API_URL=https://siakad.polinema.ac.id/api
SIAKAD_SHARED_SECRET=lms-cerdas-polinema-secret-2025
SIAKAD_SSO_ENABLED=true
SIAKAD_TIMEOUT=10
```

**config/services.php**
```php
'siakad' => [
    'url' => env('SIAKAD_URL', 'https://siakad.polinema.ac.id'),
    'api_url' => env('SIAKAD_API_URL'),
    'timeout' => env('SIAKAD_TIMEOUT', 10),
    'enabled' => env('SIAKAD_SSO_ENABLED', false),
    'shared_secret' => env('SIAKAD_SHARED_SECRET', 'default-secret-key-change-in-production'),
],
```

---

## 🧪 Testing Guide

### 1. Test dengan Simulator

**Step by step:**

```bash
# 1. Akses simulator
http://localhost:8000/siakad-demo

# 2. Pilih role:
# - Mahasiswa: http://localhost:8000/siakad-demo?role=mahasiswa
# - Dosen: http://localhost:8000/siakad-demo?role=dosen

# 3. Klik tombol "Connect to LMS"

# 4. Akan auto-redirect ke:
http://localhost:8000/auth/siakad/callback?token=...&timestamp=...

# 5. Jika sukses, redirect ke dashboard dengan user logged in
```

**Expected Result:**
```
✅ User profile muncul di SIAKAD simulator
✅ Tombol "Connect to LMS" berwarna hijau
✅ Klik tombol → redirect ke callback
✅ Callback validate token → success
✅ User logged in di LMS dashboard
✅ Nama & email sesuai dengan data SIAKAD
```

### 2. Test Manual dengan Token

**Generate token via API:**

```bash
# Get test token
curl http://localhost:8000/auth/siakad/test-token?role=mahasiswa

# Response:
{
  "message": "Test SSO token generated",
  "callback_url": "http://localhost:8000/auth/siakad/callback?token=abc123...&timestamp=1704067200",
  "user_data": {
    "nim": "2341760001",
    "nama": "Ahmad Rizki Pratama",
    "email": "2341760001@student.polinema.ac.id",
    "role": "mahasiswa",
    "prodi": "D4 Teknik Informatika"
  },
  "expires_in": 300,
  "instructions": [
    "1. Copy callback_url",
    "2. Paste di browser",
    "3. Will auto-login to LMS"
  ]
}
```

**Copy `callback_url` dan paste di browser:**
```
http://localhost:8000/auth/siakad/callback?token=abc123...&timestamp=1704067200
```

### 3. Test dengan cURL

```bash
# Test callback endpoint
curl -X GET "http://localhost:8000/auth/siakad/callback?token=YOUR_TOKEN&timestamp=1704067200" \
  -H "Accept: application/json" \
  --cookie-jar cookies.txt \
  --location

# Check response
# Should redirect to /dashboard with session cookie
```

### 4. Test Signature Verification

```bash
# Generate signed data
php artisan tinker

# In tinker:
$userData = ['nim' => '2341760001', 'nama' => 'Ahmad'];
$encrypted = base64_encode(json_encode($userData));
$timestamp = time();
$signature = hash_hmac('sha256', $encrypted . $timestamp, config('services.siakad.shared_secret'));

echo "http://localhost:8000/auth/siakad/callback?data={$encrypted}&timestamp={$timestamp}&signature={$signature}";
```

---

## 🔒 Security

### Security Features

| Feature | Implementation | Status |
|---------|---------------|--------|
| **Token Expiry** | 5 minutes TTL | ✅ |
| **One-Time Use** | Token deleted after use | ✅ |
| **HMAC Signature** | SHA-256 verification | ✅ |
| **Shared Secret** | 256-bit key | ✅ |
| **HTTPS Only** | Production enforced | ⚠️ Local |
| **CSRF Protection** | Laravel built-in | ✅ |
| **Session Regeneration** | After login | ✅ |
| **Rate Limiting** | 10 requests/minute | ✅ |

### Shared Secret Management

**⚠️ CRITICAL: Never commit shared secret to Git!**

```bash
# Generate strong secret
php -r "echo bin2hex(random_bytes(32));"

# Output: f9e4c6a8b2d5e7f1a3c4d6e8f0a2b4c6d8e0f2a4b6c8d0e2f4a6b8c0d2e4f6a8

# Add to .env (NOT .env.example)
SIAKAD_SHARED_SECRET=f9e4c6a8b2d5e7f1a3c4d6e8f0a2b4c6d8e0f2a4b6c8d0e2f4a6b8c0d2e4f6a8
```

**Production:**
- Koordinasi dengan IT Polinema untuk shared secret
- Sama di SIAKAD dan LMS
- Rotate secret setiap 6 bulan

### Token Security

```php
// Token structure
$token = [
    'value' => Str::random(64),      // 64 chars random
    'created_at' => time(),          // Timestamp
    'expires_at' => time() + 300,    // 5 minutes
    'user_data' => [...],            // Cached data
];

// Validation
if ((time() - $timestamp) > 300) {
    // Token expired
    abort(401, 'SSO token expired. Please try again.');
}

// One-time use
Cache::forget('sso_token_' . $token);
```

### HTTPS Enforcement

```php
// Middleware untuk production
if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Redirect callback harus HTTPS
if (!request()->secure() && app()->environment('production')) {
    abort(403, 'HTTPS required');
}
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Token Expired

**Error:**
```json
{
  "error": "SSO token expired",
  "message": "Token expired. Please try again.",
  "code": 401
}
```

**Solusi:**
- Token hanya valid 5 menit
- Klik "Connect to LMS" lagi
- Jangan bookmark callback URL

#### 2. Invalid Signature

**Error:**
```json
{
  "error": "Invalid signature",
  "message": "SSO signature verification failed",
  "code": 403
}
```

**Solusi:**
- Check `SIAKAD_SHARED_SECRET` sama di SIAKAD & LMS
- Pastikan tidak ada whitespace/newline di secret
- Regenerate token

#### 3. Token Not Found

**Error:**
```json
{
  "error": "SSO token not found",
  "message": "Invalid or already used token",
  "code": 404
}
```

**Solusi:**
- Token sudah digunakan (one-time use)
- Token expired dari cache
- Generate token baru

#### 4. SIAKAD API Timeout

**Error:**
```json
{
  "error": "SIAKAD API timeout",
  "message": "Could not validate token with SIAKAD",
  "code": 504
}
```

**Solusi:**
- Check koneksi internet
- SIAKAD server down/maintenance
- Increase timeout di config (`SIAKAD_TIMEOUT=30`)

### Debug Mode

**Enable debug logging:**

```php
// In SIAKADSSOController
protected function logDebug($message, $data = [])
{
    if (config('app.debug')) {
        Log::channel('sso')->info($message, $data);
    }
}

// Usage
$this->logDebug('SSO callback received', [
    'token' => $token,
    'timestamp' => $timestamp,
    'ip' => request()->ip(),
]);
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep SSO
```

### Testing Checklist

- [ ] Simulator page loads (`/siakad-demo`)
- [ ] User profile displayed correctly
- [ ] "Connect to LMS" button visible
- [ ] Click button → redirect to callback
- [ ] Token validated successfully
- [ ] User logged in to LMS
- [ ] Dashboard shows correct user data
- [ ] Logout works properly
- [ ] SSO works for mahasiswa role
- [ ] SSO works for dosen role
- [ ] Token expires after 5 minutes
- [ ] Used token cannot be reused
- [ ] Invalid signature rejected

---

## 📞 Contact & Support

**Development Team:**
- **Lead Developer:** [Your Name]
- **Email:** dev@polinema.ac.id
- **IT Support:** it@polinema.ac.id

**Documentation Updates:**
- Last Updated: Januari 2025
- Version: 2.0
- Status: Testing Phase

---

## 🎓 Next Steps

1. **Testing Phase** (Current)
   - Test all 3 authentication methods
   - Verify security measures
   - Load testing

2. **Coordination with IT Polinema**
   - Share shared secret securely
   - Configure production callback URLs
   - Setup monitoring

3. **Production Deployment**
   - Enable HTTPS enforcement
   - Configure rate limiting
   - Setup error monitoring

4. **Documentation for Users**
   - Create user guide
   - Video tutorial
   - FAQ

---

**Status:** ✅ Ready for Testing  
**Next Milestone:** Production Coordination with IT Polinema
