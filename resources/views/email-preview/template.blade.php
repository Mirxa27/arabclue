<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Email Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .device-frame {
            transition: all 0.3s ease;
        }
        
        .device-desktop {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .device-tablet {
            max-width: 768px;
            margin: 0 auto;
        }
        
        .device-mobile {
            max-width: 375px;
            margin: 0 auto;
        }
        
        .email-container {
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }
        
        .email-preview {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .toolbar {
            background: #1f2937;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .device-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .zoom-btn {
            background: #374151;
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .zoom-btn:hover {
            background: #4b5563;
        }
        
        .email-content {
            transform-origin: top left;
            transition: transform 0.3s ease;
        }
        
        .responsive-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Responsive Indicator -->
    <div class="responsive-indicator" id="deviceIndicator">
        <i class="fas fa-desktop"></i>
        <span id="deviceText">Desktop View</span>
    </div>

    <!-- Navigation -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ $backUrl }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Templates
                    </a>
                    <div class="ml-6">
                        <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>
                        <p class="text-sm text-gray-500">Subject: {{ $subject }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Device Switcher -->
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button onclick="switchDevice('desktop')" class="device-btn px-3 py-1 rounded text-sm font-medium transition-colors bg-blue-500 text-white">
                            <i class="fas fa-desktop mr-1"></i>Desktop
                        </button>
                        <button onclick="switchDevice('tablet')" class="device-btn px-3 py-1 rounded text-sm font-medium transition-colors text-gray-600 hover:text-gray-900">
                            <i class="fas fa-tablet-alt mr-1"></i>Tablet
                        </button>
                        <button onclick="switchDevice('mobile')" class="device-btn px-3 py-1 rounded text-sm font-medium transition-colors text-gray-600 hover:text-gray-900">
                            <i class="fas fa-mobile-alt mr-1"></i>Mobile
                        </button>
                    </div>
                    
                    <!-- Zoom Controls -->
                    <div class="flex items-center space-x-2">
                        <button onclick="zoomOut()" class="zoom-btn bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <span id="zoomLevel" class="text-sm text-gray-600 min-w-[40px] text-center">100%</span>
                        <button onclick="zoomIn()" class="zoom-btn bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300">
                            <i class="fas fa-search-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Preview Container -->
    <div class="email-container">
        <div id="emailFrame" class="device-frame device-desktop">
            <div class="email-preview">
                <div class="toolbar">
                    <div class="device-indicator">
                        <i id="deviceIcon" class="fas fa-desktop"></i>
                        <span id="deviceName">Desktop</span>
                        <span class="text-gray-300 ml-2">•</span>
                        <span id="deviceDimensions" class="text-gray-300 ml-2">1200px</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-300">
                            <i class="fas fa-envelope mr-1"></i>
                            Email Preview
                        </div>
                        <div class="zoom-controls">
                            <button onclick="resetZoom()" class="zoom-btn">
                                <i class="fas fa-expand-arrows-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="emailContent" class="email-content">
                    {!! $html !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Features Info -->
    <div class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Responsive Design</h3>
                    <p class="text-sm text-gray-600">Optimized for all screen sizes and email clients</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-paint-brush text-blue-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Professional Styling</h3>
                    <p class="text-sm text-gray-600">Consistent branding and modern design elements</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-cogs text-purple-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Dynamic Content</h3>
                    <p class="text-sm text-gray-600">Personalized content based on user data</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentZoom = 1;
        let currentDevice = 'desktop';
        
        const deviceConfigs = {
            desktop: {
                class: 'device-desktop',
                icon: 'fas fa-desktop',
                name: 'Desktop',
                dimensions: '1200px',
                indicator: 'Desktop View'
            },
            tablet: {
                class: 'device-tablet',
                icon: 'fas fa-tablet-alt',
                name: 'Tablet',
                dimensions: '768px',
                indicator: 'Tablet View'
            },
            mobile: {
                class: 'device-mobile',
                icon: 'fas fa-mobile-alt',
                name: 'Mobile',
                dimensions: '375px',
                indicator: 'Mobile View'
            }
        };
        
        function switchDevice(device) {
            currentDevice = device;
            const config = deviceConfigs[device];
            const frame = document.getElementById('emailFrame');
            
            // Update frame class
            frame.className = `device-frame ${config.class}`;
            
            // Update toolbar
            document.getElementById('deviceIcon').className = config.icon;
            document.getElementById('deviceName').textContent = config.name;
            document.getElementById('deviceDimensions').textContent = config.dimensions;
            
            // Update responsive indicator
            document.getElementById('deviceIndicator').innerHTML = `
                <i class="${config.icon}"></i>
                <span id="deviceText">${config.indicator}</span>
            `;
            
            // Update device buttons
            document.querySelectorAll('.device-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('text-gray-600', 'hover:text-gray-900');
            });
            
            event.target.classList.remove('text-gray-600', 'hover:text-gray-900');
            event.target.classList.add('bg-blue-500', 'text-white');
            
            // Reset zoom when switching devices
            resetZoom();
        }
        
        function zoomIn() {
            if (currentZoom < 2) {
                currentZoom += 0.1;
                updateZoom();
            }
        }
        
        function zoomOut() {
            if (currentZoom > 0.5) {
                currentZoom -= 0.1;
                updateZoom();
            }
        }
        
        function resetZoom() {
            currentZoom = 1;
            updateZoom();
        }
        
        function updateZoom() {
            const content = document.getElementById('emailContent');
            content.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Check URL parameters for device mode
            const urlParams = new URLSearchParams(window.location.search);
            const deviceParam = urlParams.get('device');
            
            if (deviceParam && deviceConfigs[deviceParam]) {
                // Find and click the appropriate device button
                const deviceButtons = document.querySelectorAll('.device-btn');
                deviceButtons.forEach(btn => {
                    if (btn.textContent.toLowerCase().includes(deviceParam)) {
                        btn.click();
                    }
                });
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        switchDevice('desktop');
                        break;
                    case '2':
                        e.preventDefault();
                        switchDevice('tablet');
                        break;
                    case '3':
                        e.preventDefault();
                        switchDevice('mobile');
                        break;
                    case '0':
                        e.preventDefault();
                        resetZoom();
                        break;
                    case '=':
                    case '+':
                        e.preventDefault();
                        zoomIn();
                        break;
                    case '-':
                        e.preventDefault();
                        zoomOut();
                        break;
                }
            }
        });
    </script>
</body>
</html>
