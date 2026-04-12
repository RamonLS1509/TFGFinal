<template>
  <div class="w-full max-w-screen-2xl mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold mb-6">Catálogo</h1>

    <!-- Filtros -->
    <div class="flex flex-col sm:flex-row flex-wrap gap-3 mb-8 p-4 bg-gray-900 rounded-lg border border-gray-800">
      <div class="w-full sm:flex-1 sm:min-w-48">
        <BaseInput v-model="filters.search" placeholder="Buscar juego..." @input="onSearch" />
      </div>
      <select v-model="filters.genre" @change="applyFilters"
        class="w-full sm:w-auto bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:outline-none focus:border-blue-500">
        <option value="">Todos los géneros</option>
        <option v-for="genre in availableGenres" :key="genre" :value="genre">{{ genre }}</option>
      </select>
      <select v-model="filters.sort" @change="applyFilters"
        class="w-full sm:w-auto bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:outline-none focus:border-blue-500">
        <option value="">Más recientes</option>
        <option value="price_asc">Precio: menor a mayor</option>
        <option value="price_desc">Precio: mayor a menor</option>
        <option value="title">Título A-Z</option>
      </select>
    </div>

    <LoadingSpinner v-if="gamesStore.loading" />

    <template v-else>
      <p class="text-gray-500 text-sm mb-4">{{ gamesStore.pagination?.total || 0 }} juegos encontrados</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
        <GameCard v-for="game in gamesStore.games" :key="game.id" :game="game" />
      </div>

      <!-- Paginación -->
      <div v-if="gamesStore.pagination?.lastPage > 1" class="flex flex-wrap justify-center gap-2 mt-8">
        <BaseButton
          v-for="page in gamesStore.pagination.lastPage" :key="page"
          :variant="page === gamesStore.pagination.currentPage ? 'primary' : 'ghost'"
          size="sm"
          @click="goToPage(page)">
          {{ page }}
        </BaseButton>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useGamesStore } from '@/stores/games'
import { useLibraryStore } from '@/stores/library'
import { useAuthStore } from '@/stores/auth'
import GameCard from '@/components/games/GameCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const gamesStore = useGamesStore()
const libraryStore = useLibraryStore()
const auth = useAuthStore()

const filters = ref({ search: '', genre: '', sort: '' })
const availableGenres = ['Action', 'RPG', 'Strategy', 'Simulation', 'Sports', 'Indie', 'Open World', 'Souls-like']
let searchTimer = null

function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 400)
}

function buildParams(page = 1) {
  const params = { page }
  if (filters.value.search) params.search = filters.value.search
  if (filters.value.genre)  params.genre  = filters.value.genre
  if (filters.value.sort === 'price_asc')  { params.sort = 'price'; params.direction = 'asc' }
  if (filters.value.sort === 'price_desc') { params.sort = 'price'; params.direction = 'desc' }
  if (filters.value.sort === 'title')      { params.sort = 'title'; params.direction = 'asc' }
  return params
}

async function applyFilters() { await gamesStore.fetchGames(buildParams()) }
async function goToPage(page) {
  await gamesStore.fetchGames(buildParams(page))
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  await gamesStore.fetchGames()
  if (auth.isAuthenticated) await libraryStore.fetchLibrary()
})
</script>
