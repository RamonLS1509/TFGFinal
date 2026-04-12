<template>
  <div class="w-full max-w-screen-2xl mx-auto px-6 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold">Mi biblioteca</h1>
        <p class="text-gray-500 mt-1">{{ libraryStore.entries.length }} juego(s) en tu colección</p>
      </div>
      <RouterLink to="/catalog">
        <BaseButton variant="secondary">+ Añadir juegos</BaseButton>
      </RouterLink>
    </div>

    <LoadingSpinner v-if="libraryStore.loading" />

    <div v-else-if="libraryStore.entries.length === 0" class="text-center py-20 text-gray-500">
      <p class="text-6xl mb-4">📚</p>
      <p class="text-xl font-medium mb-2">Tu biblioteca está vacía</p>
      <p class="text-sm mb-6">Explora el catálogo y añade tus primeros juegos</p>
      <RouterLink to="/catalog">
        <BaseButton>Explorar catálogo</BaseButton>
      </RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-for="entry in libraryStore.entries" :key="entry.id"
        class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden hover:border-gray-700 transition-colors flex flex-col">

        <!-- Cover -->
        <RouterLink :to="{ name: 'game-detail', params: { id: entry.game_id } }" class="block">
          <div class="aspect-video bg-gray-800 overflow-hidden">
            <img v-if="entry.game?.cover_image"
              :src="entry.game.cover_image"
              :alt="entry.game?.title"
              class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
            <div v-else class="w-full h-full flex items-center justify-center text-4xl">🎮</div>
          </div>
        </RouterLink>

        <!-- Info -->
        <div class="p-4 flex flex-col gap-2 flex-1">
          <RouterLink :to="{ name: 'game-detail', params: { id: entry.game_id } }"
            class="font-medium hover:text-blue-400 transition-colors line-clamp-1">
            {{ entry.game?.title }}
          </RouterLink>
          <div class="flex items-center justify-between text-xs text-gray-500 mt-auto pt-2 border-t border-gray-800">
            <span>{{ entry.hours_played }}h jugadas</span>
            <span>{{ entry.price_paid }} €</span>
          </div>
          <p class="text-xs text-gray-600">
            Comprado: {{ new Date(entry.purchased_at).toLocaleDateString('es-ES') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useLibraryStore } from '@/stores/library'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const libraryStore = useLibraryStore()
onMounted(() => libraryStore.fetchLibrary())
</script>
