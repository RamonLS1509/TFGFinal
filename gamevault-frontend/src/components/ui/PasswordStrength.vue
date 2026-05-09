<!--Gestiona el feedback visual en tiempo real sobre la seguridad de la contraseña que está intentando crear-->
<template>
  <div v-if="password" class="flex flex-col gap-2 mt-2">
    <!-- Barra de fuerza -->
    <div class="flex gap-1">
      <div v-for="i in 4" :key="i"
        class="h-1.5 flex-1 rounded-full transition-all duration-300"
        :class="i <= strength ? strengthColor : 'bg-gray-700'">
      </div>
    </div>
    <p class="text-xs" :class="strengthTextColor">{{ strengthLabel }}</p>

    <!-- Requisitos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 mt-1">
      <div v-for="req in requirements" :key="req.label"
        class="flex items-center gap-1.5 text-xs"
        :class="req.met ? 'text-green-400' : 'text-gray-500'">
        <span>{{ req.met ? '✓' : '○' }}</span>
        <span>{{ req.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: { type: String, default: '' },
})

const requirements = computed(() => [
  { label: 'Mínimo 8 caracteres',       met: props.password.length >= 8 },
  { label: 'Al menos una mayúscula',     met: /[A-Z]/.test(props.password) },
  { label: 'Al menos una minúscula',     met: /[a-z]/.test(props.password) },
  { label: 'Al menos un número',         met: /[0-9]/.test(props.password) },
  { label: 'Al menos un símbolo (!@#$)', met: /[^A-Za-z0-9]/.test(props.password) },
])

const strength = computed(() => {
  return requirements.value.filter(r => r.met).length
})

const strengthColor = computed(() => {
  if (strength.value <= 1) return 'bg-red-500'
  if (strength.value <= 2) return 'bg-orange-500'
  if (strength.value <= 3) return 'bg-yellow-500'
  return 'bg-green-500'
})

const strengthTextColor = computed(() => {
  if (strength.value <= 1) return 'text-red-400'
  if (strength.value <= 2) return 'text-orange-400'
  if (strength.value <= 3) return 'text-yellow-400'
  return 'text-green-400'
})

const strengthLabel = computed(() => {
  if (strength.value <= 1) return 'Contraseña muy débil'
  if (strength.value <= 2) return 'Contraseña débil'
  if (strength.value <= 3) return 'Contraseña aceptable'
  if (strength.value === 4) return 'Contraseña fuerte'
  return 'Contraseña muy fuerte'
})
</script>
