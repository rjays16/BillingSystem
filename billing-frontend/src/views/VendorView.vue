<template>
  <AppLayout>
    <div v-if="vendor" class="vendor-view">
      <div v-if="!vendor.invoices" class="loading-state">
        <i class="bi bi-arrow-repeat"></i>
        <span>Loading vendor data...</span>
      </div>
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
          <p>{{ vendor.invoices.length }}</p>
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
          <p>{{ vendor.createdDate || 'N/A' }}</p>
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
              <tr v-if="vendor.invoices.length === 0">
                <td colspan="4" class="empty">No invoices found for this vendor</td>
              </tr>
              
              <tr
                v-for="invoice in vendor.invoices"
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
                <td @click="goToInvoice(invoice.id)" class="clickable">{{ invoice.date }}</td>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { vendors, invoices } from '../data/mockData'
import { useToast } from '../composables/useToast'

const route = useRoute()
const router = useRouter()
const { show } = useToast()

const getVendorById = (id) => {
  try {
    return vendors.find(v => v.id === parseInt(id))
  } catch (error) {
    console.error('Error finding vendor:', error)
    return null
  }
}

const getVendorInvoices = (vendorId) => {
  try {
    return invoices.filter(inv => inv.vendorId === vendorId)
  } catch (error) {
    console.error('Error getting invoices:', error)
    return []
  }
}

const vendor = ref(getVendorById(route.params.id))

if (vendor.value) {
  vendor.value.invoices = getVendorInvoices(vendor.value.id)
}


const paidInvoices = computed(() => {
  try {
    if (!vendor.value?.invoices?.length) return 0
    return vendor.value.invoices.filter(inv => inv.status === 'Paid').length || 0
  } catch (error) {
    console.error('Error computing paid invoices:', error)
    return 0
  }
})

const pendingInvoices = computed(() => {
  try {
    if (!vendor.value?.invoices?.length) return 0
    return vendor.value.invoices.filter(inv => inv.status === 'Pending').length || 0
  } catch (error) {
    console.error('Error computing pending invoices:', error)
    return 0
  }
})

const overdueInvoices = computed(() => {
  try {
    if (!vendor.value?.invoices?.length) return 0
    return vendor.value.invoices.filter(inv => inv.status === 'Overdue').length || 0
  } catch (error) {
    console.error('Error computing overdue invoices:', error)
    return 0
  }
})

const totalRevenue = computed(() => {
  try {
    if (!vendor.value?.invoices?.length) return 0
    return vendor.value.invoices
      .filter(inv => inv.status === 'Paid')
      .reduce((sum, inv) => sum + inv.amount, 0)
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

const deleteInvoice = (invoice) => {
  if (confirm(`Are you sure you want to delete ${invoice.number}?`)) {
    show('Invoice deleted successfully', 'success')
  }
}

onMounted(() => {
  if (vendor.value) {
    vendor.value.invoices = getVendorInvoices(vendor.value.id)
  }
})

onBeforeUnmount(() => {
})
</script>

<style scoped>
.vendor-view {
  min-height: 100vh;
  padding: 2.5rem 3rem;
  background: #f8fafc;
  font-family: 'Inter', system-ui, sans-serif;
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
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
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
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
}

.table-wrapper {
  margin-top: 1.5rem;
  border-radius: 14px;
  overflow: hidden;
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
  cursor: pointer;
  transition: background 0.15s ease;
}

.invoice-table tbody tr:hover {
  background: #f3f4f6;
}

.invoice-row td.clickable {
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
}

.status.pending {
  background: #fef9c3;
  color: #854d0e;
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

.edit-btn {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.delete-btn {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
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
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #f9fafb;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

.loading-state i {
  font-size: 1.5rem;
  color: #667eea;
  animation: spin 1s linear infinite;
}

.loading-state span {
  margin-left: 0.75rem;
  font-size: 0.9rem;
  color: #6b7280;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .vendor-view {
    padding: 2rem 1.5rem;
  }
  
  .vendor-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .invoice-stats {
    flex-wrap: wrap;
  }
  
  .invoice-table th:nth-child(4),
  .invoice-table td:nth-child(4) {
    display: none;
  }
}

@media (max-width: 480px) {
  .vendor-view {
    padding: 1.5rem 1rem;
  }
  
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .invoice-stats {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .invoice-table th,
  .invoice-table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.85rem;
  }
}
</style>
