import { defineStore } from 'pinia'

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
    login(credentials) {
      return new Promise((resolve) => {
        setTimeout(() => {
          const mockUsers = [
            { 
              id: 1, 
              email: 'admin@example.com',
              password: 'admin123',
              name: 'Allan Admin',
              role: 'admin',
              organization_id: 1 
            },
            { 
              id: 2, 
              email: 'accountant@example.com', 
              password: 'acc123',
              name: 'Rjay Accountant',
              role: 'accountant',
              organization_id: 1 
            },
            { 
              id: 3, 
              email: 'allan@doh.gov.ph', 
              password: 'allan123',
              name: 'Allan Condiman',
              role: 'accountant',
              organization_id: 1 
            },
            { 
              id: 4, 
              email: 'coco@bir.gov.ph', 
              password: 'coco123',
              name: 'Coco Martin',
              role: 'admin',
              organization_id: 2 
            },
            { 
              id: 5, 
              email: 'sheena.catacutan@bir.gov.ph', 
              password: 'sheena123',
              name: 'Sheena Catacutan',
              role: 'accountant',
              organization_id: 2 
            },
            { 
              id: 6, 
              email: 'aljun.condiman@sss.gov.ph', 
              password: 'aljun123',
              name: 'Aljun Condiman',
              role: 'accountant',
              organization_id: 3 
            }
          ]

          const user = mockUsers.find(u => 
            u.email === credentials.email && u.password === credentials.password
          )

          if (user) {
            this.user = { ...user }
            this.token = 'mock-token-' + Date.now()
            this.isAuthenticated = true
            this.loginTime = Date.now()
            localStorage.setItem('auth-user', JSON.stringify(this.user))
            localStorage.setItem('auth-token', this.token)
            localStorage.setItem('is-authenticated', 'true')
            localStorage.setItem('session-start', this.loginTime.toString())
            
            resolve({ success: true, user })
          } else {
            resolve({ success: false, error: 'Invalid credentials' })
          }
        }, 1000)
      })
    },

    async logout() {
      try {
        // Clear auth state
        this.user = null
        this.token = null
        this.isAuthenticated = false
        this.loginTime = null
        
        // Clear all localStorage auth data
        localStorage.removeItem('auth-user')
        localStorage.removeItem('auth-token')
        localStorage.removeItem('is-authenticated')
        localStorage.removeItem('session-start')
        
        // Clear organization data
        localStorage.removeItem('current-organization')
        
        // Clear session storage
        sessionStorage.clear()
        
        return { success: true, message: 'Logged out successfully' }
      } catch (error) {
        console.error('Logout error:', error)
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