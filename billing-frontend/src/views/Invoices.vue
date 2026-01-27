<template>
  <AppLayout>
    <div v-if="!$route.params.id">
      <div class="page-header">
        <div>
          <h1>Invoices</h1>
          <p class="subtitle">List of issued invoices</p>
        </div>

        <button class="btn primary" @click="openAdd">
          + Add Invoice
        </button>
      </div>

      <div class="table-wrapper">
        <table class="invoice-table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Vendor</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="invoice in displayInvoices" :key="invoice.id">
              <td @click="goToInvoice(invoice.id)" class="clickable">{{ invoice.number }}</td>
              <td @click="goToInvoice(invoice.id)" class="clickable">{{ getVendorName(invoice.vendorId) }}</td>
              <td @click="goToInvoice(invoice.id)" class="clickable">₱{{ invoice.amount.toLocaleString() }}</td>
              <td @click="goToInvoice(invoice.id)" class="clickable">
                <span
                  class="status"
                  :class="invoice.status.toLowerCase()"
                >
                  {{ invoice.status }}
                </span>
              </td>
              <td @click="goToInvoice(invoice.id)" class="clickable">{{ invoice.date }}</td>
              <td class="actions">
                <button @click="editInvoice(invoice)">Edit</button>
                <button class="danger" @click="askDelete(invoice)">
                  Delete
                </button>
              </td>
            </tr>

            <tr v-if="displayInvoices.length === 0">
              <td colspan="6" class="empty">No invoices found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <InvoiceForm
        v-if="showForm"
        :invoice="selectedInvoice"
        @save="saveInvoice"
        @close="closeForm"
      />

      <ConfirmModal
        v-if="showConfirm"
        title="Delete Invoice"
        :message="`Are you sure you want to delete ${invoiceToDelete?.number}?`"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
      />
    </div>
    <RouterView />
  </AppLayout>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import InvoiceForm from '../components/InvoiceForm.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToast } from '../composables/useToast'
import { vendors } from '../data/mockData'

const router = useRouter()
const { show } = useToast()
const setLoading = inject('setLoading')

const invoices = ref([
  {
    id: 1,
    number: 'INV-001',
    vendorId: 1,
    amount: 15000,
    status: 'Paid',
    date: '2026-01-10',
  },
  {
    id: 2,
    number: 'INV-002',
    vendorId: 2,
    amount: 8200,
    status: 'Pending',
    date: '2026-01-12',
  },
  {
    id: 3,
    number: 'INV-003',
    vendorId: 3,
    amount: 12350,
    status: 'Overdue',
    date: '2026-01-15',
  },
])

const showForm = ref(false)
const showConfirm = ref(false)
const selectedInvoice = ref(null)
const invoiceToDelete = ref(null)

const displayInvoices = computed(() => invoices.value)

const getVendorName = (vendorId) => {
  const vendor = vendors.find(v => v.id === vendorId)
  return vendor ? vendor.name : 'Unknown Vendor'
}

const goToInvoice = (id) => {
  router.push(`/invoices/${id}`)
}

const openAdd = () => {
  selectedInvoice.value = null
  showForm.value = true
}

const editInvoice = (invoice) => {
  selectedInvoice.value = { ...invoice }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  selectedInvoice.value = null
}

const saveInvoice = async (data) => {
  setLoading(true)

  try {
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 800))
    
    if (selectedInvoice.value) {
      const index = invoices.value.findIndex(
        inv => inv.id === selectedInvoice.value.id
      )

      if (index !== -1) {
        invoices.value[index] = {
          ...invoices.value[index],
          ...data,
          updatedAt: new Date().toISOString()
        }
      }

      show('Invoice updated successfully', 'success')
    } else {
      // Check for duplicate invoice numbers
      const existingInvoice = invoices.value.find(inv => 
        inv.number.toLowerCase() === data.number.toLowerCase()
      )
      
      if (existingInvoice) {
        show('Invoice number already exists', 'error')
        setLoading(false)
        return
      }

      const newInvoice = {
        id: Date.now(),
        ...data,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
      }
      
      invoices.value.push(newInvoice)
      
      // Show more detailed success message
      show(`Invoice ${data.number} created successfully`, 'success')
    }

    closeForm()
  } catch (error) {
    console.error('Error saving invoice:', error)
    show('Failed to save invoice. Please try again.', 'error')
  } finally {
    setLoading(false)
  }
}

const askDelete = (invoice) => {
  invoiceToDelete.value = invoice
  showConfirm.value = true
}

const confirmDelete = () => {
  setLoading(true)

  setTimeout(() => {
    invoices.value = invoices.value.filter(
      inv => inv.id !== invoiceToDelete.value.id
    )

    show('Invoice deleted', 'error')

    invoiceToDelete.value = null
    showConfirm.value = false
    setLoading(false)
  }, 700)
}

const cancelDelete = () => {
  invoiceToDelete.value = null
  showConfirm.value = false
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

.table-wrapper {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
}

.invoice-table th,
.invoice-table td {
  padding: 1rem 1.25rem;
  text-align: left;
  font-size: 0.9rem;
}

.invoice-table thead {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.invoice-table tbody tr {
  transition: background 0.15s ease;
}

.invoice-table tbody tr:hover {
  background: #f3f4f6;
}

.invoice-table .clickable {
  cursor: pointer;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status.paid {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.status.pending {
  background: #fef9c3;
  color: #854d0e;
  border: 1px solid #fde68a;
}

.status.overdue {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  position: relative;
}

.status.overdue::before {
  content: '!';
  position: absolute;
  left: -6px;
  top: 50%;
  transform: translateY(-50%);
  background: #991b1b;
  color: white;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.actions button {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.actions button.danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn.primary {
  background: #111827;
  color: white;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
}

.btn.primary:hover {
  background: #1f2937;
}

.empty {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .invoice-table th:nth-child(3),
  .invoice-table td:nth-child(3),
  .invoice-table th:nth-child(6),
  .invoice-table td:nth-child(6) {
    display: none;
  }
}
</style>
