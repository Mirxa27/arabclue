<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;

/**
 * AIContentQueue Model - AI Content Generation Queue Management
 * 
 * Manages queued AI content generation tasks with priority scheduling,
 * retry logic, and comprehensive status tracking
 * 
 * @property int $id
 * @property string $content_type
 * @property string $target_model_type
 * @property int $target_model_id
 * @property int $requested_by
 * @property array $parameters
 * @property array $context
 * @property string $status
 * @property int $priority
 * @property int $retry_count
 * @property int $max_retries
 * @property array $generated_content
 * @property string $error_message
 * @property \Carbon\Carbon $scheduled_at
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AIContentQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'target_model_type',
        'target_model_id',
        'requested_by',
        'parameters',
        'context',
        'status',
        'priority',
        'retry_count',
        'max_retries',
        'generated_content',
        'error_message',
        'scheduled_at',
        'started_at',
        'completed_at',
        'estimated_tokens',
        'actual_tokens',
        'processing_time_ms'
    ];

    protected $casts = [
        'parameters' => 'array',
        'context' => 'array',
        'generated_content' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    protected $dates = [
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Queue statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAUSED = 'paused';

    /**
     * Priority levels
     */
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_HIGH = 8;
    const PRIORITY_URGENT = 10;

    /**
     * Content types
     */
    const TYPE_PROPERTY_DESCRIPTION = 'property_description';
    const TYPE_META_DESCRIPTION = 'meta_description';
    const TYPE_BLOG_POST = 'blog_post';
    const TYPE_MARKETING_COPY = 'marketing_copy';
    const TYPE_FAQ_ANSWER = 'faq_answer';
    const TYPE_EMAIL_TEMPLATE = 'email_template';
    const TYPE_SOCIAL_MEDIA = 'social_media';
    const TYPE_REVIEW_RESPONSE = 'review_response';

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PAUSED => 'Paused'
        ];
    }

    /**
     * Get available priorities
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent'
        ];
    }

    /**
     * Get available content types
     */
    public static function getContentTypes(): array
    {
        return [
            self::TYPE_PROPERTY_DESCRIPTION => 'Property Description',
            self::TYPE_META_DESCRIPTION => 'Meta Description',
            self::TYPE_BLOG_POST => 'Blog Post',
            self::TYPE_MARKETING_COPY => 'Marketing Copy',
            self::TYPE_FAQ_ANSWER => 'FAQ Answer',
            self::TYPE_EMAIL_TEMPLATE => 'Email Template',
            self::TYPE_SOCIAL_MEDIA => 'Social Media Post',
            self::TYPE_REVIEW_RESPONSE => 'Review Response'
        ];
    }

    /**
     * Relationship: Queue item requested by user
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relationship: Polymorphic target model
     */
    public function targetModel(): MorphTo
    {
        return $this->morphTo('target_model', 'target_model_type', 'target_model_id');
    }

    /**
     * Scope to get pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get processing items
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to get completed items
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get failed items
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get ready for processing
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    });
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Scope to get by content type
     */
    public function scopeByContentType($query, string $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Create a new AI content generation request
     */
    public static function createRequest(
        string $contentType,
        string $targetModelType,
        int $targetModelId,
        array $parameters,
        array $context = [],
        int $priority = self::PRIORITY_NORMAL,
        \Carbon\Carbon $scheduledAt = null
    ): self {
        return static::create([
            'content_type' => $contentType,
            'target_model_type' => $targetModelType,
            'target_model_id' => $targetModelId,
            'requested_by' => auth()->id(),
            'parameters' => $parameters,
            'context' => $context,
            'status' => self::STATUS_PENDING,
            'priority' => $priority,
            'retry_count' => 0,
            'max_retries' => 3,
            'scheduled_at' => $scheduledAt,
            'estimated_tokens' => static::estimateTokens($contentType, $parameters)
        ]);
    }

    /**
     * Estimate token usage for content generation
     */
    protected static function estimateTokens(string $contentType, array $parameters): int
    {
        return match ($contentType) {
            self::TYPE_PROPERTY_DESCRIPTION => 150,
            self::TYPE_META_DESCRIPTION => 50,
            self::TYPE_BLOG_POST => 800,
            self::TYPE_MARKETING_COPY => 200,
            self::TYPE_FAQ_ANSWER => 100,
            self::TYPE_EMAIL_TEMPLATE => 300,
            self::TYPE_SOCIAL_MEDIA => 50,
            self::TYPE_REVIEW_RESPONSE => 80,
            default => 100
        };
    }

    /**
     * Get next item for processing
     */
    public static function getNextForProcessing(): ?self
    {
        return static::readyForProcessing()
            ->byPriority()
            ->first();
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now()
        ]);
    }

    /**
     * Mark as completed with generated content
     */
    public function markAsCompleted(array $generatedContent, int $actualTokens = null): void
    {
        $processingTime = $this->started_at ? now()->diffInMilliseconds($this->started_at) : null;
        
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'generated_content' => $generatedContent,
            'actual_tokens' => $actualTokens,
            'processing_time_ms' => $processingTime,
            'completed_at' => now(),
            'error_message' => null
        ]);

        // Apply generated content to target model if applicable
        $this->applyGeneratedContent();
    }

    /**
     * Mark as failed with error message
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now()
        ]);

        Log::error('AI content generation failed', [
            'queue_id' => $this->id,
            'content_type' => $this->content_type,
            'error' => $errorMessage
        ]);
    }

    /**
     * Retry failed item
     */
    public function retry(): bool
    {
        if ($this->retry_count >= $this->max_retries) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PENDING,
            'retry_count' => $this->retry_count + 1,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null
        ]);

        return true;
    }

    /**
     * Cancel the queue item
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now()
        ]);
    }

    /**
     * Pause the queue item
     */
    public function pause(): void
    {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    /**
     * Resume the queue item
     */
    public function resume(): void
    {
        if ($this->status === self::STATUS_PAUSED) {
            $this->update(['status' => self::STATUS_PENDING]);
        }
    }

    /**
     * Apply generated content to target model
     */
    protected function applyGeneratedContent(): void
    {
        if (!$this->targetModel || !$this->generated_content) {
            return;
        }

        try {
            $content = $this->generated_content;
            $target = $this->targetModel;

            match ($this->content_type) {
                self::TYPE_PROPERTY_DESCRIPTION => $target->update([
                    'description' => $content['description'] ?? '',
                    'ai_generated' => true
                ]),
                self::TYPE_META_DESCRIPTION => $target->update([
                    'meta_description' => $content['meta_description'] ?? '',
                    'ai_generated_meta' => true
                ]),
                default => null
            };

        } catch (\Exception $e) {
            Log::error('Failed to apply generated content', [
                'queue_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get processing progress
     */
    public function getProgress(): array
    {
        $totalTime = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;
        $estimatedTime = $this->getEstimatedProcessingTime();
        
        $progress = $estimatedTime > 0 ? min(100, ($totalTime / $estimatedTime) * 100) : 0;

        return [
            'status' => $this->status,
            'progress_percentage' => round($progress, 1),
            'elapsed_time' => $totalTime,
            'estimated_time' => $estimatedTime,
            'retry_count' => $this->retry_count,
            'max_retries' => $this->max_retries
        ];
    }

    /**
     * Get estimated processing time in seconds
     */
    protected function getEstimatedProcessingTime(): int
    {
        return match ($this->content_type) {
            self::TYPE_PROPERTY_DESCRIPTION => 15,
            self::TYPE_META_DESCRIPTION => 5,
            self::TYPE_BLOG_POST => 60,
            self::TYPE_MARKETING_COPY => 20,
            self::TYPE_FAQ_ANSWER => 10,
            self::TYPE_EMAIL_TEMPLATE => 25,
            self::TYPE_SOCIAL_MEDIA => 8,
            self::TYPE_REVIEW_RESPONSE => 10,
            default => 15
        };
    }

    /**
     * Get queue statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => static::count(),
            'pending' => static::pending()->count(),
            'processing' => static::processing()->count(),
            'completed' => static::completed()->count(),
            'failed' => static::failed()->count(),
            'success_rate' => static::getSuccessRate(),
            'average_processing_time' => static::getAverageProcessingTime(),
            'daily_volume' => static::getDailyVolume()
        ];
    }

    /**
     * Get success rate percentage
     */
    protected static function getSuccessRate(): float
    {
        $total = static::whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED])->count();
        $completed = static::completed()->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Get average processing time in seconds
     */
    protected static function getAverageProcessingTime(): float
    {
        $avgMs = static::completed()
            ->whereNotNull('processing_time_ms')
            ->avg('processing_time_ms');
            
        return $avgMs ? round($avgMs / 1000, 2) : 0;
    }

    /**
     * Get daily volume
     */
    protected static function getDailyVolume(): array
    {
        $today = static::whereDate('created_at', today())->count();
        $yesterday = static::whereDate('created_at', today()->subDay())->count();
        
        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'change' => $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0
        ];
    }

    /**
     * Clean up old completed items
     */
    public static function cleanupOldItems(int $daysToKeep = 30): int
    {
        $cutoff = now()->subDays($daysToKeep);
        
        return static::whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED])
            ->where('completed_at', '<', $cutoff)
            ->delete();
    }

    /**
     * Check if item can be retried
     */
    public function canBeRetried(): bool
    {
        return $this->status === self::STATUS_FAILED && 
               $this->retry_count < $this->max_retries;
    }

    /**
     * Check if item is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Check if item is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if item has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        $typeName = self::getContentTypes()[$this->content_type] ?? $this->content_type;
        return "{$typeName} for {$this->target_model_type} #{$this->target_model_id}";
    }
}