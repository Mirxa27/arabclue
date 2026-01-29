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
        Schema::table('properties', function (Blueprint $table) {
            // Add iCal synchronization columns
            $table->string('ical_url')->nullable()->after('status');
            $table->timestamp('ical_last_sync')->nullable()->after('ical_url');
            $table->enum('ical_sync_status', ['pending', 'success', 'failed'])->default('pending')->after('ical_last_sync');
            $table->text('ical_last_error')->nullable()->after('ical_sync_status');
            
            // Add index for sync operations
            $table->index(['ical_sync_status', 'ical_last_sync']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['ical_sync_status', 'ical_last_sync']);
            $table->dropColumn(['ical_url', 'ical_last_sync', 'ical_sync_status', 'ical_last_error']);
        });
    }
};
