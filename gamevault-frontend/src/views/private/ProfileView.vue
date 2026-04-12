<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Mi perfil</h1>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 flex flex-col gap-6">

      <!-- Avatar y nombre -->
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold shrink-0">
          {{ auth.user?.name?.charAt(0).toUpperCase() }}
        </div>
        <div>
          <h2 class="text-xl font-semibold">{{ auth.user?.name }}</h2>
          <p class="text-gray-400 text-sm">@{{ auth.user?.username }}</p>
          <span v-if="auth.isAdmin"
            class="inline-block text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 rounded px-2 py-0.5 mt-1">
            Administrador
          </span>
        </div>
      </div>

      <!-- Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="bg-gray-800 rounded-lg p-4">
          <p class="text-gray-500 mb-1">Email</p>
          <p class="text-gray-100">{{ auth.user?.email }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4">
          <p class="text-gray-500 mb-1">Nombre de usuario</p>
          <p class="text-gray-100">@{{ auth.user?.username }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4">
          <p class="text-gray-500 mb-1">Juegos en biblioteca</p>
          <p class="text-gray-100 font-bold text-lg">{{ libraryStore.entries.length }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4">
          <p class="text-gray-500 mb-1">Juegos en wishlist</p>
          <p class="text-gray-100 font-bold text-lg">{{ wishlistStore.items.length }}</p>
        </div>
      </div>

      <!-- Acciones rápidas -->
      <div class="flex gap-3 pt-2 border-t border-gray-800">
        <RouterLink to="/library">
          <BaseButton variant="secondary">Ver biblioteca</BaseButton>
        </RouterLink>
        <RouterLink to="/wishlist">
          <BaseButton variant="ghost">Ver wishlist</BaseButton>
        </RouterLink>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useLibraryStore } from '@/stores/library'
import { useWishlistStore } from '@/stores/wishlist'
import BaseButton from '@/components/ui/BaseButton.vue'

const auth = useAuthStore()
const libraryStore = useLibraryStore()
const wishlistStore = useWishlistStore()

onMounted(async () => {
  await Promise.all([
    libraryStore.fetchLibrary(),
    wishlistStore.fetchWishlist(),
  ])
})
</script>
