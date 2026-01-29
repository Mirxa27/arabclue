<?php

namespace Tests\Unit\Services;

use App\Services\BookingService;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase, MockeryPHPUnitIntegration;

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->mock(PaymentService::class);
        $service = $this->app->make(BookingService::class);

        $this->assertInstanceOf(BookingService::class, $service);
    }

    /** @test */
    public function it_generates_a_valid_booking_reference()
    {
        $this->mock(PaymentService::class);
        $service = $this->app->make(BookingService::class);

        $reference = $service->generateBookingReference();

        $this->assertIsString($reference);
        $this->assertStringStartsWith('HB', $reference);
        $this->assertEquals(10, strlen($reference));
    }
}