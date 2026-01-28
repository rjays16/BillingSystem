import { defineStore } from 'pinia'
import { apiEndpoints } from '../services/api'

export const useVendorStore = defineStore('vendor', {
  state: () => ({
    vendors: [],
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
    getVendorsByOrganization: (state) => (organizationId) => {
      return state.vendors.filter(vendor => vendor.organization_id === organizationId)
    },
    
    totalVendors: (state) => state.vendors.length,
    
    activeVendors: (state) => state.vendors.filter(vendor => vendor.status === 'active'),
    
    vendorById: (state) => (id) => {
      return state.vendors.find(vendor => vendor.id === id)
    }
  },

  actions: {
    async loadVendors() {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.getVendors()
        this.vendors = response.data.data || []
      } catch (error) {
        console.error('Error loading vendors:', error)
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async createVendor(vendorData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.createVendor(vendorData)
        const newVendor = response.data.data
        this.vendors.push(newVendor)
        return { success: true, data: newVendor }
      } catch (error) {
        console.error('Error creating vendor:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateVendor(id, vendorData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await apiEndpoints.updateVendor(id, vendorData)
        const updatedVendor = response.data.data
        
        const index = this.vendors.findIndex(vendor => vendor.id === id)
        if (index !== -1) {
          this.vendors[index] = updatedVendor
        }
        
        return { success: true, data: updatedVendor }
      } catch (error) {
        console.error('Error updating vendor:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteVendor(id) {
      this.loading = true
      this.error = null
      
      try {
        await apiEndpoints.deleteVendor(id)
        
        const index = this.vendors.findIndex(vendor => vendor.id === id)
        if (index !== -1) {
          this.vendors.splice(index, 1)
        }
        
        return { success: true }
      } catch (error) {
        console.error('Error deleting vendor:', error)
        this.error = error.response?.data?.message || error.message
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    clearVendors() {
      this.vendors = []
      this.error = null
    }
  }
})