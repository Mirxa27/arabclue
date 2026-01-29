<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralCredit;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReferralController extends Controller
{
    protected ReferralService $referralService;
    
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }
    
    /**
     * Get current user's referral code and history
     *
     * @return JsonResponse
     */
    public function myReferrals(): JsonResponse
    {
        $user = Auth::user();
        $referrals = Referral::where('referrer_id', $user->id)
            ->with(['referred' => function($q) {
                $q->select('id', 'name', 'email', 'avatar');
            }])
            ->latest()
            ->get();
            
        $credits = ReferralCredit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $totalCredits = ReferralCredit::where('user_id', $user->id)
            ->sum('amount');
            
        $referralCode = $this->referralService->getUserReferralCode($user);
        $referralLink = url('/register?ref=' . $referralCode);
        
        // Get referral program details
        $programDetails = $this->referralService->getProgramDetails();
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'referrals_count' => $referrals->count(),
                'successful_referrals' => $referrals->where('status', Referral::STATUS_CREDITED)->count(),
                'pending_referrals' => $referrals->whereIn('status', [
                    Referral::STATUS_PENDING, 
                    Referral::STATUS_SIGNED_UP
                ])->count(),
                'total_credits' => $totalCredits,
                'unused_credits' => $this->referralService->getAvailableCredits($user->id),
                'referrals' => $referrals,
                'credit_history' => $credits,
                'program_details' => $programDetails
            ]
        ]);
    }
    
    /**
     * Generate a new referral code for the user
     *
     * @return JsonResponse
     */
    public function generateNewCode(): JsonResponse
    {
        $user = Auth::user();
        $newReferralCode = $this->referralService->generateNewReferralCode($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $newReferralCode,
                'referral_link' => url('/register?ref=' . $newReferralCode)
            ],
            'message' => 'New referral code generated successfully'
        ]);
    }
    
    /**
     * Send referral invites via email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendInvites(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'emails' => 'required|array|min:1|max:10',
            'emails.*' => 'required|email|distinct',
            'message' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $referralCode = $this->referralService->getUserReferralCode($user);
        $customMessage = $request->message;
        
        $results = $this->referralService->sendReferralInvites(
            $user,
            $request->emails,
            $referralCode,
            $customMessage
        );
        
        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => count($results['sent']) . ' invitation(s) sent successfully'
        ]);
    }
    
    /**
     * Apply a referral code during registration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function applyReferralCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required|string|max:20'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $referralCode = $request->referral_code;
        $result = $this->referralService->validateReferralCode($referralCode);
        
        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }
        
        // Store in session for later use during registration
        session(['referral_code' => $referralCode]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'referrer_name' => $result['referrer_name']
            ],
            'message' => 'Referral code applied successfully'
        ]);
    }
    
    /**
     * Apply credits to booking
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function applyCreditsToBooking(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $bookingId = $request->booking_id;
        $amount = $request->amount;
        
        $result = $this->referralService->applyCreditsToBooking($user->id, $bookingId, $amount);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'applied_amount' => $result['applied_amount'],
                'remaining_credits' => $result['remaining_credits'],
                'booking_total' => $result['booking_total']
            ],
            'message' => 'Credits applied successfully to booking'
        ]);
    }
}
