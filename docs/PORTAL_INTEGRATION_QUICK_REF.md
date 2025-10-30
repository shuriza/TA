# 🎯 SIAKAD Portal Integration - Quick Reference

## ✨ **APA YANG BERUBAH?**

Sekarang LMS Cerdas login ke SIAKAD **TANPA PERLU API**!

### **Metode Baru: Web Scraping** 🌐

```
┌──────────────────────────────────────────────────────┐
│  SEBELUM (Butuh API):                                │
│  ❌ Memerlukan SIAKAD API endpoint                   │
│  ❌ Memerlukan API key/token                         │
│  ❌ Harus koordinasi dengan IT Polinema              │
└──────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│  SEKARANG (Portal Scraping):                         │
│  ✅ Langsung akses halaman web SIAKAD                │
│  ✅ Login dengan credentials asli SIAKAD             │
│  ✅ Extract data dari HTML dashboard                 │
│  ✅ Tidak perlu API atau koordinasi khusus           │
└──────────────────────────────────────────────────────┘
```

---

## 🔄 **CARA KERJA (Simplified)**

```
1. User masukkan NIM + Password
        ↓
2. LMS buka halaman login SIAKAD
        ↓
3. LMS kirim form login (seperti browser biasa)
        ↓
4. SIAKAD return dashboard page
        ↓
5. LMS parse HTML, ambil data user
        ↓
6. LMS buat/update user di database
        ↓
7. User login ke LMS Dashboard
```

---

## 🛠️ **FILES YANG DITAMBAHKAN**

| File | Fungsi |
|------|--------|
| `app/Services/SIAKADPortalService.php` | Core logic web scraping |
| `docs/SIAKAD_PORTAL_INTEGRATION.md` | Dokumentasi teknis lengkap |

**Dependencies Installed:**
- `symfony/dom-crawler` - Parse HTML
- `symfony/css-selector` - CSS selector support

---

## ⚙️ **KONFIGURASI**

### **.env**
```env
SIAKAD_URL=https://siakad.polinema.ac.id
SIAKAD_USE_PORTAL=true    # ← Enable portal scraping
SIAKAD_SSO_ENABLED=true
SIAKAD_FALLBACK_LOCAL=true
SIAKAD_TIMEOUT=30
```

### **Priority Login Methods:**
```
1. Portal Scraping (SIAKAD_USE_PORTAL=true) ← DEFAULT
2. API (jika tersedia)
3. Local Database (fallback)
```

---

## 🧪 **TESTING**

### **Test Availability:**
```php
php artisan tinker

$service = app(\App\Services\SIAKADPortalService::class);
$service->isAvailable(); // true = SIAKAD online
```

### **Test Login (Gunakan Credentials Dummy):**
```php
$result = $service->login('2341760001', 'password123');

// Expected output:
[
    'nim' => '2341760001',
    'nama' => 'User Name',
    'email' => '2341760001@student.polinema.ac.id',
    'prodi' => 'D4 Teknik Informatika',
    'role' => 'mahasiswa'
]
```

### **Test via Browser:**
```
1. Buka: http://localhost/login
2. Input NIM: 2341760001
3. Input Password: (password SIAKAD asli)
4. Klik "Login dengan SIAKAD"
5. Check logs: storage/logs/laravel.log
```

---

## 🔍 **DATA EXTRACTION**

### **Apa yang Di-extract dari SIAKAD:**

| Data Field | Source | Example |
|------------|--------|---------|
| **NIM/NIP** | Dashboard HTML | `2341760001` |
| **Nama** | Profile section | `John Doe` |
| **Email** | Generated/Extracted | `2341760001@student.polinema.ac.id` |
| **Prodi** | Profile section | `D4 Teknik Informatika` |
| **Role** | Detected from page | `mahasiswa` atau `dosen` |

### **Detection Patterns:**

```php
// NIM Pattern
preg_match('/(?:NIM|nim)[\s:]*(\d{10,15})/', $html, $matches);

// NIP Pattern  
preg_match('/(?:NIP|nip)[\s:]*(\d{10,15})/', $html, $matches);

// Nama Pattern
preg_match('/(?:Nama|nama)[\s:]*([A-Za-z\s\.]+)/', $html, $matches);
```

---

## ⚠️ **IMPORTANT NOTES**

### **1. HTML Structure Dependency**
```
Portal scraping bergantung pada struktur HTML SIAKAD.
Jika SIAKAD update tampilan, extraction mungkin perlu disesuaikan.
```

**Solusi:**
- Multiple extraction patterns implemented
- Fallback generation untuk missing data
- Logging untuk debug

### **2. Session Caching**
```
Session SIAKAD di-cache 2 jam di Laravel cache.
Jika SIAKAD logout di portal, cache tetap valid.
```

**Workaround:**
- Implement session validation
- Re-authenticate jika request gagal

### **3. SSL Verification**
```
Development: 'verify' => false (allow self-signed)
Production:  'verify' => true  (strict SSL)
```

---

## 🐛 **TROUBLESHOOTING**

### **Problem: "Login failed"**
```bash
# Check SIAKAD portal
curl https://siakad.polinema.ac.id/login

# Check logs
tail -f storage/logs/laravel.log | grep SIAKAD
```

**Common Causes:**
- SIAKAD portal down/maintenance
- Wrong credentials
- HTML structure changed
- Network timeout

**Solutions:**
1. Try direct login at SIAKAD portal
2. Check field names in HTML form
3. Increase timeout in .env
4. Use fallback login: `/login/standard`

### **Problem: "Data not extracted"**
```
Check logs untuk HTML output:
storage/logs/laravel.log
```

**Fix:**
1. Inspect SIAKAD dashboard HTML
2. Update extraction patterns di `SIAKADPortalService::extractUserData()`
3. Add more regex patterns

---

## 🚀 **DEPLOYMENT**

### **Production Checklist:**

```
✅ Test dengan real SIAKAD credentials
✅ Verify extraction patterns work
✅ Enable SSL verification
✅ Set proper timeout values
✅ Add rate limiting
✅ Monitor error logs
✅ Document any HTML changes
```

### **Environment:**
```env
APP_ENV=production
SIAKAD_USE_PORTAL=true
SIAKAD_TIMEOUT=30
LOG_LEVEL=warning
```

---

## 📊 **COMPARISON**

| Aspect | API Method | Portal Scraping |
|--------|------------|-----------------|
| **Dependency** | Need API access | Public web portal |
| **Setup** | Coordination required | Ready to use |
| **Reliability** | High (if API stable) | Medium (HTML changes) |
| **Speed** | Fast | Medium |
| **Maintenance** | Low | Medium |
| **Security** | High | Medium-High |

---

## 💡 **TIPS**

### **For Testing:**
```bash
# Enable debug logging
LOG_LEVEL=debug

# Test with dummy data first
# Then with real SIAKAD credentials
```

### **For Production:**
```bash
# Monitor SIAKAD availability
php artisan schedule:run

# Create health check command
php artisan make:command CheckSIAKADHealth
```

### **For Maintenance:**
```bash
# If SIAKAD HTML changes:
1. Login to SIAKAD manually
2. Inspect element → View page source
3. Update patterns in SIAKADPortalService.php
4. Test extraction
5. Deploy
```

---

## 📚 **DOCUMENTATION**

- **Full Technical Docs**: [docs/SIAKAD_PORTAL_INTEGRATION.md](SIAKAD_PORTAL_INTEGRATION.md)
- **User Guide**: [docs/LOGIN_GUIDE.md](LOGIN_GUIDE.md)
- **Implementation Summary**: [SIAKAD_SSO_IMPLEMENTATION.md](../SIAKAD_SSO_IMPLEMENTATION.md)

---

## ✅ **STATUS**

```
Portal Integration: ✅ IMPLEMENTED
Dependencies:       ✅ INSTALLED  
Configuration:      ✅ CONFIGURED
Documentation:      ✅ COMPLETE
Testing:            ⚠️  NEEDS REAL CREDENTIALS

Ready for: Development Testing
Next: Test with actual SIAKAD credentials
```

---

**Quick Commands:**

```bash
# Test availability
php artisan tinker
>>> app(\App\Services\SIAKADPortalService::class)->isAvailable()

# Clear cache
php artisan config:clear

# View logs
tail -f storage/logs/laravel.log

# Test login page
curl https://siakad.polinema.ac.id/login
```

---

**Version**: 2.0.0  
**Method**: Portal Web Scraping  
**Date**: October 29, 2025  
**Status**: ✅ Ready for Testing
