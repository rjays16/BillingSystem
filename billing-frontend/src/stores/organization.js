import { defineStore } from 'pinia'

export const useOrganizationStore = defineStore('organization', {
  state: () => ({
    organizations: [
      {
        id: 1,
        name: 'Department of Health',
        code: 'DOH',
        description: 'National Health Agency',
        address: 'San Lazaro Compound, Tayuman, Sta. Cruz, Manila 1003',
        phone: '+63 2 8651-7800',
        email: 'info@doh.gov.ph',
        created_at: '2024-01-15'
      },
      {
        id: 2,
        name: 'Bureau of Internal Revenue',
        code: 'BIR',
        description: 'National Tax Agency',
        address: 'BIR National Office, Diliman, Quezon City',
        phone: '+63 2 928-0305',
        email: 'contactus@bir.gov.ph',
        created_at: '2024-01-20'
      },
      {
        id: 3,
        name: 'Social Security System',
        code: 'SSS',
        description: 'Social Insurance Agency',
        address: 'SSS Building, East Avenue, Diliman, Quezon City',
        phone: '+63 2 8920-6401',
        email: 'member_relations@sss.gov.ph',
        created_at: '2024-01-25'
      }
    ],
    currentOrganization: null,
  }),

  getters: {
    getCurrentOrganization: (state) => state.currentOrganization,
    organizationName: (state) => state.currentOrganization?.name || 'No Organization',
    organizationCode: (state) => state.currentOrganization?.code || 'N/A',
    allOrganizations: (state) => state.organizations,
  },

  actions: {
    setOrganization(organizationId) {
      const org = this.organizations.find(o => o.id === organizationId)
      if (org) {
        this.currentOrganization = { ...org }
        localStorage.setItem('current-organization', JSON.stringify(this.currentOrganization))
      }
    },

    setCurrentOrganizationByAuth(authStore) {
      if (authStore.user?.organization_id) {
        this.setOrganization(authStore.user.organization_id)
      }
    },

    // Get organization by ID
    getOrganizationById(id) {
      return this.organizations.find(org => org.id === id)
    },

    // Initialize from localStorage
    initializeOrganization() {
      const stored = localStorage.getItem('current-organization')
      if (stored) {
        this.currentOrganization = JSON.parse(stored)
      }
    }
  }
})