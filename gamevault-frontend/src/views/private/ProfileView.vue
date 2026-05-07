<template>
  <div class="w-full max-w-screen-lg mx-auto px-6 py-8">

    <h1 class="text-3xl font-bold mb-8">Mi perfil</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Columna izquierda — avatar y stats -->
      <div class="flex flex-col gap-4">

        <!-- Avatar -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 flex flex-col items-center gap-4">
          <div class="relative">
            <img v-if="form.avatar"
              :src="form.avatar"
              :alt="auth.user?.name"
              class="w-24 h-24 rounded-full object-cover border-2 border-blue-600"
              @error="form.avatar = ''"
            />
            <div v-else
              class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-3xl font-bold border-2 border-blue-500">
              {{ auth.user?.name?.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div class="text-center">
            <p class="font-semibold text-lg">{{ auth.user?.name }}</p>
            <p class="text-gray-400 text-sm">@{{ auth.user?.username }}</p>
            <span v-if="auth.isAdmin"
              class="inline-block mt-1 text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 rounded-full px-3 py-0.5">
              Administrador
            </span>
          </div>
          <p v-if="auth.user?.bio" class="text-gray-400 text-sm text-center italic">
            "{{ auth.user.bio }}"
          </p>
        </div>

        <!-- Stats -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Estadísticas</h3>
          <div class="flex flex-col gap-3">
            <RouterLink to="/library"
              class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
              <span class="text-sm text-gray-300">Juegos en biblioteca</span>
              <span class="font-bold text-blue-400 text-lg">{{ libraryStore.entries.length }}</span>
            </RouterLink>
            <RouterLink to="/wishlist"
              class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
              <span class="text-sm text-gray-300">Juegos en wishlist</span>
              <span class="font-bold text-pink-400 text-lg">{{ wishlistStore.items.length }}</span>
            </RouterLink>
            <div class="flex items-center justify-between p-3 bg-gray-800 rounded-lg">
              <span class="text-sm text-gray-300">Miembro desde</span>
              <span class="text-sm text-gray-400">
                {{ auth.user?.created_at ? new Date(auth.user.created_at).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' }) : '—' }}
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- Columna derecha — formularios -->
      <div class="lg:col-span-2 flex flex-col gap-6">

        <!-- Editar perfil -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h2 class="text-xl font-bold mb-6">Editar perfil</h2>

          <form @submit.prevent="handleUpdateProfile" class="flex flex-col gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <BaseInput
                label="Nombre completo"
                v-model="form.name"
                :error="errors.name"
                placeholder="Tu nombre"
              />
              <BaseInput
                label="Nombre de usuario"
                v-model="form.username"
                :error="errors.username"
                placeholder="usuario_123"
              />
            </div>

            <div>
              <label class="text-sm text-gray-400 block mb-1">Biografía</label>
              <textarea
                v-model="form.bio"
                rows="3"
                maxlength="300"
                placeholder="Cuéntanos algo sobre ti..."
                class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
              ></textarea>
              <p class="text-xs text-gray-600 mt-1 text-right">{{ form.bio?.length || 0 }}/300</p>
              <p v-if="errors.bio" class="text-red-400 text-xs mt-1">{{ errors.bio }}</p>
            </div>

            <BaseInput
              label="URL del avatar"
              v-model="form.avatar"
              :error="errors.avatar"
              placeholder="https://ejemplo.com/avatar.jpg"
            />

            <!-- Preview del avatar si hay URL -->
            <div v-if="form.avatar" class="flex items-center gap-3 p-3 bg-gray-800 rounded-lg">
              <img :src="form.avatar" alt="Preview" class="w-10 h-10 rounded-full object-cover" @error="avatarError = true"/>
              <span class="text-sm text-gray-400">
                {{ avatarError ? 'URL de imagen no válida' : 'Vista previa del avatar' }}
              </span>
            </div>

            <p v-if="successProfile" class="text-green-400 text-sm bg-green-900/30 border border-green-800 rounded px-3 py-2">
              ✓ {{ successProfile }}
            </p>
            <p v-if="errorProfile" class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
              {{ errorProfile }}
            </p>

            <div class="flex gap-3 pt-2">
              <BaseButton type="submit" :loading="loadingProfile">
                Guardar cambios
              </BaseButton>
              <BaseButton type="button" variant="ghost" @click="resetForm">
                Cancelar
              </BaseButton>
            </div>
          </form>
        </div>

        <!-- Cambiar contraseña -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
          <h2 class="text-xl font-bold mb-6">Cambiar contraseña</h2>

          <form @submit.prevent="handleChangePassword" class="flex flex-col gap-4">
            <BaseInput
              label="Contraseña actual"
              type="password"
              v-model="passwordForm.current_password"
              :error="passwordErrors.current_password"
              placeholder="Tu contraseña actual"
              autocomplete="current-password"
            />
            <BaseInput
              label="Nueva contraseña"
              type="password"
              v-model="passwordForm.password"
              :error="passwordErrors.password"
              placeholder="Mínimo 8 caracteres"
              autocomplete="new-password"
            />
            <PasswordStrength :password="passwordForm.password" />
            <BaseInput
              label="Confirmar nueva contraseña"
              type="password"
              v-model="passwordForm.password_confirmation"
              :error="passwordErrors.password_confirmation"
              placeholder="Repite la nueva contraseña"
              autocomplete="new-password"
            />

            <p v-if="successPassword" class="text-green-400 text-sm bg-green-900/30 border border-green-800 rounded px-3 py-2">
              ✓ {{ successPassword }}
            </p>
            <p v-if="errorPassword" class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
              {{ errorPassword }}
            </p>

            <div>
              <BaseButton type="submit" :loading="loadingPassword">
                Cambiar contraseña
              </BaseButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useLibraryStore } from '@/stores/library'
import { useWishlistStore } from '@/stores/wishlist'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import PasswordStrength from '@/components/ui/PasswordStrength.vue'

const auth = useAuthStore()
const libraryStore = useLibraryStore()
const wishlistStore = useWishlistStore()


// ── Formulario de perfil ──────────────────────────────────────────────────────
const form = ref({
  name:     '',
  username: '',
  bio:      '',
  avatar:   '',
})
const errors = ref({})
const successProfile = ref('')
const errorProfile = ref('')
const loadingProfile = ref(false)
const avatarError = ref(false)

function resetForm() {
  form.value = {
    name:     auth.user?.name     || '',
    username: auth.user?.username || '',
    bio:      auth.user?.bio      || '',
    avatar:   auth.user?.avatar   || '',
  }
  errors.value = {}
  successProfile.value = ''
  errorProfile.value = ''
  avatarError.value = false
}

async function handleUpdateProfile() {
  errors.value = {}
  successProfile.value = ''
  errorProfile.value = ''
  loadingProfile.value = true

  try {
    const data = await auth.updateProfile(form.value)
    successProfile.value = data.message
    setTimeout(() => { successProfile.value = '' }, 3000)
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else {
      errorProfile.value = e.response?.data?.message || 'Error al actualizar el perfil.'
    }
  } finally {
    loadingProfile.value = false
  }
}

// ── Formulario de contraseña ──────────────────────────────────────────────────
const passwordForm = ref({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})
const passwordErrors = ref({})
const successPassword = ref('')
const errorPassword = ref('')
const loadingPassword = ref(false)

async function handleChangePassword() {
  passwordErrors.value = {}
  successPassword.value = ''
  errorPassword.value = ''
  loadingPassword.value = true

  try {
    const data = await auth.changePassword(passwordForm.value)
    successPassword.value = data.message
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    setTimeout(() => { successPassword.value = '' }, 3000)
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { passwordErrors.value[k] = errs[k][0] })
    } else {
      errorPassword.value = e.response?.data?.message || 'Error al cambiar la contraseña.'
    }
  } finally {
    loadingPassword.value = false
  }
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  resetForm()
  await Promise.all([
    libraryStore.fetchLibrary(),
    wishlistStore.fetchWishlist(),
  ])
})
</script>
