# 🔐 Panduan Login LMS Cerdas dengan SIAKAD SSO

## 📋 Ringkasan

LMS Cerdas terintegrasi dengan **Portal SIAKAD Polinema** menggunakan sistem Single Sign-On (SSO). Pengguna dapat login menggunakan kredensial SIAKAD yang sama untuk mengakses kedua sistem.

---

## 🎯 Alur Login

### **Opsi 1: Login dengan SIAKAD SSO (Recommended)**

```
┌─────────────────────────────────────────────────────────────┐
│  1. Buka: http://localhost/login                            │
│                                                              │
│  2. Masukkan NIM/NIP atau Email SIAKAD                      │
│     Contoh:                                                  │
│     - Mahasiswa: 2341760001                                  │
│     - Dosen: 198001012000                                    │
│     - Email: john@student.polinema.ac.id                     │
│                                                              │
│  3. Masukkan Password SIAKAD Anda                            │
│                                                              │
│  4. Klik "Login dengan SIAKAD"                               │
│                                                              │
│  5. Sistem akan:                                             │
│     ✓ Verifikasi kredensial ke Portal SIAKAD                │
│     ✓ Ambil data profil Anda dari SIAKAD                    │
│     ✓ Buat/update akun LMS otomatis                         │
│     ✓ Login ke dashboard sesuai role                        │
└─────────────────────────────────────────────────────────────┘
```

### **Opsi 2: Login dengan Email (Fallback)**

Jika sistem SIAKAD sedang maintenance atau tidak tersedia:

```
1. Klik "Login dengan Email" di halaman login
2. Masukkan email dan password lokal LMS
3. Klik "Login"
```

---

## 🔄 Mekanisme Integrasi SIAKAD

### **1. Autentikasi Hybrid**

```php
┌─────────────┐
│ User Login  │
│ (NIM/NIP)   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Cek: SIAKAD Service Available?          │
└──┬──────────────────────────────────┬───┘
   │ YES                              │ NO
   ▼                                  ▼
┌──────────────────┐         ┌────────────────┐
│ Auth via SIAKAD  │         │ Fallback Auth  │
│ API              │         │ (Local DB)     │
└──┬───────────────┘         └────────┬───────┘
   │                                  │
   ▼                                  ▼
┌──────────────────────────────────────┐
│ Get/Create User in LMS Database      │
│ - Sync nama, email, prodi            │
│ - Set role (mahasiswa/dosen/admin)   │
│ - Mark as SSO user                   │
└──────────┬───────────────────────────┘
           ▼
   ┌───────────────┐
   │ Login Success │
   │ → Dashboard   │
   └───────────────┘
```

### **2. Mapping Data SIAKAD → LMS**

| SIAKAD Field | LMS Field          | Keterangan                    |
|--------------|--------------------|-------------------------------|
| `nim`        | `nim`              | Nomor Induk Mahasiswa         |
| `nip`        | `nip`              | Nomor Induk Pegawai (Dosen)   |
| `nama`       | `name`             | Nama lengkap                  |
| `email`      | `email`            | Email (unique identifier)     |
| `prodi`      | `prodi`            | Program Studi                 |
| `role`       | Spatie Permission  | mahasiswa/dosen/admin         |

### **3. Role Mapping**

```php
SIAKAD Role → LMS Role
├─ "mahasiswa" → Role: mahasiswa
├─ "student"   → Role: mahasiswa
├─ "dosen"     → Role: dosen
├─ "lecturer"  → Role: dosen
├─ "admin"     → Role: admin
└─ "staff"     → Role: admin
```

---

## ⚙️ Konfigurasi

### **Environment Variables (.env)**

```env
# SIAKAD SSO Configuration
SIAKAD_URL=https://siakad.polinema.ac.id
SIAKAD_API_URL=https://siakad.polinema.ac.id/api
SIAKAD_SSO_ENABLED=true
SIAKAD_FALLBACK_LOCAL=true
SIAKAD_TIMEOUT=30
```

### **Services Configuration (config/services.php)**

```php
'siakad' => [
    'url' => env('SIAKAD_URL', 'https://siakad.polinema.ac.id'),
    'api_url' => env('SIAKAD_API_URL', env('SIAKAD_URL') . '/api'),
    'timeout' => env('SIAKAD_TIMEOUT', 30),
    'enabled' => env('SIAKAD_SSO_ENABLED', true),
    'fallback_local' => env('SIAKAD_FALLBACK_LOCAL', true),
],
```

---

## 🛠️ Implementasi Teknis

### **1. Service Layer (SIAKADAuthService.php)**

```php
// Fungsi utama:
- authenticate(username, password)     // Auth ke SIAKAD API
- getOrCreateUser(siakadData)          // Sync user ke LMS
- fallbackAuthenticate(username, pass) // Local auth
- isAvailable()                        // Health check SIAKAD
```

### **2. Controller (SIAKADAuthController.php)**

```php
// Routes:
POST /login/siakad     → SIAKAD SSO Login
GET  /login/standard   → Standard Email Login Form
POST /login/standard   → Standard Email Login Process
```

### **3. Database Schema**

```sql
ALTER TABLE users ADD COLUMN:
- nim VARCHAR(255) NULLABLE UNIQUE        -- Nomor Induk Mahasiswa
- nip VARCHAR(255) NULLABLE UNIQUE        -- Nomor Induk Pegawai
- prodi VARCHAR(255) NULLABLE             -- Program Studi
- is_sso BOOLEAN DEFAULT FALSE            -- Flag SSO user
- last_siakad_sync TIMESTAMP NULLABLE     -- Last sync datetime
```

---

## 📊 Expected API Response from SIAKAD

### **POST /api/login**

**Request:**
```json
{
  "username": "2341760001",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "nim": "2341760001",
    "nama": "John Doe",
    "email": "john@student.polinema.ac.id",
    "prodi": "D4 Teknik Informatika",
    "role": "mahasiswa"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## 🔒 Keamanan

### **1. Password Handling**

- **SSO Users**: Password di-hash random (tidak digunakan untuk login)
- **Local Users**: Password di-hash dengan bcrypt
- **Session**: Menggunakan Laravel session dengan CSRF protection

### **2. Fallback Mechanism**

```php
if (SIAKAD service down) {
    → Try local authentication
    → Allow users dengan existing accounts
    → Show warning message
}
```

### **3. Auto Email Verification**

```php
// SSO users otomatis verified
'email_verified_at' => now()
```

---

## 🧪 Testing

### **1. Test Login SIAKAD (Manual)**

```bash
# 1. Pastikan .env sudah dikonfigurasi
# 2. Akses http://localhost/login
# 3. Gunakan kredensial:
#    - NIM: 2341760001
#    - Password: [password SIAKAD Anda]
```

### **2. Test Fallback Login**

```bash
# 1. Disable SIAKAD (set SIAKAD_SSO_ENABLED=false)
# 2. Akses http://localhost/login/standard
# 3. Login dengan email lokal
```

### **3. Health Check**

```bash
php artisan tinker

>>> $service = app(\App\Services\SIAKADAuthService::class);
>>> $service->isAvailable();
# true = SIAKAD available
# false = SIAKAD down
```

---

## 📝 User Journey

### **Mahasiswa Login Pertama Kali**

```
1. Buka LMS → Redirect ke /login
2. Masukkan NIM: 2341760001
3. Masukkan Password SIAKAD
4. Klik "Login dengan SIAKAD"
5. ✅ Akun otomatis dibuat dari data SIAKAD
6. ✅ Role "mahasiswa" assigned otomatis
7. ✅ Redirect ke Student Dashboard
8. Lihat: Mata kuliah, tugas, jadwal
```

### **Dosen Login Pertama Kali**

```
1. Buka LMS → Redirect ke /login
2. Masukkan NIP: 198001012000
3. Masukkan Password SIAKAD
4. Klik "Login dengan SIAKAD"
5. ✅ Akun otomatis dibuat dari data SIAKAD
6. ✅ Role "dosen" assigned otomatis
7. ✅ Redirect ke Lecturer Dashboard
8. Lihat: Mata kuliah diampu, tugas mahasiswa
```

---

## 🚨 Troubleshooting

### **Problem: "Invalid credentials"**

```
Solusi:
1. Cek username/password SIAKAD di portal langsung
2. Pastikan NIM/NIP format benar (tanpa spasi)
3. Cek SIAKAD_URL di .env sudah benar
```

### **Problem: "SIAKAD service unavailable"**

```
Solusi:
1. Cek koneksi internet
2. Ping ke SIAKAD: curl https://siakad.polinema.ac.id
3. Gunakan fallback: Login dengan Email
```

### **Problem: "Account not syncing"**

```
Solusi:
1. Check logs: storage/logs/laravel.log
2. Verify API response structure
3. Check database users table fields
```

---

## 📂 File Structure

```
app/
├── Services/
│   └── SIAKADAuthService.php          # Core SSO logic
├── Http/Controllers/Auth/
│   └── SIAKADAuthController.php       # SSO controller
└── Models/
    └── User.php                       # Updated with SSO fields

resources/views/auth/
├── login.blade.php                    # SIAKAD SSO login form
└── login-standard.blade.php           # Fallback email login

routes/
└── auth.php                           # SSO routes

database/migrations/
└── 2024_10_29_000001_add_siakad_fields_to_users_table.php

config/
└── services.php                       # SIAKAD config
```

---

## 🎓 Best Practices

1. **Always Enable Fallback**: Set `SIAKAD_FALLBACK_LOCAL=true`
2. **Monitor SIAKAD Uptime**: Check logs for failed authentications
3. **Sync Data Regularly**: Update user profiles on each login
4. **Log SSO Events**: Track all SSO logins for audit
5. **Handle API Changes**: SIAKAD API might change, be flexible

---

## 📞 Support

- **SIAKAD Issues**: Hubungi IT Polinema
- **LMS Issues**: Check logs atau hubungi admin LMS
- **Portal SIAKAD**: https://siakad.polinema.ac.id
- **Portal SPADA**: https://slc.polinema.ac.id/spada/

---

**Last Updated**: October 29, 2025  
**Version**: 1.0.0
