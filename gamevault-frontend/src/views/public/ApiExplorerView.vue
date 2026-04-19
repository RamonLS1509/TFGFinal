<template>
  <div class="w-full max-w-screen-xl mx-auto px-6 py-8">

    <!-- Header -->
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-2">
        <span class="text-3xl">⚡</span>
        <h1 class="text-3xl font-bold">GameVault Public API</h1>
        <span class="text-xs bg-blue-900/50 text-blue-300 border border-blue-800 rounded-full px-3 py-1">v1</span>
      </div>
      <p class="text-gray-400 text-lg">API pública de SteamClone. Sin autenticación requerida.</p>
      <p class="text-gray-500 text-sm mt-1">Base URL: <code class="text-blue-400 bg-gray-800 px-2 py-0.5 rounded">http://localhost:8000/api/v1</code></p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-8 flex-wrap">
      <button v-for="tab in tabs" :key="tab.id"
        @click="activeTab = tab.id"
        :class="activeTab === tab.id
          ? 'bg-blue-600 text-white'
          : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700'"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        {{ tab.label }}
      </button>
    </div>

    <!-- STATS TAB -->
    <div v-if="activeTab === 'stats'">
      <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xl font-bold">GET /stats</h2>
            <p class="text-gray-400 text-sm mt-1">Estadísticas globales de la plataforma</p>
          </div>
          <BaseButton @click="loadStats" :loading="loadingStats" size="sm">
            Ejecutar
          </BaseButton>
        </div>
        <code class="block text-xs text-gray-500 bg-gray-800 rounded px-3 py-2">
          GET http://localhost:8000/api/v1/stats
        </code>
      </div>

      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Platform stats -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h3 class="text-sm font-medium text-gray-400 mb-4 uppercase tracking-wider">Plataforma</h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
              <p class="text-3xl font-bold text-blue-400">{{ stats.platform.total_games }}</p>
              <p class="text-xs text-gray-500 mt-1">Juegos</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-green-400">{{ stats.platform.total_users }}</p>
              <p class="text-xs text-gray-500 mt-1">Usuarios</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-purple-400">{{ stats.platform.total_library_entries }}</p>
              <p class="text-xs text-gray-500 mt-1">Compras</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-yellow-400">{{ stats.platform.total_wishlist_entries }}</p>
              <p class="text-xs text-gray-500 mt-1">Wishlists</p>
            </div>
          </div>
        </div>

        <!-- Games stats -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h3 class="text-sm font-medium text-gray-400 mb-4 uppercase tracking-wider">Juegos</h3>
          <div class="flex flex-col gap-3">
            <div class="flex justify-between items-center border-b border-gray-800 pb-2">
              <span class="text-gray-400 text-sm">Precio medio</span>
              <span class="font-bold text-blue-400">{{ stats.games.avg_price }} €</span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-800 pb-2">
              <span class="text-gray-400 text-sm">Precio máximo</span>
              <span class="font-bold">{{ stats.games.max_price }} €</span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-800 pb-2">
              <span class="text-gray-400 text-sm">Precio mínimo</span>
              <span class="font-bold">{{ stats.games.min_price }} €</span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-800 pb-2">
              <span class="text-gray-400 text-sm">Juegos gratis</span>
              <span class="font-bold text-green-400">{{ stats.games.free_games }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-400 text-sm">Media Metacritic</span>
              <span class="font-bold text-yellow-400">{{ stats.games.avg_metacritic }}</span>
            </div>
          </div>
        </div>

        <!-- JSON raw -->
        <div class="md:col-span-2 bg-gray-950 rounded-xl border border-gray-800 p-4">
          <p class="text-xs text-gray-500 mb-2">Respuesta JSON</p>
          <pre class="text-xs text-green-400 overflow-auto max-h-48">{{ JSON.stringify({ success: true, data: stats }, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <!-- RANKINGS TAB -->
    <div v-if="activeTab === 'rankings'">
      <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
          <div>
            <h2 class="text-xl font-bold">GET /rankings</h2>
            <p class="text-gray-400 text-sm mt-1">Rankings de juegos por distintos criterios</p>
          </div>
          <div class="flex gap-2 flex-wrap">
            <select v-model="rankingType"
              class="bg-gray-800 border border-gray-700 rounded px-3 py-1.5 text-sm text-gray-100 focus:outline-none focus:border-blue-500">
              <option value="most_owned">Más jugados</option>
              <option value="top_rated">Mejor valorados</option>
              <option value="most_wished">Más deseados</option>
              <option value="newest">Más recientes</option>
            </select>
            <BaseButton @click="loadRankings" :loading="loadingRankings" size="sm">
              Ejecutar
            </BaseButton>
          </div>
        </div>
        <code class="block text-xs text-gray-500 bg-gray-800 rounded px-3 py-2">
          GET http://localhost:8000/api/v1/rankings?type={{ rankingType }}&limit=5
        </code>
      </div>

      <div v-if="rankings.length" class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-800 text-gray-400">
            <tr>
              <th class="text-left px-4 py-3 w-12">#</th>
              <th class="text-left px-4 py-3">Juego</th>
              <th class="text-left px-4 py-3 hidden sm:table-cell">Géneros</th>
              <th class="text-right px-4 py-3">Precio</th>
              <th class="text-right px-4 py-3 hidden md:table-cell">
                {{ rankingType === 'top_rated' ? 'Metacritic' :
                   rankingType === 'most_owned' ? 'Dueños' :
                   rankingType === 'most_wished' ? 'Deseado' : 'Lanzamiento' }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="game in rankings" :key="game.id"
              class="border-t border-gray-800 hover:bg-gray-800/50 transition-colors">
              <td class="px-4 py-3">
                <span :class="game.rank <= 3 ? 'text-yellow-400 font-bold' : 'text-gray-500'">
                  {{ game.rank <= 3 ? ['🥇','🥈','🥉'][game.rank-1] : `#${game.rank}` }}
                </span>
              </td>
              <td class="px-4 py-3">
                <RouterLink :to="{ name: 'game-detail', params: { id: game.id } }"
                  class="font-medium hover:text-blue-400 transition-colors">
                  {{ game.title }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 hidden sm:table-cell">
                <div class="flex gap-1 flex-wrap">
                  <span v-for="g in game.genres?.slice(0,2)" :key="g"
                    class="text-xs px-2 py-0.5 rounded bg-gray-700 text-gray-300">{{ g }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-right text-blue-400 font-bold">{{ game.price }} €</td>
              <td class="px-4 py-3 text-right hidden md:table-cell">
                <span v-if="rankingType === 'top_rated'" :class="scoreClass(game.metacritic_score)" class="font-bold">
                  {{ game.metacritic_score }}
                </span>
                <span v-else-if="rankingType === 'most_owned'" class="text-gray-300">{{ game.owners_count }}</span>
                <span v-else-if="rankingType === 'most_wished'" class="text-gray-300">{{ game.wished_count }}</span>
                <span v-else class="text-gray-400 text-xs">{{ game.release_date }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SEARCH TAB -->
    <div v-if="activeTab === 'search'">
      <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h2 class="text-xl font-bold mb-1">GET /search</h2>
        <p class="text-gray-400 text-sm mb-4">Búsqueda avanzada con filtros combinables</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
          <BaseInput v-model="searchQuery" label="Búsqueda (q) *" placeholder="Elden Ring..." />
          <BaseInput v-model="searchFilters.genre" label="Género" placeholder="RPG" />
          <BaseInput v-model="searchFilters.platform" label="Plataforma" placeholder="Windows" />
          <BaseInput v-model="searchFilters.min_price" label="Precio mínimo" type="number" placeholder="0" />
          <BaseInput v-model="searchFilters.max_price" label="Precio máximo" type="number" placeholder="60" />
          <BaseInput v-model="searchFilters.min_score" label="Score mínimo" type="number" placeholder="75" />
        </div>

        <div class="flex items-center gap-4">
          <BaseButton @click="loadSearch" :loading="loadingSearch" :disabled="searchQuery.length < 2">
            Buscar
          </BaseButton>
          <code class="text-xs text-gray-500 bg-gray-800 rounded px-3 py-2 flex-1 truncate">
            GET /api/v1/search?q={{ searchQuery }}{{ searchFilters.genre ? `&genre=${searchFilters.genre}` : '' }}{{ searchFilters.max_price ? `&max_price=${searchFilters.max_price}` : '' }}
          </code>
        </div>
      </div>

      <div v-if="searchResults !== null">
        <p class="text-gray-500 text-sm mb-4">{{ searchResults.length }} resultado(s) para "{{ lastQuery }}"</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <GameCard v-for="game in searchResults" :key="game.id" :game="game" />
        </div>
        <div v-if="searchResults.length === 0" class="text-center py-12 text-gray-500">
          <p class="text-4xl mb-3">🔍</p>
          <p>No se encontraron resultados para "{{ lastQuery }}"</p>
        </div>
      </div>
    </div>

    <!-- DOCS TAB -->
    <div v-if="activeTab === 'docs'">
      <div class="flex flex-col gap-4">
        <div v-for="endpoint in endpoints" :key="endpoint.path"
          class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <div class="flex items-start gap-3 mb-3">
            <span class="text-xs font-bold bg-green-900/50 text-green-400 border border-green-800 rounded px-2 py-1 flex-shrink-0">
              GET
            </span>
            <div>
              <code class="text-blue-400 font-mono">{{ endpoint.path }}</code>
              <p class="text-gray-400 text-sm mt-1">{{ endpoint.description }}</p>
            </div>
          </div>

          <div v-if="endpoint.params?.length" class="mt-3">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Parámetros</p>
            <div class="flex flex-col gap-1">
              <div v-for="param in endpoint.params" :key="param.name"
                class="flex items-center gap-3 text-sm bg-gray-800 rounded px-3 py-2">
                <code class="text-yellow-400 min-w-24">{{ param.name }}</code>
                <span class="text-gray-500 text-xs min-w-16">{{ param.type }}</span>
                <span class="text-xs bg-gray-700 rounded px-1.5">{{ param.required ? 'requerido' : 'opcional' }}</span>
                <span class="text-gray-400 text-xs">{{ param.description }}</span>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Ejemplo de respuesta</p>
            <pre class="text-xs text-green-400 bg-gray-950 rounded p-3 overflow-auto max-h-40">{{ endpoint.example }}</pre>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { publicApi } from '@/services/publicApi'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import GameCard from '@/components/games/GameCard.vue'

const activeTab = ref('stats')
const tabs = [
  { id: 'stats',    label: '📊 Stats' },
  { id: 'rankings', label: '🏆 Rankings' },
  { id: 'search',   label: '🔍 Search' },
  { id: 'docs',     label: '📄 Docs' },
]

// Stats
const stats = ref(null)
const loadingStats = ref(false)
async function loadStats() {
  loadingStats.value = true
  try {
    const { data } = await publicApi.getStats()
    stats.value = data.data
  } finally {
    loadingStats.value = false
  }
}

// Rankings
const rankings = ref([])
const rankingType = ref('most_owned')
const loadingRankings = ref(false)
async function loadRankings() {
  loadingRankings.value = true
  try {
    const { data } = await publicApi.getRankings(rankingType.value, 10)
    rankings.value = data.data
  } finally {
    loadingRankings.value = false
  }
}

// Search
const searchQuery = ref('')
const searchFilters = ref({ genre: '', platform: '', min_price: '', max_price: '', min_score: '' })
const searchResults = ref(null)
const lastQuery = ref('')
const loadingSearch = ref(false)
async function loadSearch() {
  if (searchQuery.value.length < 2) return
  loadingSearch.value = true
  lastQuery.value = searchQuery.value
  try {
    const filters = Object.fromEntries(
      Object.entries(searchFilters.value).filter(([, v]) => v !== '')
    )
    const { data } = await publicApi.search(searchQuery.value, filters)
    searchResults.value = data.data
  } finally {
    loadingSearch.value = false
  }
}

function scoreClass(score) {
  if (score >= 75) return 'text-green-400'
  if (score >= 50) return 'text-yellow-400'
  return 'text-red-400'
}

// Docs
const endpoints = [
  {
    path: '/api/v1/stats',
    description: 'Estadísticas globales de la plataforma: juegos, usuarios, compras y precios.',
    params: [],
    example: `{
  "success": true,
  "data": {
    "platform": { "total_games": 3, "total_users": 1 },
    "games": { "avg_price": 34.99, "avg_metacritic": 91.0 }
  }
}`,
  },
  {
    path: '/api/v1/rankings',
    description: 'Rankings de juegos ordenados por distintos criterios.',
    params: [
      { name: 'type',  type: 'string',  required: false, description: 'most_owned | top_rated | most_wished | newest' },
      { name: 'limit', type: 'integer', required: false, description: 'Número de resultados (máx. 50)' },
    ],
    example: `{
  "success": true,
  "data": [
    { "rank": 1, "title": "Elden Ring", "metacritic_score": 95 }
  ],
  "meta": { "type": "top_rated", "limit": 10 }
}`,
  },
  {
    path: '/api/v1/genres',
    description: 'Lista todos los géneros disponibles con el número de juegos de cada uno.',
    params: [],
    example: `{
  "success": true,
  "data": [
    { "name": "RPG", "games_count": 3 },
    { "name": "Action", "games_count": 3 }
  ]
}`,
  },
  {
    path: '/api/v1/platforms',
    description: 'Lista todas las plataformas disponibles con el número de juegos de cada una.',
    params: [],
    example: `{
  "success": true,
  "data": [
    { "name": "Windows", "games_count": 3 }
  ]
}`,
  },
  {
    path: '/api/v1/search',
    description: 'Búsqueda avanzada de juegos por título, descripción, desarrollador o publisher.',
    params: [
      { name: 'q',         type: 'string',  required: true,  description: 'Texto a buscar (mín. 2 caracteres)' },
      { name: 'genre',     type: 'string',  required: false, description: 'Filtrar por género' },
      { name: 'platform',  type: 'string',  required: false, description: 'Filtrar por plataforma' },
      { name: 'min_price', type: 'number',  required: false, description: 'Precio mínimo' },
      { name: 'max_price', type: 'number',  required: false, description: 'Precio máximo' },
      { name: 'min_score', type: 'integer', required: false, description: 'Puntuación Metacritic mínima' },
      { name: 'limit',     type: 'integer', required: false, description: 'Número de resultados (máx. 50)' },
    ],
    example: `{
  "success": true,
  "data": [
    { "id": 3, "title": "Elden Ring", "price": 54.99 }
  ],
  "meta": { "query": "Elden", "results_count": 1 }
}`,
  },
  {
    path: '/api/v1/game/{slug}',
    description: 'Detalle completo de un juego por su slug, incluyendo estadísticas de comunidad.',
    params: [
      { name: 'slug', type: 'string', required: true, description: 'Slug único del juego (ej: elden-ring)' },
    ],
    example: `{
  "success": true,
  "data": {
    "title": "Elden Ring",
    "slug": "elden-ring",
    "community": { "owners_count": 1, "wished_count": 2 }
  }
}`,
  },
]

onMounted(() => {
  loadStats()
})
</script>
