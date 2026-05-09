<!--Gestiona la seccion completa de reseña de la comunidad dentro de la pagina de detalle de un juego-->
<template>
  <div class="mt-10">
    <h2 class="text-2xl font-bold mb-6">Reseñas de la comunidad</h2>

    <!-- Stats generales -->
    <div v-if="reviewsStore.stats && reviewsStore.stats.total > 0" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

      <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 text-center">
        <p class="text-4xl font-bold text-blue-400">{{ reviewsStore.stats.avg_score }}</p>
        <div class="flex justify-center gap-1 mt-2">
          <span v-for="i in 10" :key="i"
            :class="i <= Math.round(Number(reviewsStore.stats.avg_score) || 0) ? 'text-yellow-400' : 'text-gray-700'"
            class="text-sm">★</span>
        </div>
        <p class="text-gray-500 text-xs mt-1">{{ reviewsStore.stats.total }} reseñas</p>
      </div>

      <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 text-center">
        <p class="text-4xl font-bold text-green-400">{{ recommendedPercent }}%</p>
        <p class="text-gray-400 text-sm mt-2">Lo recomiendan</p>
        <p class="text-gray-500 text-xs mt-1">
          {{ reviewsStore.stats.recommended }} de {{ reviewsStore.stats.total }}
        </p>
      </div>

      <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <p class="text-xs text-gray-500 mb-3">Distribución de puntuaciones</p>
        <div class="flex flex-col gap-1">
          <div v-for="score in [10, 9, 8, 7, 6, 5, 4, 3, 2, 1]" :key="score" class="flex items-center gap-2">
            <span class="text-xs text-gray-500 w-4 text-right">{{ score }}</span>
            <div class="flex-1 bg-gray-800 rounded-full h-1.5">
              <div class="h-1.5 rounded-full bg-blue-500 transition-all" :style="{ width: barWidth(score) + '%' }">
              </div>
            </div>
            <span class="text-xs text-gray-600 w-4">
              {{ (reviewsStore.stats.distribution || {})[score] || 0 }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulario para escribir reseña -->
    <div v-if="auth.isAuthenticated" class="mb-8">

      <!-- Reseña existente del usuario -->
      <div v-if="reviewsStore.myReview && reviewsStore.myReview.id && !editing"
        class="bg-blue-950/30 border border-blue-800/50 rounded-xl p-5 mb-4">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs text-blue-400 font-medium">Tu reseña</span>
              <span class="text-gray-500 text-xs">{{ reviewsStore.myReview.score }}/10</span>
            </div>
            <p class="font-medium">{{ reviewsStore.myReview.title }}</p>
            <p class="text-gray-400 text-sm mt-1">{{ reviewsStore.myReview.body }}</p>
          </div>
          <div class="flex gap-2 flex-shrink-0">
            <BaseButton size="sm" variant="ghost" @click="startEdit">Editar</BaseButton>
            <BaseButton size="sm" variant="danger" @click="handleDelete" :loading="deleting">
              Eliminar
            </BaseButton>
          </div>
        </div>
      </div>

      <!-- Formulario nueva / editar reseña -->
      <div v-if="!reviewsStore.myReview || !reviewsStore.myReview.id || editing"
        class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-lg font-bold mb-4">
          {{ editing ? 'Editar tu reseña' : 'Escribe una reseña' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">

          <!-- Puntuación -->
          <div>
            <label class="text-sm text-gray-400 block mb-2">Puntuación *</label>
            <div class="flex gap-2 flex-wrap">
              <button v-for="n in 10" :key="n" type="button" @click="form.score = n" :class="[
                'w-10 h-10 rounded-lg text-sm font-bold transition-all',
                form.score === n
                  ? 'bg-blue-600 text-white scale-110'
                  : 'bg-gray-800 text-gray-400 hover:bg-gray-700'
              ]">
                {{ n }}
              </button>
            </div>
            <p v-if="errors.score" class="text-red-400 text-xs mt-1">{{ errors.score }}</p>
          </div>

          <!-- Recomendación -->
          <div class="flex gap-3">
            <button type="button" @click="form.recommended = true" :class="form.recommended
              ? 'bg-green-700 text-white border-green-600'
              : 'bg-gray-800 text-gray-400 border-gray-700'"
              class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm transition-colors">
              👍 Recomendado
            </button>
            <button type="button" @click="form.recommended = false" :class="!form.recommended
              ? 'bg-red-800 text-white border-red-700'
              : 'bg-gray-800 text-gray-400 border-gray-700'"
              class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm transition-colors">
              👎 No recomendado
            </button>
          </div>

          <BaseInput label="Título de la reseña *" v-model="form.title" :error="errors.title"
            placeholder="Resume tu opinión en una frase" />

          <div>
            <label class="text-sm text-gray-400 block mb-1">Reseña detallada *</label>
            <textarea v-model="form.body" rows="5" placeholder="Comparte tu experiencia (mínimo 20 caracteres)..."
              class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
            <div class="flex justify-between mt-1">
              <p v-if="errors.body" class="text-red-400 text-xs">{{ errors.body }}</p>
              <p class="text-xs text-gray-600 ml-auto">{{ form.body?.length || 0 }}/2000</p>
            </div>
          </div>

          <p v-if="serverError" class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
            {{ serverError }}
          </p>

          <div class="flex gap-3">
            <BaseButton type="submit" :loading="submitting">
              {{ editing ? 'Guardar cambios' : 'Publicar reseña' }}
            </BaseButton>
            <BaseButton v-if="editing" type="button" variant="ghost" @click="cancelEdit">
              Cancelar
            </BaseButton>
          </div>
        </form>
      </div>
    </div>

    <!-- No autenticado -->
    <div v-else class="bg-gray-900 rounded-xl border border-gray-800 p-6 text-center mb-8">
      <p class="text-gray-400 mb-3">Inicia sesión para escribir una reseña</p>
      <RouterLink to="/login">
        <BaseButton>Iniciar sesión</BaseButton>
      </RouterLink>
    </div>

    <!-- Listado -->
    <LoadingSpinner v-if="reviewsStore.loading" />

    <div v-else-if="reviewsStore.reviews.length === 0" class="text-center py-10 text-gray-500">
      <p class="text-4xl mb-3">📝</p>
      <p>Sé el primero en escribir una reseña</p>
    </div>

    <div v-else class="flex flex-col gap-4">
      <div v-for="review in reviewsStore.reviews" :key="review.id"
        class="bg-gray-900 rounded-xl border border-gray-800 p-5">

        <div class="flex items-start justify-between gap-4 mb-3">
          <div class="flex items-center gap-3">
            <div
              class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
              {{ review.user?.name?.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="font-medium text-sm">{{ review.user?.name }}</p>
              <p class="text-gray-500 text-xs">@{{ review.user?.username }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 flex-shrink-0">
            <span :class="review.recommended ? 'text-green-400' : 'text-red-400'" class="text-xs">
              {{ review.recommended ? '👍 Recomendado' : '👎 No recomendado' }}
            </span>
            <span :class="scoreColor(review.score)"
              class="font-bold text-lg w-10 h-10 flex items-center justify-center rounded-lg bg-gray-800">
              {{ review.score }}
            </span>
          </div>
        </div>

        <p class="font-medium mb-1">{{ review.title }}</p>
        <p class="text-gray-400 text-sm leading-relaxed">{{ review.body }}</p>

        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-800">
          <p class="text-xs text-gray-600">
            {{ new Date(review.created_at).toLocaleDateString('es-ES', {
              day: 'numeric', month: 'long', year: 'numeric'
            }) }}
          </p>
          <BaseButton v-if="auth.isAdmin && auth.user?.id !== review.user_id" size="sm" variant="danger"
            @click="handleAdminDelete(review.id)">
            Eliminar
          </BaseButton>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="reviewsStore.pagination?.lastPage > 1" class="flex justify-center gap-2 mt-4">
        <BaseButton v-for="page in reviewsStore.pagination.lastPage" :key="page"
          :variant="page === reviewsStore.pagination.currentPage ? 'primary' : 'ghost'" size="sm"
          @click="$emit('changePage', page)">
          {{ page }}
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useReviewsStore } from '@/stores/reviews'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const props = defineProps({
  gameId: { type: Number, required: true },
})
defineEmits(['changePage'])

const auth = useAuthStore()
const reviewsStore = useReviewsStore()

const editing = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const serverError = ref('')
const errors = ref({})

const form = ref({
  score: 0,
  title: '',
  body: '',
  recommended: true,
})

//Calcula el porcentaje de usuarios que recomiendan el juego
const recommendedPercent = computed(() => {
  if (!reviewsStore.stats?.total) return 0
  return Math.round((reviewsStore.stats.recommended / reviewsStore.stats.total) * 100)
})
//Calcula el ancho de cada barra de distribución de puntuaciones
function barWidth(score) {
  if (!reviewsStore.stats?.total) return 0
  const dist = reviewsStore.stats.distribution || {}
  const count = Number(dist[score] || 0)
  return Math.round((count / reviewsStore.stats.total) * 100)
}
//Asigna color verde, amarillo o rojo a la puntuación según su valor
function scoreColor(score) {
  if (score >= 8) return 'text-green-400'
  if (score >= 5) return 'text-yellow-400'
  return 'text-red-400'
}

//Rellena el formulario con los datos de la reseña existente y activa el modo edición
function startEdit() {
  const r = reviewsStore.myReview
  if (!r) return
  form.value = {
    score: r.score,
    title: r.title,
    body: r.body,
    recommended: r.recommended,
  }
  editing.value = true
}

//Cancela la edición limpiando errores y desactivando el modo edición
function cancelEdit() {
  editing.value = false
  errors.value = {}
  serverError.value = ''
}

//gestiona tanto la creación como la edición de la reseña, manejando errores de validación 422 y errores generales del servidor.
async function handleSubmit() {
  errors.value = {}
  serverError.value = ''
  submitting.value = true

  try {
    if (editing.value) {
      await reviewsStore.updateReview(reviewsStore.myReview.id, form.value)
      editing.value = false
    } else {
      await reviewsStore.createReview({ ...form.value, game_id: props.gameId })
      form.value = { score: 0, title: '', body: '', recommended: true }
    }
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else {
      serverError.value = e.response?.data?.message || 'Error al publicar la reseña.'
    }
  } finally {
    submitting.value = false
  }
}

//Pide confirmación y elimina la reseña propia del usuario
async function handleDelete() {
  const reviewId = reviewsStore.myReview?.id
  if (!reviewId) return
  if (!confirm('¿Eliminar tu reseña?')) return
  deleting.value = true
  try {
    await reviewsStore.deleteReview(reviewId)
  } finally {
    deleting.value = false
  }
}

//Permite al administrador eliminar cualquier reseña ajena con confirmación
async function handleAdminDelete(reviewId) {
  if (!reviewId) return
  if (!confirm('¿Eliminar esta reseña?')) return
  await reviewsStore.deleteReview(reviewId)
}

//Cuando el componente se carga, si el usuario está autenticado busca automaticamente su reseña para ese juego
onMounted(async () => {
  if (auth.isAuthenticated) {
    await reviewsStore.fetchMyReview(props.gameId)
  }
})
</script>
