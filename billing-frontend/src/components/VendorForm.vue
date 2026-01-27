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
              @input="onInputChange('name')"
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
              @input="onInputChange('email')"
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
              @input="onInputChange('phone')"
            />
            <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
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
})

const errors = reactive({
  name: '',
  email: '',
  phone: '',
})

const isEdit = computed(() => !!props.vendor)

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const validateField = (field) => {
  switch (field) {
    case 'name':
      const trimmedName = form.name.trim()
      if (!trimmedName) {
        errors.name = 'Vendor name is required'
      } else if (trimmedName.length < 2) {
        errors.name = 'Vendor name must be at least 2 characters'
      } else if (trimmedName.length > 100) {
        errors.name = 'Vendor name must be less than 100 characters'
      } else if (!/^[a-zA-Z0-9\s&.,'-]+$/.test(trimmedName)) {
        errors.name = 'Vendor name contains invalid characters'
      } else {
        errors.name = ''
      }
      form.name = trimmedName
      break
      
    case 'email':
      const trimmedEmail = form.email.trim()
      if (!trimmedEmail) {
        errors.email = 'Email is required'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmedEmail)) {
        errors.email = 'Please enter a valid email address'
      } else if (trimmedEmail.length > 255) {
        errors.email = 'Email is too long'
      } else {
        errors.email = ''
      }
      form.email = trimmedEmail
      break
      
    case 'phone':
      const trimmedPhone = form.phone.trim()
      if (!trimmedPhone) {
        errors.phone = 'Phone number is required'
      } else if (trimmedPhone.length < 7) {
        errors.phone = 'Phone number is too short'
      } else if (trimmedPhone.length > 20) {
        errors.phone = 'Phone number is too long'
      } else if (!/^[\d\s\-\+\(\)]+$/.test(trimmedPhone)) {
        errors.phone = 'Phone number can only contain digits, spaces, and +()-'
      } else if (!/^[\+]?[\d\s\-\(\)]+$/.test(trimmedPhone)) {
        errors.phone = 'Invalid phone number format'
      } else {
        errors.phone = ''
      }
      break
  }
}

const validateForm = () => {
  const fields = ['name', 'email', 'phone']
  fields.forEach(field => validateField(field))
  
  return !Object.values(errors).some(error => error !== '')
}

const onInputChange = (field) => {
  if (errors[field]) {
    errors[field] = ''
  }
  
  if (field === 'name' && form.name.length > 0) {
    if (form.name.length < 2) {
      errors.name = 'Too short - minimum 2 characters'
    } else if (!/^[a-zA-Z0-9\s&.,'-]+$/.test(form.name)) {
      errors.name = 'Only letters, numbers, and &.,- characters allowed'
    }
  }
  
  if (field === 'email' && form.email.length > 0) {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]*$/.test(form.email)) {
      errors.email = 'Invalid email format'
    }
  }
  
  if (field === 'phone' && form.phone.length > 0) {
    if (!/^[\d\s\-\+\(\)]+$/.test(form.phone)) {
      errors.phone = 'Only digits, spaces, and +()- allowed'
    } else if (form.phone.length < 7) {
      errors.phone = 'Too short'
    }
  }
}

const checkVendorUniqueness = async (vendorName, vendorEmail) => {
  const existingVendors = [
    { id: 1, name: 'ABC Corp', email: 'billing@abccorp.com' },
    { id: 2, name: 'XYZ Solutions', email: 'finance@xyz.com' },
    { id: 3, name: 'Delta Services', email: 'accounts@delta.com' }
  ]
  
  if (props.vendor) {
    if (props.vendor.name.toLowerCase() === vendorName.toLowerCase()) {
      return true
    }
    if (props.vendor.email.toLowerCase() === vendorEmail.toLowerCase()) {
      return true
    }
  }

  await new Promise(resolve => setTimeout(resolve, 300))
  const isUnique = !existingVendors.some(vendor => 
    vendor.name.toLowerCase() === vendorName.toLowerCase() || 
    vendor.email.toLowerCase() === vendorEmail.toLowerCase()
  )
  
  if (!isUnique) {
    const duplicateVendor = existingVendors.find(vendor => 
      vendor.name.toLowerCase() === vendorName.toLowerCase() || 
      vendor.email.toLowerCase() === vendorEmail.toLowerCase()
    )
    
    if (duplicateVendor.name.toLowerCase() === vendorName.toLowerCase()) {
      errors.name = 'Vendor name already exists'
    }
    if (duplicateVendor.email.toLowerCase() === vendorEmail.toLowerCase()) {
      errors.email = 'Email already exists'
    }
    
    return false
  }
  
  return true
}

const submit = async () => {
  if (!validateForm()) {
    const firstErrorField = Object.keys(errors).find(field => errors[field])
    if (firstErrorField) {
      const element = document.querySelector(`[v-model*="${firstErrorField}"]`)
      if (element) element.focus()
    }
    return
  }

  isSubmitting.value = true

  try {
    if (!props.vendor) {
      const isUnique = await checkVendorUniqueness(form.name.trim(), form.email.trim())
      if (!isUnique) {
        isSubmitting.value = false
        return
      }
    }
    
    await new Promise(resolve => setTimeout(resolve, 800))
    
    emit('save', { 
      ...form,
      name: form.name.trim(),
      email: form.email.trim(),
      phone: form.phone.trim(),
      createdAt: props.vendor ? props.vendor.createdAt : new Date().toISOString(),
      updatedAt: new Date().toISOString()
    })
  } catch (error) {
    console.error('Error saving vendor:', error)
    
    // Handle different error scenarios
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = serverErrors[field][0]
        }
      })
    } else if (error.response?.status === 409) {
      errors.name = 'Vendor already exists'
      errors.email = 'Email already registered'
    } else {
      const errorMessage = error.response?.data?.message || 'An error occurred while saving vendor'
      console.error(errorMessage)
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(
  () => props.vendor,
  (val) => {
    if (val) {
      Object.assign(form, val)
    } else {
      form.name = ''
      form.email = ''
      form.phone = ''
    }
    clearErrors()
  },
  { immediate: true }
)
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
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9375rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error {
  border-color: #ef4444;
}

.form-group input.error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 0.8125rem;
  margin-top: 0.375rem;
  font-weight: 500;
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

.btn.secondary:hover {
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
  margin-right: 0.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
