import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const initialized = ref(false)  // ← clave: saber si ya intentamos cargar el usuario

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
    await api.post('/api/logout')
    user.value = null
  }

  async function fetchUser() {
    if (initialized.value) return   // ← si ya lo intentamos, no repetir
    loading.value = true
    try {
      const { data } = await api.get('/api/me')
      user.value = data
    } catch {
      user.value = null
    } finally {
      loading.value = false
      initialized.value = true      // ← marcar como intentado pase lo que pase
    }
  }

  return {
    user, loading, initialized,
    isAuthenticated, isAdmin,
    register, login, logout, fetchUser
  }
})
