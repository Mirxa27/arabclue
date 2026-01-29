<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    /**
     * Get all application settings
     * 
     * @return array
     */
    public function getAllSettings(): array
    {
        return Cache::remember('all_settings', 3600, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    
    /**
     * Get a specific setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            $setting = DB::table('settings')->where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Cast value based on type
            switch ($setting->type ?? 'string') {
                case 'boolean':
                    return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    return (int) $setting->value;
                case 'float':
                    return (float) $setting->value;
                case 'array':
                case 'json':
                    return json_decode($setting->value, true);
                default:
                    return $setting->value;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get setting', [
                'error' => $e->getMessage(),
                'key' => $key
            ]);
            return $default;
        }
    }
    
    /**
     * Set a specific setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    public function set(string $key, $value, string $type = 'string'): bool
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'type' => $type, 'updated_at' => now()]
            );
            
            // Clear cache
            Cache::forget('all_settings');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set setting', [
                'error' => $e->getMessage(),
                'key' => $key,
                'value' => $value
            ]);
            
            return false;
        }
    }

    /**
     * Update settings
     * 
     * @param array $settings
     * @return bool
     */
    public function update(array $settings): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($settings as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
            
            DB::commit();
            
            // Clear cache
            Cache::forget('all_settings');
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update settings', [
                'error' => $e->getMessage(),
                'settings' => $settings
            ]);
            
            return false;
        }
    }
    
    /**
     * Get all environment variables that are allowed to be edited
     * 
     * @return array
     */
    public function getEnvVariables(): array
    {
        $envFile = app()->environmentFilePath();
        $envContents = file_exists($envFile) ? file_get_contents($envFile) : '';
        
        $variables = [];
        $lines = explode("\n", $envContents);
        
        $allowedPrefixes = [
            'APP_',
            'MAIL_',
            'PUSHER_',
            'AWS_',
            'OPENAI_',
            'GEMINI_',
            'TWILIO_',
            'STRIPE_',
            'PAYPAL_',
            'FRONTEND_'
        ];
        
        $excludedVars = [
            'APP_KEY',
            'MAIL_PASSWORD',
            'DB_PASSWORD'
        ];
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                $line = trim($line);
                if (strpos($line, '#') === 0) {
                    continue; // Skip comments
                }
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                
                // Skip if not in allowed prefixes or if explicitly excluded
                if (in_array($name, $excludedVars)) {
                    $variables[$name] = '[HIDDEN FOR SECURITY]';
                    continue;
                }
                
                $allowed = false;
                foreach ($allowedPrefixes as $prefix) {
                    if (strpos($name, $prefix) === 0) {
                        $allowed = true;
                        break;
                    }
                }
                
                if ($allowed) {
                    $value = trim($value);
                    // Remove quotes if present
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    $variables[$name] = $value;
                }
            }
        }
        
        return $variables;
    }
    
    /**
     * Update environment variables
     * 
     * @param array $variables
     * @return array
     */
    public function updateEnvVariables(array $variables): array
    {
        $envFile = app()->environmentFilePath();
        $envContents = file_exists($envFile) ? file_get_contents($envFile) : '';
        
        $updated = [];
        $failed = [];
        
        // Only allow specific prefixes to be updated
        $allowedPrefixes = [
            'APP_NAME',
            'APP_URL',
            'APP_TIMEZONE',
            'MAIL_',
            'PUSHER_',
            'AWS_',
            'OPENAI_',
            'GEMINI_',
            'TWILIO_',
            'STRIPE_',
            'PAYPAL_',
            'FRONTEND_'
        ];
        
        // Never allow these to be updated via the API
        $disallowedVars = [
            'APP_KEY',
            'APP_ENV',
            'APP_DEBUG',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ];
        
        foreach ($variables as $key => $value) {
            // Skip disallowed variables
            if (in_array($key, $disallowedVars)) {
                $failed[$key] = 'Cannot update this sensitive variable';
                continue;
            }
            
            // Check if variable has allowed prefix
            $isAllowed = false;
            foreach ($allowedPrefixes as $prefix) {
                if (strpos($key, $prefix) === 0) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                $failed[$key] = 'Variable does not have allowed prefix';
                continue;
            }
            
            // Escape any quotes in the value
            $value = str_replace('"', '\"', $value);
            
            // Update the variable in the contents
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";
            
            if (preg_match($pattern, $envContents)) {
                $envContents = preg_replace($pattern, $replacement, $envContents);
                $updated[] = $key;
            } else {
                // Variable doesn't exist, add it at the end
                $envContents .= PHP_EOL . $replacement;
                $updated[] = $key;
            }
        }
        
        if (!empty($updated)) {
            try {
                file_put_contents($envFile, $envContents);
            } catch (\Exception $e) {
                Log::error('Failed to update .env file', [
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'updated' => [],
                    'failed' => array_merge($failed, array_fill_keys($updated, 'File write permission denied')),
                    'error' => 'Failed to write to .env file'
                ];
            }
        }
        
        return [
            'success' => !empty($updated),
            'updated' => $updated,
            'failed' => $failed
        ];
    }
}
