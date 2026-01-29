<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Generate unique slug for the model
     */
    public function generateSlug(string $source = null): string
    {
        $source = $source ?? $this->getSlugSource();
        $slug = Str::slug($source);
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Update slug if source has changed
     */
    public function updateSlugIfNeeded(): void
    {
        $sourceField = $this->getSlugSourceField();
        
        if ($this->isDirty($sourceField)) {
            $newSlug = $this->generateSlug($this->{$sourceField});
            $this->setAttribute($this->getSlugField(), $newSlug);
        }
    }

    /**
     * Check if slug exists for this model
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where($this->getSlugField(), $slug);
        
        // Exclude current model if updating
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }
        
        return $query->exists();
    }

    /**
     * Get the source field for slug generation
     */
    protected function getSlugSourceField(): string
    {
        return property_exists($this, 'slugSourceField') 
            ? $this->slugSourceField 
            : 'title';
    }

    /**
     * Get the slug field name
     */
    protected function getSlugField(): string
    {
        return property_exists($this, 'slugField') 
            ? $this->slugField 
            : 'slug';
    }

    /**
     * Get the source content for slug generation
     */
    protected function getSlugSource(): string
    {
        $sourceField = $this->getSlugSourceField();
        return $this->{$sourceField} ?? '';
    }

    /**
     * Automatically generate slug when creating
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $slugField = $model->getSlugField();
            
            if (empty($model->{$slugField})) {
                $model->{$slugField} = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            $model->updateSlugIfNeeded();
        });
    }

    /**
     * Find model by slug
     */
    public static function findBySlug(string $slug)
    {
        $instance = new static;
        return static::where($instance->getSlugField(), $slug)->first();
    }

    /**
     * Find model by slug or fail
     */
    public static function findBySlugOrFail(string $slug)
    {
        $instance = new static;
        return static::where($instance->getSlugField(), $slug)->firstOrFail();
    }

    /**
     * Get the route key for the model (use slug instead of ID)
     */
    public function getRouteKeyName(): string
    {
        return $this->getSlugField();
    }

    /**
     * Scope to find by slug
     */
    public function scopeSlug($query, string $slug)
    {
        return $query->where($this->getSlugField(), $slug);
    }
}