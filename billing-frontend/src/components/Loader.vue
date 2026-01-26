<template>
  <Teleport to="body">
    <div v-if="loading" class="loader-overlay">
      <div class="spinner"></div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  loading: Boolean,
})
</script>

<style>
.loader-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 5px solid #e5e7eb;
  border-top-color: #111827;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
