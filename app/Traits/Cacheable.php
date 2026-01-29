<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Boot the trait
     */
    protected static function bootCacheable(): void
    {
        static::saved(function ($model) {
            $model->clearModelCache();
        });

        static::deleted(function ($model) {
            $model->clearModelCache();
        });
    }

    /**
     * Get cache key for the model
     */
    public function getCacheKey(string $suffix = ''): string
    {
        $baseKey = strtolower(class_basename($this)) . ':' . $this->getKey();
        return $suffix ? $baseKey . ':' . $suffix : $baseKey;
    }

    /**
     * Get collection cache key
     */
    public static function getCollectionCacheKey(string $suffix = ''): string
    {
        $baseKey = strtolower(class_basename(new static()));
        return $suffix ? $baseKey . ':collection:' . $suffix : $baseKey . ':collection';
    }

    /**
     * Cache a model attribute
     */
    public function cacheAttribute(string $attribute, $value, int $ttl = 3600): void
    {
        $cacheKey = $this->getCacheKey($attribute);
        Cache::put($cacheKey, $value, $ttl);
    }

    /**
     * Get cached attribute
     */
    public function getCachedAttribute(string $attribute, $default = null)
    {
        $cacheKey = $this->getCacheKey($attribute);
        return Cache::get($cacheKey, $default);
    }

    /**
     * Remember a computed value in cache
     */
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $cacheKey = $this->getCacheKey($key);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache a relationship
     */
    public function cacheRelation(string $relation, int $ttl = 3600)
    {
        return $this->remember($relation, function () use ($relation) {
            return $this->{$relation};
        }, $ttl);
    }

    /**
     * Cache a computed property
     */
    public function cacheComputed(string $property, callable $callback, int $ttl = 3600)
    {
        return $this->remember('computed:' . $property, $callback, $ttl);
    }

    /**
     * Cache model with all relationships
     */
    public function cacheWithRelations(array $relations = [], int $ttl = 3600): void
    {
        $cacheKey = $this->getCacheKey('with_relations');
        
        $data = [
            'model' => $this->toArray(),
            'relations' => []
        ];
        
        foreach ($relations as $relation) {
            if ($this->relationLoaded($relation)) {
                $data['relations'][$relation] = $this->{$relation};
            }
        }
        
        Cache::put($cacheKey, $data, $ttl);
    }

    /**
     * Get cached model with relations
     */
    public function getCachedWithRelations(): ?array
    {
        $cacheKey = $this->getCacheKey('with_relations');
        return Cache::get($cacheKey);
    }

    /**
     * Clear model cache
     */
    public function clearModelCache(): void
    {
        $pattern = $this->getCacheKey('*');
        $this->clearCacheByPattern($pattern);
    }

    /**
     * Clear collection cache
     */
    public static function clearCollectionCache(): void
    {
        $pattern = static::getCollectionCacheKey('*');
        static::clearCacheByPattern($pattern);
    }

    /**
     * Clear cache by pattern
     */
    protected static function clearCacheByPattern(string $pattern): void
    {
        // This implementation depends on your cache driver
        // For Redis, you could use KEYS pattern
        // For array/file cache, you might need a different approach
        
        $cacheDriver = Cache::getStore();
        
        if (method_exists($cacheDriver, 'flush')) {
            // For simple implementations, we'll clear specific known keys
            $baseKey = str_replace('*', '', $pattern);
            $keys = [
                $baseKey . 'with_relations',
                $baseKey . 'computed',
                $baseKey . 'stats',
                $baseKey . 'metadata'
            ];
            
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Cache model statistics
     */
    public function cacheStats(array $stats, int $ttl = 1800): void
    {
        $cacheKey = $this->getCacheKey('stats');
        Cache::put($cacheKey, $stats, $ttl);
    }

    /**
     * Get cached statistics
     */
    public function getCachedStats(): ?array
    {
        $cacheKey = $this->getCacheKey('stats');
        return Cache::get($cacheKey);
    }

    /**
     * Cache aggregated data
     */
    public static function cacheAggregated(string $key, callable $callback, int $ttl = 3600)
    {
        $cacheKey = static::getCollectionCacheKey('aggregated:' . $key);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache query results
     */
    public static function cacheQuery(string $queryKey, callable $callback, int $ttl = 1800)
    {
        $cacheKey = static::getCollectionCacheKey('query:' . $queryKey);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Increment cached counter
     */
    public function incrementCacheCounter(string $counter, int $value = 1): int
    {
        $cacheKey = $this->getCacheKey('counter:' . $counter);
        return Cache::increment($cacheKey, $value);
    }

    /**
     * Decrement cached counter
     */
    public function decrementCacheCounter(string $counter, int $value = 1): int
    {
        $cacheKey = $this->getCacheKey('counter:' . $counter);
        return Cache::decrement($cacheKey, $value);
    }

    /**
     * Get cached counter value
     */
    public function getCacheCounter(string $counter): int
    {
        $cacheKey = $this->getCacheKey('counter:' . $counter);
        return Cache::get($cacheKey, 0);
    }

    /**
     * Cache metadata
     */
    public function cacheMetadata(array $metadata, int $ttl = 7200): void
    {
        $cacheKey = $this->getCacheKey('metadata');
        Cache::put($cacheKey, $metadata, $ttl);
    }

    /**
     * Get cached metadata
     */
    public function getCachedMetadata(): ?array
    {
        $cacheKey = $this->getCacheKey('metadata');
        return Cache::get($cacheKey);
    }

    /**
     * Set cache tags for easier invalidation
     */
    public function getCacheTags(): array
    {
        return [
            strtolower(class_basename($this)),
            strtolower(class_basename($this)) . ':' . $this->getKey()
        ];
    }

    /**
     * Cache with tags (if supported by cache driver)
     */
    public function cacheWithTags(string $key, $value, int $ttl = 3600): void
    {
        $cacheKey = $this->getCacheKey($key);
        $tags = $this->getCacheTags();
        
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->put($cacheKey, $value, $ttl);
        } else {
            Cache::put($cacheKey, $value, $ttl);
        }
    }

    /**
     * Flush cache by tags
     */
    public static function flushCacheByTags(array $tags): void
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
        }
    }

    /**
     * Get cache statistics for the model
     */
    public function getCacheStatistics(): array
    {
        // This is a basic implementation
        // In a real scenario, you'd track hits/misses
        return [
            'cache_keys' => $this->getActiveCacheKeys(),
            'total_size' => $this->calculateCacheSize(),
            'last_cleared' => $this->getCachedAttribute('cache_cleared_at'),
            'hit_rate' => $this->getCacheHitRate()
        ];
    }

    /**
     * Get active cache keys for this model
     */
    protected function getActiveCacheKeys(): array
    {
        // This would need to be implemented based on your cache driver
        // For now, return common key suffixes
        return [
            'with_relations',
            'stats', 
            'metadata',
            'computed'
        ];
    }

    /**
     * Calculate approximate cache size
     */
    protected function calculateCacheSize(): int
    {
        // This is a placeholder implementation
        return 0;
    }

    /**
     * Get cache hit rate
     */
    protected function getCacheHitRate(): float
    {
        // This would require tracking hits/misses
        return 0.0;
    }

    /**
     * Warm up cache with commonly accessed data
     */
    public function warmUpCache(): void
    {
        // Cache commonly accessed relationships
        $this->cacheRelation('user', 7200);
        
        // Cache computed properties
        $this->cacheComputed('display_name', function () {
            return $this->getDisplayName();
        }, 7200);
        
        // Cache metadata
        $this->cacheMetadata([
            'cached_at' => now()->toISOString(),
            'version' => 1
        ]);
    }

    /**
     * Check if model has cached data
     */
    public function hasCachedData(string $key = null): bool
    {
        if ($key) {
            $cacheKey = $this->getCacheKey($key);
            return Cache::has($cacheKey);
        }
        
        // Check for any cached data
        $commonKeys = ['with_relations', 'stats', 'metadata'];
        foreach ($commonKeys as $suffix) {
            if (Cache::has($this->getCacheKey($suffix))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get display name (placeholder method)
     */
    protected function getDisplayName(): string
    {
        return $this->name ?? $this->title ?? 'Model #' . $this->getKey();
    }
}