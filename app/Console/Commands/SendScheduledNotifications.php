<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled 
                            {--type=all : Type of notifications to send (all, reminders, reviews)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications (booking reminders, review requests, etc.)';

    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No notifications will be sent');
        }

        $this->info('🚀 Starting scheduled notifications...');

        try {
            switch ($type) {
                case 'reminders':
                    $this->sendBookingReminders($dryRun);
                    break;
                
                case 'reviews':
                    $this->sendReviewRequests($dryRun);
                    break;
                
                case 'all':
                default:
                    $this->sendBookingReminders($dryRun);
                    $this->sendReviewRequests($dryRun);
                    break;
            }

            $this->info('✅ Scheduled notifications completed successfully');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to send scheduled notifications: ' . $e->getMessage());
            Log::error('Scheduled notifications command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Send booking reminder notifications
     */
    protected function sendBookingReminders(bool $dryRun): void
    {
        $this->info('📅 Processing booking reminders...');

        if ($dryRun) {
            $this->showBookingReminderPreview();
            return;
        }

        $this->notificationService->scheduleBookingReminders();
        $this->info('✅ Booking reminders processed');
    }

    /**
     * Send review request notifications
     */
    protected function sendReviewRequests(bool $dryRun): void
    {
        $this->info('⭐ Processing review requests...');

        if ($dryRun) {
            $this->showReviewRequestPreview();
            return;
        }

        $this->notificationService->scheduleReviewRequests();
        $this->info('✅ Review requests processed');
    }

    /**
     * Show preview of booking reminders that would be sent
     */
    protected function showBookingReminderPreview(): void
    {
        // Check-in reminders (1 day before)
        $checkInBookings = \App\Models\Booking::where('status', 'confirmed')
            ->whereDate('check_in', now()->addDay()->toDateString())
            ->with(['user', 'property'])
            ->get();

        // Check-out reminders (same day)
        $checkOutBookings = \App\Models\Booking::where('status', 'confirmed')
            ->whereDate('check_out', now()->toDateString())
            ->with(['user', 'property'])
            ->get();

        // Upcoming stay reminders (3 days before)
        $upcomingBookings = \App\Models\Booking::where('status', 'confirmed')
            ->whereDate('check_in', now()->addDays(3)->toDateString())
            ->with(['user', 'property'])
            ->get();

        $this->table(
            ['Type', 'Count', 'Details'],
            [
                ['Check-in Reminders', $checkInBookings->count(), 'Bookings checking in tomorrow'],
                ['Check-out Reminders', $checkOutBookings->count(), 'Bookings checking out today'],
                ['Upcoming Stay Reminders', $upcomingBookings->count(), 'Bookings starting in 3 days'],
            ]
        );

        if ($checkInBookings->count() > 0) {
            $this->info('Check-in reminders would be sent to:');
            foreach ($checkInBookings as $booking) {
                $this->line("  - {$booking->user->name} ({$booking->user->email}) - {$booking->property->title}");
            }
        }

        if ($checkOutBookings->count() > 0) {
            $this->info('Check-out reminders would be sent to:');
            foreach ($checkOutBookings as $booking) {
                $this->line("  - {$booking->user->name} ({$booking->user->email}) - {$booking->property->title}");
            }
        }

        if ($upcomingBookings->count() > 0) {
            $this->info('Upcoming stay reminders would be sent to:');
            foreach ($upcomingBookings as $booking) {
                $this->line("  - {$booking->user->name} ({$booking->user->email}) - {$booking->property->title}");
            }
        }
    }

    /**
     * Show preview of review requests that would be sent
     */
    protected function showReviewRequestPreview(): void
    {
        $completedBookings = \App\Models\Booking::where('status', 'confirmed')
            ->whereDate('check_out', now()->subDay()->toDateString())
            ->whereDoesntHave('reviews', function ($query) {
                $query->where('reviewer_type', 'guest');
            })
            ->with(['user', 'property'])
            ->get();

        $this->table(
            ['Type', 'Count', 'Details'],
            [
                ['Review Requests', $completedBookings->count(), 'Completed bookings without guest reviews'],
            ]
        );

        if ($completedBookings->count() > 0) {
            $this->info('Review requests would be sent to:');
            foreach ($completedBookings as $booking) {
                $this->line("  - {$booking->user->name} ({$booking->user->email}) - {$booking->property->title}");
            }
        }
    }
}
