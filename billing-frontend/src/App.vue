<template>
  <router-view />
  <ToastContainer />
  <Loader :loading="loading" />
</template>

<script setup>
import { ref, provide } from 'vue'
import ToastContainer from './components/ToastContainer.vue'
import Loader from './components/Loader.vue'

const loading = ref(false)

/* Make loader controllable from anywhere */
provide('setLoading', (value) => {
  loading.value = value
})
</script>
