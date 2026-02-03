<template>
  <Teleport to="body">
    <div class="vendor-modal-overlay" @click.self="$emit('close')">
      <div class="vendor-modal">
        <h2>{{ isEdit ? 'Edit Vendor' : 'Add Vendor' }}</h2>

        <form @submit.prevent="submit">
          <div class="form-group">
            <label>Vendor Name *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter vendor name"
              required
              :class="{ 'error': errors.name }"
              @blur="validateField('name')"
            />
            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
          </div>

          <div class="form-group">
            <label>Email *</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="name@company.com"
              required
              :class="{ 'error': errors.email }"
              @blur="validateField('email')"
            />
            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label>Phone *</label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="+63 912 345 6789"
              required
              :class="{ 'error': errors.phone }"
              @blur="validateField('phone')"
            />
            <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
          </div>

          <div class="form-group">
            <label>Address</label>
            <input
              v-model="form.address"
              type="text"
              placeholder="Complete address (optional)"
            />
          </div>

          <div class="form-group status-group">
            <label for="vendorStatus">Vendor Status *</label>
            <div class="select-wrapper">
              <select 
                id="vendorStatus"
                v-model="form.status" 
                required
                :class="{ 'error': errors.status }"
              >
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <i class="bi bi-chevron-down select-icon"></i>
            </div>
            <span v-if="errors.status" class="error-text">{{ errors.status }}</span>
          </div>

          <div class="actions">
            <button 
              type="button" 
              class="btn secondary" 
              @click="$emit('close')"
              :disabled="isSubmitting"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn primary"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting" class="spinner"></span>
              {{ isSubmitting ? (isEdit ? 'Updating...' : 'Saving...') : (isEdit ? 'Update' : 'Save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, watch, computed, ref } from 'vue'

const props = defineProps({
  vendor: Object,
})

const emit = defineEmits(['save', 'close'])

const isSubmitting = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  status: 'active'
})

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  status: ''
})

const isEdit = computed(() => !!props.vendor)

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const isValidPhone = (phone) => {
  return /^[\d\s\-\+\(\)]+$/.test(phone) && phone.length >= 7
}

const validateField = (field) => {
  errors[field] = ''
  
  switch (field) {
    case 'name':
      if (!form.name.trim()) {
        errors.name = 'Vendor name is required'
      } else if (form.name.trim().length < 2) {
        errors.name = 'Vendor name must be at least 2 characters'
      } else if (form.name.trim().length > 100) {
        errors.name = 'Vendor name must be less than 100 characters'
      }
      break
      
    case 'email':
      if (!form.email.trim()) {
        errors.email = 'Email is required'
      } else if (!isValidEmail(form.email.trim())) {
        errors.email = 'Please enter a valid email address'
      } else if (form.email.trim().length > 100) {
        errors.email = 'Email must be less than 100 characters'
      }
      break
      
    case 'phone':
      if (!form.phone.trim()) {
        errors.phone = 'Phone number is required'
      } else if (!isValidPhone(form.phone.trim())) {
        errors.phone = 'Please enter a valid phone number'
      } else if (form.phone.trim().length > 20) {
        errors.phone = 'Phone number must be less than 20 characters'
      }
      break
      
    case 'status':
      if (!form.status) {
        errors.status = 'Status is required'
      }
      break
  }
}

const validateForm = () => {
  const fields = ['name', 'email', 'phone', 'status']
  fields.forEach(field => validateField(field))
  
  return !Object.values(errors).some(error => error !== '')
}

const submit = async () => {
  
  if (!validateForm()) {
    return
  }


  isSubmitting.value = true

  try {
    const vendorData = { 
      name: form.name.trim(),
      email: form.email.trim(),
      phone: form.phone.trim(),
      address: form.address.trim(),
      status: form.status || 'active'
    }
    
    emit('save', vendorData)
    
  } catch (error) {
    console.error('Error in submit:', error)
    
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = serverErrors[field][0]
        }
      })
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(
  () => props.vendor,
  (val) => {
    if (val) {
      Object.assign(form, {
        name: val.name || '',
        email: val.email || '',
        phone: val.phone || '',
        address: val.address || '',
        status: val.status || 'active'
      })
    } else {
      form.name = ''
      form.email = ''
      form.phone = ''
      form.address = ''
      form.status = 'active'
    }
    clearErrors()
  },
  { immediate: true }
)
</script>

<style scoped>
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

.vendor-modal h2 {
  margin-bottom: 1.5rem;
  color: #111827;
  font-size: 1.25rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.25rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9375rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error,
.form-group select.error {
  border-color: #ef4444;
}

.form-group input.error:focus,
.form-group select.error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 0.8125rem;
  margin-top: 0.375rem;
  font-weight: 500;
}

.select-wrapper {
  position: relative;
}

.form-group select {
  padding-right: 2.5rem;
  cursor: pointer;
  appearance: none;
  background: #ffffff;
}

.form-group select:hover {
  border-color: #667eea;
}

.select-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6b7280;
  font-size: 0.8rem;
}

.status-group {
  background: linear-gradient(135deg, #f8faff 0%, #e0e7ff 100%);
  border-left: 3px solid #667eea;
  border-radius: 0 12px 12px 8px;
  padding: 1rem 1.5rem;
  margin-top: 0.5rem;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn.primary {
  background: #111827;
  color: white;
}

.btn.primary:hover:not(:disabled) {
  background: #1f2937;
}

.btn.primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn.secondary {
  background: #f9fafb;
  color: #374151;
  border: 1px solid #e5e7eb;
}

.btn.secondary:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>