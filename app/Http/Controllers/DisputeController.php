<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    protected $disputeService;

    public function __construct(DisputeService $disputeService)
    {
        $this->middleware('auth');
        $this->disputeService = $disputeService;
    }

    /**
     * Display user's disputes
     */
    public function index(Request $request)
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

        return view('disputes.index', compact('disputes'));
    }

    /**
     * Show dispute creation form
     */
    public function create(Request $request)
    {
        $bookingId = $request->get('booking_id');
        $booking = null;

        if ($bookingId) {
            $booking = Booking::where('id', $bookingId)
                ->where(function($query) use ($request) {
                    $query->where('user_id', $request->user()->id)
                          ->orWhereHas('property', function($q) use ($request) {
                              $q->where('user_id', $request->user()->id);
                          });
                })
                ->with(['property', 'user'])
                ->firstOrFail();

            // Check if dispute can be created (within 14 days)
            if ($booking->checkout_date && now()->isAfter($booking->checkout_date->addDays(14))) {
                return redirect()->route('disputes.index')
                    ->with('error', 'Disputes must be raised within 14 days of checkout.');
            }
        }

        $disputeTypes = [
            'cancellation' => 'Booking Cancellation',
            'refund' => 'Refund Request',
            'property_issues' => 'Property Not as Described',
            'cleanliness' => 'Cleanliness Issues',
            'accuracy' => 'Listing Accuracy',
            'safety' => 'Safety Concerns',
            'communication' => 'Host Communication',
            'check_in' => 'Check-in Issues',
            'damage_claim' => 'Damage Claim',
            'other' => 'Other'
        ];

        return view('disputes.create', compact('booking', 'disputeTypes'));
    }

    /**
     * Store new dispute
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'dispute_type' => 'required|string|in:cancellation,refund,property_issues,cleanliness,accuracy,safety,communication,check_in,damage_claim,other',
            'reason' => 'required|string|max:1000',
            'amount_disputed' => 'nullable|numeric|min:0',
            'evidence_description' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        try {
            $booking = Booking::findOrFail($validated['booking_id']);
            
            // Authorize user can create dispute for this booking
            $this->authorize('createDispute', $booking);

            // Create dispute via service
            $dispute = $this->disputeService->createDispute($booking, $validated, $request->user());

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Dispute created successfully. You will receive updates as the case progresses.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create dispute: ' . $e->getMessage());
        }
    }

    /**
     * Show specific dispute
     */
    public function show(Dispute $dispute)
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
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('disputes.show', compact('dispute'));
    }

    /**
     * Add message to dispute
     */
    public function addMessage(Request $request, Dispute $dispute)
    {
        $this->authorize('reply', $dispute);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        try {
            $message = $this->disputeService->addMessage($dispute, $validated, $request->user());

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Message sent successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Download dispute attachment
     */
    public function downloadAttachment(Dispute $dispute, $messageId)
    {
        $this->authorize('view', $dispute);

        $message = $dispute->messages()
            ->where('id', $messageId)
            ->whereNotNull('attachment_path')
            ->firstOrFail();

        $filePath = storage_path('app/private/' . $message->attachment_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $message->attachment_name);
    }
}