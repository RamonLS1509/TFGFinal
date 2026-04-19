import api from './api'

const BASE = '/api/v1'

export const publicApi = {
  // Estadísticas globales de la plataforma
  getStats() {
    return api.get(`${BASE}/stats`)
  },

  // Rankings: most_owned | top_rated | most_wished | newest
  getRankings(type = 'most_owned', limit = 10) {
    return api.get(`${BASE}/rankings`, { params: { type, limit } })
  },

  // Géneros disponibles con conteo
  getGenres() {
    return api.get(`${BASE}/genres`)
  },

  // Plataformas disponibles con conteo
  getPlatforms() {
    return api.get(`${BASE}/platforms`)
  },

  // Búsqueda avanzada
  search(query, filters = {}) {
    return api.get(`${BASE}/search`, { params: { q: query, ...filters } })
  },

  // Detalle por slug
  getGameBySlug(slug) {
    return api.get(`${BASE}/game/${slug}`)
  },
}
