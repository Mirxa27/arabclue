<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabibiStay Email Templates Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">📧 HabibiStay Email Templates</h1>
                <p class="text-xl text-blue-100 mb-6">Preview and test all email templates across different devices</p>
                <div class="flex justify-center space-x-4">
                    <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2">
                        <span class="text-sm font-medium">✅ 10 Templates</span>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2">
                        <span class="text-sm font-medium">📱 Responsive Design</span>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2">
                        <span class="text-sm font-medium">🎨 Professional Branding</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Preview Buttons -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-sm border p-4 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📱 Device Preview Mode</h3>
            <div class="flex flex-wrap gap-3">
                <button onclick="setDeviceMode('desktop')" class="device-btn bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-desktop mr-2"></i>Desktop
                </button>
                <button onclick="setDeviceMode('tablet')" class="device-btn bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-tablet-alt mr-2"></i>Tablet
                </button>
                <button onclick="setDeviceMode('mobile')" class="device-btn bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-mobile-alt mr-2"></i>Mobile
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-2">Click on any email template below to preview it in the selected device mode.</p>
        </div>
    </div>

    <!-- Email Templates Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($emailTemplates as $key => $template)
            <div class="bg-white rounded-xl shadow-sm border card-hover cursor-pointer" onclick="previewEmail('{{ $key }}')">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 {{ $template['color'] }} rounded-lg flex items-center justify-center text-white text-xl mr-4">
                            {{ $template['icon'] }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $template['title'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $template['description'] }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-eye mr-1"></i>
                            <span>Click to preview</span>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Responsive
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Tested
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">✨ Email System Features</h2>
                <p class="text-lg text-gray-600">Enterprise-grade email templates with advanced features</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-mobile-alt text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Mobile Optimized</h3>
                    <p class="text-gray-600">Perfect rendering on all devices and email clients</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-palette text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional Design</h3>
                    <p class="text-gray-600">Modern, branded templates that build trust</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Analytics Ready</h3>
                    <p class="text-gray-600">Built-in tracking for opens, clicks, and engagement</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-cog text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Personalized</h3>
                    <p class="text-gray-600">Dynamic content based on user preferences</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">🚀 HabibiStay Email System</h3>
                <p class="text-gray-400">Production-ready email templates for exceptional user experience</p>
                <div class="mt-4 flex justify-center space-x-6 text-sm text-gray-400">
                    <span>✅ 10 Email Templates</span>
                    <span>✅ Multi-channel Notifications</span>
                    <span>✅ Advanced Analytics</span>
                    <span>✅ A/B Testing</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDeviceMode = 'desktop';
        
        function setDeviceMode(mode) {
            currentDeviceMode = mode;
            
            // Update button styles
            document.querySelectorAll('.device-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'bg-blue-600');
                btn.classList.add('bg-gray-500', 'hover:bg-gray-600');
            });
            
            event.target.classList.remove('bg-gray-500', 'hover:bg-gray-600');
            event.target.classList.add('bg-blue-500', 'hover:bg-blue-600');
        }
        
        function previewEmail(templateKey) {
            const baseUrl = '{{ url("/email-preview") }}';
            const url = `${baseUrl}/${templateKey}?device=${currentDeviceMode}`;
            window.open(url, '_blank');
        }
        
        // Initialize desktop mode
        document.addEventListener('DOMContentLoaded', function() {
            setDeviceMode('desktop');
        });
    </script>
</body>
</html>
