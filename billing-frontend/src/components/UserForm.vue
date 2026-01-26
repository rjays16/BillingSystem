<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEdit ? 'Edit User' : 'Add New User' }}</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>



      <form @submit.prevent="handleSubmit" class="user-form">
        <div class="form-group">
          <label>Full Name *</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter full name"
            required
            :class="{ 'error': errors.name }"
            @blur="validateField('name')"
            @input="onInputChange('name')"
          />
          <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
        </div>

        <div class="form-group">
          <label>Email Address *</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="name@example.com"
            required
            :class="{ 'error': errors.email }"
            @blur="validateField('email')"
            @input="onInputChange('email')"
          />
          <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label>Password *</label>
          <input
            v-model="form.password"
            type="password"
            :placeholder="isEdit ? 'Leave blank to keep current password' : 'Enter password'"
            :required="!isEdit"
            :class="{ 'error': errors.password }"
            @blur="validateField('password')"
            @input="onInputChange('password')"
          />
          <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
          <div v-if="!props.user" class="password-hints">
            <small class="hint-text">
              Password must contain: 8+ characters, uppercase, lowercase, number, and special character
            </small>
          </div>
        </div>

        <div class="form-group">
          <label>Role *</label>
          <select
            v-model="form.role"
            required
            :class="{ 'error': errors.role }"
            @blur="validateField('role')"
            @change="onInputChange('role')"
          >
            <option value="">Select a role</option>
            <option value="admin">Admin</option>
            <option value="accountant">Accountant</option>
          </select>
          <span v-if="errors.role" class="error-text">{{ errors.role }}</span>
        </div>

        <div class="form-group">
          <label>Organization *</label>
          <select
            v-model="form.organization_id"
            required
            :class="{ 'error': errors.organization_id }"
            @blur="validateField('organization_id')"
            @change="onInputChange('organization_id')"
          >
            <option value="">Select an organization</option>
            <option 
              v-for="org in organizations" 
              :key="org.id" 
              :value="org.id"
            >
              {{ org.name }}
            </option>
          </select>
          <span v-if="errors.organization_id" class="error-text">{{ errors.organization_id }}</span>
        </div>

        <div class="form-group" v-if="isEdit">
          <label>Status</label>
          <select
            v-model="form.status"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="$emit('close')">
            Cancel
          </button>
          <button type="submit" class="btn-save" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner"></span>
            {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update User' : 'Add User') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  organizations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['save', 'close'])

const isSubmitting = ref(false)
const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  organization_id: '',
  status: 'active'
})

const errors = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  organization_id: ''
})

const isEdit = computed(() => !!props.user)

const clearErrors = () => {
  errors.value = {
    name: '',
    email: '',
    password: '',
    role: '',
    organization_id: ''
  }
}

const validateField = (field) => {
  switch (field) {
    case 'name':
      if (!form.value.name.trim()) {
        errors.value.name = 'Name is required'
      } else if (form.value.name.trim().length < 2) {
        errors.value.name = 'Name must be at least 2 characters long'
      } else if (form.value.name.trim().length > 100) {
        errors.value.name = 'Name must be less than 100 characters'
      } else if (!/^[a-zA-Z\s.'-]+$/.test(form.value.name.trim())) {
        errors.value.name = 'Name can only contain letters, spaces, dots, hyphens, and apostrophes'
      } else {
        errors.value.name = ''
      }
      break
    case 'email':
      if (!form.value.email.trim()) {
        errors.value.email = 'Email is required'
      } else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(form.value.email)) {
        errors.value.email = 'Please enter a valid email address'
      } else if (form.value.email.length > 254) {
        errors.value.email = 'Email address is too long'
      } else {
        errors.value.email = ''
      }
      break
    case 'password':
      if (!props.user && !form.value.password) {
        errors.value.password = 'Password is required'
      } else if (form.value.password && form.value.password.length < 8) {
        errors.value.password = 'Password must be at least 8 characters long'
      } else if (form.value.password && form.value.password.length > 128) {
        errors.value.password = 'Password must be less than 128 characters'
      } else if (form.value.password && !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(form.value.password)) {
        errors.value.password = 'Password must contain at least one uppercase letter, one lowercase letter, and one number'
      } else if (form.value.password && !/(?=.*[!@#$%^&*(),.?":{}|<>])/.test(form.value.password)) {
        errors.value.password = 'Password must contain at least one special character'
      } else {
        errors.value.password = ''
      }
      break
    case 'role':
      if (!form.value.role) {
        errors.value.role = 'Role is required'
      } else if (!['admin', 'accountant'].includes(form.value.role)) {
        errors.value.role = 'Please select a valid role'
      } else {
        errors.value.role = ''
      }
      break
    case 'organization_id':
      if (!form.value.organization_id) {
        errors.value.organization_id = 'Organization is required'
      } else if (isNaN(Number(form.value.organization_id))) {
        errors.value.organization_id = 'Please select a valid organization'
      } else {
        errors.value.organization_id = ''
      }
      break
  }
}

const validateForm = () => {
  const fields = ['name', 'email', 'role', 'organization_id']
  if (!props.user) fields.push('password')
  
  fields.forEach(field => validateField(field))
  
  return !Object.values(errors.value).some(error => error !== '')
}

const onInputChange = (field) => {

  if (errors.value[field]) {
    errors.value[field] = ''
  }
  
  if (field === 'name' && form.value.name.length > 0) {
    if (form.value.name.length < 2) {
      errors.value.name = 'Name must be at least 2 characters long'
    } else if (form.value.name.length > 100) {
      errors.value.name = 'Name must be less than 100 characters'
    } else if (!/^[a-zA-Z\s.'-]+$/.test(form.value.name.trim())) {
      errors.value.name = 'Name can only contain letters, spaces, dots, hyphens, and apostrophes'
    }
  }
  
  if (field === 'email' && form.value.email.length > 3) {
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(form.value.email)) {
      errors.value.email = 'Please enter a valid email address'
    } else if (form.value.email.length > 254) {
      errors.value.email = 'Email address is too long'
    }
  }

  if (field === 'password' && form.value.password.length > 0) {
    if (form.value.password.length < 8) {
      errors.value.password = 'Password must be at least 8 characters long'
    } else if (form.value.password.length > 128) {
      errors.value.password = 'Password must be less than 128 characters'
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(form.value.password)) {
      errors.value.password = 'Password must contain uppercase, lowercase, and number'
    } else if (!/(?=.*[!@#$%^&*(),.?":{}|<>])/.test(form.value.password)) {
      errors.value.password = 'Password must contain at least one special character'
    }
  }
  
  if (field === 'role' && form.value.role) {
    if (!['admin', 'accountant'].includes(form.value.role)) {
      errors.value.role = 'Please select a valid role'
    }
  }
  
  if (field === 'organization_id' && form.value.organization_id) {
    if (isNaN(Number(form.value.organization_id))) {
      errors.value.organization_id = 'Please select a valid organization'
    }
  }
}

const checkEmailUniqueness = async () => {
  return true 
}

const handleSubmit = async () => {
  if (!validateForm()) {
    const firstErrorField = Object.keys(errors.value).find(field => errors.value[field])
    if (firstErrorField) {
      const element = document.querySelector(`[v-model*="${firstErrorField}"]`)
      if (element) element.focus()
    }
    return
  }

  isSubmitting.value = true

  try {
    if (!props.user || form.value.email !== props.user.email) {
      const isEmailUnique = await checkEmailUniqueness()
      if (!isEmailUnique) {
        errors.value.email = 'Email address is already in use'
        isSubmitting.value = false
        return
      }
    }

    await new Promise(resolve => setTimeout(resolve, 1000))
    const submitData = { ...form.value }
    if (props.user && !submitData.password) {
      delete submitData.password
    }
    
    emit('save', submitData)
  } catch (error) {
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.value.hasOwnProperty(field)) {
          errors.value[field] = serverErrors[field][0]
        }
      })
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(() => props.user, (newUser) => {
  if (newUser) {
    form.value = { ...newUser }
    form.value.password = ''
  } else {
    form.value = {
      name: '',
      email: '',
      password: '',
      role: '',
      organization_id: '',
      status: 'active'
    }
  }
  clearErrors()
}, { immediate: true })
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease-out;
  margin: 20px;
  position: relative;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  padding: 0.5rem;
}

.user-form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group input,
.form-group select {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: border-color 0.2s ease;
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

.error-text {
  color: #ef4444;
  font-size: 0.8125rem;
  margin-top: 0.375rem;
}

.password-hints {
  margin-top: 0.25rem;
}

.hint-text {
  color: #6b7280;
  font-size: 0.75rem;
  font-style: italic;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: 2px solid #e5e7eb;
  background: white;
  color: #6b7280;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #f3f4f6;
}

.btn-save {
  flex: 2;
  padding: 0.75rem 1.5rem;
  border: none;
  background: #667eea;
  color: white;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-save:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
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