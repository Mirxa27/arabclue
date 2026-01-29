<?php

namespace App\Services;

use App\Mail\ReferralInvitation;
use App\Models\Booking;
use App\Models\Referral;
use App\Models\ReferralCredit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReferralService
{
    /**
     * Generate a new referral code for a user
     * 
     * @param User $user
     * @return string
     */
    public function generateNewReferralCode(User $user): string
    {
        // Create a unique referral code
        $referralCode = strtoupper(Str::random(8));
        
        // Make sure it's unique
        while (Referral::where('referral_code', $referralCode)->exists()) {
            $referralCode = strtoupper(Str::random(8));
        }
        
        // Update any existing referrals with this new code
        Referral::where('referrer_id', $user->id)
            ->whereNull('referred_id')
            ->update(['referral_code' => $referralCode]);
            
        // Also create a default pending referral with this code if none exist
        Referral::firstOrCreate(
            ['referrer_id' => $user->id, 'referred_id' => null],
            ['referral_code' => $referralCode, 'status' => Referral::STATUS_PENDING]
        );
        
        return $referralCode;
    }
    
    /**
     * Get a user's active referral code
     * 
     * @param User $user
     * @return string
     */
    public function getUserReferralCode(User $user): string
    {
        $referral = Referral::where('referrer_id', $user->id)
            ->whereNull('referred_id')
            ->first();
            
        if (!$referral) {
            return $this->generateNewReferralCode($user);
        }
        
        return $referral->referral_code;
    }
    
    /**
     * Validate if a referral code is valid
     * 
     * @param string $code
     * @return array
     */
    public function validateReferralCode(string $code): array
    {
        $referral = Referral::where('referral_code', $code)
            ->whereNull('referred_id')
            ->first();
            
        if (!$referral) {
            return [
                'valid' => false,
                'message' => 'Invalid referral code'
            ];
        }
        
        // Make sure user isn't referring themselves
        if (auth()->check() && auth()->id() == $referral->referrer_id) {
            return [
                'valid' => false,
                'message' => 'You cannot use your own referral code'
            ];
        }
        
        // Get referrer's name
        $referrer = User::find($referral->referrer_id);
        
        return [
            'valid' => true,
            'referrer_id' => $referral->referrer_id,
            'referrer_name' => $referrer->name,
            'message' => 'Valid referral code'
        ];
    }
    
    /**
     * Process a new user registration with a referral code
     * 
     * @param User $newUser
     * @param string|null $referralCode
     * @return array
     */
    public function processRegistrationReferral(User $newUser, ?string $referralCode): array
    {
        if (!$referralCode) {
            return [
                'success' => false,
                'message' => 'No referral code provided'
            ];
        }
        
        // Find the referral
        $referral = Referral::where('referral_code', $referralCode)
            ->whereNull('referred_id')
            ->first();
            
        if (!$referral) {
            return [
                'success' => false,
                'message' => 'Invalid referral code'
            ];
        }
        
        // Update the referral with the new user
        $referral->referred_id = $newUser->id;
        $referral->referred_email = $newUser->email;
        $referral->status = Referral::STATUS_SIGNED_UP;
        $referral->signup_completed_at = now();
        $referral->save();
        
        // Create a new referral code entry for the referral link to be available again
        Referral::create([
            'referrer_id' => $referral->referrer_id,
            'referral_code' => $referral->referral_code,
            'status' => Referral::STATUS_PENDING
        ]);
        
        // Check if we should give immediate credits to either party
        $settings = $this->getProgramDetails();
        
        if ($settings['signup_credit_referrer'] > 0) {
            $this->addReferralCredit(
                $referral->referrer_id,
                $settings['signup_credit_referrer'],
                ReferralCredit::TYPE_SIGNUP_REFERRER,
                "New user signup: {$newUser->email}"
            );
        }
        
        if ($settings['signup_credit_referred'] > 0) {
            $this->addReferralCredit(
                $newUser->id,
                $settings['signup_credit_referred'],
                ReferralCredit::TYPE_SIGNUP_REFERRED,
                "Signup bonus from referral"
            );
        }
        
        return [
            'success' => true,
            'message' => 'Referral processed successfully'
        ];
    }
    
    /**
     * Process a booking completion by a referred user
     * 
     * @param Booking $booking
     * @return array
     */
    public function processReferredBooking(Booking $booking): array
    {
        // Find if user was referred
        $referral = Referral::where('referred_id', $booking->user_id)
            ->whereNotNull('referred_id')
            ->whereNull('first_booking_completed_at')
            ->first();
            
        if (!$referral) {
            return [
                'success' => false,
                'message' => 'No eligible referral found'
            ];
        }
        
        // Update referral status
        $referral->status = Referral::STATUS_COMPLETED;
        $referral->first_booking_completed_at = now();
        $referral->save();
        
        // Add credits to both parties
        $settings = $this->getProgramDetails();
        $bookingAmount = $booking->total_price;
        
        // Calculate referrer reward
        $referrerAmount = 0;
        if ($settings['booking_credit_type_referrer'] === Referral::CREDIT_TYPE_CASH) {
            $referrerAmount = $settings['booking_credit_amount_referrer'];
        } else if ($settings['booking_credit_type_referrer'] === Referral::CREDIT_TYPE_PERCENTAGE) {
            $referrerAmount = ($bookingAmount * $settings['booking_credit_amount_referrer']) / 100;
        }
        
        // Calculate referred reward
        $referredAmount = 0;
        if ($settings['booking_credit_type_referred'] === Referral::CREDIT_TYPE_CASH) {
            $referredAmount = $settings['booking_credit_amount_referred'];
        } else if ($settings['booking_credit_type_referred'] === Referral::CREDIT_TYPE_PERCENTAGE) {
            $referredAmount = ($bookingAmount * $settings['booking_credit_amount_referred']) / 100;
        }
        
        // Credit both parties
        if ($referrerAmount > 0) {
            $this->addReferralCredit(
                $referral->referrer_id,
                $referrerAmount,
                ReferralCredit::TYPE_BOOKING_REFERRER,
                "Referral booking completed: Booking #{$booking->id}"
            );
        }
        
        if ($referredAmount > 0) {
            $this->addReferralCredit(
                $referral->referred_id,
                $referredAmount,
                ReferralCredit::TYPE_BOOKING_REFERRED,
                "First booking bonus from referral: Booking #{$booking->id}"
            );
        }
        
        // Update referral status to credited
        $referral->status = Referral::STATUS_CREDITED;
        $referral->credits_awarded_at = now();
        $referral->referrer_credit = $referrerAmount;
        $referral->referred_credit = $referredAmount;
        $referral->referrer_credit_type = $settings['booking_credit_type_referrer'];
        $referral->referred_credit_type = $settings['booking_credit_type_referred'];
        $referral->metadata = [
            'booking_id' => $booking->id,
            'booking_amount' => $bookingAmount
        ];
        $referral->save();
        
        return [
            'success' => true,
            'message' => 'Referral booking processed successfully',
            'referrer_credit' => $referrerAmount,
            'referred_credit' => $referredAmount
        ];
    }
    
    /**
     * Add referral credit to a user
     * 
     * @param int $userId
     * @param float $amount
     * @param string $creditType
     * @param string $description
     * @return ReferralCredit
     */
    private function addReferralCredit(int $userId, float $amount, string $creditType, string $description): ReferralCredit
    {
        return ReferralCredit::create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => $creditType,
            'description' => $description,
            'expires_at' => now()->addMonths(6) // Credits expire after 6 months
        ]);
    }
    
    /**
     * Get available credits for a user
     * 
     * @param int $userId
     * @return float
     */
    public function getAvailableCredits(int $userId): float
    {
        // Get total credits
        $totalCredits = ReferralCredit::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->sum('amount');
            
        // Get used credits
        $usedCredits = ReferralCredit::where('user_id', $userId)
            ->where('type', ReferralCredit::TYPE_USED)
            ->sum('amount');
            
        return max(0, $totalCredits - $usedCredits);
    }
    
    /**
     * Apply referral credits to a booking
     * 
     * @param int $userId
     * @param int $bookingId
     * @param float $amount
     * @return array
     */
    public function applyCreditsToBooking(int $userId, int $bookingId, float $amount): array
    {
        // Start a transaction
        return DB::transaction(function () use ($userId, $bookingId, $amount) {
            // Check available credits
            $availableCredits = $this->getAvailableCredits($userId);
            
            if ($availableCredits < $amount) {
                return [
                    'success' => false,
                    'message' => 'Insufficient credits'
                ];
            }
            
            // Get the booking
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();
                
            if (!$booking) {
                return [
                    'success' => false,
                    'message' => 'Booking not found or not eligible for credits'
                ];
            }
            
            // Ensure amount doesn't exceed the booking total
            $maxApplicable = min($amount, $booking->total_price);
            
            // Record the credit usage
            $this->addReferralCredit(
                $userId,
                -$maxApplicable,
                ReferralCredit::TYPE_USED,
                "Applied to Booking #{$bookingId}"
            );
            
            // Update the booking
            $booking->referral_credit_applied = ($booking->referral_credit_applied ?? 0) + $maxApplicable;
            $booking->save();
            
            // Calculate new totals
            $newTotal = $booking->total_price - $booking->referral_credit_applied;
            
            return [
                'success' => true,
                'applied_amount' => $maxApplicable,
                'remaining_credits' => $this->getAvailableCredits($userId),
                'booking_total' => $newTotal
            ];
        });
    }
    
    /**
     * Send referral invites via email
     * 
     * @param User $referrer
     * @param array $emails
     * @param string $referralCode
     * @param string|null $customMessage
     * @return array
     */
    public function sendReferralInvites(User $referrer, array $emails, string $referralCode, ?string $customMessage = null): array
    {
        $results = [
            'sent' => [],
            'failed' => []
        ];
        
        foreach ($emails as $email) {
            try {
                // Check if user already exists
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $results['failed'][] = [
                        'email' => $email,
                        'reason' => 'User already registered'
                    ];
                    continue;
                }
                
                // Check if already invited
                $existingInvite = Referral::where('referred_email', $email)
                    ->whereNull('referred_id')
                    ->where('referrer_id', $referrer->id)
                    ->first();
                    
                if ($existingInvite) {
                    // Update the existing invite
                    $existingInvite->referral_code = $referralCode;
                    $existingInvite->save();
                } else {
                    // Create a new invite
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_email' => $email,
                        'referral_code' => $referralCode,
                        'status' => Referral::STATUS_PENDING
                    ]);
                }
                
                // Send email
                $referralLink = url('/register?ref=' . $referralCode);
                Mail::to($email)->send(new ReferralInvitation($referrer, $referralLink, $customMessage));
                
                $results['sent'][] = $email;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'email' => $email,
                    'reason' => 'Failed to send email'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get referral program details
     * 
     * @return array
     */
    public function getProgramDetails(): array
    {
        // In a real app, these would come from settings in the database
        return [
            'enabled' => true,
            'signup_credit_referrer' => 10, // $10 for referrer on signup
            'signup_credit_referred' => 10, // $10 for new user on signup
            'booking_credit_type_referrer' => Referral::CREDIT_TYPE_PERCENTAGE,
            'booking_credit_amount_referrer' => 5, // 5% of booking
            'booking_credit_type_referred' => Referral::CREDIT_TYPE_PERCENTAGE,
            'booking_credit_amount_referred' => 5, // 5% of first booking
            'credit_expiry_days' => 180, // Credits expire after 6 months
            'min_booking_value' => 100, // Minimum booking value to qualify
            'max_referral_credit' => 500, // Maximum credit per referral
            'terms_url' => url('/referral-terms'),
            'description' => 'Invite friends and earn credits when they sign up and book with HabibiStay. You get 5% of their first booking, and they get 5% off their first booking!'
        ];
    }
}
