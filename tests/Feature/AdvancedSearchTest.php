<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Property;
use App\Models\Amenity;

class AdvancedSearchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->seed(); // Assuming you have a seeder for initial data
    }

    /** @test */
    public function it_can_search_with_multiple_filters()
    {
        // Create specific properties and amenities for testing
        $amenity1 = Amenity::factory()->create(['name' => 'Swimming Pool']);
        $amenity2 = Amenity::factory()->create(['name' => 'WiFi']);

        $property1 = Property::factory()->create([
            'title' => 'Luxury Villa with Pool',
            'city' => 'Riyadh',
            'price_per_night' => 1500,
            'property_type' => 'villa',
            'accommodates' => 8,
        ]);
        $property1->amenities()->attach([$amenity1->id, $amenity2->id]);

        $property2 = Property::factory()->create([
            'title' => 'Cozy Apartment in City Center',
            'city' => 'Riyadh',
            'price_per_night' => 400,
            'property_type' => 'apartment',
            'accommodates' => 4,
        ]);
        $property2->amenities()->attach($amenity2->id);

        // Test Case 1: Search for a luxury villa
        $response = $this->getJson('/api/v1/properties/search?city=Riyadh&property_type=villa&min_price=1000&amenities[]=' . $amenity1->id);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.id', $property1->id);

        // Test Case 2: Search for an apartment with WiFi
        $response = $this->getJson('/api/v1/properties/search?city=Riyadh&property_type=apartment&max_price=500&amenities[]=' . $amenity2->id);

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.id', $property2->id);
    }
}
