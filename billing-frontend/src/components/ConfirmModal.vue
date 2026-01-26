<template>
  <Teleport to="body">
    <div class="confirm-overlay" @click.self="$emit('cancel')">
      <div class="confirm-modal">
        <h3 class="title">{{ title }}</h3>
        <p class="message">{{ message }}</p>

        <div class="actions">
          <button class="btn secondary" @click="$emit('cancel')">
            Cancel
          </button>
          <button class="btn danger" @click="$emit('confirm')">
            Delete
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Confirm',
  },
  message: {
    type: String,
    default: 'Are you sure?',
  },
})

defineEmits(['confirm', 'cancel'])
</script>

<style>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999;
}

.confirm-modal {
  background: #ffffff;
  width: 360px;
  padding: 1.75rem;
  border-radius: 14px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.message {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.btn {
  padding: 0.45rem 0.9rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn.secondary {
  background: #f3f4f6;
}

.btn.danger {
  background: #fee2e2;
  color: #991b1b;
}
</style>
