<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEdit ? 'Edit Invoice' : 'Add Invoice' }}</h2>
        <button @click="$emit('close')" class="close-btn">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label>Invoice Number *</label>
          <input
            v-model="form.number"
            type="text"
            placeholder="INV-001"
            required
            :class="{ 'error': errors.number }"
            @blur="validateField('number')"
            @input="onInputChange('number')"
            style="text-transform: uppercase"
          />
          <span v-if="errors.number" class="error-text">{{ errors.number }}</span>
        </div>

        <div class="form-group">
          <label>Vendor *</label>
          <select 
            v-model="form.vendorId" 
            required
            :class="{ 'error': errors.vendorId }"
            @blur="validateField('vendorId')"
            @change="onInputChange('vendorId')"
          >
            <option value="">Select a vendor</option>
            <option
              v-for="vendor in vendors"
              :key="vendor.id"
              :value="vendor.id"
            >
              {{ vendor.name }}
            </option>
          </select>
          <span v-if="errors.vendorId" class="error-text">{{ errors.vendorId }}</span>
        </div>

        <div class="form-group">
          <label>Amount (₱) *</label>
          <input
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            required
            :class="{ 'error': errors.amount }"
            @blur="validateField('amount')"
            @input="onInputChange('amount')"
          />
          <span v-if="errors.amount" class="error-text">{{ errors.amount }}</span>
        </div>

        <div class="form-group">
          <label>Status *</label>
          <select 
            v-model="form.status" 
            required
            :class="{ 'error': errors.status }"
            @blur="validateField('status')"
            @change="onInputChange('status')"
          >
            <option value="">Select status</option>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
            <option value="Overdue">Overdue</option>
          </select>
          <span v-if="errors.status" class="error-text">{{ errors.status }}</span>
        </div>

        <div class="form-group">
          <label>Invoice Date *</label>
          <input
            v-model="form.date"
            type="date"
            required
            :class="{ 'error': errors.date }"
            @blur="validateField('date')"
            @change="onInputChange('date')"
            :max="new Date().toISOString().split('T')[0]"
          />
          <span v-if="errors.date" class="error-text">{{ errors.date }}</span>
        </div>

        <div class="form-actions">
          <button 
            type="button" 
            @click="$emit('close')" 
            class="btn btn-secondary"
            :disabled="isSubmitting"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="btn btn-primary"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="spinner"></span>
            {{ isSubmitting ? (isEdit ? 'Updating...' : 'Saving...') : (isEdit ? 'Update' : 'Save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, computed, ref } from 'vue'
import { vendors } from '../data/mockData'

const props = defineProps({
  invoice: Object,
})

const emit = defineEmits(['save', 'close'])

const isSubmitting = ref(false)

const form = reactive({
  number: '',
  vendorId: '',
  amount: 0,
  status: 'Pending',
  date: '',
})

const errors = reactive({
  number: '',
  vendorId: '',
  amount: '',
  status: '',
  date: ''
})

const isEdit = computed(() => !!props.invoice)

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const validateField = (field) => {
  switch (field) {
    case 'number':
      const trimmedNumber = form.number.trim().toUpperCase()
      if (!trimmedNumber) {
        errors.number = 'Invoice number is required'
      } else if (trimmedNumber.length < 5) {
        errors.number = 'Invoice number must be at least 5 characters'
      } else if (trimmedNumber.length > 20) {
        errors.number = 'Invoice number must be less than 20 characters'
      } else if (!/^[A-Z]{2,6}[-]?[A-Z0-9]{2,10}$/.test(trimmedNumber)) {
        errors.number = 'Format: INV-001, ABC-12345, INV2023001 (letters, optional dash, numbers)'
      } else {
        errors.number = ''
      }
      form.number = trimmedNumber
      break
    case 'vendorId':
      if (!form.vendorId) {
        errors.vendorId = 'Please select a vendor'
      } else if (isNaN(Number(form.vendorId))) {
        errors.vendorId = 'Please select a valid vendor'
      } else {
        errors.vendorId = ''
      }
      break
    case 'amount':
      if (!form.amount || form.amount <= 0) {
        errors.amount = 'Amount must be greater than 0'
      } else if (form.amount < 0.01) {
        errors.amount = 'Minimum amount is ₱0.01'
      } else if (form.amount > 999999999.99) {
        errors.amount = 'Maximum amount is ₱999,999,999.99'
      } else if (!Number.isInteger(form.amount * 100)) {
        errors.amount = 'Amount can have maximum 2 decimal places'
      } else {
        errors.amount = ''
      }
      break
    case 'status':
      if (!form.status) {
        errors.status = 'Please select a status'
      } else if (!['Pending', 'Paid', 'Overdue'].includes(form.status)) {
        errors.status = 'Please select a valid status'
      } else {
        errors.status = ''
      }
      break
    case 'date':
      if (!form.date) {
        errors.date = 'Invoice date is required'
      } else {
        const selectedDate = new Date(form.date)
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        const oneYearAgo = new Date()
        oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1)
        
        if (selectedDate > today) {
          errors.date = 'Invoice date cannot be in the future'
        } else if (selectedDate < oneYearAgo) {
          errors.date = 'Invoice date cannot be more than 1 year old'
        } else {
          errors.date = ''
        }
      }
      break
  }
}

const validateForm = () => {
  const fields = ['number', 'vendorId', 'amount', 'status', 'date']
  fields.forEach(field => validateField(field))
  
  return !Object.values(errors).some(error => error !== '')
}

const onInputChange = (field) => {
  if (errors[field]) {
    errors[field] = ''
  }

  if (field === 'number') {
    const value = form.number.trim().toUpperCase()
    form.number = value 
    
    if (value.length > 0 && value.length < 5) {
      errors.number = 'Too short - minimum 5 characters'
    } else if (value.length > 20) {
      errors.number = 'Too long - maximum 20 characters'
    } else if (value.length > 0 && !/^[A-Z]/.test(value)) {
      errors.number = 'Must start with letters'
    }
  }
  
  if (field === 'amount' && form.amount > 0) {
    if (form.amount > 999999999.99) {
      errors.amount = 'Maximum amount exceeded'
    } else if (!Number.isInteger(form.amount * 100)) {
      errors.amount = 'Maximum 2 decimal places'
    }
  }
  
  if (field === 'date' && form.date) {
    const selectedDate = new Date(form.date)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    if (selectedDate > today) {
      errors.date = 'Future dates not allowed'
    }
  }
}

const checkInvoiceNumberUniqueness = async (invoiceNumber) => {
  const existingInvoices = [
    { id: 1, number: 'INV-001' },
    { id: 2, number: 'INV-002' },
    { id: 3, number: 'INV-003' }
  ]

  if (props.invoice && props.invoice.number === invoiceNumber) {
    return true
  }

  await new Promise(resolve => setTimeout(resolve, 300))
  const isUnique = !existingInvoices.some(inv => inv.number === invoiceNumber)
  
  if (!isUnique) {
    errors.number = 'Invoice number already exists'
    return false
  }
  
  return true
}

const submit = async () => {
  if (!validateForm()) {
    const firstErrorField = Object.keys(errors).find(field => errors[field])
    if (firstErrorField) {
      const element = document.querySelector(`[v-model*="${firstErrorField}"], select[v-model*="${firstErrorField}"]`)
      if (element) element.focus()
    }
    return
  }

  isSubmitting.value = true

  try {
    if (!props.invoice) {
      const isUnique = await checkInvoiceNumberUniqueness(form.number.trim().toUpperCase())
      if (!isUnique) {
        isSubmitting.value = false
        return
      }
    }

    if (form.status === 'Paid' && !props.invoice) {
    }

    if (form.status === 'Overdue') {
      const invoiceDate = new Date(form.date)
      const today = new Date()
      const daysSinceInvoice = Math.floor((today - invoiceDate) / (1000 * 60 * 60 * 24))
      
      if (daysSinceInvoice < 30) {
        console.log('Invoice marked as overdue but is less than 30 days old')
      }
    }

    await new Promise(resolve => setTimeout(resolve, 800))
    
    emit('save', { 
      ...form,
      number: form.number.trim().toUpperCase(), 
      createdAt: props.invoice ? props.invoice.createdAt : new Date().toISOString(),
      updatedAt: new Date().toISOString()
    })
  } catch (error) {
    console.error('Error saving invoice:', error)
    
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = serverErrors[field][0]
        }
      })
    } else if (error.response?.status === 409) {
      errors.number = 'Invoice number already exists'
    } else {
      const errorMessage = error.response?.data?.message || 'An error occurred while saving the invoice'
      console.error(errorMessage)
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(
  () => props.invoice,
  (val) => {
    if (val) {
      Object.assign(form, val)
    } else {
      form.number = ''
      form.vendorId = ''
      form.amount = 0
      form.status = 'Pending'
      form.date = new Date().toISOString().split('T')[0]
    }
    clearErrors()
  },
  { immediate: true }
)

watch(
  () => form.number,
  (newValue) => {
    if (newValue) {
      form.number = newValue.toUpperCase().replace(/[^A-Z0-9-]/g, '')
    }
  }
)
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
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6b7280;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
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



.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
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

.btn-primary {
  background: #111827;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1f2937;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f9fafb;
  color: #374151;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
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

@media (max-width: 640px) {
  .modal-content {
    margin: 1rem;
    max-height: 95vh;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
