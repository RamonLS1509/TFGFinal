<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-gray-900 rounded-xl border border-gray-800 p-8">
      <h1 class="text-2xl font-bold mb-1">Iniciar sesión</h1>
      <p class="text-gray-500 text-sm mb-6">Bienvenido de nuevo a GameVault</p>

      <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
        <BaseInput id="email" label="Email" type="email" v-model="form.email" :error="errors.email"
          placeholder="tu@email.com" autocomplete="email" />
        <BaseInput id="password" label="Contraseña" type="password" v-model="form.password" :error="errors.password"
          placeholder="••••••••" autocomplete="current-password" />

        <p v-if="serverError" class="text-red-400 text-sm bg-red-900/30 border border-red-800 rounded px-3 py-2">
          {{ serverError }}
        </p>

        <BaseButton type="submit" size="lg" :loading="loading" class="w-full mt-2">
          Iniciar sesión
        </BaseButton>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        ¿No tienes cuenta?
        <RouterLink to="/register" class="text-blue-400 hover:underline ml-1">Regístrate</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const errors = ref({})
const serverError = ref('')
const loading = ref(false)

async function handleLogin() {
  errors.value = {}
  serverError.value = ''
  loading.value = true
  try {
    await auth.login(form.value)
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else if (e.response?.status === 401) {
      serverError.value = 'Email o contraseña incorrectos.'
    }
  } finally {
    loading.value = false
  }
}
</script>
