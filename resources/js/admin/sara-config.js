/**
 * Sara AI Configuration Management
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const configForm = document.getElementById('sara-config-form');
    const temperatureInput = document.getElementById('temperature');
    const temperatureValue = document.getElementById('temperature-value');
    const resetButton = document.getElementById('reset-config');
    const exportButton = document.getElementById('export-config');
    const importButton = document.getElementById('import-config');
    const testMessage = document.getElementById('test-message');
    const sendTestButton = document.getElementById('send-test');
    const testChat = document.getElementById('test-chat');

    // Update temperature display value
    temperatureInput.addEventListener('input', function() {
        temperatureValue.textContent = this.value;
    });

    // Save configuration
    configForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(configForm);
        const saveBtn = configForm.querySelector('button[type="submit"]');
        const originalText = saveBtn.textContent;

        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;

        fetch('/admin/sara/config/save', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Configuration saved successfully', 'success');
            } else {
                showNotification('Error saving configuration', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving configuration', 'error');
        })
        .finally(() => {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
    });

    // Reset configuration
    resetButton.addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all settings to default values?')) {
            fetch('/admin/sara/config/reset', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    showNotification('Error resetting configuration', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error resetting configuration', 'error');
            });
        }
    });

    // Export configuration
    exportButton.addEventListener('click', function() {
        fetch('/admin/sara/config/export')
            .then(response => response.json())
            .then(data => {
                if (data.config) {
                    const configStr = JSON.stringify(data.config, null, 2);
                    const blob = new Blob([configStr], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `sara-config-${new Date().toISOString().split('T')[0]}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    showNotification('Error exporting configuration', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error exporting configuration', 'error');
            });
    });

    // Import configuration
    importButton.addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'application/json';

        input.onchange = e => {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = event => {
                try {
                    const config = JSON.parse(event.target.result);

                    const formData = new FormData();
                    formData.append('config', JSON.stringify(config));

                                            fetch('/admin/sara/config/import', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showNotification('Error importing configuration', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error importing configuration', 'error');
                    });
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    showNotification('Invalid configuration file', 'error');
                }
            };

            reader.readAsText(file);
        };

        input.click();
    });

    // Test chat functionality
    sendTestButton.addEventListener('click', function() {
        sendTestMessage();
    });

    testMessage.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendTestMessage();
        }
    });

    function sendTestMessage() {
        const message = testMessage.value.trim();
        if (!message) return;

        // Add user message to chat
        addChatMessage('user', message);
        testMessage.value = '';

        // Add typing indicator
        const typingId = addTypingIndicator();

        // Get form data for configuration
        const formData = new FormData(configForm);
        formData.append('message', message);

        // Send message to Sara
        fetch('/admin/sara/config/test-message', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            removeTypingIndicator(typingId);

            // Add Sara's response
            if (data.response) {
                addChatMessage('sara', data.response);
            } else {
                addChatMessage('sara', 'Sorry, there was an error processing your request.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            removeTypingIndicator(typingId);
            addChatMessage('sara', 'Sorry, there was an error processing your request.');
        });
    }

    function addChatMessage(sender, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}-message mb-4`;

        const messageContent = document.createElement('div');
        messageContent.className = sender === 'user' ? 
            'bg-blue-100 text-blue-800 p-3 rounded-lg ml-auto max-w-3/4' : 
            'bg-gray-100 text-gray-800 p-3 rounded-lg mr-auto max-w-3/4';

        const nameSpan = document.createElement('div');
        nameSpan.className = 'font-semibold text-xs mb-1';
        nameSpan.textContent = sender === 'user' ? 'You' : 'Sara AI';

        const textSpan = document.createElement('div');
        textSpan.textContent = message;

        messageContent.appendChild(nameSpan);
        messageContent.appendChild(textSpan);
        messageDiv.appendChild(messageContent);

        testChat.appendChild(messageDiv);
        testChat.scrollTop = testChat.scrollHeight;

        // Remove the initial placeholder if present
        const placeholder = testChat.querySelector('.text-center.text-gray-500');
        if (placeholder) {
            placeholder.remove();
        }
    }

    function addTypingIndicator() {
        const id = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = id;
        typingDiv.className = 'chat-message sara-message mb-4';

        const typingContent = document.createElement('div');
        typingContent.className = 'bg-gray-100 text-gray-800 p-3 rounded-lg mr-auto';

        const nameSpan = document.createElement('div');
        nameSpan.className = 'font-semibold text-xs mb-1';
        nameSpan.textContent = 'Sara AI';

        const typingAnimation = document.createElement('div');
        typingAnimation.className = 'typing-animation';
        typingAnimation.innerHTML = '<span></span><span></span><span></span>';

        typingContent.appendChild(nameSpan);
        typingContent.appendChild(typingAnimation);
        typingDiv.appendChild(typingContent);

        testChat.appendChild(typingDiv);
        testChat.scrollTop = testChat.scrollHeight;

        return id;
    }

    function removeTypingIndicator(id) {
        const typingDiv = document.getElementById(id);
        if (typingDiv) {
            typingDiv.remove();
        }
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-md ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
        notification.textContent = message;

        // Add to DOM
        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});
