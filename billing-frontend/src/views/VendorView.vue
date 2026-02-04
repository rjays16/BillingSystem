<template>
  <AppLayout>
    <div v-if="!canAccessVendor" class="access-denied">
      <i class="bi bi-shield-x"></i>
      <h2>Access Denied</h2>
      <p>You don't have permission to view this vendor.</p>
    </div>

    <div v-else-if="loading" class="loading-state">
      <div class="loader"></div>
      <p>Loading vendor data...</p>
    </div>

    <div v-else-if="!vendor" class="not-found">
      <i class="bi bi-search"></i>
      <h2>Vendor Not Found</h2>
      <p>The vendor you're looking for doesn't exist or you don't have access to view it.</p>
      <button @click="router.push('/vendors')" class="btn primary">
        Back to Vendors
      </button>
    </div>

    <div v-else>
      <div class="vendor-header">
        <div class="vendor-info">
          <h1>{{ vendor.name }}</h1>
          <p class="subtitle">{{ vendor.email }}</p>
        </div>

        <span
          class="status-badge"
          :class="vendor.status ? vendor.status.toLowerCase() : 'active'"
        >
          {{ vendor.status || 'Active' }}
        </span>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <h4>
            <i class="bi bi-telephone"></i>
            Phone
          </h4>
          <p>{{ vendor.phone }}</p>
        </div>

        <div class="info-card">
          <h4>
            <i class="bi bi-envelope"></i>
            Email
          </h4>
          <p>{{ vendor.email }}</p>
        </div>

        <div class="info-card">
          <h4>
            <i class="bi bi-receipt"></i>
            Total Invoices
          </h4>
          <p>{{ vendorInvoices.length }}</p>
        </div>

        <div class="info-card">
          <h4>
            <i class="bi bi-currency-peso"></i>
            Total Revenue
          </h4>
          <p>₱{{ totalRevenue.toLocaleString() }}</p>
        </div>

        <div class="info-card">
          <h4>
            <i class="bi bi-calendar-check"></i>
            Created Date
          </h4>
          <p>{{ formatDate(vendor.created_at) }}</p>
        </div>
      </div>

      <div class="invoices-section">
        <div class="section-header">
          <h2 class="section-title">
            <i class="bi bi-receipt-cutoff"></i>
            Recent Invoices
          </h2>
          <div class="invoice-stats">
            <span class="stat">
              <span class="stat-value">{{ paidInvoices }}</span>
              <span class="stat-label">Paid</span>
            </span>
            <span class="stat pending">
              <span class="stat-value">{{ pendingInvoices }}</span>
              <span class="stat-label">Pending</span>
            </span>
            <span class="stat overdue">
              <span class="stat-value">{{ overdueInvoices }}</span>
              <span class="stat-label">Overdue</span>
            </span>
          </div>
        </div>

        <div class="table-wrapper">
          <table class="invoice-table">
            <thead>
              <tr>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="vendorInvoices.length === 0">
                <td colspan="5" class="empty">No invoices found for this vendor</td>
              </tr>
              
              <tr
                v-for="invoice in vendorInvoices"
                :key="invoice.id"
                class="invoice-row"
              >
                <td @click="goToInvoice(invoice.id)" class="clickable">{{ invoice.number }}</td>
                <td @click="goToInvoice(invoice.id)" class="clickable">₱{{ invoice.amount.toLocaleString() }}</td>
                <td @click="goToInvoice(invoice.id)" class="clickable">
                  <span
                    class="status"
                    :class="invoice.status.toLowerCase()"
                  >
                    {{ invoice.status }}
                  </span>
                </td>
                <td @click="goToInvoice(invoice.id)" class="clickable">{{ formatDate(invoice.date) }}</td>
                <td class="actions">
                  <button @click="editInvoice(invoice)" class="edit-btn">Edit</button>
                  <button @click="deleteInvoice(invoice)" class="delete-btn">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '../composables/useToast'
import { useVendorStore } from '../stores/vendor'
import { apiEndpoints } from '../services/api'
import AppLayout from '../layouts/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const { show } = useToast()
const vendorStore = useVendorStore()

const vendor = ref(null)
const vendorInvoices = ref([])
const loading = ref(true)
const canAccessVendor = ref(true)

const loadVendorData = async () => {
  loading.value = true
  
  try {
    const vendorId = route.params.id
    const result = await vendorStore.getVendor(vendorId)
    
    if (result.success) {
      vendor.value = result.data
    } else {
      canAccessVendor.value = false
    }
    
    const invoicesResponse = await apiEndpoints.getInvoices()
    vendorInvoices.value = invoicesResponse.data.data.filter(
      inv => inv.vendor_id === Number(vendorId)
    )
    
  } catch (error) {
    console.error('Error loading vendor data:', error)
    canAccessVendor.value = false
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const paidInvoices = computed(() => {
  try {
    if (!vendorInvoices.value?.length) return 0
    return vendorInvoices.value.filter(inv => inv.status === 'paid').length || 0
  } catch (error) {
    console.error('Error computing paid invoices:', error)
    return 0
  }
})

const pendingInvoices = computed(() => {
  try {
    if (!vendorInvoices.value?.length) return 0
    return vendorInvoices.value.filter(inv => inv.status === 'sent').length || 0
  } catch (error) {
    console.error('Error computing pending invoices:', error)
    return 0
  }
})

const overdueInvoices = computed(() => {
  try {
    if (!vendorInvoices.value?.length) return 0
    return vendorInvoices.value.filter(inv => inv.status === 'overdue').length || 0
  } catch (error) {
    console.error('Error computing overdue invoices:', error)
    return 0
  }
})

const totalRevenue = computed(() => {
  try {
    if (!vendorInvoices.value?.length) return 0
    return vendorInvoices.value
      .filter(inv => inv.status === 'paid')
      .reduce((sum, inv) => sum + Number(inv.amount), 0)
  } catch (error) {
    console.error('Error computing total revenue:', error)
    return 0
  }
})

const goToInvoice = (id) => {
  router.push(`/invoices/${id}`)
}

const editInvoice = (invoice) => {
  router.push(`/invoices`)
  setTimeout(() => {
    window.dispatchEvent(new CustomEvent('editInvoice', { detail: invoice }))
  }, 100)
}

const deleteInvoice = async (invoice) => {
  if (confirm(`Are you sure you want to delete ${invoice.number}?`)) {
    try {
      await apiEndpoints.deleteInvoice(invoice.id)
      vendorInvoices.value = vendorInvoices.value.filter(inv => inv.id !== invoice.id)
      show('Invoice deleted successfully', 'success')
    } catch (error) {
      console.error('Error deleting invoice:', error)
      show('Failed to delete invoice', 'error')
    }
  }
}

onMounted(() => {
  loadVendorData()
})
</script>

<style scoped>
.loading-state,
.not-found,
.access-denied {
  text-align: center;
  padding: 4rem 2rem;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  margin: 2rem 0;
}

.loading-state i,
.not-found i,
.access-denied i {
  font-size: 3rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.loading-state h2,
.not-found h2,
.access-denied h2 {
  color: #111827;
  margin-bottom: 0.5rem;
}

.loading-state p,
.not-found p,
.access-denied p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.loader {
  width: 2rem;
  height: 2rem;
  border: 2px solid #e5e7eb;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.vendor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.vendor-info h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  margin-top: 0.5rem;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.active {
  background: #dcfce7;
  color: #166534;
}

.status-badge.inactive {
  background: #f3f4f6;
  color: #6b7280;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.25rem;
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
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.info-card h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-card h4 i {
  font-size: 1.1rem;
  color: #667eea;
}

.info-card p {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.invoices-section {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  padding: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title i {
  font-size: 1.3rem;
  color: #667eea;
}

.invoice-stats {
  display: flex;
  gap: 1rem;
}

.stat {
  background: #f9fafb;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.table-wrapper {
  margin-top: 1.5rem;
  border-radius: 14px;
  overflow: hidden;
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
}

.invoice-table tbody tr {
  transition: background 0.15s ease;
  border-bottom: 1px solid #f3f4f6;
}

.invoice-table tbody tr:last-child {
  border-bottom: none;
}

.invoice-table tbody tr:hover {
  background: #f9fafb;
}

.invoice-row td.clickable {
  cursor: pointer;
}

.status {
  padding: 0.375rem 0.875rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  display: inline-block;
}

.status.paid {
  background: #dcfce7;
  color: #166534;
}

.status.sent {
  background: #dbeafe;
  color: #1e40af;
}

.status.draft {
  background: #f3f4f6;
  color: #6b7280;
}

.status.overdue {
  background: #fee2e2;
  color: #991b1b;
  position: relative;
  padding-left: 1.5rem;
}

.status.overdue::before {
  content: '!';
  position: absolute;
  left: 0.5rem;
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

.status.cancelled {
  background: #f3f4f6;
  color: #6b7280;
  text-decoration: line-through;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.edit-btn {
  background: #f3f4f6;
  color: #374151;
}

.edit-btn:hover {
  background: #e5e7eb;
}

.delete-btn {
  background: #fee2e2;
  color: #991b1b;
}

.delete-btn:hover {
  background: #fecaca;
}

.empty {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
  font-style: italic;
}

.btn.primary {
  background: #111827;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s ease;
}

.btn.primary:hover {
  background: #1f2937;
}

@media (max-width: 768px) {
  .vendor-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .invoice-stats {
    width: 100%;
    justify-content: space-between;
  }
  
  .invoice-table th:nth-child(4),
  .invoice-table td:nth-child(4) {
    display: none;
  }
}
</style>