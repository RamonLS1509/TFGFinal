import { useToast } from '@/composables/useToast'

const toast = useToast()

async function handleSubmit() {
  errors.value = {}
  serverError.value = ''
  loading.value = true

  try {
    const payload = { ...form.value, price: parseFloat(form.value.price) }
    if (form.value.metacritic_score) {
      payload.metacritic_score = parseInt(form.value.metacritic_score)
    }

    if (isEdit.value) {
      await gamesStore.updateGame(route.params.id, payload)
      toast.success('Juego actualizado correctamente.')
    } else {
      await gamesStore.createGame(payload)
      toast.success('Juego creado correctamente.')
    }
    router.push({ name: 'admin-games' })
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors || {}
      Object.keys(errs).forEach(k => { errors.value[k] = errs[k][0] })
    } else {
      serverError.value = e.response?.data?.message || 'Error al guardar el juego.'
    }
  } finally {
    loading.value = false
  }
}
