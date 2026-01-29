<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralCredit;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->middleware('auth');
        $this->referralService = $referralService;
    }

    /**
     * Display referral dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's referrals with related data
        $referrals = Referral::where('referrer_id', $user->id)
            ->with(['referred_user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get referral statistics
        $stats = [
            'total_referred' => $referrals->total(),
            'successful_signups' => Referral::where('referrer_id', $user->id)
                ->whereIn('status', ['signed_up', 'completed', 'credited'])
                ->count(),
            'completed_bookings' => Referral::where('referrer_id', $user->id)
                ->whereIn('status', ['completed', 'credited'])
                ->count(),
            'total_earned' => ReferralCredit::where('user_id', $user->id)
                ->where('type', 'referrer_bonus')
                ->sum('amount'),
        ];

        // Get available credits
        $availableCredits = ReferralCredit::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->sum('amount');

        // Get referral code
        $referralCode = $this->referralService->getUserReferralCode($user);
        $referralLink = config('app.url') . '/register?ref=' . $referralCode;

        // Get recent activity
        $recentActivity = Referral::where('referrer_id', $user->id)
            ->with(['referred_user'])
            ->whereIn('status', ['signed_up', 'completed', 'credited'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('referrals.index', compact(
            'referrals', 
            'stats', 
            'availableCredits', 
            'referralCode', 
            'referralLink', 
            'recentActivity'
        ));
    }

    /**
     * Send referral invitations
     */
    public function sendInvites(Request $request)
    {
        $validated = $request->validate([
            'emails' => 'required|array|max:10',
            'emails.*' => 'required|email|distinct',
            'personal_message' => 'nullable|string|max:500',
        ]);

        try {
            $user = $request->user();
            $result = $this->referralService->sendInvitations(
                $user,
                $validated['emails'],
                $validated['personal_message'] ?? null
            );

            $successCount = count($result['sent']);
            $failCount = count($result['failed']);

            if ($successCount > 0) {
                $message = "Successfully sent {$successCount} invitation(s).";
                if ($failCount > 0) {
                    $message .= " {$failCount} invitation(s) failed to send.";
                }
                return redirect()->route('referrals.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('referrals.index')
                    ->with('error', 'Failed to send invitations. Please try again.');
            }

        } catch (\Exception $e) {
            return redirect()->route('referrals.index')
                ->with('error', 'Error sending invitations: ' . $e->getMessage());
        }
    }

    /**
     * Apply referral credits to booking
     */
    public function applyCredits(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'credit_amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = $request->user();
            $result = $this->referralService->applyCreditsToBooking(
                $user,
                $validated['booking_id'],
                $validated['credit_amount']
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Credits applied successfully',
                    'applied_amount' => $result['applied_amount'],
                    'remaining_credits' => $result['remaining_credits']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error applying credits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get referral program information
     */
    public function program()
    {
        $programInfo = [
            'signup_credit' => config('referrals.signup_credit', 25),
            'referrer_credit' => config('referrals.referrer_credit', 25),
            'minimum_booking_amount' => config('referrals.minimum_booking_amount', 50),
            'credit_expiry_days' => config('referrals.credit_expiry_days', 365),
            'max_credits_per_booking' => config('referrals.max_credits_per_booking', 100),
            'terms_and_conditions' => config('referrals.terms_and_conditions', ''),
        ];

        return view('referrals.program', compact('programInfo'));
    }

    /**
     * Show referral credits history
     */
    public function credits(Request $request)
    {
        $user = $request->user();
        
        $credits = ReferralCredit::where('user_id', $user->id)
            ->with(['referral.referred_user', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $creditsSummary = [
            'total_earned' => ReferralCredit::where('user_id', $user->id)->sum('amount'),
            'total_used' => ReferralCredit::where('user_id', $user->id)
                ->whereIn('status', ['used', 'partially_used'])
                ->sum('used_amount'),
            'available' => ReferralCredit::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->sum('amount'),
            'expired' => ReferralCredit::where('user_id', $user->id)
                ->where('status', 'expired')
                ->sum('amount'),
        ];

        return view('referrals.credits', compact('credits', 'creditsSummary'));
    }
}