<template>
  <div style="width: 100%; min-height: 100vh;" class="bg-gray-950 text-gray-100 flex flex-col">
    <AppNavbar />
    <main style="width: 100%;" class="flex-1">
      <RouterView v-slot="{ Component, route }">
        <Transition :name="transitionName" mode="out-in">
          <component :is="Component" :key="route.path" />
        </Transition>
      </RouterView>
    </main>
    <AppFooter />
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppNavbar from '@/components/common/AppNavbar.vue'
import AppFooter from '@/components/common/AppFooter.vue'
import ToastContainer from '@/components/common/ToastContainer.vue'

const route = useRoute()
const transitionName = ref('fade')

const slideRoutes = ['home', 'catalog', 'library', 'wishlist', 'profile', 'api-explorer']

watch(() => route.name, (newName) => {
  transitionName.value = slideRoutes.includes(newName) ? 'slide-up' : 'fade'
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.slide-up-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.slide-up-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.slide-up-enter-from {
  opacity: 0;
  transform: translateY(16px);
}
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
@media (prefers-reduced-motion: reduce) {
  .fade-enter-active,
  .fade-leave-active,
  .slide-up-enter-active,
  .slide-up-leave-active {
    transition: none;
  }
}
</style>
