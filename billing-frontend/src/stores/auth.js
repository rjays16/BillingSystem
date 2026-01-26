import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  getters: {
    isAdmin: (state) => state.user?.role === 'admin',
    isAccountant: (state) => state.user?.role === 'accountant',
    userRole: (state) => state.user?.role || 'guest',
    userName: (state) => state.user?.name || '',
    userAvatar: (state) => {
      if (!state.user?.name) return '?'
      return state.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
    }
  },

  actions: {
    login(credentials) {
      return new Promise((resolve) => {
        setTimeout(() => {
          const mockUsers = [
            { 
              id: 1, 
              email: 'allan@example.com', 
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
              email: 'coco@example.com', 
              password: 'coco123',
              name: 'Coco martin',
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
            localStorage.setItem('auth-user', JSON.stringify(this.user))
            localStorage.setItem('auth-token', this.token)
            localStorage.setItem('is-authenticated', 'true')
            
            resolve({ success: true, user })
          } else {
            resolve({ success: false, error: 'Invalid credentials' })
          }
        }, 1000)
      })
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      
      // Clear localStorage
      localStorage.removeItem('auth-user')
      localStorage.removeItem('auth-token')
      localStorage.removeItem('is-authenticated')
    },

    // Initialize auth state from localStorage
    initializeAuth() {
      const user = localStorage.getItem('auth-user')
      const token = localStorage.getItem('auth-token')
      const isAuthenticated = localStorage.getItem('is-authenticated') === 'true'

      if (user && token && isAuthenticated) {
        this.user = JSON.parse(user)
        this.token = token
        this.isAuthenticated = true
      }
    }
  }
})