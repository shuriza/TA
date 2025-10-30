# 🔐 Cara Akses Login SIAKAD

## 📍 **URL LOGIN**

### **Halaman Login Utama (SIAKAD SSO):**
```
http://localhost:8000/login
```

### **Halaman Login Email (Fallback):**
```
http://localhost:8000/login/standard
```

---

## 🎯 **LANGKAH-LANGKAH LOGIN**

### **Opsi 1: Login dengan SIAKAD (Recommended)**

1. **Buka browser** (Chrome/Firefox/Edge)

2. **Akses URL:**
   ```
   http://localhost:8000/login
   ```

3. **Isi form login:**
   ```
   Username: [NIM/NIP Anda]
   Password: [Password SIAKAD Anda]
   ```

4. **Klik tombol:**
   ```
   "Login dengan SIAKAD"
   ```

5. **Redirect otomatis** ke dashboard sesuai role:
   - Mahasiswa → Student Dashboard
   - Dosen → Lecturer Dashboard
   - Admin → Admin Dashboard

---

### **Opsi 2: Login dengan Email (Jika SIAKAD Down)**

1. **Klik link:**
   ```
   "Login dengan Email"
   ```
   (Ada di bagian bawah form login)

2. **Atau langsung buka:**
   ```
   http://localhost:8000/login/standard
   ```

3. **Isi form:**
   ```
   Email: [Email terdaftar]
   Password: [Password lokal LMS]
   ```

4. **Klik:**
   ```
   "Login"
   ```

---

## 👤 **TEST ACCOUNTS**

### **Mahasiswa (Student):**
```
Username: 2341760001
Password: password
Email: mahasiswa1@student.polinema.ac.id
```

### **Dosen (Lecturer):**
```
Username: 198001012000
Password: password  
Email: dosen1@polinema.ac.id
```

### **Admin:**
```
Username: admin
Password: password
Email: admin@polinema.ac.id
```

---

## 🖥️ **TAMPILAN HALAMAN LOGIN**

Halaman login sekarang menampilkan:

```
┌─────────────────────────────────────────────────────┐
│  📘 Login dengan Akun SIAKAD                        │
│  Gunakan NIM/NIP dan password SIAKAD Polinema       │
│  Portal SIAKAD Polinema →                           │
├─────────────────────────────────────────────────────┤
│                                                     │
│  NIM / NIP / Email                                  │
│  ┌──────────────────────────────────────────────┐  │
│  │ Masukkan NIM, NIP, atau Email                │  │
│  └──────────────────────────────────────────────┘  │
│  Contoh: 2341760001 (mahasiswa) atau             │
│         198001012000 (dosen)                      │
│                                                     │
│  Password SIAKAD                                    │
│  ┌──────────────────────────────────────────────┐  │
│  │ ••••••••                                      │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│  ☐ Ingat saya                                      │
│                                                     │
│          [  Login dengan SIAKAD  ]                 │
│                                                     │
├─────────────────────────────────────────────────────┤
│  Tidak bisa login dengan SIAKAD?                   │
│  → Login dengan Email                              │
└─────────────────────────────────────────────────────┘
```

---

## 🔄 **ALUR SETELAH LOGIN**

### **Mahasiswa:**
```
Login → Student Dashboard
        ├─ Tugas Urgent (H-3)
        ├─ Tugas Upcoming (H-7)
        ├─ Mata Kuliah
        └─ Recent Submissions
```

### **Dosen:**
```
Login → Lecturer Dashboard
        ├─ Statistics (Courses, Students, Assignments)
        ├─ Pending Grading
        ├─ Courses Taught
        └─ Recent Assignments
```

### **Admin:**
```
Login → Admin Dashboard
        ├─ System Statistics
        ├─ User Management
        ├─ Course Management
        └─ System Info
```

---

## 🧪 **TESTING**

### **Test 1: Akses Login Page**
```bash
# Buka browser
http://localhost:8000/login

# Expected: Form login dengan notice SIAKAD
```

### **Test 2: Login dengan Test Account**
```bash
# Input
Username: mahasiswa1@student.polinema.ac.id
Password: password

# Expected: Redirect ke student dashboard
```

### **Test 3: SIAKAD SSO (Jika punya akun SIAKAD)**
```bash
# Input
Username: [NIM Anda]
Password: [Password SIAKAD Anda]

# Expected: 
# - Extract data dari SIAKAD
# - Create/update account
# - Login success
```

---

## 📱 **ROUTES YANG TERSEDIA**

```
GET  /login              → Form login SIAKAD
POST /login/siakad       → Process SIAKAD login
GET  /login/standard     → Form login email
POST /login/standard     → Process email login
POST /logout             → Logout
GET  /register           → Register (optional)
GET  /forgot-password    → Reset password
```

---

## 🚨 **TROUBLESHOOTING**

### **Problem: Halaman tidak muncul**
```bash
# Check server
php artisan serve --port=8000

# Akses
http://localhost:8000/login
```

### **Problem: Route not found**
```bash
# Clear cache
php artisan route:clear
php artisan config:clear

# List routes
php artisan route:list --name=login
```

### **Problem: Login gagal**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> User::all()
```

---

## 🎨 **CUSTOMIZATION**

### **Ganti Logo/Branding:**
Edit file: `resources/views/layouts/guest.blade.php`

### **Ganti Warna:**
Edit file: `resources/views/auth/login.blade.php`

### **Ganti Text:**
Edit file: `resources/views/auth/login.blade.php`

---

## ✅ **CHECKLIST**

- [x] Server running (port 8000)
- [x] Routes registered
- [x] Login page accessible
- [x] SIAKAD form displayed
- [x] Fallback login available
- [ ] Test dengan real credentials
- [ ] Verify extraction works

---

**Server Status:** ✅ Running  
**URL:** http://localhost:8000/login  
**Ready:** ✅ Yes

**Buka browser dan akses:** http://localhost:8000/login
