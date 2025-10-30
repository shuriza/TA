# 🧪 Testing Quick Reference - Smart LMS

**Quick commands & URLs untuk testing Smart LMS**

---

## 🚀 Quick Start

```bash
# Start server
cd C:\shuriza\TA
php artisan serve

# Server running at: http://localhost:8000
```

---

## 🔗 Testing URLs

### SSO Testing

| URL | Description | Expected Result |
|-----|-------------|----------------|
| `http://localhost:8000/siakad-demo` | SIAKAD Simulator (Mahasiswa) | Shows profile + "Connect to LMS" button |
| `http://localhost:8000/siakad-demo?role=dosen` | SIAKAD Simulator (Dosen) | Shows dosen profile |
| `http://localhost:8000/auth/siakad/test-token` | Generate test token | JSON response with callback URL |
| `http://localhost:8000/auth/siakad/test-token?role=dosen` | Generate token (Dosen) | Dosen test token |

### Main App URLs

| URL | Description | Login Required |
|-----|-------------|---------------|
| `http://localhost:8000` | Welcome page | No |
| `http://localhost:8000/login` | Login page | No |
| `http://localhost:8000/dashboard` | Dashboard | Yes |
| `http://localhost:8000/assignments` | Assignments list | Yes |
| `http://localhost:8000/notifications` | Notifications | Yes |
| `http://localhost:8000/ai-assistant` | AI Assistant chat | Yes |
| `http://localhost:8000/ai/study-plan` | Study planner | Yes |
| `http://localhost:8000/ai/insights` | AI insights | Yes |

---

## 🧪 SSO Testing Scenarios

### Scenario 1: Mahasiswa Login via SSO

```bash
# 1. Open simulator
Start: http://localhost:8000/siakad-demo

# 2. You should see:
✅ Profile card dengan:
   - Nama: Ahmad Rizki Pratama
   - NIM: 2341760001
   - Email: 2341760001@student.polinema.ac.id
   - Prodi: D4 Teknik Informatika

✅ Green "Connect to LMS" button

# 3. Click button
Expected: Redirect to /auth/siakad/callback?token=...&timestamp=...

# 4. After validation
Expected: Auto-login → Redirect to /dashboard

# 5. Check dashboard
Should show: Mahasiswa dashboard with assignments, courses, etc.
```

### Scenario 2: Dosen Login via SSO

```bash
# 1. Open simulator
Start: http://localhost:8000/siakad-demo?role=dosen

# 2. You should see:
✅ Profile card dengan:
   - Nama: Dr. Budi Santoso, M.Kom
   - NIP: 198001012000
   - Email: 198001012000@polinema.ac.id

✅ Green "Connect to LMS" button

# 3. Click button & auto-login
Expected: Dosen dashboard
```

### Scenario 3: Token Expiry Test

```bash
# 1. Generate token
GET: http://localhost:8000/auth/siakad/test-token?role=mahasiswa

# 2. Copy callback_url from response
Example: http://localhost:8000/auth/siakad/callback?token=abc123...&timestamp=1234567890

# 3. Wait 6+ minutes (token expires after 5 min)

# 4. Try to access the callback_url
Expected: Error "SSO token expired. Please try again."
```

### Scenario 4: Token Reuse Test

```bash
# 1. Generate token & login
GET: http://localhost:8000/auth/siakad/test-token?role=mahasiswa
Click callback URL → Login success

# 2. Logout
Click logout button

# 3. Try to use same callback URL again
Expected: Error "SSO token not found" or "Invalid token"
Reason: Token is one-time use only
```

---

## 📋 Testing Checklist

### ✅ SSO Flow

- [ ] **Simulator Loads**
  - Profile displayed correctly
  - "Connect to LMS" button visible
  - Green gradient card styling

- [ ] **SSO Redirect**
  - Click button → redirect to callback
  - URL contains `token` & `timestamp` parameters
  - No errors during redirect

- [ ] **Token Validation**
  - Token validated successfully
  - User data retrieved from cache
  - No validation errors

- [ ] **Auto-Login**
  - User logged in automatically
  - Session created
  - Redirect to dashboard

- [ ] **Dashboard Display**
  - Correct user name displayed
  - Correct role (mahasiswa/dosen)
  - Dashboard content relevant to role

- [ ] **Security**
  - Token expires after 5 minutes ✓
  - Token cannot be reused ✓
  - Invalid signature rejected ✓

### ✅ Notifications

- [ ] **Bell Icon**
  - Shows unread count badge
  - Opens dropdown on click
  - Lists recent notifications

- [ ] **Mark as Read**
  - Click notification → marked as read
  - Badge count decreases
  - Notification moved to "read" section

- [ ] **Delete**
  - Delete button works
  - Notification removed from list
  - Count updated

### ✅ AI Assistant

- [ ] **Chat**
  - Send message → AI responds
  - Responses relevant & helpful
  - Context aware (role, courses)

- [ ] **Recommendations**
  - Shows personalized recommendations
  - Based on user's courses & progress
  - Actionable suggestions

- [ ] **Study Plan**
  - Generates weekly study plan
  - Considers upcoming deadlines
  - Personalized to user

- [ ] **Insights**
  - Shows performance metrics
  - Charts & visualizations
  - Actionable insights

### ✅ Assignments

- [ ] **List View**
  - Shows all assignments
  - Filter by course works
  - Sort by deadline works

- [ ] **Detail View**
  - Assignment details displayed
  - Submission form visible (if student)
  - Submissions list visible (if lecturer)

- [ ] **Create (Lecturer)**
  - Form loads correctly
  - Create assignment works
  - Notification sent to students

- [ ] **Submit (Student)**
  - Upload file works
  - Submit button works
  - Notification sent to lecturer

---

## 🐛 Common Issues & Solutions

### Issue 1: "Server not running"

```bash
# Check if server is running
ps aux | findstr php

# If not running, start it
cd C:\shuriza\TA
php artisan serve
```

### Issue 2: "Route not found"

```bash
# Clear & cache routes
php artisan route:clear
php artisan route:cache

# Verify routes
php artisan route:list --name=siakad
```

### Issue 3: "Token expired" immediately

```bash
# Check server time
php -r "echo date('Y-m-d H:i:s');"

# Check .env configuration
# Make sure SIAKAD_TIMEOUT is set correctly
```

### Issue 4: "Invalid signature"

```bash
# Check shared secret in .env
# SIAKAD_SHARED_SECRET must be same in simulator & validator

# Verify in .env:
SIAKAD_SHARED_SECRET=lms-cerdas-polinema-secret-2025
```

### Issue 5: "User not found after login"

```bash
# Check if user was created
php artisan tinker
>>> User::where('nim', '2341760001')->first();

# If null, check SIAKADSSOController::handleTokenAuth()
# Make sure User::firstOrCreate() is working
```

---

## 📊 Test Data

### Test Users (from simulator)

**Mahasiswa:**
```
NIM: 2341760001
Nama: Ahmad Rizki Pratama
Email: 2341760001@student.polinema.ac.id
Prodi: D4 Teknik Informatika
Role: mahasiswa
```

**Dosen:**
```
NIP: 198001012000
Nama: Dr. Budi Santoso, M.Kom
Email: 198001012000@polinema.ac.id
Role: dosen
```

---

## 🔧 Debug Commands

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check database
php artisan tinker
>>> User::count();
>>> Cache::get('sso_token_...');

# Check routes
php artisan route:list --name=siakad

# Check config
php artisan tinker
>>> config('services.siakad.shared_secret');
>>> config('services.siakad.url');
```

---

## 📞 Quick Help

**Error tracking:**
```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Filter SSO logs
tail -f storage/logs/laravel.log | grep SSO
```

**Need help?**
- Check: `docs/SSO_CALLBACK_FLOW.md`
- Check: `docs/PROJECT_STATUS.md`
- Email: dev@polinema.ac.id

---

**Last Updated:** 29 Oktober 2025  
**Version:** 2.0
