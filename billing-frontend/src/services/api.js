import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth-token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle token expiration
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid, redirect to login
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-user')
      localStorage.removeItem('is-authenticated')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

export const apiEndpoints = {
  getOrganizations: () => api.get('/api/organizations'),
  getCurrentUser: () => api.get('/api/user'),
  getOrganizationUsers: (orgId) => api.get(`/api/organizations/${orgId}/users`),
  getVendors: () => api.get('/api/vendors'),
  getVendor: (id) => api.get(`/api/vendors/${id}`),
  createVendor: (data) => api.post('/api/vendors', data),
  updateVendor: (id, data) => api.put(`/api/vendors/${id}`, data),
  deleteVendor: (id) => api.delete(`/api/vendors/${id}`),
  
  getInvoices: () => api.get('/api/invoices'),
  getInvoice: (id) => api.get(`/api/invoices/${id}`),
  createInvoice: (data) => api.post('/api/invoices', data),
  updateInvoice: (id, data) => api.put(`/api/invoices/${id}`, data),
  deleteInvoice: (id) => api.delete(`/api/invoices/${id}`),
}