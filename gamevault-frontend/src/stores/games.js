import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useGamesStore = defineStore('games', () => {
  const games = ref([])
  const currentGame = ref(null)
  const pagination = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchGames(params = {}) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/api/games', { params })
      games.value = data.data
      pagination.value = {
        currentPage: data.current_page,
        lastPage:    data.last_page,
        total:       data.total,
        perPage:     data.per_page,
      }
    } catch (e) {
      error.value = 'Error al cargar los juegos.'
    } finally {
      loading.value = false
    }
  }

  async function fetchGame(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/api/games/${id}`)
      currentGame.value = data
    } finally {
      loading.value = false
    }
  }

  // Admin
  async function createGame(payload) {
    const { data } = await api.post('/api/games', payload)
    return data
  }

  async function updateGame(id, payload) {
    const { data } = await api.put(`/api/games/${id}`, payload)
    return data
  }

  async function deleteGame(id) {
    await api.delete(`/api/games/${id}`)
    games.value = games.value.filter(g => g.id !== id)
  }

  return { games, currentGame, pagination, loading, error,
           fetchGames, fetchGame, createGame, updateGame, deleteGame }
})
