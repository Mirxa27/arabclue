<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaraMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
        'suggested_actions',
        'intent',
        'confidence_score',
        'tokens_used'
    ];

    protected $casts = [
        'metadata' => 'array',
        'suggested_actions' => 'array',
        'confidence_score' => 'float'
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SaraConversation::class);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function isFromUser(): bool
    {
        return $this->role === 'user';
    }

    public function isFromAssistant(): bool
    {
        return $this->role === 'assistant';
    }
}