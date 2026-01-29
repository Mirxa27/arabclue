<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Cacheable;

/**
 * UiComponent Model - Dynamic UI Component Management
 * 
 * Manages reusable UI components for flexible page layouts
 * with configuration-driven rendering and theme integration
 * 
 * @property int $id
 * @property int $page_layout_id
 * @property string $component_key
 * @property string $component_type
 * @property string $position
 * @property int $sort_order
 * @property array $configuration
 * @property array $style_overrides
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UiComponent extends Model
{
    use HasFactory, SoftDeletes, Cacheable;

    protected $fillable = [
        'page_layout_id',
        'component_key',
        'component_type',
        'position',
        'sort_order',
        'configuration',
        'style_overrides',
        'is_enabled',
        'responsive_config',
        'animation_config',
        'conditional_display',
        'version'
    ];

    protected $casts = [
        'configuration' => 'array',
        'style_overrides' => 'array',
        'responsive_config' => 'array',
        'animation_config' => 'array',
        'conditional_display' => 'array',
        'is_enabled' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Component types
     */
    const TYPE_HEADER = 'header';
    const TYPE_FOOTER = 'footer';
    const TYPE_HERO = 'hero';
    const TYPE_FEATURE_GRID = 'feature_grid';
    const TYPE_PROPERTY_CARD = 'property_card';
    const TYPE_SEARCH_BAR = 'search_bar';
    const TYPE_TESTIMONIAL = 'testimonial';
    const TYPE_CTA_BUTTON = 'cta_button';
    const TYPE_IMAGE_GALLERY = 'image_gallery';
    const TYPE_TEXT_BLOCK = 'text_block';
    const TYPE_FORM = 'form';
    const TYPE_MAP = 'map';
    const TYPE_SIDEBAR = 'sidebar';
    const TYPE_NAVIGATION = 'navigation';
    const TYPE_BREADCRUMB = 'breadcrumb';
    const TYPE_PAGINATION = 'pagination';
    const TYPE_FILTER = 'filter';
    const TYPE_MODAL = 'modal';
    const TYPE_TABS = 'tabs';
    const TYPE_ACCORDION = 'accordion';

    /**
     * Component positions
     */
    const POSITION_HEADER = 'header';
    const POSITION_MAIN = 'main';
    const POSITION_SIDEBAR = 'sidebar';
    const POSITION_FOOTER = 'footer';
    const POSITION_MODAL = 'modal';
    const POSITION_OVERLAY = 'overlay';

    /**
     * Get available component types
     */
    public static function getComponentTypes(): array
    {
        return [
            self::TYPE_HEADER => 'Header Component',
            self::TYPE_FOOTER => 'Footer Component',
            self::TYPE_HERO => 'Hero Section',
            self::TYPE_FEATURE_GRID => 'Feature Grid',
            self::TYPE_PROPERTY_CARD => 'Property Card',
            self::TYPE_SEARCH_BAR => 'Search Bar',
            self::TYPE_TESTIMONIAL => 'Testimonial',
            self::TYPE_CTA_BUTTON => 'Call to Action',
            self::TYPE_IMAGE_GALLERY => 'Image Gallery',
            self::TYPE_TEXT_BLOCK => 'Text Block',
            self::TYPE_FORM => 'Form Component',
            self::TYPE_MAP => 'Map Component',
            self::TYPE_SIDEBAR => 'Sidebar',
            self::TYPE_NAVIGATION => 'Navigation',
            self::TYPE_BREADCRUMB => 'Breadcrumb',
            self::TYPE_PAGINATION => 'Pagination',
            self::TYPE_FILTER => 'Filter Component',
            self::TYPE_MODAL => 'Modal Dialog',
            self::TYPE_TABS => 'Tab Container',
            self::TYPE_ACCORDION => 'Accordion'
        ];
    }

    /**
     * Get available positions
     */
    public static function getPositions(): array
    {
        return [
            self::POSITION_HEADER => 'Header Area',
            self::POSITION_MAIN => 'Main Content',
            self::POSITION_SIDEBAR => 'Sidebar',
            self::POSITION_FOOTER => 'Footer Area',
            self::POSITION_MODAL => 'Modal Layer',
            self::POSITION_OVERLAY => 'Overlay Layer'
        ];
    }

    /**
     * Relationship: Component belongs to a page layout
     */
    public function pageLayout(): BelongsTo
    {
        return $this->belongsTo(PageLayout::class);
    }

    /**
     * Scope to get enabled components
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to get by position
     */
    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to get by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('component_type', $type);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get components for position with caching
     */
    public static function getForPosition(string $position, int $layoutId): array
    {
        $cacheKey = "ui_components_{$layoutId}_{$position}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($position, $layoutId) {
            return static::enabled()
                ->where('page_layout_id', $layoutId)
                ->byPosition($position)
                ->ordered()
                ->get()
                ->toArray();
        });
    }

    /**
     * Get component configuration with defaults
     */
    public function getConfigurationWithDefaults(): array
    {
        $defaults = $this->getDefaultConfiguration();
        $userConfig = $this->configuration ?? [];
        
        return array_merge($defaults, $userConfig);
    }

    /**
     * Get default configuration for component type
     */
    protected function getDefaultConfiguration(): array
    {
        return match ($this->component_type) {
            self::TYPE_HERO => [
                'title' => 'Welcome to HabibiStay',
                'subtitle' => 'Find your perfect home away from home',
                'background_image' => '',
                'button_text' => 'Explore Properties',
                'button_link' => '/properties',
                'overlay_opacity' => 0.5,
                'text_color' => '#ffffff'
            ],
            self::TYPE_SEARCH_BAR => [
                'placeholder' => 'Where are you going?',
                'show_dates' => true,
                'show_guests' => true,
                'search_button_text' => 'Search',
                'style' => 'modern'
            ],
            self::TYPE_PROPERTY_CARD => [
                'show_rating' => true,
                'show_amenities' => true,
                'show_host_info' => false,
                'card_style' => 'elevated',
                'image_aspect_ratio' => '16:9'
            ],
            self::TYPE_TESTIMONIAL => [
                'show_rating' => true,
                'show_avatar' => true,
                'layout' => 'card',
                'background_color' => '#f8f9fa'
            ],
            self::TYPE_CTA_BUTTON => [
                'text' => 'Get Started',
                'style' => 'primary',
                'size' => 'large',
                'icon' => '',
                'link' => '#'
            ],
            default => []
        };
    }

    /**
     * Get responsive configuration
     */
    public function getResponsiveConfig(): array
    {
        return array_merge([
            'mobile' => ['visible' => true, 'columns' => 1],
            'tablet' => ['visible' => true, 'columns' => 2],
            'desktop' => ['visible' => true, 'columns' => 3]
        ], $this->responsive_config ?? []);
    }

    /**
     * Get style overrides with CSS variables
     */
    public function getStyleOverrides(): string
    {
        $styles = $this->style_overrides ?? [];
        $css = "";
        
        if (!empty($styles)) {
            $css .= "#{$this->component_key} {";
            foreach ($styles as $property => $value) {
                $css .= "{$property}: {$value};";
            }
            $css .= "}";
        }
        
        return $css;
    }

    /**
     * Update configuration
     */
    public function updateConfiguration(array $config): void
    {
        $currentConfig = $this->configuration ?? [];
        $updatedConfig = array_merge($currentConfig, $config);
        
        $this->update(['configuration' => $updatedConfig]);
    }

    /**
     * Update style overrides
     */
    public function updateStyles(array $styles): void
    {
        $currentStyles = $this->style_overrides ?? [];
        $updatedStyles = array_merge($currentStyles, $styles);
        
        $this->update(['style_overrides' => $updatedStyles]);
    }

    /**
     * Check if component should be displayed
     */
    public function shouldDisplay(array $context = []): bool
    {
        if (!$this->is_enabled) {
            return false;
        }
        
        $conditions = $this->conditional_display ?? [];
        
        if (empty($conditions)) {
            return true;
        }
        
        // Evaluate display conditions
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Evaluate display condition
     */
    protected function evaluateCondition(array $condition, array $context): bool
    {
        $type = $condition['type'] ?? 'always';
        
        return match ($type) {
            'user_authenticated' => auth()->check(),
            'user_role' => in_array(auth()->user()?->role, $condition['roles'] ?? []),
            'device_type' => in_array($context['device_type'] ?? 'desktop', $condition['devices'] ?? []),
            'page_type' => ($context['page_type'] ?? '') === ($condition['page_type'] ?? ''),
            'date_range' => $this->isWithinDateRange($condition),
            'feature_flag' => config("features.{$condition['flag']}", false),
            default => true
        };
    }

    /**
     * Check if current date is within range
     */
    protected function isWithinDateRange(array $condition): bool
    {
        $now = now();
        $start = isset($condition['start_date']) ? \Carbon\Carbon::parse($condition['start_date']) : null;
        $end = isset($condition['end_date']) ? \Carbon\Carbon::parse($condition['end_date']) : null;
        
        if ($start && $now->lt($start)) {
            return false;
        }
        
        if ($end && $now->gt($end)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get animation configuration
     */
    public function getAnimationConfig(): array
    {
        return array_merge([
            'entrance' => 'fadeIn',
            'duration' => 0.5,
            'delay' => 0,
            'trigger' => 'viewport',
            'easing' => 'ease-out'
        ], $this->animation_config ?? []);
    }

    /**
     * Clone component for another layout
     */
    public function cloneFor(int $newLayoutId): self
    {
        $clone = $this->replicate(['created_at', 'updated_at']);
        $clone->page_layout_id = $newLayoutId;
        $clone->component_key = $this->component_key . '_clone_' . time();
        $clone->save();
        
        return $clone;
    }

    /**
     * Move component to different position
     */
    public function moveToPosition(string $newPosition, int $newSortOrder = null): void
    {
        $updateData = ['position' => $newPosition];
        
        if ($newSortOrder !== null) {
            $updateData['sort_order'] = $newSortOrder;
        }
        
        $this->update($updateData);
    }

    /**
     * Get render data for component
     */
    public function getRenderData(array $context = []): array
    {
        return [
            'id' => $this->component_key,
            'type' => $this->component_type,
            'position' => $this->position,
            'config' => $this->getConfigurationWithDefaults(),
            'styles' => $this->getStyleOverrides(),
            'responsive' => $this->getResponsiveConfig(),
            'animation' => $this->getAnimationConfig(),
            'visible' => $this->shouldDisplay($context)
        ];
    }

    /**
     * Clear component cache
     */
    public function clearCache(): void
    {
        cache()->forget("ui_components_{$this->page_layout_id}_{$this->position}");
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
     * Get display name for admin
     */
    public function getDisplayName(): string
    {
        $typeName = self::getComponentTypes()[$this->component_type] ?? $this->component_type;
        return "{$typeName} ({$this->component_key})";
    }
}