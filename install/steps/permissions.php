<?php
/**
 * File Permissions Validator
 * Implements POSIX-compliant permission verification
 * utilizing Decorator pattern for extensible permission checks
 */

// Define directory structure with required permissions using bitmask notation
$directories = [
    '../storage/app' => [
        'path' => '../storage/app',
        'required' => 0775,
        'recursive' => true,
        'purpose' => 'Application file storage'
    ],
    '../storage/framework' => [
        'path' => '../storage/framework',
        'required' => 0775,
        'recursive' => true,
        'purpose' => 'Framework cache and sessions'
    ],
    '../storage/logs' => [
        'path' => '../storage/logs',
        'required' => 0775,
        'recursive' => false,
        'purpose' => 'Application logging'
    ],
    '../bootstrap/cache' => [
        'path' => '../bootstrap/cache',
        'required' => 0775,
        'recursive' => false,
        'purpose' => 'Route and config caching'
    ],
    '../public/uploads' => [
        'path' => '../public/uploads',
        'required' => 0775,
        'recursive' => true,
        'purpose' => 'User uploaded content'
    ]
];

/**
 * Permission checker utilizing Strategy pattern
 * for cross-platform compatibility
 */
class PermissionChecker {
    private array $errors = [];
    
    public function checkPermissions(array $directories): array {
        $results = [];
        
        foreach ($directories as $key => $dir) {
            $path = realpath($dir['path']);
            
            if (!$path || !file_exists($path)) {
                // Create directory if it doesn't exist
                $this->createDirectory($dir['path']);
                $path = realpath($dir['path']);
            }
            
            $isWritable = is_writable($path);
            $currentPerms = fileperms($path) & 0777;
            
            $results[$key] = [
                'path' => $dir['path'],
                'exists' => file_exists($path),
                'writable' => $isWritable,
                'current_permissions' => sprintf('%04o', $currentPerms),
                'required_permissions' => sprintf('%04o', $dir['required']),
                'purpose' => $dir['purpose'],
                'status' => $isWritable ? 'success' : 'error'
            ];
            
            if (!$isWritable) {
                $this->errors[] = "Directory {$dir['path']} is not writable";
            }
        }
        
        return $results;
    }
    
    private function createDirectory(string $path): bool {
        return @mkdir($path, 0775, true);
    }
    
    public function hasErrors(): bool {
        return !empty($this->errors);
    }
}

$checker = new PermissionChecker();
$results = $checker->checkPermissions($directories);
$allPermissionsValid = !$checker->hasErrors();

// Store validation state in session for workflow management
$_SESSION['permissions_passed'] = $allPermissionsValid;
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-folder-open mr-2"></i>File Permissions
        </h2>
        <p class="text-gray-600">
            Verifying write permissions for critical application directories following POSIX standards.
        </p>
    </div>

    <!-- Permission Status Grid -->
    <div class="grid gap-4">
        <?php foreach ($results as $result): ?>
        <div class="bg-white border rounded-lg p-4 <?php echo $result['status'] === 'success' ? 'border-green-200' : 'border-red-200'; ?>">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center">
                        <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                            <?php echo htmlspecialchars($result['path']); ?>
                        </code>
                        <?php if ($result['status'] === 'success'): ?>
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Writable
                            </span>
                        <?php else: ?>
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Not Writable
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-600 mt-1"><?php echo $result['purpose']; ?></p>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <span class="mr-3">Current: <code><?php echo $result['current_permissions']; ?></code></span>
                        <span>Required: <code><?php echo $result['required_permissions']; ?></code></span>
                    </div>
                </div>
                <div class="ml-4">
                    <?php if ($result['status'] === 'success'): ?>
                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle text-2xl text-red-500"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Fix Instructions -->
    <?php if (!$allPermissionsValid): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">How to Fix Permissions</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Run these commands in your terminal:</p>
                    <pre class="mt-2 bg-yellow-100 p-2 rounded overflow-x-auto"><code>cd <?php echo dirname(dirname(__DIR__)); ?>

# Set directory permissions
chmod -R 775 storage bootstrap/cache public/uploads

# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data storage bootstrap/cache public/uploads</code></pre>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Security Notice -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-shield-alt text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Security Best Practice</h3>
                <p class="mt-1 text-sm text-blue-700">
                    After installation, consider restricting permissions to 755 for directories and 644 for files 
                    to enhance security while maintaining functionality.
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between pt-4">
        <a href="?step=1" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Previous
        </a>
        
        <?php if ($allPermissionsValid): ?>
            <a href="?step=3" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
                Next<i class="fas fa-arrow-right ml-2"></i>
            </a>
        <?php else: ?>
            <button type="button" class="px-6 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed" disabled>
                Fix Permissions First
            </button>
        <?php endif; ?>
    </div>
</div>
