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
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email_type');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['scheduled', 'sent', 'failed', 'cancelled'])->default('scheduled');
            $table->json('email_data')->nullable();
            $table->string('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['scheduled_for', 'status']);
            $table->index(['user_id', 'email_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
