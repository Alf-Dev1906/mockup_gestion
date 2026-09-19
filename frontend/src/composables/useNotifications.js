import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'

/**
 * Composable para usar notificaciones con el store Pinia
 * Maneja UI state local (panel), store maneja estado global
 */
export const useNotifications = () => {
  const router = useRouter()
  const store = useNotificationsStore()

  // State local (UI)
  const mostrarPanel = ref(false)

  // ════════════════════════════════════════════════════════════════════
  // COMPUTED (delegado al store)
  // ════════════════════════════════════════════════════════════════════

  const notificaciones = computed(() => store.items)
  const contadorNoLeidas = computed(() => store.unreadCount)
  const cargando = computed(() => store.loading)
  const totalNotificaciones = computed(() => store.totalNotificaciones)
  const noLeidas = computed(() => store.noLeidas)
  const leidas = computed(() => store.leidas)

  // ════════════════════════════════════════════════════════════════════
  // MÉTODOS
  // ════════════════════════════════════════════════════════════════════

  /**
   * Obtener todas las notificaciones
   */
  const obtenerNotificaciones = async (page = 1, filtro = 'todas') => {
    return await store.fetchAll(page, filtro)
  }

  /**
   * Marcar como leída (con optimistic update)
   */
  const marcarLeida = async (notificacionId) => {
    try {
      await store.markRead(notificacionId)
    } catch (error) {
      console.error('Error al marcar como leída:', error)
      throw error
    }
  }

  /**
   * Marcar todas como leídas
   */
  const marcarTodasLeidas = async () => {
    try {
      await store.markAllRead()
    } catch (error) {
      console.error('Error al marcar todas como leídas:', error)
      throw error
    }
  }

  /**
   * Eliminar notificación
   */
  const eliminarNotificacion = async (notificacionId) => {
    try {
      await store.deleteNotification(notificacionId)
    } catch (error) {
      console.error('Error al eliminar notificación:', error)
      throw error
    }
  }

  /**
   * Eliminar todas las leídas
   */
  const eliminarTodasLeidas = async () => {
    try {
      await store.deleteAllRead()
    } catch (error) {
      console.error('Error al eliminar leídas:', error)
      throw error
    }
  }

  /**
   * Ir a una notificación
   */
  const irANotificacion = async (notificacion) => {
    // Marcar como leída si no lo está
    if (!notificacion.leida) {
      try {
        await marcarLeida(notificacion.id)
      } catch (error) {
        console.error('Error marcando como leída:', error)
      }
    }

    // Navegar
    if (notificacion.url) {
      await router.push(notificacion.url)
      mostrarPanel.value = false
    }
  }

  /**
   * Icono según tipo
   */
  const getIconoPorTipo = (tipo) => {
    const iconos = {
      examen_publicado: '📝',
      tarea_publicada: '📋',
      tarea_por_vencer: '⏰',
      examen_calificado: '📊',
      asistencia_ausente: '❌',
      entrega_calificada: '📌',
      anuncio_general: '📢',
    }
    return iconos[tipo] || '🔔'
  }

  /**
   * Color según tipo
   */
  const getColorPorTipo = (tipo) => {
    const colores = {
      examen_publicado: 'bg-blue-100 text-blue-700',
      tarea_publicada: 'bg-purple-100 text-purple-700',
      tarea_por_vencer: 'bg-yellow-100 text-yellow-700',
      examen_calificado: 'bg-green-100 text-green-700',
      asistencia_ausente: 'bg-red-100 text-red-700',
      entrega_calificada: 'bg-indigo-100 text-indigo-700',
      anuncio_general: 'bg-gray-100 text-gray-700',
    }
    return colores[tipo] || 'bg-gray-100 text-gray-700'
  }

  // ════════════════════════════════════════════════════════════════════
  // CICLO DE VIDA
  // ════════════════════════════════════════════════════════════════════

  onMounted(() => {
    store.startPolling(30000) // 30 segundos
  })

  onUnmounted(() => {
    store.stopPolling()
  })

  return {
    // State
    notificaciones,
    contadorNoLeidas,
    cargando,
    mostrarPanel,
    totalNotificaciones,
    noLeidas,
    leidas,

    // Métodos
    obtenerNotificaciones,
    marcarLeida,
    marcarTodasLeidas,
    eliminarNotificacion,
    eliminarTodasLeidas,
    irANotificacion,
    getIconoPorTipo,
    getColorPorTipo,
  }
}
