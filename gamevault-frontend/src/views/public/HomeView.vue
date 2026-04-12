<template>
  <div class="w-full">
    <!-- Hero -->
    <section class="w-full bg-gradient-to-b from-blue-950 to-gray-950 py-24 px-6 text-center">
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">Tu tienda de videojuegos</h1>
      <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto">
        Descubre miles de juegos, construye tu biblioteca y comparte tu wishlist.
      </p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <RouterLink to="/catalog">
          <BaseButton size="lg" class="w-full sm:w-auto">Explorar catálogo</BaseButton>
        </RouterLink>
        <RouterLink v-if="!auth.isAuthenticated" to="/register">
          <BaseButton variant="ghost" size="lg" class="w-full sm:w-auto">Crear cuenta gratis</BaseButton>
        </RouterLink>
      </div>
    </section>

    <!-- Featured games -->
    <section class="w-full max-w-screen-2xl mx-auto px-6 py-12">
      <h2 class="text-2xl font-bold mb-6">Juegos destacados</h2>
      <LoadingSpinner v-if="gamesStore.loading" />
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
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
  await gamesStore.fetchGames({ per_page: 12 })
  if (auth.isAuthenticated) await libraryStore.fetchLibrary()
})
</script>
