<template>
  <div class="vendor-view">
    <div class="header">
      <div>
        <h1>{{ vendor.name }}</h1>
        <p class="subtitle">{{ vendor.email }}</p>
      </div>
    </div>
    <div class="info-grid">
      <div class="info-card">
        <h4>Phone</h4>
        <p>{{ vendor.phone }}</p>
      </div>

      <div class="info-card">
        <h4>Total Invoices</h4>
        <p>{{ vendor.invoices.length }}</p>
      </div>
    </div>

    <h2 class="section-title">Invoices</h2>

    <div class="table-wrapper">
      <table class="invoice-table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="invoice in vendor.invoices"
            :key="invoice.id"
            @click="goToInvoice(invoice.id)"
          >
            <td>{{ invoice.number }}</td>
            <td>₱{{ invoice.amount.toLocaleString() }}</td>
            <td>
              <span
                class="status"
                :class="invoice.status.toLowerCase()"
              >
                {{ invoice.status }}
              </span>
            </td>
            <td>{{ invoice.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const vendor = ref({
  id: route.params.id,
  name: 'ABC Corp',
  email: 'billing@abccorp.com',
  phone: '+63 912 345 6789',
  invoices: [
    {
      id: 1,
      number: 'INV-001',
      amount: 15000,
      status: 'Paid',
      date: '2026-01-10',
    },
    {
      id: 2,
      number: 'INV-002',
      amount: 8200,
      status: 'Pending',
      date: '2026-01-12',
    },
  ],
})

const goToInvoice = (id) => {
  router.push(`/invoices/${id}`)
}
</script>

<style scoped>
.subtitle {
  color: #6b7280;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2.5rem;
}

.info-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
}

.info-card h4 {
  font-size: 0.85rem;
  color: #6b7280;
}

.section-title {
  margin-bottom: 1rem;
}

.table-wrapper {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
}

.invoice-table th,
.invoice-table td {
  padding: 1rem 1.25rem;
}

.invoice-table thead {
  background: #f9fafb;
}

.invoice-table tbody tr {
  cursor: pointer;
}

.invoice-table tbody tr:hover {
  background: #f3f4f6;
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
</style>
