import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useLibraryStore } from '@/stores/library'
import api from '@/services/api'

const mockEntries = [
  { id: 1, game_id: 10, user_id: 1, price_paid: 9.99, game: { title: 'Witcher 3' } },
  { id: 2, game_id: 20, user_id: 1, price_paid: 29.99, game: { title: 'Elden Ring' } },
]

describe('Library Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts empty', () => {
    const store = useLibraryStore()
    expect(store.entries).toHaveLength(0)
  })

  it('fetchLibrary fills entries', async () => {
    api.get = vi.fn().mockResolvedValue({ data: mockEntries })

    const store = useLibraryStore()
    await store.fetchLibrary()

    expect(store.entries).toHaveLength(2)
    expect(store.entries[0].game_id).toBe(10)
  })

  it('ownsGame returns true for owned game', async () => {
    api.get = vi.fn().mockResolvedValue({ data: mockEntries })

    const store = useLibraryStore()
    await store.fetchLibrary()

    expect(store.ownsGame(10)).toBe(true)
    expect(store.ownsGame(99)).toBe(false)
  })

  it('addToLibrary prepends new entry', async () => {
    const newEntry = { id: 3, game_id: 30, user_id: 1, price_paid: 49.99 }
    api.post = vi.fn().mockResolvedValue({ data: newEntry })

    const store = useLibraryStore()
    store.entries = [...mockEntries]
    await store.addToLibrary(30)

    expect(store.entries).toHaveLength(3)
    expect(store.entries[0].game_id).toBe(30)
  })

  it('removeFromLibrary removes entry by id', async () => {
    api.delete = vi.fn().mockResolvedValue({})

    const store = useLibraryStore()
    store.entries = [...mockEntries]
    await store.removeFromLibrary(1)

    expect(store.entries).toHaveLength(1)
    expect(store.entries.find(e => e.id === 1)).toBeUndefined()
  })
})
