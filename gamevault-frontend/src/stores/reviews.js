import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

//Gestiona el estado global de las reseñas de la aplicacion
export const useReviewsStore = defineStore('reviews', () => {
  const reviews   = ref([])
  const stats     = ref(null)
  const myReview  = ref(null)
  const myReviews = ref([])
  const pagination = ref(null)
  const loading   = ref(false)

  //Obtiene las reseñas y estadisticas de un juego concreto.
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
  //Obtiene la reseña del usuario para un juego concreto
  async function fetchMyReviews() {
    loading.value = true
    try {
      const { data } = await api.get('/api/reviews/my')
      myReviews.value = data
    } finally {
      loading.value = false
    }
  }

//Crea una reseña, la añade al principio del listado actual, actualiza las estadisticas incrementando el total y los recomendados si procede
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

  //Actualiza una reseña del backend y la reemplaza en el array buscandola por id
  async function updateReview(reviewId, payload) {
    const toast = useToast()
    const { data } = await api.put(`/api/reviews/${reviewId}`, payload)
    myReview.value = data
    const index = reviews.value.findIndex(r => r.id === reviewId)
    if (index !== -1) reviews.value[index] = data
    toast.success('Reseña actualizada correctamente.')
    return data
  }

  //Elimina una reseña, limpia myReview y decrementa el contador de estadisticas
  async function deleteReview(reviewId) {
    const toast = useToast()
    if (!reviewId) throw new Error('reviewId es undefined')
    await api.delete(`/api/reviews/${reviewId}`)
    myReview.value = null
    reviews.value  = reviews.value.filter(r => r.id !== reviewId)
    if (stats.value) stats.value.total--
    toast.success('Reseña eliminada.')
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
