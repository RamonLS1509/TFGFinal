<template>
  <RouterLink :to="{ name: 'game-detail', params: { id: game.id } }"
    class="group bg-gray-900 rounded-lg overflow-hidden border border-gray-800 hover:border-blue-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/20 flex flex-col">

    <!-- Cover -->
    <div class="relative overflow-hidden aspect-video bg-gray-800">
      <img v-if="game.cover_image"
        :src="game.cover_image"
        :alt="game.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-600 text-4xl">🎮</div>

      <!-- Metacritic badge -->
      <div v-if="game.metacritic_score"
        :class="metaClass"
        class="absolute top-2 right-2 text-xs font-bold px-2 py-0.5 rounded">
        {{ game.metacritic_score }}
      </div>
    </div>

    <!-- Info -->
    <div class="p-4 flex flex-col gap-2 flex-1">
      <h3 class="font-medium text-gray-100 group-hover:text-blue-400 transition-colors line-clamp-1">
        {{ game.title }}
      </h3>
      <p class="text-xs text-gray-500 line-clamp-1">{{ game.developer }}</p>

      <div class="flex flex-wrap gap-1 mt-auto pt-2">
        <span v-for="genre in game.genres?.slice(0, 2)" :key="genre"
          class="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-400">
          {{ genre }}
        </span>
      </div>

      <div class="flex items-center justify-between mt-2">
        <span class="text-blue-400 font-bold">
          {{ game.price === 0 ? 'Gratis' : `${game.price} €` }}
        </span>
        <span v-if="inLibrary" class="text-xs text-green-400">✓ En biblioteca</span>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { useLibraryStore } from '@/stores/library'

const props = defineProps({
  game: { type: Object, required: true },
})

const library = useLibraryStore()
const inLibrary = computed(() => library.ownsGame(props.game.id))

const metaClass = computed(() => {
  const s = props.game.metacritic_score
  if (s >= 75) return 'bg-green-700 text-green-100'
  if (s >= 50) return 'bg-yellow-700 text-yellow-100'
  return 'bg-red-800 text-red-100'
})
</script>
