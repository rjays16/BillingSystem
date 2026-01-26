<template>
  <AppLayout>
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
      <table>
        <thead>
          <tr>
            <th>Vendor</th>
            <th>Email</th>
            <th>Phone</th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="vendor in vendors" :key="vendor.id">
            <td>{{ vendor.name }}</td>
            <td>{{ vendor.email }}</td>
            <td>{{ vendor.phone }}</td>
            <td class="actions">
              <button @click="editVendor(vendor)">Edit</button>
              <button class="danger" @click="askDelete(vendor)">
                Delete
              </button>
            </td>
          </tr>

          <tr v-if="vendors.length === 0">
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
  </AppLayout>
</template>

<script setup>
import { ref, inject } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import VendorForm from '../components/VendorForm.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToast } from '../composables/useToast'

const { show } = useToast()
const setLoading = inject('setLoading')
const vendors = ref([
  {
    id: 1,
    name: 'ABC Corp',
    email: 'billing@abccorp.com',
    phone: '+63 912 345 6789',
  },
  {
    id: 2,
    name: 'XYZ Solutions',
    email: 'finance@xyz.com',
    phone: '+63 923 456 7890',
  },
  {
    id: 3,
    name: 'Delta Services',
    email: 'accounts@delta.com',
    phone: '+63 934 567 8901',
  },
])

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

const closeForm = () => {
  showForm.value = false
  selectedVendor.value = null
}

const saveVendor = (data) => {
  setLoading(true)

  setTimeout(() => {
    if (selectedVendor.value) {
      const index = vendors.value.findIndex(
        v => v.id === selectedVendor.value.id
      )

      if (index !== -1) {
        vendors.value[index] = {
          ...vendors.value[index],
          ...data,
        }
      }

      show('Vendor updated successfully', 'success')
    } else {
      vendors.value.push({
        id: Date.now(),
        ...data,
      })

      show('Vendor added successfully', 'success')
    }

    closeForm()
    setLoading(false)
  }, 800) 
}

const askDelete = (vendor) => {
  vendorToDelete.value = vendor
  showConfirm.value = true
}

const confirmDelete = () => {
  setLoading(true)

  setTimeout(() => {
    vendors.value = vendors.value.filter(
      v => v.id !== vendorToDelete.value.id
    )

    show('Vendor deleted', 'error')

    vendorToDelete.value = null
    showConfirm.value = false
    setLoading(false)
  }, 700)
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
</style>
