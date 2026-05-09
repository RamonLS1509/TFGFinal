<!--Gestiona la apariencia y comportamiento de todos los botones de la aplicación-->
<template>
  <button
    v-bind="$attrs"
    :disabled="disabled || loading"
    :class="[baseClasses, variantClasses[variant], sizeClasses[size]]"
  >
    <span v-if="loading" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin mr-2"></span>
    <slot />
  </button>
</template>

<script setup>
const props = defineProps({
  variant: { type: String, default: 'primary' },
  size:    { type: String, default: 'md' },
  loading: { type: Boolean, default: false },
  disabled:{ type: Boolean, default: false },
})

const baseClasses = 'inline-flex items-center justify-center font-medium rounded transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed'

const variantClasses = {
  primary:   'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-500',
  secondary: 'bg-gray-700 hover:bg-gray-600 text-white focus:ring-gray-500',
  danger:    'bg-red-700 hover:bg-red-600 text-white focus:ring-red-500',
  ghost:     'bg-transparent hover:bg-gray-800 text-gray-300 hover:text-white border border-gray-700',
}

const sizeClasses = {
  sm: 'text-xs px-3 py-1.5',
  md: 'text-sm px-4 py-2',
  lg: 'text-base px-6 py-3',
}
</script>
