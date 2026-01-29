<?php
/**
 * Admin User Creation & Database Migration Module
 * Implements secure user creation with automatic database migration
 */

$error = '';
$success = false;
$migrationStatus = [];
$migrationComplete = false;

/**
 * Database Migration Manager
 * Handles automatic Laravel migration execution
 */
class DatabaseMigrationManager {
    private string $basePath;
    private array $output = [];
    
    public function __construct() {
        $this->basePath = dirname(__DIR__, 2);
    }
    
    public function runMigrations(): array {
        try {
            // Generate application key if not exists
            $this->generateAppKey();
            
            // Clear any cache
            $this->clearCache();
            
            // Run migrations
            $migrationResult = $this->executeMigrations();
            
            // Run essential seeders
            $seederResult = $this->runSeeders();
            
            return [
                'success' => $migrationResult && $seederResult,
                'output' => $this->output,
                'details' => [
                    'migrations' => $migrationResult,
                    'seeders' => $seederResult
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'output' => $this->output,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function generateAppKey(): void {
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (strpos($content, 'APP_KEY=') !== false && strpos($content, 'APP_KEY=base64:') === false) {
                $this->executeCommand('php artisan key:generate --force');
                $this->output[] = '✓ Application key generated successfully';
            }
        }
    }
    
    private function clearCache(): void {
        $commands = [
            'php artisan config:clear',
            'php artisan cache:clear',
            'php artisan route:clear',
            'php artisan view:clear'
        ];
        
        foreach ($commands as $command) {
            $this->executeCommand($command);
        }
        $this->output[] = '✓ Application cache cleared';
    }
    
    private function executeMigrations(): bool {
        $result = $this->executeCommand('php artisan migrate --force --no-interaction');
        $this->output[] = '✓ Database migrations executed successfully';
        return $result;
    }
    
    private function runSeeders(): bool {
        // Don't run AdminUserSeeder here as we'll create admin manually
        $result = $this->executeCommand('php artisan db:seed --class=UserPreferencesSeeder --force --no-interaction');
        $this->output[] = '✓ Essential data seeded';
        return $result;
    }
    
    private function executeCommand(string $command): bool {
        $descriptorspec = [
            0 => ["pipe", "r"],  
            1 => ["pipe", "w"],  
            2 => ["pipe", "w"]   
        ];
        
        $cwd = $this->basePath;
        $process = proc_open($command, $descriptorspec, $pipes, $cwd);
        
        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $return_value = proc_close($process);
            
            if ($error && $return_value !== 0) {
                $this->output[] = "✗ Error: " . trim($error);
                return false;
            }
            
            return $return_value === 0;
        }
        
        return false;
    }
    
    public function createAdminUser(array $userData): bool {
        try {
            // Add Laravel bootstrap
            require_once $this->basePath . '/vendor/autoload.php';
            
            $app = require_once $this->basePath . '/bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            // Check if admin already exists
            $existingAdmin = \App\Models\User::where('email', $userData['email'])->first();
            if ($existingAdmin) {
                $this->output[] = '✗ Admin user with this email already exists';
                return false;
            }
            
            // Create admin user
            $user = new \App\Models\User();
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->password = \Illuminate\Support\Facades\Hash::make($userData['password']);
            $user->role = 'admin';
            $user->status = 'active';
            $user->email_verified_at = now();
            $user->language = 'en';
            $user->preferences = [
                'dashboard_layout' => 'default',
                'notifications' => true,
                'dark_mode' => false
            ];
            $user->notification_settings = [
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true
            ];
            $user->save();
            
            $this->output[] = '✓ Admin user created successfully';
            return true;
        } catch (Exception $e) {
            $this->output[] = '✗ Error creating admin user: ' . $e->getMessage();
            return false;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'run_migration') {
        // Run database migration
        $migrationManager = new DatabaseMigrationManager();
        $result = $migrationManager->runMigrations();
        
        if ($result['success']) {
            $_SESSION['migration_complete'] = true;
            $migrationStatus = $result['output'];
            $migrationComplete = true;
        } else {
            $error = 'Migration failed: ' . ($result['error'] ?? 'Unknown error');
            $migrationStatus = $result['output'];
        }
    } elseif ($action === 'create_admin') {
        // Validate admin user data
        $name = $_POST['admin_name'] ?? '';
        $email = $_POST['admin_email'] ?? '';
        $password = $_POST['admin_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Create admin user
            $migrationManager = new DatabaseMigrationManager();
            $adminCreated = $migrationManager->createAdminUser([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);
            
            if ($adminCreated) {
                $_SESSION['admin_created'] = true;
                $_SESSION['admin_email'] = $email;
                header('Location: ?step=5');
                exit;
            } else {
                $error = 'Failed to create admin user';
            }
        }
    }
}

$migrationComplete = $_SESSION['migration_complete'] ?? false;
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-user-shield mr-2"></i>Database Setup & Admin Account
        </h2>
        <p class="text-gray-600">
            Initialize the database and create your administrator account.
        </p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$migrationComplete): ?>
    <!-- Step 1: Run Database Migrations -->
    <div class="bg-white border-2 border-purple-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-database mr-2 text-purple-600"></i>Step 1: Initialize Database
        </h3>
        <p class="text-gray-600 mb-4">
            Click the button below to automatically run database migrations and set up the required tables.
        </p>
        
        <form method="POST" id="migrationForm">
            <input type="hidden" name="action" value="run_migration">
            <button type="submit" id="migrationBtn" onclick="showMigrationProgress()" 
                class="px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-play mr-2"></i>Run Database Migration
            </button>
        </form>
        
        <?php if (!empty($migrationStatus)): ?>
        <div class="mt-4 bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Migration Progress:</h4>
            <div class="space-y-1">
                <?php foreach ($migrationStatus as $status): ?>
                <div class="text-sm text-gray-700 font-mono"><?php echo htmlspecialchars($status); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Step 2: Create Admin User -->
    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Database Ready!</h3>
                <p class="mt-1 text-sm text-green-700">All database tables have been created successfully.</p>
            </div>
        </div>
    </div>

    <div class="bg-white border-2 border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-user-plus mr-2 text-blue-600"></i>Step 2: Create Administrator Account
        </h3>

        <form method="POST" id="stepForm" class="space-y-6">
            <input type="hidden" name="action" value="create_admin">
            
            <!-- Admin Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name
                </label>
                <input type="text" name="admin_name" value="<?php echo $_POST['admin_name'] ?? ''; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Abdullah Mirza" required>
            </div>

            <!-- Admin Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                </label>
                <input type="email" name="admin_email" value="<?php echo $_POST['admin_email'] ?? 'admin@habibistay.com'; ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="admin@habibistay.com" required>
                <p class="mt-1 text-sm text-gray-500">This will be your login username</p>
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="password" name="admin_password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="••••••••" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input type="password" name="confirm_password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="••••••••" required>
                </div>
            </div>

            <!-- Password Requirements -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">Password Requirements</h4>
                        <p class="mt-1 text-sm text-blue-700">
                            Password must be at least 8 characters long.
                        </p>
                    </div>
                </div>
            </div>

            <button type="submit" id="adminBtn" 
                class="w-full px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Create Administrator Account
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="flex justify-between pt-4">
        <a href="?step=3" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Previous
        </a>
        
        <?php if ($migrationComplete): ?>
        <span class="text-sm text-gray-500">Complete the admin account creation to continue</span>
        <?php endif; ?>
    </div>
</div>

<script>
function showMigrationProgress() {
    const btn = document.getElementById('migrationBtn');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Running Migrations...';
        btn.disabled = true;
    }
}

function validateStep() {
    if (!<?php echo $migrationComplete ? 'true' : 'false'; ?>) {
        return false;
    }
    
    const form = document.getElementById('stepForm');
    if (form) {
        const inputs = form.querySelectorAll('[required]');
        for (let input of inputs) {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                return false;
            }
            input.classList.remove('border-red-500');
        }
    }
    return true;
}
</script>