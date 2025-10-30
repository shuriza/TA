<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function create(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Notify students about new assignment
     */
    public function notifyNewAssignment(Assignment $assignment): void
    {
        $students = $assignment->course->enrolledStudents;
        
        foreach ($students as $student) {
            $this->create(
                $student,
                'new_assignment',
                'Tugas Baru: ' . $assignment->title,
                "Tugas baru telah ditambahkan untuk mata kuliah {$assignment->course->name}. Deadline: {$assignment->due_at->format('d M Y, H:i')}",
                [
                    'assignment_id' => $assignment->id,
                    'course_id' => $assignment->course_id,
                    'deadline' => $assignment->due_at->toISOString(),
                ]
            );
        }
    }

    /**
     * Notify lecturer about new submission
     */
    public function notifyNewSubmission(Submission $submission): void
    {
        $lecturer = $submission->assignment->course->lecturer;
        
        $this->create(
            $lecturer,
            'submission_received',
            'Pengumpulan Baru: ' . $submission->assignment->title,
            "{$submission->user->name} telah mengumpulkan tugas {$submission->assignment->title}",
            [
                'submission_id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'student_id' => $submission->user_id,
                'student_name' => $submission->user->name,
            ]
        );
    }

    /**
     * Notify student about grade update
     */
    public function notifyGradeUpdated(Submission $submission): void
    {
        $this->create(
            $submission->user,
            'grade_updated',
            'Tugas Dinilai: ' . $submission->assignment->title,
            "Tugas Anda telah dinilai. Nilai: {$submission->score}/{$submission->assignment->max_score}",
            [
                'submission_id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'score' => $submission->score,
                'max_score' => $submission->assignment->max_score,
            ]
        );
    }

    /**
     * Notify students about upcoming deadline (H-3, H-1)
     */
    public function notifyDeadlineReminder(Assignment $assignment, int $hoursRemaining): void
    {
        $students = $assignment->course->enrolledStudents()
            ->whereDoesntHave('submissions', function ($query) use ($assignment) {
                $query->where('assignment_id', $assignment->id);
            })
            ->get();

        $days = round($hoursRemaining / 24);
        $timeText = $days > 0 ? "H-{$days}" : "{$hoursRemaining} jam lagi";

        foreach ($students as $student) {
            $this->create(
                $student,
                'deadline_reminder',
                '⏰ Deadline Mendekat: ' . $assignment->title,
                "Deadline tugas {$assignment->title} ({$assignment->course->name}) tinggal {$timeText}!",
                [
                    'assignment_id' => $assignment->id,
                    'course_id' => $assignment->course_id,
                    'deadline' => $assignment->due_at->toISOString(),
                    'hours_remaining' => $hoursRemaining,
                ]
            );
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnread(User $user): Collection
    {
        return $user->notifications()
            ->unread()
            ->recent()
            ->limit(50)
            ->get();
    }

    /**
     * Get all notifications for a user (paginated)
     */
    public function getAll(User $user, int $perPage = 20)
    {
        return $user->notifications()
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Delete old read notifications (cleanup)
     */
    public function deleteOldReadNotifications(int $daysOld = 30): int
    {
        return Notification::read()
            ->where('read_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
