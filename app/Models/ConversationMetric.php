<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'duration_minutes',
        'message_count',
        'user_messages',
        'assistant_messages',
        'channel',
        'has_booking',
        'intent',
    ];
}
