<template>
  <AppLayout>
    <div v-if="!authStore.isAdmin" class="access-denied">
      <i class="bi bi-shield-x"></i>
      <h2>Access Denied</h2>
      <p>Only administrators can access organization settings.</p>
    </div>

    <div v-else class="settings-container">
      <div class="page-header">
        <div>
          <h1>Organization Settings</h1>
          <p class="subtitle">Manage your organization configuration</p>
        </div>
      </div>

      <div class="org-context" v-if="organizationStore.currentOrganization">
        <i class="bi bi-building"></i>
        <span>Currently configuring: <strong>{{ organizationStore.organizationName }}</strong></span>
      </div>

      <form @submit.prevent="saveSettings" class="settings-form">
        <section class="form-section">
          <h3>Basic Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="orgName">Organization Name *</label>
              <input 
                id="orgName"
                v-model="formData.name" 
                type="text" 
                required
                :class="{ error: errors.name }"
              />
              <span class="error-message" v-if="errors.name">{{ errors.name }}</span>
            </div>

            <div class="form-group">
              <label for="orgCode">Organization Code *</label>
              <input 
                id="orgCode"
                v-model="formData.code" 
                type="text" 
                required
                maxlength="10"
                :class="{ error: errors.code }"
              />
              <span class="error-message" v-if="errors.code">{{ errors.code }}</span>
            </div>

            <div class="form-group full-width">
              <label for="orgDescription">Description</label>
              <textarea 
                id="orgDescription"
                v-model="formData.description" 
                rows="3"
                placeholder="Brief description of your organization..."
              ></textarea>
            </div>
          </div>
        </section>

        <section class="form-section">
          <h3>Contact Information</h3>
          <div class="form-grid">
            <div class="form-group full-width">
              <label for="orgAddress">Address</label>
              <input 
                id="orgAddress"
                v-model="formData.address" 
                type="text"
                placeholder="Complete address"
              />
            </div>

            <div class="form-group">
              <label for="orgPhone">Phone Number</label>
              <input 
                id="orgPhone"
                v-model="formData.phone" 
                type="tel"
                placeholder="+63 XXX XXX XXXX"
              />
            </div>

            <div class="form-group">
              <label for="orgEmail">Email Address</label>
              <input 
                id="orgEmail"
                v-model="formData.email" 
                type="email"
                placeholder="contact@organization.gov.ph"
                :class="{ error: errors.email }"
              />
              <span class="error-message" v-if="errors.email">{{ errors.email }}</span>
            </div>
          </div>
        </section>

        <section class="form-section">
          <h3>Billing Configuration</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="taxRate">Default Tax Rate (%)</label>
              <input 
                id="taxRate"
                v-model.number="formData.taxRate" 
                type="number"
                min="0"
                max="100"
                step="0.1"
              />
            </div>

            <div class="form-group">
              <label for="currency">Default Currency</label>
              <select id="currency" v-model="formData.currency">
                <option value="PHP">Philippine Peso (₱)</option>
                <option value="USD">US Dollar ($)</option>
                <option value="EUR">Euro (€)</option>
              </select>
            </div>

            <div class="form-group">
              <label for="paymentTerms">Payment Terms (days)</label>
              <input 
                id="paymentTerms"
                v-model.number="formData.paymentTerms" 
                type="number"
                min="1"
                max="365"
              />
            </div>
          </div>
        </section>

        <div class="form-actions">
          <button type="button" class="btn secondary" @click="resetForm">
            Reset
          </button>
          <button type="submit" class="btn primary" :disabled="isSaving">
            <i v-if="isSaving" class="bi bi-arrow-clockwise spinning"></i>
            {{ isSaving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '../composables/useToast'
import AppLayout from '../layouts/AppLayout.vue'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'

const { show } = useToast()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()

const isSaving = ref(false)
const errors = ref({})

const formData = ref({
  name: '',
  code: '',
  description: '',
  address: '',
  phone: '',
  email: '',
  taxRate: 12,
  currency: 'PHP',
  paymentTerms: 30
})

onMounted(() => {
  if (organizationStore.currentOrganization) {
    loadCurrentOrganization()
  }
})

const loadCurrentOrganization = () => {
  const org = organizationStore.currentOrganization
  formData.value = {
    name: org.name || '',
    code: org.code || '',
    description: org.description || '',
    address: org.address || '',
    phone: org.phone || '',
    email: org.email || '',
    taxRate: org.taxRate || 12,
    currency: org.currency || 'PHP',
    paymentTerms: org.paymentTerms || 30
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!formData.value.name.trim()) {
    errors.value.name = 'Organization name is required'
  }
  
  if (!formData.value.code.trim()) {
    errors.value.code = 'Organization code is required'
  } else if (formData.value.code.length < 2) {
    errors.value.code = 'Code must be at least 2 characters'
  }
  
  if (formData.value.email && !isValidEmail(formData.value.email)) {
    errors.value.email = 'Please enter a valid email address'
  }
  
  return Object.keys(errors.value).length === 0
}

const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const saveSettings = async () => {
  if (!validateForm()) {
    show('Please fix the errors before saving', 'error')
    return
  }
  
  isSaving.value = true
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const updatedOrg = {
      ...organizationStore.currentOrganization,
      ...formData.value
    }
    
    organizationStore.currentOrganization = updatedOrg
    localStorage.setItem('current-organization', JSON.stringify(updatedOrg))
    
    const orgIndex = organizationStore.organizations.findIndex(
      org => org.id === updatedOrg.id
    )
    if (orgIndex !== -1) {
      organizationStore.organizations[orgIndex] = updatedOrg
    }
    
    show('Organization settings saved successfully!', 'success')
  } catch (error) {
    console.error('Error saving settings:', error)
    show('Failed to save settings. Please try again.', 'error')
  } finally {
    isSaving.value = false
  }
}

const resetForm = () => {
  loadCurrentOrganization()
  errors.value = {}
  show('Form reset to current organization data', 'info')
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

.access-denied {
  text-align: center;
  padding: 4rem 2rem;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  margin: 2rem 0;
}

.access-denied i {
  font-size: 4rem;
  color: #dc2626;
  margin-bottom: 1rem;
}

.access-denied h2 {
  color: #111827;
  margin-bottom: 0.5rem;
}

.access-denied p {
  color: #6b7280;
}

.settings-container {
  max-width: 900px;
}

.org-context {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 10px;
  padding: 1rem 1.5rem;
  margin-bottom: 2rem;
  color: #0c4a6e;
  font-weight: 500;
}

.org-context i {
  color: #0284c7;
  font-size: 1.25rem;
}

.settings-form {
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

.form-section h3 {
  color: #111827;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-section h3::before {
  content: '';
  width: 4px;
  height: 20px;
  background: #667eea;
  border-radius: 2px;
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

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: #ffffff;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  color: #dc2626;
  font-size: 0.8rem;
  font-weight: 500;
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