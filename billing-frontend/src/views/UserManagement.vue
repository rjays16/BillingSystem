<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1>User Management</h1>
        <p class="subtitle">Manage users in your organization</p>
      </div>
      <button class="btn primary" @click="openAddUser">
        + Add User
      </button>
    </div>

    <div class="org-context" v-if="organizationStore.currentOrganization">
      <i class="bi bi-building"></i>
      <span>Showing users for: <strong>{{ organizationStore.organizationName }}</strong></span>
    </div>

    <div class="table-wrapper">
      <table class="users-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="user in organizationUsers" :key="user.id">
            <td class="user-cell">
              <div class="user-avatar">{{ getUserAvatar(user.name) }}</div>
              <div class="user-info">
                <div class="user-name">{{ user.name }}</div>
                <div class="user-org">{{ user.organization }}</div>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>
              <span class="role-badge" :class="user.role">
                {{ user.role }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="user.status">
                {{ user.status }}
              </span>
            </td>
            <td>{{ user.joinedDate }}</td>
            <td class="actions">
              <button 
                @click="editUser(user)" 
                class="btn-action edit-btn"
              >
                Edit
              </button>
              <button 
                v-if="user.id !== authStore.user?.id" 
                class="btn-action danger-btn" 
                @click="askDeleteUser(user)"
              >
                Delete
              </button>
            </td>
          </tr>

          <tr v-if="organizationUsers.length === 0">
            <td colspan="6" class="empty">No users found in this organization</td>
          </tr>
        </tbody>
      </table>
    </div>

    <UserForm
      v-if="showUserForm"
      :user="selectedUser"
      :organizations="organizationStore.allOrganizations"
      @save="saveUser"
      @close="closeUserForm"
    />

    <ConfirmModal
      v-if="showConfirm"
      title="Delete User"
      :message="`Are you sure you want to delete ${userToDelete?.name}?`"
      @confirm="confirmDeleteUser"
      @cancel="cancelDeleteUser"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
 import AppLayout from '../layouts/AppLayout.vue'
 import { useAuthStore } from '../stores/auth'
 import { useOrganizationStore } from '../stores/organization'
 import { useUserStore } from '../stores/user'
 import { useToast } from '../composables/useToast'
 import UserForm from '../components/UserForm.vue'
 import ConfirmModal from '../components/ConfirmModal.vue'

 const authStore = useAuthStore()
 const organizationStore = useOrganizationStore()
 const userStore = useUserStore()
 const { show } = useToast()

const organizationUsers = computed(() => {
  if (!organizationStore.currentOrganization) return []
  
  return userStore.users.filter(user => 
    user.organization_id === organizationStore.currentOrganization.id
  )
})

onMounted(async () => {
  try {
    await userStore.loadUsers()
    allUsers.value = userStore.users
  } catch (error) {
    show('Failed to load users', 'error')
  }
})

const getUserAvatar = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const openAddUser = () => {
  selectedUser.value = null
  showUserForm.value = true
}

const editUser = (user) => {
  selectedUser.value = { ...user }
  showUserForm.value = true
}

const closeUserForm = () => {
  showUserForm.value = false
  selectedUser.value = null
}

const saveUser = async (userData) => {
  try {
    if (selectedUser.value) {
      const result = await userStore.updateUser(selectedUser.value.id, userData)
      
      if (result.success) {
        show('User updated successfully', 'success')
        closeUserForm()
      } else {
        show(result.error || 'Failed to update user', 'error')
      }
    } else {
      const result = await userStore.createUser(userData)
      
      if (result.success) {
        show('User created successfully', 'success')
        closeUserForm()
      } else {
        show(result.error || 'Failed to create user', 'error')
      }
    }
  } catch (error) {
    show(error.message || 'An error occurred', 'error')
  }
}

const askDeleteUser = (user) => {
  userToDelete.value = user
  showConfirm.value = true
}

const confirmDeleteUser = async () => {
  if (!userToDelete.value) return
  
  try {
    const result = await userStore.deleteUser(userToDelete.value.id)
    
    if (result.success) {
      show('User deleted successfully', 'success')
      cancelDeleteUser()
    } else {
      show(result.error || 'Failed to delete user', 'error')
    }
  } catch (error) {
    show(error.message || 'An error occurred', 'error')
  }
}

const cancelDeleteUser = () => {
  userToDelete.value = null
  showConfirm.value = false
}

const getOrganizationName = (orgId) => {
  const org = organizationStore.getOrganizationById(orgId)
  return org ? org.name : 'Unknown Organization'
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.subtitle {
  color: #6b7280;
}

.org-context {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f3f4f6;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  color: #374151;
}

.org-context i {
  color: #6b7280;
}

.table-wrapper {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 1rem 1.25rem;
  text-align: left;
  font-size: 0.9rem;
}

.users-table thead {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.users-table tbody tr:hover {
  background: #f3f4f6;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #111827;
}

.user-org {
  font-size: 0.75rem;
  color: #6b7280;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.role-badge.admin {
  background: #dcfce7;
  color: #166534;
}

.role-badge.accountant {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.active {
  background: #dcfce7;
  color: #166534;
}

.status-badge.inactive {
  background: #f3f4f6;
  color: #6b7280;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-action {
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.btn-action:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.danger-btn {
  background: #fee2e2;
  color: #991b1b;
}

.btn.primary {
  background: #111827;
  color: white;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
}

.btn.primary:hover {
  background: #1f2937;
}

.empty {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .users-table th:nth-child(5),
  .users-table td:nth-child(5),
  .users-table th:nth-child(6),
  .users-table td:nth-child(6) {
    display: none;
  }
}
</style>