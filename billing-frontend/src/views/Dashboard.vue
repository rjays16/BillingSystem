<template>
  <div class="dashboard-wrapper">
    <header class="dashboard-header">
      <h1>Dashboard</h1>
      <p class="subtitle">Overview of your billing system</p>
    </header>

    <button class="logout-btn" @click="logout">
        Logout
    </button>

    <section class="stats-grid">
      <div class="stat-card">
        <h3>Total Vendors</h3>
        <p class="stat-value">—</p>
        <span class="stat-hint">Manage registered vendors</span>
      </div>

      <div class="stat-card">
        <h3>Invoices</h3>
        <p class="stat-value">—</p>
        <span class="stat-hint">Track issued invoices</span>
      </div>

      <div class="stat-card">
        <h3>Pending Payments</h3>
        <p class="stat-value">—</p>
        <span class="stat-hint">Invoices awaiting payment</span>
      </div>
    </section>

    <!-- Placeholder Content -->
    <section class="content-section">
      <h2>Activity</h2>
      <div class="empty-state">
        <p>No activity to display yet.</p>
        <span>This section will show recent actions once data is available.</span>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('isAuthenticated')
  router.push('/login')
}
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
  margin-bottom: 0.25rem;
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
  margin-bottom: 0.75rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
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
  color: #111827;
  margin-bottom: 1.25rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
}

.empty-state p {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.25rem;
}

.empty-state span {
  font-size: 0.85rem;
  color: #9ca3af;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logout-btn {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  background: transparent;
  border: 1px solid #111827;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #111827;
  color: #ffffff;
}


@media (max-width: 768px) {
  .dashboard-wrapper {
    padding: 2rem 1.5rem;
  }
}

</style>
