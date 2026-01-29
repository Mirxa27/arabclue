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
        Schema::create('sara_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('ai_provider')->default('openai');
            $table->string('model_name')->default('gpt-4');
            $table->text('api_key')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->text('sara_personality')->nullable();
            $table->text('initial_greeting')->nullable();
            $table->string('featured_properties_method')->default('automatic');
            $table->json('featured_properties')->nullable();
            $table->boolean('enable_voice_input')->default(true);
            $table->boolean('enable_button_interface')->default(true);
            $table->string('chat_interface_style')->default('floating');
            $table->string('primary_color')->default('#2957c3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sara_configurations');
    }
};
