<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="logo">BS</div>
        <span>Billing System</span>
      </div>

      <nav class="nav">
        <RouterLink to="/dashboard" class="nav-link" active-class="active">
          Dashboard
        </RouterLink>
        <RouterLink to="#" class="nav-link">
          Vendors
        </RouterLink>
        <RouterLink to="#" class="nav-link">
          Invoices
        </RouterLink>
      </nav>

      <button class="logout-btn" @click="logout">
        Logout
      </button>
    </aside>

    <main class="main-content">
      <slot />
    </main>
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
.app-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

.sidebar {
  width: 240px;
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 700;
  margin-bottom: 2rem;
}

.logo {
  width: 36px;
  height: 36px;
  border: 2px solid #000;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.nav-link {
  padding: 0.65rem 0.75rem;
  border-radius: 8px;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s ease;
}

.nav-link:hover {
  background: #f3f4f6;
}

.nav-link.active {
  background: #000;
  color: #fff;
}

.logout-btn {
  margin-top: auto;
  padding: 0.65rem;
  border-radius: 8px;
  border: 2px solid #000;
  background: #fff;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #000;
  color: #fff;
}

.main-content {
  flex: 1;
  padding: 2.5rem 3rem;
}

@media (max-width: 768px) {
  .sidebar {
    width: 200px;
  }

  .main-content {
    padding: 2rem 1.5rem;
  }
}
</style>
