import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useLibraryStore = defineStore('library', () => {
  const entries = ref([])
  const loading = ref(false)

  async function fetchLibrary() {
    loading.value = true
    try {
      const { data } = await api.get('/api/library')
      entries.value = data
    } finally {
      loading.value = false
    }
  }

  async function addToLibrary(gameId) {
    const { data } = await api.post('/api/library', { game_id: gameId })
    entries.value.unshift(data)
    return data
  }

  async function removeFromLibrary(libraryId) {
    await api.delete(`/api/library/${libraryId}`)
    entries.value = entries.value.filter(e => e.id !== libraryId)
  }

  function ownsGame(gameId) {
    return entries.value.some(e => e.game_id === gameId)
  }

  return { entries, loading, fetchLibrary, addToLibrary, removeFromLibrary, ownsGame }
})
