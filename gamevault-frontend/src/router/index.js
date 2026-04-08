import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // ── Públicas ──────────────────────────────────────────────────
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/public/HomeView.vue'),
    },
    {
      path: '/catalog',
      name: 'catalog',
      component: () => import('@/views/public/CatalogView.vue'),
    },
    {
      path: '/games/:id',
      name: 'game-detail',
      component: () => import('@/views/public/GameDetailView.vue'),
    },

    // ── Auth ───────────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { guestOnly: true },
    },

    // ── Privadas (usuario autenticado) ─────────────────────────────
    {
      path: '/library',
      name: 'library',
      component: () => import('@/views/private/LibraryView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/wishlist',
      name: 'wishlist',
      component: () => import('@/views/private/WishlistView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/private/ProfileView.vue'),
      meta: { requiresAuth: true },
    },

    // ── Admin ──────────────────────────────────────────────────────
    {
      path: '/admin/games',
      name: 'admin-games',
      component: () => import('@/views/admin/AdminGamesView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/admin/games/create',
      name: 'admin-game-create',
      component: () => import('@/views/admin/AdminGameFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/admin/games/:id/edit',
      name: 'admin-game-edit',
      component: () => import('@/views/admin/AdminGameFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Guard de navegación
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Si no sabemos aún si el usuario está autenticado, lo comprobamos
  if (auth.user === null && !auth.loading) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'home' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'home' }
  }
})

export default router
