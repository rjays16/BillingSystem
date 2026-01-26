<template>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="toast show align-items-center text-white border-0 mb-2"
      :class="toast.type"
    >
      <div class="d-flex">
        <div class="toast-body">
          {{ toast.message }}
        </div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          @click="remove(toast.id)"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast'
const { toasts, remove } = useToast()
</script>

<style>
.toast.success { background-color: #16a34a; }
.toast.error { background-color: #dc2626; }
.toast.info { background-color: #2563eb; }
</style>
