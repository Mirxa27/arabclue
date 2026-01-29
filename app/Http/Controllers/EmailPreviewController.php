<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Mail\WelcomeEmail;
use App\Mail\PropertyApprovalEmail;
use App\Mail\PaymentConfirmationEmail;
use App\Mail\ReviewRequestEmail;
use App\Mail\BookingReminderEmail;
use App\Mail\HostPayoutEmail;
use App\Mail\SystemMaintenanceEmail;
use App\Mail\SpecialOfferEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EmailPreviewController extends Controller
{
    /**
     * Show email preview dashboard
     */
    public function index()
    {
        $emailTemplates = [
            'welcome' => [
                'title' => 'Welcome Email',
                'description' => 'Sent to new users upon registration',
                'icon' => '👋',
                'color' => 'bg-blue-500'
            ],
            'property-approved' => [
                'title' => 'Property Approved',
                'description' => 'Sent when host property is approved',
                'icon' => '✅',
                'color' => 'bg-green-500'
            ],
            'property-rejected' => [
                'title' => 'Property Rejected',
                'description' => 'Sent when host property needs attention',
                'icon' => '❌',
                'color' => 'bg-red-500'
            ],
            'payment-confirmation' => [
                'title' => 'Payment Confirmation',
                'description' => 'Sent after successful booking payment',
                'icon' => '💳',
                'color' => 'bg-green-600'
            ],
            'review-request' => [
                'title' => 'Review Request',
                'description' => 'Sent after guest checkout',
                'icon' => '⭐',
                'color' => 'bg-yellow-500'
            ],
            'booking-reminder-checkin' => [
                'title' => 'Check-in Reminder',
                'description' => 'Sent 1 day before check-in',
                'icon' => '🏠',
                'color' => 'bg-purple-500'
            ],
            'booking-reminder-checkout' => [
                'title' => 'Check-out Reminder',
                'description' => 'Sent on check-out day',
                'icon' => '🚪',
                'color' => 'bg-orange-500'
            ],
            'host-payout' => [
                'title' => 'Host Payout',
                'description' => 'Sent when host payment is processed',
                'icon' => '💰',
                'color' => 'bg-green-700'
            ],
            'system-maintenance' => [
                'title' => 'System Maintenance',
                'description' => 'Sent for scheduled maintenance',
                'icon' => '🔧',
                'color' => 'bg-gray-600'
            ],
            'special-offer' => [
                'title' => 'Special Offer',
                'description' => 'Marketing campaigns and promotions',
                'icon' => '🎉',
                'color' => 'bg-pink-500'
            ],
        ];

        return view('email-preview.index', compact('emailTemplates'));
    }

    /**
     * Preview welcome email
     */
    public function welcome()
    {
        $user = $this->createSampleUser();
        $mailable = new WelcomeEmail($user);
        
        return $this->renderEmail($mailable, 'Welcome Email');
    }

    /**
     * Preview property approval email
     */
    public function propertyApproved()
    {
        $property = $this->createSampleProperty();
        $mailable = new PropertyApprovalEmail($property, true);
        
        return $this->renderEmail($mailable, 'Property Approved');
    }

    /**
     * Preview property rejection email
     */
    public function propertyRejected()
    {
        $property = $this->createSampleProperty();
        $mailable = new PropertyApprovalEmail($property, false);
        
        return $this->renderEmail($mailable, 'Property Rejected');
    }

    /**
     * Preview payment confirmation email
     */
    public function paymentConfirmation()
    {
        $booking = $this->createSampleBooking();
        $mailable = new PaymentConfirmationEmail($booking);
        
        return $this->renderEmail($mailable, 'Payment Confirmation');
    }

    /**
     * Preview review request email
     */
    public function reviewRequest()
    {
        $booking = $this->createSampleBooking();
        $mailable = new ReviewRequestEmail($booking);
        
        return $this->renderEmail($mailable, 'Review Request');
    }

    /**
     * Preview booking reminder email (check-in)
     */
    public function bookingReminderCheckin()
    {
        $booking = $this->createSampleBooking();
        $mailable = new BookingReminderEmail($booking, 'check_in');
        
        return $this->renderEmail($mailable, 'Check-in Reminder');
    }

    /**
     * Preview booking reminder email (check-out)
     */
    public function bookingReminderCheckout()
    {
        $booking = $this->createSampleBooking();
        $mailable = new BookingReminderEmail($booking, 'check_out');
        
        return $this->renderEmail($mailable, 'Check-out Reminder');
    }

    /**
     * Preview host payout email
     */
    public function hostPayout()
    {
        $booking = $this->createSampleBooking();
        $payoutAmount = 850.00;
        $mailable = new HostPayoutEmail($booking, $payoutAmount);
        
        return $this->renderEmail($mailable, 'Host Payout');
    }

    /**
     * Preview system maintenance email
     */
    public function systemMaintenance()
    {
        $maintenanceStart = now()->addHours(2);
        $maintenanceEnd = now()->addHours(4);
        $mailable = new SystemMaintenanceEmail(
            $maintenanceStart,
            $maintenanceEnd,
            'scheduled',
            ['Website', 'Mobile App', 'Booking System']
        );
        
        return $this->renderEmail($mailable, 'System Maintenance');
    }

    /**
     * Preview special offer email
     */
    public function specialOffer()
    {
        $user = $this->createSampleUser();
        $featuredProperties = collect([
            $this->createSampleProperty(),
            $this->createSampleProperty(['title' => 'Luxury Villa in Jeddah', 'city' => 'Jeddah']),
            $this->createSampleProperty(['title' => 'Modern Apartment in Dammam', 'city' => 'Dammam']),
        ]);
        
        $mailable = new SpecialOfferEmail(
            $user,
            'Summer Sale - Limited Time Only!',
            'Get ready for summer with amazing discounts on premium properties across Saudi Arabia.',
            25,
            now()->addDays(7),
            'SUMMER25',
            $featuredProperties
        );
        
        return $this->renderEmail($mailable, 'Special Offer');
    }

    /**
     * Render email template
     */
    private function renderEmail($mailable, $title)
    {
        try {
            $content = $mailable->content();
            $envelope = $mailable->envelope();
            
            $html = View::make($content->view, $content->with ?? [])->render();
            
            return view('email-preview.template', [
                'title' => $title,
                'subject' => $envelope->subject,
                'html' => $html,
                'backUrl' => route('email-preview.index')
            ]);
            
        } catch (\Exception $e) {
            return view('email-preview.error', [
                'title' => $title,
                'error' => $e->getMessage(),
                'backUrl' => route('email-preview.index')
            ]);
        }
    }

    /**
     * Create sample user for testing
     */
    private function createSampleUser($overrides = [])
    {
        $user = new User();
        $user->id = 1;
        $user->name = $overrides['name'] ?? 'Ahmed Al-Rashid';
        $user->email = $overrides['email'] ?? 'ahmed@example.com';
        $user->role = $overrides['role'] ?? 'guest';
        $user->created_at = now()->subDays(30);
        $user->email_verified_at = now()->subDays(29);
        
        return $user;
    }

    /**
     * Create sample property for testing
     */
    private function createSampleProperty($overrides = [])
    {
        $property = new Property();
        $property->id = 1;
        $property->title = $overrides['title'] ?? 'Luxury Apartment in Riyadh';
        $property->slug = $overrides['slug'] ?? 'luxury-apartment-riyadh';
        $property->description = 'Beautiful modern apartment in the heart of Riyadh with stunning city views.';
        $property->city = $overrides['city'] ?? 'Riyadh';
        $property->country = 'Saudi Arabia';
        $property->address = 'King Fahd Road, Olaya District';
        $property->price_per_night = $overrides['price_per_night'] ?? 250.00;
        $property->cleaning_fee = 75.00;
        $property->service_fee_percentage = 12.00;
        $property->accommodates = 4;
        $property->bedrooms = 2;
        $property->bathrooms = 2;
        $property->property_type = 'apartment';
        $property->check_in_time = '15:00';
        $property->check_out_time = '11:00';
        $property->minimum_nights = 1;
        $property->overall_rating = 4.8;
        $property->review_count = 127;
        $property->status = 'active';
        $property->is_featured = true;
        $property->images = [
            ['url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'],
            ['url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800']
        ];
        
        // Create sample host
        $host = $this->createSampleUser([
            'name' => 'Fatima Al-Zahra',
            'email' => 'fatima@example.com',
            'role' => 'host'
        ]);
        $property->user = $host;
        
        return $property;
    }

    /**
     * Create sample booking for testing
     */
    private function createSampleBooking($overrides = [])
    {
        $booking = new Booking();
        $booking->id = 12345;
        $booking->check_in = now()->addDays(1);
        $booking->check_out = now()->addDays(4);
        $booking->guests = 2;
        $booking->nights = 3;
        $booking->subtotal = 750.00;
        $booking->cleaning_fee = 75.00;
        $booking->service_fee = 90.00;
        $booking->total_amount = 915.00;
        $booking->status = 'confirmed';
        $booking->payment_status = 'paid';
        $booking->payment_method = 'credit_card';
        $booking->payment_intent_id = 'pi_test_123456789';
        $booking->confirmed_at = now()->subHours(2);
        $booking->special_requests = 'Late check-in around 8 PM please';
        
        // Attach user and property
        $booking->user = $this->createSampleUser();
        $booking->property = $this->createSampleProperty();
        
        return $booking;
    }
}
