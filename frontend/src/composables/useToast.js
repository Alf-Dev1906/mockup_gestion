import { ref } from 'vue'

// Estado global de notificaciones
const toasts = ref([])
let toastId = 0

export function useToast() {
  const show = (message, type = 'info', duration = 4000) => {
    const id = ++toastId
    const toast = {
      id,
      message,
      type, // 'success', 'error', 'warning', 'info'
      duration,
      visible: true
    }

    toasts.value.push(toast)

    // Auto-remover después de la duración
    if (duration > 0) {
      setTimeout(() => {
        remove(id)
      }, duration)
    }

    return id
  }

  const remove = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value[index].visible = false
      setTimeout(() => {
        toasts.value.splice(index, 1)
      }, 300) // Tiempo para animación de salida
    }
  }

  const success = (message, duration) => show(message, 'success', duration)
  const error = (message, duration) => show(message, 'error', duration)
  const warning = (message, duration) => show(message, 'warning', duration)
  const info = (message, duration) => show(message, 'info', duration)

  const clear = () => {
    toasts.value = []
  }

  return {
    toasts,
    show,
    success,
    error,
    warning,
    info,
    remove,
    clear
  }
}
