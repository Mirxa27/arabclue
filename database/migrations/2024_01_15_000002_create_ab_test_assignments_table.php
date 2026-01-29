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
        Schema::create('ab_test_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email_type'); // welcome, booking_confirmation, etc.
            $table->string('variant'); // variant_a, variant_b, etc.
            $table->timestamp('assigned_at');
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'email_type']);
            $table->index(['email_type', 'variant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ab_test_assignments');
    }
};
