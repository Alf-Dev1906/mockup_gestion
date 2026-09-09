import axios from 'axios'

const api = axios.create({
  baseURL: '/api',   // el proxy de Vite lo redirige a http://localhost:8000/api
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // withCredentials en false para Bearer tokens (no usamos cookies)
  withCredentials: false,
})

// Adjuntar token si existe
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('sge_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// Manejar respuestas y errores
api.interceptors.response.use(
  response => response,
  error => {
    // Importación dinámica del toast para evitar circularidad
    import('@/composables/useToast').then(({ useToast }) => {
      const toast = useToast()

      if (!error.response) {
        // Error de red o servidor no responde
        toast.error('Error de conexión. Verifica tu conexión a internet.')
        return Promise.reject(error)
      }

      const status = error.response.status
      const message = error.response.data?.message

      switch (status) {
        case 400:
          toast.error(message || 'Solicitud incorrecta')
          break

        case 401:
          // No autorizado - limpiar sesión y redirigir
          localStorage.removeItem('sge_token')
          localStorage.removeItem('sge_user')
          toast.warning('Sesión expirada. Por favor inicia sesión nuevamente.')
          setTimeout(() => {
            window.location.href = '/login'
          }, 1500)
          break

        case 403:
          toast.error('No tienes permisos para realizar esta acción')
          break

        case 404:
          toast.error(message || 'Recurso no encontrado')
          break

        case 409:
          toast.warning(message || 'Conflicto con el estado actual')
          break

        case 422:
          // Errores de validación
          const errors = error.response.data?.errors
          if (errors) {
            const firstError = Object.values(errors)[0]
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
          } else {
            toast.error(message || 'Error de validación')
          }
          break

        case 429:
          toast.warning('Demasiadas solicitudes. Por favor espera un momento.')
          break

        case 500:
          toast.error('Error interno del servidor. Intenta nuevamente.')
          break

        case 503:
          toast.error('Servicio no disponible. El sistema está en mantenimiento.')
          break

        default:
          toast.error(message || 'Ocurrió un error inesperado')
      }
    })

    return Promise.reject(error)
  }
)

export default api
