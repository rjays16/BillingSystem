<template>
  <router-view />
</template>

<script setup>
import { onMounted, onErrorCaptured } from 'vue'
import { useAuthStore } from './stores/auth'
import ToastContainer from './components/ToastContainer.vue'
import Loader from './components/Loader.vue'

const authStore = useAuthStore()

onMounted(() => {
  authStore.initializeAuth()
})

onErrorCaptured((error, instance, info) => {
  console.error('Global error:', error)
  
  if (error.message?.includes('401') || error.message?.includes('unauthorized')) {
    window.location.href = '/login'
  }
})
</script>

<style scoped>
#app {
  min-height: 100vh;
  font-family: Arial, sans-serif;
}
</style>