<?php
/**
 * Installation Finalization Module
 * Implements Command pattern for orchestrating installation tasks
 * Utilizing async execution strategies for optimal performance
 */

// Prevent access if previous steps not completed
if (!isset($_SESSION['requirements_passed']) || 
    !isset($_SESSION['permissions_passed']) || 
    !isset($_SESSION['database_configured']) || 
    !isset($_SESSION['admin_created']) || 
    !isset($_SESSION['configuration_completed'])) {
    header('Location: ?step=1');
    exit;
}

/**
 * Installation Command Interface
 * Defines contract for all installation commands
 */
interface InstallationCommand {
    public function execute(): array;
    public function getDescription(): string;
}

/**
 * Laravel Setup Command
 * Implements Laravel initialization procedures
 */
class LaravelSetupCommand implements InstallationCommand {
    private array $dbConfig;
    
    public function __construct(array $dbConfig) {
        $this->dbConfig = $dbConfig;
    }
    
    public function execute(): array {
        try {
            // Create Laravel directory structure
            $directories = [
                '../app/Models',
                '../app/Http/Controllers/Guest',
                '../app/Http/Controllers/Host',
                '../app/Http/Controllers/Admin',
                '../app/Http/Controllers/Api',
                '../app/Services',
                '../app/Repositories',
                '../app/AI/Sara',
                '../app/Events',
                '../app/Listeners',
                '../app/Jobs',
                '../app/Mail',
                '../app/Notifications',
                '../app/Policies',
                '../app/Providers',
                '../app/Rules',
                '../database/migrations',
                '../database/seeders',
                '../database/factories',
                '../resources/views/layouts',
                '../resources/views/guest',
                '../resources/views/host',
                '../resources/views/admin',
                '../resources/views/components',
                '../resources/js/components',
                '../resources/css',
                '../public/assets/images',
                '../public/assets/css',
                '../public/assets/js',
                '../public/uploads/properties',
                '../public/uploads/users',
                '../public/uploads/documents',
                '../routes',
                '../storage/app/public',
                '../storage/framework/cache',
                '../storage/framework/sessions',
                '../storage/framework/testing',
                '../storage/framework/views',
                '../storage/logs',
                '../tests/Feature',
                '../tests/Unit'
            ];
            
            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            
            return ['success' => true, 'message' => 'Laravel structure created'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getDescription(): string {
        return 'Creating Laravel directory structure';
    }
}

/**
 * Database Migration Command
 * Executes database schema creation
 */
class DatabaseMigrationCommand implements InstallationCommand {
    private PDO $connection;
    
    public function __construct(array $dbConfig) {
        $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        $this->connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function execute(): array {
        try {
            // Create tables using raw SQL for installation
            $migrations = [
                // Users table
                "CREATE TABLE IF NOT EXISTS users (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    email_verified_at TIMESTAMP NULL,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(20),
                    role ENUM('guest', 'host', 'admin') DEFAULT 'guest',
                    avatar VARCHAR(255),
                    language VARCHAR(10) DEFAULT 'en',
                    bio TEXT,
                    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                    google_id VARCHAR(255),
                    facebook_id VARCHAR(255),
                    remember_token VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_email (email),
                    INDEX idx_role (role),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Properties table
                "CREATE TABLE IF NOT EXISTS properties (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) UNIQUE NOT NULL,
                    description TEXT,
                    property_type ENUM('house', 'apartment', 'villa', 'studio') NOT NULL,
                    room_type ENUM('entire_place', 'private_room', 'shared_room') NOT NULL,
                    accommodates INT DEFAULT 1,
                    bedrooms INT DEFAULT 1,
                    beds INT DEFAULT 1,
                    bathrooms DECIMAL(3,1) DEFAULT 1.0,
                    price_per_night DECIMAL(10,2) NOT NULL,
                    cleaning_fee DECIMAL(10,2) DEFAULT 0,
                    service_fee_percentage DECIMAL(5,2) DEFAULT 10.00,
                    address VARCHAR(255),
                    city VARCHAR(100),
                    state VARCHAR(100),
                    country VARCHAR(100) DEFAULT 'Saudi Arabia',
                    postal_code VARCHAR(20),
                    latitude DECIMAL(10,8),
                    longitude DECIMAL(11,8),
                    check_in_time TIME DEFAULT '15:00:00',
                    check_out_time TIME DEFAULT '11:00:00',
                    cancellation_policy ENUM('flexible', 'moderate', 'strict') DEFAULT 'flexible',
                    instant_booking BOOLEAN DEFAULT FALSE,
                    is_featured BOOLEAN DEFAULT FALSE,
                    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
                    views INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user (user_id),
                    INDEX idx_slug (slug),
                    INDEX idx_city (city),
                    INDEX idx_status (status),
                    INDEX idx_price (price_per_night)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Bookings table
                "CREATE TABLE IF NOT EXISTS bookings (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    property_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    host_id BIGINT UNSIGNED NOT NULL,
                    check_in DATE NOT NULL,
                    check_out DATE NOT NULL,
                    guests INT DEFAULT 1,
                    price_per_night DECIMAL(10,2) NOT NULL,
                    total_nights INT NOT NULL,
                    cleaning_fee DECIMAL(10,2) DEFAULT 0,
                    service_fee DECIMAL(10,2) DEFAULT 0,
                    total_amount DECIMAL(10,2) NOT NULL,
                    currency VARCHAR(3) DEFAULT 'SAR',
                    status ENUM('pending', 'accepted', 'declined', 'cancelled', 'completed') DEFAULT 'pending',
                    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
                    payment_method VARCHAR(50),
                    transaction_id VARCHAR(255),
                    special_requests TEXT,
                    host_message TEXT,
                    cancellation_reason TEXT,
                    cancelled_by BIGINT UNSIGNED,
                    cancelled_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_property (property_id),
                    INDEX idx_user (user_id),
                    INDEX idx_host (host_id),
                    INDEX idx_dates (check_in, check_out),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Amenities table
                "CREATE TABLE IF NOT EXISTS amenities (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    icon VARCHAR(50),
                    category ENUM('basic', 'safety', 'kitchen', 'bathroom', 'bedroom', 'entertainment', 'outdoor', 'parking') DEFAULT 'basic',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Property amenities pivot table
                "CREATE TABLE IF NOT EXISTS property_amenities (
                    property_id BIGINT UNSIGNED NOT NULL,
                    amenity_id BIGINT UNSIGNED NOT NULL,
                    PRIMARY KEY (property_id, amenity_id),
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Property images table
                "CREATE TABLE IF NOT EXISTS property_images (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    property_id BIGINT UNSIGNED NOT NULL,
                    image_path VARCHAR(255) NOT NULL,
                    caption VARCHAR(255),
                    is_primary BOOLEAN DEFAULT FALSE,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                    INDEX idx_property (property_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Reviews table
                "CREATE TABLE IF NOT EXISTS reviews (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    booking_id BIGINT UNSIGNED NOT NULL,
                    property_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    host_id BIGINT UNSIGNED NOT NULL,
                    rating DECIMAL(2,1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
                    cleanliness_rating INT CHECK (cleanliness_rating >= 1 AND cleanliness_rating <= 5),
                    communication_rating INT CHECK (communication_rating >= 1 AND communication_rating <= 5),
                    checkin_rating INT CHECK (checkin_rating >= 1 AND checkin_rating <= 5),
                    accuracy_rating INT CHECK (accuracy_rating >= 1 AND accuracy_rating <= 5),
                    location_rating INT CHECK (location_rating >= 1 AND location_rating <= 5),
                    value_rating INT CHECK (value_rating >= 1 AND value_rating <= 5),
                    comment TEXT,
                    host_response TEXT,
                    host_responded_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_property (property_id),
                    INDEX idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Messages table
                "CREATE TABLE IF NOT EXISTS messages (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    sender_id BIGINT UNSIGNED NOT NULL,
                    receiver_id BIGINT UNSIGNED NOT NULL,
                    property_id BIGINT UNSIGNED,
                    booking_id BIGINT UNSIGNED,
                    message TEXT NOT NULL,
                    is_read BOOLEAN DEFAULT FALSE,
                    read_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
                    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
                    INDEX idx_sender (sender_id),
                    INDEX idx_receiver (receiver_id),
                    INDEX idx_unread (receiver_id, is_read)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Wishlists table
                "CREATE TABLE IF NOT EXISTS wishlists (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    property_id BIGINT UNSIGNED NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_wishlist (user_id, property_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Disputes table
                "CREATE TABLE IF NOT EXISTS disputes (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    booking_id BIGINT UNSIGNED NOT NULL,
                    raised_by BIGINT UNSIGNED NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('open', 'under_review', 'resolved', 'closed') DEFAULT 'open',
                    admin_notes TEXT,
                    resolution TEXT,
                    resolved_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
                    FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Sara chatbot conversations
                "CREATE TABLE IF NOT EXISTS sara_conversations (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED,
                    session_id VARCHAR(255) NOT NULL,
                    context JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_session (session_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Sara chatbot messages
                "CREATE TABLE IF NOT EXISTS sara_messages (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    conversation_id BIGINT UNSIGNED NOT NULL,
                    role ENUM('user', 'assistant', 'system') NOT NULL,
                    content TEXT NOT NULL,
                    metadata JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (conversation_id) REFERENCES sara_conversations(id) ON DELETE CASCADE,
                    INDEX idx_conversation (conversation_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            ];
            
            foreach ($migrations as $sql) {
                $this->connection->exec($sql);
            }
            
            return ['success' => true, 'message' => 'Database tables created'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getDescription(): string {
        return 'Creating database tables';
    }
}

/**
 * Database Seeder Command
 * Populates initial data
 */
class DatabaseSeederCommand implements InstallationCommand {
    private PDO $connection;
    private array $adminData;
    
    public function __construct(array $dbConfig, array $adminData) {
        $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        $this->connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->adminData = $adminData;
    }
    
    public function execute(): array {
        try {
            // Insert admin user
            $stmt = $this->connection->prepare("
                INSERT INTO users (name, email, password, role, status, email_verified_at)
                VALUES (:name, :email, :password, 'admin', 'active', NOW())
            ");
            $stmt->execute([
                'name' => $this->adminData['name'],
                'email' => $this->adminData['email'],
                'password' => $this->adminData['password_hash']
            ]);
            
            // Insert default amenities
            $amenities = [
                ['name' => 'WiFi', 'icon' => 'fa-wifi', 'category' => 'basic'],
                ['name' => 'TV', 'icon' => 'fa-tv', 'category' => 'entertainment'],
                ['name' => 'Kitchen', 'icon' => 'fa-utensils', 'category' => 'kitchen'],
                ['name' => 'Washer', 'icon' => 'fa-tshirt', 'category' => 'basic'],
                ['name' => 'Free parking', 'icon' => 'fa-parking', 'category' => 'parking'],
                ['name' => 'Air conditioning', 'icon' => 'fa-snowflake', 'category' => 'basic'],
                ['name' => 'Heating', 'icon' => 'fa-temperature-high', 'category' => 'basic'],
                ['name' => 'Pool', 'icon' => 'fa-swimming-pool', 'category' => 'outdoor'],
                ['name' => 'Gym', 'icon' => 'fa-dumbbell', 'category' => 'basic'],
                ['name' => 'Hot tub', 'icon' => 'fa-hot-tub', 'category' => 'outdoor'],
                ['name' => 'Smoke alarm', 'icon' => 'fa-bell', 'category' => 'safety'],
                ['name' => 'Carbon monoxide alarm', 'icon' => 'fa-wind', 'category' => 'safety'],
                ['name' => 'First aid kit', 'icon' => 'fa-medkit', 'category' => 'safety'],
                ['name' => 'Fire extinguisher', 'icon' => 'fa-fire-extinguisher', 'category' => 'safety'],
                ['name' => 'Lock on bedroom door', 'icon' => 'fa-lock', 'category' => 'safety']
            ];
            
            $stmt = $this->connection->prepare("
                INSERT INTO amenities (name, icon, category) VALUES (:name, :icon, :category)
            ");
            
            foreach ($amenities as $amenity) {
                $stmt->execute($amenity);
            }
            
            return ['success' => true, 'message' => 'Initial data seeded'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getDescription(): string {
        return 'Seeding initial data';
    }
}

/**
 * Installation Orchestrator
 * Coordinates all installation commands
 */
class InstallationOrchestrator {
    private array $commands = [];
    private array $results = [];
    
    public function addCommand(InstallationCommand $command): void {
        $this->commands[] = $command;
    }
    
    public function execute(): array {
        foreach ($this->commands as $command) {
            $this->results[] = [
                'description' => $command->getDescription(),
                'result' => $command->execute()
            ];
            
            // Stop on failure
            if (!end($this->results)['result']['success']) {
                break;
            }
        }
        
        return $this->results;
    }
    
    public function isSuccessful(): bool {
        foreach ($this->results as $result) {
            if (!$result['result']['success']) {
                return false;
            }
        }
        return true;
    }
}

// Execute installation if triggered
$installationStarted = false;
$installationResults = [];
$installationSuccess = false;

if (isset($_POST['start_installation'])) {
    $installationStarted = true;
    
    // Create orchestrator
    $orchestrator = new InstallationOrchestrator();
    
    // Add commands
    $orchestrator->addCommand(new LaravelSetupCommand($_SESSION['db_config']));
    $orchestrator->addCommand(new DatabaseMigrationCommand($_SESSION['db_config']));
    $orchestrator->addCommand(new DatabaseSeederCommand($_SESSION['db_config'], $_SESSION['admin_user']));
    
    // Execute installation
    $installationResults = $orchestrator->execute();
    $installationSuccess = $orchestrator->isSuccessful();
    
    if ($installationSuccess) {
        // Create installation marker
        file_put_contents('../.installed', date('Y-m-d H:i:s'));
        
        // Clear session
        session_destroy();
    }
}
?>

<div class="space-y-6">
    <?php if (!$installationStarted): ?>
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-rocket mr-2"></i>Ready to Install
        </h2>
        <p class="text-gray-600">
            All prerequisites have been verified. Click the button below to begin the installation process.
        </p>
    </div>

    <!-- Installation Summary -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="font-semibold text-lg mb-4">Installation Summary</h3>
        
        <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-gray-600">Database</span>
                <span class="font-medium"><?php echo $_SESSION['db_config']['database']; ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-gray-600">Admin Email</span>
                <span class="font-medium"><?php echo $_SESSION['admin_user']['email']; ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-gray-600">Application URL</span>
                <span class="font-medium"><?php echo $_SESSION['db_config']['app_url'] ?? 'http://localhost'; ?></span>
            </div>
        </div>
    </div>

    <!-- Warning -->
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-amber-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800">Important</h3>
                <p class="mt-1 text-sm text-amber-700">
                    The installation will create database tables and initial data. 
                    This process may take a few moments. Do not close this window during installation.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" class="text-center">
        <button type="submit" name="start_installation" value="1" 
            class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-medium rounded-lg hover:from-green-700 hover:to-green-800 transition-colors text-lg">
            <i class="fas fa-download mr-2"></i>Start Installation
        </button>
    </form>

    <?php else: ?>
    
    <!-- Installation Progress -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-cog fa-spin mr-2"></i>Installation Progress
        </h2>
    </div>

    <div class="space-y-4">
        <?php foreach ($installationResults as $result): ?>
        <div class="bg-white border rounded-lg p-4 <?php echo $result['result']['success'] ? 'border-green-200' : 'border-red-200'; ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium"><?php echo $result['description']; ?></p>
                    <?php if (!$result['result']['success']): ?>
                    <p class="text-sm text-red-600 mt-1"><?php echo $result['result']['message']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($result['result']['success']): ?>
                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                    <?php else: ?>
                        <i class="fas fa-times-circle text-2xl text-red-500"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($installationSuccess): ?>
    <!-- Success Message -->
    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-green-800">Installation Completed!</h3>
                <p class="mt-2 text-sm text-green-700">
                    HabibiStay has been successfully installed. You can now access your platform.
                </p>
            </div>
        </div>
    </div>

    <!-- Next Steps -->
    <div class="bg-blue-50 rounded-lg p-6">
        <h3 class="font-semibold text-lg mb-4">Next Steps</h3>
        <ol class="list-decimal list-inside space-y-2 text-sm text-blue-900">
            <li>Delete the <code class="bg-blue-100 px-1 rounded">/install</code> directory for security</li>
            <li>Set up a cron job for scheduled tasks: <code class="bg-blue-100 px-1 rounded">* * * * * php artisan schedule:run</code></li>
            <li>Configure your web server to point to the <code class="bg-blue-100 px-1 rounded">/public</code> directory</li>
            <li>Enable HTTPS for secure connections</li>
            <li>Complete payment gateway configuration in the admin panel</li>
        </ol>
    </div>

    <div class="text-center pt-4">
        <a href="../public" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>Go to HabibiStay
        </a>
    </div>

    <?php else: ?>
    <!-- Error Message -->
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-red-800">Installation Failed</h3>
                <p class="mt-2 text-sm text-red-700">
                    Please fix the errors above and try again. You may need to manually clean up any partial installation.
                </p>
            </div>
        </div>
    </div>

    <div class="text-center pt-4">
        <a href="?step=1" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-redo mr-2"></i>Start Over
        </a>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
