<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Fallback to file cache if database cache is not available
        Cache::extend('smart', function ($app) {
            try {
                // Test if database cache is working
                $key = 'cache_test_' . time();
                Cache::driver('database')->put($key, true, 10);
                Cache::driver('database')->get($key);
                Cache::driver('database')->forget($key);

                // If we reach here, database cache is working
                return Cache::repository(Cache::store('database')->getStore());
            } catch (\Exception $e) {
                // If database cache fails, use file cache as fallback
                return Cache::repository(Cache::store('file')->getStore());
            }
        });
    }
}
