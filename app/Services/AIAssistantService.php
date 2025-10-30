<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenAI\Laravel\Facades\OpenAI;

class AIAssistantService
{
    /**
     * Get assignment recommendations for a student
     */
    public function getAssignmentRecommendations(User $user): array
    {
        // Get student's assignments
        $assignments = Assignment::whereHas('course.students', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'published')
        ->with(['course', 'submissions' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->get();

        // Filter not submitted
        $notSubmitted = $assignments->filter(function($assignment) {
            return $assignment->submissions->isEmpty();
        });

        // Prepare context for AI
        $context = $this->prepareAssignmentContext($notSubmitted);

        $prompt = <<<EOT
Kamu adalah asisten AI untuk mahasiswa. Analisis daftar tugas berikut dan berikan rekomendasi prioritas pengerjaan.

Data Tugas:
{$context}

Berikan rekomendasi dalam format JSON dengan struktur:
{
  "recommendations": [
    {
      "assignment_id": int,
      "priority_level": "critical|high|medium|low",
      "reason": "string (penjelasan kenapa harus diprioritaskan)",
      "estimated_time": "string (estimasi waktu pengerjaan)",
      "tips": ["tip1", "tip2"]
    }
  ],
  "summary": "string (ringkasan kondisi deadline mahasiswa)",
  "study_plan": "string (saran jadwal belajar)"
}

Fokus pada:
1. Deadline yang paling dekat
2. Bobot/impact tugas
3. Kompleksitas (effort)
4. Distribusi waktu yang seimbang
EOT;

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah AI assistant untuk manajemen tugas kuliah mahasiswa. Selalu berikan rekomendasi yang praktis dan realistis.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

            $result = json_decode($response->choices[0]->message->content, true);
            return $result;
        } catch (\Exception $e) {
            // Fallback if API fails
            return $this->getFallbackRecommendations($notSubmitted);
        }
    }

    /**
     * Get study plan suggestions
     */
    public function getStudyPlan(User $user, int $daysAhead = 7): array
    {
        $assignments = Assignment::whereHas('course.students', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'published')
        ->whereBetween('due_at', [now(), now()->addDays($daysAhead)])
        ->with('course')
        ->get();

        $context = <<<EOT
Mahasiswa memiliki {$assignments->count()} tugas dalam {$daysAhead} hari ke depan:

EOT;

        foreach ($assignments as $assignment) {
            $context .= "- {$assignment->title} ({$assignment->course->name})\n";
            $context .= "  Deadline: {$assignment->due_at->format('d M Y H:i')}\n";
            $context .= "  Estimasi: {$assignment->effort_mins} menit\n\n";
        }

        $prompt = <<<EOT
{$context}

Buatkan jadwal belajar yang realistis dalam format JSON:
{
  "daily_plan": [
    {
      "day": "Senin, 29 Okt",
      "tasks": [
        {
          "time": "14:00-16:00",
          "assignment": "string",
          "activity": "string"
        }
      ],
      "total_hours": float
    }
  ],
  "tips": ["tip1", "tip2"],
  "warning": "string jika overload"
}

Pastikan:
1. Seimbang (max 4 jam/hari)
2. Beri waktu istirahat
3. Prioritas deadline dekat
4. Sisakan buffer time
EOT;

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah AI study planner yang memahami beban mahasiswa.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            return $this->getFallbackStudyPlan($assignments);
        }
    }

    /**
     * Chat with AI about assignments
     */
    public function chat(User $user, string $message, array $context = []): string
    {
        $systemContext = $this->getUserContext($user);

        $messages = [
            ['role' => 'system', 'content' => "Kamu adalah AI assistant untuk mahasiswa. Berikut konteks mahasiswa:\n\n{$systemContext}"],
        ];

        // Add conversation history
        foreach ($context as $msg) {
            $messages[] = $msg;
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => 0.8,
                'max_tokens' => 500,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return "Maaf, saya sedang mengalami gangguan. Silakan coba lagi nanti.";
        }
    }

    /**
     * Analyze submission patterns and give insights
     */
    public function getStudentInsights(User $user): array
    {
        $submissions = $user->submissions()
            ->with('assignment')
            ->where('status', 'graded')
            ->get();

        if ($submissions->isEmpty()) {
            return [
                'message' => 'Belum ada data submission yang cukup untuk analisis.',
                'insights' => [],
            ];
        }

        $avgScore = $submissions->avg('score');
        $onTime = $submissions->filter(function($s) {
            return $s->submitted_at <= $s->assignment->due_at;
        })->count();
        $late = $submissions->count() - $onTime;

        $context = <<<EOT
Data Submission Mahasiswa:
- Total tugas selesai: {$submissions->count()}
- Rata-rata nilai: {$avgScore}
- On time: {$onTime}
- Terlambat: {$late}

Mata kuliah dengan nilai tertinggi:
EOT;

        $byCourse = $submissions->groupBy('assignment.course.name');
        foreach ($byCourse as $course => $subs) {
            $avg = $subs->avg('score');
            $context .= "\n- {$course}: {$avg}";
        }

        $prompt = <<<EOT
{$context}

Berikan insights dalam format JSON:
{
  "overall_performance": "string (excellent|good|needs_improvement)",
  "strengths": ["string"],
  "weaknesses": ["string"],
  "recommendations": ["string"],
  "study_tips": ["string"]
}
EOT;

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah AI academic advisor yang menganalisis performa mahasiswa.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

            return json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            return [
                'message' => 'Tidak dapat menganalisis data saat ini.',
                'insights' => [],
            ];
        }
    }

    /**
     * Prepare assignment context for AI
     */
    private function prepareAssignmentContext(Collection $assignments): string
    {
        $context = '';
        foreach ($assignments as $assignment) {
            $daysUntil = now()->diffInDays($assignment->due_at, false);
            $context .= "ID: {$assignment->id}\n";
            $context .= "Judul: {$assignment->title}\n";
            $context .= "Mata Kuliah: {$assignment->course->name}\n";
            $context .= "Deadline: {$assignment->due_at->format('d M Y H:i')} ({$daysUntil} hari lagi)\n";
            $context .= "Priority: {$assignment->priority}\n";
            $context .= "Impact: {$assignment->impact}/100\n";
            $context .= "Effort: {$assignment->effort_mins} menit\n";
            $context .= "---\n";
        }
        return $context;
    }

    /**
     * Get user context for chat
     */
    private function getUserContext(User $user): string
    {
        $assignmentCount = Assignment::whereHas('course.students', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'published')->count();

        $submissionCount = $user->submissions()->count();

        return <<<EOT
Nama: {$user->name}
Role: {$user->role}
Total Tugas Aktif: {$assignmentCount}
Total Submission: {$submissionCount}
EOT;
    }

    /**
     * Fallback recommendations without AI
     */
    private function getFallbackRecommendations(Collection $assignments): array
    {
        $recommendations = [];

        foreach ($assignments as $assignment) {
            $daysUntil = now()->diffInDays($assignment->due_at, false);
            
            if ($daysUntil <= 1) {
                $priority = 'critical';
            } elseif ($daysUntil <= 3) {
                $priority = 'high';
            } elseif ($daysUntil <= 7) {
                $priority = 'medium';
            } else {
                $priority = 'low';
            }

            $recommendations[] = [
                'assignment_id' => $assignment->id,
                'priority_level' => $priority,
                'reason' => "Deadline dalam {$daysUntil} hari",
                'estimated_time' => $assignment->effort_mins ? "{$assignment->effort_mins} menit" : "Tidak diketahui",
                'tips' => ["Kerjakan sesegera mungkin", "Baca deskripsi dengan teliti"],
            ];
        }

        return [
            'recommendations' => $recommendations,
            'summary' => "Anda memiliki {$assignments->count()} tugas yang belum dikerjakan.",
            'study_plan' => "Prioritaskan tugas dengan deadline terdekat.",
        ];
    }

    /**
     * Fallback study plan without AI
     */
    private function getFallbackStudyPlan(Collection $assignments): array
    {
        $plan = [];
        $currentDate = now();

        foreach (range(0, 6) as $day) {
            $date = $currentDate->copy()->addDays($day);
            $dayAssignments = $assignments->filter(function($a) use ($date) {
                return $a->due_at->isSameDay($date);
            });

            $plan[] = [
                'day' => $date->translatedFormat('l, d M'),
                'tasks' => $dayAssignments->map(function($a) {
                    return [
                        'time' => '09:00-11:00',
                        'assignment' => $a->title,
                        'activity' => 'Kerjakan tugas',
                    ];
                })->toArray(),
                'total_hours' => $dayAssignments->count() * 2,
            ];
        }

        return [
            'daily_plan' => $plan,
            'tips' => ['Kerjakan tugas secara konsisten', 'Jangan menunda'],
            'warning' => null,
        ];
    }
}
