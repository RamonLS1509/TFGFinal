import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

//Gestiona el estado global de la biblioteca del usuario
export const useLibraryStore = defineStore('library', () => {
  const entries = ref([])
  const loading = ref(false)

  //Obtiene del backend todos los juegos de la biblioteca del usuario y los guarda en entries
  async function fetchLibrary() {
    loading.value = true
    try {
      const { data } = await api.get('/api/library')
      entries.value = data
    } finally {
      loading.value = false
    }
  }

  //Añade un juego a la biblioteca, lo inserta al principio del array con unshift y muestra un toast de confirmacion
  async function addToLibrary(gameId) {
    const toast = useToast()
    const { data } = await api.post('/api/library', { game_id: gameId })
    entries.value.unshift(data)
    toast.success('¡Juego añadido a tu biblioteca!')
    return data
  }

  //Elimina un juego de la biblioteca tanto en el backend como del array filtrando por id, y muestra un toast de confirmacion
  async function removeFromLibrary(libraryId) {
    const toast = useToast()
    await api.delete(`/api/library/${libraryId}`)
    entries.value = entries.value.filter(e => e.id !== libraryId)
    toast.success('Juego eliminado de la biblioteca.')
  }

  //Comprueba si el usuario ya tiene un juego concreto en su biblioteca buscando en el array local.
  function ownsGame(gameId) {
    return entries.value.some(e => e.game_id === gameId)
  }

  return { entries, loading, fetchLibrary, addToLibrary, removeFromLibrary, ownsGame }
})
