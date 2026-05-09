import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

//Gestiona la identidad del usuario a lo largo de toda la aplicación, desde el registo y login hasta el logout
export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const initialized = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  //Obtiene la cookie CSRF de Laravel Sanctum
  async function fetchCsrfCookie() {
    await api.get('/sanctum/csrf-cookie')
  }
//Obtiene la cookie CSRF, registra al usuario y guarda sus datos en el store
  async function register(payload) {
    const toast = useToast()
    await fetchCsrfCookie()
    const { data } = await api.post('/api/register', payload)
    user.value = data.user
    toast.success(`¡Bienvenido, ${data.user.name}!`)
    return data
  }
//Igual que register pero para iniciar sesion
  async function login(payload) {
    const toast = useToast()
    await fetchCsrfCookie()
    const { data } = await api.post('/api/login', payload)
    user.value = data.user
    toast.success(`¡Bienvenido de nuevo, ${data.user.name}!`)
    return data
  }

  //Cierra la sesion en el servidor y en caso de exito o fallo limpia al usuario y vacia los stores de biblioteca, wishlist y reseñas para que no queden
  //datos privados de otro usuario en memoria.
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

  //Actualiza los datos del perfil y refresca el usuario en el store
  async function updateProfile(payload) {
    const toast = useToast()
    const { data } = await api.put('/api/profile', payload)
    user.value = data.user
    toast.success('Perfil actualizado correctamente.')
    return data
  }

  //Cambia la contraseña mostrando confirmación mediante toast
  async function changePassword(payload) {
    const toast = useToast()
    const { data } = await api.put('/api/profile/password', payload)
    toast.success('Contraseña cambiada correctamente.')
    return data
  }

  //Comprueba si hay sesion activa consultando /api/me.
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
