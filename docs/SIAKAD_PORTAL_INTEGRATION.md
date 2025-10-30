# 🌐 SIAKAD Portal Integration - Web Scraping Method

## 📋 Overview

Karena **Portal SIAKAD Polinema tidak menyediakan API publik**, kami menggunakan **Web Scraping** untuk mengintegrasikan login SIAKAD dengan LMS Cerdas.

### ✅ Keuntungan Metode Ini:
- ✅ **Tidak perlu API** - Langsung akses portal web SIAKAD
- ✅ **Real credentials** - User login dengan username/password SIAKAD asli
- ✅ **Auto extract data** - Ambil data user dari halaman dashboard
- ✅ **Session caching** - Simpan session untuk request berikutnya
- ✅ **Fallback ready** - Jika gagal, ada local authentication

---

## 🔄 Cara Kerja

### **Flow Diagram:**

```
┌────────────────────────────────────────────────────────────┐
│ 1. USER: Input NIM/NIP + Password                         │
└────────────────────┬───────────────────────────────────────┘
                     │
                     ▼
┌────────────────────────────────────────────────────────────┐
│ 2. LMS: GET https://siakad.polinema.ac.id/login           │
│    - Load halaman login SIAKAD                            │
│    - Extract CSRF token dari form                         │
│    - Detect field names (username, password)              │
└────────────────────┬───────────────────────────────────────┘
                     │
                     ▼
┌────────────────────────────────────────────────────────────┐
│ 3. LMS: POST ke SIAKAD Login Form                         │
│    - Submit username, password, CSRF token                │
│    - Dapatkan cookies session                             │
└────────────────────┬───────────────────────────────────────┘
                     │
                     ▼
┌────────────────────────────────────────────────────────────┐
│ 4. LMS: Parse Response HTML                               │
│    - Cek apakah login berhasil                            │
│    - Extract: NIM/NIP, Nama, Email, Prodi                 │
│    - Detect role (mahasiswa/dosen)                        │
└────────────────────┬───────────────────────────────────────┘
                     │
                     ▼
┌────────────────────────────────────────────────────────────┐
│ 5. LMS: Get or Create User in Database                    │
│    - Cari user by email/nim/nip                           │
│    - Buat baru jika belum ada                             │
│    - Update data dari SIAKAD                              │
│    - Set role dan flag SSO                                │
└────────────────────┬───────────────────────────────────────┘
                     │
                     ▼
┌────────────────────────────────────────────────────────────┐
│ 6. LMS: Login Success → Dashboard                         │
│    - Cache session untuk reuse                            │
│    - Redirect ke dashboard sesuai role                    │
└────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Implementasi Teknis

### **1. Service: SIAKADPortalService**

**File**: `app/Services/SIAKADPortalService.php`

**Key Methods:**

```php
// Login ke portal SIAKAD
login(string $username, string $password): ?array

// Extract data user dari HTML
extractUserData(string $html, string $username): ?array

// Cek apakah login berhasil
checkLoginSuccess(string $body, int $statusCode): bool

// Get courses (future feature)
getCourses(string $username): ?array

// Health check
isAvailable(): bool
```

### **2. HTML Parsing Strategy**

Kami menggunakan **Symfony DOM Crawler** untuk parse HTML:

```php
use Symfony\Component\DomCrawler\Crawler;

$crawler = new Crawler($html);

// Extract CSRF token
$csrfToken = $crawler->filter('input[name="_token"]')->attr('value');

// Extract user info
$profileText = $crawler->filter('.profile, .user-info')->text();

// Regex patterns for NIM/NIP
preg_match('/(?:NIM|nim)[\s:]*(\d{10,15})/', $profileText, $matches);
```

### **3. Data Extraction Patterns**

**Pattern 1: Profile Section**
```html
<div class="profile">
  NIM: 2341760001
  Nama: John Doe
  Prodi: D4 Teknik Informatika
</div>
```

**Pattern 2: Meta Tags**
```html
<title>Dashboard Mahasiswa - SIAKAD Polinema</title>
```

**Pattern 3: Table Data**
```html
<table>
  <tr><td>NIM</td><td>2341760001</td></tr>
  <tr><td>Nama</td><td>John Doe</td></tr>
</table>
```

### **4. Success Detection**

Login dianggap berhasil jika:
- ✅ Body HTML mengandung: "dashboard", "beranda", "logout", "profil"
- ✅ HTTP Status Code: 200-299 atau 300-399 (redirect)
- ❌ Gagal jika ada: "login gagal", "invalid credentials"

---

## 🔧 Konfigurasi

### **Environment Variables (.env)**

```env
# SIAKAD Portal Integration
SIAKAD_URL=https://siakad.polinema.ac.id
SIAKAD_USE_PORTAL=true              # Enable web scraping
SIAKAD_SSO_ENABLED=true             # Enable SSO
SIAKAD_FALLBACK_LOCAL=true          # Allow local login
SIAKAD_TIMEOUT=30                   # Request timeout (seconds)
```

### **Disable Portal Scraping (Use API Only)**

```env
SIAKAD_USE_PORTAL=false
```

---

## 🎯 Testing

### **Test Manual:**

1. **Buka browser**
2. **Login ke SIAKAD Portal**: https://siakad.polinema.ac.id/login
3. **Inspect element** pada form login
4. **Catat field names**: `username`, `password`, `_token`
5. **Test di LMS**: http://localhost/login
6. **Input NIM + Password SIAKAD**
7. **Check logs**: `storage/logs/laravel.log`

### **Test dengan Tinker:**

```php
php artisan tinker

// Test portal service
$service = app(\App\Services\SIAKADPortalService::class);

// Check availability
$service->isAvailable(); // true/false

// Test login (use real credentials)
$result = $service->login('2341760001', 'password');
// Returns: array with nim, nama, email, prodi, role
```

---

## 📊 Session Caching

Session SIAKAD di-cache untuk menghindari login berulang:

```php
// Cache key format
$sessionKey = 'siakad_session_' . md5($username);

// Cache structure
[
    'cookies' => [...],      // Session cookies dari SIAKAD
    'user_data' => [         // Data user yang di-extract
        'nim' => '2341760001',
        'nama' => 'John Doe',
        'email' => '2341760001@student.polinema.ac.id',
        'prodi' => 'D4 Teknik Informatika',
        'role' => 'mahasiswa'
    ]
]

// TTL: 2 hours
```

**Keuntungan Caching:**
- ✅ Tidak perlu login ulang untuk 2 jam
- ✅ Bisa fetch data dari SIAKAD (courses, grades) tanpa re-auth
- ✅ Improve performance

---

## 🔐 Security Considerations

### **1. Password Handling**
```php
❌ NEVER stored in LMS database
✅ Only sent to SIAKAD portal via HTTPS
✅ Cleared from memory after authentication
```

### **2. Session Security**
```php
✅ Cookies encrypted in cache
✅ Session expires after 2 hours
✅ Auto-logout on SIAKAD side still applies
```

### **3. SSL Verification**
```php
// Development (allow self-signed)
'verify' => false

// Production (strict SSL)
'verify' => true
```

### **4. Rate Limiting**
```php
// Prevent brute force
'throttle:10,1' // 10 attempts per minute
```

---

## ⚠️ Limitations & Challenges

### **1. HTML Structure Changes**
**Problem**: SIAKAD bisa update struktur HTML kapan saja  
**Solution**: Multiple extraction patterns + fallback logic

### **2. Login Form Variations**
**Problem**: Field names bisa berbeda (username vs nim vs nip)  
**Solution**: Auto-detect field names dari form

### **3. Session Expiry**
**Problem**: SIAKAD session expired sebelum cache  
**Solution**: Re-authenticate otomatis jika request gagal

### **4. Cloudflare/WAF**
**Problem**: SIAKAD mungkin pakai Cloudflare anti-bot  
**Solution**: Add proper user-agent headers

```php
Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)...',
    'Accept' => 'text/html,application/xhtml+xml...',
])
```

---

## 🚀 Advanced Features (Future)

### **1. Course Sync**
```php
// Get enrolled courses from SIAKAD dashboard
$courses = $service->getCourses($username);

// Sync to LMS database
foreach ($courses as $course) {
    Course::updateOrCreate(...);
}
```

### **2. Grade Sync**
```php
// Extract grades from SIAKAD
$grades = $service->getGrades($username);
```

### **3. Schedule Sync**
```php
// Get class schedule
$schedule = $service->getSchedule($username);
```

### **4. Announcement Scraping**
```php
// Get latest announcements
$announcements = $service->getAnnouncements();
```

---

## 📖 Example: Real SIAKAD HTML Structure

### **Login Page (Hypothetical)**

```html
<!DOCTYPE html>
<html>
<head>
    <title>Login - SIAKAD Polinema</title>
</head>
<body>
    <form action="/auth/login" method="POST">
        <input type="hidden" name="_token" value="abc123xyz">
        
        <label>NIM/NIP</label>
        <input type="text" name="username" placeholder="Masukkan NIM/NIP">
        
        <label>Password</label>
        <input type="password" name="password">
        
        <button type="submit">Login</button>
    </form>
</body>
</html>
```

### **Dashboard Page (After Login)**

```html
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Mahasiswa - SIAKAD Polinema</title>
</head>
<body>
    <div class="user-info">
        <h2>Selamat Datang, John Doe</h2>
        <div class="profile-data">
            <p>NIM: 2341760001</p>
            <p>Nama: John Doe</p>
            <p>Prodi: D4 Teknik Informatika</p>
            <p>Email: john@student.polinema.ac.id</p>
        </div>
    </div>
    
    <a href="/logout">Logout</a>
    
    <div class="courses">
        <h3>Mata Kuliah</h3>
        <ul>
            <li>Pemrograman Web</li>
            <li>Basis Data</li>
        </ul>
    </div>
</body>
</html>
```

---

## 🧪 Debug & Troubleshooting

### **Enable Debug Logging**

```php
// In SIAKADPortalService.php
Log::debug('SIAKAD Login HTML', [
    'html' => substr($html, 0, 1000), // First 1000 chars
]);
```

### **Check Logs**

```bash
tail -f storage/logs/laravel.log | grep SIAKAD
```

### **Test with curl**

```bash
# Get login page
curl -v https://siakad.polinema.ac.id/login

# Check form structure
curl https://siakad.polinema.ac.id/login | grep "input name"
```

---

## 📚 Dependencies

```json
{
    "require": {
        "symfony/dom-crawler": "^7.3",
        "symfony/css-selector": "^7.3",
        "guzzlehttp/guzzle": "^7.10"
    }
}
```

### **Installation:**

```bash
composer require symfony/dom-crawler symfony/css-selector
```

---

## ✅ Production Checklist

- [ ] Test dengan real SIAKAD credentials
- [ ] Verify HTML structure match extraction patterns
- [ ] Enable SSL verification (`'verify' => true`)
- [ ] Set proper user-agent headers
- [ ] Implement rate limiting
- [ ] Add error monitoring
- [ ] Cache optimization
- [ ] Handle SIAKAD maintenance mode
- [ ] Add retry logic for network failures
- [ ] Document SIAKAD HTML changes

---

## 📞 Support

### **If Login Fails:**

1. Check SIAKAD portal directly: https://siakad.polinema.ac.id
2. Verify username/password
3. Check logs: `storage/logs/laravel.log`
4. Try fallback login: http://localhost/login/standard
5. Contact IT Polinema if SIAKAD down

### **For Developers:**

- **Service**: `app/Services/SIAKADPortalService.php`
- **Controller**: `app/Http/Controllers/Auth/SIAKADAuthController.php`
- **Config**: `config/services.php` → `siakad.use_portal`
- **Logs**: `storage/logs/laravel.log`

---

**Last Updated**: October 29, 2025  
**Version**: 2.0.0 - Portal Integration  
**Method**: Web Scraping + HTML Parsing  
**Status**: ✅ Ready for Testing
