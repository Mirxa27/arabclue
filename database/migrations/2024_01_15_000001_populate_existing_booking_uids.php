<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Populate UIDs for existing bookings to ensure iCal export compatibility.
     */
    public function up(): void
    {
        // Get all bookings that don't have a UID yet
        $bookings = DB::table('bookings')->whereNull('uid')->get();
        
        foreach ($bookings as $booking) {
            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'uid' => (string) Str::uuid(),
                    'source' => $booking->ical_uid ? 'ical_import' : 'direct'
                ]);
        }
        
        \Log::info("Populated UIDs for {$bookings->count()} existing bookings");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it populates data
        // The previous migration handles the column removal
    }
};
