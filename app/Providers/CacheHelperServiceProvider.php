<?php

namespace App\Providers;

use App\Helpers\CacheHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class CacheHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind a singleton instance to the container
        $this->app->singleton('cache-helper', function ($app) {
            return new CacheHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add a directive for Blade templates to safely access cached values
        Blade::directive('cacheSafe', function ($expression) {
            return "<?php echo \App\Helpers\CacheHelper::get({$expression}); ?>";
        });
    }
}
