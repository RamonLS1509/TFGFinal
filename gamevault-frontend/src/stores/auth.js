import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const initialized = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  async function fetchCsrfCookie() {
    await api.get('/sanctum/csrf-cookie')
  }

  async function register(payload) {
    const toast = useToast()
    await fetchCsrfCookie()
    const { data } = await api.post('/api/register', payload)
    user.value = data.user
    toast.success(`¡Bienvenido, ${data.user.name}!`)
    return data
  }

  async function login(payload) {
    const toast = useToast()
    await fetchCsrfCookie()
    const { data } = await api.post('/api/login', payload)
    user.value = data.user
    toast.success(`¡Bienvenido de nuevo, ${data.user.name}!`)
    return data
  }

  async function logout() {
    const toast = useToast()

    try {
      await fetchCsrfCookie()
      await api.post('/api/logout')
    } catch (e) {
      console.warn('Logout request failed:', e)
    } finally {
      // Limpiar el usuario
      user.value = null
      initialized.value = false

      // Limpiar todos los stores de datos privados
      const { useLibraryStore }  = await import('@/stores/library')
      const { useWishlistStore } = await import('@/stores/wishlist')
      const { useReviewsStore }  = await import('@/stores/reviews')

      const libraryStore  = useLibraryStore()
      const wishlistStore = useWishlistStore()
      const reviewsStore  = useReviewsStore()

      libraryStore.entries   = []
      wishlistStore.items    = []
      reviewsStore.myReview  = null
      reviewsStore.myReviews = []

      toast.info('Sesión cerrada correctamente.')
    }
  }

  async function updateProfile(payload) {
    const toast = useToast()
    const { data } = await api.put('/api/profile', payload)
    user.value = data.user
    toast.success('Perfil actualizado correctamente.')
    return data
  }

  async function changePassword(payload) {
    const toast = useToast()
    const { data } = await api.put('/api/profile/password', payload)
    toast.success('Contraseña cambiada correctamente.')
    return data
  }

  async function fetchUser() {
    if (initialized.value) return
    loading.value = true
    try {
      const { data } = await api.get('/api/me')
      user.value = data
    } catch {
      user.value = null
    } finally {
      loading.value = false
      initialized.value = true
    }
  }

  return {
    user, loading, initialized,
    isAuthenticated, isAdmin,
    register, login, logout, fetchUser,
    updateProfile, changePassword,
  }
})
