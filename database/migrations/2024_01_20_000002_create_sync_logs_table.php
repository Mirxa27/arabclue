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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->nullable()->constrained()->onDelete('set null');
            $table->string('channel_name');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // channel_connected, property_sync, etc.
            $table->enum('status', ['success', 'error', 'warning', 'info']);
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['channel_id', 'status']);
            $table->index(['property_id', 'action']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
