<template>
  <div class="invoice-view">
    <div v-if="!canAccessInvoice" class="access-denied">
      <i class="bi bi-shield-x"></i>
      <h2>Access Denied</h2>
      <p>You don't have permission to view this invoice.</p>
    </div>

    <div v-else class="invoice-content">
      <div class="invoice-header">
        <div>
          <h1>Invoice {{ invoice.number }}</h1>
          <p class="subtitle">Issued on {{ formatDate(invoice.date) }}</p>
          <div class="org-context">
            <i class="bi bi-building"></i>
            <span>{{ getOrganizationName(invoice.organization_id) }}</span>
          </div>
        </div>
 
        <div class="header-right">
          <span
            class="status"
            :class="invoice.status.toLowerCase()"
          >
            {{ invoice.status }}
          </span>
        </div>
      </div>

      <div class="detailed-info-grid">
        <div class="info-card">
          <h4><i class="bi bi-person"></i>Vendor Information</h4>
          <div class="vendor-details">
            <p class="vendor-name">{{ getVendorName(invoice.vendorId) }}</p>
            <p class="vendor-email">{{ getVendorEmail(invoice.vendorId) }}</p>
            <p class="vendor-phone">{{ getVendorPhone(invoice.vendorId) }}</p>
          </div>
        </div>
 
        <div class="info-card">
          <h4><i class="bi bi-currency-dollar"></i>Financial Details</h4>
          <div class="financial-details">
            <div class="detail-row">
              <span>Subtotal:</span>
              <span>₱{{ calculateSubtotal().toLocaleString() }}</span>
            </div>
            <div class="detail-row">
              <span>Tax (12%):</span>
              <span>₱{{ calculateTax().toLocaleString() }}</span>
            </div>
            <div class="detail-row total">
              <span><strong>Total Amount:</strong></span>
              <span><strong>₱{{ invoice.amount.toLocaleString() }}</strong></span>
            </div>
          </div>
        </div>
 
        <div class="info-card">
          <h4><i class="bi bi-calendar-check"></i>Timeline</h4>
          <div class="timeline-details">
            <div class="detail-row">
              <span>Issue Date:</span>
              <span>{{ formatDate(invoice.date) }}</span>
            </div>
            <div class="detail-row">
              <span>Due Date:</span>
              <span>{{ formatDate(invoice.dueDate) }}</span>
            </div>
            <div class="detail-row" v-if="invoice.paidDate">
              <span>Paid Date:</span>
              <span>{{ formatDate(invoice.paidDate) }}</span>
            </div>
          </div>
        </div>
      </div>
 
      <div class="items-section">
        <h2><i class="bi bi-receipt"></i>Invoice Items</h2>
 
        <table class="items-table">
          <thead>
            <tr>
              <th>Description</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th>Total</th>
              <th v-if="authStore.isAdmin">Actions</th>
            </tr>
          </thead>
 
          <tbody>
            <tr v-for="(item, index) in invoice.items" :key="index">
              <td>{{ item.description }}</td>
              <td>{{ item.qty }}</td>
              <td>₱{{ item.price.toLocaleString() }}</td>
              <td>₱{{ (item.qty * item.price).toLocaleString() }}</td>
              <td v-if="authStore.isAdmin" class="actions-cell">
                <button @click="editItem(index)" class="btn-icon" title="Edit item">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="deleteItem(index)" class="btn-icon danger" title="Delete item">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="invoice-actions">
        <div v-if="authStore.isAdmin" class="admin-actions">
          <button @click="editInvoice" class="btn primary">
            <i class="bi bi-pencil"></i>
            Edit Invoice
          </button>
          <button @click="printInvoice" class="btn secondary">
            <i class="bi bi-printer"></i>
            Print Invoice
          </button>
          <button @click="sendReminder" v-if="invoice.status === 'Pending'" class="btn warning">
            <i class="bi bi-envelope"></i>
            Send Reminder
          </button>
          <button @click="markAsPaid" v-if="invoice.status !== 'Paid'" class="btn success">
            <i class="bi bi-check-circle"></i>
            Mark as Paid
          </button>
          <button @click="deleteInvoice" class="btn danger">
            <i class="bi bi-trash"></i>
            Delete Invoice
          </button>
        </div>

        <div v-else class="accountant-actions">
          <button @click="printInvoice" class="btn secondary">
            <i class="bi bi-printer"></i>
            Print Invoice
          </button>
          <button @click="downloadInvoice" class="btn secondary">
            <i class="bi bi-download"></i>
            Download PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '../composables/useToast'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'
import { vendors } from '../data/mockData'

const route = useRoute()
const router = useRouter()
const { show } = useToast()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()

const invoice = ref({
  id: parseInt(route.params.id),
  number: `INV-${String(route.params.id).padStart(3, '0')}`,
  vendorId: 1,
  amount: 15000,
  status: 'Pending',
  date: '2026-01-10',
  dueDate: '2026-01-24',
  paidDate: null,
  organization_id: 1, 
  items: [
    {
      description: 'Software License Subscription - Annual',
      qty: 1,
      price: 10000,
    },
    {
      description: 'Premium Support & Maintenance Package',
      qty: 12,
      price: 416.67,
    },
  ],
})

const canAccessInvoice = computed(() => {
  if (!authStore.isAuthenticated || !organizationStore.currentOrganization) {
    return false
  }
  
  return invoice.value.organization_id === organizationStore.currentOrganization.id
})

const getVendorName = (vendorId) => {
  const vendor = vendors.find(v => v.id === vendorId)
  return vendor ? vendor.name : 'Unknown Vendor'
}

const getVendorEmail = (vendorId) => {
  const vendor = vendors.find(v => v.id === vendorId)
  return vendor ? vendor.email : 'N/A'
}

const getVendorPhone = (vendorId) => {
  const vendor = vendors.find(v => v.id === vendorId)
  return vendor ? vendor.phone : 'N/A'
}

const getOrganizationName = (orgId) => {
  const org = organizationStore.organizations.find(o => o.id === orgId)
  return org ? org.name : 'Unknown Organization'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not set'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const calculateSubtotal = () => {
  return invoice.value.items.reduce((sum, item) => sum + (item.qty * item.price), 0)
}

const calculateTax = () => {
  return calculateSubtotal() * 0.12 
}

const editInvoice = () => {
  show('Edit invoice feature coming soon!', 'info')
}

const deleteInvoice = () => {
  if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
    show('Invoice deleted successfully', 'success')
    router.push('/invoices')
  }
}

const printInvoice = () => {
  window.print()
  show('Print dialog opened', 'info')
}

const sendReminder = () => {
  show('Payment reminder sent to vendor', 'success')
}

const markAsPaid = () => {
  invoice.value.status = 'Paid'
  invoice.value.paidDate = new Date().toISOString().split('T')[0]
  show('Invoice marked as paid', 'success')
}

const downloadInvoice = () => {
  show('PDF download started', 'info')
}

const editItem = (index) => {
  show(`Edit item ${index + 1}`, 'info')
}

const deleteItem = (index) => {
  if (confirm(`Remove item: ${invoice.value.items[index].description}?`)) {
    invoice.value.items.splice(index, 1)
    show('Item removed from invoice', 'success')
  }
}

onMounted(() => {

})
</script>

<style scoped>
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

.invoice-content {
  max-width: 1000px;
}

.subtitle {
  color: #6b7280;
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
}

.org-context {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.org-context i {
  color: #667eea;
}

.status {
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status.paid {
  background: #dcfce7;
  color: #166534;
}

.status.pending {
  background: #fef9c3;
  color: #854d0e;
}

.status.overdue {
  background: #fee2e2;
  color: #991b1b;
}

.detailed-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.info-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  padding: 1.5rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.info-card h4 {
  font-size: 0.9rem;
  color: #374151;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.info-card h4 i {
  color: #667eea;
  font-size: 1rem;
}

.vendor-details p,
.financial-details p,
.timeline-details p {
  margin: 0.5rem 0;
  color: #374151;
}

.vendor-name {
  font-weight: 600;
  font-size: 1rem;
  color: #111827;
}

.vendor-email,
.vendor-phone {
  font-size: 0.875rem;
  color: #6b7280;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row.total {
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-weight: 600;
  color: #111827;
}

.items-section {
  margin-bottom: 2.5rem;
}

.items-section h2 {
  margin-bottom: 1rem;
  color: #111827;
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.items-section h2 i {
  color: #667eea;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.items-table th,
.items-table td {
  padding: 1rem 1.25rem;
  text-align: left;
}

.items-table th {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.items-table tbody tr:not(:last-child) {
  border-bottom: 1px solid #f3f4f6;
}

.items-table tbody tr:hover {
  background: #f9fafb;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  padding: 0.375rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6b7280;
}

.btn-icon:hover {
  background: #f3f4f6;
  color: #374151;
}

.btn-icon.danger:hover {
  background: #fee2e2;
  color: #dc2626;
}

.invoice-actions {
  display: flex;
  justify-content: flex-end;
  padding: 2rem;
  background: #f9fafb;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

.admin-actions,
.accountant-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn {
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
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

.btn.primary:hover {
  background: #1f2937;
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

.btn.success {
  background: #059669;
  color: white;
}

.btn.success:hover {
  background: #047857;
}

.btn.warning {
  background: #d97706;
  color: white;
}

.btn.warning:hover {
  background: #b45309;
}

.btn.danger {
  background: #dc2626;
  color: white;
}

.btn.danger:hover {
  background: #b91c1c;
}

/* Responsive */
@media (max-width: 768px) {
  .invoice-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .header-right {
    justify-content: flex-end;
  }
  
  .detailed-info-grid {
    grid-template-columns: 1fr;
  }
  
  .items-table th:nth-child(3),
  .items-table td:nth-child(3) {
    display: none;
  }
  
  .invoice-actions {
    justify-content: center;
  }
  
  .admin-actions,
  .accountant-actions {
    justify-content: center;
  }
  
  .btn {
    flex: 1;
    justify-content: center;
    min-width: 0;
  }
}

@media print {
  .invoice-actions {
    display: none;
  }
  
  .invoice-content {
    background: white;
  }
  
  .info-card {
    box-shadow: none;
    border: 1px solid #000;
  }
}
</style>
