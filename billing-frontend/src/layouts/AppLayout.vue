<template>
  <div class="app-layout" :class="{ collapsed }">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <i class="bi bi-receipt-cutoff"></i>
        <span v-if="!collapsed">Billing</span>
      </div>

      <nav class="nav">
        <RouterLink to="/dashboard" class="nav-link" active-class="active">
          <i class="bi bi-speedometer2"></i>
          <span v-if="!collapsed">Dashboard</span>
        </RouterLink>

        <RouterLink to="/vendors" class="nav-link" active-class="active">
          <i class="bi bi-people"></i>
          <span v-if="!collapsed">Vendors</span>
        </RouterLink>

        <RouterLink to="/invoices" class="nav-link" active-class="active">
          <i class="bi bi-file-earmark-text"></i>
          <span v-if="!collapsed">Invoices</span>
        </RouterLink>
      </nav>

      <button class="collapse-btn" @click="toggleSidebar">
        <i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
      </button>
    </aside>

    <div class="main-area">
      <header class="topbar">
        <nav class="breadcrumbs">
          <RouterLink to="/dashboard" class="crumb">Home</RouterLink>

          <span
            v-for="(crumb, index) in breadcrumbs"
            :key="index"
            class="crumb-wrapper"
          >
            <span class="separator">/</span>
            <span class="crumb active">{{ crumb }}</span>
          </span>
        </nav>

        <div class="topbar-spacer"></div>
        <div ref="userMenuRef" class="user-menu" @click.stop="toggleDropdown">
          <div class="avatar">AJ</div>
          <i class="bi bi-chevron-down"></i>

          <div v-if="showDropdown" class="dropdown">
            <button>Profile</button>
            <button>Settings</button>
            <hr />
            <button class="logout" @click="logout">Logout</button>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const breadcrumbs = computed(() => {
  return route.matched.map((r) => {
    if (r.path.includes(':id')) {
      return `#${route.params.id}`
    }
    return r.meta?.breadcrumb
  }).filter(Boolean)
})

const collapsed = ref(false)

onMounted(() => {
  collapsed.value = localStorage.getItem('sidebar-collapsed') === 'true'
})

watch(collapsed, val => {
  localStorage.setItem('sidebar-collapsed', val)
})

const toggleSidebar = () => {
  collapsed.value = !collapsed.value
}

const showDropdown = ref(false)
const userMenuRef = ref(null)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const handleClickOutside = (e) => {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const logout = () => {
  localStorage.removeItem('isAuthenticated')
  router.push('/login')
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css');

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
  position: relative;
  transition: width 0.25s ease;
}

.app-layout.collapsed .sidebar {
  width: 80px;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  text-decoration: none;
  color: #374151;
  font-weight: 500;
}

.nav-link:hover {
  background: #f3f4f6;
}

.nav-link.active {
  background: #111827;
  color: #ffffff;
}

.app-layout.collapsed .nav-link {
  justify-content: center;
}

.app-layout.collapsed .nav-link span,
.app-layout.collapsed .sidebar-brand span {
  display: none;
}

.collapse-btn {
  position: absolute;
  top: 50%;
  right: -14px;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  cursor: pointer;
}

.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: 64px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 2rem;
  display: flex;
  align-items: center;
}

.topbar-spacer {
  flex: 1;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #6b7280;
}

.crumb {
  text-decoration: none;
  color: #6b7280;
  font-weight: 500;
}

.crumb.active {
  color: #111827;
  font-weight: 600;
}

.separator {
  margin: 0 0.5rem;
  color: #9ca3af;
}

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #111827;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dropdown {
  position: absolute;
  top: 48px;
  right: 0;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  min-width: 160px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  z-index: 20;
}

.dropdown button {
  width: 100%;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
}

.dropdown button:hover {
  background: #f3f4f6;
}

.dropdown .logout {
  color: #991b1b;
}

.main-content {
  padding: 2.5rem 3rem;
}
</style>
