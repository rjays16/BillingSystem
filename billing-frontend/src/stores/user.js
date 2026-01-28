import { defineStore } from 'pinia'
import { apiEndpoints } from '../services/api'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      perPage: 10,
      total: 0,
      lastPage: 1
    }
  }),

  getters: {
    getUsersByOrganization: (state) => (organizationId) => {
      return state.users.filter(user => user.organization_id === organizationId)
    },
    
    totalUsers: (state) => state.users.length,
    
    adminUsers: (state) => state.users.filter(user => user.role === 'admin'),
    
    accountantUsers: (state) => state.users.filter(user => user.role === 'accountant'),
    
    userById: (state) => (id) => {
      return state.users.find(user => user.id === id)
    }
  },

  actions: {
    async loadUsers() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.getUsers()
        this.users = response.data.data || []
      } catch (error) {
        console.error('Error loading users:', error)
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.createUser(userData)
        const newUser = response.data.data
        this.users.push(newUser)
        return { success: true, data: newUser }
      } catch (error) {
        console.error('Error creating user:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateUser(id, userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.updateUser(id, userData)
        const updatedUser = response.data.data
        
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index] = updatedUser
        }
        
        return { success: true, data: updatedUser }
      } catch (error) {
        console.error('Error updating user:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteUser(id) {
      this.loading = true
      this.error = null
      
      try {
        await apiEndpoints.deleteUser(id)
        
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users.splice(index, 1)
        }
        
        return { success: true }
      } catch (error) {
        console.error('Error deleting user:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    clearUsers() {
      this.users = []
      this.error = null
    }
  }
})