<!--Componente común del Navbar-->
<template>
  <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 w-full">
    <div class="w-full max-w-screen-2xl mx-auto px-6 flex items-center justify-between h-16">

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2 font-bold text-xl text-blue-400 flex-shrink-0">
        <span class="text-2xl">🎮</span>
        <span>GameVault</span>
      </RouterLink>

      <!-- Nav links — escritorio -->
      <div class="hidden md:flex items-center gap-6 text-sm">
        <RouterLink to="/catalog" class="text-gray-300 hover:text-white transition-colors"
          active-class="text-white font-medium">
          Catálogo
        </RouterLink>

        <template v-if="auth.isAuthenticated">
          <RouterLink to="/library" class="text-gray-300 hover:text-white transition-colors"
            active-class="text-white font-medium">
            Biblioteca
          </RouterLink>
          <RouterLink to="/wishlist" class="text-gray-300 hover:text-white transition-colors"
            active-class="text-white font-medium">
            Wishlist
          </RouterLink>

          <template v-if="auth.isAdmin">
            <RouterLink to="/api-explorer" class="text-yellow-400 hover:text-yellow-300 transition-colors"
              active-class="text-yellow-300 font-medium">
              API
            </RouterLink>
            <RouterLink to="/admin/games" class="text-yellow-400 hover:text-yellow-300 transition-colors"
              active-class="text-yellow-300 font-medium">
              Panel de Juegos
            </RouterLink>
            <RouterLink to="/admin/users" class="text-yellow-400 hover:text-yellow-300 transition-colors"
              active-class="text-yellow-300 font-medium">
              Panel de Usuarios
            </RouterLink>
          </template>
        </template>
      </div>

      <!-- Auth buttons — escritorio -->
      <div class="hidden md:flex items-center gap-3">
        <template v-if="auth.isAuthenticated">
          <RouterLink to="/profile"
            class="flex items-center gap-2 text-sm text-gray-300 hover:text-white transition-colors">
            <div
              class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
              {{ auth.user.name.charAt(0).toUpperCase() }}
            </div>
            <span>{{ auth.user.name }}</span>
          </RouterLink>
          <button @click="handleLogout"
            class="text-sm text-gray-400 hover:text-white transition-colors px-3 py-1.5 rounded border border-gray-700 hover:border-gray-500">
            Salir
          </button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="text-sm text-gray-300 hover:text-white transition-colors px-3 py-1.5">
            Iniciar sesión
          </RouterLink>
          <RouterLink to="/register"
            class="text-sm bg-blue-600 hover:bg-blue-500 transition-colors px-4 py-1.5 rounded font-medium">
            Registrarse
          </RouterLink>
        </template>
      </div>

      <!-- Botón hamburguesa — móvil -->
      <button @click="menuOpen = !menuOpen"
        class="md:hidden p-2 rounded text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
        <svg v-if="!menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Menú móvil desplegable -->
    <div v-if="menuOpen" class="md:hidden bg-gray-900 border-t border-gray-800 px-6 py-4 flex flex-col gap-4">
      <RouterLink to="/catalog" @click="menuOpen = false"
        class="text-gray-300 hover:text-white transition-colors py-2 border-b border-gray-800">
        Catálogo
      </RouterLink>

      <template v-if="auth.isAuthenticated">
        <RouterLink to="/library" @click="menuOpen = false"
          class="text-gray-300 hover:text-white transition-colors py-2 border-b border-gray-800">
          Biblioteca
        </RouterLink>
        <RouterLink to="/wishlist" @click="menuOpen = false"
          class="text-gray-300 hover:text-white transition-colors py-2 border-b border-gray-800">
          Wishlist
        </RouterLink>
        <RouterLink to="/profile" @click="menuOpen = false"
          class="text-gray-300 hover:text-white transition-colors py-2 border-b border-gray-800">
          Mi perfil
        </RouterLink>

        <template v-if="auth.isAdmin">
          <RouterLink to="/api-explorer" @click="menuOpen = false"
            class="text-yellow-400 hover:text-yellow-300 transition-colors py-2 border-b border-gray-800">
            API Explorer
          </RouterLink>
          <RouterLink to="/admin/games" @click="menuOpen = false"
            class="text-yellow-400 hover:text-yellow-300 transition-colors py-2 border-b border-gray-800">
            Panel admin
          </RouterLink>
          <RouterLink to="/admin/users" @click="menuOpen = false"
            class="text-yellow-400 hover:text-yellow-300 transition-colors py-2 border-b border-gray-800">
            Usuarios
          </RouterLink>
        </template>

        <button @click="handleLogout" class="text-left text-red-400 hover:text-red-300 transition-colors py-2">
          Cerrar sesión
        </button>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

async function handleLogout() {
  menuOpen.value = false
  try {
    await auth.logout()
  } finally {
    router.push({ name: 'home' })
  }
}
</script>
