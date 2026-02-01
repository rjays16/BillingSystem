<template>
  <AppLayout>
    <div v-if="!$route.params.id">
      <div class="page-header">
        <div>
          <h1>Vendors</h1>
          <p class="subtitle">Registered vendors</p>
        </div>

        <button class="btn primary" @click="openAdd">
          + Add Vendor
        </button>
      </div>

      <div class="table-wrapper">
        <div v-if="loading || vendorStore.loading" class="loading-spinner">
          <div class="spinner"></div>
          <p>Loading vendors...</p>
        </div>
        
        <table v-else>
          <thead>
            <tr>
              <th>Vendor</th>
              <th>Email</th>
              <th>Phone</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="vendor in vendorStore.vendors" :key="vendor.id" class="vendor-row" @click="goToVendor(vendor)">
              <td>{{ vendor.name }}</td>
              <td>{{ vendor.email }}</td>
              <td>{{ vendor.phone }}</td>
              <td class="actions" @click.stop.prevent>
                <button @click="editVendor(vendor)">Edit</button>
                <button class="danger" @click="askDelete(vendor)">
                  Delete
                </button>
              </td>
            </tr>

            <tr v-if="vendorStore.vendors.length === 0">
              <td colspan="4" class="empty">No vendors found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <VendorForm
        v-if="showForm"
        :vendor="selectedVendor"
        @save="saveVendor"
        @close="closeForm"
      />

      <ConfirmModal
        v-if="showConfirm"
        title="Delete Vendor"
        :message="`Are you sure you want to delete ${vendorToDelete?.name}?`"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
      />
    </div>
    <RouterView />
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import VendorForm from '../components/VendorForm.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToast } from '../composables/useToast'
import { useVendorStore } from '../stores/vendor'

const { show } = useToast()
const router = useRouter()
const vendorStore = useVendorStore()
const loading = ref(false)

const showForm = ref(false)
const showConfirm = ref(false)

const selectedVendor = ref(null)
const vendorToDelete = ref(null)

const openAdd = () => {
  selectedVendor.value = null
  showForm.value = true
}

const editVendor = (vendor) => {
  selectedVendor.value = { ...vendor }
  showForm.value = true
}

const goToVendor = (vendor) => {
  if (vendor && vendor.id) {
    router.push(`/vendors/${vendor.id}`)
  }
}

const closeForm = () => {
  showForm.value = false
  selectedVendor.value = null
}

const saveVendor = async (data) => {
  loading.value = true

  try {
    if (selectedVendor.value) {
      const result = await vendorStore.updateVendor(selectedVendor.value.id, data)
      
      if (result.success) {
        show('Vendor updated successfully', 'success')
        closeForm()
      } else {
        show(result.error || 'Failed to update vendor', 'error')
      }
    } else {
      const result = await vendorStore.createVendor(data)
      
      if (result.success) {
        show('Vendor created successfully', 'success')
        closeForm()
      } else {
        show(result.error || 'Failed to create vendor', 'error')
      }
    }
  } catch (error) {
    show(error.message || 'An error occurred', 'error')
  } finally {
    loading.value = false
  }
}

const confirmDelete = async () => {
  if (!vendorToDelete.value) return
  
  loading.value = true

  try {
    const result = await vendorStore.deleteVendor(vendorToDelete.value.id)
    
    if (result.success) {
      show('Vendor deleted successfully', 'success')
      cancelDelete()
    } else {
      show(result.error || 'Failed to delete vendor', 'error')
    }
  } catch (error) {
    show(error.message || 'An error occurred', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const result = await vendorStore.loadVendors()
  } catch (error) {
    console.error('Failed to load vendors:', error)
    show('Failed to load vendors', 'error')
  } finally {
    loading.value = false
  }
})

const askDelete = (vendor) => {
  vendorToDelete.value = vendor
  showConfirm.value = true
}


const cancelDelete = () => {
  vendorToDelete.value = null
  showConfirm.value = false
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.subtitle {
  color: #6b7280;
}

.table-wrapper {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 1rem;
}

thead {
  background: #f9fafb;
}

tbody tr:hover {
  background: #f3f4f6;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.vendor-row {
  cursor: pointer;
  transition: background 0.15s ease;
}

.vendor-row:hover {
  background: #f3f4f6;
}

.vendor-row td.clickable {
  position: relative;
}

.vendor-row td.clickable:hover::after {
  content: '→';
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  color: #667eea;
  font-size: 0.8rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.vendor-row td.clickable:hover::after {
  opacity: 1;
}

button {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

button.danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn.primary {
  background: #111827;
  color: white;
  padding: 0.6rem 1rem;
  border-radius: 8px;
}

.empty {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6b7280;
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  table th:nth-child(3),
  table td:nth-child(3) {
    display: none;
  }
}
</style>
