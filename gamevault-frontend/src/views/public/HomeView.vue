<template>
  <div>
    <!-- Hero -->
    <section class="bg-linear-to-b from-blue-950 to-gray-950 py-24 px-4 text-center">
      <h1 class="text-5xl font-bold mb-4">Tu tienda de videojuegos</h1>
      <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto">
        Descubre miles de juegos, construye tu biblioteca y comparte tu wishlist.
      </p>
      <div class="flex justify-center gap-4">
        <RouterLink to="/catalog">
          <BaseButton size="lg">Explorar catálogo</BaseButton>
        </RouterLink>
        <RouterLink v-if="!auth.isAuthenticated" to="/register">
          <BaseButton variant="ghost" size="lg">Crear cuenta gratis</BaseButton>
        </RouterLink>
      </div>
    </section>

    <!-- Featured games -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold mb-6">Juegos destacados</h2>

      <LoadingSpinner v-if="gamesStore.loading" />

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <GameCard v-for="game in gamesStore.games" :key="game.id" :game="game" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGamesStore } from '@/stores/games'
import { useLibraryStore } from '@/stores/library'
import GameCard from '@/components/games/GameCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const auth = useAuthStore()
const gamesStore = useGamesStore()
const libraryStore = useLibraryStore()

onMounted(async () => {
  await gamesStore.fetchGames({ per_page: 8 })
  if (auth.isAuthenticated) {
    await libraryStore.fetchLibrary()
  }
})
</script>
