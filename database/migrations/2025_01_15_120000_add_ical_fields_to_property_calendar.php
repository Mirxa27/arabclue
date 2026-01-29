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
        Schema::table('property_calendar', function (Blueprint $table) {
            // Add fields for iCal integration
            $table->string('source')->nullable()->after('notes')->comment('Source of the calendar entry (external, booking, manual)');
            $table->string('external_id')->nullable()->after('source')->comment('External calendar event ID');
            $table->string('title')->nullable()->after('external_id')->comment('Event title for external calendars');
            
            // Add index for external calendar lookups
            $table->index(['property_id', 'source']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_calendar', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'source']);
            $table->dropIndex(['external_id']);
            $table->dropColumn(['source', 'external_id', 'title']);
        });
    }
};
