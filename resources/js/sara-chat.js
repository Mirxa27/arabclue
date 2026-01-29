/**
 * Sara Chat - HabibiStay AI Assistant
 */

class SaraChat {
    constructor(config = {}) {
        this.config = {
            apiEndpoint: '/api/sara/chat',
            featuredPropertiesEndpoint: '/api/properties/featured',
            enableVoice: true,
            enableButtons: true,
            interfaceStyle: 'floating', // floating, embedded, fullscreen
            primaryColor: '#2957c3',
            initialGreeting: 'Hi there! I\'m Sara, your HabibiStay assistant. How can I help you find the perfect stay today?',
            ...config
        };

        this.conversation = {
            id: null,
            messages: []
        };

        this.voiceRecognition = null;
        this.isListening = false;
        this.isRecording = false;
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.currentAudioElement = null;
        this.isPlayingAudio = false;

        this.init();
    }

    /**
     * Initialize the chat interface
     */
    init() {
        // Create chat interface if not embedded
        if (this.config.interfaceStyle === 'floating') {
            this.createFloatingInterface();
        } else if (this.config.interfaceStyle === 'fullscreen' && this.isMobileDevice()) {
            this.createFullscreenInterface();
        }

        // Initialize voice recognition if available and enabled
        if (this.config.enableVoice && 'webkitSpeechRecognition' in window) {
            this.initVoiceRecognition();
        }

        // Set primary color CSS variables
        document.documentElement.style.setProperty('--sara-primary-color', this.config.primaryColor);

        // Start with initial greeting if configured
        if (this.config.initialGreeting && this.isInterfaceVisible()) {
            setTimeout(() => {
                this.addSaraMessage(this.config.initialGreeting);
                this.loadFeaturedProperties();
            }, 500);
        }
    }

    /**
     * Create a floating chat bubble interface
     */
    createFloatingInterface() {
        // Create the chat bubble
        const chatBubble = document.createElement('div');
        chatBubble.className = 'sara-chat-bubble';
        chatBubble.innerHTML = `
            <div class="sara-bubble-icon">
                <span>S</span>
            </div>
            <div class="sara-notification-dot hidden"></div>
        `;
        document.body.appendChild(chatBubble);

        // Create the chat panel (initially hidden)
        const chatPanel = document.createElement('div');
        chatPanel.className = 'sara-chat-panel hidden';
        chatPanel.innerHTML = `
            <div class="sara-chat-header">
                <div class="sara-chat-title">
                    <div class="sara-avatar">S</div>
                    <div class="sara-info">
                        <h3>Sara</h3>
                        <p>HabibiStay Assistant</p>
                    </div>
                </div>
                <div class="sara-chat-actions">
                    <button class="sara-voice-toggle" title="Toggle Voice Input">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                    <button class="sara-close" title="Close Chat">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="sara-chat-messages"></div>
            <div class="sara-button-interface hidden"></div>
            <div class="sara-chat-input">
                <input type="text" placeholder="Type your message..." />
                <button class="sara-voice-input" title="Voice Input">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>
                <button class="sara-send-message" title="Send Message">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(chatPanel);

        // Add event listeners
        chatBubble.addEventListener('click', () => this.toggleChatPanel());
        chatPanel.querySelector('.sara-close').addEventListener('click', () => this.toggleChatPanel());
        chatPanel.querySelector('.sara-send-message').addEventListener('click', () => this.sendMessage());
        chatPanel.querySelector('.sara-voice-toggle').addEventListener('click', () => this.toggleVoiceInput());
        chatPanel.querySelector('.sara-voice-input').addEventListener('click', () => this.startVoiceInput());

        const inputField = chatPanel.querySelector('.sara-chat-input input');
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Store references
        this.chatBubble = chatBubble;
        this.chatPanel = chatPanel;
    }

    /**
     * Create a fullscreen interface for mobile devices
     */
    createFullscreenInterface() {
        // Create fullscreen container
        const fullscreenChat = document.createElement('div');
        fullscreenChat.className = 'sara-fullscreen-chat hidden';
        fullscreenChat.innerHTML = `
            <div class="sara-chat-header">
                <div class="sara-chat-title">
                    <div class="sara-avatar">S</div>
                    <div class="sara-info">
                        <h3>Sara</h3>
                        <p>HabibiStay Assistant</p>
                    </div>
                </div>
                <div class="sara-chat-actions">
                    <button class="sara-voice-toggle" title="Toggle Voice Input">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                    <button class="sara-minimize" title="Minimize Chat">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="sara-chat-messages"></div>
            <div class="sara-button-interface hidden"></div>
            <div class="sara-chat-input">
                <input type="text" placeholder="Type your message..." />
                <button class="sara-voice-input" title="Voice Input">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>
                <button class="sara-send-message" title="Send Message">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(fullscreenChat);

        // Create mobile chat trigger button
        const mobileTrigger = document.createElement('div');
        mobileTrigger.className = 'sara-mobile-trigger';
        mobileTrigger.innerHTML = `
            <div class="sara-bubble-icon">
                <span>S</span>
            </div>
            <div class="sara-notification-dot hidden"></div>
        `;
        document.body.appendChild(mobileTrigger);

        // Add event listeners
        mobileTrigger.addEventListener('click', () => this.toggleFullscreenChat());
        fullscreenChat.querySelector('.sara-minimize').addEventListener('click', () => this.toggleFullscreenChat());
        fullscreenChat.querySelector('.sara-send-message').addEventListener('click', () => this.sendMessage());
        fullscreenChat.querySelector('.sara-voice-toggle').addEventListener('click', () => this.toggleVoiceInput());
        fullscreenChat.querySelector('.sara-voice-input').addEventListener('click', () => this.startVoiceInput());

        const inputField = fullscreenChat.querySelector('.sara-chat-input input');
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Store references
        this.mobileTrigger = mobileTrigger;
        this.fullscreenChat = fullscreenChat;
    }

    /**
     * Initialize voice recognition
     */
    initVoiceRecognition() {
        if (!('webkitSpeechRecognition' in window)) {
            return;
        }

        this.voiceRecognition = new webkitSpeechRecognition();
        this.voiceRecognition.continuous = false;
        this.voiceRecognition.interimResults = false;
        this.voiceRecognition.lang = 'en-US';

        this.voiceRecognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            this.setInputText(transcript);
            // Automatically send message after voice input
            setTimeout(() => this.sendMessage(), 500);
        };

        this.voiceRecognition.onerror = (event) => {
            console.error('Voice recognition error:', event.error);
            this.addSystemMessage('I couldn\'t hear you clearly. Please try again or type your message.');
        };
    }

    /**
     * Toggle the chat panel visibility
     */
    toggleChatPanel() {
        if (this.chatPanel.classList.contains('hidden')) {
            this.chatPanel.classList.remove('hidden');
            this.chatBubble.classList.add('hidden');

            // If this is the first time opening, show initial greeting
            if (this.conversation.messages.length === 0) {
                this.addSaraMessage(this.config.initialGreeting);
                this.loadFeaturedProperties();
            }

            // Hide notification dot
            const notificationDot = this.chatBubble.querySelector('.sara-notification-dot');
            if (notificationDot) {
                notificationDot.classList.add('hidden');
            }
        } else {
            this.chatPanel.classList.add('hidden');
            this.chatBubble.classList.remove('hidden');
        }
    }

    /**
     * Toggle fullscreen chat on mobile devices
     */
    toggleFullscreenChat() {
        if (this.fullscreenChat.classList.contains('hidden')) {
            this.fullscreenChat.classList.remove('hidden');
            this.mobileTrigger.classList.add('hidden');

            // If this is the first time opening, show initial greeting
            if (this.conversation.messages.length === 0) {
                this.addSaraMessage(this.config.initialGreeting);
                this.loadFeaturedProperties();
            }

            // Hide notification dot
            const notificationDot = this.mobileTrigger.querySelector('.sara-notification-dot');
            if (notificationDot) {
                notificationDot.classList.add('hidden');
            }
        } else {
            this.fullscreenChat.classList.add('hidden');
            this.mobileTrigger.classList.remove('hidden');
        }
    }

    /**
     * Add a message from Sara to the chat
     */
    addSaraMessage(message) {
        const messagesContainer = this.getMessagesContainer();

        const messageDiv = document.createElement('div');
        messageDiv.className = 'sara-message sara-assistant';
        messageDiv.innerHTML = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <p>${message}</p>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();

        // Store the message
        this.conversation.messages.push({
            role: 'assistant',
            content: message
        });
    }

    /**
     * Add a message from the user to the chat
     */
    addUserMessage(message) {
        const messagesContainer = this.getMessagesContainer();

        const messageDiv = document.createElement('div');
        messageDiv.className = 'sara-message sara-user';
        messageDiv.innerHTML = `
            <div class="sara-bubble">
                <p>${message}</p>
            </div>
            <div class="sara-avatar">${this.getUserInitial()}</div>
        `;

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();

        // Store the message
        this.conversation.messages.push({
            role: 'user',
            content: message
        });
    }

    /**
     * Add a system message (for errors, notifications, etc.)
     */
    addSystemMessage(message) {
        const messagesContainer = this.getMessagesContainer();

        const messageDiv = document.createElement('div');
        messageDiv.className = 'sara-message sara-system';
        messageDiv.innerHTML = `
            <div class="sara-system-bubble">
                <p>${message}</p>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    /**
     * Show typing indicator
     */
    showTypingIndicator() {
        const messagesContainer = this.getMessagesContainer();

        const indicatorDiv = document.createElement('div');
        indicatorDiv.className = 'sara-message sara-assistant sara-typing-indicator';
        indicatorDiv.innerHTML = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <div class="sara-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;

        messagesContainer.appendChild(indicatorDiv);
        this.scrollToBottom();
    }

    /**
     * Remove typing indicator
     */
    removeTypingIndicator() {
        const indicator = document.querySelector('.sara-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    /**
     * Display property cards
     */
    displayPropertyCards(properties) {
        const messagesContainer = this.getMessagesContainer();

        const propertiesDiv = document.createElement('div');
        propertiesDiv.className = 'sara-message sara-assistant';

        let propertiesHtml = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <div class="sara-property-cards">
        `;

        // Generate property cards
        properties.forEach(property => {
            propertiesHtml += `
                <div class="sara-property-card" data-property-id="${property.id}">
                    <div class="sara-property-image">
                        <img src="${property.primary_image || '/images/placeholder.jpg'}" alt="${property.name}">
                    </div>
                    <div class="sara-property-details">
                        <h4>${property.name}</h4>
                        <p class="sara-property-location">${property.location}</p>
                        <div class="sara-property-meta">
                            <div class="sara-property-rating">
                                <span class="sara-stars">
                                    ${this.getStarRating(property.rating || 0)}
                                </span>
                                <span class="sara-rating-value">${property.rating || 0}</span>
                            </div>
                            <div class="sara-property-type">${property.property_type || 'Property'}</div>
                        </div>
                        <div class="sara-property-price">$${property.price_per_night || 0} / night</div>
                        <button class="sara-book-property" data-property-id="${property.id}">Book Now</button>
                    </div>
                </div>
            `;
        });

        propertiesHtml += `
                </div>
            </div>
        `;

        propertiesDiv.innerHTML = propertiesHtml;
        messagesContainer.appendChild(propertiesDiv);

        // Add event listeners for booking buttons
        const bookButtons = propertiesDiv.querySelectorAll('.sara-book-property');
        bookButtons.forEach(button => {
            button.addEventListener('click', () => {
                const propertyId = button.getAttribute('data-property-id');
                const propertyName = button.closest('.sara-property-card').querySelector('h4').textContent;
                const message = `I want to book ${propertyName}`;
                this.setInputText(message);
                this.sendMessage();
            });
        });

        this.scrollToBottom();
    }

    /**
     * Display booking form
     */
    displayBookingForm(property) {
        const messagesContainer = this.getMessagesContainer();

        const formDiv = document.createElement('div');
        formDiv.className = 'sara-message sara-assistant';
        formDiv.innerHTML = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <div class="sara-booking-form">
                    <h4>Book ${property.name}</h4>
                    <div class="sara-form-fields">
                        <div class="sara-form-field">
                            <label>Check-in</label>
                            <input type="date" class="sara-booking-checkin" min="${this.getFormattedDate()}"/>
                        </div>
                        <div class="sara-form-field">
                            <label>Check-out</label>
                            <input type="date" class="sara-booking-checkout" min="${this.getFormattedDate(1)}"/>
                        </div>
                        <div class="sara-form-field">
                            <label>Guests</label>
                            <select class="sara-booking-guests">
                                <option value="1">1 guest</option>
                                <option value="2" selected>2 guests</option>
                                <option value="3">3 guests</option>
                                <option value="4">4 guests</option>
                                <option value="5">5 guests</option>
                                <option value="6">6+ guests</option>
                            </select>
                        </div>
                    </div>
                    <button class="sara-confirm-booking" data-property-id="${property.id}">Continue to Payment</button>
                </div>
            </div>
        `;

        messagesContainer.appendChild(formDiv);

        // Add event listener for the booking button
        const confirmButton = formDiv.querySelector('.sara-confirm-booking');
        confirmButton.addEventListener('click', () => {
            const propertyId = confirmButton.getAttribute('data-property-id');
            const checkin = formDiv.querySelector('.sara-booking-checkin').value;
            const checkout = formDiv.querySelector('.sara-booking-checkout').value;
            const guests = formDiv.querySelector('.sara-booking-guests').value;

            if (!checkin || !checkout) {
                this.addSystemMessage('Please select check-in and check-out dates.');
                return;
            }

            const message = `I want to book property ${propertyId} from ${checkin} to ${checkout} for ${guests} guests`;
            this.setInputText(message);
            this.sendMessage();
        });

        this.scrollToBottom();
    }

    /**
     * Display payment options
     */
    displayPaymentOptions(booking) {
        const messagesContainer = this.getMessagesContainer();

        const paymentDiv = document.createElement('div');
        paymentDiv.className = 'sara-message sara-assistant';
        paymentDiv.innerHTML = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <div class="sara-payment-options">
                    <h4>Payment Options</h4>
                    <div class="sara-payment-summary">
                        <div class="sara-payment-row">
                            <span>Total Amount</span>
                            <span class="sara-payment-amount">$${booking.total_amount}</span>
                        </div>
                        <div class="sara-payment-details">
                            ${booking.nights} nights at ${booking.property_name}
                        </div>
                    </div>
                    <div class="sara-payment-methods">
                        <button class="sara-payment-method sara-paypal" data-payment="paypal">
                            <img src="/images/paypal-logo.png" alt="PayPal">
                            Pay with PayPal
                        </button>
                        <button class="sara-payment-method sara-myfatoorah" data-payment="myfatoorah">
                            <img src="/images/myfatoorah-logo.png" alt="MyFatoorah">
                            Pay with MyFatoorah
                        </button>
                    </div>
                </div>
            </div>
        `;

        messagesContainer.appendChild(paymentDiv);

        // Add event listeners for payment buttons
        const paymentButtons = paymentDiv.querySelectorAll('.sara-payment-method');
        paymentButtons.forEach(button => {
            button.addEventListener('click', () => {
                const paymentMethod = button.getAttribute('data-payment');
                const message = `I want to pay with ${paymentMethod} for booking #${booking.id}`;
                this.setInputText(message);
                this.sendMessage();
            });
        });

        this.scrollToBottom();
    }

    /**
     * Display button options
     */
    displayButtonOptions(buttons) {
        const messagesContainer = this.getMessagesContainer();

        const buttonsDiv = document.createElement('div');
        buttonsDiv.className = 'sara-message sara-assistant';

        let buttonsHtml = `
            <div class="sara-avatar">S</div>
            <div class="sara-bubble">
                <div class="sara-button-options">
        `;

        buttons.forEach(button => {
            buttonsHtml += `
                <button class="sara-option-button" data-action="${button.action}">
                    ${button.text}
                </button>
            `;
        });

        buttonsHtml += `
                </div>
            </div>
        `;

        buttonsDiv.innerHTML = buttonsHtml;
        messagesContainer.appendChild(buttonsDiv);

        // Add event listeners
        const optionButtons = buttonsDiv.querySelectorAll('.sara-option-button');
        optionButtons.forEach(button => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-action');
                this.setInputText(action);
                this.sendMessage();
            });
        });

        this.scrollToBottom();
    }

    /**
     * Update the button interface with suggested actions
     */
    updateButtonInterface(actions) {
        if (!this.config.enableButtons) return;

        const buttonInterface = this.getButtonInterface();
        if (!buttonInterface) return;

        // Clear existing buttons
        buttonInterface.innerHTML = '';

        // Add new buttons
        actions.forEach(action => {
            const button = document.createElement('button');
            button.className = 'sara-quick-action';
            button.textContent = action.text;
            button.setAttribute('data-action', action.action);

            button.addEventListener('click', () => {
                const actionText = button.getAttribute('data-action');
                this.setInputText(actionText);
                this.sendMessage();
            });

            buttonInterface.appendChild(button);
        });

        // Show the button interface
        buttonInterface.classList.remove('hidden');
    }

    /**
     * Send a message to Sara
     */
    sendMessage() {
        const inputField = this.getInputField();
        const message = inputField.value.trim();

        if (!message) return;

        // Add user message to chat
        this.addUserMessage(message);

        // Clear input field
        inputField.value = '';

        // Show typing indicator
        this.showTypingIndicator();

        // Send message to API
        fetch(this.config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                message: message,
                conversation_id: this.conversation.id
            })
        })
        .then(response => response.json())
        .then(data => {
            // Store conversation ID
            this.conversation.id = data.conversation_id;

            // Remove typing indicator
            this.removeTypingIndicator();

            // Process response based on type
            if (data.response_type === 'text') {
                this.addSaraMessage(data.message);
            } else if (data.response_type === 'property_search' && data.properties) {
                this.addSaraMessage(data.message);
                this.displayPropertyCards(data.properties);
            } else if (data.response_type === 'booking_form' && data.property) {
                this.addSaraMessage(data.message);
                this.displayBookingForm(data.property);
            } else if (data.response_type === 'payment' && data.booking) {
                this.addSaraMessage(data.message);
                this.displayPaymentOptions(data.booking);
            } else if (data.response_type === 'buttons' && data.buttons) {
                this.addSaraMessage(data.message);
                this.displayButtonOptions(data.buttons);
            } else {
                // Fallback for unknown response types
                this.addSaraMessage(data.message || 'I\'m not sure how to respond to that.');
            }

            // Update button interface if suggested actions are provided
            if (data.suggested_actions) {
                this.updateButtonInterface(data.suggested_actions);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            this.removeTypingIndicator();
            this.addSystemMessage('Sorry, there was an error processing your request. Please try again.');
        });
    }

    /**
     * Load featured properties
     */
    loadFeaturedProperties() {
        fetch(this.config.featuredPropertiesEndpoint)
            .then(response => response.json())
            .then(data => {
                if (data.properties && data.properties.length > 0) {
                    setTimeout(() => {
                        this.addSaraMessage('Here are some featured properties you might like:');
                        this.displayPropertyCards(data.properties.slice(0, 2));

                        // Add follow-up message
                        setTimeout(() => {
                            this.addSaraMessage('Would you like to know more about any of these properties, or would you prefer different options?');

                            // Show button interface if enabled
                            if (this.config.enableButtons) {
                                this.updateButtonInterface([
                                    { text: 'Find a place', action: 'I need a place to stay' },
                                    { text: 'More options', action: 'Show me more options' },
                                    { text: 'Help', action: 'I need help' }
                                ]);
                            }
                        }, 1000);
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error loading featured properties:', error);
            });
    }

    /**
     * Toggle voice input
     */
    toggleVoiceInput() {
        const voiceToggle = document.querySelector('.sara-voice-toggle');
        const voiceInput = document.querySelector('.sara-voice-input');

        if (voiceToggle && voiceInput) {
            this.config.enableVoice = !this.config.enableVoice;

            if (this.config.enableVoice) {
                voiceToggle.classList.add('active');
                voiceInput.classList.remove('hidden');
            } else {
                voiceToggle.classList.remove('active');
                voiceInput.classList.add('hidden');

                // Stop listening if active
                if (this.isListening && this.voiceRecognition) {
                    this.voiceRecognition.stop();
                    this.isListening = false;
                }
            }
        }
    }

    /**
     * Start voice input
     */
    startVoiceInput() {
        if (!this.config.enableVoice || !this.voiceRecognition) return;

        try {
            this.voiceRecognition.start();
            this.isListening = true;
            this.addSystemMessage('Listening...');
        } catch (e) {
            console.error('Voice recognition error:', e);
            this.addSystemMessage('Could not start voice recognition. Please try again.');
        }
    }

    /**
     * Helper methods
     */

    // Get user initial for avatar
    getUserInitial() {
        // This would typically come from the authenticated user
        // For demo purposes, return 'G' for Guest
        return 'G';
    }

    // Get CSRF token
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // Get messages container based on active interface
    getMessagesContainer() {
        if (this.fullscreenChat && !this.fullscreenChat.classList.contains('hidden')) {
            return this.fullscreenChat.querySelector('.sara-chat-messages');
        } else if (this.chatPanel && !this.chatPanel.classList.contains('hidden')) {
            return this.chatPanel.querySelector('.sara-chat-messages');
        } else {
            // Fallback to embedded container if it exists
            return document.querySelector('#sara-embedded-messages') || document.createElement('div');
        }
    }

    // Get button interface container
    getButtonInterface() {
        if (this.fullscreenChat && !this.fullscreenChat.classList.contains('hidden')) {
            return this.fullscreenChat.querySelector('.sara-button-interface');
        } else if (this.chatPanel && !this.chatPanel.classList.contains('hidden')) {
            return this.chatPanel.querySelector('.sara-button-interface');
        } else {
            // Fallback to embedded interface if it exists
            return document.querySelector('#sara-embedded-buttons') || null;
        }
    }

    // Get input field
    getInputField() {
        if (this.fullscreenChat && !this.fullscreenChat.classList.contains('hidden')) {
            return this.fullscreenChat.querySelector('.sara-chat-input input');
        } else if (this.chatPanel && !this.chatPanel.classList.contains('hidden')) {
            return this.chatPanel.querySelector('.sara-chat-input input');
        } else {
            // Fallback to embedded input if it exists
            return document.querySelector('#sara-embedded-input') || document.createElement('input');
        }
    }

    // Set input text
    setInputText(text) {
        const input = this.getInputField();
        if (input) {
            input.value = text;
            // Focus the input
            input.focus();
        }
    }

    // Scroll messages to bottom
    scrollToBottom() {
        const messagesContainer = this.getMessagesContainer();
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    // Check if interface is visible
    isInterfaceVisible() {
        return (this.chatPanel && !this.chatPanel.classList.contains('hidden')) ||
               (this.fullscreenChat && !this.fullscreenChat.classList.contains('hidden')) ||
               document.querySelector('#sara-embedded-container');
    }

    // Check if current device is mobile
    isMobileDevice() {
        return window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    // Get formatted date for input fields
    getFormattedDate(addDays = 0) {
        const date = new Date();
        if (addDays) {
            date.setDate(date.getDate() + addDays);
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Generate star rating HTML
    getStarRating(rating) {
        const fullStars = Math.floor(rating);
        const halfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

        let starsHtml = '';

        // Full stars
        for (let i = 0; i < fullStars; i++) {
            starsHtml += '<span class="sara-star-full">★</span>';
        }

        // Half star
        if (halfStar) {
            starsHtml += '<span class="sara-star-half">★</span>';
        }

        // Empty stars
        for (let i = 0; i < emptyStars; i++) {
            starsHtml += '<span class="sara-star-empty">☆</span>';
        }

        return starsHtml;
    }

    /**
     * Voice Streaming Functionality
     */

    // Initialize voice recording for streaming
    async initVoiceRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                audio: {
                    sampleRate: 16000,
                    channelCount: 1,
                    echoCancellation: true,
                    noiseSuppression: true
                }
            });

            this.mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'audio/webm;codecs=opus'
            });

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.audioChunks.push(event.data);
                }
            };

            this.mediaRecorder.onstop = () => {
                this.processVoiceRecording();
            };

            return true;
        } catch (error) {
            console.error('Failed to initialize voice recording:', error);
            return false;
        }
    }

    // Start voice recording with streaming
    async startVoiceRecording() {
        if (this.isRecording) return;

        try {
            if (!this.mediaRecorder) {
                const initialized = await this.initVoiceRecording();
                if (!initialized) {
                    throw new Error('Failed to initialize voice recording');
                }
            }

            this.audioChunks = [];
            this.isRecording = true;
            this.mediaRecorder.start();

            // Update UI to show recording state
            this.updateVoiceButtonState('recording');
            this.showVoiceRecordingIndicator();

        } catch (error) {
            console.error('Failed to start voice recording:', error);
            this.showErrorMessage('Failed to start voice recording. Please check microphone permissions.');
        }
    }

    // Stop voice recording
    stopVoiceRecording() {
        if (!this.isRecording || !this.mediaRecorder) return;

        this.isRecording = false;
        this.mediaRecorder.stop();
        this.updateVoiceButtonState('processing');
        this.hideVoiceRecordingIndicator();
    }

    // Process recorded voice audio
    async processVoiceRecording() {
        try {
            if (this.audioChunks.length === 0) {
                throw new Error('No audio data recorded');
            }

            const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
            const audioBase64 = await this.blobToBase64(audioBlob);

            // Send to Sara voice streaming API
            const response = await fetch('/api/sara-voice/process-stream', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    audio: audioBase64.split(',')[1], // Remove data:audio/webm;base64, prefix
                    file_type: 'webm'
                })
            });

            const result = await response.json();

            if (result.success) {
                // Add user message
                this.addUserMessage(result.data.user_message);

                // Stream Sara's audio response
                await this.streamSaraResponse(result.data);
            } else {
                throw new Error(result.message || 'Voice processing failed');
            }

        } catch (error) {
            console.error('Voice processing error:', error);
            this.showErrorMessage('Failed to process voice input. Please try again.');
        } finally {
            this.updateVoiceButtonState('idle');
            this.audioChunks = [];
        }
    }

    // Stream Sara's audio response
    async streamSaraResponse(responseData) {
        try {
            // Add Sara's text response immediately
            this.addSaraMessage(responseData.ai_response);

            // If streaming is available, play the audio
            if (responseData.stream_ready && responseData.stream_id) {
                await this.playStreamingAudio(responseData);
            }

        } catch (error) {
            console.error('Failed to stream Sara response:', error);
        }
    }

    // Play streaming audio response
    async playStreamingAudio(responseData) {
        try {
            const streamUrl = `/api/sara-voice/stream/${responseData.stream_id}?` + new URLSearchParams({
                text: responseData.ai_response,
                options: JSON.stringify(responseData.stream_options)
            });

            // Create audio element for streaming
            const audio = new Audio();
            audio.preload = 'none';
            
            this.currentAudioElement = audio;
            this.isPlayingAudio = true;

            // Show audio playing indicator
            this.showAudioPlayingIndicator();

            // Set up event listeners
            audio.onloadstart = () => {
                console.log('Audio loading started');
            };

            audio.oncanplay = () => {
                audio.play().catch(error => {
                    console.error('Audio play failed:', error);
                    this.isPlayingAudio = false;
                    this.hideAudioPlayingIndicator();
                });
            };

            audio.onended = () => {
                this.isPlayingAudio = false;
                this.currentAudioElement = null;
                this.hideAudioPlayingIndicator();
            };

            audio.onerror = (error) => {
                console.error('Audio streaming error:', error);
                this.isPlayingAudio = false;
                this.hideAudioPlayingIndicator();
            };

            // Start streaming
            audio.src = streamUrl;

        } catch (error) {
            console.error('Failed to play streaming audio:', error);
            this.isPlayingAudio = false;
            this.hideAudioPlayingIndicator();
        }
    }

    // Update voice button state
    updateVoiceButtonState(state) {
        const voiceButtons = document.querySelectorAll('.sara-voice-toggle, #voiceToggle');
        
        voiceButtons.forEach(button => {
            button.classList.remove('recording', 'processing', 'idle');
            button.classList.add(state);

            switch (state) {
                case 'recording':
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
                        </svg>
                    `;
                    button.title = 'Recording... Click to stop';
                    break;
                case 'processing':
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    `;
                    button.title = 'Processing...';
                    break;
                default:
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    `;
                    button.title = 'Click to start voice input';
            }
        });
    }

    // Show voice recording indicator
    showVoiceRecordingIndicator() {
        const messagesContainer = this.getMessagesContainer();
        if (!messagesContainer) return;

        const indicator = document.createElement('div');
        indicator.className = 'sara-voice-indicator recording';
        indicator.innerHTML = `
            <div class="sara-voice-animation">
                <div class="sara-pulse"></div>
                <div class="sara-pulse"></div>
                <div class="sara-pulse"></div>
            </div>
            <span>Recording... Speak now</span>
        `;

        messagesContainer.appendChild(indicator);
        this.scrollToBottom();
    }

    // Hide voice recording indicator
    hideVoiceRecordingIndicator() {
        const indicators = document.querySelectorAll('.sara-voice-indicator.recording');
        indicators.forEach(indicator => indicator.remove());
    }

    // Show audio playing indicator
    showAudioPlayingIndicator() {
        const messagesContainer = this.getMessagesContainer();
        if (!messagesContainer) return;

        const indicator = document.createElement('div');
        indicator.className = 'sara-audio-indicator playing';
        indicator.innerHTML = `
            <div class="sara-audio-animation">
                <div class="sara-wave"></div>
                <div class="sara-wave"></div>
                <div class="sara-wave"></div>
                <div class="sara-wave"></div>
            </div>
            <span>Sara is speaking...</span>
        `;

        messagesContainer.appendChild(indicator);
        this.scrollToBottom();
    }

    // Hide audio playing indicator
    hideAudioPlayingIndicator() {
        const indicators = document.querySelectorAll('.sara-audio-indicator.playing');
        indicators.forEach(indicator => indicator.remove());
    }

    // Convert blob to base64
    blobToBase64(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

    // Get authentication token
    getAuthToken() {
        // Try to get from meta tag first
        const metaToken = document.querySelector('meta[name="api-token"]');
        if (metaToken) {
            return metaToken.content;
        }

        // Fallback to localStorage or sessionStorage
        return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
    }

    // Show error message
    showErrorMessage(message) {
        const messagesContainer = this.getMessagesContainer();
        if (!messagesContainer) return;

        const errorDiv = document.createElement('div');
        errorDiv.className = 'sara-error-message';
        errorDiv.innerHTML = `
            <div class="sara-error-icon">⚠️</div>
            <span>${message}</span>
        `;

        messagesContainer.appendChild(errorDiv);
        this.scrollToBottom();

        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Toggle voice input (updated to support streaming)
    toggleVoiceInput() {
        if (this.isRecording) {
            this.stopVoiceRecording();
        } else if (this.isPlayingAudio) {
            // Stop current audio playback
            if (this.currentAudioElement) {
                this.currentAudioElement.pause();
                this.currentAudioElement = null;
                this.isPlayingAudio = false;
                this.hideAudioPlayingIndicator();
            }
        } else {
            this.startVoiceRecording();
        }
    }
}

// Initialize Sara when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if Sara config is available (would be set by backend)
    const saraConfig = window.saraConfig || {};

    // Initialize Sara chat
    window.saraChat = new SaraChat(saraConfig);
});
