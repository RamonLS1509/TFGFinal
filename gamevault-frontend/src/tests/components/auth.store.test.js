import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts with null user', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
  })

  it('isAuthenticated is false when user is null', () => {
    const store = useAuthStore()
    expect(store.isAuthenticated).toBe(false)
  })

  it('isAuthenticated is true when user is set', () => {
    const store = useAuthStore()
    store.user = { id: 1, name: 'Test', role: 'user' }
    expect(store.isAuthenticated).toBe(true)
  })

  it('isAdmin is false for regular user', () => {
    const store = useAuthStore()
    store.user = { id: 1, name: 'Test', role: 'user' }
    expect(store.isAdmin).toBe(false)
  })

  it('isAdmin is true for admin user', () => {
    const store = useAuthStore()
    store.user = { id: 1, name: 'Admin', role: 'admin' }
    expect(store.isAdmin).toBe(true)
  })

  it('login sets user on success', async () => {
    const mockUser = { id: 1, name: 'Test User', email: 'test@test.com', role: 'user' }
    api.get  = vi.fn().mockResolvedValue({})
    api.post = vi.fn().mockResolvedValue({ data: { user: mockUser, message: 'ok' } })

    const store = useAuthStore()
    await store.login({ email: 'test@test.com', password: 'password' })

    expect(store.user).toEqual(mockUser)
    expect(store.isAuthenticated).toBe(true)
  })

  it('logout clears the user', async () => {
    api.post = vi.fn().mockResolvedValue({})

    const store = useAuthStore()
    store.user = { id: 1, name: 'Test' }
    await store.logout()

    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('fetchUser sets user on success', async () => {
    const mockUser = { id: 2, name: 'Fetched', role: 'user' }
    api.get = vi.fn().mockResolvedValue({ data: mockUser })

    const store = useAuthStore()
    await store.fetchUser()

    expect(store.user).toEqual(mockUser)
  })

  it('fetchUser sets null on error (unauthenticated)', async () => {
    api.get = vi.fn().mockRejectedValue(new Error('Unauthenticated'))

    const store = useAuthStore()
    store.user = { id: 1 }
    await store.fetchUser()

    expect(store.user).toBeNull()
  })
})
