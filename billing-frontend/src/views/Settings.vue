<template>
  <AppLayout>
    <div class="page-header">
      <h1>Organization Settings</h1>
      <p class="subtitle">Manage your organization settings</p>
    </div>
    
    <div class="placeholder">
      <i class="bi bi-gear-fill"></i>
      <h2>Organization Settings (Admin Only)</h2>
      <p>This page will allow admins to configure organization-specific settings.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../layouts/AppLayout.vue'
</script>

<style scoped>
.placeholder {
  text-align: center;
  padding: 4rem 2rem;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  margin: 2rem 0;
}

.placeholder i {
  font-size: 4rem;
  color: #667eea;
  margin-bottom: 1rem;
}

.placeholder h2 {
  color: #111827;
  margin-bottom: 0.5rem;
}

.placeholder p {
  color: #6b7280;
}
</style>