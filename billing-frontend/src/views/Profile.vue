<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1>Profile</h1>
        <p class="subtitle">Manage your personal profile information</p>
      </div>
      <button
  class="btn btn-primary"
  @click="saveProfile"
  :disabled="isSaving"
>
  {{ isSaving ? 'Saving...' : 'Save Changes' }}
</button>

    </div>

    <div class="profile-card">
      <div class="profile-header">
        <div class="user-avatar-large">
          {{ authStore.userAvatar }}
        </div>
        <div class="user-info-header">
          <h2>{{ authStore.userName }}</h2>
          <p class="user-role">
            <i class="bi bi-shield-check"></i>
            {{ authStore.userRole }} • {{ organizationStore.organizationName }}
          </p>
        </div>
      </div>
    </div>

    <form @submit.prevent="saveProfile" class="profile-form">
      <section class="form-section">
        <h3><i class="bi bi-person"></i>Personal Information</h3>
        <div class="form-grid">
          <div class="form-group">
            <label for="userName" class="required-label">
              Full Name
              <span class="required-asterisk">*</span>
            </label>
            <input 
              id="userName"
              v-model="formData.name" 
              type="text" 
              required
              placeholder="Enter your full name"
            />
            <span class="error-message" v-if="errors.name">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.name }}
            </span>
          </div>

          <div class="form-group">
            <label for="userEmail" class="required-label">
              Email Address
              <span class="required-asterisk">*</span>
            </label>
            <input 
              id="userEmail"
              v-model="formData.email" 
              type="email" 
              required
              placeholder="your.email@organization.gov.ph"
            />
            <span class="error-message" v-if="errors.email">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.email }}
            </span>
          </div>

          <div class="form-group">
            <label for="userPhone">Phone Number</label>
            <input 
              id="userPhone"
              v-model="formData.phone" 
              type="tel"
              placeholder="+63 XXX XXX XXXX"
            />
            <span class="error-message" v-if="errors.phone">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.phone }}
            </span>
          </div>
        </div>
      </section>

      <section class="password-section">
        <h3><i class="bi bi-lock"></i>Change Password</h3>
        <div class="form-grid">
          <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input 
              id="currentPassword"
              v-model="passwordData.currentPassword"
              type="password"
              placeholder="Enter your current password"
            />
            <span class="error-message" v-if="errors.currentPassword">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.currentPassword }}
            </span>
          </div>

          <div class="form-group">
            <label for="newPassword">New Password</label>
            <input 
              id="newPassword"
              v-model="passwordData.newPassword"
              type="password"
              placeholder="Enter new password (min 8 characters)"
            />
            <span class="error-message" v-if="errors.newPassword">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.newPassword }}
            </span>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <input 
              id="confirmPassword"
              v-model="passwordData.confirmPassword"
              type="password"
              placeholder="Confirm your new password"
            />
            <span class="error-message" v-if="errors.confirmPassword">
              <i class="bi bi-exclamation-circle"></i>
              {{ errors.confirmPassword }}
            </span>
          </div>
        </div>
      </section>

      <section class="form-section">
        <h3><i class="bi bi-info-circle"></i>Account Information</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Role</label>
            <div class="role-display">
              <i class="bi bi-shield-check"></i>
              <span class="role-badge" :class="authStore.userRole">
                {{ authStore.userRole }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label>Organization</label>
            <div class="org-display">
              <i class="bi bi-house"></i>
              <span>{{ organizationStore.organizationName }}</span>
            </div>
          </div>

          <div class="form-group">
            <label>Member Since</label>
            <div class="member-since">
              <i class="bi bi-calendar"></i>
              <span>{{ formatDate(authStore.user?.created_at) }}</span>
            </div>
          </div>
        </div>
      </section>

      <div class="form-actions">
        <button type="button" class="btn secondary" @click="resetForm">
          Reset
        </button>
        <button type="submit" class="btn primary" @click="saveProfile" :disabled="isSaving">
          <i v-if="isSaving" class="bi bi-arrow-clockwise spinning"></i>
          {{ isSaving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </AppLayout>

</template>
<script setup>
// import { useToast } from '../composables/useToast'
// import AppLayout from '../layouts/AppLayout.vue'
// import { useAuthStore } from '../stores/auth'
// import api, { apiEndpoints } from '../services/api'

import { ref, onMounted } from 'vue'
import { apiEndpoints } from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useToast } from '../composables/useToast'
import { useOrganizationStore } from '../stores/organization'

const { show } = useToast()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()

const isSaving = ref(false)
const errors = ref({})

const formData = ref({
  name: '',
  email: '',
  phone: ''
})

const passwordData = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

onMounted(() => {
  loadUserProfile()
  loadCurrentUserFromAPI()
})

const loadUserProfile = () => {
  if (authStore.user) {
    formData.value = {
      name: authStore.user.name || '',
      email: authStore.user.email || '',
      phone: authStore.user.phone || ''
    }
  }
}

const loadCurrentUserFromAPI = async () => {
  try {
    const response = await apiEndpoints.getCurrentUser()
    console.log('Fresh user data from API:', response.data)
    
    if (response.data && response.data.user) {
      authStore.user = response.data.user
      
      formData.value = {
        name: response.data.user.name || '',
        email: response.data.user.email || '',
        phone: response.data.user.phone || ''
      }
      
      console.log('Updated form data from API:', formData.value)
    }
  } catch (error) {
    console.error('Failed to load current user from API:', error)
  }
}

const validateField = (field) => {
  errors.value[field] = ''
  
  switch (field) {
    case 'name':
      if (!formData.value.name.trim()) {
        errors.value.name = 'Full name is required'
      } else if (formData.value.name.trim().length < 2) {
        errors.value.name = 'Name must be at least 2 characters'
      }
      break
      
    case 'email':
      if (!formData.value.email.trim()) {
        errors.value.email = 'Email address is required'
      } else if (!isValidEmail(formData.value.email)) {
        errors.value.email = 'Please enter a valid email address'
      }
      break
      
    case 'phone':
      if (formData.value.phone && !isValidPhone(formData.value.phone)) {
        errors.value.phone = 'Please enter a valid phone number'
      }
      break
  }
}

const validatePassword = (field) => {
  errors.value[field] = ''
  
  switch (field) {
    case 'currentPassword':
      if (passwordData.value.currentPassword && passwordData.value.currentPassword.length < 1) {
        errors.value.currentPassword = 'Current password is required'
      }
      break
      
    case 'newPassword':
      if (passwordData.value.newPassword && passwordData.value.newPassword.length < 8) {
        errors.value.newPassword = 'Password must be at least 8 characters'
      } else if (!isStrongPassword(passwordData.value.newPassword)) {
        errors.value.newPassword = 'Password must contain uppercase, lowercase, and numbers'
      }
      break
      
    case 'confirmPassword':
      if (passwordData.value.confirmPassword !== passwordData.value.newPassword) {
        errors.value.confirmPassword = 'Passwords do not match'
      }
      break
  }
}

const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const isValidPhone = (phone) => {
  const phoneRegex = /^(\+63|0)?[0-9]{10,11}$/
  return phoneRegex.test(phone.replace(/[\s-]/g, ''))
}

const isStrongPassword = (password) => {
  const hasUpper = /[A-Z]/.test(password)
  const hasLower = /[a-z]/.test(password)
  const hasNumber = /[0-9]/.test(password)
  return hasUpper && hasLower && hasNumber
}

const validateForm = () => {
  errors.value = {}
  
  validateField('name')
  validateField('email')
  validateField('phone')
  
  const hasPasswordData = passwordData.value.currentPassword || 
                        passwordData.value.newPassword || 
                        passwordData.value.confirmPassword
  
  if (hasPasswordData) {
    validatePassword('currentPassword')
    validatePassword('newPassword')
    validatePassword('confirmPassword')
  }
  
  return Object.keys(errors.value).length === 0
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not available'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const resetForm = () => {
  loadUserProfile()
  passwordData.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  errors.value = {}
  show('Form reset to current profile data', 'info')
}

const saveProfile = async () => {
  isSaving.value = true

  try {
    const payload = {
      name: formData.value.name,
      email: formData.value.email,
      phone: formData.value.phone,
    }

    // only send password fields if filled
    if (
      passwordData.value.currentPassword &&
      passwordData.value.newPassword
    ) {
      payload.current_password = passwordData.value.currentPassword
      payload.new_password = passwordData.value.newPassword
      payload.new_password_confirmation =
        passwordData.value.confirmPassword
    }

    const response = await apiEndpoints.updateProfile(payload)

    // update auth store
    authStore.setUser(response.data.user)

    show('Profile updated successfully', 'success')

    // clear password fields
    passwordData.value.currentPassword = ''
    passwordData.value.newPassword = ''
    passwordData.value.confirmPassword = ''
  } catch (error) {
    show(
      error.response?.data?.message || 'Failed to update profile',
      'error'
    )
  } finally {
    isSaving.value = false
  }
}


</script>

<style scoped>
.subtitle {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #111827;
  font-size: 1.75rem;
  font-weight: 700;
}

.profile-container {
  max-width: 900px;
}

.profile-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  color: white;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.user-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: 3px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.user-info-header h2 {
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.user-info-header p {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.user-info-header p i {
  color: rgba(255, 255, 255, 0.8);
}

.profile-form {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.form-section {
  padding: 2rem;
  border-bottom: 1px solid #f3f4f6;
}

.form-section:last-of-type {
  border-bottom: none;
}

.password-section {
  background: #f8faff;
  border-left: 4px solid #667eea;
}

.form-section h3 {
  color: #374151;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-section h3 i {
  color: #667eea;
  font-size: 1.1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.required-label {
  color: #374151;
}

.required-asterisk {
  color: #dc2626;
  font-weight: 700;
  font-size: 0.9rem;
}

.form-group.has-error label {
  color: #dc2626;
}

.required-asterisk.animation {
  animation: pulse 1s ease-in-out;
}

.form-group input {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: #ffffff;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.error-message {
  color: #dc2626;
  font-size: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.25rem;
}

.error-message i {
  font-size: 0.85rem;
}

.role-display,
.org-display,
.member-since {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #374151;
  font-weight: 500;
}

.role-display i,
.org-display i,
.member-since i {
  color: #667eea;
  font-size: 1rem;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: capitalize;
}

.role-badge.admin {
  background: #dcfce7;
  color: #166534;
}

.role-badge.accountant {
  background: #dbeafe;
  color: #1e40af;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 2rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  display: flex;
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
  opacity: 0.5;
  cursor: not-allowed;
}

.btn.secondary {
  background: #ffffff;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn.secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>