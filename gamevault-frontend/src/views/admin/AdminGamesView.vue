<template>
  <div class="w-full max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold">Gestión de juegos</h1>
      <RouterLink to="/admin/games/create">
        <BaseButton>+ Nuevo juego</BaseButton>
      </RouterLink>
    </div>

    <LoadingSpinner v-if="gamesStore.loading" />

    <div v-else class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-800 text-gray-400">
          <tr>
            <th class="text-left px-4 py-3">Título</th>
            <th class="text-left px-4 py-3 hidden md:table-cell">Desarrollador</th>
            <th class="text-left px-4 py-3 hidden lg:table-cell">Géneros</th>
            <th class="text-right px-4 py-3">Precio</th>
            <th class="text-center px-4 py-3">Estado</th>
            <th class="text-right px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="game in gamesStore.games" :key="game.id"
            class="border-t border-gray-800 hover:bg-gray-800/50 transition-colors">
            <td class="px-4 py-3 font-medium">{{ game.title }}</td>
            <td class="px-4 py-3 text-gray-400 hidden md:table-cell">{{ game.developer }}</td>
            <td class="px-4 py-3 hidden lg:table-cell">
              <div class="flex gap-1 flex-wrap">
                <span v-for="g in game.genres?.slice(0,2)" :key="g"
                  class="text-xs px-2 py-0.5 rounded bg-gray-700 text-gray-300">{{ g }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-right text-blue-400 font-bold">{{ game.price }} €</td>
            <td class="px-4 py-3 text-center">
              <span :class="game.is_active ? 'text-green-400' : 'text-gray-600'" class="text-xs">
                {{ game.is_active ? '● Activo' : '● Inactivo' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex justify-end gap-2">
                <RouterLink :to="{ name: 'admin-game-edit', params: { id: game.id } }">
                  <BaseButton variant="ghost" size="sm">Editar</BaseButton>
                </RouterLink>
                <BaseButton variant="danger" size="sm" @click="handleDelete(game)">
                  Eliminar
                </BaseButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useGamesStore } from '@/stores/games'
import { useToast } from '@/composables/useToast'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const gamesStore = useGamesStore()
const toast      = useToast()

onMounted(() => gamesStore.fetchGames({ per_page: 50 }))

async function handleDelete(game) {
  if (!confirm(`¿Eliminar "${game.title}"? Esta acción no se puede deshacer.`)) return
  await gamesStore.deleteGame(game.id)
  toast.success(`"${game.title}" eliminado correctamente.`)
}
</script>
