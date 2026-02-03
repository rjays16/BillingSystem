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
            placeholder="DOH-2024-001"
            required
            :class="{ 'error': errors.number }"
            @blur="validateField('number')"
            @input="onInputChange('number')"
          />
          <span v-if="errors.number" class="error-text">{{ errors.number }}</span>
        </div>

        <div class="form-group">
          <label>Vendor *</label>
          <select 
            v-model="form.vendor_id" 
            required
            :class="{ 'error': errors.vendor_id }"
            @blur="validateField('vendor_id')"
            @change="onInputChange('vendor_id')"
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
          <span v-if="errors.vendor_id" class="error-text">{{ errors.vendor_id }}</span>
        </div>

        <div class="form-group">
          <label>Amount (₱) *</label>
          <input
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0.01"
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
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
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

        <div class="form-group">
          <label>Due Date</label>
          <input
            v-model="form.due_date"
            type="date"
            :class="{ 'error': errors.due_date }"
            @blur="validateField('due_date')"
            @change="onInputChange('due_date')"
            :min="form.date || undefined"
          />
          <span v-if="errors.due_date" class="error-text">{{ errors.due_date }}</span>
        </div>

        <div class="form-group">
          <label>Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Additional notes (optional)"
            :class="{ 'error': errors.notes }"
            @blur="validateField('notes')"
            @input="onInputChange('notes')"
            maxlength="2000"
          ></textarea>
          <span v-if="errors.notes" class="error-text">{{ errors.notes }}</span>
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
import { reactive, watch, computed, ref, onMounted } from 'vue'
import { useVendorStore } from '../stores/vendor'

const props = defineProps({
  invoice: Object,
})

const emit = defineEmits(['save', 'close'])

const vendorStore = useVendorStore()
const isSubmitting = ref(false)

const vendors = computed(() => vendorStore.vendors)

const form = reactive({
  number: '',
  vendor_id: '',
  amount: 0,
  status: 'draft',
  date: '',
  due_date: '',
  notes: ''
})

const errors = reactive({
  number: '',
  vendor_id: '',
  amount: '',
  status: '',
  date: '',
  due_date: '',
  notes: ''
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
      const trimmedNumber = form.number.trim()
      if (!trimmedNumber) {
        errors.number = 'Invoice number is required'
      } else if (trimmedNumber.length < 3) {
        errors.number = 'Invoice number must be at least 3 characters'
      } else if (trimmedNumber.length > 255) {
        errors.number = 'Invoice number must be less than 255 characters'
      } else {
        errors.number = ''
      }
      break
      
    case 'vendor_id':
      if (!form.vendor_id) {
        errors.vendor_id = 'Please select a vendor'
      } else {
        errors.vendor_id = ''
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
      } else if (!['draft', 'sent', 'paid', 'overdue', 'cancelled'].includes(form.status)) {
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
        today.setHours(23, 59, 59, 999)
        
        if (selectedDate > today) {
          errors.date = 'Invoice date cannot be in the future'
        } else {
          errors.date = ''
        }
      }
      break
      
    case 'due_date':
      if (form.due_date && form.date) {
        const dueDate = new Date(form.due_date)
        const invoiceDate = new Date(form.date)
        
        if (dueDate < invoiceDate) {
          errors.due_date = 'Due date must be after or equal to invoice date'
        } else {
          errors.due_date = ''
        }
      }
      break
      
    case 'notes':
      if (form.notes && form.notes.length > 2000) {
        errors.notes = 'Notes cannot exceed 2000 characters'
      } else {
        errors.notes = ''
      }
      break
  }
}

const validateForm = () => {
  const fields = ['number', 'vendor_id', 'amount', 'status', 'date']
  fields.forEach(field => validateField(field))
  
  if (form.due_date) {
    validateField('due_date')
  }
  
  if (form.notes) {
    validateField('notes')
  }
  
  return !Object.values(errors).some(error => error !== '')
}

const onInputChange = (field) => {
  if (errors[field]) {
    errors[field] = ''
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
    today.setHours(23, 59, 59, 999)
    
    if (selectedDate > today) {
      errors.date = 'Future dates not allowed'
    }
    
    if (form.due_date) {
      validateField('due_date')
    }
  }
  
  if (field === 'due_date' && form.due_date && form.date) {
    const dueDate = new Date(form.due_date)
    const invoiceDate = new Date(form.date)
    
    if (dueDate < invoiceDate) {
      errors.due_date = 'Due date must be after invoice date'
    }
  }
}

const submit = async () => {
  console.log('🔵 InvoiceForm submit called!')
  
  if (!validateForm()) {
    console.log('❌ Validation failed:', errors)
    const firstErrorField = Object.keys(errors).find(field => errors[field])
    if (firstErrorField) {
      const element = document.querySelector(`[v-model*="${firstErrorField}"]`)
      if (element) element.focus()
    }
    return
  }

  console.log('✅ Validation passed, emitting save event...')

  isSubmitting.value = true

  try {
    const invoiceData = {
      number: form.number.trim(),
      vendor_id: Number(form.vendor_id),
      amount: Number(form.amount),
      status: form.status,
      date: form.date,
      due_date: form.due_date || null,
      notes: form.notes.trim() || null
    }
    
    console.log('📤 Sending invoice data:', invoiceData)
    
    emit('save', invoiceData)

  } catch (error) {
    console.error('💥 Error in submit:', error)
    
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = serverErrors[field][0]
        }
      })
    } else if (error.response?.status === 409) {
      errors.number = 'Invoice number already exists'
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(
  () => props.invoice,
  (val) => {
    if (val) {
      form.number = val.number || ''
      form.vendor_id = val.vendor_id || ''
      form.amount = val.amount || 0
      form.status = val.status || 'draft'
      form.date = val.date || ''
      form.due_date = val.due_date || ''
      form.notes = val.notes || ''
    } else {
      form.number = ''
      form.vendor_id = ''
      form.amount = 0
      form.status = 'draft'
      form.date = new Date().toISOString().split('T')[0]
      form.due_date = ''
      form.notes = ''
    }
    clearErrors()
  },
  { immediate: true }
)

onMounted(async () => {
  if (vendors.value.length === 0) {
    await vendorStore.loadVendors()
  }
})
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9375rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  font-family: inherit;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
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
  border-color: #ef4444;
}

.form-group input.error:focus,
.form-group select.error:focus,
.form-group textarea.error:focus {
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

.btn-secondary:hover:not(:disabled) {
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