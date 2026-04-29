<template>
  <div class="min-h-[80vh] flex items-center justify-center px-6">
    <div class="text-center max-w-lg">

      <!-- Número 404 grande -->
      <div class="relative mb-8">
        <p class="text-[180px] font-black leading-none text-gray-900 select-none">404</p>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="text-center">
            <p class="text-6xl mb-2">🎮</p>
            <p class="text-white font-bold text-xl">Game not found</p>
          </div>
        </div>
      </div>

      <h1 class="text-2xl font-bold mb-3">Página no encontrada</h1>
      <p class="text-gray-400 mb-8 leading-relaxed">
        La página que buscas no existe o ha sido eliminada.
        Puede que el enlace sea incorrecto o que el contenido ya no esté disponible.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <RouterLink to="/">
          <BaseButton size="lg" class="w-full sm:w-auto">
            Ir al inicio
          </BaseButton>
        </RouterLink>
        <RouterLink to="/catalog">
          <BaseButton variant="ghost" size="lg" class="w-full sm:w-auto">
            Ver catálogo
          </BaseButton>
        </RouterLink>
        <BaseButton variant="ghost" size="lg" @click="goBack" class="w-full sm:w-auto">
          Volver atrás
        </BaseButton>
      </div>

      <!-- Juegos sugeridos -->
      <div v-if="gamesStore.games.length > 0" class="mt-12">
        <p class="text-gray-500 text-sm mb-4">Mientras tanto, quizás te interese...</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <RouterLink
            v-for="game in gamesStore.games.slice(0, 3)"
            :key="game.id"
            :to="{ name: 'game-detail', params: { id: game.id } }"
            class="bg-gray-900 rounded-lg border border-gray-800 hover:border-gray-600 transition-colors overflow-hidden flex items-center gap-3 p-3">
            <img v-if="game.cover_image"
              :src="game.cover_image"
              :alt="game.title"
              class="w-12 h-12 rounded object-cover flex-shrink-0"
            />
            <div v-else class="w-12 h-12 rounded bg-gray-800 flex items-center justify-center text-xl flex-shrink-0">🎮</div>
            <div class="min-w-0">
              <p class="text-sm font-medium truncate">{{ game.title }}</p>
              <p class="text-xs text-blue-400">{{ game.price === 0 ? 'Gratis' : `${game.price} €` }}</p>
            </div>
          </RouterLink>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGamesStore } from '@/stores/games'
import BaseButton from '@/components/ui/BaseButton.vue'

const router = useRouter()
const gamesStore = useGamesStore()

function goBack() {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/')
  }
}

onMounted(() => {
  if (gamesStore.games.length === 0) {
    gamesStore.fetchGames({ per_page: 3 })
  }
})
</script>
