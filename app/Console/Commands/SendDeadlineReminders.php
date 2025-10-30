<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminders for upcoming assignments';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for upcoming assignment deadlines...');

        $now = Carbon::now();
        $reminders = [
            72 => 'H-3',  // 3 days before
            24 => 'H-1',  // 1 day before
            6 => '6 jam', // 6 hours before
        ];

        $totalSent = 0;

        foreach ($reminders as $hours => $label) {
            $targetTime = $now->copy()->addHours($hours);
            $startWindow = $targetTime->copy()->subMinutes(30);
            $endWindow = $targetTime->copy()->addMinutes(30);

            // Find assignments with deadlines in this window
            $assignments = Assignment::where('status', 'published')
                ->whereBetween('due_at', [$startWindow, $endWindow])
                ->get();

            foreach ($assignments as $assignment) {
                // Check if reminder already sent for this time window
                $alreadySent = \App\Models\Notification::where('type', 'deadline_reminder')
                    ->where('data->assignment_id', $assignment->id)
                    ->where('data->hours_remaining', $hours)
                    ->where('created_at', '>', $now->copy()->subHours(1))
                    ->exists();

                if (!$alreadySent) {
                    $this->notificationService->notifyDeadlineReminder($assignment, $hours);
                    $this->line("  ✓ Sent {$label} reminder for: {$assignment->title}");
                    $totalSent++;
                }
            }
        }

        $this->info("Total reminders sent: {$totalSent}");
        return 0;
    }
}
