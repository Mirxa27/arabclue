<template>
  <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200">
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
      <router-link to="/" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50">
        <svg class="w-6 h-6 mb-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
        </svg>
        <span class="text-sm text-gray-500">Home</span>
      </router-link>
      <router-link to="/stays" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50">
        <svg class="w-6 h-6 mb-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
        </svg>
        <span class="text-sm text-gray-500">Stays</span>
      </router-link>
      <button @click="openSara" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50">
        <svg class="w-8 h-8 mb-1 text-white" fill="#2957c3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm text-gray-500">Sara</span>
      </button>
      <router-link to="/messages" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50">
        <svg class="w-6 h-6 mb-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
          <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
        </svg>
        <span class="text-sm text-gray-500">Messages</span>
      </router-link>
      <router-link to="/profile" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50">
        <svg class="w-6 h-6 mb-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm text-gray-500">Profile</span>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    openSara() {
      // Logic to open Sara chatbot
    }
  }
}
</script>
