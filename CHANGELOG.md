# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2025-10-29

### Added - Phase 1: Frontend Views
- **Student Dashboard** with urgent assignments (H-3) and upcoming tasks
- **Lecturer Dashboard** with pending grading and course overview
- **Admin Dashboard** with system statistics and management tools
- **Assignment List View** with advanced filtering (course, status, priority, sort)
- **Assignment Detail View** with submission form for students
- **Assignment Create Form** for lecturers with file upload support
- Color-coded priority system (red/yellow/green borders)
- Responsive design with Tailwind CSS
- Role-based content display

### Added - Phase 2: Notification System
- **Notification Bell** component with unread count badge
- **Real-time Notification Dropdown** with 50 latest notifications
- **Notification Types**:
  - `new_assignment` - When assignment is published
  - `deadline_reminder` - H-3, H-1, 6 hours before deadline
  - `submission_received` - When student submits
  - `grade_updated` - When submission is graded
- **NotificationService** for creating and managing notifications
- **Automated Deadline Reminders** via scheduled command (hourly)
- Notification list page with filter and pagination
- Mark as read / Mark all as read functionality
- Delete notifications feature
- Auto-refresh every 30 seconds

### Added - Phase 3: AI Assistant
- **AIAssistantService** with OpenAI GPT-4o Mini integration
- **Assignment Recommendations** with AI-powered prioritization
- **Study Plan Generator** for realistic weekly schedules
- **Chat Interface** for Q&A about assignments
- **Performance Insights** analyzing submission patterns
- **Fallback System** that works without API key
- Quick action buttons for common tasks
- Real-time chat with conversation context
- Color-coded recommendation cards

### Added - Database & Models
- Migration for `notifications` table
- Migration for `submissions` table (completed)
- Notification model with scopes (unread, read, recent)
- Relationships between User, Assignment, Submission, Notification

### Added - Controllers
- `DashboardController` with role-based views
- `AssignmentViewController` for CRUD operations
- `NotificationController` for notification management
- `AIAssistantController` for AI features

### Added - Commands
- `reminders:deadline` - Send deadline reminders
- Scheduled tasks in `routes/console.php`

### Added - Services
- `NotificationService` - Handle all notification logic
- `AIAssistantService` - AI-powered features

### Added - Routes
- Assignment routes (index, create, store, show, submit)
- Notification routes (index, unread, read, readAll, destroy)
- AI Assistant routes (assistant, recommendations, studyPlan, chat, insights)

### Added - Components
- `notification-bell.blade.php` - Notification dropdown component

### Added - Seeders
- `NotificationSeeder` - Sample notifications for testing

### Added - Dependencies
- `openai-php/laravel` (v0.17.1) - OpenAI integration

### Fixed
- Controller middleware issue (Laravel 11+ compatibility)
- Submissions table missing columns
- Navigation menu updated with new links

### Technical
- PHP 8.3.13
- Laravel 12.36.0
- MySQL database
- Tailwind CSS for styling
- Alpine.js for interactivity

## Roadmap

### Future Enhancements
- [ ] Telegram bot integration for notifications
- [ ] Email notifications
- [ ] Grade management interface for lecturers
- [ ] Calendar view for deadlines
- [ ] Export reports (PDF/Excel)
- [ ] Mobile app (PWA)
- [ ] Advanced analytics dashboard
- [ ] Peer review system
- [ ] Discussion forums per assignment
- [ ] File versioning for submissions

### Planned Features
- [ ] Multi-language support (ID/EN)
- [ ] Dark mode
- [ ] Assignment templates
- [ ] Plagiarism detection
- [ ] Video submission support
- [ ] Live coding assignments
- [ ] Gamification (badges, achievements)
- [ ] Student portfolio

---

**Version Format**: [Major.Minor.Patch]
- **Major**: Breaking changes
- **Minor**: New features
- **Patch**: Bug fixes
