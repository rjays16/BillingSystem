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

        <RouterLink 
          v-if="authStore.isAdmin" 
          to="/users" 
          class="nav-link" 
          active-class="active"
        >
          <i class="bi bi-people-fill"></i>
          <span v-if="!collapsed">Users</span>
        </RouterLink>
      </nav>

      <button class="collapse-btn" @click="toggleSidebar">
        <i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
      </button>
    </aside>

    <div class="main-area">
      <header class="topbar">
        <div class="left-section">
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
          
          <div class="org-context" v-if="organizationStore.currentOrganization">
            <i class="bi bi-building"></i>
            <span class="org-name">{{ organizationStore.organizationName }}</span>
            <span class="org-code">({{ organizationStore.organizationCode }})</span>
          </div>
        </div>

        <div class="topbar-spacer"></div>

        <div class="session-warning" v-if="authStore.isSessionExpiring">
          <i class="bi bi-exclamation-triangle"></i>
          <span>Session expires in {{ Math.floor(authStore.sessionRemaining / (60 * 1000)) }} minutes</span>
          <button @click="authStore.extendSession" class="btn-extend">
            Extend
          </button>
        </div>

        <div ref="orgSwitcherRef" class="org-switcher-wrapper" v-if="shouldShowOrgSwitcher">
          <div class="org-switcher" @click="toggleOrgSelector">
            <i class="bi bi-building"></i>
            <span>Switch Organization</span>
            <i class="bi bi-chevron-down"></i>
          </div>
          
          <div v-if="showOrgSelector" class="org-dropdown" @click.stop>
            <div class="org-dropdown-header">
              <i class="bi bi-building"></i>
              <span>Switch Organization</span>
              <button @click="showOrgSelector = false" class="close-btn">
                <i class="bi bi-x"></i>
              </button>
            </div>
            
            <div class="org-list">
              <div
                v-for="org in organizationStore.allOrganizations"
                :key="org.id"
                class="org-option"
                :class="{ active: org.id === organizationStore.currentOrganization?.id }"
                @click="switchOrganization(org)"
              >
                <i class="bi bi-building"></i>
                <div class="org-info">
                  <div class="org-name">{{ org.name }}</div>
                  <div class="org-code">{{ org.code }}</div>
                </div>
                <div class="org-description" v-if="org.description">{{ org.description }}</div>
              </div>
            </div>
          </div>
        </div>

        <div ref="userMenuRef" class="user-menu" @click.stop="toggleDropdown">
          <div class="avatar">{{ authStore.userAvatar }}</div>
          <div class="user-info" v-if="!collapsed">
            <div class="user-name">{{ authStore.userName }}</div>
            <div class="user-role">{{ authStore.userRole }}</div>
          </div>
          <i class="bi bi-chevron-down"></i>

          <div v-if="showDropdown" class="dropdown">
            <div class="user-header">
              <div class="dropdown-avatar">{{ authStore.userAvatar }}</div>
              <div>
                <div class="dropdown-name">{{ authStore.userName }}</div>
                <div class="dropdown-role">{{ authStore.userRole }}</div>
                <div class="dropdown-org" v-if="organizationStore.currentOrganization">
                  {{ organizationStore.currentOrganization.name }}
                </div>
              </div>
            </div>
            <hr />
            <button @click="goToProfile">Profile</button>
            <button v-if="authStore.isAdmin" @click="goToSettings">Organization Settings</button>
            <hr />
            <button class="logout" @click="logout">Logout</button>
          </div>
        </div>
      </header>

      <main class="main-content">
        <slot />
      </main>
    </div>
    
    <ConfirmModal
      v-if="showLogoutModal"
      title="Confirm Logout"
      message="Are you sure you want to logout?"
      confirm-text="Logout"
      @confirm="confirmLogout"
      @cancel="cancelLogout"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'
import ConfirmModal from '../components/ConfirmModal.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const organizationStore = useOrganizationStore()

const userMenuRef = ref(null)
const orgSwitcherRef = ref(null)
const showDropdown = ref(false)
const showOrgSelector = ref(false)
const collapsed = ref(false)
const showLogoutModal = ref(false)

const breadcrumbs = computed(() => {
  return route.matched
    .map((r) => {
      if (r.path.includes(':id')) {
        return `#${route.params.id}`
      }
      return r.meta?.breadcrumb
    })
    .filter(Boolean)
})

const shouldShowOrgSwitcher = computed(() => {
  return organizationStore.allOrganizations?.length > 1
})

const toggleSidebar = () => {
  collapsed.value = !collapsed.value
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const toggleOrgSelector = () => {
  showOrgSelector.value = !showOrgSelector.value
  showDropdown.value = false
}

const logout = () => {
  showLogoutModal.value = true
  showDropdown.value = false
}

const confirmLogout = async () => {
  showLogoutModal.value = false
  
  try {
    const result = await authStore.logout()
    
    if (result.success) {
      router.push('/login')
    } else {
      console.error('Logout failed:', result.error)
    }
  } catch (error) {
    console.error('Logout error:', error)
  }
}

const cancelLogout = () => {
  showLogoutModal.value = false
}

const goToProfile = () => {
  showDropdown.value = false
  router.push('/profile')
}

const goToSettings = () => {
  showDropdown.value = false
  router.push('/settings')
}

const switchOrganization = (org) => {
  if (org.id === organizationStore.currentOrganization?.id) {
    showOrgSelector.value = false
    return
  }
  
  organizationStore.setOrganization(org.id)
  showOrgSelector.value = false
  
  window.location.reload()
}

const handleClickOutside = (event) => {
  const isOutsideUserMenu = !userMenuRef.value?.contains(event.target)
  const isOutsideOrgSwitcher = !orgSwitcherRef.value?.contains(event.target)
  
  if (isOutsideUserMenu) {
    showDropdown.value = false
  }
  
  if (isOutsideOrgSwitcher) {
    showOrgSelector.value = false
  }
}

let sessionTimeoutInterval = null

const startSessionTimeoutCheck = () => {
  if (sessionTimeoutInterval) {
    clearInterval(sessionTimeoutInterval)
  }
  
  sessionTimeoutInterval = setInterval(() => {
    const remaining = authStore.checkSessionTimeout()
    
    if (remaining === false) {
      router.push('/login')
    }
  }, 60 * 1000)
}

onMounted(() => {
  collapsed.value = localStorage.getItem('sidebar-collapsed') === 'true'
  document.addEventListener('click', handleClickOutside)
  
  authStore.initializeAuth()
  organizationStore.initializeOrganization()
  
  if (!organizationStore.currentOrganization && authStore.user?.organization_id) {
    organizationStore.setCurrentOrganizationByAuth(authStore)
  }
  
  window.addEventListener('userUpdated', (event) => {
    authStore.user = event.detail
    localStorage.setItem('auth-user', JSON.stringify(event.detail))
  })
  
  startSessionTimeoutCheck()
})

watch(collapsed, (val) => {
  localStorage.setItem('sidebar-collapsed', val)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  
  if (sessionTimeoutInterval) {
    clearInterval(sessionTimeoutInterval)
  }
})
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

.app-layout.collapsed .sidebar-brand span {
  display: none;
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
  transition: all 0.2s ease;
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

.app-layout.collapsed .nav-link span {
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
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.collapse-btn:hover {
  background: #f9fafb;
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
  gap: 1rem;
  position: relative;
}

.left-section {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
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

.org-context {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #374151;
  background: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.org-context i {
  color: #6b7280;
}

.org-name {
  font-weight: 600;
}

.org-code {
  color: #6b7280;
  font-weight: 500;
}

.session-warning {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #fee2e2;
  color: #991b1b;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  border: 1px solid #fecaca;
}

.session-warning i {
  font-size: 1.1rem;
}

.btn-extend {
  background: #991b1b;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-extend:hover {
  background: #7f1d1d;
}

.org-switcher-wrapper {
  position: relative;
}

.org-switcher {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s ease;
}

.org-switcher:hover {
  background: #f3f4f6;
  transform: translateY(-1px);
}

.org-switcher i:first-child {
  font-size: 1.1rem;
  color: #667eea;
}

.org-switcher span {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.org-dropdown {
  position: absolute;
  top: 48px;
  right: 0;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  min-width: 280px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  z-index: 20;
}

.org-dropdown-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.org-dropdown-header i {
  font-size: 1.1rem;
  color: #667eea;
}

.org-dropdown-header span {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: #6b7280;
  transition: color 0.2s ease;
}

.close-btn:hover {
  color: #111827;
}

.org-list {
  max-height: 300px;
  overflow-y: auto;
}

.org-option {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.org-option:hover {
  background: #f8faff;
}

.org-option.active {
  background: #667eea;
}

.org-option.active .org-name,
.org-option.active .org-code,
.org-option.active .org-description {
  color: #ffffff;
}

.org-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.org-info .org-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.org-info .org-code {
  font-size: 0.75rem;
  color: #6b7280;
}

.org-description {
  font-size: 0.75rem;
  color: #6b7280;
  padding-left: 1.75rem;
}

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background 0.2s ease;
}

.user-menu:hover {
  background: #f9fafb;
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
  font-size: 0.875rem;
  font-weight: 600;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
  line-height: 1;
}

.user-role {
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1;
  text-transform: capitalize;
}

.dropdown {
  position: absolute;
  top: 48px;
  right: 0;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  min-width: 220px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  z-index: 20;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
}

.dropdown-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #111827;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  flex-shrink: 0;
}

.dropdown-name {
  font-weight: 600;
  color: #111827;
  font-size: 0.875rem;
}

.dropdown-role {
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.dropdown-org {
  color: #374151;
  font-size: 0.75rem;
  margin-top: 0.125rem;
}

.dropdown hr {
  margin: 0;
  border: none;
  border-top: 1px solid #f3f4f6;
}

.dropdown button {
  width: 100%;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  font-size: 0.875rem;
  color: #374151;
  transition: background 0.2s ease;
}

.dropdown button:hover {
  background: #f3f4f6;
}

.dropdown .logout {
  color: #991b1b;
}

.main-content {
  padding: 2.5rem 3rem;
  flex: 1;
}
</style>