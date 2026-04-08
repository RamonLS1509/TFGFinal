<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Mi biblioteca</h1>
    <p class="text-gray-500 mb-8">{{ libraryStore.entries.length }} juego(s) en tu colección</p>

    <LoadingSpinner v-if="libraryStore.loading" />

    <div v-else-if="libraryStore.entries.length === 0" class="text-center py-20 text-gray-500">
      <p class="text-5xl mb-4">📚</p>
      <p class="text-lg">Tu biblioteca está vacía</p>
      <RouterLink to="/catalog">
        <BaseButton class="mt-4">Explorar catálogo</BaseButton>
      </RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="entry in libraryStore.entries" :key="entry.id"
        class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden flex gap-4 p-4 hover:border-gray-700 transition-colors">

        <img v-if="entry.game?.cover_image"
          :src="entry.game.cover_image"
          :alt="entry.game.title"
          class="w-20 h-16 object-cover rounded shrink-0"
        />
        <div v-else class="w-20 h-16 bg-gray-800 rounded shrink-0 flex items-center justify-center text-2xl">🎮</div>

        <div class="flex-1 min-w-0">
          <RouterLink :to="{ name: 'game-detail', params: { id: entry.game_id } }"
            class="font-medium hover:text-blue-400 transition-colors line-clamp-1">
            {{ entry.game?.title }}
          </RouterLink>
          <p class="text-xs text-gray-500 mt-1">
            Comprado: {{ new Date(entry.purchased_at).toLocaleDateString('es-ES') }}
          </p>
          <p class="text-xs text-gray-500">Pagado: {{ entry.price_paid }} €</p>
          <p class="text-xs text-gray-400 mt-1">{{ entry.hours_played }}h jugadas</p>
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
