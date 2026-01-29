<template>
  <div class="sara-chat-interface">
    <div class="chat-header">
      <h3>Sara</h3>
    </div>
    <div class="chat-messages">
      <div v-for="message in messages" :key="message.id" class="message" :class="message.role">
        <p>{{ message.content }}</p>
      </div>
    </div>
    <div class="chat-input">
      <input type="text" v-model="newMessage" @keyup.enter="sendMessage" placeholder="Type a message...">
      <button @click="sendMessage">Send</button>
    </div>
    <div class="suggested-actions">
      <button v-for="action in suggestedActions" :key="action.label" @click="handleAction(action)">
        {{ action.label }}
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      messages: [],
      newMessage: '',
      suggestedActions: [],
      conversationId: null,
    };
  },
  methods: {
    async startConversation() {
      try {
        const res = await axios.post('/api/chat/start');
        this.conversationId = res.data.conversation_id;
        this.messages.push({ role: 'assistant', content: res.data.content });
        this.suggestedActions = res.data.actions || [];
      } catch (e) { console.error(e); }
    },
    async sendMessage() {
      if (!this.newMessage) return;
      const msg = this.newMessage;
      this.messages.push({ role: 'user', content: msg });
      this.newMessage = '';
      try {
        const res = await axios.post('/api/chat/message', { conversation_id: this.conversationId, message: msg });
        this.messages.push({ role: 'assistant', content: res.data.content });
        this.suggestedActions = res.data.suggested_actions || [];
      } catch (e) { console.error(e); }
    },
    async handleAction(action) {
      try {
        const res = await axios.post('/api/chat/action', { conversation_id: this.conversationId, action: action.data });
        this.messages.push({ role: 'assistant', content: res.data.content });
        this.suggestedActions = res.data.suggested_actions || [];
      } catch (e) { console.error(e); }
    },
  },
  mounted() {
    this.startConversation();
  },
};
</script>

<style scoped>
.sara-chat-interface {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  z-index: 1000;
}
.chat-header {
  padding: 1rem;
  background-color: #2957c3;
  color: white;
  text-align: center;
}
.chat-messages {
  flex-grow: 1;
  padding: 1rem;
  overflow-y: auto;
}
.message {
  margin-bottom: 1rem;
}
.message.user {
  text-align: right;
}
.message.assistant {
  text-align: left;
}
.chat-input {
  display: flex;
  padding: 1rem;
  border-top: 1px solid #eee;
}
.chat-input input {
  flex-grow: 1;
  border: 1px solid #ccc;
  padding: 0.5rem;
  border-radius: 5px;
}
.chat-input button {
  margin-left: 1rem;
  padding: 0.5rem 1rem;
  background-color: #2957c3;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.suggested-actions {
  display: flex;
  flex-wrap: wrap;
  padding: 0.5rem;
  border-top: 1px solid #eee;
}
.suggested-actions button {
  margin: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  border-radius: 20px;
  cursor: pointer;
}
</style>
