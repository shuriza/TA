# Models Reference - LMS Cerdas

Dokumentasi lengkap untuk semua Eloquent Models dalam aplikasi LMS Cerdas.

## 📋 Daftar Models

### 1. User Model
**File:** `app/Models/User.php`

**Traits:**
- `HasFactory`, `Notifiable`, `HasRoles` (Spatie Permission)

**Fillable Fields:**
```php
'name', 'email', 'password', 'role', 'nim', 'nip', 'timezone', 'telegram_chat_id', 'provider_tokens'
```

**Casts:**
```php
'email_verified_at' => 'datetime',
'password' => 'hashed',
'provider_tokens' => 'encrypted:array'
```

**Relationships:**
- `taughtCourses()` - HasMany Course (untuk dosen)
- `enrolledCourses()` - BelongsToMany Course (untuk mahasiswa)
- `submissions()` - HasMany Submission
- `reminders()` - HasMany Reminder
- `activities()` - HasMany Activity

**Roles:** admin, dosen, mahasiswa

---

### 2. Course Model
**File:** `app/Models/Course.php`

**Fillable Fields:**
```php
'code', 'name', 'lecturer_id', 'semester', 'class', 'description', 'color', 'is_active'
```

**Casts:**
```php
'is_active' => 'boolean'
```

**Relationships:**
- `lecturer()` - BelongsTo User
- `students()` - BelongsToMany User (pivot: course_user)
- `materials()` - HasMany Material
- `assignments()` - HasMany Assignment
- `lmsMaps()` - HasMany LmsMap (polymorphic)

**Contoh:**
```php
$course = Course::find(1);
$lecturer = $course->lecturer;
$students = $course->students;
$assignments = $course->assignments()->where('status', 'published')->get();
```

---

### 3. Assignment Model
**File:** `app/Models/Assignment.php`

**Fillable Fields:**
```php
'course_id', 'title', 'description', 'due_at', 'status', 'priority', 
'effort_mins', 'impact', 'tag', 'lms_url', 'allow_late_submission', 
'max_score', 'attachments'
```

**Casts:**
```php
'due_at' => 'datetime',
'allow_late_submission' => 'boolean',
'attachments' => 'array'
```

**Enums:**
- `status`: 'draft', 'published', 'closed'
- `priority`: 'low', 'medium', 'high'

**Relationships:**
- `course()` - BelongsTo Course
- `submissions()` - HasMany Submission
- `reminders()` - HasMany Reminder
- `files()` - HasMany File (polymorphic)
- `lmsMaps()` - HasMany LmsMap (polymorphic)

**Contoh:**
```php
$urgent = Assignment::where('priority', 'high')
    ->where('due_at', '<=', now()->addDays(3))
    ->get();
```

---

### 4. Material Model
**File:** `app/Models/Material.php`

**Fillable Fields:**
```php
'course_id', 'title', 'description', 'type', 'file_url', 'lms_url'
```

**Relationships:**
- `course()` - BelongsTo Course

**Contoh:**
```php
$materials = Material::where('course_id', 1)
    ->where('type', 'pdf')
    ->get();
```

---

### 5. Submission Model
**File:** `app/Models/Submission.php`

**Fillable Fields:**
```php
'assignment_id', 'user_id', 'content', 'score', 'feedback', 'status', 'submitted_at'
```

**Casts:**
```php
'submitted_at' => 'datetime'
```

**Relationships:**
- `assignment()` - BelongsTo Assignment
- `user()` - BelongsTo User
- `files()` - HasMany File (polymorphic)

**Contoh:**
```php
$submission = Submission::create([
    'assignment_id' => 1,
    'user_id' => auth()->id(),
    'content' => 'Jawaban saya...',
    'submitted_at' => now()
]);
```

---

### 6. File Model
**File:** `app/Models/File.php`

**Fillable Fields:**
```php
'fileable_type', 'fileable_id', 'filename', 'original_name', 'path', 'mime_type', 'size'
```

**Relationships:**
- `fileable()` - MorphTo (Assignment atau Submission)

**Contoh:**
```php
// File untuk assignment
$assignment->files()->create([
    'filename' => 'modul_1.pdf',
    'original_name' => 'Modul Praktikum 1.pdf',
    'path' => 'assignments/modul_1.pdf',
    'mime_type' => 'application/pdf',
    'size' => 1024000
]);

// File untuk submission
$submission->files()->create([...]);
```

---

### 7. Reminder Model
**File:** `app/Models/Reminder.php`

**Fillable Fields:**
```php
'user_id', 'assignment_id', 'type', 'scheduled_at', 'sent_at', 'channel', 'status'
```

**Casts:**
```php
'scheduled_at' => 'datetime',
'sent_at' => 'datetime'
```

**Relationships:**
- `user()` - BelongsTo User
- `assignment()` - BelongsTo Assignment

**Reminder Types:**
- H-7 (7 hari sebelum deadline)
- H-3 (3 hari sebelum deadline)
- H-1 (1 hari sebelum deadline)
- H-0 (hari H deadline)

**Channels:**
- telegram
- email
- push

**Contoh:**
```php
Reminder::create([
    'user_id' => 1,
    'assignment_id' => 5,
    'type' => 'H-3',
    'scheduled_at' => $assignment->due_at->subDays(3),
    'channel' => 'telegram',
    'status' => 'pending'
]);
```

---

### 8. LmsMap Model
**File:** `app/Models/LmsMap.php`

**Fillable Fields:**
```php
'mappable_type', 'mappable_id', 'lms_platform', 'external_id', 'external_url', 'last_synced_at'
```

**Casts:**
```php
'last_synced_at' => 'datetime'
```

**Relationships:**
- `mappable()` - MorphTo (Course atau Assignment)

**LMS Platforms:**
- spada_polinema
- moodle
- google_classroom
- canvas

**Contoh:**
```php
// Map assignment ke SPADA
$assignment->lmsMaps()->create([
    'lms_platform' => 'spada_polinema',
    'external_id' => '12345',
    'external_url' => 'https://slc.polinema.ac.id/spada/mod/assign/view.php?id=12345',
    'last_synced_at' => now()
]);
```

---

### 9. Activity Model
**File:** `app/Models/Activity.php`

**Fillable Fields:**
```php
'user_id', 'type', 'description', 'metadata'
```

**Casts:**
```php
'metadata' => 'array'
```

**Relationships:**
- `user()` - BelongsTo User

**Activity Types:**
- login
- sync_lms
- submit_assignment
- view_material
- ai_query

**Contoh:**
```php
Activity::create([
    'user_id' => auth()->id(),
    'type' => 'sync_lms',
    'description' => 'Sinkronisasi SPADA berhasil',
    'metadata' => [
        'courses_synced' => 5,
        'assignments_synced' => 12,
        'duration_seconds' => 8
    ]
]);
```

---

## 🔗 Database Relationships

```
User
├── taughtCourses (1:N) → Course
├── enrolledCourses (N:M) → Course (via course_user)
├── submissions (1:N) → Submission
├── reminders (1:N) → Reminder
└── activities (1:N) → Activity

Course
├── lecturer (N:1) → User
├── students (N:M) → User
├── materials (1:N) → Material
├── assignments (1:N) → Assignment
└── lmsMaps (1:N polymorphic) → LmsMap

Assignment
├── course (N:1) → Course
├── submissions (1:N) → Submission
├── reminders (1:N) → Reminder
├── files (1:N polymorphic) → File
└── lmsMaps (1:N polymorphic) → LmsMap

Submission
├── assignment (N:1) → Assignment
├── user (N:1) → User
└── files (1:N polymorphic) → File
```

---

## 🎯 Query Examples

### Get assignments due in 3 days for a student
```php
$student = User::find(1);
$urgentAssignments = Assignment::whereHas('course.students', function($q) use ($student) {
    $q->where('user_id', $student->id);
})
->where('status', 'published')
->whereBetween('due_at', [now(), now()->addDays(3)])
->orderBy('due_at')
->get();
```

### Get all submissions for an assignment with student info
```php
$submissions = Submission::where('assignment_id', 5)
    ->with(['user', 'files'])
    ->orderBy('submitted_at', 'desc')
    ->get();
```

### Get courses with assignments count
```php
$courses = Course::withCount(['assignments' => function($q) {
    $q->where('status', 'published');
}])
->where('is_active', true)
->get();
```

### Sync status check
```php
$course = Course::with('lmsMaps')->find(1);
$lastSync = $course->lmsMaps->max('last_synced_at');
if ($lastSync->diffInHours(now()) > 6) {
    // Perlu sync ulang
}
```

---

## 📊 Seeders

Database telah di-seed dengan data demo:

- **9 Users:** 1 admin, 3 dosen, 5 mahasiswa
- **5 Courses:** TI-2A (Pemrograman Web), TI-2B (Basis Data), TI-2C (Algoritma), TI-3A (Mobile), TI-3B (AI)
- **16 Assignments:** 3 per course (Quiz, Praktikum, Diskusi) + 1 completed
- **3 Roles:** admin, dosen, mahasiswa
- **22 Permissions:** CRUD untuk courses, assignments, materials, submissions, dll.

### Default Credentials

**Admin:**
- Email: admin@polinema.ac.id
- Password: password

**Dosen:**
- budi.santoso@polinema.ac.id / password
- dewi.kartika@polinema.ac.id / password
- ahmad.rizky@polinema.ac.id / password

**Mahasiswa:**
- andi.pratama@students.polinema.ac.id / password
- siti.nurhaliza@students.polinema.ac.id / password
- (dan 3 lainnya)

---

## 🚀 Next Steps

1. ✅ Models dan relationships selesai
2. 🔄 Buat API Controllers untuk CRUD operations
3. 🔄 Implement SPADA Sync Workers
4. 🔄 Build AI Assistant (ParseTask, PlanDay)
5. 🔄 Setup Notification System (Telegram, Email)
6. 🔄 Create Frontend Views (Dashboard, Assignments List, Calendar)
