<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LoadingSpinner v-if="gamesStore.loading" />

    <template v-else-if="game">
      <!-- Header image -->
      <div class="rounded-xl overflow-hidden mb-8 aspect-video bg-gray-800">
        <img v-if="game.header_image || game.cover_image"
          :src="game.header_image || game.cover_image"
          :alt="game.title"
          class="w-full h-full object-cover" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main info -->
        <div class="lg:col-span-2">
          <h1 class="text-3xl font-bold mb-2">{{ game.title }}</h1>
          <p class="text-gray-400 text-sm mb-4">{{ game.developer }} · {{ game.publisher }}</p>

          <div class="flex flex-wrap gap-2 mb-6">
            <span v-for="genre in game.genres" :key="genre"
              class="text-xs px-3 py-1 rounded-full bg-blue-900/50 text-blue-300 border border-blue-800">
              {{ genre }}
            </span>
          </div>

          <p class="text-gray-300 leading-relaxed">{{ game.description }}</p>
        </div>

        <!-- Purchase panel -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 h-fit flex flex-col gap-4">
          <div class="text-3xl font-bold text-blue-400">
            {{ game.price === 0 ? 'Gratis' : `${game.price} €` }}
          </div>

          <div class="text-sm text-gray-500">
            <p>Lanzamiento: {{ new Date(game.release_date).toLocaleDateString('es-ES') }}</p>
            <p class="mt-1">Plataformas: {{ game.platforms?.join(', ') }}</p>
            <p v-if="game.metacritic_score" class="mt-1">
              Metacritic: <span :class="metaClass">{{ game.metacritic_score }}</span>
            </p>
          </div>

          <template v-if="auth.isAuthenticated">
            <div v-if="inLibrary" class="text-green-400 text-sm font-medium bg-green-900/30 border border-green-800 rounded px-3 py-2 text-center">
              ✓ En tu biblioteca
            </div>
            <BaseButton v-else @click="handleBuy" :loading="buyLoading" size="lg" class="w-full">
              Añadir a la biblioteca
            </BaseButton>

            <BaseButton
              @click="toggleWishlist"
              :variant="inWishlist ? 'secondary' : 'ghost'"
              size="md"
              class="w-full">
              {{ inWishlist ? '♥ En tu wishlist' : '♡ Añadir a wishlist' }}
            </BaseButton>
          </template>

          <template v-else>
            <RouterLink to="/login">
              <BaseButton size="lg" class="w-full">Iniciar sesión para comprar</BaseButton>
            </RouterLink>
          </template>

          <p v-if="actionError" class="text-red-400 text-xs">{{ actionError }}</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useGamesStore } from '@/stores/games'
import { useLibraryStore } from '@/stores/library'
import { useWishlistStore } from '@/stores/wishlist'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const route = useRoute()
const gamesStore = useGamesStore()
const libraryStore = useLibraryStore()
const wishlistStore = useWishlistStore()
const auth = useAuthStore()

const buyLoading = ref(false)
const actionError = ref('')

const game = computed(() => gamesStore.currentGame)
const inLibrary = computed(() => libraryStore.ownsGame(game.value?.id))
const inWishlist = computed(() => wishlistStore.hasGame(game.value?.id))
const metaClass = computed(() => {
  const s = game.value?.metacritic_score
  if (s >= 75) return 'text-green-400 font-bold'
  if (s >= 50) return 'text-yellow-400 font-bold'
  return 'text-red-400 font-bold'
})

async function handleBuy() {
  buyLoading.value = true
  actionError.value = ''
  try {
    await libraryStore.addToLibrary(game.value.id)
    if (inWishlist.value) {
      const entry = wishlistStore.getEntry(game.value.id)
      await wishlistStore.removeFromWishlist(entry.id)
    }
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Error al añadir el juego.'
  } finally {
    buyLoading.value = false
  }
}

async function toggleWishlist() {
  actionError.value = ''
  try {
    if (inWishlist.value) {
      const entry = wishlistStore.getEntry(game.value.id)
      await wishlistStore.removeFromWishlist(entry.id)
    } else {
      await wishlistStore.addToWishlist(game.value.id)
    }
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Error al actualizar la wishlist.'
  }
}

onMounted(async () => {
  await gamesStore.fetchGame(route.params.id)
  if (auth.isAuthenticated) {
    await Promise.all([libraryStore.fetchLibrary(), wishlistStore.fetchWishlist()])
  }
})
</script>
