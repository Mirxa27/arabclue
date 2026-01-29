<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, we need to check if the incorrect index exists and drop it
        if (Schema::hasTable('bookings')) {
            // SQLite doesn't support dropping indexes directly through Schema builder
            // We need to use raw SQL
            try {
                // Drop the incorrect index if it exists
                DB::statement('DROP INDEX IF EXISTS bookings_property_id_ical_uid_index');
                
                // Check if the correct index doesn't already exist before creating it
                $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='bookings' AND name='bookings_property_id_uid_index'");
                
                if (empty($indexes)) {
                    // Create the correct index
                    Schema::table('bookings', function (Blueprint $table) {
                        $table->index(['property_id', 'uid'], 'bookings_property_id_uid_index');
                    });
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the migration
                \Log::warning('Failed to fix bookings index: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // In the down migration, we would recreate the incorrect index
        // But this is not recommended as it would break the application
        // So we'll leave this empty
    }
};
