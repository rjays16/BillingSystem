import { defineStore } from 'pinia'
import { apiEndpoints } from '../services/api'

export const useOrganizationStore = defineStore('organization', {
  state: () => ({
    organizations: [],
    currentOrganization: null,
    loading: false,
    error: null
  }),

  getters: {
    getCurrentOrganization: (state) => state.currentOrganization,
    organizationName: (state) => state.currentOrganization?.name || 'No Organization',
    organizationCode: (state) => state.currentOrganization?.code || 'N/A',
    allOrganizations: (state) => state.organizations
  },

  actions: {
    async loadOrganizations() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.getOrganizations()
        this.organizations = response.data.data || []
      } catch (error) {
        console.error('Error loading organizations:', error)
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    setOrganization(organizationId) {
      const org = this.organizations.find(o => o.id === organizationId)
      if (org) {
        this.currentOrganization = { ...org }
        localStorage.setItem('current-organization', JSON.stringify(this.currentOrganization))
      }
    },

    setCurrentOrganizationByAuth(authStore) {
      console.log('Setting organization from auth:', authStore.user)
      const orgId = authStore.user?.organization_id || authStore.user?.organization?.id
      if (orgId) {
        console.log('Found orgId:', orgId)
        this.setOrganization(orgId)
      }
    },

    getOrganizationById(id) {
      return this.organizations.find(org => org.id === id)
    },

    async initializeOrganization() {
      const stored = localStorage.getItem('current-organization')
      if (stored) {
        try {
          this.currentOrganization = JSON.parse(stored)
        } catch (error) {
          console.error('Error parsing stored organization:', error)
          localStorage.removeItem('current-organization')
        }
      }

      const authStore = useAuthStore()
      if (authStore.isAuthenticated && this.organizations.length === 0) {
        await this.loadOrganizations()
      }
    },

    switchOrganization(organizationId) {
      this.setOrganization(organizationId)
    },

    clearOrganization() {
      this.currentOrganization = null
      localStorage.removeItem('current-organization')
    }
  }
})