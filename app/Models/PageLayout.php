<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRevisions;
use App\Traits\Cacheable;

/**
 * PageLayout Model - Dynamic Page Layout Management
 * 
 * Manages page layouts with component-based architecture
 * for flexible content arrangement and theme customization
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $theme_id
 * @property array $layout_config
 * @property array $components
 * @property array $seo_settings
 * @property bool $is_active
 * @property bool $is_default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PageLayout extends Model
{
    use HasFactory, SoftDeletes, HasRevisions, Cacheable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'theme_id',
        'layout_config',
        'components',
        'seo_settings',
        'is_active',
        'is_default',
        'page_type',
        'template_path',
        'css_classes',
        'javascript_code',
        'meta_tags'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'components' => 'array',
        'seo_settings' => 'array',
        'meta_tags' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Page types
     */
    const TYPE_HOME = 'home';
    const TYPE_PROPERTY_LIST = 'property_list';
    const TYPE_PROPERTY_DETAIL = 'property_detail';
    const TYPE_BOOKING = 'booking';
    const TYPE_PROFILE = 'profile';
    const TYPE_STATIC = 'static';
    const TYPE_BLOG = 'blog';
    const TYPE_CONTACT = 'contact';

    /**
     * Get available page types
     */
    public static function getPageTypes(): array
    {
        return [
            self::TYPE_HOME => 'Homepage',
            self::TYPE_PROPERTY_LIST => 'Property Listing',
            self::TYPE_PROPERTY_DETAIL => 'Property Detail',
            self::TYPE_BOOKING => 'Booking Page',
            self::TYPE_PROFILE => 'User Profile',
            self::TYPE_STATIC => 'Static Page',
            self::TYPE_BLOG => 'Blog Page',
            self::TYPE_CONTACT => 'Contact Page'
        ];
    }

    /**
     * Relationship: Layout belongs to a theme
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Relationship: Layout has many UI components
     */
    public function uiComponents(): HasMany
    {
        return $this->hasMany(UiComponent::class);
    }

    /**
     * Scope to get active layouts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default layout
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get by page type
     */
    public function scopeByPageType($query, string $pageType)
    {
        return $query->where('page_type', $pageType);
    }

    /**
     * Scope to get by theme
     */
    public function scopeByTheme($query, int $themeId)
    {
        return $query->where('theme_id', $themeId);
    }

    /**
     * Get default layout for page type
     */
    public static function getDefaultForPageType(string $pageType): ?self
    {
        $cacheKey = "default_layout_{$pageType}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($pageType) {
            return static::active()
                ->byPageType($pageType)
                ->default()
                ->first();
        });
    }

    /**
     * Get layout by slug with caching
     */
    public static function getBySlug(string $slug): ?self
    {
        $cacheKey = "page_layout_slug_{$slug}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($slug) {
            return static::active()
                ->where('slug', $slug)
                ->with(['theme', 'uiComponents'])
                ->first();
        });
    }

    /**
     * Get layout configuration with component data
     */
    public function getFullConfiguration(): array
    {
        $config = $this->layout_config ?? [];
        
        // Merge with theme settings
        if ($this->theme) {
            $themeConfig = $this->theme->layout_settings ?? [];
            $config = array_merge($themeConfig, $config);
        }
        
        // Add component definitions
        $config['components'] = $this->getComponentDefinitions();
        
        return $config;
    }

    /**
     * Get component definitions
     */
    protected function getComponentDefinitions(): array
    {
        $components = [];
        
        foreach ($this->uiComponents as $component) {
            $components[$component->component_key] = [
                'type' => $component->component_type,
                'config' => $component->configuration,
                'position' => $component->position,
                'order' => $component->sort_order,
                'enabled' => $component->is_enabled
            ];
        }
        
        return $components;
    }

    /**
     * Add component to layout
     */
    public function addComponent(array $componentData): UiComponent
    {
        return $this->uiComponents()->create(array_merge($componentData, [
            'page_layout_id' => $this->id
        ]));
    }

    /**
     * Remove component from layout
     */
    public function removeComponent(string $componentKey): bool
    {
        return $this->uiComponents()
            ->where('component_key', $componentKey)
            ->delete() > 0;
    }

    /**
     * Update component configuration
     */
    public function updateComponent(string $componentKey, array $config): bool
    {
        return $this->uiComponents()
            ->where('component_key', $componentKey)
            ->update(['configuration' => $config]) > 0;
    }

    /**
     * Get SEO settings
     */
    public function getSeoSettings(): array
    {
        return array_merge([
            'title' => $this->name,
            'description' => $this->description,
            'keywords' => '',
            'og_title' => $this->name,
            'og_description' => $this->description,
            'og_image' => '',
            'canonical_url' => '',
            'noindex' => false,
            'nofollow' => false
        ], $this->seo_settings ?? []);
    }

    /**
     * Update SEO settings
     */
    public function updateSeoSettings(array $seoData): void
    {
        $currentSeo = $this->seo_settings ?? [];
        $updatedSeo = array_merge($currentSeo, $seoData);
        
        $this->update(['seo_settings' => $updatedSeo]);
    }

    /**
     * Get custom CSS classes
     */
    public function getCssClasses(): string
    {
        $classes = [$this->page_type . '-page'];
        
        if ($this->css_classes) {
            $classes[] = $this->css_classes;
        }
        
        if ($this->theme) {
            $classes[] = 'theme-' . $this->theme->slug;
        }
        
        return implode(' ', $classes);
    }

    /**
     * Get template path for rendering
     */
    public function getTemplatePath(): string
    {
        return $this->template_path ?? "layouts.{$this->page_type}";
    }

    /**
     * Generate layout preview
     */
    public function generatePreview(): array
    {
        return [
            'layout_id' => $this->id,
            'name' => $this->name,
            'type' => $this->page_type,
            'theme' => $this->theme?->name,
            'components' => $this->uiComponents->map(function ($component) {
                return [
                    'key' => $component->component_key,
                    'type' => $component->component_type,
                    'position' => $component->position,
                    'enabled' => $component->is_enabled
                ];
            }),
            'screenshot_url' => $this->getScreenshotUrl()
        ];
    }

    /**
     * Get screenshot URL for preview
     */
    protected function getScreenshotUrl(): ?string
    {
        // This would generate or return cached screenshot URL
        return asset("screenshots/layouts/{$this->id}.png");
    }

    /**
     * Clone layout with new name
     */
    public function cloneLayout(string $newName, string $newSlug = null): self
    {
        $newSlug = $newSlug ?? \Str::slug($newName);
        
        $clone = $this->replicate([
            'name',
            'slug',
            'is_default',
            'created_at',
            'updated_at'
        ]);
        
        $clone->fill([
            'name' => $newName,
            'slug' => $newSlug,
            'is_default' => false
        ]);
        
        $clone->save();
        
        // Clone UI components
        foreach ($this->uiComponents as $component) {
            $componentClone = $component->replicate(['created_at', 'updated_at']);
            $componentClone->page_layout_id = $clone->id;
            $componentClone->save();
        }
        
        return $clone;
    }

    /**
     * Set as default for page type
     */
    public function setAsDefault(): void
    {
        // Remove default flag from other layouts of same type
        static::byPageType($this->page_type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
        
        // Clear cache
        cache()->forget("default_layout_{$this->page_type}");
    }

    /**
     * Clear layout cache
     */
    public function clearCache(): void
    {
        cache()->forget("page_layout_slug_{$this->slug}");
        cache()->forget("default_layout_{$this->page_type}");
        
        // Clear component cache if needed
        foreach ($this->uiComponents as $component) {
            $component->clearCache();
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
        return 'slug';
    }

    /**
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        return $this->name;
    }
}