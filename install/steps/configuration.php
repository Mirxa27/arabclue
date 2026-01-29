<?php
/**
 * Platform Configuration Module
 * Implements Service Provider pattern for modular configuration
 * Following Laravel's configuration architecture
 */

$error = '';
$success = false;

/**
 * Configuration Manager implementing Repository pattern
 * for centralized configuration management
 */
class ConfigurationManager {
    private array $config = [];
    private string $envPath;
    
    public function __construct(string $envPath = '../.env') {
        $this->envPath = $envPath;
        $this->loadExistingConfiguration();
    }
    
    private function loadExistingConfiguration(): void {
        if (file_exists($this->envPath)) {
            $envContent = file_get_contents($this->envPath);
            $lines = explode("\n", $envContent);
            
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->config[trim($key)] = trim($value, '"\'');
                }
            }
        }
    }
    
    public function updateConfiguration(array $newConfig): bool {
        // Merge new configuration with existing
        $this->config = array_merge($this->config, $newConfig);
        
        // Generate updated .env content
        $envContent = $this->generateEnvContent();
        
        // Write to file with atomic operation
        $tempFile = $this->envPath . '.tmp';
        if (file_put_contents($tempFile, $envContent) !== false) {
            return rename($tempFile, $this->envPath);
        }
        
        return false;
    }
    
    private function generateEnvContent(): string {
        $sections = [
            'Application' => ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL'],
            'Database' => ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
            'Mail' => ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'],
            'Payment Gateways' => ['PAYPAL_MODE', 'PAYPAL_SANDBOX_CLIENT_ID', 'PAYPAL_SANDBOX_SECRET', 'MYFATOORAH_API_KEY', 'MYFATOORAH_MODE'],
            'AI Configuration' => ['OPENAI_API_KEY', 'SARA_CHATBOT_MODEL', 'SARA_CHATBOT_TEMPERATURE', 'SARA_CHATBOT_MAX_TOKENS'],
            'Services' => ['GOOGLE_MAPS_API_KEY', 'TWILIO_SID', 'TWILIO_TOKEN', 'TWILIO_FROM'],
            'Cache & Queue' => ['CACHE_DRIVER', 'QUEUE_CONNECTION', 'SESSION_DRIVER']
        ];
        
        $content = '';
        foreach ($sections as $section => $keys) {
            $content .= "# {$section}\n";
            foreach ($keys as $key) {
                if (isset($this->config[$key])) {
                    $value = $this->config[$key];
                    // Quote values containing spaces or special characters
                    if (strpos($value, ' ') !== false || preg_match('/[#$%&*(){}[\]|\\\\:;"\'<>?,]/', $value)) {
                        $value = '"' . addslashes($value) . '"';
                    }
                    $content .= "{$key}={$value}\n";
                }
            }
            $content .= "\n";
        }
        
        return $content;
    }
    
    public function generateAppKey(): string {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configManager = new ConfigurationManager();
    
    $config = [
        // Application settings
        'APP_NAME' => $_POST['app_name'] ?? 'HabibiStay',
        'APP_KEY' => $configManager->generateAppKey(),
        'APP_TIMEZONE' => $_POST['app_timezone'] ?? 'Asia/Riyadh',
        
        // Mail configuration
        'MAIL_MAILER' => $_POST['mail_driver'] ?? 'smtp',
        'MAIL_HOST' => $_POST['mail_host'] ?? '',
        'MAIL_PORT' => $_POST['mail_port'] ?? '587',
        'MAIL_USERNAME' => $_POST['mail_username'] ?? '',
        'MAIL_PASSWORD' => $_POST['mail_password'] ?? '',
        'MAIL_ENCRYPTION' => $_POST['mail_encryption'] ?? 'tls',
        'MAIL_FROM_ADDRESS' => $_POST['mail_from_address'] ?? 'noreply@habibistay.com',
        'MAIL_FROM_NAME' => $_POST['mail_from_name'] ?? 'HabibiStay',
        
        // Payment gateways
        'PAYPAL_MODE' => $_POST['paypal_mode'] ?? 'sandbox',
        'PAYPAL_SANDBOX_CLIENT_ID' => $_POST['paypal_client_id'] ?? '',
        'PAYPAL_SANDBOX_SECRET' => $_POST['paypal_secret'] ?? '',
        'MYFATOORAH_API_KEY' => $_POST['myfatoorah_api_key'] ?? '',
        'MYFATOORAH_MODE' => $_POST['myfatoorah_mode'] ?? 'test',
        'MYFATOORAH_COUNTRY_ISO' => 'SA',
        
        // AI Configuration
        'OPENAI_API_KEY' => $_POST['openai_api_key'] ?? '',
        'SARA_CHATBOT_MODEL' => $_POST['sara_model'] ?? 'gpt-4',
        'SARA_CHATBOT_TEMPERATURE' => $_POST['sara_temperature'] ?? '0.7',
        'SARA_CHATBOT_MAX_TOKENS' => $_POST['sara_max_tokens'] ?? '2000',
        
        // Additional services
        'GOOGLE_MAPS_API_KEY' => $_POST['google_maps_key'] ?? '',
        'TWILIO_SID' => $_POST['twilio_sid'] ?? '',
        'TWILIO_TOKEN' => $_POST['twilio_token'] ?? '',
        'TWILIO_FROM' => $_POST['twilio_from'] ?? '',
        
        // Performance settings
        'CACHE_DRIVER' => $_POST['cache_driver'] ?? 'file',
        'QUEUE_CONNECTION' => $_POST['queue_driver'] ?? 'sync',
        'SESSION_DRIVER' => 'file'
    ];
    
    if ($configManager->updateConfiguration($config)) {
        $_SESSION['configuration_completed'] = true;
        
        // Mark installation as complete
        file_put_contents('../.installed', json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'admin_email' => $_SESSION['admin_email'] ?? 'admin@habibistay.com'
        ]));
        
        header('Location: ?step=6');
        exit;
    } else {
        $error = 'Failed to save configuration';
    }
}

// Define timezone options
$timezones = [
    'Asia/Riyadh' => 'Riyadh (GMT+3)',
    'Asia/Dubai' => 'Dubai (GMT+4)',
    'Asia/Kuwait' => 'Kuwait (GMT+3)',
    'Asia/Bahrain' => 'Bahrain (GMT+3)',
    'Asia/Qatar' => 'Qatar (GMT+3)',
    'UTC' => 'UTC'
];
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-cog mr-2"></i>Platform Configuration
        </h2>
        <p class="text-gray-600">
            Configure essential services and integrations for your HabibiStay platform.
        </p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
    </div>
    <?php endif; ?>

    <form method="POST" id="stepForm" class="space-y-8">
        <!-- Application Settings -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-globe mr-2"></i>Application Settings
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Application Name
                    </label>
                    <input type="text" name="app_name" value="<?php echo $_POST['app_name'] ?? 'HabibiStay'; ?>" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Timezone
                    </label>
                    <select name="app_timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                        <?php foreach ($timezones as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo ($_POST['app_timezone'] ?? 'Asia/Riyadh') === $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Email Configuration -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-envelope mr-2"></i>Email Configuration
            </h3>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mail Driver
                    </label>
                    <select name="mail_driver" id="mail_driver" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                        <option value="smtp">SMTP</option>
                        <option value="sendmail">Sendmail</option>
                        <option value="mailgun">Mailgun</option>
                        <option value="ses">Amazon SES</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Encryption
                    </label>
                    <select name="mail_encryption" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="">None</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP Host
                    </label>
                    <input type="text" name="mail_host" placeholder="smtp.gmail.com" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP Port
                    </label>
                    <input type="number" name="mail_port" value="587" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <input type="text" name="mail_username" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="password" name="mail_password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
            </div>
        </div>

        <!-- Payment Gateways -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-credit-card mr-2"></i>Payment Gateways
            </h3>
            
            <!-- PayPal Configuration -->
            <div class="mb-6">
                <h4 class="font-medium mb-3">PayPal Configuration</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mode
                        </label>
                        <select name="paypal_mode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                            <option value="sandbox">Sandbox (Testing)</option>
                            <option value="live">Live (Production)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Client ID
                        </label>
                        <input type="text" name="paypal_client_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600"
                            placeholder="Optional - Configure later">
                    </div>
                </div>
            </div>
            
            <!-- MyFatoorah Configuration -->
            <div>
                <h4 class="font-medium mb-3">MyFatoorah Configuration</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            API Key
                        </label>
                        <input type="text" name="myfatoorah_api_key" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600"
                            placeholder="Optional - Configure later">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mode
                        </label>
                        <select name="myfatoorah_mode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                            <option value="test">Test</option>
                            <option value="live">Live</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Configuration -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-4">
                <i class="fas fa-robot mr-2"></i>Sara AI Chatbot Configuration
            </h3>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        OpenAI API Key
                    </label>
                    <input type="text" name="openai_api_key" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600"
                        placeholder="sk-...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Model
                    </label>
                    <select name="sara_model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                        <option value="gpt-4">GPT-4 (Recommended)</option>
                        <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Temperature <span class="text-xs text-gray-500">(0.0 - 1.0)</span>
                    </label>
                    <input type="number" name="sara_temperature" value="0.7" step="0.1" min="0" max="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Max Tokens
                    </label>
                    <input type="number" name="sara_max_tokens" value="2000" min="100" max="4000"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600">
                </div>
            </div>
        </div>

        <!-- Optional Services -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Optional Configuration</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        You can skip optional configurations and set them up later from the admin panel.
                        The system will work with default settings.
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between pt-4">
            <a href="?step=4" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Previous
            </a>
            
            <button type="submit" id="submitBtn" 
                class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
                Save Configuration<i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
