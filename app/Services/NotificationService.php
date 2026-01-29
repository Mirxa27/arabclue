<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Property;
use App\Notifications\WelcomeNotification;
use App\Notifications\PropertyApprovalNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\ReviewRequestNotification;
use App\Notifications\BookingReminderNotification;
use App\Notifications\HostPayoutNotification;
use App\Notifications\SystemMaintenanceNotification;
use App\Notifications\NewBookingNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send welcome notification to new user
     */
    public function sendWelcomeNotification(User $user): void
    {
        try {
            $user->notify(new WelcomeNotification($user));
            \Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user));
            Log::info('Welcome notification sent', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send property approval/rejection notification
     */
    public function sendPropertyApprovalNotification(Property $property, bool $approved): void
    {
        try {
            $property->user->notify(new PropertyApprovalNotification($property, $approved));
            Log::info('Property approval notification sent', [
                'property_id' => $property->id,
                'host_id' => $property->user_id,
                'approved' => $approved
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send property approval notification', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send payment confirmation notification
     */
    public function sendPaymentConfirmationNotification(Booking $booking): void
    {
        try {
            $booking->user->notify(new PaymentConfirmationNotification($booking));
            Log::info('Payment confirmation notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send new booking notification to host
     */
    public function sendNewBookingNotification(Booking $booking): void
    {
        try {
            $booking->property->user->notify(new NewBookingNotification($booking));
            Log::info('New booking notification sent to host', [
                'booking_id' => $booking->id,
                'host_id' => $booking->property->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new booking notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send booking reminder notification
     */
    public function sendBookingReminderNotification(Booking $booking, string $reminderType = 'check_in'): void
    {
        try {
            $booking->user->notify(new BookingReminderNotification($booking, $reminderType));
            Log::info('Booking reminder notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'reminder_type' => $reminderType
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking reminder notification', [
                'booking_id' => $booking->id,
                'reminder_type' => $reminderType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send review request notification
     */
    public function sendReviewRequestNotification(Booking $booking): void
    {
        try {
            $booking->user->notify(new ReviewRequestNotification($booking));
            Log::info('Review request notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send review request notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send host payout notification
     */
    public function sendHostPayoutNotification(Booking $booking, float $payoutAmount, $payoutDate = null): void
    {
        try {
            $booking->property->user->notify(new HostPayoutNotification($booking, $payoutAmount, $payoutDate));
            Log::info('Host payout notification sent', [
                'booking_id' => $booking->id,
                'host_id' => $booking->property->user_id,
                'payout_amount' => $payoutAmount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send host payout notification', [
                'booking_id' => $booking->id,
                'payout_amount' => $payoutAmount,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send booking cancellation notification
     */
    public function sendBookingCancelledNotification(Booking $booking): void
    {
        try {
            // Notify guest
            $booking->user->notify(new BookingCancelledNotification($booking));
            
            // Notify host
            $booking->property->user->notify(new BookingCancelledNotification($booking));
            
            Log::info('Booking cancellation notifications sent', [
                'booking_id' => $booking->id,
                'guest_id' => $booking->user_id,
                'host_id' => $booking->property->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking cancellation notifications', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send system maintenance notification to all users
     */
    public function sendSystemMaintenanceNotification(
        $maintenanceStart,
        $maintenanceEnd,
        string $maintenanceType = 'scheduled',
        array $affectedServices = [],
        array $userIds = null
    ): void {
        try {
            $query = User::query();
            
            // If specific user IDs provided, filter to those users
            if ($userIds !== null) {
                $query->whereIn('id', $userIds);
            }
            
            // Send to users in chunks to avoid memory issues
            $query->chunk(100, function ($users) use ($maintenanceStart, $maintenanceEnd, $maintenanceType, $affectedServices) {
                Notification::send($users, new SystemMaintenanceNotification(
                    $maintenanceStart,
                    $maintenanceEnd,
                    $maintenanceType,
                    $affectedServices
                ));
            });
            
            Log::info('System maintenance notifications sent', [
                'maintenance_type' => $maintenanceType,
                'start' => $maintenanceStart,
                'end' => $maintenanceEnd,
                'affected_services' => $affectedServices
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send system maintenance notifications', [
                'maintenance_type' => $maintenanceType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send bulk notifications to multiple users
     */
    public function sendBulkNotification($notification, array $userIds): void
    {
        try {
            $users = User::whereIn('id', $userIds)->get();
            Notification::send($users, $notification);
            
            Log::info('Bulk notification sent', [
                'notification_type' => get_class($notification),
                'user_count' => count($userIds)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk notification', [
                'notification_type' => get_class($notification),
                'user_count' => count($userIds),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule booking reminders for upcoming bookings
     */
    public function scheduleBookingReminders(): void
    {
        try {
            // Check-in reminders (1 day before)
            $checkInBookings = Booking::where('status', 'confirmed')
                ->whereDate('check_in', now()->addDay()->toDateString())
                ->with(['user', 'property'])
                ->get();

            foreach ($checkInBookings as $booking) {
                $this->sendBookingReminderNotification($booking, 'check_in');
            }

            // Check-out reminders (same day)
            $checkOutBookings = Booking::where('status', 'confirmed')
                ->whereDate('check_out', now()->toDateString())
                ->with(['user', 'property'])
                ->get();

            foreach ($checkOutBookings as $booking) {
                $this->sendBookingReminderNotification($booking, 'check_out');
            }

            // Upcoming stay reminders (3 days before)
            $upcomingBookings = Booking::where('status', 'confirmed')
                ->whereDate('check_in', now()->addDays(3)->toDateString())
                ->with(['user', 'property'])
                ->get();

            foreach ($upcomingBookings as $booking) {
                $this->sendBookingReminderNotification($booking, 'upcoming');
            }

            Log::info('Booking reminders scheduled', [
                'check_in_reminders' => $checkInBookings->count(),
                'check_out_reminders' => $checkOutBookings->count(),
                'upcoming_reminders' => $upcomingBookings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule booking reminders', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule review requests for completed bookings
     */
    public function scheduleReviewRequests(): void
    {
        try {
            // Send review requests 1 day after checkout
            $completedBookings = Booking::where('status', 'confirmed')
                ->whereDate('check_out', now()->subDay()->toDateString())
                ->whereDoesntHave('reviews', function ($query) {
                    $query->where('reviewer_type', 'guest');
                })
                ->with(['user', 'property'])
                ->get();

            foreach ($completedBookings as $booking) {
                $this->sendReviewRequestNotification($booking);
            }

            Log::info('Review requests scheduled', [
                'review_requests_sent' => $completedBookings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule review requests', [
                'error' => $e->getMessage()
            ]);
        }
    }
    public function sendDisputeCreatedNotification(\App\Models\Dispute $dispute): void
    {
        // Placeholder implementation
    }

    public function sendDisputeMessageNotification(\App\Models\Dispute $dispute, \App\Models\DisputeMessage $message): void
    {
        // Placeholder implementation
    }

    public function sendDisputeResolvedNotification(\App\Models\Dispute $dispute, ?array $refundResult): void
    {
        // Placeholder implementation
    }
}
