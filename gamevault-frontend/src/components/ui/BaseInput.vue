<!--Gestiona la presentación y comportamiento base de todos los campos de texto de la aplicación-->
<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-sm text-gray-400">{{ label }}</label>
    <input
      :id="id"
      v-bind="$attrs"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 placeholder-gray-500
             focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500
             disabled:opacity-50 w-full"
    />
    <p v-if="error" class="text-red-400 text-xs">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  id:         { type: String, default: () => `input-${Math.random().toString(36).slice(2)}` },
  label:      { type: String, default: '' },
  modelValue: { type: String, default: '' },
  error:      { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>
