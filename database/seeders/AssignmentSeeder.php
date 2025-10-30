<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // Assignment 1: Urgent & Important (H-3)
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Quiz ' . $course->name,
                'description' => 'Quiz tentang materi minggu 1-4',
                'due_at' => Carbon::now()->addDays(3),
                'status' => 'published',
                'priority' => 'high',
                'effort_mins' => 60,
                'impact' => 90,
                'tag' => 'quiz',
                'lms_url' => 'https://slc.polinema.ac.id/spada/mod/quiz/view.php?id=' . rand(1000, 9999),
                'allow_late_submission' => false,
                'max_score' => 100,
            ]);

            // Assignment 2: Important (H-7)
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Tugas Praktikum ' . $course->name,
                'description' => 'Implementasi project sesuai modul praktikum',
                'due_at' => Carbon::now()->addDays(7),
                'status' => 'published',
                'priority' => 'high',
                'effort_mins' => 180,
                'impact' => 85,
                'tag' => 'praktikum',
                'lms_url' => 'https://slc.polinema.ac.id/spada/mod/assign/view.php?id=' . rand(1000, 9999),
                'allow_late_submission' => true,
                'max_score' => 100,
            ]);

            // Assignment 3: Normal (H-14)
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Diskusi Forum ' . $course->name,
                'description' => 'Partisipasi dalam forum diskusi mingguan',
                'due_at' => Carbon::now()->addDays(14),
                'status' => 'published',
                'priority' => 'medium',
                'effort_mins' => 30,
                'impact' => 50,
                'tag' => 'diskusi',
                'lms_url' => 'https://slc.polinema.ac.id/spada/mod/forum/view.php?id=' . rand(1000, 9999),
                'allow_late_submission' => true,
                'max_score' => 20,
            ]);
        }

        // Add some completed assignments
        $course1 = $courses->first();
        Assignment::create([
            'course_id' => $course1->id,
            'title' => 'Tugas Mingguan Selesai',
            'description' => 'Tugas yang sudah dikumpulkan',
            'due_at' => Carbon::now()->subDays(2),
            'status' => 'closed',
            'priority' => 'medium',
            'effort_mins' => 120,
            'impact' => 60,
            'tag' => 'tugas',
            'lms_url' => 'https://slc.polinema.ac.id/spada/mod/assign/view.php?id=' . rand(1000, 9999),
            'allow_late_submission' => true,
            'max_score' => 100,
        ]);
    }
}
