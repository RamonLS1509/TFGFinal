<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Mi wishlist</h1>
    <p class="text-gray-500 mb-8">{{ wishlistStore.items.length }} juego(s) en tu lista de deseos</p>

    <LoadingSpinner v-if="wishlistStore.loading" />

    <div v-else-if="wishlistStore.items.length === 0" class="text-center py-20 text-gray-500">
      <p class="text-5xl mb-4">💝</p>
      <p class="text-lg">Tu wishlist está vacía</p>
      <RouterLink to="/catalog">
        <BaseButton class="mt-4">Explorar catálogo</BaseButton>
      </RouterLink>
    </div>

    <div v-else class="flex flex-col gap-4">
      <div v-for="item in wishlistStore.items" :key="item.id"
        class="bg-gray-900 rounded-lg border border-gray-800 flex items-center gap-4 p-4 hover:border-gray-700 transition-colors">

        <img v-if="item.game?.cover_image"
          :src="item.game.cover_image"
          :alt="item.game.title"
          class="w-24 h-16 object-cover rounded shrink-0" />
        <div v-else class="w-24 h-16 bg-gray-800 rounded shrink-0 flex items-center justify-center text-2xl">🎮</div>

        <div class="flex-1 min-w-0">
          <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }"
            class="font-medium hover:text-blue-400 transition-colors">
            {{ item.game?.title }}
          </RouterLink>
          <p class="text-blue-400 font-bold text-sm mt-1">{{ item.game?.price }} €</p>
          <span v-if="item.priority === 1"
            class="inline-block text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 rounded px-2 py-0.5 mt-1">
            Alta prioridad
          </span>
        </div>

        <div class="flex gap-2 shrink-0">
          <RouterLink :to="{ name: 'game-detail', params: { id: item.game_id } }">
            <BaseButton size="sm">Comprar</BaseButton>
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

async function remove(id) {
  await wishlistStore.removeFromWishlist(id)
}
</script>
