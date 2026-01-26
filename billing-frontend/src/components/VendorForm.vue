<template>
  <Teleport to="body">
    <div class="vendor-modal-overlay" @click.self="$emit('close')">
      <div class="vendor-modal">
        <h2>{{ isEdit ? 'Edit Vendor' : 'Add Vendor' }}</h2>

        <div class="form-group">
          <label>Vendor Name</label>
          <input v-model="form.name" />
        </div>

        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" />
        </div>

        <div class="form-group">
          <label>Phone</label>
          <input v-model="form.phone" />
        </div>

        <div class="actions">
          <button class="btn secondary" @click="$emit('close')">
            Cancel
          </button>
          <button class="btn primary" @click="submit">
            {{ isEdit ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, watch, computed } from 'vue'

const props = defineProps({
  vendor: Object,
})

const emit = defineEmits(['save', 'close'])

const form = reactive({
  name: '',
  email: '',
  phone: '',
})

const isEdit = computed(() => !!props.vendor)

watch(
  () => props.vendor,
  (val) => {
    if (val) Object.assign(form, val)
    else {
      form.name = ''
      form.email = ''
      form.phone = ''
    }
  },
  { immediate: true }
)

const submit = () => {
  emit('save', { ...form })
}
</script>

<style>
.vendor-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999; 
}

.vendor-modal {
  background: #ffffff;
  padding: 2rem;
  width: 420px;
  border-radius: 14px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
}

.form-group input {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.btn.primary {
  background: #111827;
  color: white;
}

.btn.secondary {
  background: #f3f4f6;
}
</style>
