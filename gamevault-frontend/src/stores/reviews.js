import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

export const useReviewsStore = defineStore('reviews', () => {
  const reviews   = ref([])
  const stats     = ref(null)
  const myReview  = ref(null)
  const myReviews = ref([])
  const pagination = ref(null)
  const loading   = ref(false)

  async function fetchGameReviews(gameId, page = 1) {
    loading.value = true
    try {
      const { data } = await api.get(`/api/games/${gameId}/reviews`, { params: { page } })
      reviews.value  = data.reviews.data
      stats.value    = data.stats
      pagination.value = {
        currentPage: data.reviews.current_page,
        lastPage:    data.reviews.last_page,
        total:       data.reviews.total,
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchMyReview(gameId) {
  try {
    const { data } = await api.get(`/api/games/${gameId}/reviews/mine`)
    // Si data es null o vacío, myReview debe ser null
    myReview.value = data ?? null
  } catch {
    myReview.value = null
  }
}

  async function fetchMyReviews() {
    loading.value = true
    try {
      const { data } = await api.get('/api/reviews/my')
      myReviews.value = data
    } finally {
      loading.value = false
    }
  }

  async function createReview(payload) {
    const toast = useToast()
    const { data } = await api.post('/api/reviews', payload)
    myReview.value = data
    reviews.value.unshift(data)
    if (stats.value) {
      stats.value.total++
      if (data.recommended) stats.value.recommended++
    }
    toast.success('¡Reseña publicada correctamente!')
    return data
  }

  async function updateReview(reviewId, payload) {
    const toast = useToast()
    const { data } = await api.put(`/api/reviews/${reviewId}`, payload)
    myReview.value = data
    const index = reviews.value.findIndex(r => r.id === reviewId)
    if (index !== -1) reviews.value[index] = data
    toast.success('Reseña actualizada correctamente.')
    return data
  }

  async function deleteReview(reviewId) {
    const toast = useToast()
    if (!reviewId) throw new Error('reviewId es undefined')
    await api.delete(`/api/reviews/${reviewId}`)
    myReview.value = null
    reviews.value  = reviews.value.filter(r => r.id !== reviewId)
    if (stats.value) stats.value.total--
    toast.success('Reseña eliminada.')
  }

  async function handleBuy() {
  buyLoading.value = true
  actionError.value = ''
  try {
    await libraryStore.addToLibrary(game.value.id)
    // Si estaba en wishlist, se elimina automáticamente al comprar
    if (inWishlist.value) {
      const entry = wishlistStore.getEntry(game.value.id)
      await wishlistStore.removeFromWishlist(entry.id)
    }
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Error al añadir el juego.'
  } finally {
    buyLoading.value = false
  }
}

  function reset() {
    reviews.value    = []
    stats.value      = null
    myReview.value   = null
    pagination.value = null
  }

  return {
    reviews, stats, myReview, myReviews, pagination, loading,
    fetchGameReviews, fetchMyReview, fetchMyReviews,
    createReview, updateReview, deleteReview, reset,
  }
})
