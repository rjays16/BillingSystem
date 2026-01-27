<template>
  <AppLayout>
    <div class="dashboard-wrapper">
      <header class="dashboard-header">
        <div>
          <h1>Dashboard</h1>
          <p class="subtitle">Organization Overview - {{ organizationStore.organizationName }}</p>
        </div>
      
        <div class="context-info">
          <div class="org-info" v-if="organizationStore.currentOrganization">
            <i class="bi bi-building"></i>
            <div>
              <div class="org-name">{{ organizationStore.organizationName }}</div>
              <div class="user-welcome">
                Welcome back, <strong>{{ authStore.userName }}</strong>
                <span class="role-badge" :class="authStore.userRole">
                  {{ authStore.userRole }}
                </span>
              </div>
              <div class="access-info">
                <small>{{ authStore.isAdmin ? 'Full Access' : 'Read Only Access' }}</small>
              </div>
            </div>
          </div>
        </div>
      </header>

      <section class="context-banner" v-if="organizationStore.currentOrganization">
        <div class="banner-content">
          <i class="bi bi-shield-check"></i>
          <div>
            <strong>Data Isolation Active</strong>
            <span>Showing only {{ organizationStore.organizationName }} data</span>
          </div>
        </div>
        <div class="banner-stats">
          <span>{{ totalInvoices }} invoices</span>
          <span>₱{{ totalRevenue.toLocaleString() }} revenue</span>
        </div>
      </section>

      <section class="stats-grid">
        <div class="stat-card">
          <h3>Total Invoices</h3>
          <p class="stat-value">{{ totalInvoices }}</p>
          <span class="stat-hint">{{ organizationStore.organizationName }} invoices</span>
        </div>

        <div class="stat-card">
          <h3>Paid Invoices</h3>
          <p class="stat-value">{{ paidInvoices }}</p>
          <span class="stat-hint">Completed payments</span>
        </div>

        <div class="stat-card">
          <h3>Pending Payments</h3>
          <p class="stat-value">₱{{ pendingPayments.toLocaleString() }}</p>
          <span class="stat-hint">Awaiting payment</span>
        </div>

        <div class="stat-card">
          <h3>Overdue Invoices</h3>
          <p class="stat-value" style="color: #dc2626;">{{ overdueInvoices }}</p>
          <span class="stat-hint">Requires attention</span>
        </div>

        <div class="stat-card">
          <h3>Total Revenue</h3>
          <p class="stat-value" style="color: #059669;">₱{{ totalRevenue.toLocaleString() }}</p>
          <span class="stat-hint">Collected payments</span>
        </div>

        <div class="stat-card">
          <h3>Total Vendors</h3>
          <p class="stat-value">{{ totalVendors }}</p>
          <span class="stat-hint">Registered vendors</span>
        </div>
      </section>

      <section class="quick-actions-section" v-if="quickActions.length > 0">
        <h2>Quick Actions</h2>
        <div class="quick-actions-grid">
          <div 
            v-for="action in quickActions" 
            :key="action.label"
            class="action-card"
            :class="{ disabled: action.disabled }"
            @click="handleQuickAction(action)"
          >
            <i :class="action.icon"></i>
            <span>{{ action.label }}</span>
          </div>
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
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'
import { apiEndpoints } from '../services/api'

const router = useRouter()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()
const dashboardData = ref({})

const fetchDashboardData = async () => {
  try {
    // Fetch real data from API
    const invoicesResponse = await apiEndpoints.getInvoices()
    const organizationsResponse = await apiEndpoints.getOrganizations()
    const usersResponse = await apiEndpoints.getCurrentUser()
    
    return {
      invoices: invoicesResponse.data.data || [],
      organizations: organizationsResponse.data || [],
      currentUser: usersResponse.data.user || null,
    }
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
    return {
      invoices: [],
      organizations: [],
      currentUser: null,
    }
  }
      organization_id: organizationId,
      amount: Math.floor(Math.random() * 50000) + 5000,
      status: statuses[Math.floor(Math.random() * statuses.length)],
      date: new Date(2026, 0, Math.floor(Math.random() * 27) + 1).toISOString().split('T')[0]
    })
  }
  
  return invoices.sort((a, b) => new Date(b.date) - new Date(a.date))
})()

const organizationInvoices = computed(() => {
  if (!organizationStore.currentOrganization) {
    return []
  }
  return (dashboardData.value.invoices || []).filter(inv => 
    inv.organization_id === organizationStore.currentOrganization.id
  )
})

const totalVendors = computed(() => {
  return (dashboardData.value.organizations || []).length
})

const totalInvoices = computed(() => organizationInvoices.value.length)

const pendingPayments = computed(() =>
  organizationInvoices.value
    .filter(inv => inv.status !== 'Paid')
    .reduce((sum, inv) => sum + inv.amount, 0)
)

const paidInvoices = computed(() =>
  organizationInvoices.value.filter(inv => inv.status === 'Paid').length
)

const overdueInvoices = computed(() =>
  organizationInvoices.value.filter(inv => inv.status === 'Overdue').length
)

const totalRevenue = computed(() =>
  organizationInvoices.value
    .filter(inv => inv.status === 'Paid')
    .reduce((sum, inv) => sum + inv.amount, 0)
)

const quickActions = computed(() => {
  if (authStore.isAdmin) {
    return [
      { label: 'Create Invoice', route: '/invoices', action: 'add', icon: 'bi-plus-circle' },
      { label: 'Add Vendor', route: '/vendors', action: 'add', icon: 'bi-plus-circle' },
      { label: 'Manage Users', route: '/users', icon: 'bi-people' },
      { label: 'Organization Settings', route: '/settings', icon: 'bi-gear' },
      { label: 'View Reports', route: '#', icon: 'bi-bar-chart', disabled: true },
    ]
  } else {
    return [
      { label: 'View Invoices', route: '/invoices', icon: 'bi-file-earmark-text' },
      { label: 'View Vendors', route: '/vendors', icon: 'bi-people' },
      { label: 'Download Reports', route: '#', icon: 'bi-download', disabled: true },
    ]
  }
})

const activities = [
  { id: 1, message: 'Invoice INV-001 created', time: '2 hours ago' },
  { id: 2, message: 'Vendor ABC Corp added', time: 'Yesterday' },
  { id: 3, message: 'Payment received for INV-003', time: '2 days ago' },
]

onMounted(async () => {
  authStore.initializeAuth()
  organizationStore.initializeOrganization()

  if (!organizationStore.currentOrganization && authStore.user?.organization_id) {
    organizationStore.setCurrentOrganizationByAuth(authStore)
  }

  const data = await fetchDashboardData()
  dashboardData.value = data
})

const handleQuickAction = (action) => {
  if (action.disabled) return
  
  router.push(action.route)
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
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.dashboard-header {
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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

.context-info {
  text-align: right;
}

.org-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #ffffff;
  padding: 1rem;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.org-info i {
  font-size: 1.5rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.5rem;
  border-radius: 8px;
}

.org-info .org-name {
  font-weight: 600;
  color: #111827;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.user-welcome {
  font-size: 0.8rem;
  color: #6b7280;
  line-height: 1.4;
}

.role-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: capitalize;
  margin-left: 0.5rem;
}

.role-badge.admin {
  background: #dcfce7;
  color: #166534;
}

.role-badge.accountant {
  background: #dbeafe;
  color: #1e40af;
}

.access-info {
  margin-top: 0.25rem;
}

.access-info small {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
}

.context-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.banner-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.banner-content i {
  font-size: 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem;
  border-radius: 10px;
}

.banner-content div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.banner-content strong {
  font-size: 1rem;
  font-weight: 600;
}

.banner-content span {
  font-size: 0.85rem;
  opacity: 0.9;
}

.banner-stats {
  display: flex;
  gap: 1.5rem;
  font-weight: 600;
}

.banner-stats span {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
}

.quick-actions-section {
  margin-bottom: 3rem;
}

.quick-actions-section h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
  color: #111827;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-card:hover:not(.disabled) {
  border-color: #667eea;
  background: #f8faff;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.action-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f9fafb;
}

.action-card i {
  font-size: 1.25rem;
  color: #667eea;
}

.action-card span {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-wrapper {
    padding: 2rem 1.5rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1.5rem;
    align-items: stretch;
  }
  
  .context-info {
    text-align: left;
  }
  
  .org-info {
    justify-content: center;
  }
  
  .context-banner {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .banner-content {
    justify-content: center;
  }
  
  .banner-stats {
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .quick-actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
