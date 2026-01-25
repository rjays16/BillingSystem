import { createRouter, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Vendors from '../views/Vendors.vue'
import Invoices from '../views/Invoices.vue'
import InvoiceView from '../views/InvoiceView.vue'

const routes = [
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
    meta: { breadcrumb: 'Vendor' },
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

export default router
