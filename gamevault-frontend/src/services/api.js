import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,       // Envía cookies de sesión en cada petición
  withXSRFToken: true,         // Envía el token CSRF de Sanctum automáticamente
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Interceptor: si la sesión expira (401), redirige al login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
