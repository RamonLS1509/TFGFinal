<template>
  <div class="w-full max-w-screen-xl mx-auto px-6 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold">Mi wishlist</h1>
        <p class="text-gray-500 mt-1">{{ wishlistStore.items.length }} juego(s) en tu lista de deseos</p>
      </div>
      <RouterLink to="/catalog">
        <BaseButton variant="secondary">+ Añadir juegos</BaseButton>
      </RouterLink>
    </div>

    <LoadingSpinner v-if="wishlistStore.loading" />

    <!-- Estado vacío -->
    <div v-else-if="wishlistStore.items.length === 0" class="py-12">
      <div class="max-w-md mx-auto text-center">
        <div class="relative w-32 h-32 mx-auto mb-6">
          <div class="w-32 h-32 rounded-full bg-gray-800 flex items-center justify-center">
            <span class="text-6xl">💝</span>
          </div>
          <div
            class="absolute -bottom-1 -right-1 w-10 h-10 rounded-full bg-pink-600 flex items-center justify-center text-lg font-bold">
            0
          </div>
        </div>

        <h2 class="text-xl font-bold mb-2">Tu wishlist está vacía</h2>
        <p class="text-gray-500 mb-6 leading-relaxed">
          Guarda los juegos que quieres comprar más adelante. Así no se te olvida ninguno.
        </p>

        <RouterLink to="/catalog">
          <BaseButton size="lg">Explorar catálogo</BaseButton>
        </RouterLink>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
          <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <p class="text-2xl mb-2">🔍</p>
            <p class="text-sm font-medium mb-1">Explora el catálogo</p>
            <p class="text-xs text-gray-500">Busca juegos por género, precio o plataforma</p>
          </div>
          <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <p class="text-2xl mb-2">♡</p>
            <p class="text-sm font-medium mb-1">Añade a wishlist</p>
            <p class="text-xs text-gray-500">Pulsa el botón en cualquier juego que te interese</p>
          </div>
          <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <p class="text-2xl mb-2">🛒</p>
            <p class="text-sm font-medium mb-1">Compra cuando quieras</p>
            <p class="text-xs text-gray-500">Desde tu wishlist puedes comprar directamente</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Wishlist con juegos -->
    <div v-else>
      <!-- Resumen -->
      <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
          <p class="text-2xl font-bold text-blue-400">{{ wishlistStore.items.length }}</p>
          <p class="text-xs text-gray-500 mt-1">Juegos</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
          <p class="text-2xl font-bold text-yellow-400">{{ favoritesCount }}</p>
          <p class="text-xs text-gray-500 mt-1">Juegos favoritos</p>
        </div>
      </div>

      <div class="flex flex-col gap-3">
        <div v-for="item in sortedItems" :key="item.id"
          class="bg-gray-900 rounded-xl border flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 hover:border-gray-700 transition-colors"
          :class="item.priority === 1 ? 'border-yellow-800/50' : 'border-gray-800'">

          <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }" class="flex-shrink-0">
            <div class="w-full sm:w-32 h-20 bg-gray-800 rounded-lg overflow-hidden">
              <img v-if="item.game?.cover_image" :src="item.game.cover_image" :alt="item.game?.title"
                class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-2xl">🎮</div>
            </div>
          </RouterLink>

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }"
                class="font-medium hover:text-blue-400 transition-colors">
                {{ item.game?.title }}
              </RouterLink>
              <span v-if="item.priority === 1"
                class="text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 rounded-full px-2 py-0.5">
                ★ Juego favorito
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Añadido {{ new Date(item.created_at).toLocaleDateString('es-ES') }}
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">

            <!-- Favorito -->
            <button @click="togglePriority(item)" :class="item.priority === 1
              ? 'text-yellow-400 bg-yellow-900/30 border-yellow-800 font-semibold'
              : 'text-gray-500 bg-gray-800 border-gray-700 hover:text-yellow-400 hover:border-yellow-800'"
              class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border transition-all text-xs w-full sm:w-auto">
              <span>★</span>
              <span>{{ item.priority === 1 ? 'Favorito' : 'Favorito' }}</span>
            </button>

            <!-- Comprar -->
            <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }" class="flex-1 sm:flex-none">
              <BaseButton size="sm" class="w-full sm:w-32">
                🛒 Comprar
              </BaseButton>
            </RouterLink>

            <!-- Eliminar -->
            <BaseButton variant="ghost" size="sm" @click="remove(item)"
              class="text-gray-500 hover:text-red-400 hover:border-red-800 w-full sm:w-auto">
              Eliminar
            </BaseButton>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useWishlistStore } from '@/stores/wishlist'
import { useToast } from '@/composables/useToast'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import api from '@/services/api'

const wishlistStore = useWishlistStore()
const toast = useToast()

const favoritesCount = computed(() =>
  wishlistStore.items.filter(i => i.priority === 1).length
)

const sortedItems = computed(() =>
  [...wishlistStore.items].sort((a, b) => b.priority - a.priority)
)

async function togglePriority(item) {
  const newPriority = item.priority === 1 ? 0 : 1
  await api.put(`/api/wishlist/${item.id}`, { priority: newPriority })
  item.priority = newPriority
  toast.info(newPriority === 1 ? 'Marcado como favorito.' : 'Eliminado de favoritos.')
}

async function remove(item) {
  await wishlistStore.removeFromWishlist(item.id)
}

onMounted(() => wishlistStore.fetchWishlist())
</script>
