import axios from 'axios'
import { useToast } from '@/composables/useToast'

//Gestiona la comunicación con el backend y el manejo global de errores HTTP
const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Rutas que no deben mostrar toast ni redirigir aunque fallen
const silentUrls = ['/api/me', '/sanctum/csrf-cookie']

// Códigos que se gestionan localmente en los formularios
const silentCodes = [422, 409]

const errorMessages = {
  400: 'Solicitud incorrecta.',
  403: 'No tienes permiso para realizar esta acción.',
  404: 'El recurso solicitado no existe.',
  429: 'Demasiadas solicitudes. Espera un momento.',
  500: 'Error interno del servidor. Inténtalo más tarde.',
  503: 'Servicio no disponible. Inténtalo más tarde.',
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status  = error.response?.status
    const url     = error.config?.url ?? ''
    const isSilentUrl  = silentUrls.some(u => url.includes(u))
    const isSilentCode = silentCodes.includes(status)

    // Si es una URL silenciosa o código silencioso, no hacemos nada
    if (isSilentUrl || isSilentCode) {
      return Promise.reject(error)
    }

    const toast = useToast()

    // Error 401 — solo redirige si no es una URL silenciosa
    if (status === 401) {
      toast.error('Tu sesión ha expirado. Inicia sesión de nuevo.')
      setTimeout(() => {
        window.location.href = '/login'
      }, 1500)
      return Promise.reject(error)
    }

    // Resto de errores HTTP conocidos
    if (status && errorMessages[status]) {
      toast.error(errorMessages[status])
      return Promise.reject(error)
    }

    // Error de red (backend no disponible)
    if (!error.response) {
      toast.error('No se puede conectar con el servidor. Verifica tu conexión.')
      return Promise.reject(error)
    }

    // Cualquier otro error con mensaje del servidor
    const message = error.response?.data?.message
    if (message) {
      toast.error(message)
    }

    return Promise.reject(error)
  }
)

export default api
