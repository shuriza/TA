# 🎓 Integrasi SPADA Polinema - LMS Cerdas

Dokumentasi lengkap untuk integrasi dengan SPADA Polinema.

---

## 📋 Informasi SPADA

- **SPADA URL**: https://slc.polinema.ac.id/spada/
- **SIAKAD URL**: https://siakad.polinema.ac.id/beranda
- **Platform**: Moodle-based LMS
- **Authentication**: Via SIAKAD (Single Sign-On)

---

## 🔐 Alur Autentikasi

### Flow Diagram:
```
┌──────────┐      ┌──────────┐      ┌──────────┐
│   User   │─────▶│  SIAKAD  │─────▶│  SPADA   │
└──────────┘      └──────────┘      └──────────┘
                       │                  │
                       │ Login Success    │
                       ├─────────────────▶│
                       │ Session Cookie   │
                       │                  │
                       │◀─────────────────┤
                       │ Access Granted   │
```

### Step-by-Step:
1. **Login ke SIAKAD**
   - URL: `https://siakad.polinema.ac.id/beranda/login`
   - POST credentials (username & password)
   - Terima session cookie dari SIAKAD

2. **Redirect ke SPADA**
   - SIAKAD redirect otomatis ke SPADA
   - Session cookie dibawa ke SPADA
   - SPADA verifikasi session

3. **Akses SPADA Resources**
   - Gunakan session cookie untuk semua request
   - Session valid ~6 jam (bisa berbeda)

---

## 🔧 Implementasi

### 1. Environment Setup

File `.env`:
```env
# SPADA Polinema
SPADA_URL=https://slc.polinema.ac.id/spada/
SIAKAD_URL=https://siakad.polinema.ac.id/beranda
SPADA_SESSION_COOKIE=
SPADA_CSRF_TOKEN=
```

File `config/services.php`:
```php
'spada' => [
    'url' => env('SPADA_URL', 'https://slc.polinema.ac.id/spada/'),
    'siakad_url' => env('SIAKAD_URL', 'https://siakad.polinema.ac.id/beranda'),
    'session_cookie' => env('SPADA_SESSION_COOKIE'),
    'csrf_token' => env('SPADA_CSRF_TOKEN'),
],
```

### 2. SpadaConnector Usage

```php
use App\Services\Lms\SpadaConnector;

// Inisialisasi
$spada = new SpadaConnector();

// Login (satu kali, session akan di-cache)
$success = $spada->authenticate(
    username: '2341760xxx',  // NIM mahasiswa
    password: 'password123'
);

if ($success) {
    // Get courses
    $courses = $spada->getCourses();
    
    foreach ($courses as $course) {
        echo "Course: {$course['name']}\n";
        
        // Get assignments per course
        $assignments = $spada->getAssignments($course['external_id']);
        
        foreach ($assignments as $assignment) {
            echo "  - {$assignment['title']} (Due: {$assignment['due_at']})\n";
        }
        
        // Get materials
        $materials = $spada->getMaterials($course['external_id']);
    }
}
```

### 3. Check Authentication Status

```php
if ($spada->isAuthenticated()) {
    // Session masih valid, langsung fetch data
    $courses = $spada->getCourses();
} else {
    // Session expired, perlu login ulang
    $spada->authenticate($username, $password);
}
```

---

## 🛠️ SPADA API Endpoints

SPADA menggunakan Moodle Web Services. Berikut endpoint yang tersedia:

### Get User Courses
```
GET /webservice/rest/server.php
?wstoken={token}
&wsfunction=core_enrol_get_users_courses
&moodlewsrestformat=json
&userid={user_id}
```

### Get Course Contents
```
GET /webservice/rest/server.php
?wstoken={token}
&wsfunction=core_course_get_contents
&moodlewsrestformat=json
&courseid={course_id}
```

### Get Assignments
```
GET /webservice/rest/server.php
?wstoken={token}
&wsfunction=mod_assign_get_assignments
&moodlewsrestformat=json
&courseids[0]={course_id}
```

### Get Assignment Submissions
```
GET /webservice/rest/server.php
?wstoken={token}
&wsfunction=mod_assign_get_submissions
&moodlewsrestformat=json
&assignmentids[0]={assignment_id}
```

---

## 📝 Data Mapping

### Courses Mapping
```php
SPADA Course → Local Course
├── id → external_id
├── idnumber/shortname → code
├── fullname → name
├── summary → description
└── [extracted] → semester
```

### Assignments Mapping
```php
SPADA Assignment → Local Assignment
├── id → external_id
├── cmid → course_module_id
├── name → title
├── intro → description
├── duedate → due_at (timestamp → Carbon)
├── cutoffdate → allow_late_submission (exists = false)
└── [generated URL] → lms_url
```

### Materials Mapping
```php
SPADA Module → Local Material
├── id → external_id
├── name → title
├── modname → type (resource/url/page/folder)
├── url → url
└── description → description
```

---

## 🔄 Sync Strategy

### 1. Initial Sync (First Time)
```php
use App\Actions\Sync\SyncCourses;
use App\Actions\Sync\SyncAssignments;
use App\Actions\Sync\SyncMaterials;

// Sync courses
app(SyncCourses::class)->handle($user, $spada);

// Sync assignments & materials
foreach ($user->courses as $course) {
    app(SyncAssignments::class)->handle($course, $spada);
    app(SyncMaterials::class)->handle($course, $spada);
}
```

### 2. Incremental Sync (Scheduled)
```php
// Di Console/Kernel.php
$schedule->call(function () {
    $users = User::where('role', 'mahasiswa')->get();
    
    foreach ($users as $user) {
        SyncFromSpada::dispatch($user);
    }
})->everyFiveMinutes();
```

### 3. Webhook Sync (If Supported)
```php
// routes/api.php
Route::post('/webhook/spada', [WebhookController::class, 'spada'])
    ->middleware('verify.webhook.signature');

// WebhookController
public function spada(Request $request)
{
    $event = $request->input('event'); // assignment.created, assignment.updated
    $data = $request->input('data');
    
    match($event) {
        'assignment.created' => $this->handleNewAssignment($data),
        'assignment.updated' => $this->handleUpdatedAssignment($data),
        default => null,
    };
}
```

---

## 🔍 Web Scraping (Fallback)

Jika Moodle API tidak tersedia atau token tidak bisa didapat, gunakan web scraping:

### 1. Install Simple HTML DOM
```bash
composer require simplehtmldom/simplehtmldom
```

### 2. Scrape Courses
```php
use simplehtmldom\HtmlWeb;

$client = new HtmlWeb();
$html = $client->load('https://slc.polinema.ac.id/spada/my/');

$courses = [];
foreach ($html->find('.course-title') as $element) {
    $courses[] = [
        'name' => $element->plaintext,
        'url' => $element->parent->href,
    ];
}
```

### 3. Scrape Assignments
```php
$html = $client->load('https://slc.polinema.ac.id/spada/course/view.php?id=' . $courseId);

$assignments = [];
foreach ($html->find('.activity.assign') as $element) {
    $assignments[] = [
        'title' => $element->find('.instancename', 0)->plaintext,
        'due' => $element->find('.due-date', 0)->plaintext,
    ];
}
```

**Note**: Web scraping kurang reliable dan bisa break saat UI berubah. Gunakan API jika memungkinkan.

---

## 🧪 Testing Integration

### 1. Manual Testing
```bash
php artisan tinker

>>> use App\Services\Lms\SpadaConnector;
>>> $spada = new SpadaConnector();
>>> $spada->authenticate('2341760xxx', 'password');
>>> $courses = $spada->getCourses();
>>> dd($courses);
```

### 2. Unit Test
```php
// tests/Feature/SpadaIntegrationTest.php
use Tests\TestCase;
use App\Services\Lms\SpadaConnector;

class SpadaIntegrationTest extends TestCase
{
    public function test_can_authenticate_to_spada()
    {
        $spada = new SpadaConnector();
        
        $result = $spada->authenticate(
            config('testing.spada.username'),
            config('testing.spada.password')
        );
        
        $this->assertTrue($result);
        $this->assertTrue($spada->isAuthenticated());
    }
    
    public function test_can_fetch_courses()
    {
        $spada = new SpadaConnector();
        $spada->authenticate(...);
        
        $courses = $spada->getCourses();
        
        $this->assertIsArray($courses);
        $this->assertNotEmpty($courses);
        $this->assertArrayHasKey('external_id', $courses[0]);
    }
}
```

### 3. Test Config
```php
// config/testing.php
return [
    'spada' => [
        'username' => env('TEST_SPADA_USERNAME'),
        'password' => env('TEST_SPADA_PASSWORD'),
    ],
];
```

---

## ⚠️ Known Issues & Solutions

### Issue 1: Session Expired
**Problem**: Session SPADA expire setelah beberapa jam
**Solution**: 
- Cek `isAuthenticated()` sebelum setiap request
- Auto re-authenticate jika session expired
- Simpan credentials (encrypted) untuk auto-login

### Issue 2: CSRF Token
**Problem**: Beberapa endpoint butuh CSRF token
**Solution**:
- Extract CSRF token dari HTML login page
- Simpan di session/cache
- Include di setiap POST request

### Issue 3: Rate Limiting
**Problem**: SPADA bisa rate-limit jika terlalu banyak request
**Solution**:
- Implement delay antar request (500ms - 1s)
- Cache hasil fetch (5-10 menit)
- Sync incremental, bukan full sync

### Issue 4: Struktur HTML Berubah
**Problem**: Web scraping break saat UI update
**Solution**:
- Prioritaskan API endpoint
- Unit test untuk scraping logic
- Fallback graceful jika scraping gagal

---

## 🔒 Security Considerations

### 1. Credential Storage
```php
// JANGAN simpan plain text!
// Gunakan encryption
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($password);
$user->update(['provider_tokens' => $encrypted]);

// Decrypt saat perlu
$password = Crypt::decryptString($user->provider_tokens);
```

### 2. Session Storage
```php
// Simpan session di cache dengan TTL
Cache::put("spada_session_{$user->id}", $cookies, now()->addHours(6));

// Auto-cleanup expired sessions
$schedule->call(function () {
    Cache::tags(['spada_sessions'])->flush();
})->daily();
```

### 3. Request Logging
```php
// Log semua request ke SPADA untuk debugging
Log::channel('spada')->info('Request', [
    'user_id' => $user->id,
    'endpoint' => $url,
    'method' => $method,
    'timestamp' => now(),
]);
```

---

## 📊 Monitoring & Analytics

### Track Sync Status
```php
// Model: LmsMap
$map = LmsMap::updateOrCreate([
    'provider' => 'spada',
    'external_course_id' => $courseId,
    'user_id' => $user->id,
], [
    'sync_status' => 'synced',
    'last_sync_at' => now(),
    'sync_count' => DB::raw('sync_count + 1'),
]);
```

### Metrics Dashboard
- Total courses synced
- Total assignments fetched
- Sync success rate
- Average sync time
- Failed sync attempts

---

## 🚀 Optimization Tips

1. **Batch Requests**: Fetch multiple courses dalam 1 request jika API support
2. **Parallel Processing**: Gunakan Queue untuk sync multiple users
3. **Selective Sync**: Hanya sync course yang aktif/semester berjalan
4. **Diff Detection**: Cek timestamp, hanya sync yang berubah
5. **Compression**: Enable gzip untuk response besar

---

## 📚 Resources

- [Moodle Web Services Documentation](https://docs.moodle.org/dev/Web_services)
- [Moodle REST API](https://docs.moodle.org/dev/Creating_a_web_service_client)
- [GuzzleHTTP Documentation](https://docs.guzzlephp.org/)

---

## 🤝 Support

Untuk pertanyaan atau issue terkait integrasi SPADA:
- Check logs: `storage/logs/spada.log`
- Debug dengan Telescope: `/telescope/requests`
- Cek status SPADA: https://slc.polinema.ac.id/spada/admin/tool/health/

---

**Last Updated**: 29 Oktober 2025  
**Status**: ✅ Ready for Implementation
