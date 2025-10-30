# 🎓 LMS Cerdas - Alur Login dengan SIAKAD SSO

## 📌 RINGKASAN CEPAT

**LMS Cerdas** menggunakan sistem **Single Sign-On (SSO)** dengan Portal SIAKAD Polinema. Anda bisa login menggunakan:

### ✅ Login dengan SIAKAD (Recommended)
- **URL**: http://localhost/login
- **Username**: NIM (mahasiswa) atau NIP (dosen)
- **Password**: Password SIAKAD Anda
- **Keuntungan**: 
  - ✓ Satu akun untuk SIAKAD & LMS
  - ✓ Data otomatis sinkron
  - ✓ Tidak perlu registrasi manual

### 🔄 Login dengan Email (Fallback)
- **URL**: http://localhost/login/standard
- **Username**: Email terdaftar
- **Password**: Password lokal LMS
- **Digunakan jika**: SIAKAD sedang maintenance

---

## 🔐 ALUR LOGIN VISUAL

```
┌─────────────────────────────────────────────────────────────────┐
│                  HALAMAN LOGIN LMS CERDAS                       │
│                 http://localhost/login                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  📋 Login dengan Akun SIAKAD Polinema                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                 │
│  NIM / NIP / Email                                             │
│  ┌────────────────────────────────────────────────┐            │
│  │ 2341760001                                      │            │
│  └────────────────────────────────────────────────┘            │
│  Contoh: 2341760001 (mahasiswa) atau 198001012000 (dosen)     │
│                                                                 │
│  Password SIAKAD                                               │
│  ┌────────────────────────────────────────────────┐            │
│  │ ••••••••••                                      │            │
│  └────────────────────────────────────────────────┘            │
│                                                                 │
│  ☐ Ingat saya                                                  │
│                                                                 │
│          [  Login dengan SIAKAD  ] ← Klik ini!                │
│                                                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  Tidak bisa login? → Login dengan Email                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚀 PROSES AUTENTIKASI

```
USER                    LMS CERDAS              SIAKAD PORTAL
  │                         │                          │
  │  1. Input NIM/Password  │                          │
  ├────────────────────────>│                          │
  │                         │                          │
  │                         │  2. Cek SIAKAD Available │
  │                         ├─────────────────────────>│
  │                         │                          │
  │                         │  3. POST /api/login      │
  │                         ├─────────────────────────>│
  │                         │     { username, pass }   │
  │                         │                          │
  │                         │  4. Validate Credentials │
  │                         │<─────────────────────────┤
  │                         │     { user data }        │
  │                         │                          │
  │  5. Create/Update User  │                          │
  │     in LMS Database     │                          │
  │<────────────────────────┤                          │
  │                         │                          │
  │  6. Login Success       │                          │
  │     → Dashboard         │                          │
  │<────────────────────────┤                          │
  │                         │                          │
```

### **Jika SIAKAD Down (Fallback)**

```
USER                    LMS CERDAS
  │                         │
  │  1. SIAKAD timeout      │
  ├────────────────────────>│
  │                         │
  │  2. Try Local Database  │
  │<────────────────────────┤
  │                         │
  │  3. If account exists   │
  │     → Login Success     │
  │<────────────────────────┤
```

---

## 👥 CONTOH PENGGUNAAN

### **Mahasiswa Login Pertama Kali**

1. Buka: **http://localhost/login**
2. Input:
   - Username: `2341760001` (NIM Anda)
   - Password: Password SIAKAD Anda
3. Klik: **Login dengan SIAKAD**
4. Sistem akan:
   ```
   ✓ Hubungi SIAKAD API
   ✓ Verifikasi kredensial
   ✓ Ambil data: Nama, Email, Prodi
   ✓ Buat akun LMS otomatis
   ✓ Set role: "mahasiswa"
   ✓ Login ke Student Dashboard
   ```
5. Anda akan melihat:
   - 📚 Mata kuliah yang diikuti
   - 📝 Tugas yang harus dikerjakan
   - 🔔 Notifikasi deadline
   - 🤖 AI Assistant

### **Dosen Login Pertama Kali**

1. Buka: **http://localhost/login**
2. Input:
   - Username: `198001012000` (NIP Anda)
   - Password: Password SIAKAD Anda
3. Klik: **Login dengan SIAKAD**
4. Sistem akan:
   ```
   ✓ Verifikasi ke SIAKAD
   ✓ Buat akun dengan role "dosen"
   ✓ Login ke Lecturer Dashboard
   ```
5. Anda akan melihat:
   - 👨‍🏫 Mata kuliah yang diampu
   - 📄 Tugas yang perlu dinilai
   - 📊 Statistik mahasiswa

---

## 🔧 KONFIGURASI UNTUK ADMIN

### **1. Environment Variables (.env)**

```env
# SIAKAD Integration
SIAKAD_URL=https://siakad.polinema.ac.id
SIAKAD_API_URL=https://siakad.polinema.ac.id/api
SIAKAD_SSO_ENABLED=true          # Enable/disable SSO
SIAKAD_FALLBACK_LOCAL=true       # Allow local login jika SIAKAD down
SIAKAD_TIMEOUT=30                # Timeout request ke SIAKAD (seconds)
```

### **2. Disable SIAKAD SSO (Emergency)**

Jika SIAKAD bermasalah dan Anda ingin force semua user login lokal:

```env
SIAKAD_SSO_ENABLED=false
```

Lalu jalankan:
```bash
php artisan config:cache
```

---

## ❓ FAQ

### **Q: Apa perbedaan login SIAKAD vs login Email?**

| Aspek              | SIAKAD SSO                          | Email Login              |
|--------------------|-------------------------------------|--------------------------|
| **Username**       | NIM/NIP                             | Email                    |
| **Password**       | Password SIAKAD                     | Password lokal LMS       |
| **Registrasi**     | Otomatis (auto-create)              | Manual (harus register)  |
| **Sync Data**      | Otomatis dari SIAKAD                | Manual update            |
| **Availability**   | Butuh SIAKAD online                 | Selalu tersedia          |

### **Q: Apa yang terjadi saat login pertama kali dengan SIAKAD?**

```
1. LMS kirim kredensial ke SIAKAD API
2. SIAKAD validasi dan kirim balik data user
3. LMS buat akun baru dengan data dari SIAKAD:
   - Nama lengkap
   - Email
   - NIM/NIP
   - Program Studi
   - Role (mahasiswa/dosen)
4. User otomatis login ke dashboard
```

### **Q: Bagaimana jika SIAKAD sedang down?**

```
1. LMS coba hubungi SIAKAD (timeout 30 detik)
2. Jika gagal, muncul pesan error
3. User bisa klik "Login dengan Email"
4. Login menggunakan akun lokal (jika sudah pernah login sebelumnya)
```

### **Q: Apakah password SIAKAD disimpan di LMS?**

**TIDAK**. Password SIAKAD hanya dikirim ke SIAKAD API untuk verifikasi, tidak disimpan di database LMS. User SSO memiliki password random di LMS yang tidak digunakan untuk login.

### **Q: Bagaimana cara ganti password?**

- **User SSO**: Ganti password di Portal SIAKAD
- **User lokal**: Ganti password di profile LMS atau klik "Lupa Password"

---

## 🛡️ KEAMANAN

### **Proteksi yang Diterapkan**

1. **HTTPS Required**: Komunikasi ke SIAKAD menggunakan HTTPS
2. **CSRF Protection**: Setiap form login dilindungi CSRF token
3. **Session Security**: Session dienkripsi dan regenerated setelah login
4. **Password Hashing**: Password lokal di-hash dengan bcrypt
5. **Rate Limiting**: Prevent brute force (max 5 attempts/minute)

### **Data yang Disimpan**

```sql
users table:
├─ name               (dari SIAKAD)
├─ email              (dari SIAKAD)
├─ nim                (dari SIAKAD)
├─ nip                (dari SIAKAD)
├─ prodi              (dari SIAKAD)
├─ password           (random hash untuk SSO users)
├─ is_sso             (true/false)
└─ last_siakad_sync   (timestamp)
```

---

## 📞 BANTUAN

### **Login Bermasalah?**

1. **Cek kredensial**: Pastikan NIM/NIP dan password benar
2. **Coba Portal SIAKAD**: Login di https://siakad.polinema.ac.id
3. **Gunakan fallback**: Klik "Login dengan Email"
4. **Hubungi admin**: Jika masih gagal

### **Lupa Password?**

- **SSO User**: Reset di Portal SIAKAD
- **Local User**: Klik "Lupa Password" di halaman login

### **Contact Support**

- **IT Polinema**: Untuk masalah SIAKAD
- **Admin LMS**: Untuk masalah LMS Cerdas
- **Email**: support@polinema.ac.id

---

## 📚 DOKUMENTASI LENGKAP

- **SIAKAD SSO Technical**: [docs/SIAKAD_SSO_LOGIN.md](SIAKAD_SSO_LOGIN.md)
- **User Guide**: [docs/USER_GUIDE.md](USER_GUIDE.md)
- **Admin Guide**: [docs/ADMIN_GUIDE.md](ADMIN_GUIDE.md)

---

**Dibuat**: 29 Oktober 2025  
**Versi**: 1.0.0  
**Status**: ✅ Production Ready
