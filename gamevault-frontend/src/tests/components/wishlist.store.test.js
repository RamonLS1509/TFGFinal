import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useWishlistStore } from '@/stores/wishlist'
import api from '@/services/api'

describe('Wishlist Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts empty', () => {
    const store = useWishlistStore()
    expect(store.items).toHaveLength(0)
  })

  it('fetchWishlist fills items', async () => {
    api.get = vi.fn().mockResolvedValue({
      data: [
        { id: 1, game_id: 5, priority: 0 },
        { id: 2, game_id: 6, priority: 1 },
      ]
    })

    const store = useWishlistStore()
    await store.fetchWishlist()

    expect(store.items).toHaveLength(2)
  })

  it('hasGame returns true for wishlisted game', async () => {
    api.get = vi.fn().mockResolvedValue({ data: [{ id: 1, game_id: 5, priority: 0 }] })

    const store = useWishlistStore()
    await store.fetchWishlist()

    expect(store.hasGame(5)).toBe(true)
    expect(store.hasGame(99)).toBe(false)
  })

  it('getEntry returns the correct entry', async () => {
    api.get = vi.fn().mockResolvedValue({ data: [{ id: 1, game_id: 5, priority: 1 }] })

    const store = useWishlistStore()
    await store.fetchWishlist()

    const entry = store.getEntry(5)
    expect(entry?.id).toBe(1)
    expect(entry?.priority).toBe(1)
  })

  it('addToWishlist prepends new item', async () => {
    api.post = vi.fn().mockResolvedValue({ data: { id: 3, game_id: 7, priority: 0 } })

    const store = useWishlistStore()
    await store.addToWishlist(7)

    expect(store.items).toHaveLength(1)
    expect(store.items[0].game_id).toBe(7)
  })

  it('removeFromWishlist removes item by id', async () => {
    api.delete = vi.fn().mockResolvedValue({})

    const store = useWishlistStore()
    store.items = [
      { id: 1, game_id: 5, priority: 0 },
      { id: 2, game_id: 6, priority: 1 },
    ]
    await store.removeFromWishlist(1)

    expect(store.items).toHaveLength(1)
    expect(store.items.find(i => i.id === 1)).toBeUndefined()
  })
})
