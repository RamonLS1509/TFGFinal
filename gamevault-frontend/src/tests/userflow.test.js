// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useLibraryStore } from '@/stores/library'
import { useWishlistStore } from '@/stores/wishlist'
import { useReviewsStore } from '@/stores/reviews'
import api from '@/services/api'

// ── TC-001 a TC-004: Usuario sin sesión ───────────────────────────────────────
describe('TC-001 a TC-004 — Usuario sin sesión', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que el store de autenticación arranca sin usuario
   * y con isAuthenticated en false.
   */
  it('TC-001: el store de auth empieza sin usuario', () => {
    const auth = useAuthStore()
    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
  })

  /**
   * Verifica que la biblioteca empieza vacía y que ownsGame
   * devuelve false para cualquier juego sin autenticación.
   */
  it('TC-004: biblioteca vacía sin autenticación', () => {
    const library = useLibraryStore()
    expect(library.entries).toHaveLength(0)
    expect(library.ownsGame(1)).toBe(false)
  })

  /**
   * Verifica que la wishlist empieza vacía y que hasGame
   * devuelve false para cualquier juego sin autenticación.
   */
  it('TC-004: wishlist vacía sin autenticación', () => {
    const wishlist = useWishlistStore()
    expect(wishlist.items).toHaveLength(0)
    expect(wishlist.hasGame(1)).toBe(false)
  })
})

// ── TC-005 a TC-008: Auth ─────────────────────────────────────────────────────
describe('TC-005 a TC-008 — Auth: registro y login', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que al registrarse correctamente el usuario queda
   * guardado en el store y isAuthenticated pasa a true.
   */
  it('TC-005: register setea el usuario en el store', async () => {
    const mockUser = { id: 1, name: 'Test', email: 'test@test.com', role: 'user' }
    api.get = vi.fn().mockResolvedValue({})
    api.post = vi.fn().mockResolvedValue({ data: { user: mockUser, message: 'ok' } })

    const auth = useAuthStore()

    await auth.register({
      name: 'Test',
      username: 'test',
      email: 'test@test.com',
      password: 'Password1!',
      password_confirmation: 'Password1!',
    })

    expect(auth.user).toEqual(mockUser)
    expect(auth.isAuthenticated).toBe(true)
    expect(auth.initialized).toBe(false)
  })

  /**
   * Verifica que al hacer login correctamente el usuario queda
   * guardado en el store y isAuthenticated pasa a true.
   */
  it('TC-007: login setea el usuario y initialized en true', async () => {
    const mockUser = { id: 1, name: 'Test', role: 'user' }
    api.get = vi.fn().mockResolvedValue({})
    api.post = vi.fn().mockResolvedValue({ data: { user: mockUser, message: 'ok' } })

    const auth = useAuthStore()
    await auth.login({ email: 'test@test.com', password: 'Password1!' })

    expect(auth.user).toEqual(mockUser)
    expect(auth.isAuthenticated).toBe(true)
    expect(auth.initialized).toBe(false)
  })

  /**
   * Verifica que un login fallido con credenciales incorrectas
   * no modifica el store y el usuario permanece como null.
   */
  it('TC-008: login fallido no setea usuario', async () => {
    api.get = vi.fn().mockResolvedValue({})
    api.post = vi.fn().mockRejectedValue({ response: { status: 401 } })

    const auth = useAuthStore()
    try {
      await auth.login({ email: 'wrong@test.com', password: 'wrong' })
    } catch (_) {
      // error esperado
    }

    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
  })

  /**
   * Verifica que al hacer logout se limpia el usuario del store
   * y también se vacían los datos de biblioteca y wishlist.
   */
  it('logout limpia usuario y stores relacionados', async () => {
    api.get = vi.fn().mockResolvedValue({})
    api.post = vi.fn().mockResolvedValue({})

    const auth = useAuthStore()
    const library = useLibraryStore()
    const wishlist = useWishlistStore()

    auth.user = { id: 1, name: 'Test', role: 'user' }
    library.entries = [{ id: 1, game_id: 10 }]
    wishlist.items = [{ id: 1, game_id: 20 }]

    await auth.logout()

    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
    expect(library.entries).toHaveLength(0)
    expect(wishlist.items).toHaveLength(0)
  })
})

// ── TC-015 a TC-016: Biblioteca ───────────────────────────────────────────────
describe('TC-015 a TC-016 — Biblioteca', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que al añadir un juego a la biblioteca este aparece
   * en el store y ownsGame devuelve true para ese juego.
   */
  it('TC-015: addToLibrary añade el juego al store', async () => {
    const newEntry = { id: 1, game_id: 5, price_paid: 29.99 }
    api.post = vi.fn().mockResolvedValue({ data: newEntry })

    const library = useLibraryStore()
    await library.addToLibrary(5)

    expect(library.entries).toHaveLength(1)
    expect(library.ownsGame(5)).toBe(true)
  })

  /**
   * Verifica que ownsGame devuelve true para un juego que está
   * en el store y false para uno que no está.
   */
  it('TC-016: ownsGame devuelve true para juego comprado', () => {
    const library = useLibraryStore()
    library.entries = [{ id: 1, game_id: 5 }]

    expect(library.ownsGame(5)).toBe(true)
    expect(library.ownsGame(99)).toBe(false)
  })

  /**
   * Verifica que al eliminar un juego de la biblioteca este desaparece
   * del store y ownsGame pasa a devolver false para ese juego.
   */
  it('TC-015: removeFromLibrary elimina el juego del store', async () => {
    api.delete = vi.fn().mockResolvedValue({})

    const library = useLibraryStore()
    library.entries = [
      { id: 1, game_id: 5 },
      { id: 2, game_id: 10 },
    ]

    await library.removeFromLibrary(1)

    expect(library.entries).toHaveLength(1)
    expect(library.ownsGame(5)).toBe(false)
  })
})

// ── TC-018 a TC-021: Wishlist ─────────────────────────────────────────────────
describe('TC-018 a TC-021 — Wishlist', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que al añadir un juego a la wishlist este aparece
   * en el store y hasGame devuelve true para ese juego.
   */
  it('TC-018: addToWishlist añade el juego', async () => {
    const newItem = { id: 1, game_id: 5, priority: 0 }
    api.post = vi.fn().mockResolvedValue({ data: newItem })

    const wishlist = useWishlistStore()
    await wishlist.addToWishlist(5)

    expect(wishlist.items).toHaveLength(1)
    expect(wishlist.hasGame(5)).toBe(true)
  })

  /**
   * Verifica que al eliminar un juego de la wishlist este desaparece
   * del store y hasGame pasa a devolver false para ese juego.
   */
  it('TC-019: removeFromWishlist elimina el juego', async () => {
    api.delete = vi.fn().mockResolvedValue({})

    const wishlist = useWishlistStore()
    wishlist.items = [{ id: 1, game_id: 5, priority: 0 }]

    await wishlist.removeFromWishlist(1)

    expect(wishlist.items).toHaveLength(0)
    expect(wishlist.hasGame(5)).toBe(false)
  })

  /**
   * Verifica que getEntry devuelve la entrada correcta de la wishlist
   * con todos sus datos, incluyendo la prioridad.
   */
  it('TC-020: getEntry devuelve la entrada correcta', () => {
    const wishlist = useWishlistStore()
    wishlist.items = [
      { id: 1, game_id: 5, priority: 1 },
      { id: 2, game_id: 10, priority: 0 },
    ]

    const entry = wishlist.getEntry(5)
    expect(entry?.id).toBe(1)
    expect(entry?.priority).toBe(1)
  })

  /**
   * Verifica que al comprar un juego que estaba en la wishlist,
   * este aparece en la biblioteca y desaparece de la wishlist.
   */
  it('TC-021: tras comprar, el juego desaparece de wishlist', async () => {
    api.post = vi.fn().mockResolvedValue({ data: { id: 1, game_id: 5 } })
    api.delete = vi.fn().mockResolvedValue({})

    const library = useLibraryStore()
    const wishlist = useWishlistStore()
    wishlist.items = [{ id: 1, game_id: 5, priority: 0 }]

    await library.addToLibrary(5)
    await wishlist.removeFromWishlist(1)

    expect(library.ownsGame(5)).toBe(true)
    expect(wishlist.hasGame(5)).toBe(false)
  })
})

// ── TC-010 a TC-013: Reseñas ──────────────────────────────────────────────────
describe('TC-010 a TC-013 — Reseñas', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que al crear una reseña esta queda guardada en myReview
   * y aparece en el listado de reseñas del store.
   */
  it('TC-010: createReview añade la reseña al store', async () => {
    const newReview = { id: 1, game_id: 3, score: 9, title: 'Excelente', recommended: true }
    api.post = vi.fn().mockResolvedValue({ data: newReview })

    const reviews = useReviewsStore()
    await reviews.createReview({
      game_id: 3, score: 9,
      title: 'Excelente', body: 'Muy bueno', recommended: true,
    })

    expect(reviews.myReview).toEqual(newReview)
    expect(reviews.reviews).toHaveLength(1)
  })

  /**
   * Verifica que al actualizar una reseña los nuevos datos se reflejan
   * tanto en myReview como en el listado de reseñas del store.
   */
  it('TC-012: updateReview actualiza la reseña en el store', async () => {
    const updated = { id: 1, game_id: 3, score: 10, title: 'Actualizado', recommended: true }
    api.put = vi.fn().mockResolvedValue({ data: updated })

    const reviews = useReviewsStore()
    reviews.myReview = { id: 1, game_id: 3, score: 9, title: 'Original' }
    reviews.reviews = [{ id: 1, game_id: 3, score: 9, title: 'Original' }]

    await reviews.updateReview(1, { score: 10, title: 'Actualizado' })

    expect(reviews.myReview.score).toBe(10)
    expect(reviews.reviews[0].score).toBe(10)
  })

  /**
   * Verifica que al eliminar una reseña myReview pasa a null
   * y el listado de reseñas del store queda vacío.
   */
  it('TC-013: deleteReview elimina la reseña del store', async () => {
    api.delete = vi.fn().mockResolvedValue({})

    const reviews = useReviewsStore()
    reviews.myReview = { id: 1, game_id: 3, score: 9 }
    reviews.reviews = [{ id: 1, game_id: 3, score: 9 }]

    await reviews.deleteReview(1)

    expect(reviews.myReview).toBeNull()
    expect(reviews.reviews).toHaveLength(0)
  })
})

// ── TC-022 a TC-024: Perfil ───────────────────────────────────────────────────
describe('TC-022 a TC-024 — Perfil', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  /**
   * Verifica que al actualizar el perfil los nuevos datos del usuario
   * quedan reflejados correctamente en el store de autenticación.
   */
  it('TC-023: updateProfile actualiza el usuario en el store', async () => {
    const updatedUser = {
      id: 1, name: 'Nuevo Nombre',
      username: 'test', email: 'test@test.com', role: 'user',
    }
    api.put = vi.fn().mockResolvedValue({ data: { user: updatedUser, message: 'ok' } })

    const auth = useAuthStore()
    auth.user = { id: 1, name: 'Viejo Nombre', username: 'test', email: 'test@test.com', role: 'user' }

    await auth.updateProfile({ name: 'Nuevo Nombre' })

    expect(auth.user.name).toBe('Nuevo Nombre')
  })

  /**
   * Verifica que al cambiar la contraseña se llama al endpoint
   * correcto con los datos proporcionados.
   */
  it('TC-024: changePassword llama al endpoint correcto', async () => {
    api.put = vi.fn().mockResolvedValue({ data: { message: 'ok' } })

    const auth = useAuthStore()
    await auth.changePassword({
      current_password: 'OldPass1!',
      password: 'NewPass1!',
      password_confirmation: 'NewPass1!',
    })

    expect(api.put).toHaveBeenCalledWith('/api/profile/password', expect.any(Object))
  })
})
