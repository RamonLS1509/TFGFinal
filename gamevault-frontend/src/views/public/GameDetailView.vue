<template>
  <div class="w-full max-w-screen-xl mx-auto px-6 py-8">
    <LoadingSpinner v-if="gamesStore.loading" />

    <template v-else-if="game">
      <!-- Breadcrumb -->
      <div class="text-sm text-gray-500 mb-4">
        <RouterLink to="/catalog" class="hover:text-gray-300 transition-colors">Catálogo</RouterLink>
        <span class="mx-2">›</span>
        <span class="text-gray-300">{{ game.title }}</span>
      </div>

      <!-- Header image -->
      <div class="w-full rounded-xl overflow-hidden mb-8 bg-gray-800" style="max-height: 400px;">
        <img v-if="game.header_image || game.cover_image" :src="game.header_image || game.cover_image" :alt="game.title"
          class="w-full h-full object-cover" style="max-height: 400px;" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Info principal -->
        <div class="lg:col-span-2 flex flex-col gap-4">
          <h1 class="text-3xl md:text-4xl font-bold">{{ game.title }}</h1>
          <p class="text-gray-400">{{ game.developer }} · {{ game.publisher }}</p>

          <div class="flex flex-wrap gap-2">
            <span v-for="genre in game.genres" :key="genre"
              class="text-xs px-3 py-1 rounded-full bg-blue-900/50 text-blue-300 border border-blue-800">
              {{ genre }}
            </span>
          </div>

          <p class="text-gray-300 leading-relaxed">{{ game.description }}</p>

          <!-- Info adicional -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
            <div class="bg-gray-900 rounded-lg p-3 border border-gray-800">
              <p class="text-xs text-gray-500 mb-1">Lanzamiento</p>
              <p class="text-sm font-medium">{{ new Date(game.release_date).toLocaleDateString('es-ES') }}</p>
            </div>
            <div class="bg-gray-900 rounded-lg p-3 border border-gray-800">
              <p class="text-xs text-gray-500 mb-1">Plataformas</p>
              <p class="text-sm font-medium">{{ game.platforms?.join(', ') }}</p>
            </div>
            <div v-if="game.metacritic_score" class="bg-gray-900 rounded-lg p-3 border border-gray-800">
              <p class="text-xs text-gray-500 mb-1">Metacritic</p>
              <p class="text-sm font-bold" :class="metaClass">{{ game.metacritic_score }}</p>
            </div>
          </div>
        </div>

        <!-- Panel de compra -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 h-fit flex flex-col gap-4 lg:sticky lg:top-20">
          <div class="text-3xl font-bold text-blue-400">
            {{ game.price == 0 ? 'Gratis' : `${game.price} €` }}
          </div>

          <template v-if="auth.isAuthenticated">
            <div v-if="inLibrary"
              class="text-green-400 text-sm font-medium bg-green-900/30 border border-green-800 rounded px-3 py-3 text-center">
              ✓ En tu biblioteca
            </div>
            <BaseButton v-else @click="handleBuy" :loading="buyLoading" size="lg" class="w-full">
              Añadir a la biblioteca
            </BaseButton>

            <BaseButton v-if="!inLibrary" @click="toggleWishlist" :variant="inWishlist ? 'secondary' : 'ghost'"
              size="md" class="w-full">
              {{ inWishlist ? '♥ En tu wishlist' : '♡ Añadir a wishlist' }}
            </BaseButton>
          </template>

          <template v-else>
            <RouterLink to="/login" class="block">
              <BaseButton size="lg" class="w-full">Iniciar sesión para comprar</BaseButton>
            </RouterLink>
            <RouterLink to="/register" class="block">
              <BaseButton variant="ghost" size="md" class="w-full">Crear cuenta gratis</BaseButton>
            </RouterLink>
          </template>

          <p v-if="actionError" class="text-red-400 text-xs text-center">{{ actionError }}</p>
        </div>
      </div>
      <!-- Reseñas — debajo del grid principal -->
      <div class="lg:col-span-2">
        <GameReviews :game-id="game.id" @change-page="loadPage" />
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
import GameReviews from '@/components/games/GameReviews.vue'
import { useReviewsStore } from '@/stores/reviews'

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
  if (s >= 75) return 'text-green-400'
  if (s >= 50) return 'text-yellow-400'
  return 'text-red-400'
})

const reviewsStore = useReviewsStore()

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
  const promises = [reviewsStore.fetchGameReviews(route.params.id)]
  if (auth.isAuthenticated) {
    promises.push(libraryStore.fetchLibrary())
    promises.push(wishlistStore.fetchWishlist())
  }
  await Promise.all(promises)
})

async function loadPage(page) {
  await reviewsStore.fetchGameReviews(game.value.id, page)
}
</script>
