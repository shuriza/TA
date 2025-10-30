# 🎓 LMS Cerdas - Smart Learning Management System

> **Rancang Bangun LMS Cerdas Berbasis Laravel**: Materi • Tugas • Pengumpulan • Pengingat (Sinkron Otomatis dari LMS Kampus)

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql)](https://postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)](https://redis.io)

## 📋 Deskripsi

LMS Cerdas adalah aplikasi Learning Management System yang dirancang khusus untuk mahasiswa dengan fitur:

- ✅ **Auto-Sync** dari LMS kampus (Moodle/Google Classroom/Canvas)
- 🤖 **AI Assistant** untuk deteksi tugas & metadata otomatis
- ⏰ **Smart Reminders** (H-7/H-3/H-1/H-0) via Telegram/Email
- 📚 **Materi & Tugas** terorganisir per mata kuliah
- 📤 **Sistem Pengumpulan** dengan file versioning & checksum
- 🔍 **Pencarian Cerdas** (BM25 + Semantic Search)

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                     Client UI (Web/Mobile)                   │
│                  (Mahasiswa / Dosen / Admin)                 │
└───────────────────────────┬─────────────────────────────────┘
                            │
        ┌───────────────────┴───────────────────┐
        │       Laravel 12 Backend API          │
        ├───────────────────────────────────────┤
        │  • Materi & Tugas Management          │
        │  • Pengumpulan (Submissions)          │
        │  • Reminder & Notifikasi              │
        │  • AI Assistant (Extract & Plan)      │
        │  • LMS Sync Workers                   │
        └───────────────────┬───────────────────┘
                            │
    ┌───────────────────────┴───────────────────────┐
    │              Infrastructure Layer             │
    ├───────────────────────────────────────────────┤
    │  PostgreSQL  │  Redis  │  Meilisearch  │  S3  │
    │   (Database) │ (Queue) │   (Search)    │(File)│
    └───────────────────────────────────────────────┘
```

### Integrasi LMS

```
[LMS Kampus]  →  [Connector]  →  [Sync Worker]  →  [App]
  (Moodle/         (API/           (Normalize,      (Display)
  Classroom/       Webhook)         Map, Dedup)
  Canvas)
```

---

## 📊 Database Schema (ERD)

Tabel utama:

- **users** - Data mahasiswa/dosen (dengan OAuth tokens)
- **courses** - Mata kuliah (kode, nama, semester, dosen)
- **materials** - Materi pembelajaran (file/link per MK)
- **assignments** - Tugas (title, due_at, priority, effort, impact)
- **submissions** - Pengumpulan tugas (file, timestamp, grade)
- **files** - File storage (versioning, checksum)
- **reminders** - Pengingat otomatis (H-7/H-3/H-1/H-0)
- **lms_maps** - Mapping LMS eksternal ke lokal
- **activities** - Audit log semua aktivitas

---

## 🚀 Tech Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis 7
- **Search**: Meilisearch
- **File Storage**: S3/Cloudflare R2

### AI & Integration
- **AI Model**: DeepSeek R1 (via OpenRouter)
- **LMS Integration**: SPADA Polinema (Moodle-based)
  - URL: https://slc.polinema.ac.id/spada/
  - Auth via: https://siakad.polinema.ac.id/beranda
- **Notifications**: Telegram Bot API, Email, WhatsApp Cloud API

### Frontend
- **Template**: Blade (Laravel Breeze)
- **CSS**: Tailwind CSS
- **JS**: Alpine.js (minimal)
- **Build**: Vite

---

## 📦 Installation

### Prerequisites
```bash
- PHP >= 8.2
- Composer
- MySQL >= 8.0 (atau MariaDB >= 10.6)
- Redis >= 6
- Node.js >= 18
- npm/yarn
```

### Setup Steps

1. **Clone & Install Dependencies**
```bash
git clone <repository-url> lms-cerdas
cd lms-cerdas
composer install
npm install
```

2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="LMS Cerdas"
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_DATABASE=lms_cerdas
DB_USERNAME=root
DB_PASSWORD=your_password

QUEUE_CONNECTION=redis
CACHE_STORE=redis

OPENROUTER_API_KEY=your_api_key
AI_MODEL=deepseek/deepseek-r1:free

TELEGRAM_BOT_TOKEN=your_bot_token

# SPADA Polinema Integration
SPADA_URL=https://slc.polinema.ac.id/spada/
SIAKAD_URL=https://siakad.polinema.ac.id/beranda
```

3. **Database Migration**
```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE lms_cerdas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate
php artisan db:seed  # Optional: seed demo data
```

4. **Build Assets**
```bash
npm run build
```

5. **Run Application**
```bash
# Terminal 1: Web server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Terminal 3: Scheduler
php artisan schedule:work
```

---

## 🔧 Configuration

### Timezone
Semua datetime menggunakan **Asia/Jakarta** timezone.
```php
// config/app.php
'timezone' => 'Asia/Jakarta',
```

### Queue & Scheduler
```bash
# Queue untuk background jobs (reminder, sync, notif)
php artisan queue:work --queue=high,default,low

# Scheduler untuk cron jobs
php artisan schedule:work
```

### Telegram Bot Setup
1. Buat bot via [@BotFather](https://t.me/BotFather)
2. Dapatkan token dan username
3. Set di `.env`:
   ```env
   TELEGRAM_BOT_TOKEN=123456:ABC-DEF...
   TELEGRAM_BOT_USERNAME=YourBotUsername
   ```

---

## 📁 Project Structure

```
app/
├── Actions/
│   ├── Ai/              # AI-related actions (ParseTask, PlanDay)
│   └── Sync/            # LMS sync workers
├── Http/
│   └── Controllers/
│       └── Api/         # API endpoints
├── Models/              # Eloquent models
├── Services/
│   ├── Ai/              # OpenRouter client, AI services
│   ├── Lms/             # Moodle, Google Classroom connectors
│   └── Notification/    # Telegram, Email, WhatsApp services
└── Support/             # Utilities (Prioritizer, FileHasher, etc)

database/
├── migrations/          # Database migrations
├── factories/           # Model factories
└── seeders/             # Database seeders

resources/
├── views/               # Blade templates
└── js/                  # Frontend assets

routes/
├── web.php              # Web routes
├── api.php              # API routes (Sanctum protected)
└── console.php          # Artisan commands
```

---

## 🎯 Fitur Utama

### 1. Mahasiswa
- ✅ Dashboard dengan quick-add tugas
- ✅ Lihat daftar materi per MK (file/link + ringkasan)
- ✅ Tugas auto-sync dari LMS + filter (MK/status/prioritas)
- ✅ Upload pengumpulan (file versioning + checksum)
- ✅ Notifikasi pengingat H-7/H-3/H-1/H-0 (08:00 WIB)
- ✅ Pencarian materi (keyword + semantic)

### 2. Dosen
- ✅ Kelola materi & tugas per kelas
- ✅ Lihat status pengumpulan mahasiswa
- ✅ Beri catatan & nilai
- ✅ Export rekap (.csv)

### 3. AI Assistant
- ✅ Parse teks bebas → structured task
- ✅ Auto-detect tugas baru dari sync
- ✅ Generate metadata (priority, effort, impact)
- ✅ Buat reminder otomatis

---

## 🔔 Reminder System

Reminder otomatis berdasarkan due date:

| Waktu | Channel | Deskripsi |
|-------|---------|-----------|
| H-7   | Database + Email | Peringatan awal |
| H-3   | Database + Telegram | Urgent reminder |
| H-1   | Telegram + Email | Last call |
| H-0   | Telegram (08:00) | Deadline hari ini |

Adaptif: Tugas high-priority dapat tambah H-10/H-2.

---

## 🔐 Security

- ✅ OAuth 2.0 untuk integrasi LMS (least-privilege)
- ✅ Token encrypted di database
- ✅ Tidak simpan password LMS
- ✅ CSRF protection (web) + Sanctum (API)
- ✅ File validation (MIME, size, checksum)
- ✅ Audit log semua aktivitas
- ✅ Rate limiting pada AI endpoints (30/min)

---

## 📈 Evaluasi & Target

| Metrik | Target | Keterangan |
|--------|--------|------------|
| **Akurasi deteksi tugas** | Precision/Recall ≥ 0.90 | Auto-detect dari LMS |
| **Latensi sinkronisasi** | ≤ 60 detik (webhook) / ≤ 5 menit (polling) | Real-time sync |
| **Delivery reminder** | ≥ 99% | Reliabilitas notif |
| **SUS Score** | ≥ 75 | Kepuasan pengguna |
| **Search nDCG@10** | Naik ≥ 15% | Hybrid vs keyword-only |

---

## 📅 Roadmap Implementasi (6 Minggu)

| Minggu | Deliverables |
|--------|--------------|
| **M1** | ERD, auth, courses & materials; integrasi Telegram |
| **M2** | Assignments & submissions; file versioning + bukti submit |
| **M3** | Connector (Moodle/Email Parser) + mapping |
| **M4** | Scheduler & queue; notifikasi; Meilisearch (keyword) |
| **M5** | AI assistant (extract & plan) + semantik dasar |
| **M6** | Testing, optimasi, dokumentasi & demo |

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific feature
php artisan test --filter=TaskApiTest

# Run with coverage
php artisan test --coverage
```

---

## 🔨 Development

### Artisan Commands
```bash
# Generate daily plan (runs at 05:30)
php artisan plan:generate --today

# Sync from LMS
php artisan lms:sync moodle
php artisan lms:sync classroom

# Send pending reminders
php artisan reminder:send

# Reset demo data
php artisan demo:reset
```

### API Documentation
OpenAPI spec tersedia di `openapi.yaml` (auto-generated).

---

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Author

**NAMA LENGKAP** - NIM  
D-III Manajemen Informatika  
PSDKU Nama Kampus  
Tahun 2025

---

## 🙏 Acknowledgments

- Laravel Framework
- OpenRouter & DeepSeek
- Spatie Packages
- Laravel Scout & Meilisearch
- Telegram Bot PHP SDK

---

## 📞 Support

Untuk pertanyaan atau bantuan:
- Email: your.email@example.com
- Telegram: @yourusername
- Issues: [GitHub Issues](https://github.com/yourusername/lms-cerdas/issues)

---

**Built with ❤️ using Laravel**
