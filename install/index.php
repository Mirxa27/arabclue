<?php
/**
 * HabibiStay Installation Wizard
 * @version 1.0.0
 * @author HabibiStay Development Team
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define installation steps
$steps = [
    1 => 'requirements',
    2 => 'permissions',
    3 => 'database',
    4 => 'admin',
    5 => 'configuration',
    6 => 'finish'
];

$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$currentStep = max(1, min(6, $currentStep));

// Check if already installed
if (file_exists('../.installed') && $currentStep !== 6) {
    header('Location: ../public');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabibiStay Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .step-active {
            background-color: #667eea;
            color: white;
        }
        .step-completed {
            background-color: #48bb78;
            color: white;
        }
        .loader {
            border-top-color: #667eea;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }
        @-webkit-keyframes spinner {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }
        @keyframes spinner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="gradient-bg text-white py-6 shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-home text-3xl mr-3"></i>
                        <h1 class="text-2xl font-bold">HabibiStay Installation Wizard</h1>
                    </div>
                    <span class="text-sm opacity-75">Version 1.0.0</span>
                </div>
            </div>
        </header>

        <!-- Progress Steps -->
        <div class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between">
                    <?php foreach ($steps as $num => $step): ?>
                    <div class="flex-1 text-center">
                        <div class="relative">
                            <?php if ($num < count($steps)): ?>
                            <div class="absolute top-1/2 right-0 w-full h-0.5 bg-gray-300 -z-10"></div>
                            <?php endif; ?>
                            <div class="mx-auto w-10 h-10 rounded-full flex items-center justify-center 
                                <?php echo $num < $currentStep ? 'step-completed' : ($num == $currentStep ? 'step-active' : 'bg-gray-300'); ?>">
                                <?php if ($num < $currentStep): ?>
                                    <i class="fas fa-check text-sm"></i>
                                <?php else: ?>
                                    <?php echo $num; ?>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs mt-2 <?php echo $num == $currentStep ? 'font-semibold' : ''; ?>">
                                <?php echo ucfirst($step); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <?php
                    // Include step content
                    $stepFile = "steps/{$steps[$currentStep]}.php";
                    if (file_exists($stepFile)) {
                        include $stepFile;
                    } else {
                        echo "<p class='text-red-600'>Error: Step file not found.</p>";
                    }
                    ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4">
            <div class="container mx-auto px-4 text-center">
                <p class="text-sm">© 2025 HabibiStay. Building Wealth, Creating Memories.</p>
            </div>
        </footer>
    </div>

    <script>
        // Installation wizard JavaScript utilities
        function validateStep() {
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

        function showLoader() {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                btn.disabled = true;
            }
        }
    </script>
</body>
</html>
