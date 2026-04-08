<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-gray-900 rounded-xl border border-gray-800 p-8">
      <h1 class="text-2xl font-bold mb-1">Crear cuenta</h1>
      <p class="text-gray-500 text-sm mb-6">Únete a SteamClone hoy</p>

      <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
        <BaseInput id="name" label="Nombre completo" v-model="form.name" :error="errors.name" placeholder="Tu nombre" />
        <BaseInput id="username" label="Nombre de usuario" v-model="form.username" :error="errors.username" placeholder="gamer_pro" />
        <BaseInput id="email" label="Email" type="email" v-model="form.email" :error="errors.email" placeholder="tu@email.com" />
        <BaseInput id="password" label="Contraseña" type="password" v-model="form.password" :error="errors.password" placeholder="Mín. 8 caracteres" />
        <BaseInput id="password_confirmation" label="Confirmar contraseña" type="password" v-model="form.password_confirmation" :error="errors.password_confirmation" placeholder="Repite la contraseña" />

        <p v-if="serverError" class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
          {{ serverError }}
        </p>

        <BaseButton type="submit" size="lg" :loading="loading" class="w-full mt-2">
          Crear cuenta
        </BaseButton>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        ¿Ya tienes cuenta?
        <RouterLink to="/login" class="text-blue-400 hover:underline ml-1">Inicia sesión</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const router = useRouter()
const auth   = useAuthStore()

const form = ref({ name: '', username: '', email: '', password: '', password_confirmation: '' })
const errors = ref({})
const serverError = ref('')
const loading = ref(false)

async function handleRegister() {
  errors.value = {}
  serverError.value = ''
  loading.value = true
  try {
    await auth.register(form.value)
    router.push({ name: 'home' })
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else {
      serverError.value = e.response?.data?.message || 'Error al registrarse.'
    }
  } finally {
    loading.value = false
  }
}
</script>
