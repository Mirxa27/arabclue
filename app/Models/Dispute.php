<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Dispute Model - Booking Dispute Management
 * 
 * Handles disputes between guests and hosts for booking-related issues
 * 
 * @property int $id
 * @property string $dispute_id
 * @property int $booking_id
 * @property int $raised_by
 * @property int $against_user_id
 * @property string $type
 * @property string $subject
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property float|null $amount_disputed
 * @property float|null $refund_amount
 * @property array|null $evidence
 * @property int|null $assigned_to
 * @property string|null $admin_notes
 * @property string|null $resolution
 * @property \Carbon\Carbon|null $resolved_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Dispute extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Dispute types
     */
    const TYPE_CANCELLATION = 'cancellation';
    const TYPE_REFUND = 'refund';
    const TYPE_PROPERTY_ISSUE = 'property_issue';
    const TYPE_CLEANLINESS = 'cleanliness';
    const TYPE_SAFETY = 'safety';
    const TYPE_AMENITIES = 'amenities';
    const TYPE_HOST_BEHAVIOR = 'host_behavior';
    const TYPE_GUEST_BEHAVIOR = 'guest_behavior';
    const TYPE_PAYMENT = 'payment';
    const TYPE_OTHER = 'other';

    /**
     * Dispute status
     */
    const STATUS_OPEN = 'open';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_WAITING_RESPONSE = 'waiting_response';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Dispute priority
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'dispute_id',
        'booking_id',
        'raised_by',
        'against_user_id',
        'type',
        'subject',
        'description',
        'status',
        'priority',
        'amount_disputed',
        'refund_amount',
        'evidence',
        'assigned_to',
        'admin_notes',
        'resolution',
        'resolved_at'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'amount_disputed' => 'float',
        'refund_amount' => 'float',
        'evidence' => 'array',
        'resolved_at' => 'datetime'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'priority' => self::PRIORITY_MEDIUM,
        'evidence' => '[]'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispute) {
            if (empty($dispute->dispute_id)) {
                $dispute->dispute_id = static::generateDisputeId();
            }
        });

        static::saved(function ($dispute) {
            // Send notifications on status changes
            if ($dispute->wasChanged('status')) {
                $dispute->sendStatusNotification();
            }
        });
    }

    /**
     * Generate unique dispute ID
     */
    public static function generateDisputeId(): string
    {
        do {
            $id = 'DSP-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('dispute_id', $id)->exists());

        return $id;
    }

    /**
     * Booking relationship
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * User who raised the dispute
     */
    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    /**
     * User against whom dispute is raised
     */
    public function againstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    /**
     * Admin assigned to handle dispute
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Dispute messages/communications
     */
    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Add evidence to dispute
     */
    public function addEvidence(array $evidenceItem): void
    {
        $evidence = $this->evidence ?? [];
        $evidence[] = [
            'type' => $evidenceItem['type'], // 'image', 'document', 'video'
            'file_path' => $evidenceItem['file_path'],
            'description' => $evidenceItem['description'] ?? null,
            'uploaded_at' => now()->toISOString()
        ];
        
        $this->update(['evidence' => $evidence]);
    }

    /**
     * Assign to admin
     */
    public function assignToAdmin(int $adminId): void
    {
        $this->update([
            'assigned_to' => $adminId,
            'status' => self::STATUS_IN_REVIEW
        ]);
    }

    /**
     * Update status
     */
    public function updateStatus(string $status, string $notes = null): void
    {
        $updateData = ['status' => $status];
        
        if ($notes) {
            $updateData['admin_notes'] = $this->admin_notes . "\n\n" . now()->format('Y-m-d H:i:s') . ": " . $notes;
        }
        
        if ($status === self::STATUS_RESOLVED || $status === self::STATUS_CLOSED) {
            $updateData['resolved_at'] = now();
        }
        
        $this->update($updateData);
    }

    /**
     * Resolve dispute
     */
    public function resolve(string $resolution, float $refundAmount = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolution' => $resolution,
            'refund_amount' => $refundAmount,
            'resolved_at' => now()
        ]);

        // Process refund if amount is specified
        if ($refundAmount && $refundAmount > 0) {
            $this->processRefund($refundAmount);
        }
    }

    /**
     * Close dispute
     */
    public function close(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'resolved_at' => now(),
            'admin_notes' => $this->admin_notes . "\n\nClosed: " . ($reason ?? 'No reason provided')
        ]);
    }

    /**
     * Reopen dispute
     */
    public function reopen(): void
    {
        $this->update([
            'status' => self::STATUS_OPEN,
            'resolved_at' => null
        ]);
    }

    /**
     * Process refund
     */
    protected function processRefund(float $amount): void
    {
        // This would integrate with your payment system
        // For now, we'll just log the refund request
        activity()
            ->performedOn($this)
            ->withProperties([
                'refund_amount' => $amount,
                'booking_id' => $this->booking_id,
                'user_id' => $this->raised_by
            ])
            ->log('refund_processed');
    }

    /**
     * Send status notification
     */
    protected function sendStatusNotification(): void
    {
        $users = [$this->raisedBy, $this->againstUser];
        
        foreach ($users as $user) {
            if ($user) {
                $user->sendPushNotification(
                    "Dispute Update",
                    "Your dispute {$this->dispute_id} status has been updated to: {$this->status}",
                    [
                        'type' => 'dispute_update',
                        'dispute_id' => $this->id,
                        'status' => $this->status
                    ]
                );
            }
        }
    }

    /**
     * Get dispute timeline
     */
    public function getTimeline(): array
    {
        $timeline = [];
        
        // Add creation
        $timeline[] = [
            'date' => $this->created_at,
            'action' => 'Dispute Created',
            'description' => "Dispute raised by {$this->raisedBy->name}",
            'user' => $this->raisedBy->name
        ];
        
        // Add messages
        foreach ($this->messages as $message) {
            $timeline[] = [
                'date' => $message->created_at,
                'action' => 'Message Added',
                'description' => \Illuminate\Support\Str::limit($message->content, 100),
                'user' => $message->sender->name
            ];
        }
        
        // Add resolution
        if ($this->resolved_at) {
            $timeline[] = [
                'date' => $this->resolved_at,
                'action' => 'Dispute Resolved',
                'description' => $this->resolution ?? 'Dispute was resolved',
                'user' => $this->assignedAdmin?->name ?? 'System'
            ];
        }
        
        return collect($timeline)->sortBy('date')->values()->toArray();
    }

    /**
     * Check if dispute can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_WAITING_RESPONSE]);
    }

    /**
     * Check if user can participate in dispute
     */
    public function canUserParticipate(int $userId): bool
    {
        return $this->raised_by === $userId || 
               $this->against_user_id === $userId || 
               $this->assigned_to === $userId;
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status badge info for UI
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            self::STATUS_OPEN => ['text' => 'Open', 'color' => 'red'],
            self::STATUS_IN_REVIEW => ['text' => 'In Review', 'color' => 'yellow'],
            self::STATUS_WAITING_RESPONSE => ['text' => 'Waiting Response', 'color' => 'blue'],
            self::STATUS_RESOLVED => ['text' => 'Resolved', 'color' => 'green'],
            self::STATUS_CLOSED => ['text' => 'Closed', 'color' => 'gray'],
            default => ['text' => 'Unknown', 'color' => 'gray']
        };
    }

    /**
     * Scope for open disputes
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_REVIEW, self::STATUS_WAITING_RESPONSE]);
    }

    /**
     * Scope for resolved disputes
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope for disputes by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for disputes involving user
     */
    public function scopeInvolvingUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('raised_by', $userId)
              ->orWhere('against_user_id', $userId);
        });
    }

    /**
     * Scope for assigned disputes
     */
    public function scopeAssignedTo($query, int $adminId)
    {
        return $query->where('assigned_to', $adminId);
    }

    /**
     * Get dispute statistics
     */
    public static function getStatistics(): array
    {
        $disputes = static::all();
        
        return [
            'total' => $disputes->count(),
            'open' => $disputes->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_REVIEW, self::STATUS_WAITING_RESPONSE])->count(),
            'resolved' => $disputes->where('status', self::STATUS_RESOLVED)->count(),
            'closed' => $disputes->where('status', self::STATUS_CLOSED)->count(),
            'by_type' => $disputes->groupBy('type')->map->count()->toArray(),
            'by_priority' => $disputes->groupBy('priority')->map->count()->toArray(),
            'total_refunded' => $disputes->whereNotNull('refund_amount')->sum('refund_amount'),
            'average_resolution_time' => $disputes->where('status', self::STATUS_RESOLVED)
                                                 ->avg(function ($dispute) {
                                                     return $dispute->resolved_at?->diffInHours($dispute->created_at);
                                                 })
        ];
    }

    /**
     * Create dispute for booking
     */
    public static function createForBooking(
        int $bookingId,
        int $raisedBy,
        array $disputeData
    ): self {
        $booking = Booking::findOrFail($bookingId);
        $againstUserId = $raisedBy === $booking->user_id ? $booking->host_id : $booking->user_id;
        
        return static::create([
            'booking_id' => $bookingId,
            'raised_by' => $raisedBy,
            'against_user_id' => $againstUserId,
            'type' => $disputeData['type'],
            'subject' => $disputeData['subject'],
            'description' => $disputeData['description'],
            'priority' => $disputeData['priority'] ?? self::PRIORITY_MEDIUM,
            'amount_disputed' => $disputeData['amount_disputed'] ?? null,
            'evidence' => $disputeData['evidence'] ?? []
        ]);
    }
}