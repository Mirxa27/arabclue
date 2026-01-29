<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Mail\WelcomeEmail;
use App\Mail\PropertyApprovalEmail;
use App\Mail\PaymentConfirmationEmail;
use App\Mail\ReviewRequestEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_can_be_rendered()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $mailable = new WelcomeEmail($user);
        
        $rendered = $mailable->render();
        
        $this->assertStringContainsString('Welcome to HabibiStay, John Doe!', $rendered);
        $this->assertStringContainsString('john@example.com', $rendered);
        $this->assertStringContainsString('Exceptional Stays. Exceptional Returns.', $rendered);
    }

    public function test_property_approval_email_can_be_rendered()
    {
        $host = User::factory()->host()->create(['name' => 'Jane Host']);
        $property = Property::factory()->create([
            'user_id' => $host->id,
            'title' => 'Beautiful Riyadh Apartment',
            'city' => 'Riyadh',
            'price_per_night' => 250.00
        ]);

        $mailable = new PropertyApprovalEmail($property, true);
        
        $rendered = $mailable->render();
        
        $this->assertStringContainsString('Congratulations! Your property has been approved', $rendered);
        $this->assertStringContainsString('Beautiful Riyadh Apartment', $rendered);
        $this->assertStringContainsString('Jane Host', $rendered);
        $this->assertStringContainsString('SAR 250.00', $rendered);
    }

    public function test_property_rejection_email_can_be_rendered()
    {
        $host = User::factory()->host()->create(['name' => 'Jane Host']);
        $property = Property::factory()->create([
            'user_id' => $host->id,
            'title' => 'Needs Work Apartment'
        ]);

        $mailable = new PropertyApprovalEmail($property, false);
        
        $rendered = $mailable->render();
        
        $this->assertStringContainsString('Your property needs some attention', $rendered);
        $this->assertStringContainsString('Needs Work Apartment', $rendered);
        $this->assertStringContainsString('Jane Host', $rendered);
        $this->assertStringContainsString('Action Required', $rendered);
    }

    public function test_payment_confirmation_email_can_be_rendered()
    {
        $guest = User::factory()->create(['name' => 'John Guest']);
        $host = User::factory()->host()->create(['name' => 'Jane Host']);
        $property = Property::factory()->create([
            'user_id' => $host->id,
            'title' => 'Luxury Villa',
            'city' => 'Riyadh'
        ]);
        
        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'total_amount' => 1500.00,
            'total_nights' => 3,
            'payment_method' => 'credit_card'
        ]);

        $mailable = new PaymentConfirmationEmail($booking);
        
        $rendered = $mailable->render();
        
        $this->assertStringContainsString('Payment Confirmed!', $rendered);
        $this->assertStringContainsString('John Guest', $rendered);
        $this->assertStringContainsString('Luxury Villa', $rendered);
        $this->assertStringContainsString('SAR 1,500.00', $rendered);
        $this->assertStringContainsString('3 nights', $rendered);
    }

    public function test_review_request_email_can_be_rendered()
    {
        $guest = User::factory()->create(['name' => 'John Guest']);
        $host = User::factory()->host()->create(['name' => 'Jane Host']);
        $property = Property::factory()->create([
            'user_id' => $host->id,
            'title' => 'Amazing Stay',
            'city' => 'Riyadh'
        ]);
        
        $booking = Booking::factory()->completed()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'total_nights' => 2
        ]);

        $mailable = new ReviewRequestEmail($booking);
        
        $rendered = $mailable->render();
        
        $this->assertStringContainsString('How was your stay?', $rendered);
        $this->assertStringContainsString('John Guest', $rendered);
        $this->assertStringContainsString('Amazing Stay', $rendered);
        $this->assertStringContainsString('Jane Host', $rendered);
        $this->assertStringContainsString('2 nights', $rendered);
    }

    public function test_email_layout_contains_required_elements()
    {
        $user = User::factory()->create();
        $mailable = new WelcomeEmail($user);
        
        $rendered = $mailable->render();
        
        // Check for layout elements
        $this->assertStringContainsString('HabibiStay', $rendered);
        $this->assertStringContainsString('Exceptional Stays. Exceptional Returns.', $rendered);
        $this->assertStringContainsString('© ' . date('Y') . ' HabibiStay', $rendered);
        $this->assertStringContainsString('Riyadh, Saudi Arabia', $rendered);
        $this->assertStringContainsString('Unsubscribe', $rendered);
        $this->assertStringContainsString('Privacy Policy', $rendered);
        $this->assertStringContainsString('Contact Us', $rendered);
    }

    public function test_emails_are_mobile_responsive()
    {
        $user = User::factory()->create();
        $mailable = new WelcomeEmail($user);
        
        $rendered = $mailable->render();
        
        // Check for responsive design elements
        $this->assertStringContainsString('max-width: 600px', $rendered);
        $this->assertStringContainsString('@media only screen and (max-width: 600px)', $rendered);
        $this->assertStringContainsString('width: 100% !important', $rendered);
    }

    public function test_emails_have_proper_styling()
    {
        $user = User::factory()->create();
        $mailable = new WelcomeEmail($user);
        
        $rendered = $mailable->render();
        
        // Check for proper styling
        $this->assertStringContainsString('background: linear-gradient', $rendered);
        $this->assertStringContainsString('border-radius:', $rendered);
        $this->assertStringContainsString('font-family:', $rendered);
        $this->assertStringContainsString('email-button', $rendered);
    }
}
