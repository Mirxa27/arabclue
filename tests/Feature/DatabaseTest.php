<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookings_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasColumn('bookings', 'id'));
        $this->assertTrue(Schema::hasColumn('bookings', 'total_nights'));
        $this->assertTrue(Schema::hasColumn('bookings', 'subtotal'));
    }
}
