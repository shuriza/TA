# 📚 Smart LMS - Intelligent Learning Management System<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



Sistem manajemen pembelajaran berbasis AI untuk meningkatkan produktivitas akademik mahasiswa dengan integrasi SPADA ULM.<p align="center">

<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

![Laravel](https://img.shields.io/badge/Laravel-12.36.0-red)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

![PHP](https://img.shields.io/badge/PHP-8.3+-blue)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

![License](https://img.shields.io/badge/License-MIT-green)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

</p>

## 🌟 Features

## About Laravel

### 1. **Smart Dashboard**

- **Student Dashboard**: Urgent assignments (H-3), upcoming tasks, submission historyLaravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- **Lecturer Dashboard**: Course overview, pending grading, recent assignments

- **Admin Dashboard**: System statistics, management tools, SPADA sync- [Simple, fast routing engine](https://laravel.com/docs/routing).

- [Powerful dependency injection container](https://laravel.com/docs/container).

### 2. **Assignment Management**- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

- ✅ CRUD operations (Create, Read, Update, Delete)- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

- ✅ File attachments (PDF, DOC, PPT, etc.)- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

- ✅ Priority levels (High, Medium, Low)- [Robust background job processing](https://laravel.com/docs/queues).

- ✅ Status tracking (Draft, Published, Closed)- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- ✅ Late submission controls

- ✅ Advanced filtering & sortingLaravel is accessible, powerful, and provides tools required for large, robust applications.

- ✅ Color-coded by priority

## Learning Laravel

### 3. **Real-time Notification System**

- 🔔 **Notification Bell** with unread count badgeLaravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

- 📨 **4 Notification Types**:

  - New Assignment (untuk students)You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

  - Deadline Reminder (H-3, H-1, 6 jam)

  - Submission Received (untuk lecturers)If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

  - Grade Updated (untuk students)

- ⏰ **Automated Reminders** via scheduled commands## Laravel Sponsors

- 📱 Ready for Telegram integration

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### 4. **AI Assistant** 🤖

- 🎯 **Task Prioritization**: AI-powered recommendations### Premium Partners

- 📅 **Study Planner**: Generate realistic weekly schedules

- 💬 **Chat Interface**: Q&A about assignments & study tips- **[Vehikl](https://vehikl.com/)**

- 📊 **Performance Insights**: Analyze submission patterns- **[Tighten Co.](https://tighten.co)**

- 🚀 Powered by **OpenAI GPT-4o Mini**- **[WebReinvent](https://webreinvent.com/)**

- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

### 5. **SPADA Integration**- **[64 Robots](https://64robots.com)**

- 🔄 Automatic sync with SPADA ULM- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**

- 📥 Import courses, assignments, submissions- **[Cyber-Duck](https://cyber-duck.co.uk)**

- 🔗 OAuth2 authentication- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

- ⏱️ Scheduled sync every 6 hours- **[Jump24](https://jump24.co.uk)**

- **[Redberry](https://redberry.international/laravel/)**

## 🛠️ Tech Stack- **[Active Logic](https://activelogic.com)**

- **[byte5](https://byte5.de)**

### Backend- **[OP.GG](https://op.gg)**

- **Laravel 12.36.0** (PHP 8.3+)

- **MySQL** (Database)## Contributing

- **Laravel Breeze** (Authentication)

- **Spatie Permissions** (Role-based access)Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

- **Laravel Scout** (Search)

- **OpenAI PHP** (AI features)## Code of Conduct



### FrontendIn order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

- **Blade Templates**

- **Tailwind CSS** (Styling)## Security Vulnerabilities

- **Alpine.js** (Interactivity)

- **Heroicons** (Icons)If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



### Tools## License

- **Laravel Horizon** (Queue monitoring)

- **Laravel Telescope** (Debugging)The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## 📋 Requirements

- PHP 8.3 or higher
- MySQL 8.0 or higher
- Composer 2.x
- Node.js 18+ & NPM
- OpenAI API Key (optional, for AI features)

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/shuriza/TA.git
cd TA
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env`
```env
APP_NAME="Smart LMS"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_cerdas
DB_USERNAME=root
DB_PASSWORD=your_password

# OpenAI (Optional - for AI features)
OPENAI_API_KEY=sk-your-openai-api-key

# SPADA OAuth (Optional - for sync)
SPADA_CLIENT_ID=your_client_id
SPADA_CLIENT_SECRET=your_client_secret
SPADA_REDIRECT_URI=http://localhost/oauth/callback
```

### 5. Database Setup
```bash
php artisan migrate --seed
```

This will create:
- ✅ All tables (users, courses, assignments, etc.)
- ✅ Default roles & permissions
- ✅ Sample users (students, lecturers, admin)
- ✅ Sample courses & assignments
- ✅ Sample notifications

### 6. Build Assets
```bash
npm run build
```

### 7. Run Application
```bash
php artisan serve
```

Access: http://127.0.0.1:8000

## 👥 Default Users

After seeding, you can login with:

| Role | Email | Password | NIM/NIP |
|------|-------|----------|---------|
| Admin | admin@ulm.ac.id | password | - |
| Dosen | dosen@ulm.ac.id | password | 198001012006041001 |
| Mahasiswa | mahasiswa@ulm.ac.id | password | 2110131210001 |

## 📁 Project Structure

```
app/
├── Console/Commands/
│   ├── SyncSPADA.php              # SPADA sync command
│   └── SendDeadlineReminders.php  # Notification reminders
├── Http/Controllers/
│   ├── DashboardController.php    # Role-based dashboards
│   ├── AssignmentViewController.php # Assignment CRUD
│   ├── NotificationController.php  # Notifications
│   └── AIAssistantController.php   # AI features
├── Models/
│   ├── User.php
│   ├── Course.php
│   ├── Assignment.php
│   ├── Submission.php
│   └── Notification.php
└── Services/
    ├── NotificationService.php    # Notification logic
    ├── AIAssistantService.php     # AI features
    └── SPADAService.php           # SPADA integration

resources/views/
├── dashboard/
│   ├── student.blade.php
│   ├── lecturer.blade.php
│   └── admin.blade.php
├── assignments/
│   ├── index.blade.php            # List view
│   ├── show.blade.php             # Detail view
│   └── create.blade.php           # Create form
├── notifications/
│   └── index.blade.php            # Notification list
├── ai/
│   └── assistant.blade.php        # AI chat interface
└── components/
    └── notification-bell.blade.php # Notification dropdown
```

## ⚙️ Scheduled Tasks

### Commands
```bash
# SPADA Sync (every 6 hours)
php artisan spada:sync

# Deadline Reminders (hourly)
php artisan reminders:deadline

# Manual run for testing
php artisan spada:sync --force
php artisan reminders:deadline
```

### Setup Cron (Production)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] Login as student, lecturer, admin
- [ ] View dashboards for each role
- [ ] Create new assignment (lecturer)
- [ ] Submit assignment (student)
- [ ] Check notifications
- [ ] Test AI assistant chat
- [ ] Generate study plan
- [ ] Sync SPADA (if configured)

## 📊 Database Schema

### Core Tables
- `users` - User accounts (students, lecturers, admin)
- `courses` - Course information
- `assignments` - Assignment data
- `submissions` - Student submissions
- `notifications` - Notification history
- `course_user` - Student enrollments (pivot)
- `files` - File attachments

## 🐛 Troubleshooting

### Common Issues

**AI features not working**
- Check `OPENAI_API_KEY` in `.env`
- Fallback mode activates without API key

**Notifications not appearing**
- Verify `notifications` table exists
- Check JavaScript console for errors

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Shuriza**
- GitHub: [@shuriza](https://github.com/shuriza)
- Project: Tugas Akhir - Smart LMS with AI Integration

## 🙏 Acknowledgments

- Laravel Framework
- OpenAI API
- SPADA ULM
- Universitas Lambung Mangkurat

---

**Built with ❤️ for better academic productivity**
