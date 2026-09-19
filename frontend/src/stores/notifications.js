import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  // ════════════════════════════════════════════════════════════════════
  // STATE
  // ════════════════════════════════════════════════════════════════════

  const items = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const pollingActive = ref(false)
  const failCount = ref(0) // ✅ Contador de fallos consecutivos
  let pollingInterval = null

  // ════════════════════════════════════════════════════════════════════
  // COMPUTED
  // ════════════════════════════════════════════════════════════════════

  const totalNotificaciones = computed(() => items.value.length)

  const noLeidas = computed(() => items.value.filter(n => !n.leida))

  const leidas = computed(() => items.value.filter(n => n.leida))

  const porTipo = computed(() => {
    const grupos = {}
    items.value.forEach(n => {
      if (!grupos[n.tipo]) {
        grupos[n.tipo] = []
      }
      grupos[n.tipo].push(n)
    })
    return grupos
  })

  // ════════════════════════════════════════════════════════════════════
  // ACTIONS
  // ════════════════════════════════════════════════════════════════════

  /**
   * Obtener contador de no leídas (polling ligero)
   * Se ejecuta cada 30 segundos
   */
  async function fetchUnreadCount() {
    try {
      const { data } = await api.get('/notificaciones/no-leidas-count')
      unreadCount.value = data.data.no_leidas_count

      // Actualizar las últimas 3 en el estado local si no están ya
      const ultimas = data.data.ultimas_notificaciones || []
      ultimas.forEach(notifData => {
        const exists = items.value.find(n => n.id === notifData.id)
        if (!exists) {
          items.value.unshift({
            id: notifData.id,
            tipo: notifData.tipo,
            titulo: notifData.titulo,
            mensaje: notifData.mensaje,
            timestamp: notifData.timestamp,
            leida: false,
          })
        }
      })

      // ✅ Reset contador de fallos en éxito
      failCount.value = 0

    } catch (error) {
      console.error('Error fetching unread count:', error)
      // ✅ Incrementar contador de fallos
      failCount.value++
      throw error
    }
  }

  /**
   * Obtener lista completa de notificaciones (paginadas)
   */
  async function fetchAll(page = 1, filtro = 'todas', perPage = 20) {
    loading.value = true
    try {
      const { data } = await api.get('/notificaciones', {
        params: { page, filtro, per_page: perPage },
      })

      items.value = data.data.map(n => ({
        id: n.id,
        tipo: n.tipo,
        titulo: n.titulo,
        mensaje: n.mensaje,
        url: n.url,
        timestamp: n.timestamp,
        leida: n.leida,
        creada_hace: n.creada_hace,
      }))

      return data.pagination
    } catch (error) {
      console.error('Error fetching notifications:', error)
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Marcar notificación como leída
   * Optimistic update: actualiza localmente primero, luego API
   */
  async function markRead(notificationId) {
    // Optimistic update
    const idx = items.value.findIndex(n => n.id === notificationId)
    if (idx >= 0) {
      const oldValue = items.value[idx].leida
      items.value[idx].leida = true

      // Decrementar contador
      if (!oldValue) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }

      // API call
      try {
        await api.post(`/notificaciones/${notificationId}/leer`)
      } catch (error) {
        // Revertir si falla
        items.value[idx].leida = oldValue
        if (!oldValue) {
          unreadCount.value += 1
        }
        throw error
      }
    }
  }

  /**
   * Marcar todas como leídas
   */
  async function markAllRead() {
    // Optimistic update
    const noLeidasAntes = items.value.filter(n => !n.leida).length

    items.value.forEach(n => {
      n.leida = true
    })
    unreadCount.value = 0

    try {
      await api.post('/notificaciones/leer-todas')
    } catch (error) {
      // Revertir: recargar
      await fetchAll()
      throw error
    }
  }

  /**
   * Eliminar una notificación
   */
  async function deleteNotification(notificationId) {
    // Optimistic update
    const idx = items.value.findIndex(n => n.id === notificationId)
    const removed = items.value.splice(idx, 1)[0]

    // Actualizar contador si era no leída
    if (removed && !removed.leida) {
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }

    try {
      await api.delete(`/notificaciones/${notificationId}`)
    } catch (error) {
      // Revertir
      items.value.splice(idx, 0, removed)
      if (removed && !removed.leida) {
        unreadCount.value += 1
      }
      throw error
    }
  }

  /**
   * Eliminar todas las leídas
   */
  async function deleteAllRead() {
    const antes = items.value.length

    // Optimistic update
    items.value = items.value.filter(n => !n.leida)

    try {
      await api.post('/notificaciones/eliminar-todas')
    } catch (error) {
      // Revertir
      await fetchAll()
      throw error
    }
  }

  /**
   * Iniciar polling automático
   */
  function startPolling(intervalMs = 30000) {
    if (pollingActive.value) {
      return
    }

    pollingActive.value = true

    // Ejecutar inmediatamente
    fetchUnreadCount()

    // Luego cada 30s
    pollingInterval = setInterval(fetchUnreadCount, intervalMs)
  }

  /**
   * Detener polling
   */
  function stopPolling() {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
    }
    pollingActive.value = false
  }

  /**
   * Limpiar el store
   */
  function reset() {
    items.value = []
    unreadCount.value = 0
    loading.value = false
    stopPolling()
  }

  // ════════════════════════════════════════════════════════════════════
  // RETURN
  // ════════════════════════════════════════════════════════════════════

  return {
    // State
    items,
    unreadCount,
    loading,
    pollingActive,
    failCount, // ✅ Exponer contador de fallos

    // Computed
    totalNotificaciones,
    noLeidas,
    leidas,
    porTipo,

    // Actions
    fetchUnreadCount,
    fetchAll,
    markRead,
    markAllRead,
    deleteNotification,
    deleteAllRead,
    startPolling,
    stopPolling,
    reset,
  }
})
