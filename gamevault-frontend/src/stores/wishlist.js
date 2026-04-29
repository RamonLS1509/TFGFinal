import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

export const useWishlistStore = defineStore('wishlist', () => {
  const items = ref([])
  const loading = ref(false)

  async function fetchWishlist() {
    loading.value = true
    try {
      const { data } = await api.get('/api/wishlist')
      items.value = data
    } finally {
      loading.value = false
    }
  }

  async function addToWishlist(gameId, priority = 0) {
    const toast = useToast()
    const { data } = await api.post('/api/wishlist', { game_id: gameId, priority })
    items.value.unshift(data)
    toast.success('Juego añadido a tu wishlist.')
    return data
  }

  async function removeFromWishlist(wishlistId) {
    const toast = useToast()
    await api.delete(`/api/wishlist/${wishlistId}`)
    items.value = items.value.filter(i => i.id !== wishlistId)
    toast.success('Juego eliminado de la wishlist.')
  }

  function hasGame(gameId) {
    return items.value.some(i => i.game_id === gameId)
  }

  function getEntry(gameId) {
    return items.value.find(i => i.game_id === gameId)
  }

  return { items, loading, fetchWishlist, addToWishlist, removeFromWishlist, hasGame, getEntry }
})
