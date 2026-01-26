import { createRouter, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Vendors from '../views/Vendors.vue'
import Invoices from '../views/Invoices.vue'
import InvoiceView from '../views/InvoiceView.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    component: Login,
    meta: { breadcrumb: 'Login' },
  },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { breadcrumb: 'Dashboard' },
  },
  {
    path: '/vendors',
    component: Vendors,
    meta: { breadcrumb: 'Vendors' },
  },
  {
    path: '/invoices',
    component: Invoices,
    meta: { breadcrumb: 'Invoices' },
    children: [
      {
        path: ':id',
        component: InvoiceView,
        meta: { breadcrumb: 'View' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

/*

router.beforeEach((to, from, next) => {
  const isAuthenticated =
    localStorage.getItem('isAuthenticated') === 'true'

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  if (to.meta.guestOnly && isAuthenticated) {
    return next('/dashboard')
  }

  next()
})
 */

export default router
