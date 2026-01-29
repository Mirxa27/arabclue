<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRevisions;
use App\Traits\Cacheable;

/**
 * ContentBlock Model - Dynamic Content Management
 * 
 * Manages reusable content blocks for dynamic page composition
 * with multi-language support and AI-generated content capabilities
 * 
 * @property int $id
 * @property string $identifier
 * @property string $title
 * @property string $content
 * @property string $type
 * @property string $language
 * @property array $metadata
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ContentBlock extends Model
{
    use HasFactory, HasRevisions, Cacheable;

    protected $fillable = [
        'identifier',
        'title',
        'content',
        'type',
        'language',
        'metadata',
        'is_active',
        'ai_generated',
        'ai_prompt',
        'seo_title',
        'seo_description',
        'tags'
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'ai_generated' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Content block types
     */
    const TYPE_TEXT = 'text';
    const TYPE_HTML = 'html';
    const TYPE_MARKDOWN = 'markdown';
    const TYPE_HERO = 'hero';
    const TYPE_FEATURE = 'feature';
    const TYPE_TESTIMONIAL = 'testimonial';
    const TYPE_FAQ = 'faq';
    const TYPE_CTA = 'cta';

    /**
     * Get all available content types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Plain Text',
            self::TYPE_HTML => 'HTML Content',
            self::TYPE_MARKDOWN => 'Markdown',
            self::TYPE_HERO => 'Hero Section',
            self::TYPE_FEATURE => 'Feature Block',
            self::TYPE_TESTIMONIAL => 'Testimonial',
            self::TYPE_FAQ => 'FAQ Item',
            self::TYPE_CTA => 'Call to Action'
        ];
    }

    /**
     * Scope to get active content blocks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get by identifier
     */
    public function scopeByIdentifier($query, string $identifier)
    {
        return $query->where('identifier', $identifier);
    }

    /**
     * Scope to get by language
     */
    public function scopeByLanguage($query, string $language = null)
    {
        $language = $language ?? app()->getLocale();
        return $query->where('language', $language);
    }

    /**
     * Scope to get by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get content block by identifier with caching
     */
    public static function getByIdentifier(string $identifier, string $language = null): ?self
    {
        $language = $language ?? app()->getLocale();
        $cacheKey = "content_block_{$identifier}_{$language}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($identifier, $language) {
            return static::active()
                ->byIdentifier($identifier)
                ->byLanguage($language)
                ->first();
        });
    }

    /**
     * Get rendered content
     */
    public function getRenderedContent(): string
    {
        return match ($this->type) {
            self::TYPE_MARKDOWN => $this->renderMarkdown(),
            self::TYPE_HTML => $this->content,
            default => e($this->content)
        };
    }

    /**
     * Render markdown content
     */
    protected function renderMarkdown(): string
    {
        // Basic markdown rendering (you might want to use a proper markdown library)
        $content = $this->content;
        
        // Headers
        $content = preg_replace('/^### (.*)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*)/m', '<h1>$1</h1>', $content);
        
        // Bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $content);
        
        // Line breaks
        $content = nl2br($content);
        
        return $content;
    }

    /**
     * Get metadata value
     */
    public function getMeta(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Set metadata value
     */
    public function setMeta(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Check if content was AI generated
     */
    public function isAIGenerated(): bool
    {
        return $this->ai_generated === true;
    }

    /**
     * Mark as AI generated
     */
    public function markAsAIGenerated(string $prompt = null): void
    {
        $this->update([
            'ai_generated' => true,
            'ai_prompt' => $prompt
        ]);
    }

    /**
     * Get SEO title or fallback to title
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?? $this->title;
    }

    /**
     * Get SEO description or generate from content
     */
    public function getSeoDescription(): string
    {
        if ($this->seo_description) {
            return $this->seo_description;
        }
        
        // Generate from content (first 160 characters)
        $cleanContent = strip_tags($this->getRenderedContent());
        return substr($cleanContent, 0, 160) . (strlen($cleanContent) > 160 ? '...' : '');
    }

    /**
     * Get content blocks for a specific page
     */
    public static function getForPage(string $page, string $language = null): array
    {
        $language = $language ?? app()->getLocale();
        $cacheKey = "page_content_blocks_{$page}_{$language}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($page, $language) {
            return static::active()
                ->byLanguage($language)
                ->where('identifier', 'like', $page . '%')
                ->orderBy('identifier')
                ->get()
                ->keyBy('identifier')
                ->toArray();
        });
    }

    /**
     * Clear content cache
     */
    public function clearCache(): void
    {
        $languages = ['en', 'ar', 'es', 'fr']; // Add your supported languages
        
        foreach ($languages as $language) {
            cache()->forget("content_block_{$this->identifier}_{$language}");
        }
        
        // Clear page cache if identifier contains page name
        if (str_contains($this->identifier, '_')) {
            $page = explode('_', $this->identifier)[0];
            foreach ($languages as $language) {
                cache()->forget("page_content_blocks_{$page}_{$language}");
            }
        }
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($model) {
            $model->clearCache();
        });
        
        static::deleted(function ($model) {
            $model->clearCache();
        });
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'identifier';
    }

    /**
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        return $this->title ?: $this->identifier;
    }
}