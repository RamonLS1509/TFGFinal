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
      <p class="text-gray-500 text-sm mt-1">
        Base URL: <code class="text-blue-400 bg-gray-800 px-2 py-0.5 rounded">http://localhost:8000/api/v1</code>
      </p>
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

      <div v-if="stats" class="flex flex-col gap-6">

        <!-- Usuarios registrados -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h3 class="text-sm font-medium text-gray-400 mb-4 uppercase tracking-wider">Usuarios registrados</h3>
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-blue-900/50 border border-blue-800 flex items-center justify-center text-2xl">
              👥
            </div>
            <div>
              <p class="text-4xl font-bold text-blue-400">{{ stats.platform.total_users }}</p>
              <p class="text-sm text-gray-500 mt-1">usuarios registrados en la plataforma</p>
            </div>
          </div>
        </div>

        <!-- JSON raw -->
        <div class="bg-gray-950 rounded-xl border border-gray-800 p-4">
          <p class="text-xs text-gray-500 mb-2">Respuesta JSON</p>
          <pre class="text-xs text-green-400 overflow-auto max-h-48">{{ JSON.stringify({ success: true, data: stats }, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <!-- SEARCH TAB -->
    <div v-if="activeTab === 'search'">
      <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h2 class="text-xl font-bold mb-1">GET /search</h2>
        <p class="text-gray-400 text-sm mb-4">Búsqueda avanzada — puedes buscar por cualquier campo</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
          <BaseInput v-model="searchQuery"        label="Búsqueda (q) *"  placeholder="Elden Ring, FromSoftware..." />
          <BaseInput v-model="searchFilters.genre"    label="Género"          placeholder="RPG" />
          <BaseInput v-model="searchFilters.platform" label="Plataforma"      placeholder="Windows" />
          <BaseInput v-model="searchFilters.min_price" label="Precio mínimo"  type="number" placeholder="0" />
          <BaseInput v-model="searchFilters.max_price" label="Precio máximo"  type="number" placeholder="60" />
          <BaseInput v-model="searchFilters.min_score" label="Score mínimo"   type="number" placeholder="75" />
        </div>

        <!-- Info de campos buscables -->
        <div class="bg-gray-800 rounded-lg p-3 mb-4">
          <p class="text-xs text-gray-400 mb-2 font-medium">El campo <code class="text-blue-400">q</code> busca en todos estos campos a la vez:</p>
          <div class="flex flex-wrap gap-2">
            <span v-for="field in searchableFields" :key="field"
              class="text-xs bg-gray-700 text-gray-300 rounded px-2 py-0.5">
              {{ field }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <BaseButton @click="loadSearch" :loading="loadingSearch" :disabled="searchQuery.length < 2">
            Buscar
          </BaseButton>
          <code class="text-xs text-gray-500 bg-gray-800 rounded px-3 py-2 flex-1 truncate">
            GET /api/v1/search?q={{ searchQuery || '...' }}{{ searchFilters.genre ? `&genre=${searchFilters.genre}` : '' }}{{ searchFilters.platform ? `&platform=${searchFilters.platform}` : '' }}{{ searchFilters.min_price ? `&min_price=${searchFilters.min_price}` : '' }}{{ searchFilters.max_price ? `&max_price=${searchFilters.max_price}` : '' }}{{ searchFilters.min_score ? `&min_score=${searchFilters.min_score}` : '' }}
          </code>
        </div>
      </div>

      <div v-if="searchResults !== null">
        <p class="text-gray-500 text-sm mb-4">
          {{ searchResults.length }} resultado(s) para "<span class="text-gray-300">{{ lastQuery }}</span>"
        </p>

        <div v-if="searchResults.length === 0" class="text-center py-12 text-gray-500">
          <p class="text-4xl mb-3">🔍</p>
          <p>No se encontraron resultados para "{{ lastQuery }}"</p>
          <p class="text-sm mt-2 text-gray-600">Prueba buscando por título, desarrollador, publisher o descripción</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <GameCard v-for="game in searchResults" :key="game.id" :game="game" />
        </div>
      </div>
    </div>

    <!-- DOCS TAB -->
    <div v-if="activeTab === 'docs'">
      <div class="flex flex-col gap-4">
        <div v-for="endpoint in endpoints" :key="endpoint.path"
          class="bg-gray-900 rounded-xl border border-gray-800 p-6">

          <div class="flex items-start gap-3 mb-3">
            <span :class="methodClass(endpoint.method)"
              class="text-xs font-bold rounded px-2 py-1 flex-shrink-0">
              {{ endpoint.method }}
            </span>
            <div>
              <code class="text-blue-400 font-mono">{{ endpoint.path }}</code>
              <p class="text-gray-400 text-sm mt-1">{{ endpoint.description }}</p>
              <span v-if="endpoint.auth"
                class="inline-block mt-1 text-xs bg-yellow-900/30 text-yellow-400 border border-yellow-800/50 rounded px-2 py-0.5">
                🔒 Requiere autenticación
              </span>
            </div>
          </div>

          <div v-if="endpoint.params?.length" class="mt-3">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Parámetros</p>
            <div class="flex flex-col gap-1">
              <div v-for="param in endpoint.params" :key="param.name"
                class="flex items-center gap-3 text-sm bg-gray-800 rounded px-3 py-2">
                <code class="text-yellow-400 w-32 flex-shrink-0">{{ param.name }}</code>
                <span class="text-gray-500 text-xs w-16 flex-shrink-0">{{ param.type }}</span>
                <span class="text-xs px-1.5 py-0.5 rounded flex-shrink-0"
                  :class="param.required ? 'bg-red-900/50 text-red-400' : 'bg-gray-700 text-gray-400'">
                  {{ param.required ? 'requerido' : 'opcional' }}
                </span>
                <span class="text-gray-400 text-xs">{{ param.description }}</span>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Ejemplo de respuesta</p>
            <pre class="text-xs text-green-400 bg-gray-950 rounded p-3 overflow-auto max-h-48">{{ endpoint.example }}</pre>
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
  { id: 'stats',  label: '📊 Stats'  },
  { id: 'search', label: '🔍 Search' },
  { id: 'docs',   label: '📄 Docs'   },
]

// ── Stats ─────────────────────────────────────────────────────────────────────
const stats        = ref(null)
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

// ── Search ────────────────────────────────────────────────────────────────────
const searchQuery   = ref('')
const searchFilters = ref({ genre: '', platform: '', min_price: '', max_price: '', min_score: '' })
const searchResults = ref(null)
const lastQuery     = ref('')
const loadingSearch = ref(false)

const searchableFields = ['título', 'descripción', 'desarrollador', 'publisher']

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

// ── Docs ──────────────────────────────────────────────────────────────────────
function methodClass(method) {
  return {
    GET:    'bg-green-900/50 text-green-400 border border-green-800',
    POST:   'bg-blue-900/50 text-blue-400 border border-blue-800',
    PUT:    'bg-yellow-900/50 text-yellow-400 border border-yellow-800',
    DELETE: 'bg-red-900/50 text-red-400 border border-red-800',
  }[method] ?? 'bg-gray-800 text-gray-400'
}

const endpoints = [
  // ── Públicas v1 ─────────────────────────────────────────────────────────────
  {
    method: 'GET', path: '/api/v1/stats', auth: false,
    description: 'Estadísticas globales: total de usuarios registrados en la plataforma.',
    params: [],
    example: `{
  "success": true,
  "data": {
    "platform": { "total_users": 42 },
    "generated_at": "2025-01-01T00:00:00Z"
  }
}`,
  },
  {
    method: 'GET', path: '/api/v1/genres', auth: false,
    description: 'Lista todos los géneros disponibles con el número de juegos de cada uno.',
    params: [],
    example: `{
  "success": true,
  "data": [
    { "name": "RPG", "games_count": 3 },
    { "name": "Action", "games_count": 3 }
  ],
  "meta": { "total_genres": 2 }
}`,
  },
  {
    method: 'GET', path: '/api/v1/platforms', auth: false,
    description: 'Lista todas las plataformas disponibles con el número de juegos de cada una.',
    params: [],
    example: `{
  "success": true,
  "data": [
    { "name": "Windows", "games_count": 3 },
    { "name": "PlayStation", "games_count": 2 }
  ],
  "meta": { "total_platforms": 2 }
}`,
  },
  {
    method: 'GET', path: '/api/v1/search', auth: false,
    description: 'Búsqueda avanzada. El campo q busca en título, descripción, desarrollador y publisher simultáneamente.',
    params: [
      { name: 'q',          type: 'string',  required: true,  description: 'Texto a buscar (mín. 2 caracteres). Busca en título, descripción, desarrollador y publisher.' },
      { name: 'genre',      type: 'string',  required: false, description: 'Filtrar por género exacto' },
      { name: 'platform',   type: 'string',  required: false, description: 'Filtrar por plataforma exacta' },
      { name: 'min_price',  type: 'number',  required: false, description: 'Precio mínimo (€)' },
      { name: 'max_price',  type: 'number',  required: false, description: 'Precio máximo (€)' },
      { name: 'min_score',  type: 'integer', required: false, description: 'Puntuación Metacritic mínima (0-100)' },
      { name: 'limit',      type: 'integer', required: false, description: 'Número de resultados máximo (máx. 50)' },
    ],
    example: `{
  "success": true,
  "data": [
    {
      "id": 3, "title": "Elden Ring",
      "developer": "FromSoftware", "price": 54.99,
      "genres": ["RPG", "Action"], "metacritic_score": 95
    }
  ],
  "meta": { "query": "Elden", "results_count": 1, "limit": 10 }
}`,
  },
  {
    method: 'GET', path: '/api/v1/game/{slug}', auth: false,
    description: 'Detalle completo de un juego por su slug, incluyendo estadísticas de comunidad.',
    params: [
      { name: 'slug', type: 'string', required: true, description: 'Slug único del juego (ej: elden-ring)' },
    ],
    example: `{
  "success": true,
  "data": {
    "id": 3, "title": "Elden Ring", "slug": "elden-ring",
    "description": "...", "developer": "FromSoftware",
    "publisher": "Bandai Namco", "price": 54.99,
    "genres": ["RPG"], "platforms": ["Windows"],
    "metacritic_score": 95, "release_date": "2022-02-25",
    "community": { "owners_count": 5, "wished_count": 12 }
  }
}`,
  },
  // ── Juegos públicos ──────────────────────────────────────────────────────────
  {
    method: 'GET', path: '/api/games', auth: false,
    description: 'Listado paginado de juegos activos con filtros.',
    params: [
      { name: 'search',    type: 'string',  required: false, description: 'Buscar por título' },
      { name: 'genre',     type: 'string',  required: false, description: 'Filtrar por género' },
      { name: 'platform',  type: 'string',  required: false, description: 'Filtrar por plataforma' },
      { name: 'max_price', type: 'number',  required: false, description: 'Precio máximo' },
      { name: 'sort',      type: 'string',  required: false, description: 'Campo de ordenación: price, title, release_date' },
      { name: 'direction', type: 'string',  required: false, description: 'Dirección: asc, desc' },
      { name: 'page',      type: 'integer', required: false, description: 'Número de página (12 por página)' },
    ],
    example: `{
  "current_page": 1, "total": 3, "per_page": 12,
  "data": [
    { "id": 1, "title": "Cyberpunk 2077", "price": 39.99 }
  ]
}`,
  },
  {
    method: 'GET', path: '/api/games/{game}', auth: false,
    description: 'Detalle de un juego por su ID.',
    params: [
      { name: 'game', type: 'integer', required: true, description: 'ID del juego' },
    ],
    example: `{
  "id": 1, "title": "Cyberpunk 2077",
  "developer": "CD Projekt Red", "price": 39.99,
  "genres": ["RPG", "Action"], "platforms": ["Windows"]
}`,
  },
  {
    method: 'GET', path: '/api/games/{game}/reviews', auth: false,
    description: 'Reseñas de un juego con estadísticas de puntuación.',
    params: [
      { name: 'game', type: 'integer', required: true,  description: 'ID del juego' },
      { name: 'page', type: 'integer', required: false, description: 'Número de página' },
    ],
    example: `{
  "reviews": { "data": [...], "total": 5 },
  "stats": {
    "total": 5, "avg_score": 8.4,
    "recommended": 4, "distribution": { "10": 2, "8": 2, "6": 1 }
  }
}`,
  },
  // ── Auth ─────────────────────────────────────────────────────────────────────
  {
    method: 'POST', path: '/api/register', auth: false,
    description: 'Registro de un nuevo usuario.',
    params: [
      { name: 'name',                  type: 'string', required: true,  description: 'Nombre completo' },
      { name: 'username',              type: 'string', required: true,  description: 'Nombre de usuario único (alpha_dash)' },
      { name: 'email',                 type: 'string', required: true,  description: 'Email único' },
      { name: 'password',              type: 'string', required: true,  description: 'Contraseña (mín. 8 caracteres)' },
      { name: 'password_confirmation', type: 'string', required: true,  description: 'Confirmación de contraseña' },
    ],
    example: `{
  "message": "Usuario registrado correctamente.",
  "user": { "id": 1, "name": "Test", "email": "test@test.com", "role": "user" }
}`,
  },
  {
    method: 'POST', path: '/api/login', auth: false,
    description: 'Inicio de sesión. Requiere obtener el CSRF cookie antes (/sanctum/csrf-cookie).',
    params: [
      { name: 'email',    type: 'string', required: true, description: 'Email del usuario' },
      { name: 'password', type: 'string', required: true, description: 'Contraseña' },
    ],
    example: `{
  "message": "Sesión iniciada.",
  "user": { "id": 1, "name": "Test", "role": "user" }
}`,
  },
  {
    method: 'POST', path: '/api/logout', auth: true,
    description: 'Cierra la sesión del usuario autenticado.',
    params: [],
    example: `{ "message": "Sesión cerrada." }`,
  },
  // ── Perfil ───────────────────────────────────────────────────────────────────
  {
    method: 'GET', path: '/api/me', auth: true,
    description: 'Datos del usuario autenticado.',
    params: [],
    example: `{
  "id": 1, "name": "Test", "username": "testuser",
  "email": "test@test.com", "role": "user",
  "bio": null, "avatar": null
}`,
  },
  {
    method: 'GET', path: '/api/profile', auth: true,
    description: 'Perfil completo del usuario con estadísticas de biblioteca y wishlist.',
    params: [],
    example: `{
  "id": 1, "name": "Test", "username": "testuser",
  "stats": { "library_count": 3, "wishlist_count": 5 }
}`,
  },
  {
    method: 'PUT', path: '/api/profile', auth: true,
    description: 'Actualizar datos del perfil del usuario.',
    params: [
      { name: 'name',     type: 'string', required: false, description: 'Nombre completo' },
      { name: 'username', type: 'string', required: false, description: 'Nombre de usuario único' },
      { name: 'bio',      type: 'string', required: false, description: 'Biografía (máx. 300 caracteres)' },
      { name: 'avatar',   type: 'string', required: false, description: 'URL del avatar' },
    ],
    example: `{
  "message": "Perfil actualizado correctamente.",
  "user": { "id": 1, "name": "Nuevo nombre", "bio": "Mi bio" }
}`,
  },
  {
    method: 'PUT', path: '/api/profile/password', auth: true,
    description: 'Cambiar la contraseña del usuario.',
    params: [
      { name: 'current_password',      type: 'string', required: true, description: 'Contraseña actual' },
      { name: 'password',              type: 'string', required: true, description: 'Nueva contraseña (mín. 8 caracteres)' },
      { name: 'password_confirmation', type: 'string', required: true, description: 'Confirmación de nueva contraseña' },
    ],
    example: `{ "message": "Contraseña actualizada correctamente." }`,
  },
  // ── Biblioteca ───────────────────────────────────────────────────────────────
  {
    method: 'GET', path: '/api/library', auth: true,
    description: 'Listado de juegos en la biblioteca del usuario autenticado.',
    params: [],
    example: `[
  {
    "id": 1, "game_id": 3, "price_paid": 54.99,
    "purchased_at": "2025-01-01T00:00:00Z",
    "game": { "title": "Elden Ring" }
  }
]`,
  },
  {
    method: 'POST', path: '/api/library', auth: true,
    description: 'Añadir un juego a la biblioteca (compra). No puede estar ya en biblioteca.',
    params: [
      { name: 'game_id', type: 'integer', required: true, description: 'ID del juego a añadir' },
    ],
    example: `{
  "id": 1, "game_id": 3, "price_paid": 54.99,
  "purchased_at": "2025-01-01T00:00:00Z"
}`,
  },
  {
    method: 'DELETE', path: '/api/library/{library}', auth: true,
    description: 'Eliminar un juego de la biblioteca. Solo el propietario puede eliminarlo.',
    params: [
      { name: 'library', type: 'integer', required: true, description: 'ID de la entrada de biblioteca' },
    ],
    example: `{ "message": "Juego eliminado de la biblioteca." }`,
  },
  // ── Wishlist ─────────────────────────────────────────────────────────────────
  {
    method: 'GET', path: '/api/wishlist', auth: true,
    description: 'Listado de juegos en la wishlist del usuario.',
    params: [],
    example: `[
  {
    "id": 1, "game_id": 2, "priority": 1,
    "game": { "title": "The Witcher 3" }
  }
]`,
  },
  {
    method: 'POST', path: '/api/wishlist', auth: true,
    description: 'Añadir un juego a la wishlist. No puede estar ya en biblioteca ni en wishlist.',
    params: [
      { name: 'game_id',  type: 'integer', required: true,  description: 'ID del juego' },
      { name: 'priority', type: 'integer', required: false, description: 'Prioridad: 0 = normal, 1 = favorito' },
    ],
    example: `{ "id": 1, "game_id": 2, "priority": 0 }`,
  },
  {
    method: 'PUT', path: '/api/wishlist/{wishlist}', auth: true,
    description: 'Actualizar la prioridad de un juego en la wishlist.',
    params: [
      { name: 'wishlist', type: 'integer', required: true, description: 'ID de la entrada de wishlist' },
      { name: 'priority', type: 'integer', required: true, description: '0 = normal, 1 = favorito' },
    ],
    example: `{ "id": 1, "game_id": 2, "priority": 1 }`,
  },
  {
    method: 'DELETE', path: '/api/wishlist/{wishlist}', auth: true,
    description: 'Eliminar un juego de la wishlist.',
    params: [
      { name: 'wishlist', type: 'integer', required: true, description: 'ID de la entrada de wishlist' },
    ],
    example: `{ "message": "Juego eliminado de la wishlist." }`,
  },
  // ── Reseñas ──────────────────────────────────────────────────────────────────
  {
    method: 'POST', path: '/api/reviews', auth: true,
    description: 'Crear una reseña. Solo una reseña por juego por usuario.',
    params: [
      { name: 'game_id',     type: 'integer', required: true,  description: 'ID del juego' },
      { name: 'score',       type: 'integer', required: true,  description: 'Puntuación del 1 al 10' },
      { name: 'title',       type: 'string',  required: true,  description: 'Título de la reseña (máx. 150 caracteres)' },
      { name: 'body',        type: 'string',  required: true,  description: 'Cuerpo de la reseña (mín. 20, máx. 2000 caracteres)' },
      { name: 'recommended', type: 'boolean', required: false, description: 'Si recomienda el juego (default: true)' },
    ],
    example: `{
  "id": 1, "game_id": 3, "score": 9,
  "title": "Obra maestra", "recommended": true,
  "user": { "name": "Test", "username": "testuser" }
}`,
  },
  {
    method: 'PUT', path: '/api/reviews/{review}', auth: true,
    description: 'Actualizar una reseña propia.',
    params: [
      { name: 'review',      type: 'integer', required: true,  description: 'ID de la reseña' },
      { name: 'score',       type: 'integer', required: false, description: 'Nueva puntuación (1-10)' },
      { name: 'title',       type: 'string',  required: false, description: 'Nuevo título' },
      { name: 'body',        type: 'string',  required: false, description: 'Nuevo cuerpo' },
      { name: 'recommended', type: 'boolean', required: false, description: 'Nueva recomendación' },
    ],
    example: `{ "id": 1, "score": 10, "title": "Título actualizado" }`,
  },
  {
    method: 'DELETE', path: '/api/reviews/{review}', auth: true,
    description: 'Eliminar una reseña. El propietario o un admin pueden eliminarla.',
    params: [
      { name: 'review', type: 'integer', required: true, description: 'ID de la reseña' },
    ],
    example: `{ "message": "Reseña eliminada." }`,
  },
]

onMounted(() => {
  loadStats()
})
</script>
