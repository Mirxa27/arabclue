<?php
/**
 * Requirements Validation Module
 * Implements comprehensive system requirement verification
 * using Factory pattern for extensible checks
 */

// Define minimum requirements using configuration array
$requirements = [
    'php' => [
        'required' => '8.1.0',
        'current' => PHP_VERSION,
        'satisfied' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'message' => 'PHP 8.1+ required for Laravel 10.x compatibility'
    ],
    'extensions' => [
        'openssl' => [
            'loaded' => extension_loaded('openssl'),
            'message' => 'OpenSSL PHP Extension required for encryption'
        ],
        'pdo' => [
            'loaded' => extension_loaded('pdo'),
            'message' => 'PDO PHP Extension required for database abstraction'
        ],
        'mbstring' => [
            'loaded' => extension_loaded('mbstring'),
            'message' => 'Mbstring PHP Extension required for UTF-8 support'
        ],
        'tokenizer' => [
            'loaded' => extension_loaded('tokenizer'),
            'message' => 'Tokenizer PHP Extension required for Artisan'
        ],
        'xml' => [
            'loaded' => extension_loaded('xml'),
            'message' => 'XML PHP Extension required for XML parsing'
        ],
        'ctype' => [
            'loaded' => extension_loaded('ctype'),
            'message' => 'Ctype PHP Extension required for character validation'
        ],
        'json' => [
            'loaded' => extension_loaded('json'),
            'message' => 'JSON PHP Extension required for API communication'
        ],
        'bcmath' => [
            'loaded' => extension_loaded('bcmath'),
            'message' => 'BCMath PHP Extension required for precision calculations'
        ],
        'curl' => [
            'loaded' => extension_loaded('curl'),
            'message' => 'cURL PHP Extension required for HTTP requests'
        ],
        'fileinfo' => [
            'loaded' => extension_loaded('fileinfo'),
            'message' => 'Fileinfo PHP Extension required for file validation'
        ],
        'gd' => [
            'loaded' => extension_loaded('gd') || extension_loaded('imagick'),
            'message' => 'GD/Imagick PHP Extension required for image processing'
        ]
    ]
];

// Calculate overall status
$allRequirementsMet = $requirements['php']['satisfied'];
foreach ($requirements['extensions'] as $ext) {
    if (!$ext['loaded']) {
        $allRequirementsMet = false;
        break;
    }
}

// Store status in session for navigation control
$_SESSION['requirements_passed'] = $allRequirementsMet;
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-server mr-2"></i>System Requirements
        </h2>
        <p class="text-gray-600">
            Verifying your server meets the minimum requirements for HabibiStay platform.
        </p>
    </div>

    <!-- PHP Version Check -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="font-semibold text-lg mb-4">PHP Version</h3>
        <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow-sm">
            <div>
                <span class="font-medium">PHP <?php echo $requirements['php']['required']; ?>+</span>
                <span class="text-sm text-gray-500 ml-2">(Current: <?php echo $requirements['php']['current']; ?>)</span>
            </div>
            <div>
                <?php if ($requirements['php']['satisfied']): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Passed
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i> Failed
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PHP Extensions Check -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="font-semibold text-lg mb-4">Required PHP Extensions</h3>
        <div class="space-y-2">
            <?php foreach ($requirements['extensions'] as $name => $ext): ?>
            <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                <div>
                    <span class="font-medium"><?php echo ucfirst($name); ?></span>
                    <span class="text-sm text-gray-500 ml-2"><?php echo $ext['message']; ?></span>
                </div>
                <div>
                    <?php if ($ext['loaded']): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i> Enabled
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times mr-1"></i> Missing
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Additional Recommendations -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Recommended Configuration</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Memory Limit: 256MB+ (Current: <?php echo ini_get('memory_limit'); ?>)</li>
                        <li>Max Execution Time: 300s+ (Current: <?php echo ini_get('max_execution_time'); ?>s)</li>
                        <li>Upload Max Filesize: 50MB+ (Current: <?php echo ini_get('upload_max_filesize'); ?>)</li>
                        <li>Post Max Size: 50MB+ (Current: <?php echo ini_get('post_max_size'); ?>)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between pt-4">
        <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800" disabled>
            <i class="fas fa-arrow-left mr-2"></i>Previous
        </button>
        
        <?php if ($allRequirementsMet): ?>
            <a href="?step=2" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
                Next<i class="fas fa-arrow-right ml-2"></i>
            </a>
        <?php else: ?>
            <button type="button" class="px-6 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed" disabled>
                Fix Requirements First
            </button>
        <?php endif; ?>
    </div>
</div>
