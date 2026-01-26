<template>
  <AppLayout>
    <div class="dashboard-wrapper">
      <header class="dashboard-header">
        <div>
          <h1>Dashboard</h1>
          <p class="subtitle">Overview of your billing system</p>
        </div>
      </header>

      <section class="stats-grid">
        <div class="stat-card">
          <h3>Total Vendors</h3>
          <p class="stat-value">{{ totalVendors }}</p>
          <span class="stat-hint">Registered vendors</span>
        </div>

        <div class="stat-card">
          <h3>Total Invoices</h3>
          <p class="stat-value">{{ totalInvoices }}</p>
          <span class="stat-hint">Issued invoices</span>
        </div>

        <div class="stat-card">
          <h3>Pending Payments</h3>
          <p class="stat-value">₱{{ pendingPayments.toLocaleString() }}</p>
          <span class="stat-hint">Awaiting payment</span>
        </div>
      </section>

      <section class="content-section">
        <h2>Activity</h2>

        <div v-if="activities.length === 0" class="empty-state">
          <p>No activity to display yet.</p>
          <span>This section will show recent actions once data is available.</span>
        </div>

        <ul v-else class="activity-list">
          <li v-for="activity in activities" :key="activity.id">
            <span class="activity-message">{{ activity.message }}</span>
            <span class="activity-time">{{ activity.time }}</span>
          </li>
        </ul>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import { vendors, invoices } from '../data/mockData'


const totalVendors = computed(() => vendors.length)
const totalInvoices = computed(() => invoices.length)

const pendingPayments = computed(() =>
  invoices
    .filter(inv => inv.status !== 'Paid')
    .reduce((sum, inv) => sum + inv.amount, 0)
)


const activities = [
  { id: 1, message: 'Invoice INV-001 created', time: '2 hours ago' },
  { id: 2, message: 'Vendor ABC Corp added', time: 'Yesterday' },
  { id: 3, message: 'Payment received for INV-003', time: '2 days ago' },
]
</script>

<style scoped>
.dashboard-wrapper {
  min-height: 100vh;
  padding: 2.5rem 3rem;
  background: #f8fafc;
  font-family: 'Inter', system-ui, sans-serif;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 1.75rem;
  border: 1px solid #e5e7eb;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.stat-card h3 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin: 0.5rem 0;
}

.stat-hint {
  font-size: 0.8rem;
  color: #9ca3af;
}

.content-section {
  background: #ffffff;
  border-radius: 14px;
  padding: 2rem;
  border: 1px solid #e5e7eb;
}

.content-section h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
}

/* Activity */
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
}

.empty-state p {
  font-weight: 600;
  color: #374151;
}

.empty-state span {
  font-size: 0.85rem;
  color: #9ca3af;
}

.activity-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.activity-list li {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.activity-message {
  font-weight: 500;
  color: #374151;
}

.activity-time {
  font-size: 0.85rem;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-wrapper {
    padding: 2rem 1.5rem;
  }
}
</style>
