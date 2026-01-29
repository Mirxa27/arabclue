<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DisputeController extends Controller
{
    protected $paymentService;
    protected $notificationService;

    public function __construct(PaymentService $paymentService, NotificationService $notificationService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's disputes
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $disputes = Dispute::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('booking', function($q) use ($user) {
                      $q->whereHas('property', function($prop) use ($user) {
                          $prop->where('user_id', $user->id);
                      });
                  });
        })
        ->with(['booking.property', 'messages' => function($query) {
            $query->latest()->limit(1);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }

    /**
     * Create a new dispute with 14-day validation
     */
    public function store(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->authorize('createDispute', $booking);

            // Validate 14-day window for dispute creation
            $this->validateDisputeTimeWindow($booking);

            $validated = $request->validate([
                'dispute_type' => 'required|string|in:cancellation,refund,property_issues,cleanliness,accuracy,safety,communication,check_in,damage_claim,other',
                'reason' => 'required|string|max:1000',
                'amount_disputed' => 'nullable|numeric|min:0',
                'evidence_description' => 'nullable|string|max:2000',
                'attachments' => 'nullable|array|max:5',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB max
            ]);

            // Create the dispute
            $dispute = $booking->disputes()->create([
                'user_id' => $request->user()->id,
                'dispute_type' => $validated['dispute_type'],
                'reason' => $validated['reason'],
                'amount_disputed' => $validated['amount_disputed'] ?? 0,
                'evidence_description' => $validated['evidence_description'],
                'status' => 'open',
                'priority' => $this->calculatePriority($validated, $booking),
                'admin_assigned_id' => $this->autoAssignAdmin(),
            ]);

            // Create initial message
            $initialMessage = $dispute->messages()->create([
                'user_id' => $request->user()->id,
                'message' => $validated['reason'],
                'message_type' => 'initial_claim',
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                $this->handleAttachments($request->file('attachments'), $dispute, $initialMessage);
            }

            // Send notifications
            $this->sendDisputeCreatedNotifications($dispute);

            // Load relationships for response
            $dispute->load(['booking.property', 'messages.user', 'adminAssigned']);

            return response()->json([
                'success' => true,
                'message' => 'Dispute created successfully. You will receive updates as the case progresses.',
                'data' => $dispute
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Dispute creation failed', [
                'booking_id' => $booking->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create dispute. Please try again.'
            ], 500);
        }
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

    public function show(Dispute $dispute): JsonResponse
    {
        $this->authorize('view', $dispute);
        
        $dispute->load([
            'messages.user', 
            'booking.property.user',
            'adminAssigned',
            'user'
        ]);
        
        // Mark messages as read for current user
        $dispute->messages()
            ->where('user_id', '!=', request()->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $dispute
        ]);
    }

    public function reply(Request $request, Dispute $dispute): JsonResponse
    {
        $this->authorize('reply', $dispute);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'message_type' => 'string|in:response,counter_offer,evidence,agreement',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $message = $dispute->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'response',
        ]);

        if ($request->hasFile('attachments')) {
            $this->handleAttachments($request->file('attachments'), $dispute, $message);
        }

        // Send notifications to relevant parties
        $this->sendMessageNotifications($dispute, $message);

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => $message->load('user')
        ], 201);
    }

    /**
     * Resolve dispute with refund processing
     */
    public function resolve(Request $request, Dispute $dispute): JsonResponse
    {
        $this->authorize('resolve', $dispute);

        $validated = $request->validate([
            'resolution' => 'required|string|in:full_refund,partial_refund,no_refund,other',
            'refund_amount' => 'nullable|numeric|min:0',
            'resolution_notes' => 'required|string|max:1000',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        try {
            // Process refund if applicable
            $refundResult = null;
            if (in_array($validated['resolution'], ['full_refund', 'partial_refund']) && $validated['refund_amount'] > 0) {
                $refundResult = $this->processDisputeRefund($dispute, $validated['refund_amount']);
            }

            // Update dispute status
            $dispute->update([
                'status' => 'resolved',
                'resolution' => $validated['resolution'],
                'refund_amount' => $validated['refund_amount'] ?? 0,
                'resolution_notes' => $validated['resolution_notes'],
                'admin_notes' => $validated['admin_notes'],
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
            ]);

            // Create resolution message
            $dispute->messages()->create([
                'user_id' => $request->user()->id,
                'message' => $validated['resolution_notes'],
                'message_type' => 'resolution',
                'is_admin' => $request->user()->hasRole('admin'),
            ]);

            // Send resolution notifications
            $this->sendResolutionNotifications($dispute, $refundResult);

            return response()->json([
                'success' => true,
                'message' => 'Dispute resolved successfully',
                'data' => [
                    'dispute' => $dispute->fresh()->load(['messages.user', 'booking']),
                    'refund_result' => $refundResult
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Dispute resolution failed', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve dispute. Please try again.'
            ], 500);
        }
    }

    /**
     * Calculate dispute priority based on type and amount
     */
    private function calculatePriority(array $validated, Booking $booking): string
    {
        $highPriorityTypes = ['safety', 'damage_claim'];
        $amount = $validated['amount_disputed'] ?? 0;

        if (in_array($validated['dispute_type'], $highPriorityTypes)) {
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
        $admin = \App\Models\User::role('admin')
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
    }

    /**
     * Send dispute created notifications
     */
    private function sendDisputeCreatedNotifications(Dispute $dispute): void
    {
        $this->notificationService->sendDisputeCreatedNotification($dispute);
    }

    /**
     * Send message notifications
     */
    private function sendMessageNotifications(Dispute $dispute, DisputeMessage $message): void
    {
        $this->notificationService->sendDisputeMessageNotification($dispute, $message);
    }

    /**
     * Send resolution notifications
     */
    private function sendResolutionNotifications(Dispute $dispute, ?array $refundResult): void
    {
        $this->notificationService->sendDisputeResolvedNotification($dispute, $refundResult);
    }
}
