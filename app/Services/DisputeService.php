<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DisputeService
{
    protected $notificationService;
    protected $paymentService;

    public function __construct(NotificationService $notificationService, PaymentService $paymentService)
    {
        $this->notificationService = $notificationService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create a new dispute
     */
    public function createDispute(Booking $booking, array $data, User $user): Dispute
    {
        // Validate 14-day window
        $this->validateDisputeTimeWindow($booking);

        // Create the dispute
        $dispute = $booking->disputes()->create([
            'user_id' => $user->id,
            'dispute_type' => $data['dispute_type'],
            'reason' => $data['reason'],
            'amount_disputed' => $data['amount_disputed'] ?? 0,
            'evidence_description' => $data['evidence_description'] ?? null,
            'status' => 'open',
            'priority' => $this->calculatePriority($data, $booking),
            'admin_assigned_id' => $this->autoAssignAdmin(),
        ]);

        // Create initial message
        $initialMessage = $dispute->messages()->create([
            'user_id' => $user->id,
            'message' => $data['reason'],
            'message_type' => 'initial_claim',
        ]);

        // Handle file attachments if any
        if (!empty($data['attachments'])) {
            $this->handleAttachments($data['attachments'], $dispute, $initialMessage);
        }

        // Send notifications
        $this->sendDisputeCreatedNotifications($dispute);

        return $dispute->load(['booking.property', 'messages.user', 'adminAssigned']);
    }

    /**
     * Add message to dispute
     */
    public function addMessage(Dispute $dispute, array $data, User $user): DisputeMessage
    {
        $message = $dispute->messages()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'message_type' => 'response',
        ]);

        // Handle file attachments if any
        if (!empty($data['attachments'])) {
            $this->handleAttachments($data['attachments'], $dispute, $message);
        }

        // Send notifications to relevant parties
        $this->sendMessageNotifications($dispute, $message);

        return $message->load('user');
    }

    /**
     * Resolve dispute with refund processing
     */
    public function resolveDispute(Dispute $dispute, array $data, User $admin): Dispute
    {
        // Process refund if applicable
        $refundResult = null;
        if (in_array($data['resolution'], ['full_refund', 'partial_refund']) && $data['refund_amount'] > 0) {
            $refundResult = $this->processDisputeRefund($dispute, $data['refund_amount']);
        }

        // Update dispute status
        $dispute->update([
            'status' => 'resolved',
            'resolution' => $data['resolution'],
            'refund_amount' => $data['refund_amount'] ?? 0,
            'resolution_notes' => $data['resolution_notes'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'resolved_at' => now(),
            'resolved_by' => $admin->id,
        ]);

        // Create resolution message
        $dispute->messages()->create([
            'user_id' => $admin->id,
            'message' => $data['resolution_notes'],
            'message_type' => 'resolution',
            'is_admin' => true,
        ]);

        // Send resolution notifications
        $this->sendResolutionNotifications($dispute, $refundResult);

        return $dispute->fresh()->load(['messages.user', 'booking']);
    }

    /**
     * Validate 14-day dispute window
     */
    private function validateDisputeTimeWindow(Booking $booking): void
    {
        if (!$booking->checkout_date) {
            throw ValidationException::withMessages([
                'booking' => ['Cannot create dispute for booking without checkout date']
            ]);
        }

        $disputeDeadline = $booking->checkout_date->addDays(14);
        
        if (now()->isAfter($disputeDeadline)) {
            throw ValidationException::withMessages([
                'time_limit' => ['Disputes must be raised within 14 days of checkout. Deadline was ' . $disputeDeadline->format('M j, Y')]
            ]);
        }
    }

    /**
     * Calculate dispute priority based on type and amount
     */
    private function calculatePriority(array $data, Booking $booking): string
    {
        $highPriorityTypes = ['safety', 'damage_claim'];
        $amount = $data['amount_disputed'] ?? 0;

        if (in_array($data['dispute_type'], $highPriorityTypes)) {
            return 'urgent';
        }

        if ($amount > $booking->total_amount * 0.5) {
            return 'high';
        }

        if ($amount > 100) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Auto-assign admin based on workload
     */
    private function autoAssignAdmin(): ?int
    {
        $admin = User::role('admin')
            ->withCount(['assignedDisputes' => function($query) {
                $query->whereIn('status', ['open', 'in_review']);
            }])
            ->orderBy('assigned_disputes_count')
            ->first();

        return $admin?->id;
    }

    /**
     * Handle file attachments
     */
    private function handleAttachments(array $files, Dispute $dispute, DisputeMessage $message): void
    {
        foreach ($files as $file) {
            $path = $file->store('disputes/' . $dispute->id, 'private');
            
            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'user_id' => $message->user_id,
                'message' => 'File attachment: ' . $file->getClientOriginalName(),
                'message_type' => 'attachment',
                'attachment_path' => $path,
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_size' => $file->getSize(),
                'attachment_mime' => $file->getMimeType(),
            ]);
        }
    }

    /**
     * Process dispute refund
     */
    private function processDisputeRefund(Dispute $dispute, float $amount): array
    {
        $booking = $dispute->booking;
        
        try {
            if ($booking->payment_method === 'paypal') {
                return $this->paymentService->processRefund(
                    $booking->payment_transaction_id,
                    $amount,
                    'Dispute resolution refund'
                );
            } elseif ($booking->payment_method === 'myfatoorah') {
                return $this->paymentService->processRefund(
                    $booking->payment_transaction_id,
                    $amount,
                    'Dispute resolution refund'
                );
            }

            throw new \Exception('Unsupported payment method for refund');

        } catch (\Exception $e) {
            Log::error('Dispute refund failed', [
                'dispute_id' => $dispute->id,
                'booking_id' => $booking->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Send dispute created notifications
     */
    private function sendDisputeCreatedNotifications(Dispute $dispute): void
    {
        try {
            $this->notificationService->sendDisputeCreatedNotification($dispute);
        } catch (\Exception $e) {
            Log::error('Failed to send dispute created notification', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send message notifications
     */
    private function sendMessageNotifications(Dispute $dispute, DisputeMessage $message): void
    {
        try {
            $this->notificationService->sendDisputeMessageNotification($dispute, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send dispute message notification', [
                'dispute_id' => $dispute->id,
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send resolution notifications
     */
    private function sendResolutionNotifications(Dispute $dispute, ?array $refundResult): void
    {
        try {
            $this->notificationService->sendDisputeResolvedNotification($dispute, $refundResult);
        } catch (\Exception $e) {
            Log::error('Failed to send dispute resolved notification', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get dispute statistics
     */
    public function getDisputeStats(): array
    {
        return [
            'total' => Dispute::count(),
            'open' => Dispute::where('status', 'open')->count(),
            'in_review' => Dispute::where('status', 'in_review')->count(),
            'resolved' => Dispute::where('status', 'resolved')->count(),
            'closed' => Dispute::where('status', 'closed')->count(),
            'avg_resolution_time' => Dispute::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
            'refund_total' => Dispute::where('status', 'resolved')
                ->where('resolution', 'LIKE', '%refund%')
                ->sum('refund_amount'),
        ];
    }
}