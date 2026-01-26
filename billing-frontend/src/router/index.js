import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'

import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Vendors from '../views/Vendors.vue'
import Invoices from '../views/Invoices.vue'
import InvoiceView from '../views/InvoiceView.vue'
import VendorView from '../views/VendorView.vue'
import UserManagement from '../views/UserManagement.vue'
import Profile from '../views/Profile.vue'
import Settings from '../views/Settings.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    component: Login,
    meta: { 
      breadcrumb: 'Login',
      requiresAuth: false,
      guestOnly: true
    },
  },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { 
      breadcrumb: 'Dashboard',
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/vendors',
    component: Vendors,
    meta: { 
      breadcrumb: 'Vendors',
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
    children: [
      {
        path: ':id',
        component: VendorView,
        meta: { breadcrumb: 'View', requiresAuth: true, roles: ['admin', 'accountant'] },
      },
    ],
  },
  
  {
    path: '/invoices',
    component: Invoices,
    meta: { 
      breadcrumb: 'Invoices',
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
    children: [
      {
        path: ':id',
        component: InvoiceView,
        meta: { breadcrumb: 'View', requiresAuth: true, roles: ['admin', 'accountant'] },
      },
    ],
  },
  {
    path: '/users',
    component: UserManagement,
    meta: { 
      breadcrumb: 'Users',
      requiresAuth: true,
      roles: ['admin'] // Admin only
    },
  },
  {
    path: '/profile',
    component: Profile,
    meta: { 
      breadcrumb: 'Profile',
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/settings',
    component: Settings,
    meta: { 
      breadcrumb: 'Settings',
      requiresAuth: true,
      roles: ['admin'] // Admin only
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route guard for authentication and role-based access
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const organizationStore = useOrganizationStore()

  // Initialize auth state
  authStore.initializeAuth()
  organizationStore.initializeOrganization()

  const isAuthenticated = authStore.isAuthenticated
  const userRole = authStore.userRole

  // Redirect authenticated users away from guest pages (like login)
  if (to.meta.guestOnly && isAuthenticated) {
    return next('/dashboard')
  }

  // Redirect unauthenticated users to login
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  // Check role-based access
  if (to.meta.roles && isAuthenticated) {
    const hasRole = to.meta.roles.includes(userRole)
    if (!hasRole) {
      // Redirect to dashboard if user doesn't have required role
      return next('/dashboard')
    }
  }

  next()
})

export default router
