import axios from 'axios'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false,
})

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth-token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - Handle errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-user')
      localStorage.removeItem('is-authenticated')
      window.location.href = '/login'
    }
    
    if (error.response?.status === 403) {
      console.error('Access forbidden:', error.response.data)
    }
    
    if (error.response?.status === 500) {
      console.error('Server error:', error.response.data)
    }
    
    return Promise.reject(error)
  }
)

export default api
export { api }

export const apiEndpoints = {
  login: (credentials) => api.post('/api/login', credentials),
  logout: () => api.post('/api/logout'),
  
  getCurrentUser: () => api.get('/api/user'),
  updateProfile: (data) => api.put('/api/user/profile', data),
  
  getOrganizations: () => api.get('/api/organizations'),
  getOrganization: (id) => api.get(`/api/organizations/${id}`),
  updateOrganization: (id, data) => api.put(`/api/organizations/${id}`, data),
  getOrganizationUsers: (orgId) => api.get(`/api/organizations/${orgId}/users`),
  
  getVendors: (params = {}) => api.get('/api/vendors', { params }),
  getVendor: (id) => api.get(`/api/vendors/${id}`),
  createVendor: (data) => api.post('/api/vendors', data),
  updateVendor: (id, data) => api.put(`/api/vendors/${id}`, data),
  deleteVendor: (id) => api.delete(`/api/vendors/${id}`),
  
  getInvoices: (params = {}) => api.get('/api/invoices', { params }),
  getInvoice: (id) => api.get(`/api/invoices/${id}`),
  createInvoice: (data) => api.post('/api/invoices', data),
  updateInvoice: (id, data) => api.put(`/api/invoices/${id}`, data),
  deleteInvoice: (id) => api.delete(`/api/invoices/${id}`),
  getInvoicesByStatus: (status) => api.get(`/api/invoices/status/${status}`),
  
  // Users (Admin only)
  getUsers: () => api.get('/api/users'),
  getUser: (id) => api.get(`/api/users/${id}`),
  createUser: (data) => api.post('/api/users', data),
  updateUser: (id, data) => api.put(`/api/users/${id}`, data),
  deleteUser: (id) => api.delete(`/api/users/${id}`),
  
  getDashboardStats: () => api.get('/api/dashboard/stats'),
}