<template>
  <footer class="bg-gray-900 border-t border-gray-800 py-8 mt-12 w-full">
    <div class="w-full max-w-screen-2xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-gray-500 text-sm">
      <div class="flex items-center gap-2">
        <span class="text-xl">🎮</span>
        <span class="font-medium text-gray-400">SteamClone</span>
      </div>
      <p>© 2026 SteamClone · Trabajo Fin de Máster · Full Stack Developer</p>
      <div class="flex gap-4">
        <RouterLink to="/catalog" class="hover:text-gray-300 transition-colors">Catálogo</RouterLink>
        <RouterLink to="/login" class="hover:text-gray-300 transition-colors">Login</RouterLink>
      </div>
    </div>
  </footer>
</template>
