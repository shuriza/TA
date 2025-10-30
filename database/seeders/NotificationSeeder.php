<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first student
        $student = User::where('role', 'mahasiswa')->first();
        
        if (!$student) {
            $this->command->warn('No student found. Skipping notification seeder.');
            return;
        }

        // Get first assignment
        $assignment = Assignment::first();
        
        if (!$assignment) {
            $this->command->warn('No assignment found. Skipping notification seeder.');
            return;
        }

        // Create sample notifications for student
        Notification::create([
            'user_id' => $student->id,
            'type' => 'new_assignment',
            'title' => 'Tugas Baru: ' . $assignment->title,
            'message' => "Tugas baru telah ditambahkan untuk mata kuliah {$assignment->course->name}. Deadline: " . $assignment->due_at->format('d M Y, H:i'),
            'data' => [
                'assignment_id' => $assignment->id,
                'course_id' => $assignment->course_id,
                'deadline' => $assignment->due_at->toISOString(),
            ],
        ]);

        Notification::create([
            'user_id' => $student->id,
            'type' => 'deadline_reminder',
            'title' => '⏰ Deadline Mendekat: ' . $assignment->title,
            'message' => "Deadline tugas {$assignment->title} ({$assignment->course->name}) tinggal H-3!",
            'data' => [
                'assignment_id' => $assignment->id,
                'course_id' => $assignment->course_id,
                'deadline' => $assignment->due_at->toISOString(),
                'hours_remaining' => 72,
            ],
            'read_at' => now()->subDay(), // Mark as read
        ]);

        // Get first lecturer
        $lecturer = User::where('role', 'dosen')->first();
        
        if ($lecturer) {
            Notification::create([
                'user_id' => $lecturer->id,
                'type' => 'submission_received',
                'title' => 'Pengumpulan Baru: ' . $assignment->title,
                'message' => "{$student->name} telah mengumpulkan tugas {$assignment->title}",
                'data' => [
                    'submission_id' => 1,
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                ],
            ]);
        }

        $this->command->info('Sample notifications created successfully!');
    }
}
