@extends('layouts.admin')

@section('title', 'Sara AI Configuration')
@section('page-title', 'Sara AI Configuration')

@push('styles')
<style>
    .typing-animation {
        display: flex;
        align-items: center;
        column-gap: 6px;
    }

    .typing-animation span {
        height: 8px;
        width: 8px;
        background: #888;
        border-radius: 50%;
        display: block;
        opacity: 0.4;
    }

    .typing-animation span:nth-child(1) {
        animation: bounce 1s infinite;
    }

    .typing-animation span:nth-child(2) {
        animation: bounce 1s infinite 0.2s;
    }

    .typing-animation span:nth-child(3) {
        animation: bounce 1s infinite 0.4s;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
            opacity: 0.4;
        }
        50% {
            transform: translateY(-4px);
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white transition-all duration-500 transform translate-x-full ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);

        // Animate out after delay
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 5000);
    }
    document.addEventListener('DOMContentLoaded', function() {
        // API provider selection handling
        const apiProviderSelect = document.getElementById('api_provider');
        const aiModelSelect = document.getElementById('ai_model');
        const apiEndpointField = document.getElementById('api_endpoint');
        const apiEndpointContainer = document.getElementById('api_endpoint_container');

        if (apiProviderSelect) {
            apiProviderSelect.addEventListener('change', function() {
                // Show/hide model options based on selected provider
                const selectedProvider = this.value;

                // Hide all model option groups
                document.querySelectorAll('.provider-models').forEach(group => {
                    group.hidden = true;
                });

                // Show only the relevant model options
                const relevantGroup = document.querySelector(`.provider-${selectedProvider}`);
                if (relevantGroup) {
                    relevantGroup.hidden = false;

                    // Select the first option in the group if current selection is hidden
                    const firstOption = relevantGroup.querySelector('option');
                    if (firstOption && aiModelSelect.selectedOptions[0].parentElement.hidden) {
                        firstOption.selected = true;
                    }
                }

                // Show endpoint field for Azure and Custom providers
                if (apiEndpointContainer) {
                    apiEndpointContainer.classList.toggle('hidden', 
                        selectedProvider !== 'azure' && selectedProvider !== 'custom');

                    // Make endpoint required for Azure and Custom
                    if (apiEndpointField) {
                        apiEndpointField.required = 
                            selectedProvider === 'azure' || selectedProvider === 'custom';
                    }
                }

                // Show Azure deployment field only for Azure
                const azureDeploymentContainer = document.getElementById('azure_deployment_container');
                const azureDeploymentField = document.getElementById('azure_deployment');

                if (azureDeploymentContainer) {
                    azureDeploymentContainer.classList.toggle('hidden', selectedProvider !== 'azure');

                    // Make deployment field required for Azure
                    if (azureDeploymentField) {
                        azureDeploymentField.required = selectedProvider === 'azure';
                    }
                }
            });

            // Trigger change event on load to initialize correctly
            apiProviderSelect.dispatchEvent(new Event('change'));
        }

        // Temperature slider value display
        const temperatureSlider = document.getElementById('temperature');
        const temperatureValue = document.getElementById('temperature-value');

        if (temperatureSlider && temperatureValue) {
            temperatureSlider.addEventListener('input', function() {
                temperatureValue.textContent = this.value;
            });
        }

        // Form submission
        const configForm = document.getElementById('sara-config-form');
        if (configForm) {
            configForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';

                // Collect form data
                const formData = new FormData(this);

                // Send to server (AJAX call example)
                fetch('/admin/sara/config/save', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    // Show success message
                    if (data.success) {
                        showNotification('Configuration saved successfully!', 'success');
                    } else {
                        showNotification('Error saving configuration', 'error');
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    // Restore button state and show error
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    showNotification('Error saving configuration', 'error');
                    console.error(error);
                });
            });
        }

        // Test API Connection
        const testApiBtn = document.getElementById('test-api-connection');
        const apiStatusEl = document.getElementById('api-connection-status');

        if (testApiBtn && apiStatusEl) {
            testApiBtn.addEventListener('click', function() {
                const apiKey = document.getElementById('api_key').value;
                const apiProvider = document.getElementById('api_provider').value;
                const apiEndpoint = document.getElementById('api_endpoint').value;

                if (!apiKey) {
                    apiStatusEl.textContent = 'Please enter an API key';
                    apiStatusEl.className = 'ml-2 text-sm text-red-500';
                    return;
                }

                // Show loading state
                testApiBtn.disabled = true;
                const originalText = testApiBtn.textContent;
                testApiBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';
                apiStatusEl.textContent = 'Testing connection...';
                apiStatusEl.className = 'ml-2 text-sm text-gray-500';

                // Test API connection
                fetch('/admin/sara/config/test-api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        api_key: apiKey,
                        api_provider: apiProvider,
                        api_endpoint: apiEndpoint
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Restore button state
                    testApiBtn.disabled = false;
                    testApiBtn.textContent = originalText;

                    // Show result
                    if (data.success) {
                        apiStatusEl.textContent = 'Connection successful! ✓';
                        apiStatusEl.className = 'ml-2 text-sm text-green-500';
                    } else {
                        apiStatusEl.textContent = data.message || 'Connection failed! ✗';
                        apiStatusEl.className = 'ml-2 text-sm text-red-500';
                    }
                })
                .catch(error => {
                    // Restore button state and show error
                    testApiBtn.disabled = false;
                    testApiBtn.textContent = originalText;
                    apiStatusEl.textContent = 'Connection failed! ✗';
                    apiStatusEl.className = 'ml-2 text-sm text-red-500';
                    console.error(error);
                });
            });
        }

        // Test chat functionality
        const testMessage = document.getElementById('test-message');
        const sendTestBtn = document.getElementById('send-test');
        const testChat = document.getElementById('test-chat');

        if (testMessage && sendTestBtn && testChat) {
            sendTestBtn.addEventListener('click', function() {
                const message = testMessage.value.trim();

                if (!message) return;

                // Add user message to chat
                appendChatMessage('user', message);
                testMessage.value = '';

                // Show typing indicator
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'flex items-start mb-4';
                typingIndicator.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-brand-blue flex items-center justify-center text-white mr-2 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="bg-gray-100 rounded-lg py-2 px-3 max-w-md">
                        <div class="typing-animation">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                `;
                typingIndicator.id = 'typing-indicator';
                testChat.appendChild(typingIndicator);
                testChat.scrollTop = testChat.scrollHeight;

                // Get form data to use for testing
                const aiModel = document.getElementById('ai_model').value;
                const systemPrompt = document.getElementById('system_prompt').value;
                const temperature = document.getElementById('temperature').value;
                const apiProvider = document.getElementById('api_provider').value;

                // Send to server to get real AI response
                fetch('/admin/sara/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        message: message,
                        ai_model: aiModel,
                        system_prompt: systemPrompt,
                        temperature: temperature,
                        api_provider: apiProvider
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Remove typing indicator
                    const indicator = document.getElementById('typing-indicator');
                    if (indicator) indicator.remove();

                    if (data.success) {
                        // Add AI response to chat
                        const responseDiv = document.createElement('div');
                        responseDiv.className = 'flex mb-3';
                        responseDiv.innerHTML = `
                            <div class="bg-gray-100 rounded-lg py-2 px-4 max-w-xs">
                                ${formatAiResponse(data.response || 'Sorry, I encountered an error. Please check your API configuration.')}
                            </div>
                        `;
                        testChat.appendChild(responseDiv);
                        testChat.scrollTop = testChat.scrollHeight;
                    } else {
                        // Show error message
                        appendChatMessage('ai', 'Sorry, I encountered an error: ' + (data.message || 'Unknown error'));
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    // Remove typing indicator
                    const indicator = document.getElementById('typing-indicator');
                    if (indicator) indicator.remove();

                    // Show error message
                    appendChatMessage('ai', 'Sorry, I encountered a technical error. Please check your API configuration.');
                    console.error(error);
                });

                // Legacy fallback simulation
                if (false) {
                    setTimeout(function() {
                    // Remove typing indicator
                    const indicator = document.getElementById('typing-indicator');
                    if (indicator) indicator.remove();

                    // Generate response based on message and settings
                    let response;
                    if (message.toLowerCase().includes('hello') || message.toLowerCase().includes('hi')) {
                        response = 'Hello! I\'m Sara, your AI assistant for HabibiStay. How can I help you find accommodations in Riyadh?';
                    } else if (message.toLowerCase().includes('property') || message.toLowerCase().includes('stay') || message.toLowerCase().includes('accommodation')) {
                        response = 'I can help you find the perfect property! We have several luxury options in Riyadh, including villas, apartments, and family homes. Could you tell me your preferred area and budget?';
                    } else if (message.toLowerCase().includes('price') || message.toLowerCase().includes('cost') || message.toLowerCase().includes('budget')) {
                        response = 'Our properties range from 350 SAR per night for standard apartments to 1,500+ SAR for luxury villas. May I know your budget range to suggest suitable options?';
                    } else {
                        response = 'Thank you for your message. I\'d be happy to help with your inquiry about HabibiStay accommodations in Riyadh. Could you provide more details about what you\'re looking for?';
                    }

                    // Add AI response to chat
                    appendChatMessage('ai', response);
                }, 1500);
            });

            // Send message on Enter key press
            testMessage.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendTestBtn.click();
                }
            });
        }

        // Reset configuration to defaults
        const resetConfigBtn = document.getElementById('reset-config');
        if (resetConfigBtn) {
            resetConfigBtn.addEventListener('click', function(e) {
                if (confirm('Are you sure you want to reset all Sara AI settings to default values? This action cannot be undone.')) {
                    fetch('/admin/sara/config/reset', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Configuration reset successfully! Reloading page...', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showNotification('Error resetting configuration', 'error');
                            console.error(data.message);
                        }
                    })
                    .catch(error => {
                        showNotification('Error resetting configuration', 'error');
                        console.error(error);
                    });
                }
            });
        }

        // Helper function to append chat messages
        function appendChatMessage(type, message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start mb-4';

            if (type === 'user') {
                messageDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 mr-2 flex-shrink-0 order-2 ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="bg-brand-blue text-white rounded-lg py-2 px-3 max-w-md order-1 ml-auto">
                        ${message}
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-brand-blue flex items-center justify-center text-white mr-2 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="bg-gray-100 rounded-lg py-2 px-3 max-w-md">
                        ${message}
                    </div>
                `;
            }

            testChat.appendChild(messageDiv);
            testChat.scrollTop = testChat.scrollHeight;
        }

        // Reset form to defaults
        const resetBtn = document.getElementById('reset-config');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to reset all settings to default values?')) {
                    // Reset form values to defaults
                    document.getElementById('ai_model').value = 'gpt-4o-mini';
                    document.getElementById('system_prompt').value = 'You are Sara, HabibiStay\'s friendly AI assistant. You help guests find perfect accommodations in Riyadh, Saudi Arabia. You are knowledgeable about local culture, speak multiple languages, and provide personalized recommendations. Always be helpful, professional, and culturally sensitive.\n\nKey responsibilities:\n- Help guests search for properties\n- Provide local recommendations\n- Assist with booking process\n- Answer questions about properties and amenities\n- Offer customer support\n\nAlways maintain a warm, welcoming tone that reflects Saudi hospitality.';
                    document.getElementById('temperature').value = '0.7';
                    document.getElementById('temperature-value').textContent = '0.7';
                    document.getElementById('max_tokens').value = '500';
                    document.getElementById('greeting_message').value = 'مرحباً! أنا سارة، مساعدتك الذكية في هبيبي ستاي. كيف يمكنني مساعدتك في العثور على الإقامة المثالية في الرياض؟\n\nHello! I\'m Sara, your AI assistant at HabibiStay. How can I help you find the perfect stay in Riyadh?';
                    document.getElementById('conversation_timeout').value = '30';

                    // Reset checkboxes
                    document.getElementById('voice_enabled').checked = true;
                    document.getElementById('multilingual_enabled').checked = true;

                    // Reset featured properties
                    document.getElementById('featured_1').checked = true;
                    document.getElementById('featured_2').checked = true;
                    document.getElementById('featured_3').checked = false;

                    showNotification('Settings reset to defaults', 'success');
                }
            });
        }

        // Export and Import functionality
        const exportBtn = document.getElementById('export-config');
        const importBtn = document.getElementById('import-config');

        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                // Collect form data
                const formData = new FormData(document.getElementById('sara-config-form'));
                const configData = {};

                for (const [key, value] of formData.entries()) {
                    if (configData[key]) {
                        if (!Array.isArray(configData[key])) {
                            configData[key] = [configData[key]];
                        }
                        configData[key].push(value);
                    } else {
                        configData[key] = value;
                    }
                }

                // Create download file
                const dataStr = JSON.stringify(configData, null, 2);
                const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);

                // Create download link
                const exportFileDefaultName = 'sara-config-' + new Date().toISOString().split('T')[0] + '.json';
                const linkElement = document.createElement('a');
                linkElement.setAttribute('href', dataUri);
                linkElement.setAttribute('download', exportFileDefaultName);
                linkElement.click();

                showNotification('Configuration exported successfully', 'success');
            });
        }

        if (importBtn) {
            importBtn.addEventListener('click', function() {
                // Create file input
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'application/json';

                fileInput.addEventListener('change', function() {
                    const file = fileInput.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const config = JSON.parse(e.target.result);

                            // Apply imported values to form
                            for (const [key, value] of Object.entries(config)) {
                                const element = document.getElementById(key) || document.getElementsByName(key)[0];

                                if (!element) continue;

                                if (element.type === 'checkbox') {
                                    element.checked = value === 'on' || value === true;
                                } else if (element.type === 'select-one') {
                                    element.value = value;
                                } else if (element.type === 'range') {
                                    element.value = value;
                                    document.getElementById(key + '-value').textContent = value;
                                } else {
                                    element.value = value;
                                }
                            }

                            showNotification('Configuration imported successfully', 'success');
                        } catch (error) {
                            console.error('Error importing configuration:', error);
                            showNotification('Error importing configuration', 'error');
                        }
                    };
                    reader.readAsText(file);
                });

                fileInput.click();
            });
        }

        // Initialize charts for statistics
        initCharts();
    });

    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md transition-opacity duration-300 ${
            type === 'success' ? 'bg-green-50 border-l-4 border-green-500 text-green-700' : 
            'bg-red-50 border-l-4 border-red-500 text-red-700'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${
                        type === 'success' ?
                        '<svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                        '<svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                    }
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button class="inline-flex rounded-md p-1.5 text-gray-500 hover:bg-gray-100 focus:outline-none">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                            <!-- Advanced Settings -->
                            <div class="mt-6 border-t border-gray-200 pt-4">
                <h4 class="text-md font-medium text-gray-900 mb-3">Advanced Settings</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="context_length" class="block text-sm font-medium text-gray-700 mb-1">Conversation Context Length</label>
                        <select id="context_length" name="context_length" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                            <option value="4" {{ ($config['context_length'] ?? 4) == 4 ? 'selected' : '' }}>4 messages</option>
                            <option value="8" {{ ($config['context_length'] ?? 4) == 8 ? 'selected' : '' }}>8 messages</option>
                            <option value="12" {{ ($config['context_length'] ?? 4) == 12 ? 'selected' : '' }}>12 messages</option>
                            <option value="16" {{ ($config['context_length'] ?? 4) == 16 ? 'selected' : '' }}>16 messages</option>
                            <option value="20" {{ ($config['context_length'] ?? 4) == 20 ? 'selected' : '' }}>20 messages</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Number of previous messages to include in each new request.</p>
                    </div>

                    <div>
                        <label for="request_timeout" class="block text-sm font-medium text-gray-700 mb-1">API Request Timeout (seconds)</label>
                        <input type="number" id="request_timeout" name="request_timeout" value="{{ $config['request_timeout'] ?? 30 }}" min="5" max="120" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">Maximum time to wait for AI responses before timing out.</p>
                    </div>
                </div>
                            </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto dismiss after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);

        // Dismiss on click
        notification.querySelector('button').addEventListener('click', () => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        });
    }

    // Initialize charts for statistics
    function initCharts() {
        // Language usage chart
        const langCtx = document.getElementById('language-chart');
        if (langCtx) {
            new Chart(langCtx, {
                type: 'pie',
                data: {
                    labels: ['English', 'Arabic', 'French', 'German', 'Other'],
                    datasets: [{
                        data: [45, 30, 12, 8, 5],
                        backgroundColor: [
                            '#3B82F6', // blue
                            '#10B981', // green
                            '#F59E0B', // amber
                            '#EF4444', // red
                            '#8B5CF6'  // purple
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Usage trend chart
        const trendCtx = document.getElementById('usage-trend-chart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Jun 2', 'Jun 3', 'Jun 4', 'Jun 5', 'Jun 6', 'Jun 7', 'Jun 8', 'Jun 9'],
                    datasets: [{
                        label: 'Conversations',
                        data: [42, 55, 49, 48, 38, 65, 57, 63],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    }
</script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Configuration Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">AI Model Configuration</h3>
            <p class="mt-1 text-sm text-gray-500">Configure Sara's behavior, personality, and AI model settings.</p>
        </div>
        
        <form id="sara-config-form" class="p-6 space-y-6">
            @csrf
            
            <!-- AI Model Selection -->
            <div>
                <label for="ai_model" class="block text-sm font-medium text-gray-700">AI Model</label>
                <select id="ai_model" name="ai_model" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <optgroup label="OpenAI Models" class="provider-models provider-openai">
                        <option value="gpt-4o-mini" {{ ($config['ai_model'] ?? 'gpt-4o-mini') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Recommended)</option>
                        <option value="gpt-4o" {{ ($config['ai_model'] ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                        <option value="gpt-4" {{ ($config['ai_model'] ?? '') == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                        <option value="gpt-3.5-turbo" {{ ($config['ai_model'] ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                    </optgroup>
                    <optgroup label="Anthropic Models" class="provider-models provider-anthropic">
                        <option value="claude-3-opus" {{ ($config['ai_model'] ?? '') == 'claude-3-opus' ? 'selected' : '' }}>Claude 3 Opus</option>
                        <option value="claude-3-sonnet" {{ ($config['ai_model'] ?? '') == 'claude-3-sonnet' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                        <option value="claude-3-haiku" {{ ($config['ai_model'] ?? '') == 'claude-3-haiku' ? 'selected' : '' }}>Claude 3 Haiku</option>
                    </optgroup>
                    <optgroup label="Azure Models" class="provider-models provider-azure">
                        <option value="azure-gpt-4" {{ ($config['ai_model'] ?? '') == 'azure-gpt-4' ? 'selected' : '' }}>Azure GPT-4</option>
                        <option value="azure-gpt-35-turbo" {{ ($config['ai_model'] ?? '') == 'azure-gpt-35-turbo' ? 'selected' : '' }}>Azure GPT-3.5 Turbo</option>
                    </optgroup>
                </select>
                <p class="mt-2 text-sm text-gray-500">Choose the AI model that powers Sara's responses.</p>
            </div>

            <!-- System Prompt -->
            <div>
                <label for="system_prompt" class="block text-sm font-medium text-gray-700">System Prompt</label>
                <textarea id="system_prompt" name="system_prompt" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm" placeholder="Enter Sara's system prompt...">You are Sara, HabibiStay's friendly AI assistant. You help guests find perfect accommodations in Riyadh, Saudi Arabia. You are knowledgeable about local culture, speak multiple languages, and provide personalized recommendations. Always be helpful, professional, and culturally sensitive.

Key responsibilities:
- Help guests search for properties
- Provide local recommendations
- Assist with booking process
- Answer questions about properties and amenities
- Offer customer support

Always maintain a warm, welcoming tone that reflects Saudi hospitality.</textarea>
                <p class="mt-2 text-sm text-gray-500">Define Sara's personality, role, and behavior guidelines.</p>
            </div>

            <!-- Temperature Setting -->
            <div>
                <label for="temperature" class="block text-sm font-medium text-gray-700">Response Creativity (Temperature)</label>
                <div class="mt-1 flex items-center space-x-4">
                    <input type="range" id="temperature" name="temperature" min="0" max="1" step="0.1" value="0.7" class="flex-1">
                    <span id="temperature-value" class="text-sm text-gray-500 w-12">0.7</span>
                </div>
                <div class="mt-1 flex justify-between text-xs text-gray-400">
                    <span>Conservative</span>
                    <span>Creative</span>
                </div>
                <p class="mt-2 text-sm text-gray-500">Lower values make responses more focused and deterministic, higher values more creative and varied.</p>
            </div>

            <!-- Max Tokens -->
            <div>
                <label for="max_tokens" class="block text-sm font-medium text-gray-700">Maximum Response Length</label>
                <input type="number" id="max_tokens" name="max_tokens" value="500" min="50" max="2000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                <p class="mt-2 text-sm text-gray-500">Maximum number of tokens (words) in Sara's responses.</p>
            </div>

            <!-- Featured Properties -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Featured Properties to Show</label>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center">
                        <input id="featured_1" name="featured_properties[]" value="1" type="checkbox" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        <label for="featured_1" class="ml-2 text-sm text-gray-700">Luxury Villa - Al Rajhi</label>
                    </div>

                        <div class="mb-4">
                            <label for="context_length" class="block text-sm font-medium text-gray-700 mb-1">Conversation Context Length</label>
                            <select id="context_length" name="context_length" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                                <option value="4" {{ ($config['context_length'] ?? 4) == 4 ? 'selected' : '' }}>4 messages</option>
                                <option value="8" {{ ($config['context_length'] ?? 4) == 8 ? 'selected' : '' }}>8 messages</option>
                                <option value="12" {{ ($config['context_length'] ?? 4) == 12 ? 'selected' : '' }}>12 messages</option>
                                <option value="16" {{ ($config['context_length'] ?? 4) == 16 ? 'selected' : '' }}>16 messages</option>
                                <option value="20" {{ ($config['context_length'] ?? 4) == 20 ? 'selected' : '' }}>20 messages</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Number of previous messages to include in each new request. Higher values provide more context but increase costs.</p>
                        </div>
                    <div class="flex items-center">
                        <input id="featured_2" name="featured_properties[]" value="2" type="checkbox" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        <label for="featured_2" class="ml-2 text-sm text-gray-700">Executive Apartment - Olaya</label>
                    </div>
                    <div class="flex items-center">
                        <input id="featured_3" name="featured_properties[]" value="3" type="checkbox" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        <label for="featured_3" class="ml-2 text-sm text-gray-700">Family Home - Al Malqa</label>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">Select which properties Sara should showcase when starting conversations.</p>
            </div>

            <!-- Voice Settings -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Voice & Speech Settings</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="voice_enabled" class="flex items-center">
                            <input type="checkbox" id="voice_enabled" name="voice_enabled" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Enable Voice Recognition</span>
                        </label>
                    </div>
                    
                    <div>
                        <label for="voice_language" class="block text-sm font-medium text-gray-700">Voice Language</label>
                        <select id="voice_language" name="voice_language" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                            <option value="en-US">English (US)</option>
                            <option value="ar-SA">Arabic (Saudi Arabia)</option>
                            <option value="en-GB">English (UK)</option>
                            <option value="fr-FR">French</option>
                            <option value="de-DE">German</option>
                            <option value="es-ES">Spanish</option>
                            <option value="ru-RU">Russian</option>
                            <option value="zh-CN">Chinese (Simplified)</option>
                            <option value="google" {{ ($config['api_provider'] ?? '') === 'google' ? 'selected' : '' }}>Google AI</option>
                            <option value="custom" {{ ($config['api_provider'] ?? '') === 'custom' ? 'selected' : '' }}>Custom API</option>
                        </select>
                    </div>
                </div>

                <!-- Multilingual Support -->
                <div class="mt-4">
                    <label for="multilingual_enabled" class="flex items-center">
                        <input type="checkbox" id="multilingual_enabled" name="multilingual_enabled" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Enable Multilingual Support</span>
                    </label>
                    <p class="mt-2 text-sm text-gray-500">When enabled, Sara will automatically detect and respond in the user's language.</p>

                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="en" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">English</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="ar" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Arabic</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="fr" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">French</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="de" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">German</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="es" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Spanish</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="ru" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Russian</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="zh" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Chinese</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="supported_languages[]" value="ja" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Japanese</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Conversation Settings -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Conversation Settings</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="greeting_message" class="block text-sm font-medium text-gray-700">Greeting Message</label>
                        <textarea id="greeting_message" name="greeting_message" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">مرحباً! أنا سارة، مساعدتك الذكية في هبيبي ستاي. كيف يمكنني مساعدتك في العثور على الإقامة المثالية في الرياض؟

Hello! I'm Sara, your AI assistant at HabibiStay. How can I help you find the perfect stay in Riyadh?</textarea>
                    </div>
                    
                    <div>
                        <label for="conversation_timeout" class="block text-sm font-medium text-gray-700">Conversation Timeout (minutes)</label>
                        <input type="number" id="conversation_timeout" name="conversation_timeout" value="30" min="5" max="120" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    </div>
                </div>
            </div>

            <!-- API Integration Settings -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">API Integration Settings</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="api_provider" class="block text-sm font-medium text-gray-700">API Provider</label>
                        <select id="api_provider" name="api_provider" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                            <option value="openai" {{ ($config['api_provider'] ?? 'openai') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                            <option value="anthropic" {{ ($config['api_provider'] ?? '') == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                            <option value="azure" {{ ($config['api_provider'] ?? '') == 'azure' ? 'selected' : '' }}>Azure OpenAI</option>
                            <option value="custom" {{ ($config['api_provider'] ?? '') == 'custom' ? 'selected' : '' }}>Custom Provider</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">Select the AI service provider.</p>
                    </div>

                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                        <input type="password" id="api_key" name="api_key" placeholder="Enter your API key" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm" value="{{ $config['api_key'] ?? '' }}">
                        <p class="mt-2 text-sm text-gray-500">Your API key will be encrypted before storing.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" id="test-api-connection" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Test API Connection
                    </button>
                    <span id="api-connection-status" class="ml-2 text-sm"></span>
                </div>
            </div>

            <!-- API Configuration -->
            <div id="api_endpoint_container" class="mt-4">
                <label for="api_endpoint" class="block text-sm font-medium text-gray-700">API Endpoint URL</label>
                <input type="url" id="api_endpoint" name="api_endpoint" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm" placeholder="https://api.openai.com/v1/chat/completions">
                <p class="mt-1 text-sm text-gray-500">Custom endpoint URL (required for Azure and custom providers)</p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-between items-center">
                <div class="space-x-2">
                    <button type="button" id="reset-config" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Reset to Defaults
                    </button>
                    <button type="button" id="export-config" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Export Config
                    </button>
                    <button type="button" id="import-config" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Import Config
                    </button>
                </div>
                <button type="submit" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Test Sara -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Test Sara AI</h3>
            <p class="mt-1 text-sm text-gray-500">Test Sara's responses with your current configuration.</p>
        </div>
        
        <div class="p-6">
            <div id="test-chat" class="border border-gray-200 rounded-lg p-4 h-64 overflow-y-auto mb-4 bg-gray-50">
                <div class="text-center text-gray-500 text-sm">Start a conversation to test Sara...</div>
            </div>
            
            <div class="flex space-x-2">
                <input type="text" id="test-message" placeholder="Type a message to test Sara..." class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                <button id="send-test" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Send
                </button>
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Usage Statistics</h3>
        </div>
        
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-medium text-gray-700">Statistics Overview</h4>
                <div class="flex space-x-2">
                    <select id="stats-period" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    <button id="refresh-stats" class="text-sm bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>

                                            <div id="azure_deployment_container" class="mt-4 hidden">
                                                <label for="azure_deployment" class="block text-sm font-medium text-gray-700 mb-1">Azure Deployment Name</label>
                                                <input type="text" name="azure_deployment" id="azure_deployment" class="mt-1 focus:ring-brand-blue focus:border-brand-blue block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $config['azure_deployment'] ?? '' }}" placeholder="your-deployment-name">
                                                <p class="mt-1 text-sm text-gray-500">The deployment name for your Azure OpenAI resource.</p>
                                            </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center bg-gray-50 p-4 rounded-lg">
                    <div class="text-gray-500 text-sm mb-1">Total Conversations</div>
                    <div class="text-2xl font-bold text-brand-blue" id="total-conversations">1,247</div>
                    <div class="text-sm text-gray-500">Total Conversations</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-brand-blue" id="avg-response-time">1.2s</div>
                    <div class="text-sm text-gray-500">Avg Response Time</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-brand-blue" id="satisfaction-rate">94%</div>
                    <div class="text-sm text-gray-500">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        // Initialize usage statistics chart
        const usageChart = document.getElementById('usage-chart');
        if (usageChart) {
            const usageData = @json($usageData ?? []);

            new Chart(usageChart, {
                type: 'line',
                data: {
                    labels: usageData.map(item => item.date),
                    datasets: [{
                        label: 'API Requests',
                        data: usageData.map(item => item.count),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    // Temperature slider
    const temperatureSlider = document.getElementById('temperature');
    const temperatureValue = document.getElementById('temperature-value');
    
    temperatureSlider.addEventListener('input', function() {
        temperatureValue.textContent = this.value;
    });

    // Form submission
    document.getElementById('sara-config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/api/v1/admin/sara/config', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Configuration saved successfully!');
            } else {
                alert('Error saving configuration: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving configuration');
        });
    });

    // Test chat functionality
    document.getElementById('send-test').addEventListener('click', sendTestMessage);
    document.getElementById('test-message').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendTestMessage();
        }
    });

    function sendTestMessage() {
        const messageInput = document.getElementById('test-message');
        const message = messageInput.value.trim();

        if (!message) return;

        // Add user message to chat
        const chatContainer = document.getElementById('test-chat');
        chatContainer.innerHTML += `
            <div class="flex justify-end mb-3">
                <div class="bg-brand-blue text-white rounded-lg py-2 px-4 max-w-3/4">
                    ${message}
                </div>
            </div>
        `;

        // Clear input
        messageInput.value = '';
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Show typing indicator
        chatContainer.innerHTML += `
            <div id="typing-indicator" class="flex mb-3">
                <div class="bg-gray-200 rounded-lg py-2 px-4 max-w-3/4">
                    <div class="flex space-x-1">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>
        `;

        // Get current configuration from form
        const formData = new FormData(document.getElementById('sara-config-form'));
        formData.append('message', message);

        // Send test request
        fetch('/api/v1/admin/sara/test', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            document.getElementById('typing-indicator').remove();

            // Add AI response to chat
            chatContainer.innerHTML += `
                <div class="flex mb-3">
                    <div class="bg-gray-200 rounded-lg py-2 px-4 max-w-3/4">
                        ${data.response}
                    </div>
                </div>
            `;

            chatContainer.scrollTop = chatContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
            // Remove typing indicator
            document.getElementById('typing-indicator').remove();

            // Add error message
            chatContainer.innerHTML += `
                <div class="flex mb-3">
                    <div class="bg-red-100 text-red-800 rounded-lg py-2 px-4 max-w-3/4">
                        Error processing your request. Please try again.
                    </div>
                </div>
            `;

            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    }
    document.getElementById('test-message').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            sendTestMessage();
        }
    });

    function sendTestMessage() {
        const messageInput = document.getElementById('test-message');
        const message = messageInput.value.trim();

        if (!message) return;

        // Add user message to chat
        const chatContainer = document.getElementById('test-chat');
        if (chatContainer.querySelector('.text-center')) {
            chatContainer.innerHTML = '';
        }

        chatContainer.innerHTML += `
            <div class="flex justify-end mb-3">
                <div class="bg-brand-blue text-white rounded-lg py-2 px-4 max-w-xs">
                    ${escapeHtml(message)}
                </div>
            </div>
        `;

        // Clear input
        messageInput.value = '';

        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Show typing indicator
        chatContainer.innerHTML += `
            <div id="typing-indicator" class="flex mb-3">
                <div class="bg-gray-200 text-gray-700 rounded-lg py-2 px-4 max-w-xs">
                    <div class="typing-animation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div id="api_endpoint_container" class="mt-4 hidden">
                    <label for="api_endpoint" class="block text-sm font-medium text-gray-700">API Endpoint</label>
                    <input type="text" id="api_endpoint" name="api_endpoint" placeholder="https://your-resource.openai.azure.com/" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>

                <div id="azure_deployment_container" class="mt-4 hidden">
                    <label for="azure_deployment" class="block text-sm font-medium text-gray-700">Azure Deployment Name</label>
                    <input type="text" id="azure_deployment" name="azure_deployment" placeholder="your-deployment-name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    <p class="mt-2 text-sm text-gray-500">The deployment name for your Azure OpenAI resource.</p>
                </div>
            </div>
        `;

        // Get current config values
        const formData = new FormData(document.getElementById('sara-config-form'));
        const config = {
            model: formData.get('ai_model'),
            system_prompt: formData.get('system_prompt'),
            temperature: formData.get('temperature'),
            max_tokens: formData.get('max_tokens')
        };

        // Send to test API
        fetch('/api/v1/admin/sara/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                config: config
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            document.getElementById('typing-indicator').remove();

            // Add AI response
            chatContainer.innerHTML += `
                <div class="flex mb-3">
                    <div class="bg-gray-200 text-gray-700 rounded-lg py-2 px-4 max-w-xs">
                        ${formatAiResponse(data.response)}
                    </div>
                </div>
            `;

            // Scroll to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);

            // Remove typing indicator
            document.getElementById('typing-indicator').remove();

            // Show error message
            chatContainer.innerHTML += `
                <div class="flex mb-3">
                    <div class="bg-red-100 text-red-700 rounded-lg py-2 px-4 max-w-xs">
                        Error: Unable to get response from Sara.
                    </div>
                </div>
            `;

            // Scroll to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatAiResponse(text) {
        // Convert URLs to links
        text = text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-blue-600 hover:underline">$1</a>');

        // Convert line breaks to <br>
        text = text.replace(/\n/g, '<br>');

        return text;
    }
    document.getElementById('test-message').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendTestMessage();
        }
    });

    function sendTestMessage() {
        const messageInput = document.getElementById('test-message');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        const chatContainer = document.getElementById('test-chat');
        
        // Add user message
        addMessageToChat('You', message, 'user');
        messageInput.value = '';
        
        // Add loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'text-gray-500 text-sm italic';
        loadingDiv.textContent = 'Sara is typing...';
        chatContainer.appendChild(loadingDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        // Send to Sara
        fetch('/api/v1/admin/sara/test-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            chatContainer.removeChild(loadingDiv);
            if (data.success) {
                addMessageToChat('Sara', data.response, 'assistant');
            } else {
                addMessageToChat('System', 'Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            chatContainer.removeChild(loadingDiv);
            addMessageToChat('System', 'Error connecting to Sara', 'error');
        });
    }


    // Load current configuration
    loadCurrentConfig();
    
    function loadCurrentConfig() {
        fetch('/admin/sara/config/get')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const config = data.config;
                    document.getElementById('ai_model').value = config.ai_model || 'gpt-4o-mini';
                    document.getElementById('system_prompt').value = config.system_prompt || '';
                    document.getElementById('temperature').value = config.temperature || 0.7;
                    document.getElementById('temperature-value').textContent = config.temperature || 0.7;
                    document.getElementById('max_tokens').value = config.max_tokens || 500;
                    document.getElementById('voice_enabled').checked = config.voice_enabled !== false;
                    document.getElementById('voice_language').value = config.voice_language || 'en-US';
                    document.getElementById('greeting_message').value = config.greeting_message || '';
                    document.getElementById('conversation_timeout').value = config.conversation_timeout || 30;
                }
            })
            .catch(error => console.error('Error loading config:', error));
    }
});
</script>
@endpush
