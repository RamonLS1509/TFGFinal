import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

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
    const toast = useToast()
    const { data } = await api.post('/api/library', { game_id: gameId })
    entries.value.unshift(data)
    toast.success('¡Juego añadido a tu biblioteca!')
    return data
  }

  async function removeFromLibrary(libraryId) {
    const toast = useToast()
    await api.delete(`/api/library/${libraryId}`)
    entries.value = entries.value.filter(e => e.id !== libraryId)
    toast.success('Juego eliminado de la biblioteca.')
  }

  function ownsGame(gameId) {
    return entries.value.some(e => e.game_id === gameId)
  }

  return { entries, loading, fetchLibrary, addToLibrary, removeFromLibrary, ownsGame }
})
