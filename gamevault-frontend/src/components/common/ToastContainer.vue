<template>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg pointer-events-auto cursor-pointer border"
        :class="toastClasses[toast.type]"
        @click="remove(toast.id)">

        <span class="text-lg flex-shrink-0 mt-0.5">{{ toastIcons[toast.type] }}</span>

        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium leading-snug">{{ toast.message }}</p>
        </div>

        <button
          @click.stop="remove(toast.id)"
          class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity text-lg leading-none mt-0.5">
          ✕
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, remove } = useToast()

const toastClasses = {
  success: 'bg-green-900 border-green-700 text-green-100',
  error:   'bg-red-900 border-red-700 text-red-100',
  warning: 'bg-yellow-900 border-yellow-700 text-yellow-100',
  info:    'bg-blue-900 border-blue-700 text-blue-100',
}

const toastIcons = {
  success: '✓',
  error:   '✕',
  warning: '⚠',
  info:    'ℹ',
}
</script>

<style scoped>
.toast-enter-active {
  transition: all 0.3s ease;
}
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
.toast-move {
  transition: transform 0.25s ease;
}
</style>
