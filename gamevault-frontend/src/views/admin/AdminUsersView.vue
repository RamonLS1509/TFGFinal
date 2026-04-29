<template>
  <div class="w-full max-w-screen-xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold">Gestión de usuarios</h1>
        <p class="text-gray-500 mt-1">{{ pagination?.total || 0 }} usuarios registrados</p>
      </div>
    </div>

    <LoadingSpinner v-if="loading" />

    <div v-else class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-800 text-gray-400">
          <tr>
            <th class="text-left px-4 py-3">Usuario</th>
            <th class="text-left px-4 py-3 hidden md:table-cell">Email</th>
            <th class="text-center px-4 py-3 hidden lg:table-cell">Biblioteca</th>
            <th class="text-center px-4 py-3 hidden lg:table-cell">Wishlist</th>
            <th class="text-center px-4 py-3 hidden lg:table-cell">Reseñas</th>
            <th class="text-center px-4 py-3">Rol</th>
            <th class="text-center px-4 py-3 hidden md:table-cell">Registro</th>
            <th class="text-right px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id"
            class="border-t border-gray-800 hover:bg-gray-800/50 transition-colors"
            :class="user.id === auth.user?.id ? 'opacity-50' : ''">

            <!-- Avatar + nombre -->
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0"
                  :class="user.role === 'admin' ? 'bg-yellow-600' : 'bg-blue-600'">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium">{{ user.name }}</p>
                  <p class="text-gray-500 text-xs">@{{ user.username }}</p>
                </div>
              </div>
            </td>

            <td class="px-4 py-3 text-gray-400 hidden md:table-cell">{{ user.email }}</td>

            <td class="px-4 py-3 text-center hidden lg:table-cell">
              <span class="text-blue-400 font-medium">{{ user.library_count }}</span>
            </td>
            <td class="px-4 py-3 text-center hidden lg:table-cell">
              <span class="text-pink-400 font-medium">{{ user.wishlist_count }}</span>
            </td>
            <td class="px-4 py-3 text-center hidden lg:table-cell">
              <span class="text-green-400 font-medium">{{ user.reviews_count }}</span>
            </td>

            <td class="px-4 py-3 text-center">
              <span :class="user.role === 'admin'
                  ? 'bg-yellow-900/50 text-yellow-400 border-yellow-800'
                  : 'bg-gray-800 text-gray-400 border-gray-700'"
                class="text-xs border rounded-full px-2 py-0.5">
                {{ user.role === 'admin' ? '★ Admin' : 'Usuario' }}
              </span>
            </td>

            <td class="px-4 py-3 text-center text-gray-500 text-xs hidden md:table-cell">
              {{ new Date(user.created_at).toLocaleDateString('es-ES') }}
            </td>

            <td class="px-4 py-3 text-right">
              <BaseButton
                v-if="user.id !== auth.user?.id && user.role !== 'admin'"
                variant="danger"
                size="sm"
                :loading="deletingId === user.id"
                @click="handleDelete(user)">
                Eliminar
              </BaseButton>
              <span v-else-if="user.id === auth.user?.id"
                class="text-xs text-gray-600">Tú</span>
              <span v-else
                class="text-xs text-gray-600">—</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <div v-if="pagination?.last_page > 1"
        class="flex justify-center gap-2 p-4 border-t border-gray-800">
        <BaseButton
          v-for="page in pagination.last_page" :key="page"
          :variant="page === pagination.current_page ? 'primary' : 'ghost'"
          size="sm"
          @click="loadPage(page)">
          {{ page }}
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import api from '@/services/api'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const auth  = useAuthStore()
const toast = useToast()

const users      = ref([])
const pagination = ref(null)
const loading    = ref(false)
const deletingId = ref(null)

async function loadUsers(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/users', { params: { page } })
    users.value      = data.data
    pagination.value = data
  } finally {
    loading.value = false
  }
}

async function loadPage(page) {
  await loadUsers(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function handleDelete(user) {
  if (!confirm(`¿Eliminar al usuario "${user.name}"?\n\nSe eliminarán también su biblioteca, wishlist y reseñas. Esta acción no se puede deshacer.`)) return

  deletingId.value = user.id
  try {
    const { data } = await api.delete(`/api/admin/users/${user.id}`)
    users.value = users.value.filter(u => u.id !== user.id)
    if (pagination.value) pagination.value.total--
    toast.success(data.message)
  } finally {
    deletingId.value = null
  }
}

onMounted(() => loadUsers())
</script>
