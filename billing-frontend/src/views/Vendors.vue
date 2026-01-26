<template>
  <AppLayout>
    <h1>Vendors</h1>
    <p class="subtitle">Registered vendors</p>

    <div class="table-wrapper">
      <table class="vendor-table">
        <thead>
          <tr>
            <th>Vendor</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Total Invoices</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="vendor in vendors"
            :key="vendor.id"
            @click="goToVendor(vendor.id)"
          >
            <td>{{ vendor.name }}</td>
            <td>{{ vendor.email }}</td>
            <td>{{ vendor.phone }}</td>
            <td>{{ vendor.invoices }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Nested view -->
    <RouterView />
  </AppLayout>
</template>

<script setup>
import AppLayout from '../layouts/AppLayout.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const vendors = ref([
  {
    id: 1,
    name: 'ABC Corp',
    email: 'billing@abccorp.com',
    phone: '+63 912 345 6789',
    invoices: 5,
  },
  {
    id: 2,
    name: 'XYZ Solutions',
    email: 'finance@xyz.com',
    phone: '+63 923 456 7890',
    invoices: 3,
  },
  {
    id: 3,
    name: 'Delta Services',
    email: 'accounts@delta.com',
    phone: '+63 934 567 8901',
    invoices: 8,
  },
])

const goToVendor = (id) => {
  router.push(`/vendors/${id}`)
}
</script>

<style scoped>
.subtitle {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.table-wrapper {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.vendor-table {
  width: 100%;
  border-collapse: collapse;
}

.vendor-table th,
.vendor-table td {
  padding: 1rem 1.25rem;
  text-align: left;
}

.vendor-table thead {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.vendor-table tbody tr {
  cursor: pointer;
  transition: background 0.15s ease;
}

.vendor-table tbody tr:hover {
  background: #f3f4f6;
}
</style>
