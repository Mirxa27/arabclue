<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_runs_migrations_without_errors()
    {
        $this->artisan('migrate:fresh');
        $this->assertTrue(Schema::hasTable('bookings'));
        $this->assertTrue(Schema::hasColumn('bookings', 'subtotal'));
    }
}
