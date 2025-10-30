# 📖 Development Guide - LMS Cerdas

Panduan lengkap untuk development project LMS Cerdas.

---

## 🎯 Quick Start

### 1. Setup Environment

```bash
# Install dependencies
composer install
npm install

# Setup .env
cp .env.example .env
php artisan key:generate

# Migrate database
php artisan migrate --seed

# Build assets
npm run dev
```

### 2. Run Services

**Terminal 1 - Web Server:**
```bash
php artisan serve
# Atau gunakan Herd/Valet/Laragon
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work --tries=3
```

**Terminal 3 - Scheduler:**
```bash
php artisan schedule:work
```

**Terminal 4 - Asset Watcher (Development):**
```bash
npm run dev
```

---

## 🗂️ Migration & Seeding

### Create Migration
```bash
# Basic migration
php artisan make:migration create_tablename_table

# Migration untuk alter table
php artisan make:migration add_column_to_tablename_table --table=tablename
```

### Run Migrations
```bash
# Migrate database
php artisan migrate

# Rollback terakhir
php artisan migrate:rollback

# Rollback semua & migrate ulang
php artisan migrate:fresh

# Migrate + seed
php artisan migrate:fresh --seed
```

### Create Seeders
```bash
php artisan make:seeder UserSeeder
php artisan make:seeder CourseSeeder
php artisan make:seeder DemoSeeder
```

---

## 🏭 Factories & Models

### Create Model + Factory + Migration + Seeder
```bash
php artisan make:model Assignment -mfs
# -m: migration
# -f: factory
# -s: seeder
# -c: controller
# -a: all (mfsc + request + policy)
```

### Factory Usage
```php
// Create single
$user = User::factory()->create();

// Create multiple
$users = User::factory()->count(10)->create();

// With relation
$assignment = Assignment::factory()
    ->for(Course::factory())
    ->create();

// With specific data
$user = User::factory()->create([
    'role' => 'dosen',
    'email' => 'dosen@example.com',
]);
```

---

## 🎨 Controllers & Routes

### Create Controller
```bash
# API Resource Controller
php artisan make:controller Api/AssignmentController --api

# Standard Controller
php artisan make:controller TaskController

# Invokable Controller (single action)
php artisan make:controller SendReminderController --invokable
```

### Route Examples

**routes/api.php:**
```php
use App\Http\Controllers\Api\AssignmentController;

Route::middleware('auth:sanctum')->group(function () {
    // Resource routes
    Route::apiResource('assignments', AssignmentController::class);
    
    // Custom routes
    Route::get('/assignments/overdue', [AssignmentController::class, 'overdue']);
    Route::post('/ai/parse-task', [AiController::class, 'parseTask']);
});
```

**routes/web.php:**
```php
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
});
```

---

## 🔐 Authentication & Authorization

### Sanctum API Authentication

**Issue Token:**
```php
$token = $user->createToken('api-token')->plainTextToken;
```

**Protect Route:**
```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

### Spatie Permission

**Assign Role:**
```php
$user->assignRole('mahasiswa');
$user->assignRole('dosen');
```

**Check Permission:**
```php
// In controller
if ($user->hasRole('dosen')) {
    // ...
}

// In middleware
Route::middleware(['role:dosen'])->group(function () {
    // ...
});

// In blade
@role('dosen')
    <button>Edit Tugas</button>
@endrole
```

---

## 📝 Form Requests & Validation

### Create Request
```bash
php artisan make:request StoreAssignmentRequest
php artisan make:request UpdateAssignmentRequest
```

### Request Example
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['dosen', 'admin']);
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'required|date|after:now',
            'priority' => 'in:low,medium,high',
            'effort_mins' => 'nullable|integer|min:5',
            'impact' => 'nullable|integer|between:0,100',
        ];
    }

    public function messages(): array
    {
        return [
            'due_at.after' => 'Deadline harus di masa depan.',
            'course_id.exists' => 'Mata kuliah tidak ditemukan.',
        ];
    }
}
```

---

## ⚙️ Jobs & Queues

### Create Job
```bash
php artisan make:job SendReminderJob
php artisan make:job SyncMoodleAssignmentsJob
```

### Job Example
```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public Reminder $reminder
    ) {}

    public function handle(): void
    {
        // Send reminder logic
        $this->reminder->user->notify(
            new TaskDueSoon($this->reminder->assignment)
        );
        
        $this->reminder->update(['sent_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failed job
        \Log::error('Reminder failed', [
            'reminder_id' => $this->reminder->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### Dispatch Job
```php
// Dispatch immediately
SendReminderJob::dispatch($reminder);

// Dispatch to specific queue
SendReminderJob::dispatch($reminder)->onQueue('high');

// Dispatch delayed
SendReminderJob::dispatch($reminder)->delay(now()->addMinutes(10));

// Dispatch chain
Bus::chain([
    new SyncMoodleAssignmentsJob(),
    new GenerateRemindersJob(),
    new NotifyUsersJob(),
])->dispatch();
```

---

## 📅 Scheduler & Commands

### Create Command
```bash
php artisan make:command GenerateDailyPlan
php artisan make:command SyncFromLms
```

### Command Example
```php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDailyPlan extends Command
{
    protected $signature = 'plan:generate {--today}';
    protected $description = 'Generate daily plan untuk semua user';

    public function handle(): int
    {
        $this->info('Generating daily plans...');
        
        $users = User::where('role', 'mahasiswa')->get();
        $bar = $this->output->createProgressBar($users->count());
        
        foreach ($users as $user) {
            app(PlanDay::class)->handle($user);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Daily plans generated successfully!');
        
        return Command::SUCCESS;
    }
}
```

### Register in Scheduler
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Generate daily plan setiap pagi jam 05:30
    $schedule->command('plan:generate --today')
             ->dailyAt('05:30')
             ->timezone('Asia/Jakarta');
    
    // Send pending reminders setiap menit
    $schedule->job(new SendPendingRemindersJob())
             ->everyMinute();
    
    // Sync from Moodle setiap 5 menit
    $schedule->command('lms:sync moodle')
             ->everyFiveMinutes();
    
    // Cleanup old activities setiap minggu
    $schedule->command('activities:cleanup --days=90')
             ->weekly()
             ->sundays()
             ->at('02:00');
}
```

---

## 🔔 Notifications

### Create Notification
```bash
php artisan make:notification TaskDueSoon
php artisan make:notification AssignmentCreated
```

### Notification Example
```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskDueSoon extends Notification
{
    public function __construct(
        public Assignment $assignment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
        // Atau: return [$notifiable->notify_channel];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengingat: ' . $this->assignment->title)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Tugas akan segera deadline:')
            ->line('**' . $this->assignment->title . '**')
            ->line('Deadline: ' . $this->assignment->due_at->format('d M Y H:i'))
            ->action('Lihat Tugas', url('/assignments/' . $this->assignment->id))
            ->line('Jangan lupa untuk submit tepat waktu!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'due_at' => $this->assignment->due_at,
            'course' => $this->assignment->course->name,
        ];
    }
}
```

### Send Notification
```php
// To single user
$user->notify(new TaskDueSoon($assignment));

// To multiple users
Notification::send($users, new AssignmentCreated($assignment));

// Via specific channel
$user->notify((new TaskDueSoon($assignment))->via(['telegram']));
```

---

## 🔍 Meilisearch Integration

### Setup Index
```php
// In model
use Laravel\Scout\Searchable;

class Material extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'course_name' => $this->course->name,
            'type' => $this->type,
        ];
    }

    public function searchableAs(): string
    {
        return 'materials_index';
    }
}
```

### Import Data
```bash
# Import all models
php artisan scout:import "App\Models\Material"

# Flush index
php artisan scout:flush "App\Models\Material"
```

### Search Usage
```php
// Basic search
$materials = Material::search('Laravel')->get();

// With filters
$materials = Material::search('Database')
    ->where('course_id', 1)
    ->paginate(15);

// Raw query
$materials = Material::search('', function ($meilisearch, $query, $options) {
    $options['filter'] = 'type = pdf AND course_id = 1';
    return $meilisearch->search($query, $options);
})->get();
```

---

## 🧪 Testing

### Create Test
```bash
# Feature test
php artisan make:test Api/AssignmentApiTest

# Unit test
php artisan make:test Services/PrioritizerTest --unit
```

### Test Example
```php
namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Assignment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_assignments(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $assignments = Assignment::factory()->count(5)->create();
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/assignments');
        
        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }

    public function test_can_filter_overdue_assignments(): void
    {
        $user = User::factory()->create();
        
        Assignment::factory()->create(['due_at' => now()->subDay()]);
        Assignment::factory()->create(['due_at' => now()->addDay()]);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/assignments?filter=overdue');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }
}
```

### Run Tests
```bash
# All tests
php artisan test

# Specific test
php artisan test --filter=AssignmentApiTest

# With coverage
php artisan test --coverage

# Parallel testing
php artisan test --parallel
```

---

## 🐛 Debugging

### Laravel Telescope
```bash
# Install
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Access
http://localhost/telescope
```

### Debug Tips
```php
// Dump and die
dd($variable);

// Dump
dump($variable);

// Log
\Log::info('Debug info', ['data' => $data]);
\Log::error('Error occurred', ['exception' => $e]);

// Query log
DB::listen(function($query) {
    dump($query->sql);
    dump($query->bindings);
});

// Clockwork (alternative to Telescope)
composer require itsgoingd/clockwork --dev
```

---

## 📦 Deployment

### Production Checklist

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize
php artisan optimize

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link
```

### Supervisor Config (Queue Worker)
```ini
[program:lms-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Daily](https://laraveldaily.com)
- [Spatie Packages](https://spatie.be/open-source)
- [Laravel News](https://laravel-news.com)
- [Laracasts](https://laracasts.com)

---

**Happy Coding! 🚀**
