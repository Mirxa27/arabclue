<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ContactInquiry Model - Contact Form Submission Management
 * 
 * Manages contact form submissions with categorization,
 * status tracking, and response management capabilities
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $subject
 * @property string $message
 * @property string $interested_in
 * @property string $status
 * @property int $assigned_to
 * @property \Carbon\Carbon $responded_at
 * @property string $response_notes
 * @property string $ip_address
 * @property string $user_agent
 * @property array $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ContactInquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'interested_in',
        'status',
        'assigned_to',
        'responded_at',
        'response_notes',
        'ip_address',
        'user_agent',
        'metadata',
        'source',
        'utm_campaign',
        'utm_source',
        'utm_medium',
        'referrer_url'
    ];

    protected $casts = [
        'metadata' => 'array',
        'responded_at' => 'datetime'
    ];

    protected $dates = [
        'responded_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Inquiry statuses
     */
    const STATUS_NEW = 'new';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESPONDED = 'responded';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_SPAM = 'spam';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Interest categories
     */
    const INTEREST_BOOKING = 'booking';
    const INTEREST_LISTING = 'listing';
    const INTEREST_INVESTING = 'investing';
    const INTEREST_PARTNERSHIP = 'partnership';
    const INTEREST_SUPPORT = 'support';
    const INTEREST_GENERAL = 'general';
    const INTEREST_MEDIA = 'media';
    const INTEREST_CAREERS = 'careers';

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESPONDED => 'Responded',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_SPAM => 'Spam',
            self::STATUS_ARCHIVED => 'Archived'
        ];
    }

    /**
     * Get available interest categories
     */
    public static function getInterestCategories(): array
    {
        return [
            self::INTEREST_BOOKING => 'Property Booking',
            self::INTEREST_LISTING => 'List My Property',
            self::INTEREST_INVESTING => 'Investment Opportunities',
            self::INTEREST_PARTNERSHIP => 'Partnership Inquiry',
            self::INTEREST_SUPPORT => 'Customer Support',
            self::INTEREST_GENERAL => 'General Inquiry',
            self::INTEREST_MEDIA => 'Media & Press',
            self::INTEREST_CAREERS => 'Career Opportunities'
        ];
    }

    /**
     * Relationship: Inquiry assigned to user (staff member)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope to get new inquiries
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope to get assigned inquiries
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED);
    }

    /**
     * Scope to get unresolved inquiries
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', [self::STATUS_RESOLVED, self::STATUS_SPAM, self::STATUS_ARCHIVED]);
    }

    /**
     * Scope to get by interest category
     */
    public function scopeByInterest($query, string $interest)
    {
        return $query->where('interested_in', $interest);
    }

    /**
     * Scope to get high priority inquiries
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('interested_in', [self::INTEREST_INVESTING, self::INTEREST_PARTNERSHIP]);
    }

    /**
     * Scope to get recent inquiries
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Create inquiry from contact form
     */
    public static function createFromForm(array $data, array $metadata = []): self
    {
        $inquiry = static::create(array_merge($data, [
            'status' => self::STATUS_NEW,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge($metadata, [
                'created_via' => 'contact_form',
                'url' => request()->url(),
                'referrer' => request()->header('referer'),
                'utm_params' => [
                    'utm_source' => request()->input('utm_source'),
                    'utm_medium' => request()->input('utm_medium'),
                    'utm_campaign' => request()->input('utm_campaign'),
                    'utm_term' => request()->input('utm_term'),
                    'utm_content' => request()->input('utm_content')
                ]
            ])
        ]));

        // Auto-assign based on interest category
        $inquiry->autoAssign();

        return $inquiry;
    }

    /**
     * Auto-assign inquiry based on category
     */
    public function autoAssign(): void
    {
        $assignmentRules = [
            self::INTEREST_BOOKING => 'customer_support',
            self::INTEREST_LISTING => 'property_specialist',
            self::INTEREST_INVESTING => 'business_development',
            self::INTEREST_PARTNERSHIP => 'business_development',
            self::INTEREST_SUPPORT => 'customer_support',
            self::INTEREST_MEDIA => 'marketing',
            self::INTEREST_CAREERS => 'hr'
        ];

        $role = $assignmentRules[$this->interested_in] ?? 'customer_support';
        
        // Find available staff member with the appropriate role
        $assignee = User::where('role', $role)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if ($assignee) {
            $this->assignTo($assignee->id);
        }
    }

    /**
     * Assign inquiry to a staff member
     */
    public function assignTo(int $userId): void
    {
        $this->update([
            'assigned_to' => $userId,
            'status' => self::STATUS_ASSIGNED
        ]);

        // Send notification to assigned user
        $assignedUser = User::find($userId);
        if ($assignedUser) {
            $assignedUser->notify(new \App\Notifications\InquiryAssigned($this));
        }
    }

    /**
     * Mark as responded
     */
    public function markAsResponded(string $responseNotes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESPONDED,
            'responded_at' => now(),
            'response_notes' => $responseNotes
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(string $responseNotes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'response_notes' => $responseNotes
        ]);
    }

    /**
     * Mark as spam
     */
    public function markAsSpam(): void
    {
        $this->update(['status' => self::STATUS_SPAM]);
        
        // Add to spam blacklist if needed
        $this->addToSpamFilter();
    }

    /**
     * Archive inquiry
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Add to spam filter
     */
    protected function addToSpamFilter(): void
    {
        // Add email to spam filter (implementation depends on your spam filtering system)
        cache()->put("spam_email:{$this->email}", true, now()->addDays(30));
    }

    /**
     * Check if inquiry is high priority
     */
    public function isHighPriority(): bool
    {
        return in_array($this->interested_in, [self::INTEREST_INVESTING, self::INTEREST_PARTNERSHIP]);
    }

    /**
     * Check if inquiry needs urgent attention
     */
    public function needsUrgentAttention(): bool
    {
        // More than 24 hours old and not responded
        return $this->created_at->lt(now()->subHours(24)) && 
               !in_array($this->status, [self::STATUS_RESPONDED, self::STATUS_RESOLVED]);
    }

    /**
     * Get response time in hours
     */
    public function getResponseTimeHours(): ?float
    {
        if (!$this->responded_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->responded_at);
    }

    /**
     * Get formatted response time
     */
    public function getFormattedResponseTime(): string
    {
        if (!$this->responded_at) {
            return 'Not responded';
        }

        $hours = $this->getResponseTimeHours();
        
        if ($hours < 1) {
            return $this->created_at->diffInMinutes($this->responded_at) . ' minutes';
        } elseif ($hours < 24) {
            return round($hours, 1) . ' hours';
        } else {
            return round($hours / 24, 1) . ' days';
        }
    }

    /**
     * Get priority level
     */
    public function getPriorityLevel(): string
    {
        if ($this->isHighPriority()) {
            return 'high';
        }
        
        if ($this->needsUrgentAttention()) {
            return 'urgent';
        }
        
        return 'normal';
    }

    /**
     * Get inquiry source
     */
    public function getSource(): string
    {
        return $this->source ?? 'website';
    }

    /**
     * Get UTM campaign data
     */
    public function getUtmData(): array
    {
        $metadata = $this->metadata ?? [];
        $utmParams = $metadata['utm_params'] ?? [];
        
        return [
            'source' => $utmParams['utm_source'] ?? null,
            'medium' => $utmParams['utm_medium'] ?? null,
            'campaign' => $utmParams['utm_campaign'] ?? null,
            'term' => $utmParams['utm_term'] ?? null,
            'content' => $utmParams['utm_content'] ?? null
        ];
    }

    /**
     * Generate response template
     */
    public function getResponseTemplate(): string
    {
        $templates = [
            self::INTEREST_BOOKING => "Dear {$this->name},\n\nThank you for your interest in booking with HabibiStay...\n\nBest regards,\nHabibiStay Team",
            self::INTEREST_LISTING => "Dear {$this->name},\n\nThank you for your interest in listing your property with HabibiStay...\n\nBest regards,\nHabibiStay Property Team",
            self::INTEREST_INVESTING => "Dear {$this->name},\n\nThank you for your interest in investment opportunities with HabibiStay...\n\nBest regards,\nHabibiStay Business Development",
            'default' => "Dear {$this->name},\n\nThank you for contacting HabibiStay...\n\nBest regards,\nHabibiStay Team"
        ];
        
        return $templates[$this->interested_in] ?? $templates['default'];
    }

    /**
     * Get inquiry statistics
     */
    public static function getStatistics(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'total' => static::where('created_at', '>=', $startDate)->count(),
            'by_status' => static::where('created_at', '>=', $startDate)
                ->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status')
                ->toArray(),
            'by_interest' => static::where('created_at', '>=', $startDate)
                ->groupBy('interested_in')
                ->selectRaw('interested_in, count(*) as count')
                ->pluck('count', 'interested_in')
                ->toArray(),
            'response_rate' => static::getResponseRate($days),
            'average_response_time' => static::getAverageResponseTime($days),
            'high_priority_count' => static::where('created_at', '>=', $startDate)
                ->whereIn('interested_in', [self::INTEREST_INVESTING, self::INTEREST_PARTNERSHIP])
                ->count()
        ];
    }

    /**
     * Get response rate percentage
     */
    protected static function getResponseRate(int $days): float
    {
        $startDate = now()->subDays($days);
        $total = static::where('created_at', '>=', $startDate)->count();
        $responded = static::where('created_at', '>=', $startDate)
            ->whereIn('status', [self::STATUS_RESPONDED, self::STATUS_RESOLVED])
            ->count();
            
        return $total > 0 ? round(($responded / $total) * 100, 2) : 0;
    }

    /**
     * Get average response time in hours
     */
    protected static function getAverageResponseTime(int $days): float
    {
        $startDate = now()->subDays($days);
        
        $inquiries = static::where('created_at', '>=', $startDate)
            ->whereNotNull('responded_at')
            ->get();
            
        if ($inquiries->isEmpty()) {
            return 0;
        }
        
        $totalHours = $inquiries->sum(function ($inquiry) {
            return $inquiry->getResponseTimeHours();
        });
        
        return round($totalHours / $inquiries->count(), 2);
    }

    /**
     * Clean up old archived inquiries
     */
    public static function cleanupOldInquiries(int $daysToKeep = 365): int
    {
        $cutoff = now()->subDays($daysToKeep);
        
        return static::where('status', self::STATUS_ARCHIVED)
            ->where('created_at', '<', $cutoff)
            ->delete();
    }

    /**
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        return "{$this->name} - {$this->subject}";
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_ASSIGNED => 'yellow',
            self::STATUS_IN_PROGRESS => 'orange',
            self::STATUS_RESPONDED => 'green',
            self::STATUS_RESOLVED => 'emerald',
            self::STATUS_SPAM => 'red',
            self::STATUS_ARCHIVED => 'gray',
            default => 'gray'
        };
    }
}