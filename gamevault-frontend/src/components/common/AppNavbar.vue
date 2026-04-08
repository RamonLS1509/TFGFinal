<template>
  <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2 font-bold text-xl text-blue-400">
        <span class="text-2xl">🎮</span>
        <span>SteamClone</span>
      </RouterLink>

      <!-- Nav links -->
      <div class="hidden md:flex items-center gap-6 text-sm">
        <RouterLink to="/catalog"
          class="text-gray-300 hover:text-white transition-colors"
          active-class="text-white font-medium">
          Catálogo
        </RouterLink>

        <template v-if="auth.isAuthenticated">
          <RouterLink to="/library"
            class="text-gray-300 hover:text-white transition-colors"
            active-class="text-white font-medium">
            Biblioteca
          </RouterLink>
          <RouterLink to="/wishlist"
            class="text-gray-300 hover:text-white transition-colors"
            active-class="text-white font-medium">
            Wishlist
          </RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin/games"
            class="text-yellow-400 hover:text-yellow-300 transition-colors"
            active-class="text-yellow-300 font-medium">
            Admin
          </RouterLink>
        </template>
      </div>

      <!-- Auth buttons -->
      <div class="flex items-center gap-3">
        <template v-if="auth.isAuthenticated">
          <RouterLink to="/profile"
            class="flex items-center gap-2 text-sm text-gray-300 hover:text-white transition-colors">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-xs">
              {{ auth.user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="hidden md:block">{{ auth.user.name }}</span>
          </RouterLink>
          <button @click="handleLogout"
            class="text-sm text-gray-400 hover:text-white transition-colors px-3 py-1.5 rounded border border-gray-700 hover:border-gray-500">
            Salir
          </button>
        </template>
        <template v-else>
          <RouterLink to="/login"
            class="text-sm text-gray-300 hover:text-white transition-colors px-3 py-1.5">
            Iniciar sesión
          </RouterLink>
          <RouterLink to="/register"
            class="text-sm bg-blue-600 hover:bg-blue-500 transition-colors px-4 py-1.5 rounded font-medium">
            Registrarse
          </RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}
</script>
