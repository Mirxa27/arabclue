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
        Schema::create('channel_property', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->nullable(); // ID on the external platform
            $table->enum('sync_status', ['synced', 'syncing', 'error', 'pending'])->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_data')->nullable(); // Additional sync information
            $table->timestamps();

            $table->unique(['channel_id', 'property_id']);
            $table->index(['property_id', 'sync_status']);
            $table->index(['channel_id', 'sync_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_property');
    }
};
