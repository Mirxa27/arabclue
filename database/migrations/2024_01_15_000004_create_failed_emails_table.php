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
        Schema::create('failed_emails', function (Blueprint $table) {
            $table->id();
            $table->json('email_data');
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('failed_at');
            $table->timestamps();

            // Indexes
            $table->index(['failed_at']);
            $table->index(['retry_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_emails');
    }
};
