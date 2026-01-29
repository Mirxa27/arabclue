<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI\Contracts\ClientContract as OpenAIClientContract; // Alias the OpenAI Client Contract
use OpenAI\Laravel\Facades\OpenAI;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAIClientContract::class, function ($app) {
            // Ensure the openai.php config file and OPENAI_API_KEY in .env are set
            $apiKey = config('openai.api_key');
            if (empty($apiKey)) {
                // Fallback to env if config is not cached or not set,
                // though ideally phpunit.xml should cover test env
                $apiKey = env('OPENAI_API_KEY');
            }
            if (empty($apiKey)) {
                throw new \Exception('OpenAI API key is not configured. Please set it in your .env file or config/openai.php, and ensure it is available in phpunit.xml for tests.');
            }
            return OpenAI::factory()
                ->withApiKey($apiKey)
                // Optionally add organization if you use it
                // ->withOrganization(config('openai.organization')) 
                ->make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
