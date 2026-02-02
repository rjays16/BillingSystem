<template>
  <AppLayout>
    <div v-if="!$route.params.id">
      <div class="page-header">
        <div>
          <h1>Invoices</h1>
          <p class="subtitle">List of issued invoices</p>
        </div>

        <button 
          v-if="authStore.isAdmin" 
          class="btn primary" 
          @click="openAdd"
        >
          + Add Invoice
        </button>
      </div>

      <div class="context-bar" v-if="organizationStore.currentOrganization">
        <div class="org-context">
          <i class="bi bi-building"></i>
          <span>Showing invoices for: <strong>{{ organizationStore.organizationName }}</strong></span>
        </div>
        
        <div class="role-context">
          <i class="bi bi-shield-check"></i>
          <span>Role: <strong :class="authStore.userRole">{{ authStore.userRole }}</strong></span>
          <span class="access-level">
            ({{ authStore.isAdmin ? 'Full Access' : 'Read Only' }})
          </span>
        </div>
      </div>

      <div class="search-filter-section">
        <div class="search-controls">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Search invoices by number or vendor..."
              @input="resetToFirstPage"
            />
          </div>
          
          <div class="filter-controls">
            <div class="filter-group">
              <label for="status-filter">Status:</label>
              <select 
                id="status-filter"
                v-model="statusFilter"
                @change="resetToFirstPage"
              >
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="sent">Sent</option>
                <option value="overdue">Overdue</option>
              </select>
            </div>
            
            <button 
              class="btn secondary"
              @click="clearFilters"
              v-if="hasActiveFilters"
            >
              <i class="bi bi-x-circle"></i>
              Clear Filters
            </button>
          </div>
        </div>
        
        <div class="search-results" v-if="searchQuery || statusFilter">
          <span v-if="filteredInvoices.length === 0">No invoices found</span>
          <span v-else>
            Found {{ filteredInvoices.length }} invoice{{ filteredInvoices.length !== 1 ? 's' : '' }}
          </span>
        </div>
      </div>

      <div class="table-wrapper">
        <div v-if="loading" class="loading-spinner">
          <div class="spinner"></div>
          <p>Loading invoices...</p>
        </div>
        
        <table v-else class="invoice-table">
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
              <td @click="goToInvoice(invoice.id)" class="clickable">{{ getVendorName(invoice) }}</td>
              <td @click="goToInvoice(invoice.id)" class="clickable">₱{{ invoice.amount.toLocaleString() }}</td>
              <td @click="goToInvoice(invoice.id)" class="clickable">
                <span
                  class="status"
                  :class="getStatusClass(invoice.status)"
                >
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </td>
              <td @click="goToInvoice(invoice.id)" class="clickable">{{ invoice.date }}</td>
              <td class="actions" v-if="authStore.isAdmin">
                <button @click="editInvoice(invoice)">Edit</button>
                <button class="danger" @click="askDelete(invoice)">
                  Delete
                </button>
              </td>
              <td v-else class="actions">
                <span class="readonly">View Only</span>
              </td>
            </tr>

            <tr v-if="displayInvoices.length === 0">
              <td colspan="6" class="empty">No invoices found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-wrapper" v-if="invoices.length > 0">
        <div class="pagination-info">
          <span>Showing {{ paginationInfo.showing }} results</span>
        </div>
        
        <div class="pagination-controls">
          <div class="items-per-page">
            <label for="items-per-page">Show:</label>
            <select 
              id="items-per-page" 
              v-model="itemsPerPage" 
              @change="changeItemsPerPage(itemsPerPage)"
            >
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
          
          <div class="pagination-nav">
            <button 
              class="page-btn" 
              @click="previousPage" 
              :disabled="currentPage === 1"
            >
              <i class="bi bi-chevron-left"></i>
              Previous
            </button>
            
            <div class="page-numbers">
              <button 
                v-for="page in displayedPages" 
                :key="page"
                class="page-btn"
                :class="{ active: page === currentPage }"
                @click="goToPage(page)"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              class="page-btn" 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
            >
              Next
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
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
import { ref, inject, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import InvoiceForm from '../components/InvoiceForm.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToast } from '../composables/useToast'
import { apiEndpoints } from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'
import { useVendorStore } from '../stores/vendor'
import { onMounted } from 'vue'

const router = useRouter()
const { show } = useToast()
const loading = ref(false)
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()
const vendorStore = useVendorStore()

const invoices = ref([])

const showForm = ref(false)
const showConfirm = ref(false)
const selectedInvoice = ref(null)
const invoiceToDelete = ref(null)

const searchQuery = ref('')
const statusFilter = ref('')

const currentPage = ref(1)
const itemsPerPage = ref(10)

const totalPages = computed(() => Math.ceil(roleFilteredInvoices.value.length / itemsPerPage.value))

const tenantFilteredInvoices = computed(() => {
  if (!organizationStore.currentOrganization) {
    return []
  }
  
  return invoices.value.filter(invoice => 
    invoice.organization_id === organizationStore.currentOrganization.id
  )
})

const searchFilteredInvoices = computed(() => {
  let filtered = tenantFilteredInvoices.value
  
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(invoice => {
      const invoiceMatch = invoice.number.toLowerCase().includes(query)
      let vendorMatch = false
      if (invoice.vendor && invoice.vendor.name) {
        vendorMatch = invoice.vendor.name.toLowerCase().includes(query)
      } else {
        const vendor = vendorStore.vendorById(invoice.vendor_id)
        vendorMatch = vendor && vendor.name.toLowerCase().includes(query)
      }
      return invoiceMatch || vendorMatch
    })
  }
  
  if (statusFilter.value) {
    filtered = filtered.filter(invoice => invoice.status === statusFilter.value)
  }
  
  return filtered
})

const roleFilteredInvoices = computed(() => {
  return searchFilteredInvoices.value
})

const hasActiveFilters = computed(() => searchQuery.value || statusFilter.value)

const filteredInvoices = computed(() => searchFilteredInvoices.value)

const paginatedInvoices = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return roleFilteredInvoices.value.slice(start, end)
})

const displayInvoices = computed(() => paginatedInvoices.value)

const paginationInfo = computed(() => {
  const totalItems = roleFilteredInvoices.value.length
  const start = totalItems > 0 ? (currentPage.value - 1) * itemsPerPage.value + 1 : 0
  const end = Math.min(currentPage.value * itemsPerPage.value, totalItems)
  return {
    start: start,
    end: end,
    total: totalItems,
    showing: totalItems > 0 ? `${start}-${end} of ${totalItems}` : '0 items'
  }
})

const displayedPages = computed(() => {
  const delta = 2
  const range = []
  const rangeWithDots = []
  
  for (
    let i = Math.max(2, currentPage.value - delta);
    i <= Math.min(totalPages.value - 1, currentPage.value + delta);
    i++
  ) {
    range.push(i)
  }
  
  if (currentPage.value - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (currentPage.value + delta < totalPages.value - 1) {
    rangeWithDots.push('...', totalPages.value)
  } else if (totalPages.value > 1) {
    rangeWithDots.push(totalPages.value)
  }
  
  return totalPages.value === 1 ? [1] : rangeWithDots
})

const getVendorName = (invoice) => {
  if (invoice.vendor && invoice.vendor.name) {
    return invoice.vendor.name
  }
  const vendor = vendorStore.vendorById(invoice.vendor_id)
  return vendor ? vendor.name : '—'
}

const getStatusClass = (status) => {
  return status.toLowerCase()
}

const getStatusLabel = (status) => {
  const statusMap = {
    'draft': 'Draft',
    'sent': 'Sent', 
    'paid': 'Paid',
    'overdue': 'Overdue',
    'cancelled': 'Cancelled'
  }
  return statusMap[status] || status
}

const fetchInvoices = async () => {
  loading.value = true

  try {
    const response = await apiEndpoints.getInvoices()
    invoices.value = response.data.data ?? response.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const fetchVendors = async () => {
  try {
    await vendorStore.loadVendors()
  } catch (e) {
    console.error('Error loading vendors:', e)
  }
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
  loading.value = true

  try {
    if (selectedInvoice.value) {
      const response = await apiEndpoints.updateInvoice(
        selectedInvoice.value.id,
        data
      )

      const index = invoices.value.findIndex(
        inv => inv.id === selectedInvoice.value.id
      )

      if (index !== -1) {
        invoices.value[index] = response.data.data
      }

      window.dispatchEvent(new CustomEvent('dashboardRefresh'))
      
      show('Invoice updated successfully', 'success')
    } else {
      const response = await apiEndpoints.createInvoice(data)
      invoices.value.unshift(response.data.data)
      window.dispatchEvent(new CustomEvent('dashboardRefresh'))
      
      show('Invoice created successfully', 'success')
    }

    closeForm()
  } catch (error) {
    console.error(error)
    show(error.response?.data?.message || 'Failed to save invoice', 'error')
  } finally {
    loading.value = false
  }
}


const askDelete = (invoice) => {
  invoiceToDelete.value = invoice
  showConfirm.value = true
}

const confirmDelete = async () => {
  loading.value = true

  try {
    await apiEndpoints.deleteInvoice(invoiceToDelete.value.id)

    invoices.value = invoices.value.filter(
      inv => inv.id !== invoiceToDelete.value.id
    )

    show('Invoice deleted successfully', 'success')
  } catch (error) {
    console.error(error)
    show('Failed to delete invoice', 'error')
  } finally {
    invoiceToDelete.value = null
    showConfirm.value = false
    loading.value = false
  }
}


const cancelDelete = () => {
  invoiceToDelete.value = null
  showConfirm.value = false
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const previousPage = () => {
  goToPage(currentPage.value - 1)
}

const nextPage = () => {
  goToPage(currentPage.value + 1)
}

const changeItemsPerPage = (items) => {
  itemsPerPage.value = items
  currentPage.value = 1 
}

const resetToFirstPage = () => {
  currentPage.value = 1
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  currentPage.value = 1
}

onMounted(() => {
  fetchInvoices()
  fetchVendors()
})

watch(() => organizationStore.currentOrganization, () => {
  currentPage.value = 1
  clearFilters()
}, { deep: true })
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
  padding: 0.35rem 0.875rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.025em;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.status:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.status.paid {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: 1px solid #047857;
  font-weight: 600;
}

.status.sent {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: white;
  border: 1px solid #1e40af;
  font-weight: 600;
}

.status.overdue {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: 1px solid #dc2626;
  position: relative;
  font-weight: 600;
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
  border: 2px solid white;
}

.status.cancelled {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
  border: 1px solid #4b5563;
  position: relative;
  font-weight: 600;
}

.status.cancelled::before {
  content: '×';
  position: absolute;
  left: -6px;
  top: 50%;
  transform: translateY(-50%);
  background: #374151;
  color: white;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  border: 2px solid white;
}

.status.sent {
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

.status.cancelled {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  position: relative;
}

.status.cancelled::before {
  content: '×';
  position: absolute;
  left: -6px;
  top: 50%;
  transform: translateY(-50%);
  background: #6b7280;
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

.status.sent {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #93c5fd;
}

.status.draft {
  background: #f3f4f6;
  color: #6b7280;
  border: 1px solid #e5e7eb;
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

.context-bar {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.org-context, .role-context {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.org-context i, .role-context i {
  color: #667eea;
  font-size: 1rem;
}

.org-context strong, .role-context strong {
  color: #111827;
  font-weight: 600;
}

.role-context strong.admin {
  color: #059669;
}

.role-context strong.accountant {
  color: #dc2626;
}

.access-level {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
}

.readonly {
  color: #9ca3af;
  font-size: 0.8rem;
  font-style: italic;
  padding: 0.375rem 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.search-filter-section {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
}

.search-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  background: #f9fafb;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  flex: 1;
  max-width: 400px;
  transition: all 0.2s ease;
}

.search-box:focus-within {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: #ffffff;
}

.search-box i {
  color: #9ca3af;
  font-size: 1rem;
  margin-right: 0.75rem;
}

.search-box input {
  flex: 1;
  border: none;
  background: none;
  outline: none;
  font-size: 0.9rem;
  color: #374151;
}

.search-box input::placeholder {
  color: #9ca3af;
}

.filter-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
  font-weight: 500;
}

.filter-group select {
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #ffffff;
  font-size: 0.9rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-results {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #0c4a6e;
  font-weight: 500;
}

.search-results span {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-wrapper {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 1.25rem;
  margin-top: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.items-per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
}

.items-per-page select {
  padding: 0.375rem 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  font-size: 0.875rem;
  cursor: pointer;
}

.items-per-page select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.pagination-nav {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.page-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #ffffff;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 40px;
  justify-content: center;
}

.page-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.page-btn:active:not(:disabled) {
  transform: translateY(1px);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f9fafb;
}

.page-btn.active {
  background: #111827;
  border-color: #111827;
  color: #ffffff;
}

.page-btn.dots {
  border: none;
  background: none;
  cursor: default;
  padding: 0 0.5rem;
}

.page-btn i {
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .invoice-table th:nth-child(3),
  .invoice-table td:nth-child(3) {
    display: none;
  }
  
  .pagination-wrapper {
    flex-direction: column;
    align-items: stretch;
  }
  
  .pagination-controls {
    justify-content: space-between;
    flex-wrap: wrap;
  }
  
  .page-btn {
    padding: 0.375rem 0.5rem;
    font-size: 0.8rem;
  }
  
  .page-btn span {
    display: none;
  }
  
  .search-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box {
    max-width: 100%;
  }
  
  .filter-controls {
    justify-content: space-between;
  }
  
  .filter-group {
    flex: 1;
  }
  
  .filter-group select {
    width: 100%;
  }
}
</style>
