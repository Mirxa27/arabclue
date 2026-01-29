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
        Schema::create('external_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('url');
            $table->boolean('auto_sync')->default(true);
            $table->enum('sync_frequency', ['hourly', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // Add source_calendar_id column to property_calendar table
        Schema::table('property_calendar', function (Blueprint $table) {
            if (!Schema::hasColumn('property_calendar', 'source_calendar_id')) {
                $table->foreignId('source_calendar_id')->nullable()->after('booking_id')
                    ->references('id')->on('external_calendars')->onDelete('set null');
            }
        });

        // Add an ical token to properties
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'calendar_token')) {
                $table->string('calendar_token', 64)->nullable()->after('status');
                $table->index('calendar_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_calendar', function (Blueprint $table) {
            $table->dropForeign(['source_calendar_id']);
            $table->dropColumn('source_calendar_id');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('calendar_token');
        });

        Schema::dropIfExists('external_calendars');
    }
};
