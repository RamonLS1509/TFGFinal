import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

//Gestiona el estado global de la wishlist del usuario
export const useWishlistStore = defineStore('wishlist', () => {
  const items = ref([])
  const loading = ref(false)

  //Obtiene del backend todos los juegos de la wishlist del usuario y los guarda en items
  async function fetchWishlist() {
    loading.value = true
    try {
      const { data } = await api.get('/api/wishlist')
      items.value = data
    } finally {
      loading.value = false
    }
  }

  //Añade un juego a la wishlist lo inserta al principio del array para que aparezca inmediatamente en la vista
  async function addToWishlist(gameId, priority = 0) {
    const toast = useToast()
    const { data } = await api.post('/api/wishlist', { game_id: gameId, priority })
    items.value.unshift(data)
    toast.success('Juego añadido a tu wishlist.')
    return data
  }

  //Elimina un juego de la wishlist tanto en el backend como del array filtrando por id
  async function removeFromWishlist(wishlistId) {
    const toast = useToast()
    await api.delete(`/api/wishlist/${wishlistId}`)
    items.value = items.value.filter(i => i.id !== wishlistId)
    toast.success('Juego eliminado de la wishlist.')
  }

  //Comprueba si un juego concreto está en la wishlist del usuario
  function hasGame(gameId) {
    return items.value.some(i => i.game_id === gameId)
  }

  //Devuelve la entrada completa de la wishlist de un juego concreto
  function getEntry(gameId) {
    return items.value.find(i => i.game_id === gameId)
  }

  return { items, loading, fetchWishlist, addToWishlist, removeFromWishlist, hasGame, getEntry }
})
