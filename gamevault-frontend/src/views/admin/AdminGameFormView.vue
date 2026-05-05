<template>
  <div class="w-full max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{{ isEdit ? 'Editar juego' : 'Nuevo juego' }}</h1>

    <form @submit.prevent="handleSubmit" class="bg-gray-900 rounded-xl border border-gray-800 p-6 flex flex-col gap-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <BaseInput label="Título *" v-model="form.title" :error="errors.title" />
        <BaseInput label="Precio (€) *" type="number" step="0.01" v-model="form.price" :error="errors.price" />
        <BaseInput label="Desarrollador *" v-model="form.developer" :error="errors.developer" />
        <BaseInput label="Publisher *" v-model="form.publisher" :error="errors.publisher" />
        <BaseInput label="Fecha de lanzamiento *" type="date" v-model="form.release_date" :error="errors.release_date" />
        <BaseInput label="Puntuación Metacritic" type="number" v-model="form.metacritic_score" :error="errors.metacritic_score" />
      </div>

      <div>
        <label class="text-sm text-gray-400 block mb-1">Descripción *</label>
        <textarea v-model="form.description" rows="4"
          class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="Mínimo 50 caracteres..."></textarea>
        <p v-if="errors.description" class="text-red-400 text-xs mt-1">{{ errors.description }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <BaseInput label="URL portada" v-model="form.cover_image" :error="errors.cover_image" placeholder="https://..." />
        <BaseInput label="URL header" v-model="form.header_image" :error="errors.header_image" placeholder="https://..." />
      </div>

      <!-- Géneros -->
      <div>
        <label class="text-sm text-gray-400 block mb-2">Géneros *</label>
        <div class="flex flex-wrap gap-2">
          <label v-for="g in availableGenres" :key="g" class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" :value="g" v-model="form.genres" class="rounded" />
            <span class="text-sm text-gray-300">{{ g }}</span>
          </label>
        </div>
        <p v-if="errors.genres" class="text-red-400 text-xs mt-1">{{ errors.genres }}</p>
      </div>

      <!-- Plataformas -->
      <div>
        <label class="text-sm text-gray-400 block mb-2">Plataformas *</label>
        <div class="flex flex-wrap gap-2">
          <label v-for="p in availablePlatforms" :key="p" class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" :value="p" v-model="form.platforms" class="rounded" />
            <span class="text-sm text-gray-300">{{ p }}</span>
          </label>
        </div>
      </div>

      <p v-if="serverError"
        class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
        {{ serverError }}
      </p>

      <div class="flex gap-3 pt-2">
        <BaseButton type="submit" :loading="loading">
          {{ isEdit ? 'Guardar cambios' : 'Crear juego' }}
        </BaseButton>
        <RouterLink to="/admin/games">
          <BaseButton variant="ghost">Cancelar</BaseButton>
        </RouterLink>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGamesStore } from '@/stores/games'
import { useToast } from '@/composables/useToast'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const route      = useRoute()
const router     = useRouter()
const gamesStore = useGamesStore()
const toast      = useToast()

const isEdit  = computed(() => !!route.params.id)
const loading = ref(false)
const errors  = ref({})
const serverError = ref('')

const availableGenres    = ['Action', 'RPG', 'Strategy', 'Simulation', 'Sports', 'Indie', 'Open World', 'Souls-like', 'Adventure', 'Horror']
const availablePlatforms = ['Windows', 'Mac', 'Linux', 'PlayStation', 'Xbox', 'Nintendo Switch']

const form = ref({
  title: '', description: '', developer: '', publisher: '',
  price: '', cover_image: '', header_image: '',
  genres: [], platforms: [], release_date: '',
  metacritic_score: '', is_active: true,
})

onMounted(async () => {
  if (isEdit.value) {
    await gamesStore.fetchGame(route.params.id)
    const g = gamesStore.currentGame
    if (g) {
      Object.keys(form.value).forEach(k => {
        if (g[k] !== undefined) form.value[k] = g[k]
      })
      form.value.release_date = g.release_date?.split('T')[0] ?? g.release_date
    }
  }
})

async function handleSubmit() {
  errors.value      = {}
  serverError.value = ''
  loading.value     = true

  try {
    const payload = { ...form.value, price: parseFloat(form.value.price) }
    if (form.value.metacritic_score) {
      payload.metacritic_score = parseInt(form.value.metacritic_score)
    }

    if (isEdit.value) {
      await gamesStore.updateGame(route.params.id, payload)
      toast.success('Juego actualizado correctamente.')
    } else {
      await gamesStore.createGame(payload)
      toast.success('Juego creado correctamente.')
    }
    router.push({ name: 'admin-games' })
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else {
      serverError.value = e.response?.data?.message || 'Error al guardar el juego.'
    }
  } finally {
    loading.value = false
  }
}
</script>
