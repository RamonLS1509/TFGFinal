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


    <div v-else-if="libraryStore.entries.length === 0" class="py-12">
      <div class="max-w-md mx-auto text-center">
        <div class="relative w-32 h-32 mx-auto mb-6">
          <div class="w-32 h-32 rounded-full bg-gray-800 flex items-center justify-center">
            <span class="text-6xl">📚</span>
          </div>
          <div
            class="absolute -bottom-1 -right-1 w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-lg font-bold">
            0
          </div>
        </div>

        <h2 class="text-xl font-bold mb-2">Tu biblioteca está vacía</h2>
        <p class="text-gray-500 mb-6 leading-relaxed">
          Aún no tienes ningún juego en tu colección. Explora el catálogo y añade tus primeros títulos.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
          <RouterLink to="/catalog">
            <BaseButton size="lg">Explorar catálogo</BaseButton>
          </RouterLink>
          <RouterLink to="/wishlist">
            <BaseButton variant="ghost" size="lg">Ver mi wishlist</BaseButton>
          </RouterLink>
        </div>

        <div v-if="wishlistStore.items.length > 0" class="bg-gray-900 rounded-xl border border-gray-800 p-5 text-left">
          <p class="text-sm font-medium text-gray-300 mb-3">
            Tienes {{ wishlistStore.items.length }} juego(s) en tu wishlist
          </p>
          <div class="flex flex-col gap-2">
            <RouterLink v-for="item in wishlistStore.items.slice(0, 3)" :key="item.id"
              :to="{ name: 'game-detail', params: { id: item.game_id } }"
              class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors">
              <img v-if="item.game?.cover_image" :src="item.game.cover_image" :alt="item.game?.title"
                class="w-10 h-10 rounded object-cover flex-shrink-0" />
              <div v-else class="w-10 h-10 rounded bg-gray-700 flex items-center justify-center text-lg flex-shrink-0">
                🎮</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ item.game?.title }}</p>
                <p class="text-xs text-blue-400">{{ item.game?.price }} €</p>
              </div>
              <span class="text-xs text-gray-500 flex-shrink-0">Ver →</span>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Biblioteca con juegos -->
    <div v-else>
      <!-- Barra de búsqueda -->
      <div class="mb-6">
        <BaseInput v-model="search" placeholder="Buscar en tu biblioteca..." />
      </div>

      <div v-if="filteredEntries.length === 0" class="text-center py-12 text-gray-500">
        <p class="text-4xl mb-3">🔍</p>
        <p class="font-medium">No se encontró "{{ search }}"</p>
        <p class="text-sm mt-1">Prueba con otro término de búsqueda</p>
        <BaseButton variant="ghost" class="mt-4" @click="search = ''">Limpiar búsqueda</BaseButton>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="entry in filteredEntries" :key="entry.id"
          class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden hover:border-gray-700 transition-colors flex flex-col">

          <RouterLink :to="{ name: 'game-detail', params: { id: entry.game_id } }" class="block">
            <div class="aspect-video bg-gray-800 overflow-hidden">
              <img v-if="entry.game?.cover_image" :src="entry.game.cover_image" :alt="entry.game?.title"
                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
              <div v-else class="w-full h-full flex items-center justify-center text-4xl">🎮</div>
            </div>
          </RouterLink>

          <div class="p-4 flex flex-col gap-2 flex-1">
            <RouterLink :to="{ name: 'game-detail', params: { id: entry.game_id } }"
              class="font-medium hover:text-blue-400 transition-colors line-clamp-1">
              {{ entry.game?.title }}
            </RouterLink>
            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto pt-2 border-t border-gray-800">
              <span>Comprado: {{ new Date(entry.purchased_at).toLocaleDateString('es-ES') }}</span>
              <span>{{ entry.price_paid }} €</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLibraryStore } from '@/stores/library'
import { useWishlistStore } from '@/stores/wishlist'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

const libraryStore = useLibraryStore()
const wishlistStore = useWishlistStore()
const search = ref('')

const filteredEntries = computed(() => {
  if (!search.value) return libraryStore.entries
  const q = search.value.toLowerCase()
  return libraryStore.entries.filter(e =>
    e.game?.title?.toLowerCase().includes(q) ||
    e.game?.developer?.toLowerCase().includes(q)
  )
})

onMounted(async () => {
  await libraryStore.fetchLibrary()
  await wishlistStore.fetchWishlist()
})
</script>
