<template>
  <div class="sara-chat-widget">
    <!-- Chat Trigger Button (Mobile) -->
    <transition name="bounce">
      <button
        v-if="!isOpen && isMobile"
        @click="openChat"
        class="chat-trigger-btn"
        :class="{ 'has-unread': unreadCount > 0 }"
        aria-label="Open Sara AI Assistant"
      >
        <div class="chat-trigger-icon">
          <Icon name="robot" />
          <div v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</div>
        </div>
        <div class="chat-trigger-pulse"></div>
      </button>
    </transition>

    <!-- Desktop Chat Widget -->
    <transition name="slide-up">
      <div
        v-if="isOpen || !isMobile"
        class="chat-container"
        :class="{ 'mobile-fullscreen': isMobile, 'desktop-widget': !isMobile }"
      >
        <!-- Chat Header -->
        <div class="chat-header">
          <div class="sara-avatar">
            <div class="avatar-container">
              <Icon name="robot" />
              <div 
                class="status-indicator" 
                :class="{ 'online': isOnline, 'typing': isTyping }"
              ></div>
            </div>
          </div>
          
          <div class="sara-info">
            <h3 class="sara-name">Sara</h3>
            <p class="sara-status">
              {{ isTyping ? 'Typing...' : isOnline ? 'Online' : 'AI Assistant' }}
            </p>
          </div>

          <div class="header-actions">
            <!-- Voice Toggle -->
            <button
              v-if="supportsVoice"
              @click="toggleVoice"
              class="voice-btn"
              :class="{ active: voiceEnabled }"
              aria-label="Toggle voice mode"
            >
              <Icon :name="voiceEnabled ? 'microphone' : 'microphone-slash'" />
            </button>

            <!-- Minimize/Close -->
            <button
              @click="closeChat"
              class="close-btn"
              aria-label="Close chat"
            >
              <Icon :name="isMobile ? 'chevron-down' : 'times'" />
            </button>
          </div>
        </div>

        <!-- Featured Properties (Initial State) -->
        <div 
          v-if="showFeaturedProperties && !hasMessages"
          class="featured-properties"
        >
          <h4 class="featured-title">Featured Properties</h4>
          <div class="properties-grid">
            <PropertyCard
              v-for="property in featuredProperties"
              :key="property.id"
              :property="property"
              @select="handlePropertySelect"
              compact
            />
          </div>
        </div>

        <!-- Chat Messages -->
        <div 
          ref="messagesContainer"
          class="chat-messages"
          :class="{ 'with-featured': showFeaturedProperties && !hasMessages }"
        >
          <!-- Welcome Message -->
          <SaraMessage
            v-if="!hasMessages"
            :message="welcomeMessage"
            type="assistant"
            :animate="true"
          />

          <!-- Conversation Messages -->
          <SaraMessage
            v-for="message in messages"
            :key="message.id"
            :message="message"
            :type="message.role"
            :animate="message.isNew"
            @action="handleMessageAction"
          />

          <!-- Typing Indicator -->
          <TypingIndicator v-if="isTyping" />

          <!-- Connection Status -->
          <div v-if="!isOnline" class="connection-status">
            <Icon name="wifi-slash" />
            <span>Reconnecting...</span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="quickActions.length > 0" class="quick-actions">
          <h5 class="quick-actions-title">Quick Actions</h5>
          <div class="actions-grid">
            <button
              v-for="action in quickActions"
              :key="action.id"
              @click="handleQuickAction(action)"
              class="quick-action-btn"
            >
              <Icon :name="action.icon" />
              <span>{{ action.label }}</span>
            </button>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="chat-input-container">
          <!-- Suggested Responses -->
          <div 
            v-if="suggestedResponses.length > 0"
            class="suggested-responses"
          >
            <button
              v-for="suggestion in suggestedResponses"
              :key="suggestion.id"
              @click="sendMessage(suggestion.text)"
              class="suggestion-btn"
            >
              {{ suggestion.text }}
            </button>
          </div>

          <!-- Input Form -->
          <form @submit.prevent="sendMessage(currentMessage)" class="input-form">
            <div class="input-wrapper">
              <!-- Voice Input (Mobile) -->
              <button
                v-if="isMobile && supportsVoice"
                @touchstart="startVoiceInput"
                @touchend="stopVoiceInput"
                @mousedown="startVoiceInput"
                @mouseup="stopVoiceInput"
                class="voice-input-btn"
                :class="{ active: isListening }"
                type="button"
                aria-label="Hold to speak"
              >
                <Icon :name="isListening ? 'microphone' : 'microphone-slash'" />
                <div v-if="isListening" class="voice-pulse"></div>
              </button>

              <!-- Text Input -->
              <textarea
                ref="messageInput"
                v-model="currentMessage"
                :placeholder="inputPlaceholder"
                class="message-input"
                rows="1"
                :maxlength="1000"
                @keydown="handleInputKeydown"
                @input="handleInputChange"
                :disabled="isLoading"
                aria-label="Type your message"
              ></textarea>

              <!-- Attachment Button -->
              <button
                @click="openAttachmentMenu"
                class="attachment-btn"
                type="button"
                aria-label="Add attachment"
              >
                <Icon name="paperclip" />
              </button>

              <!-- Send Button -->
              <button
                type="submit"
                class="send-btn"
                :disabled="!canSend"
                aria-label="Send message"
              >
                <Icon v-if="isLoading" name="spinner" class="animate-spin" />
                <Icon v-else name="paper-plane" />
              </button>
            </div>

            <!-- Character Counter -->
            <div v-if="currentMessage.length > 800" class="char-counter">
              {{ 1000 - currentMessage.length }} characters remaining
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Attachment Menu Modal -->
    <AttachmentMenu
      v-if="showAttachmentMenu"
      @close="showAttachmentMenu = false"
      @select="handleAttachment"
    />

    <!-- Property Detail Modal -->
    <PropertyDetailModal
      v-if="selectedProperty"
      :property="selectedProperty"
      @close="selectedProperty = null"
      @book="handlePropertyBook"
    />
  </div>
</template>

<script>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import { useWebSocket } from '@/composables/useWebSocket'
import { useSaraAPI } from '@/composables/useSaraAPI'
import { useVoiceInput } from '@/composables/useVoiceInput'
import { useNotifications } from '@/composables/useNotifications'
import Icon from '@/components/Icon.vue'
import SaraMessage from '@/components/SaraMessage.vue'
import PropertyCard from '@/components/PropertyCard.vue'
import PropertyDetailModal from '@/components/PropertyDetailModal.vue'
import AttachmentMenu from '@/components/AttachmentMenu.vue'
import TypingIndicator from '@/components/TypingIndicator.vue'

export default {
  name: 'SaraChatWidget',
  components: {
    Icon,
    SaraMessage,
    PropertyCard,
    PropertyDetailModal,
    AttachmentMenu,
    TypingIndicator
  },
  props: {
    apiBaseUrl: {
      type: String,
      required: true
    },
    userToken: {
      type: String,
      default: null
    },
    initialProperties: {
      type: Array,
      default: () => []
    },
    theme: {
      type: String,
      default: 'default'
    }
  },
  setup(props) {
    // Reactive state
    const isOpen = ref(false)
    const currentMessage = ref('')
    const messages = ref([])
    const conversationId = ref(null)
    const isLoading = ref(false)
    const isTyping = ref(false)
    const isListening = ref(false)
    const voiceEnabled = ref(false)
    const showAttachmentMenu = ref(false)
    const selectedProperty = ref(null)
    const featuredProperties = ref(props.initialProperties)
    const quickActions = ref([])
    const suggestedResponses = ref([])
    const unreadCount = ref(0)

    // Refs
    const messagesContainer = ref(null)
    const messageInput = ref(null)

    // Composables
    const { connect, disconnect, sendMessage: wsSend, isOnline } = useWebSocket()
    const { 
      startConversation, 
      sendMessage: apiSend, 
      getHistory,
      endConversation 
    } = useSaraAPI(props.apiBaseUrl, props.userToken)
    const { 
      startListening, 
      stopListening, 
      isSupported: supportsVoice,
      result: voiceResult 
    } = useVoiceInput()
    const { showNotification } = useNotifications()

    // Computed
    const isMobile = computed(() => window.innerWidth < 768)
    const hasMessages = computed(() => messages.value.length > 0)
    const showFeaturedProperties = computed(() => 
      featuredProperties.value.length > 0 && !hasMessages.value
    )
    const canSend = computed(() => 
      currentMessage.value.trim().length > 0 && !isLoading.value
    )
    const inputPlaceholder = computed(() => {
      if (isLoading.value) return 'Sara is thinking...'
      if (voiceEnabled.value && supportsVoice.value) return 'Speak or type your message...'
      return 'Ask Sara anything about your stay...'
    })

    const welcomeMessage = computed(() => ({
      id: 'welcome',
      content: `Hi! I'm Sara, your AI booking assistant. I can help you find the perfect property, manage your bookings, or answer any questions. ${showFeaturedProperties.value ? 'Here are some featured properties to get you started!' : 'What can I help you with today?'}`,
      role: 'assistant',
      timestamp: new Date().toISOString(),
      type: 'welcome'
    }))

    // Methods
    const openChat = async () => {
      isOpen.value = true
      if (!conversationId.value) {
        await initializeConversation()
      }
      await nextTick()
      scrollToBottom()
      focusInput()
    }

    const closeChat = () => {
      if (isMobile.value) {
        isOpen.value = false
      } else {
        // Desktop: minimize to corner
        isOpen.value = false
      }
    }

    const initializeConversation = async () => {
      try {
        isLoading.value = true
        const response = await startConversation({
          channel: isMobile.value ? 'mobile' : 'web',
          context: {
            viewport: {
              width: window.innerWidth,
              height: window.innerHeight
            },
            page: window.location.pathname,
            userAgent: navigator.userAgent
          }
        })

        conversationId.value = response.conversation.id
        
        // Load featured properties if provided
        if (response.featured_properties) {
          featuredProperties.value = response.featured_properties
        }

        // Setup WebSocket connection
        if (isOnline.value) {
          connect(`/conversations/${conversationId.value}`)
        }

      } catch (error) {
        console.error('Failed to initialize conversation:', error)
        showNotification('Failed to start chat. Please try again.', 'error')
      } finally {
        isLoading.value = false
      }
    }

    const sendMessage = async (message) => {
      if (!message.trim() || isLoading.value) return

      const userMessage = {
        id: Date.now(),
        content: message.trim(),
        role: 'user',
        timestamp: new Date().toISOString(),
        isNew: true
      }

      messages.value.push(userMessage)
      currentMessage.value = ''
      isLoading.value = true
      isTyping.value = true

      await nextTick()
      scrollToBottom()

      try {
        const response = await apiSend(conversationId.value, message.trim())
        
        const assistantMessage = {
          id: response.message.id,
          content: response.message.content,
          role: 'assistant',
          timestamp: response.message.timestamp,
          data: response.data,
          actions: response.actions,
          isNew: true
        }

        messages.value.push(assistantMessage)

        // Update quick actions and suggestions
        if (response.quick_actions) {
          quickActions.value = response.quick_actions
        }
        
        if (response.suggested_responses) {
          suggestedResponses.value = response.suggested_responses
        }

        // Handle special responses
        if (response.properties) {
          handlePropertiesResponse(response.properties)
        }

        if (response.booking_confirmation) {
          handleBookingConfirmation(response.booking_confirmation)
        }

      } catch (error) {
        console.error('Failed to send message:', error)
        
        const errorMessage = {
          id: Date.now() + 1,
          content: 'Sorry, I encountered an error. Please try again or contact support if the problem persists.',
          role: 'assistant',
          timestamp: new Date().toISOString(),
          type: 'error',
          isNew: true
        }
        
        messages.value.push(errorMessage)
        showNotification('Failed to send message', 'error')
      } finally {
        isLoading.value = false
        isTyping.value = false
        await nextTick()
        scrollToBottom()
        focusInput()
      }
    }

    const handleQuickAction = (action) => {
      switch (action.type) {
        case 'message':
          sendMessage(action.message)
          break
        case 'search':
          handleSearchAction(action.data)
          break
        case 'booking':
          handleBookingAction(action.data)
          break
        case 'external':
          window.open(action.url, '_blank')
          break
      }
    }

    const handlePropertySelect = (property) => {
      selectedProperty.value = property
    }

    const handlePropertyBook = (property, details) => {
      selectedProperty.value = null
      const bookingMessage = `I'd like to book ${property.title} for ${details.guests} guests from ${details.check_in} to ${details.check_out}`
      sendMessage(bookingMessage)
    }

    const handleMessageAction = (action) => {
      switch (action.type) {
        case 'book_property':
          handlePropertySelect(action.property)
          break
        case 'view_booking':
          window.open(`/bookings/${action.booking_id}`, '_blank')
          break
        case 'contact_host':
          sendMessage(`Can you help me contact the host for ${action.property_name}?`)
          break
      }
    }

    const toggleVoice = () => {
      voiceEnabled.value = !voiceEnabled.value
      if (voiceEnabled.value && !supportsVoice.value) {
        showNotification('Voice input is not supported in this browser', 'warning')
        voiceEnabled.value = false
      }
    }

    const startVoiceInput = () => {
      if (!voiceEnabled.value || !supportsVoice.value) return
      
      isListening.value = true
      startListening()
    }

    const stopVoiceInput = () => {
      if (!isListening.value) return
      
      isListening.value = false
      stopListening()
    }

    const handleInputKeydown = (event) => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage(currentMessage.value)
      }
    }

    const handleInputChange = () => {
      // Auto-resize textarea
      const textarea = messageInput.value
      if (textarea) {
        textarea.style.height = 'auto'
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px'
      }
    }

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      }
    }

    const focusInput = () => {
      if (!isMobile.value && messageInput.value) {
        messageInput.value.focus()
      }
    }

    const openAttachmentMenu = () => {
      showAttachmentMenu.value = true
    }

    const handleAttachment = (attachment) => {
      showAttachmentMenu.value = false
      // Handle different attachment types
      if (attachment.type === 'image') {
        sendMessage(`[Image: ${attachment.name}]`)
      }
    }

    // Watch for voice input results
    watch(voiceResult, (newResult) => {
      if (newResult && voiceEnabled.value) {
        currentMessage.value = newResult
        sendMessage(newResult)
      }
    })

    // Lifecycle
    onMounted(() => {
      // Auto-open on mobile if no previous interaction
      if (isMobile.value && !localStorage.getItem('sara_interacted')) {
        setTimeout(() => {
          openChat()
        }, 3000)
      }

      // Listen for URL parameters
      const urlParams = new URLSearchParams(window.location.search)
      if (urlParams.get('sara') === 'open') {
        openChat()
      }
    })

    onUnmounted(() => {
      disconnect()
      if (conversationId.value) {
        endConversation(conversationId.value)
      }
    })

    return {
      // State
      isOpen,
      currentMessage,
      messages,
      isLoading,
      isTyping,
      isListening,
      voiceEnabled,
      showAttachmentMenu,
      selectedProperty,
      featuredProperties,
      quickActions,
      suggestedResponses,
      unreadCount,

      // Refs
      messagesContainer,
      messageInput,

      // Computed
      isMobile,
      hasMessages,
      showFeaturedProperties,
      canSend,
      inputPlaceholder,
      welcomeMessage,
      isOnline,
      supportsVoice,

      // Methods
      openChat,
      closeChat,
      sendMessage,
      handleQuickAction,
      handlePropertySelect,
      handlePropertyBook,
      handleMessageAction,
      toggleVoice,
      startVoiceInput,
      stopVoiceInput,
      handleInputKeydown,
      handleInputChange,
      openAttachmentMenu,
      handleAttachment
    }
  }
}
</script>

<style scoped>
.sara-chat-widget {
  @apply fixed z-50;
}

/* Mobile fullscreen on small screens */
@screen lg {
  .sara-chat-widget {
    @apply bottom-4 right-4;
  }
}

.chat-trigger-btn {
  @apply fixed bottom-20 right-4 w-16 h-16 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 z-50;
}

.chat-trigger-btn:hover {
  @apply bg-blue-700 scale-110;
}

.chat-trigger-btn.has-unread {
  @apply animate-pulse;
}

.chat-trigger-icon {
  @apply relative;
}

.unread-badge {
  @apply absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center;
}

.chat-trigger-pulse {
  @apply absolute inset-0 rounded-full bg-blue-600 animate-ping opacity-75;
}

.chat-container {
  @apply bg-white rounded-lg shadow-2xl flex flex-col;
}

.chat-container.mobile-fullscreen {
  @apply fixed inset-x-0 bottom-0 top-0 rounded-none;
  height: 100vh;
  height: 100dvh; /* Dynamic viewport height for mobile */
}

.chat-container.desktop-widget {
  @apply w-96 h-[600px] max-h-[80vh];
}

.chat-header {
  @apply flex items-center p-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-purple-600 text-white;
}

.chat-container.mobile-fullscreen .chat-header {
  @apply pt-safe-top;
}

.sara-avatar {
  @apply relative mr-3;
}

.avatar-container {
  @apply w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center relative;
}

.status-indicator {
  @apply absolute -bottom-1 -right-1 w-3 h-3 border-2 border-white rounded-full;
}

.status-indicator.online {
  @apply bg-green-400;
}

.status-indicator.typing {
  @apply bg-yellow-400 animate-pulse;
}

.sara-info {
  @apply flex-1;
}

.sara-name {
  @apply font-semibold text-lg;
}

.sara-status {
  @apply text-sm opacity-90;
}

.header-actions {
  @apply flex items-center space-x-2;
}

.voice-btn, .close-btn {
  @apply w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-colors;
}

.voice-btn.active {
  @apply bg-green-400 bg-opacity-100 text-green-900;
}

.featured-properties {
  @apply p-4 border-b border-gray-100;
}

.featured-title {
  @apply font-semibold text-gray-800 mb-3;
}

.properties-grid {
  @apply grid grid-cols-1 gap-3;
}

.chat-messages {
  @apply flex-1 overflow-y-auto p-4 space-y-4;
}

.chat-messages.with-featured {
  @apply flex-none h-64;
}

.connection-status {
  @apply flex items-center justify-center space-x-2 text-gray-500 text-sm py-2;
}

.quick-actions {
  @apply px-4 py-3 border-t border-gray-100;
}

.quick-actions-title {
  @apply text-sm font-medium text-gray-700 mb-2;
}

.actions-grid {
  @apply flex space-x-2 overflow-x-auto;
}

.quick-action-btn {
  @apply flex items-center space-x-2 px-3 py-2 bg-gray-100 rounded-full text-sm whitespace-nowrap hover:bg-gray-200 transition-colors;
}

.chat-input-container {
  @apply border-t border-gray-200 bg-white;
}

.chat-container.mobile-fullscreen .chat-input-container {
  @apply pb-safe-bottom;
}

.suggested-responses {
  @apply p-3 border-b border-gray-100 flex space-x-2 overflow-x-auto;
}

.suggestion-btn {
  @apply px-3 py-2 bg-blue-100 text-blue-700 rounded-full text-sm whitespace-nowrap hover:bg-blue-200 transition-colors;
}

.input-form {
  @apply p-4;
}

.input-wrapper {
  @apply flex items-end space-x-2 bg-gray-50 rounded-full p-2;
}

.voice-input-btn {
  @apply w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center relative hover:bg-blue-700 transition-colors;
}

.voice-input-btn.active {
  @apply bg-red-500;
}

.voice-pulse {
  @apply absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75;
}

.message-input {
  @apply flex-1 bg-transparent border-none outline-none resize-none min-h-[2.5rem] max-h-[7.5rem] py-2 px-3;
}

.attachment-btn, .send-btn {
  @apply w-10 h-10 rounded-full flex items-center justify-center transition-colors;
}

.attachment-btn {
  @apply text-gray-500 hover:text-gray-700 hover:bg-gray-200;
}

.send-btn {
  @apply bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed;
}

.char-counter {
  @apply text-xs text-gray-500 mt-1 text-right;
}

/* Animations */
.bounce-enter-active {
  animation: bounce-in 0.5s;
}

.bounce-leave-active {
  animation: bounce-in 0.5s reverse;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.3s ease-out;
}

.slide-up-enter-from {
  transform: translateY(100%);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

/* Safe area support */
.pt-safe-top {
  padding-top: env(safe-area-inset-top);
}

.pb-safe-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .chat-container {
    @apply bg-gray-900 text-white;
  }
  
  .chat-header {
    @apply border-gray-700;
  }
  
  .featured-properties {
    @apply border-gray-700;
  }
  
  .quick-actions {
    @apply border-gray-700;
  }
  
  .chat-input-container {
    @apply border-gray-700 bg-gray-900;
  }
  
  .input-wrapper {
    @apply bg-gray-800;
  }
  
  .message-input {
    @apply text-white placeholder-gray-400;
  }
  
  .quick-action-btn {
    @apply bg-gray-800 text-gray-200 hover:bg-gray-700;
  }
  
  .suggestion-btn {
    @apply bg-blue-900 text-blue-200 hover:bg-blue-800;
  }
}
</style>