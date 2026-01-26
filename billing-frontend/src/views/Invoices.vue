<template>
  <AppLayout>
    <div v-if="!$route.params.id">
      <h1>Invoices</h1>
      <p class="subtitle">List of issued invoices</p>

      <div class="table-wrapper">
        <table class="invoice-table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Vendor</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="invoice in invoices"
              :key="invoice.id"
              @click="goToInvoice(invoice.id)"
            >
              <td>{{ invoice.number }}</td>
              <td>{{ invoice.vendor }}</td>
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
    <RouterView />
  </AppLayout>
</template>

<script setup>
import AppLayout from '../layouts/AppLayout.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const invoices = ref([
  {
    id: 1,
    number: 'INV-001',
    vendor: 'ABC Corp',
    amount: 15000,
    status: 'Paid',
    date: '2026-01-10',
  },
  {
    id: 2,
    number: 'INV-002',
    vendor: 'XYZ Solutions',
    amount: 8200,
    status: 'Pending',
    date: '2026-01-12',
  },
  {
    id: 3,
    number: 'INV-003',
    vendor: 'Delta Services',
    amount: 12350,
    status: 'Overdue',
    date: '2026-01-15',
  },
])

const goToInvoice = (id) => {
  router.push(`/invoices/${id}`)
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
}

@media (max-width: 768px) {
  .invoice-table th:nth-child(3),
  .invoice-table td:nth-child(3),
  .invoice-table th:nth-child(5),
  .invoice-table td:nth-child(5) {
    display: none;
  }
}
</style>
