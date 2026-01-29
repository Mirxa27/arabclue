<?php
/**
 * Database Configuration Module
 * Implements connection factory pattern with PDO abstraction layer
 * Supports multiple database drivers following SOLID principles
 */

// Initialize error handling
$error = '';
$success = false;
$connectionTested = false;

// Define supported database drivers with their specific DSN patterns
$databaseDrivers = [
    'mysql' => [
        'name' => 'MySQL/MariaDB',
        'port' => 3306,
        'dsn_pattern' => 'mysql:host=%s;port=%d;charset=utf8mb4'
    ],
    'pgsql' => [
        'name' => 'PostgreSQL',
        'port' => 5432,
        'dsn_pattern' => 'pgsql:host=%s;port=%d'
    ]
];

/**
 * Database Connection Factory
 * Implements abstract factory pattern for database connections
 */
class DatabaseConnectionFactory {
    private array $config;
    private ?PDO $connection = null;
    
    public function __construct(array $config) {
        $this->config = $config;
    }
    
    public function testConnection(): array {
        try {
            $dsn = $this->buildDSN();
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
            
            // Test database creation capability
            if (!$this->databaseExists()) {
                $this->createDatabase();
            }
            
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function buildDSN(): string {
        global $databaseDrivers;
        $driver = $this->config['driver'];
        return sprintf(
            $databaseDrivers[$driver]['dsn_pattern'],
            $this->config['host'],
            $this->config['port']
        );
    }
    
    private function databaseExists(): bool {
        $stmt = $this->connection->prepare(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?"
        );
        $stmt->execute([$this->config['database']]);
        return $stmt->fetch() !== false;
    }
    
    private function createDatabase(): void {
        $dbName = $this->config['database'];
        $this->connection->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` 
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    public function saveConfiguration(): bool {
        $envContent = $this->generateEnvContent();
        return file_put_contents('../.env', $envContent) !== false;
    }
    
    private function generateEnvContent(): string {
        return <<<ENV
APP_NAME=HabibiStay
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL={$this->config['app_url']}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION={$this->config['driver']}
DB_HOST={$this->config['host']}
DB_PORT={$this->config['port']}
DB_DATABASE={$this->config['database']}
DB_USERNAME={$this->config['username']}
DB_PASSWORD={$this->config['password']}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@habibistay.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

# Payment Gateway Configuration
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_SECRET=
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_SECRET=

MYFATOORAH_API_KEY=
MYFATOORAH_MODE=test
MYFATOORAH_COUNTRY_ISO=SA

# AI Configuration
OPENAI_API_KEY=
SARA_CHATBOT_MODEL=gpt-4
SARA_CHATBOT_TEMPERATURE=0.7
ENV;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'driver' => $_POST['db_driver'] ?? 'mysql',
        'host' => $_POST['db_host'] ?? 'localhost',
        'port' => $_POST['db_port'] ?? $databaseDrivers[$_POST['db_driver']]['port'],
        'database' => $_POST['db_name'] ?? '',
        'username' => $_POST['db_username'] ?? '',
        'password' => $_POST['db_password'] ?? '',
        'app_url' => $_POST['app_url'] ?? 'http://localhost'
    ];
    
    $factory = new DatabaseConnectionFactory($config);
    $result = $factory->testConnection();
    
    if ($result['success']) {
        $success = $factory->saveConfiguration();
        if ($success) {
            $_SESSION['database_configured'] = true;
            $_SESSION['db_config'] = $config;
            header('Location: ?step=4');
            exit;
        } else {
            $error = 'Failed to save configuration file';
        }
    } else {
        $error = $result['message'];
    }
    $connectionTested = true;
}
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-database mr-2"></i>Database Configuration
        </h2>
        <p class="text-gray-600">
            Configure your database connection using secure PDO abstraction layer.
        </p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Connection Failed</h3>
                <p class="mt-1 text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="stepForm" class="space-y-6">
        <!-- Database Driver Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Database Driver
            </label>
            <select name="db_driver" id="db_driver" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                <?php foreach ($databaseDrivers as $key => $driver): ?>
                <option value="<?php echo $key; ?>" <?php echo ($_POST['db_driver'] ?? 'mysql') === $key ? 'selected' : ''; ?>>
                    <?php echo $driver['name']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Connection Details Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Database Host
                </label>
                <input type="text" name="db_host" value="<?php echo $_POST['db_host'] ?? 'localhost'; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    placeholder="localhost" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Port
                </label>
                <input type="number" name="db_port" id="db_port" value="<?php echo $_POST['db_port'] ?? '3306'; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    required>
            </div>
        </div>

        <!-- Database Credentials -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Database Name
            </label>
            <input type="text" name="db_name" value="<?php echo $_POST['db_name'] ?? 'habibistay'; ?>" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                placeholder="habibistay" required>
            <p class="mt-1 text-sm text-gray-500">Will be created if it doesn't exist</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Username
                </label>
                <input type="text" name="db_username" value="<?php echo $_POST['db_username'] ?? ''; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    placeholder="root" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <input type="password" name="db_password" value="<?php echo $_POST['db_password'] ?? ''; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                    placeholder="••••••••">
            </div>
        </div>

        <!-- Application URL -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Application URL
            </label>
            <input type="url" name="app_url" value="<?php echo $_POST['app_url'] ?? 'http://localhost'; ?>" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                placeholder="https://habibistay.com" required>
            <p class="mt-1 text-sm text-gray-500">Full URL where HabibiStay will be accessible</p>
        </div>

        <!-- Security Notice -->
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-lock text-amber-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Security Recommendation</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Create a dedicated database user with limited privileges for production use.
                        Avoid using root credentials in production environments.
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between pt-4">
            <a href="?step=2" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Previous
            </a>
            
            <button type="submit" id="submitBtn" onclick="showLoader()" 
                class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
                Test Connection & Continue<i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>

<script>
// Dynamic port adjustment based on driver selection
document.getElementById('db_driver').addEventListener('change', function() {
    const ports = {
        'mysql': 3306,
        'pgsql': 5432
    };
    document.getElementById('db_port').value = ports[this.value] || 3306;
});
</script>
