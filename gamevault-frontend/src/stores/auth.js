import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

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
    await fetchCsrfCookie()
    const { data } = await api.post('/api/register', payload)
    user.value = data.user
    return data
  }

  async function login(payload) {
    await fetchCsrfCookie()
    const { data } = await api.post('/api/login', payload)
    user.value = data.user
    return data
  }

  async function logout() {
    try {
      await fetchCsrfCookie()
      await api.post('/api/logout')
    } catch (e) {
      // Si falla la petición igual limpiamos el estado local
      console.warn('Logout request failed:', e)
    } finally {
      user.value = null
      initialized.value = false  // ← permite re-chequear sesión tras login futuro
    }
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
    register, login, logout, fetchUser
  }
})
