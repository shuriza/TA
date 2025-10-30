# 🎯 Setup Progress - LMS Cerdas

## ✅ Yang Sudah Dikerjakan (Fase Inisialisasi)

### 1. ✅ Environment & Dependencies Setup
- [x] Laravel 12 terinstall dengan sukses (v12.36.0)
- [x] Timezone diset ke **Asia/Jakarta**
- [x] Locale diubah ke **id** (Indonesia)
- [x] Database configuration untuk **MySQL** (updated dari PostgreSQL)
- [x] Redis setup untuk **Queue & Cache**
- [x] Dependencies terinstall:
  - ✅ predis/predis (Redis client)
  - ✅ guzzlehttp/guzzle (HTTP client untuk API calls)
  - ✅ laravel/sanctum (API authentication)
  - ✅ telegram-bot/api (Telegram integration)
  - ✅ laravel/scout + meilisearch (Search engine)
  - ✅ spatie/laravel-permission (Role & Permission)
  - ✅ laravel/breeze (Authentication scaffolding)

### 2. ✅ Struktur Direktori Project
```
app/
├── Actions/
│   ├── Ai/ ✅         (ParseTask, PlanDay, DetectNewAssignment, GenerateMetadata)
│   └── Sync/ ✅       (SyncAssignments, SyncMaterials, MapCourse, DeduplicateData)
├── Services/
│   ├── Ai/ ✅         (OpenRouterClient, TaskExtractor, ReminderGenerator)
│   ├── Lms/ ✅        (MoodleConnector, GoogleClassroomConnector, CanvasConnector)
│   └── Notification/ ✅ (TelegramService, EmailService, WhatsAppService)
├── Support/ ✅        (Prioritizer, DateHelper, FileHasher, SearchHelper)
└── Http/Controllers/Api/ ✅
```

### 3. ✅ Database Schema Design
Migration files telah dibuat untuk:
- [x] `add_lms_fields_to_users_table` - Extend users dengan role, nim, nip, timezone, notify_channel, telegram_chat_id, provider_tokens
- [x] `create_courses_table` - Mata kuliah (code, name, lecturer, semester, class)
- [x] `create_materials_table` - Materi pembelajaran
- [x] `create_assignments_table` - Tugas (title, desc, due_at, status, priority, effort, impact, tag)
- [x] `create_submissions_table` - Pengumpulan tugas
- [x] `create_files_table` - File storage dengan versioning & checksum
- [x] `create_reminders_table` - Sistem reminder otomatis
- [x] `create_lms_maps_table` - Mapping LMS eksternal ke lokal
- [x] `create_activities_table` - Audit log

### 4. ✅ Configuration Files
- [x] `config/services.php` - Konfigurasi untuk:
  - OpenRouter AI (DeepSeek R1)
  - Telegram Bot
  - WhatsApp Cloud API
  - Moodle, Google Classroom, Canvas connectors
  - Meilisearch
  - Firebase Cloud Messaging

### 5. ✅ Dokumentasi Project
- [x] `README_PROJECT.md` - Dokumentasi lengkap project (arsitektur, fitur, tech stack, roadmap)
- [x] `DEVELOPMENT.md` - Development guide (migration, factory, testing, deployment)
- [x] Folder README untuk setiap komponen (Services/Ai, Actions, Support, dll)

---

## 🚧 Yang Perlu Dikerjakan Selanjutnya

### Langkah Berikutnya (Prioritas Tinggi)

#### 1. Lengkapi Migration Files
**File yang perlu diisi:**
```bash
database/migrations/
├── 2025_10_29_090913_create_materials_table.php      # ❌ Belum diisi
├── 2025_10_29_090913_create_submissions_table.php    # ❌ Belum diisi
├── 2025_10_29_090914_create_files_table.php          # ❌ Belum diisi
├── 2025_10_29_090914_create_reminders_table.php      # ❌ Belum diisi
├── 2025_10_29_090914_create_lms_maps_table.php       # ❌ Belum diisi
└── 2025_10_29_090918_create_activities_table.php     # ❌ Belum diisi
```

**Action Required:**
```bash
# Edit setiap file migration di atas sesuai ERD slide 9
# Contoh sudah ada di: 
# - add_lms_fields_to_users_table.php
# - create_courses_table.php
# - create_assignments_table.php
```

#### 2. Generate Models + Relations
```bash
# Buat models untuk setiap tabel
php artisan make:model Course -f
php artisan make:model Material -f
php artisan make:model Assignment -f
php artisan make:model Submission -f
php artisan make:model File -f
php artisan make:model Reminder -f
php artisan make:model LmsMap -f
php artisan make:model Activity -f
```

**Tambahkan relasi di setiap model:**
- User → hasMany courses, assignments, submissions, reminders, activities
- Course → hasMany materials, assignments
- Assignment → hasMany submissions, reminders, files
- Submission → belongsTo assignment, user; hasMany files

#### 3. Setup MySQL Database
```bash
# Buat database via MySQL client
mysql -u root -p
CREATE DATABASE lms_cerdas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Atau via phpMyAdmin/HeidiSQL/MySQL Workbench

# Update .env dengan credentials yang benar
DB_PASSWORD=your_actual_password

# Run migrations
php artisan migrate
```

#### 4. Build Core Services

**A. OpenRouter Client** (`app/Services/Ai/OpenRouterClient.php`)
```php
<?php

namespace App\Services\Ai;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OpenRouterClient
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url');
        $this->model = config('services.openrouter.model');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => config('services.openrouter.timeout', 30),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('services.openrouter.app_url'),
                'X-Title' => config('services.openrouter.app_name'),
            ],
        ]);
    }

    public function chat(array $messages, ?string $model = null): array
    {
        try {
            $response = $this->client->post('/chat/completions', [
                'json' => [
                    'model' => $model ?? $this->model,
                    'messages' => $messages,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::error('OpenRouter API Error', [
                'error' => $e->getMessage(),
                'messages' => $messages,
            ]);
            
            throw $e;
        }
    }
}
```

**B. Prioritizer Utility** (`app/Support/Prioritizer.php`)
```php
<?php

namespace App\Support;

use Carbon\Carbon;

class Prioritizer
{
    /**
     * Calculate priority score (0-100)
     * Formula: 0.6*urgency + 0.6*impact - 0.2*effortCapped
     */
    public static function score(
        ?Carbon $due = null,
        ?int $impact = null,
        ?int $effortMins = null
    ): int {
        $urgency = self::calculateUrgency($due);
        $impactScore = $impact ?? 50;
        $effortCapped = min(120, $effortMins ?? 30);
        
        $priority = (0.6 * $urgency) + (0.6 * $impactScore) - (0.2 * $effortCapped);
        
        return (int) max(0, min(100, $priority));
    }

    private static function calculateUrgency(?Carbon $due): int
    {
        if (!$due) {
            return 20; // Default untuk task tanpa deadline
        }
        
        $diffHours = now()->diffInHours($due, false);
        
        // Sudah lewat deadline = urgency 100
        if ($diffHours < 0) {
            return 100;
        }
        
        // Semakin dekat deadline, semakin urgent
        return (int) max(0, 100 - $diffHours);
    }
}
```

#### 5. Create Seeders untuk Demo Data
```bash
php artisan make:seeder RolePermissionSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder CourseSeeder
php artisan make:seeder AssignmentSeeder
php artisan make:seeder DemoSeeder
```

**Isi DatabaseSeeder.php:**
```php
public function run(): void
{
    $this->call([
        RolePermissionSeeder::class,
        UserSeeder::class,
        CourseSeeder::class,
        AssignmentSeeder::class,
    ]);
}
```

#### 6. Build API Endpoints (MVP)
```bash
# Buat API controllers
php artisan make:controller Api/CourseController --api
php artisan make:controller Api/MaterialController --api
php artisan make:controller Api/AssignmentController --api
php artisan make:controller Api/SubmissionController --api
php artisan make:controller Api/AiController
```

**routes/api.php:**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Courses
    Route::apiResource('courses', CourseController::class);
    
    // Materials
    Route::apiResource('materials', MaterialController::class);
    
    // Assignments
    Route::apiResource('assignments', AssignmentController::class);
    Route::get('assignments/filter/{type}', [AssignmentController::class, 'filter']); // today, week, overdue
    
    // Submissions
    Route::apiResource('submissions', SubmissionController::class);
    
    // AI Features
    Route::post('ai/parse-task', [AiController::class, 'parseTask']);
    Route::post('ai/plan-day', [AiController::class, 'planDay']);
});
```

---

## 📋 Roadmap 6 Minggu (Adjusted)

### Minggu 1 (Current) - Foundation ✅
- [x] Setup Laravel 12 + dependencies
- [x] Database schema design
- [x] Folder structure
- [x] Documentation
- [ ] **TODO**: Lengkapi semua migrations
- [ ] **TODO**: Generate models + relations
- [ ] **TODO**: Setup PostgreSQL & run migrate

### Minggu 2 - Core Features (Mahasiswa)
- [ ] Auth system (Breeze + roles)
- [ ] CRUD Courses & Materials
- [ ] CRUD Assignments
- [ ] Dashboard mahasiswa (list tugas, filter)
- [ ] File upload system (versioning + checksum)

### Minggu 3 - LMS Integration (SPADA Polinema)
- [ ] SPADA Polinema connector (via SIAKAD auth)
  - URL: https://slc.polinema.ac.id/spada/
  - Login via: https://siakad.polinema.ac.id/beranda
- [ ] Sync worker (normalize, map, dedup)
- [ ] Webhook handler (if supported)
- [ ] LMS mapping table logic
- [ ] Test sync dari SPADA dengan credentials mahasiswa

### Minggu 4 - Notification & Reminder
- [ ] Telegram bot integration
- [ ] Email notification
- [ ] Reminder scheduler (H-7/H-3/H-1/H-0)
- [ ] Queue system setup (Redis)
- [ ] Notification preferences UI

### Minggu 5 - AI Assistant & Search
- [ ] OpenRouter client implementation
- [ ] ParseTask action (extract metadata dari teks)
- [ ] Auto-detect new assignments
- [ ] Meilisearch integration (keyword search)
- [ ] Semantic search (optional)

### Minggu 6 - Testing & Polish
- [ ] Feature tests untuk API
- [ ] Integration tests (LMS sync)
- [ ] UI/UX improvements
- [ ] Performance optimization
- [ ] Documentation finalization
- [ ] Demo preparation

---

## 🎯 Next Immediate Steps (Hari Ini)

1. **Lengkapi migrations yang masih kosong** (30 menit)
   ```bash
   # Edit files:
   # - create_materials_table.php
   # - create_submissions_table.php
   # - create_files_table.php
   # - create_reminders_table.php
   # - create_lms_maps_table.php
   # - create_activities_table.php
   ```

2. **Setup PostgreSQL database** (10 menit)
   ```bash
   createdb lms_cerdas
   # Update .env dengan password yang benar
   php artisan migrate
   ```

3. **Generate models** (20 menit)
   ```bash
   php artisan make:model Course -f
   php artisan make:model Material -f
   php artisan make:model Assignment -f
   php artisan make:model Submission -f
   # dst...
   
   # Tambahkan relations di masing-masing model
   ```

4. **Buat seeders untuk demo data** (30 menit)
   ```bash
   php artisan make:seeder RolePermissionSeeder
   php artisan make:seeder UserSeeder
   php artisan make:seeder CourseSeeder
   
   php artisan db:seed
   ```

5. **Build OpenRouter client & Prioritizer** (45 menit)
   - Implement `app/Services/Ai/OpenRouterClient.php`
   - Implement `app/Support/Prioritizer.php`
   - Test dengan dummy data

---

## 📞 Support & Resources

**Dokumentasi yang Tersedia:**
- `README_PROJECT.md` - Overview project lengkap
- `DEVELOPMENT.md` - Development guide
- Folder-specific READMEs di `app/Services/*`, `app/Actions/*`

**Environment Variables yang Perlu Diisi:**
```env
# Database
DB_PASSWORD=your_postgres_password

# OpenRouter AI
OPENROUTER_API_KEY=your_openrouter_key

# Telegram (untuk testing)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username
```

**Tools yang Dibutuhkan:**
- MySQL 8.0+ (install via [mysql.com](https://dev.mysql.com/downloads/installer/) atau XAMPP/Laragon)
- Redis (install via [redis.io](https://redis.io/download) atau gunakan Docker)
- Telegram Bot (buat via [@BotFather](https://t.me/BotFather))
- OpenRouter API Key (daftar di [openrouter.ai](https://openrouter.ai))
- **Credentials SIAKAD Polinema** untuk testing sync SPADA

---

## ✅ Checklist Fase 1 (Foundation)

- [x] Laravel 12 installed
- [x] Dependencies installed (Sanctum, Scout, Permission, Telegram, Guzzle)
- [x] Timezone & locale configured
- [x] Folder structure created
- [x] Migration files created
- [x] Config files updated
- [x] Documentation written
- [ ] **Migrations completed** ⬅️ **CURRENT**
- [ ] **Database migrated**
- [ ] **Models generated**
- [ ] **Basic seeders**
- [ ] **First API endpoint working**

---

**Status: Foundation 70% Complete** 🚀

**Next Focus: Complete migrations → Run migrate → Generate models → Build first API**

---

Semoga progress ini membantu! Jika ada yang ingin dilanjutkan atau butuh bantuan dengan step tertentu, silakan beritahu saya! 💪
