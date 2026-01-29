<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\HasRevisions;
use App\Traits\Cacheable;

/**
 * Theme Model - Dynamic UI Configuration Management
 * 
 * Implements runtime theme switching with component-based architecture
 * utilizing JSON storage for flexible configuration management
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property array $color_scheme
 * @property array $typography
 * @property array $spacing
 * @property array $components
 * @property array $animations
 * @property array $breakpoints
 * @property string|null $preview_image
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Theme extends Model
{
    use HasFactory, HasRevisions, Cacheable;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'color_scheme',
        'typography',
        'spacing',
        'components',
        'animations',
        'breakpoints',
        'preview_image',
        'created_by'
    ];

    /**
     * Attribute casting for JSON columns
     */
    protected $casts = [
        'is_active' => 'boolean',
        'color_scheme' => 'array',
        'typography' => 'array',
        'spacing' => 'array',
        'components' => 'array',
        'animations' => 'array',
        'breakpoints' => 'array',
    ];

    /**
     * Default theme configuration structure
     */
    protected $attributes = [
        'color_scheme' => '{}',
        'typography' => '{}',
        'spacing' => '{}',
        'components' => '{}',
        'animations' => '{}',
        'breakpoints' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($theme) {
            if (empty($theme->slug)) {
                $theme->slug = Str::slug($theme->name);
            }
            
            // Deactivate other themes if this is being set as active
            if ($theme->is_active) {
                static::where('is_active', true)->update(['is_active' => false]);
            }
        });

        static::updating(function ($theme) {
            // Ensure only one active theme
            if ($theme->is_active && $theme->isDirty('is_active')) {
                static::where('id', '!=', $theme->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });

        static::saved(function ($theme) {
            // Clear theme cache when saved
            cache()->forget('active_theme');
            cache()->forget("theme_{$theme->id}");
        });
    }

    /**
     * Get the currently active theme
     */
    public static function active(): ?self
    {
        return cache()->remember('active_theme', now()->addHours(24), function () {
            return static::where('is_active', true)->first();
        });
    }

    /**
     * Theme creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Page layouts using this theme
     */
    public function pageLayouts(): HasMany
    {
        return $this->hasMany(PageLayout::class);
    }

    /**
     * UI components for this theme
     */
    public function uiComponents(): HasMany
    {
        return $this->hasMany(UiComponent::class);
    }

    /**
     * Theme revision history
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ThemeRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get compiled CSS for this theme
     */
    public function compileCss(): string
    {
        $css = [];
        
        // CSS custom properties from color scheme
        if (!empty($this->color_scheme)) {
            $css[] = ':root {';
            foreach ($this->color_scheme as $key => $value) {
                $css[] = "  --color-{$key}: {$value};";
            }
            $css[] = '}';
        }
        
        // Typography styles
        if (!empty($this->typography)) {
            foreach ($this->typography as $element => $styles) {
                $css[] = $this->generateCssRule($element, $styles);
            }
        }
        
        // Spacing utilities
        if (!empty($this->spacing)) {
            foreach ($this->spacing as $property => $scale) {
                foreach ($scale as $size => $value) {
                    $css[] = ".{$property}-{$size} { {$property}: {$value}; }";
                }
            }
        }
        
        // Component-specific styles
        if (!empty($this->components)) {
            foreach ($this->components as $component => $styles) {
                $css[] = $this->generateComponentCss($component, $styles);
            }
        }
        
        // Animation definitions
        if (!empty($this->animations)) {
            foreach ($this->animations as $name => $keyframes) {
                $css[] = "@keyframes {$name} {";
                foreach ($keyframes as $stop => $properties) {
                    $css[] = "  {$stop} { " . $this->propertiesToCss($properties) . " }";
                }
                $css[] = "}";
            }
        }
        
        // Responsive breakpoint overrides
        if (!empty($this->breakpoints)) {
            foreach ($this->breakpoints as $breakpoint => $overrides) {
                $css[] = "@media (min-width: {$breakpoint}) {";
                foreach ($overrides as $selector => $styles) {
                    $css[] = "  " . $this->generateCssRule($selector, $styles);
                }
                $css[] = "}";
            }
        }
        
        return implode("\n", $css);
    }

    /**
     * Generate CSS rule from selector and properties
     */
    protected function generateCssRule(string $selector, array $properties): string
    {
        return "{$selector} { " . $this->propertiesToCss($properties) . " }";
    }

    /**
     * Convert properties array to CSS string
     */
    protected function propertiesToCss(array $properties): string
    {
        $css = [];
        foreach ($properties as $property => $value) {
            $css[] = "{$property}: {$value};";
        }
        return implode(' ', $css);
    }

    /**
     * Generate component-specific CSS
     */
    protected function generateComponentCss(string $component, array $styles): string
    {
        $css = [];
        $componentClass = ".component-{$component}";
        
        foreach ($styles as $modifier => $properties) {
            if ($modifier === '_base') {
                $css[] = $this->generateCssRule($componentClass, $properties);
            } else {
                $css[] = $this->generateCssRule("{$componentClass}--{$modifier}", $properties);
            }
        }
        
        return implode("\n", $css);
    }

    /**
     * Apply theme to a specific context
     */
    public function applyToContext(string $context): array
    {
        return [
            'colors' => $this->color_scheme,
            'typography' => $this->typography[$context] ?? $this->typography['_default'] ?? [],
            'spacing' => $this->spacing,
            'components' => array_filter($this->components, function ($key) use ($context) {
                return str_starts_with($key, $context);
            }, ARRAY_FILTER_USE_KEY),
            'animations' => $this->animations
        ];
    }

    /**
     * Clone theme with modifications
     */
    public function duplicate(string $newName, array $modifications = []): self
    {
        $clone = $this->replicate();
        $clone->name = $newName;
        $clone->slug = Str::slug($newName);
        $clone->is_active = false;
        
        // Apply modifications
        foreach ($modifications as $key => $value) {
            if (in_array($key, ['color_scheme', 'typography', 'spacing', 'components', 'animations', 'breakpoints'])) {
                $clone->$key = array_merge($this->$key, $value);
            }
        }
        
        $clone->save();
        
        return $clone;
    }

    /**
     * Export theme configuration
     */
    public function export(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'color_scheme' => $this->color_scheme,
            'typography' => $this->typography,
            'spacing' => $this->spacing,
            'components' => $this->components,
            'animations' => $this->animations,
            'breakpoints' => $this->breakpoints,
            'version' => '1.0.0',
            'created_at' => $this->created_at->toIso8601String()
        ];
    }

    /**
     * Import theme configuration
     */
    public static function import(array $config, int $userId): self
    {
        return static::create([
            'name' => $config['name'] . ' (Imported)',
            'description' => $config['description'] ?? null,
            'color_scheme' => $config['color_scheme'] ?? [],
            'typography' => $config['typography'] ?? [],
            'spacing' => $config['spacing'] ?? [],
            'components' => $config['components'] ?? [],
            'animations' => $config['animations'] ?? [],
            'breakpoints' => $config['breakpoints'] ?? [],
            'created_by' => $userId
        ]);
    }

    /**
     * Get theme variables for JavaScript
     */
    public function getJsVariables(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'colors' => $this->color_scheme,
            'typography' => $this->typography,
            'spacing' => $this->spacing,
            'animations' => array_keys($this->animations),
            'breakpoints' => array_keys($this->breakpoints)
        ];
    }
}
