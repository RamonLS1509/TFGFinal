import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

//Gestiona el estado global de los juegos de la aplicación
export const useGamesStore = defineStore('games', () => {
  const games = ref([])
  const currentGame = ref(null)
  const pagination = ref(null)
  const loading = ref(false)
  const error = ref(null)

  //Obtiene el listado paginado de juegos aceptando parametros globales como filtros o pagina actual
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
//Obtiene el detalle de un juego concreto por su id y lo guarda en currentGame
  async function fetchGame(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/api/games/${id}`)
      currentGame.value = data
    } finally {
      loading.value = false
    }
  }

  //Envía los datos al backend para crear un nuevo juego y devuelve el resultado.
  async function createGame(payload) {
    const { data } = await api.post('/api/games', payload)
    return data
  }
  //Actualiza un juego existente por su id y devuelve el resultado actualizado
  async function updateGame(id, payload) {
    const { data } = await api.put(`/api/games/${id}`, payload)
    return data
  }
  //Elimina un juego del backend y admeas lo elimina del array games sin necesidad de volver a llamar a la API
  async function deleteGame(id) {
    await api.delete(`/api/games/${id}`)
    games.value = games.value.filter(g => g.id !== id)
  }

  return { games, currentGame, pagination, loading, error,
           fetchGames, fetchGame, createGame, updateGame, deleteGame }
})
