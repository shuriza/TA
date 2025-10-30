<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\LmsMap;
use App\Models\User;
use App\Services\Lms\SpadaConnector;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncSpadaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spada:sync 
                            {--user= : Sync for specific user ID}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync courses and assignments from SPADA Polinema';

    private SpadaConnector $spadaConnector;

    public function __construct()
    {
        parent::__construct();
        $this->spadaConnector = new SpadaConnector();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting SPADA sync...');
        $startTime = now();

        $userId = $this->option('user');
        $force = $this->option('force');

        $users = $userId 
            ? User::where('id', $userId)->get() 
            : User::whereNotNull('provider_tokens')->get();

        if ($users->isEmpty()) {
            $this->error('No users found with SPADA credentials');
            return 1;
        }

        $this->info("Found {$users->count()} users to sync");

        $totalCourses = 0;
        $totalAssignments = 0;
        $errors = [];

        foreach ($users as $user) {
            $this->line("\n📚 Syncing for: {$user->name} ({$user->email})");

            try {
                // Check if need to sync (every 6 hours unless forced)
                $lastSync = Activity::where('user_id', $user->id)
                    ->where('type', 'sync_lms')
                    ->latest()
                    ->first();

                if (!$force && $lastSync && $lastSync->created_at->diffInHours(now()) < 6) {
                    $this->warn("  ⏭️  Skipped (last synced {$lastSync->created_at->diffForHumans()})");
                    continue;
                }

                // Get credentials from provider_tokens
                $tokens = $user->provider_tokens ?? [];
                if (!isset($tokens['spada']) || !isset($tokens['spada']['username'])) {
                    $this->warn("  ⚠️  No SPADA credentials found");
                    continue;
                }

                // Authenticate
                $this->line("  🔐 Authenticating...");
                $authResult = $this->spadaConnector->authenticate(
                    $tokens['spada']['username'],
                    $tokens['spada']['password']
                );

                if (!$authResult) {
                    $this->error("  ❌ Authentication failed");
                    $errors[] = "{$user->email}: Authentication failed";
                    continue;
                }

                // Sync courses
                $this->line("  📖 Fetching courses...");
                $spadaCourses = $this->spadaConnector->getCourses();
                $coursesCount = $this->syncCourses($user, $spadaCourses);
                $totalCourses += $coursesCount;
                $this->info("  ✓ Synced {$coursesCount} courses");

                // Sync assignments
                $this->line("  📝 Fetching assignments...");
                $assignmentsCount = 0;
                
                foreach ($spadaCourses as $spadaCourse) {
                    $assignments = $this->spadaConnector->getAssignments($spadaCourse['id']);
                    $count = $this->syncAssignments($user, $spadaCourse['id'], $assignments);
                    $assignmentsCount += $count;
                }

                $totalAssignments += $assignmentsCount;
                $this->info("  ✓ Synced {$assignmentsCount} assignments");

                // Log activity
                Activity::create([
                    'user_id' => $user->id,
                    'type' => 'sync_lms',
                    'description' => 'SPADA sync completed successfully',
                    'metadata' => [
                        'courses' => $coursesCount,
                        'assignments' => $assignmentsCount,
                        'duration_seconds' => now()->diffInSeconds($startTime),
                    ],
                ]);

                $this->info("  ✅ Sync completed for {$user->name}");

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                $errors[] = "{$user->email}: {$e->getMessage()}";
                
                Activity::create([
                    'user_id' => $user->id,
                    'type' => 'sync_lms_failed',
                    'description' => 'SPADA sync failed',
                    'metadata' => ['error' => $e->getMessage()],
                ]);
            }
        }

        $duration = now()->diffInSeconds($startTime);
        
        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("✨ Sync Summary");
        $this->info("═══════════════════════════════════════");
        $this->info("👥 Users processed: {$users->count()}");
        $this->info("📚 Total courses synced: {$totalCourses}");
        $this->info("📝 Total assignments synced: {$totalAssignments}");
        $this->info("⏱️  Duration: {$duration} seconds");
        
        if (count($errors) > 0) {
            $this->newLine();
            $this->error("❌ Errors ({" . count($errors) . "}):");
            foreach ($errors as $error) {
                $this->error("  • {$error}");
            }
        }
        
        $this->info("═══════════════════════════════════════");

        return 0;
    }

    /**
     * Sync courses from SPADA
     */
    private function syncCourses(User $user, array $spadaCourses): int
    {
        $count = 0;

        foreach ($spadaCourses as $spadaCourse) {
            // Check if course already exists via LMS map
            $lmsMap = LmsMap::where('lms_platform', 'spada_polinema')
                ->where('external_id', $spadaCourse['id'])
                ->where('mappable_type', Course::class)
                ->first();

            if ($lmsMap) {
                // Update existing course
                $course = $lmsMap->mappable;
                $course->update([
                    'name' => $spadaCourse['name'],
                    'is_active' => true,
                ]);

                $lmsMap->update(['last_synced_at' => now()]);
            } else {
                // Create new course
                $course = Course::create([
                    'code' => $spadaCourse['code'] ?? 'SPADA-' . substr($spadaCourse['id'], 0, 6),
                    'name' => $spadaCourse['name'],
                    'lecturer_id' => $user->role === 'dosen' ? $user->id : 1, // Default to admin if student
                    'semester' => $spadaCourse['semester'] ?? '2024/2025 Genap',
                    'class' => $spadaCourse['class'] ?? 'A',
                    'description' => $spadaCourse['description'] ?? null,
                    'color' => $this->generateColor(),
                    'is_active' => true,
                ]);

                // Create LMS mapping
                $course->lmsMaps()->create([
                    'lms_platform' => 'spada_polinema',
                    'external_id' => $spadaCourse['id'],
                    'external_url' => $spadaCourse['url'] ?? null,
                    'last_synced_at' => now(),
                ]);

                // Enroll user if student
                if ($user->role === 'mahasiswa') {
                    $course->students()->attach($user->id);
                }
            }

            $count++;
        }

        return $count;
    }

    /**
     * Sync assignments from SPADA
     */
    private function syncAssignments(User $user, string $externalCourseId, array $spadaAssignments): int
    {
        $count = 0;

        // Find course by external ID
        $lmsMap = LmsMap::where('lms_platform', 'spada_polinema')
            ->where('external_id', $externalCourseId)
            ->where('mappable_type', Course::class)
            ->first();

        if (!$lmsMap) {
            return 0;
        }

        $course = $lmsMap->mappable;

        foreach ($spadaAssignments as $spadaAssignment) {
            // Check if assignment exists
            $assignmentMap = LmsMap::where('lms_platform', 'spada_polinema')
                ->where('external_id', $spadaAssignment['id'])
                ->where('mappable_type', Assignment::class)
                ->first();

            $dueAt = isset($spadaAssignment['due_date']) 
                ? Carbon::parse($spadaAssignment['due_date']) 
                : null;

            if ($assignmentMap) {
                // Update existing
                $assignment = $assignmentMap->mappable;
                $assignment->update([
                    'title' => $spadaAssignment['title'],
                    'description' => $spadaAssignment['description'] ?? null,
                    'due_at' => $dueAt,
                    'status' => $this->determineStatus($dueAt),
                    'lms_url' => $spadaAssignment['url'] ?? null,
                ]);

                $assignmentMap->update(['last_synced_at' => now()]);
            } else {
                // Create new
                $assignment = Assignment::create([
                    'course_id' => $course->id,
                    'title' => $spadaAssignment['title'],
                    'description' => $spadaAssignment['description'] ?? null,
                    'due_at' => $dueAt,
                    'status' => $this->determineStatus($dueAt),
                    'priority' => $this->determinePriority($dueAt),
                    'effort_mins' => 120, // Default
                    'impact' => 70, // Default
                    'tag' => $spadaAssignment['type'] ?? 'tugas',
                    'lms_url' => $spadaAssignment['url'] ?? null,
                    'max_score' => $spadaAssignment['max_score'] ?? 100,
                ]);

                // Create LMS mapping
                $assignment->lmsMaps()->create([
                    'lms_platform' => 'spada_polinema',
                    'external_id' => $spadaAssignment['id'],
                    'external_url' => $spadaAssignment['url'] ?? null,
                    'last_synced_at' => now(),
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Determine assignment status based on due date
     */
    private function determineStatus($dueAt): string
    {
        if (!$dueAt) {
            return 'published';
        }

        return $dueAt->isPast() ? 'closed' : 'published';
    }

    /**
     * Determine priority based on due date
     */
    private function determinePriority($dueAt): string
    {
        if (!$dueAt) {
            return 'medium';
        }

        $daysUntilDue = now()->diffInDays($dueAt, false);

        if ($daysUntilDue < 0) {
            return 'low'; // Past due
        } elseif ($daysUntilDue <= 3) {
            return 'high'; // Urgent
        } elseif ($daysUntilDue <= 7) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Generate random color for course
     */
    private function generateColor(): string
    {
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EC4899'];
        return $colors[array_rand($colors)];
    }
}
