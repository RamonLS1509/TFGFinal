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

    <div v-else-if="wishlistStore.items.length === 0" class="text-center py-20 text-gray-500">
      <p class="text-6xl mb-4">💝</p>
      <p class="text-xl font-medium mb-2">Tu wishlist está vacía</p>
      <p class="text-sm mb-6">Guarda los juegos que quieres comprar más adelante</p>
      <RouterLink to="/catalog">
        <BaseButton>Explorar catálogo</BaseButton>
      </RouterLink>
    </div>

    <div v-else class="flex flex-col gap-3">
      <div v-for="item in wishlistStore.items" :key="item.id"
        class="bg-gray-900 rounded-xl border border-gray-800 flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 hover:border-gray-700 transition-colors">

        <!-- Cover -->
        <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }" class="flex-shrink-0">
          <div class="w-full sm:w-32 h-20 bg-gray-800 rounded-lg overflow-hidden">
            <img v-if="item.game?.cover_image"
              :src="item.game.cover_image"
              :alt="item.game?.title"
              class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-2xl">🎮</div>
          </div>
        </RouterLink>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }"
            class="font-medium hover:text-blue-400 transition-colors text-lg">
            {{ item.game?.title }}
          </RouterLink>
          <p class="text-blue-400 font-bold mt-1">{{ item.game?.price }} €</p>
          <span v-if="item.priority === 1"
            class="inline-block text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 rounded px-2 py-0.5 mt-1">
            Alta prioridad
          </span>
        </div>

        <!-- Acciones -->
        <div class="flex gap-2 w-full sm:w-auto">
          <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }" class="flex-1 sm:flex-none">
            <BaseButton size="sm" class="w-full sm:w-auto">Comprar</BaseButton>
          </RouterLink>
          <BaseButton variant="ghost" size="sm" @click="remove(item.id)">✕</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useWishlistStore } from '@/stores/wishlist'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const wishlistStore = useWishlistStore()
onMounted(() => wishlistStore.fetchWishlist())
async function remove(id) { await wishlistStore.removeFromWishlist(id) }
</script>
