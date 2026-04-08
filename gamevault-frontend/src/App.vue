<template>
  <div class="min-h-screen bg-gray-950 text-gray-100 flex flex-col">
    <AppNavbar />
    <main class="flex-1">
      <RouterView />
    </main>
    <AppFooter />
  </div>
</template>

<script setup>
import AppNavbar from '@/components/common/AppNavbar.vue'
import AppFooter from '@/components/common/AppFooter.vue'
</script>
