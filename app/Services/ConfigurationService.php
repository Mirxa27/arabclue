<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Configuration Service
 * 
 * Manages system-wide configuration settings and environment variables
 * for the admin dashboard interface
 */
class ConfigurationService
{
    protected $settingsService;
    
    // Settings that can be exposed to the admin UI
    protected $exposableSettings = [
        'app' => [
            'name',
            'url',
            'timezone',
            'locale'
        ],
        'mail' => [
            'from.address',
            'from.name',
            'mailers.smtp.host',
            'mailers.smtp.port',
            'mailers.smtp.encryption'
        ],
        'services' => [
            'google.client_id',
            'google.client_secret',
            'facebook.client_id',
            'facebook.client_secret',
            'apple.client_id',
            'apple.client_secret',
            'elevenlabs.api_key',
            'cloudflare.api_key'
        ],
        'openai' => [
            'default_model'
        ],
        'ai' => [
            'enabled',
            'assistant_name',
            'voice_enabled'
        ],
        'payment' => [
            'currency',
            'service_fee_percentage',
            'tax_rate'
        ],
        'features' => [
            'wishlist_collections_enabled',
            'advanced_search_enabled',
            'referrals_enabled',
            'ical_sync_enabled',
            'host_dashboard_enabled'
        ]
    ];
    
    // Environment variables that can be exposed to the admin UI
    protected $exposableEnvVars = [
        'APP_NAME',
        'APP_URL',
        'APP_TIMEZONE',
        'APP_LOCALE',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_ENCRYPTION',
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
        'FACEBOOK_CLIENT_ID',
        'FACEBOOK_CLIENT_SECRET',
        'APPLE_CLIENT_ID',
        'APPLE_CLIENT_SECRET',
        'OPENAI_API_KEY',
        'OPENAI_DEFAULT_MODEL',
        'ELEVENLABS_API_KEY',
        'CLOUDFLARE_API_KEY',
        'CURRENCY',
        'SERVICE_FEE_PERCENTAGE',
        'TAX_RATE',
        'PUSHER_APP_ID',
        'PUSHER_APP_KEY',
        'PUSHER_APP_SECRET',
        'PUSHER_APP_CLUSTER',
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
        'AWS_DEFAULT_REGION',
        'AWS_BUCKET'
    ];
    
    // Environment variables that require application restart when changed
    protected $restartRequiredVars = [
        'APP_NAME',
        'APP_ENV',
        'APP_DEBUG',
        'APP_URL',
        'LOG_CHANNEL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'BROADCAST_DRIVER',
        'CACHE_DRIVER',
        'QUEUE_CONNECTION',
        'SESSION_DRIVER',
        'REDIS_HOST',
        'REDIS_PORT'
    ];
    
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }
    
    /**
     * Get configuration categories
     */
    public function getCategories(): array
    {
        return [
            [
                'id' => 'app',
                'name' => 'Application',
                'description' => 'General application settings'
            ],
            [
                'id' => 'mail',
                'name' => 'Mail',
                'description' => 'Email configuration settings'
            ],
            [
                'id' => 'services',
                'name' => 'External Services',
                'description' => 'Third-party service integration settings'
            ],
            [
                'id' => 'ai',
                'name' => 'AI & Sara Assistant',
                'description' => 'AI assistant and machine learning settings'
            ],
            [
                'id' => 'payment',
                'name' => 'Payments & Pricing',
                'description' => 'Payment processing and fee settings'
            ],
            [
                'id' => 'features',
                'name' => 'Feature Toggles',
                'description' => 'Enable or disable specific features'
            ]
        ];
    }
    
    /**
     * Get configuration settings by category
     */
    public function getConfigurationByCategory(string $category): array
    {
        if (!isset($this->exposableSettings[$category])) {
            throw new \Exception("Configuration category not found: {$category}");
        }
        
        $settings = [];
        
        foreach ($this->exposableSettings[$category] as $setting) {
            // Check if it's a nested setting with dot notation
            if (Str::contains($setting, '.')) {
                $keys = explode('.', $setting);
                $configValue = config($category . '.' . $setting);
                
                // Build nested structure
                $current = &$settings;
                foreach ($keys as $i => $key) {
                    if ($i === count($keys) - 1) {
                        $current[$key] = [
                            'value' => $configValue,
                            'type' => $this->determineValueType($configValue)
                        ];
                    } else {
                        if (!isset($current[$key])) {
                            $current[$key] = [];
                        }
                        $current = &$current[$key];
                    }
                }
            } else {
                $configValue = config($category . '.' . $setting);
                $settings[$setting] = [
                    'value' => $configValue,
                    'type' => $this->determineValueType($configValue)
                ];
            }
        }
        
        return [
            'category' => $category,
            'settings' => $settings
        ];
    }
    
    /**
     * Update configuration settings
     */
    public function updateConfiguration(string $category, array $settings): array
    {
        $updatedSettings = [];
        
        foreach ($settings as $setting) {
            $key = $setting['key'];
            $value = $setting['value'];
            
            // Store in database for persistence
            $settingKey = "{$category}.{$key}";
            $this->settingsService->update([$settingKey => $value]);
            
            // Update in-memory config
            config([$settingKey => $value]);
            
            $updatedSettings[] = $key;
        }
        
        // Clear config cache
        Artisan::call('config:clear');
        
        return [
            'category' => $category,
            'updated' => $updatedSettings
        ];
    }
    
    /**
     * Get environment variables that can be exposed to admin UI
     */
    public function getExposedEnvironmentVariables(): array
    {
        $variables = [];
        
        foreach ($this->exposableEnvVars as $key) {
            // Mask sensitive values
            $value = env($key);
            $isSensitive = $this->isSensitiveVariable($key);
            
            $variables[] = [
                'key' => $key,
                'value' => $isSensitive ? $this->maskSensitiveValue($value) : $value,
                'is_sensitive' => $isSensitive,
                'restart_required' => in_array($key, $this->restartRequiredVars)
            ];
        }
        
        return $variables;
    }
    
    /**
     * Update environment variables in .env file
     */
    public function updateEnvironmentVariables(array $variables): array
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);
        
        // Create backup of the current .env file
        File::copy($envFile, base_path('.env.backup-' . time()));
        
        $updatedKeys = [];
        $restartRequired = false;
        
        foreach ($variables as $variable) {
            $key = $variable['key'];
            $value = $variable['value'];
            
            // Verify this is an allowed variable
            if (!in_array($key, $this->exposableEnvVars)) {
                Log::warning("Attempted to update restricted environment variable: {$key}");
                continue;
            }
            
            // Check if restart will be required
            if (in_array($key, $this->restartRequiredVars)) {
                $restartRequired = true;
            }
            
            // Update the .env file
            if (Str::contains($envContent, $key . '=')) {
                // Replace existing variable
                $envContent = preg_replace(
                    "/^{$key}=.*$/m",
                    "{$key}=" . $this->escapeEnvValue($value),
                    $envContent
                );
            } else {
                // Add new variable
                $envContent .= "\n{$key}=" . $this->escapeEnvValue($value);
            }
            
            $updatedKeys[] = $key;
        }
        
        // Write updated content back to .env file
        File::put($envFile, $envContent);
        
        // Clear cached environment variables
        Artisan::call('config:clear');
        
        return [
            'updated' => $updatedKeys,
            'restart_required' => $restartRequired
        ];
    }
    
    /**
     * Get a configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->settingsService->get($key, $default);
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, $value, string $type = 'string'): bool
    {
        return $this->settingsService->set($key, $value, $type);
    }
    
    /**
     * Determine the type of a configuration value
     */
    protected function determineValueType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value)) {
            return 'array';
        } else {
            return 'string';
        }
    }
    
    /**
     * Check if the variable is sensitive and should be masked
     */
    protected function isSensitiveVariable(string $key): bool
    {
        $sensitiveKeywords = ['key', 'secret', 'password', 'token', 'private'];
        
        foreach ($sensitiveKeywords as $keyword) {
            if (Str::contains(strtolower($key), $keyword)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Mask sensitive value (show first and last few characters)
     */
    protected function maskSensitiveValue(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        $length = strlen($value);
        
        if ($length <= 8) {
            return '********';
        }
        
        $visibleChars = min(4, floor($length / 4));
        $prefix = substr($value, 0, $visibleChars);
        $suffix = substr($value, -$visibleChars);
        $maskedLength = $length - (2 * $visibleChars);
        
        return $prefix . str_repeat('*', $maskedLength) . $suffix;
    }
    
    /**
     * Escape value for .env file
     */
    protected function escapeEnvValue($value): string
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        
        // Check if value contains spaces or special characters
        if (preg_match('/\s|[\'"\\\\]/', $value)) {
            // Wrap in double quotes and escape double quotes
            return '"' . str_replace('"', '\\"', $value) . '"';
        }
        
        return $value;
    }
}
