import { defineStore } from 'pinia'
import api, { apiEndpoints } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
    loginTime: null,
    sessionTimeout: 24 * 60 * 60 * 1000,
  }),

  getters: {
    isAdmin: (state) => state.user?.role === 'admin',
    isAccountant: (state) => state.user?.role === 'accountant',
    userRole: (state) => state.user?.role || 'guest',
    userName: (state) => state.user?.name || '',
    userAvatar: (state) => {
      if (!state.user?.name) return '?'
      return state.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
    },
    sessionRemaining: (state) => {
      if (!state.loginTime || !state.sessionTimeout) return null
      const elapsed = Date.now() - state.loginTime
      const remaining = state.sessionTimeout - elapsed
      return Math.max(0, remaining)
    },
    isSessionExpiring: (state) => {
      const remaining = state.loginTime && state.sessionTimeout 
        ? (state.sessionTimeout - (Date.now() - state.loginTime)) / (60 * 1000)
        : null
      return remaining !== null && remaining < 30 
    }
  },

  actions: {
    async login(credentials) {
      console.log('Auth store login called with:', credentials)
      try {
        const response = await apiEndpoints.login(credentials)
        
        this.user = response.data.user
        this.token = response.data.token
        this.isAuthenticated = true
        this.loginTime = Date.now()
        localStorage.setItem('auth-user', JSON.stringify(this.user))
        localStorage.setItem('auth-token', this.token)
        localStorage.setItem('is-authenticated', 'true')
        localStorage.setItem('session-start', this.loginTime.toString())
        
        console.log('Token stored in localStorage:', localStorage.getItem('auth-token'))
        
        return { success: true, user: response.data.user }
      } catch (error) {
        console.error('Login error:', error)
        return { success: false, error: error.response?.data?.message || 'Network error' }
      }
    },

    async logout() {
      try {
        if (this.token) {
          await apiEndpoints.logout()
        }

        this.user = null
        this.token = null
        this.isAuthenticated = false
        this.loginTime = null
        
        localStorage.removeItem('auth-user')
        localStorage.removeItem('auth-token')
        localStorage.removeItem('is-authenticated')
        localStorage.removeItem('session-start')
        
        localStorage.removeItem('current-organization')
        sessionStorage.clear()
        
        return { success: true, message: 'Logged out successfully' }
      } catch (error) {
        console.error('Logout error:', error)
        // Still clear local data even if backend call fails
        this.user = null
        this.token = null
        this.isAuthenticated = false
        this.loginTime = null
        return { success: false, error: 'Failed to logout properly' }
      }
    },

    // Initialize auth state from localStorage
    initializeAuth() {
      const user = localStorage.getItem('auth-user')
      const token = localStorage.getItem('auth-token')
      const isAuthenticated = localStorage.getItem('is-authenticated') === 'true'
      const sessionStart = localStorage.getItem('session-start')

      if (user && token && isAuthenticated) {
        try {
          this.user = JSON.parse(user)
          this.token = token
          this.isAuthenticated = true
          this.loginTime = sessionStart ? parseInt(sessionStart) : Date.now()
        } catch (error) {
          console.error('Error initializing auth:', error)
          this.logout()
        }
      }
    },

    // Check session timeout
    checkSessionTimeout() {
      if (!this.isAuthenticated || !this.loginTime) return false
      
      const elapsed = Date.now() - this.loginTime
      const remaining = this.sessionTimeout - elapsed
      
      if (remaining <= 0) {
        this.logout()
        return false
      }
      
      return remaining
    },


    extendSession() {
      if (this.isAuthenticated) {
        this.loginTime = Date.now()
        localStorage.setItem('session-start', this.loginTime.toString())
      }
    }
  }
})