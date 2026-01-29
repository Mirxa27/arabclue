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
        Schema::create('email_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique()->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email_type'); // welcome, booking_confirmation, etc.
            $table->string('event_type')->default('sent'); // sent, opened, clicked, bounced
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->string('clicked_url')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->string('bounce_reason')->nullable();
            $table->json('metadata')->nullable(); // Additional data like campaign_id, etc.
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('email_client')->nullable(); // gmail, outlook, etc.
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'email_type']);
            $table->index(['email_type', 'created_at']);
            $table->index(['tracking_id']);
            $table->index(['sent_at']);
            $table->index(['opened_at']);
            $table->index(['clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_analytics');
    }
};
