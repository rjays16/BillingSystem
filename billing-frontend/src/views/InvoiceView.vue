<template>
    <div class="invoice-view">
      <div class="invoice-header">
        <div>
          <h1>Invoice {{ invoice.number }}</h1>
          <p class="subtitle">Issued on {{ invoice.date }}</p>
        </div>

        <span
          class="status"
          :class="invoice.status.toLowerCase()"
        >
          {{ invoice.status }}
        </span>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <h4>Vendor</h4>
          <p>{{ invoice.vendor }}</p>
        </div>

        <div class="info-card">
          <h4>Total Amount</h4>
          <p>₱{{ invoice.amount.toLocaleString() }}</p>
        </div>

        <div class="info-card">
          <h4>Due Date</h4>
          <p>{{ invoice.dueDate }}</p>
        </div>
      </div>

      <div class="items-section">
        <h2>Invoice Items</h2>

        <table class="items-table">
          <thead>
            <tr>
              <th>Description</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(item, index) in invoice.items" :key="index">
              <td>{{ item.description }}</td>
              <td>{{ item.qty }}</td>
              <td>₱{{ item.price.toLocaleString() }}</td>
              <td>₱{{ (item.qty * item.price).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const invoice = ref({
  id: route.params.id,
  number: `INV-00${route.params.id}`,
  vendor: 'ABC Corp',
  amount: 15000,
  status: 'Paid',
  date: '2026-01-10',
  dueDate: '2026-01-20',
  items: [
    {
      description: 'Software subscription',
      qty: 1,
      price: 10000,
    },
    {
      description: 'Support & maintenance',
      qty: 1,
      price: 5000,
    },
  ],
})
</script>

<style scoped>
.subtitle {
  color: #6b7280;
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
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
  margin-bottom: 0.25rem;
}

.info-card p {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.items-section h2 {
  margin-bottom: 1rem;
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

.items-table thead {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.items-table tbody tr:not(:last-child) {
  border-bottom: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .items-table th:nth-child(3),
  .items-table td:nth-child(3) {
    display: none;
  }
}
</style>
