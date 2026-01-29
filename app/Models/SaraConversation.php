<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaraConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', // UUID field for external reference
        'session_id', // Internal session identifier
        'user_id',
        'channel', // Added channel as it's in the migration and tests
        'context',
        // 'messages', // Removed, should be handled by relationship
        'status',
        'last_activity_at' // Corrected from 'last_activity'
    ];

    protected $casts = [
        'context' => 'array',
        // 'messages' => 'array', // Removed
        'last_activity_at' => 'datetime' // Corrected from 'last_activity'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SaraMessage::class, 'conversation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function end(): bool
    {
        return $this->update([
            'status' => 'completed',
            'last_activity_at' => now()
        ]);
    }
}
