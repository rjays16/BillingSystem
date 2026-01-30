import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Vendors from '../views/Vendors.vue'
import VendorView from '../views/VendorView.vue'
import Invoices from '../views/Invoices.vue'
import InvoiceView from '../views/InvoiceView.vue'
import Users from '../views/UserManagement.vue'
import Settings from '../views/Settings.vue'
import Profile from '../views/Profile.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      requiresAuth: false,
      guestOnly: true
    },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/vendors',
    name: 'vendors',
    component: Vendors,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/vendors/:id',
    name: 'vendor-detail',
    component: VendorView,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/invoices',
    name: 'invoices',
    component: Invoices,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/invoices/:id',
    name: 'invoice-detail',
    component: InvoiceView,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/users',
    name: 'users',
    component: Users,
    meta: { 
      requiresAuth: true,
      roles: ['admin']
    },
  },
  {
    path: '/settings',
    name: 'settings',
    component: Settings,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { 
      requiresAuth: true,
      roles: ['admin', 'accountant']
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  authStore.initializeAuth()

  const isAuthenticated = authStore.isAuthenticated

  if (to.meta.guestOnly && isAuthenticated) {
    return next('/dashboard')
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  next()
})

export default router