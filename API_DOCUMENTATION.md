# API Documentation - LMS Cerdas

Complete REST API documentation for LMS Cerdas application.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All API endpoints require Sanctum token authentication.

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
Accept: application/json
```

---

## 📝 Assignments API

### List Assignments
```http
GET /api/assignments
```

**Query Parameters:**
- `course_id` (optional) - Filter by course ID
- `status` (optional) - Filter by status: `draft`, `published`, `closed`
- `priority` (optional) - Filter by priority: `low`, `medium`, `high`
- `upcoming_days` (optional) - Get assignments due in next N days
- `my_courses` (optional) - Filter by user's enrolled courses (for students)
- `sort_by` (optional) - Sort field (default: `due_at`)
- `sort_order` (optional) - Sort order: `asc`, `desc` (default: `asc`)
- `per_page` (optional) - Items per page (default: 15)

**Example:**
```bash
GET /api/assignments?upcoming_days=7&my_courses=1&sort_by=due_at&sort_order=asc
```

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "course_id": 2,
      "title": "Quiz Pemrograman Web",
      "description": "Quiz tentang materi minggu 1-4",
      "due_at": "2025-11-01T17:28:39.000000Z",
      "status": "published",
      "priority": "high",
      "effort_mins": 60,
      "impact": 90,
      "tag": "quiz",
      "lms_url": "https://slc.polinema.ac.id/spada/...",
      "allow_late_submission": false,
      "max_score": 100,
      "course": {
        "id": 2,
        "name": "Pemrograman Web",
        "code": "TI-2A"
      },
      "submissions_count": 5
    }
  ],
  "per_page": 15,
  "total": 16
}
```

---

### Get Assignment Summary
```http
GET /api/assignments-summary
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 16,
    "published": 15,
    "draft": 0,
    "closed": 1,
    "urgent": 5,
    "upcoming": 10
  }
}
```

---

### Create Assignment
```http
POST /api/assignments
```

**Required Permission:** `create assignments` (dosen/admin only)

**Request Body:**
```json
{
  "course_id": 1,
  "title": "Tugas UTS Pemrograman Web",
  "description": "Buat aplikasi web menggunakan Laravel",
  "due_at": "2025-11-15 23:59:59",
  "status": "published",
  "priority": "high",
  "effort_mins": 240,
  "impact": 95,
  "tag": "uts",
  "lms_url": "https://slc.polinema.ac.id/spada/mod/assign/view.php?id=12345",
  "allow_late_submission": false,
  "max_score": 100,
  "attachments": ["file1.pdf", "file2.docx"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "id": 17,
    "course_id": 1,
    "title": "Tugas UTS Pemrograman Web",
    ...
  }
}
```

---

### Get Assignment Details
```http
GET /api/assignments/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Quiz Pemrograman Web",
    "course": { ... },
    "submissions": [ ... ],
    "files": [ ... ],
    "reminders": [ ... ]
  }
}
```

---

### Update Assignment
```http
PUT /api/assignments/{id}
PATCH /api/assignments/{id}
```

**Required Permission:** `edit assignments` (lecturer/admin)

**Request Body:** (all fields optional)
```json
{
  "title": "Updated Title",
  "due_at": "2025-11-20 23:59:59",
  "status": "closed"
}
```

---

### Delete Assignment
```http
DELETE /api/assignments/{id}
```

**Required Permission:** `delete assignments` (lecturer/admin)

---

## 📤 Submissions API

### List Submissions
```http
GET /api/submissions
```

**Query Parameters:**
- `assignment_id` (optional) - Filter by assignment
- `status` (optional) - Filter by status: `submitted`, `graded`
- `per_page` (optional) - Items per page (default: 15)

**Authorization:**
- Students: only see their own submissions
- Lecturers: see submissions for their courses
- Admin: see all submissions

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "assignment_id": 5,
      "user_id": 7,
      "content": "Jawaban saya adalah...",
      "score": null,
      "feedback": null,
      "status": "submitted",
      "submitted_at": "2025-10-28T10:30:00.000000Z",
      "assignment": { ... },
      "user": {
        "id": 7,
        "name": "Andi Pratama",
        "nim": "2241760001"
      },
      "files": [ ... ]
    }
  ]
}
```

---

### Create Submission
```http
POST /api/submissions
```

**Request Body (multipart/form-data):**
```
assignment_id: 5
content: "Ini adalah jawaban saya untuk tugas..."
files[]: file1.pdf
files[]: file2.docx
```

**Validation Rules:**
- Assignment must be `published` and not `closed`
- If `allow_late_submission` is false, deadline must not have passed
- Student cannot submit twice (use update instead)
- Max file size: 10MB per file

**Response:**
```json
{
  "success": true,
  "message": "Submission created successfully",
  "data": {
    "id": 25,
    "assignment_id": 5,
    "user_id": 7,
    "content": "Ini adalah jawaban saya...",
    "status": "submitted",
    "submitted_at": "2025-10-29T14:30:00.000000Z",
    "files": [
      {
        "id": 1,
        "filename": "abc123.pdf",
        "original_name": "Jawaban_Tugas.pdf",
        "size": 2048000,
        "mime_type": "application/pdf"
      }
    ]
  }
}
```

---

### Update Submission
```http
PUT /api/submissions/{id}
PATCH /api/submissions/{id}
```

**Authorization:** Only submission owner can update

**Request Body:**
```
content: "Updated answer..."
files[]: new_file.pdf
```

---

### Grade Submission (Dosen only)
```http
POST /api/submissions/{id}/grade
```

**Request Body:**
```json
{
  "score": 85,
  "feedback": "Bagus, tapi masih ada yang perlu diperbaiki di bagian validasi."
}
```

**Validation:**
- `score` must be between 0 and assignment's `max_score`
- Only course lecturer can grade

**Response:**
```json
{
  "success": true,
  "message": "Submission graded successfully",
  "data": {
    "id": 25,
    "score": 85,
    "feedback": "Bagus, tapi masih ada yang perlu diperbaiki...",
    "status": "graded"
  }
}
```

---

### Delete Submission
```http
DELETE /api/submissions/{id}
```

**Authorization:** Only submission owner

**Note:** Associated files will also be deleted from storage.

---

## 📚 Courses API

### List Courses
```http
GET /api/courses
```

### Get Course Details
```http
GET /api/courses/{id}
```

### Create Course
```http
POST /api/courses
```

### Update Course
```http
PUT /api/courses/{id}
```

### Delete Course
```http
DELETE /api/courses/{id}
```

---

## 📖 Materials API

### List Materials
```http
GET /api/materials
```

### Get Material Details
```http
GET /api/materials/{id}
```

### Create Material
```http
POST /api/materials
```

### Update Material
```http
PUT /api/materials/{id}
```

### Delete Material
```http
DELETE /api/materials/{id}
```

---

## 🔐 Authentication

### Login
```http
POST /api/login
```

**Request Body:**
```json
{
  "email": "andi.pratama@students.polinema.ac.id",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abc123xyz...",
  "user": {
    "id": 7,
    "name": "Andi Pratama",
    "email": "andi.pratama@students.polinema.ac.id",
    "role": "mahasiswa",
    "nim": "2241760001"
  }
}
```

### Logout
```http
POST /api/logout
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "title": ["The title field is required."],
    "due_at": ["The due at must be a valid date."]
  }
}
```

### Unauthorized (403)
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Not Found (404)
```json
{
  "message": "Resource not found"
}
```

---

## Testing with cURL

### Get upcoming assignments
```bash
curl -X GET "http://localhost:8000/api/assignments?upcoming_days=7&my_courses=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Submit assignment
```bash
curl -X POST "http://localhost:8000/api/submissions" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "assignment_id=5" \
  -F "content=Ini jawaban saya" \
  -F "files[]=@/path/to/file1.pdf" \
  -F "files[]=@/path/to/file2.docx"
```

### Grade submission
```bash
curl -X POST "http://localhost:8000/api/submissions/25/grade" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"score": 85, "feedback": "Bagus!"}'
```

---

## Rate Limiting

API requests are limited to:
- **60 requests per minute** for authenticated users
- **10 requests per minute** for unauthenticated users

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Pagination

All list endpoints support pagination with these parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

Response includes pagination meta:
```json
{
  "current_page": 1,
  "from": 1,
  "to": 15,
  "per_page": 15,
  "total": 50,
  "last_page": 4
}
```
