<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Notifications\PropertyApprovalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_create_property()
    {
        Storage::fake('public');
        
        $host = User::factory()->host()->create();

        $response = $this->actingAs($host)->post('/host/properties', [
            'title' => 'Beautiful Apartment in Riyadh',
            'description' => 'A lovely apartment with great amenities.',
            'property_type' => 'apartment',
            'room_type' => 'entire_place',
            'accommodates' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 1.5,
            'price_per_night' => 300.00,
            'cleaning_fee' => 50.00,
            'address' => '123 King Fahd Road',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'country' => 'Saudi Arabia',
            'postal_code' => '12345',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'house_rules' => ['No smoking', 'No pets'],
            'cancellation_policy' => 'flexible',
            'minimum_nights' => 1,
            'maximum_nights' => 30,
            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.jpg'),
            ],
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Apartment in Riyadh',
            'user_id' => $host->id,
            'status' => 'pending',
        ]);

        // Check that images were uploaded
        $property = Property::where('title', 'Beautiful Apartment in Riyadh')->first();
        $this->assertCount(2, $property->images);
    }

    public function test_guest_cannot_create_property()
    {
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($guest)->post('/host/properties', [
            'title' => 'Test Property',
            'description' => 'Test description',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_property()
    {
        Notification::fake();
        
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch("/admin/properties/{$property->id}/approve");

        $response->assertRedirect();
        
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'active',
        ]);

        $property->refresh();
        $this->assertNotNull($property->approved_at);

        // Assert notification was sent to host
        Notification::assertSentTo(
            $property->user,
            PropertyApprovalNotification::class,
            function ($notification) {
                return $notification->approved === true;
            }
        );
    }

    public function test_admin_can_reject_property()
    {
        Notification::fake();
        
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch("/admin/properties/{$property->id}/reject", [
            'rejection_reason' => 'Insufficient photos',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'rejected',
        ]);

        // Assert notification was sent to host
        Notification::assertSentTo(
            $property->user,
            PropertyApprovalNotification::class,
            function ($notification) {
                return $notification->approved === false;
            }
        );
    }

    public function test_host_can_update_own_property()
    {
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['user_id' => $host->id]);

        $response = $this->actingAs($host)->patch("/host/properties/{$property->id}", [
            'title' => 'Updated Property Title',
            'price_per_night' => 400.00,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Property Title',
            'price_per_night' => 400.00,
        ]);
    }

    public function test_host_cannot_update_other_host_property()
    {
        $host1 = User::factory()->host()->create();
        $host2 = User::factory()->host()->create();
        $property = Property::factory()->create(['user_id' => $host2->id]);

        $response = $this->actingAs($host1)->patch("/host/properties/{$property->id}", [
            'title' => 'Hacked Property',
        ]);

        $response->assertStatus(403);
    }

    public function test_property_search_functionality()
    {
        $riyadhProperty = Property::factory()->inRiyadh()->create([
            'title' => 'Riyadh Apartment',
            'status' => 'active',
        ]);
        
        $jeddahProperty = Property::factory()->create([
            'title' => 'Jeddah Villa',
            'city' => 'Jeddah',
            'status' => 'active',
        ]);

        // Search by city
        $response = $this->get('/api/properties/search?city=Riyadh');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Riyadh Apartment']);
        $response->assertJsonMissing(['title' => 'Jeddah Villa']);
    }

    public function test_property_availability_check()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        // Create a booking that conflicts with the requested dates
        $existingBooking = \App\Models\Booking::factory()->create([
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-05',
            'status' => 'confirmed',
        ]);

        $response = $this->get("/api/properties/{$property->id}/availability", [
            'check_in' => '2024-06-03',
            'check_out' => '2024-06-07',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['available' => false]);
    }

    public function test_property_pricing_calculation()
    {
        $property = Property::factory()->create([
            'price_per_night' => 200.00,
            'cleaning_fee' => 50.00,
            'service_fee_percentage' => 10.00,
        ]);

        $response = $this->get("/api/properties/{$property->id}/pricing", [
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-04', // 3 nights
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'nights' => 3,
            'subtotal' => 600.00, // 3 * 200
            'cleaning_fee' => 50.00,
            'service_fee' => 60.00, // 10% of subtotal
            'total' => 710.00,
        ]);
    }
}
