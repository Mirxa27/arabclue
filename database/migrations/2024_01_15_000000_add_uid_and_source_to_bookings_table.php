<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add UUID column for stable iCal event identification
            $table->uuid('uid')->unique()->after('id')->nullable();
            
            // Add source column to track booking origin
            $table->string('source', 50)->default('direct')->after('status');
            
            // Add index for better performance on iCal sync operations
            // The column is named 'uid', so use it for indexing
            $table->index(['property_id', 'uid']);
            $table->index(['property_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'source']);
            $table->dropIndex(['property_id', 'uid']);
            $table->dropColumn(['uid', 'source']);
        });
    }
};
