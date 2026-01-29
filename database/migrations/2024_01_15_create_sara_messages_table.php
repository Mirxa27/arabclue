<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('sara_messages');
        Schema::create('sara_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sara_conversation_id')->constrained('sara_conversations')->onDelete('cascade');
            $table->string('role');
            $table->text('content');
            $table->string('voice_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sara_messages');
    }
};
