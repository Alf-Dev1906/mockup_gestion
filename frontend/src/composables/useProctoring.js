import { ref, onMounted, onUnmounted } from 'vue'

/**
 * Composable useProctoring
 * 
 * Detecta comportamientos sospechosos durante exámenes:
 * - Cambio de pestaña (visibilitychange)
 * - Pérdida de foco de ventana (blur)
 * - Salida de pantalla completa (fullscreenchange)
 * 
 * Guards contra falsos positivos:
 * - Grace period de 3 segundos al inicio del examen
 * - Debounce de 500ms en blur
 * - Inactividad de 5 minutos con alerta
 * 
 * Auto-envío: Al superar advertencias_max, automáticamente envía el examen
 */
export function useProctoring(quizId, attemptId, advertenciasMax = 3) {
  const api = useApi() // Inyectar servicio API
  
  // Estado
  const advertenciasCount = ref(0)
  const modal = ref(false)
  const mensajeAdvertencia = ref('')
  const tipoIncidencia = ref('')
  
  // Control de timers
  let debounceTimer = null
  let inactividadTimer = null
  let startTime = null
  
  // Constantes
  const GRACE_PERIOD_MS = 3000         // Ignorar eventos en los primeros 3 segundos
  const DEBOUNCE_MS = 500               // Debounce en blur
  const INACTIVIDAD_MINUTOS = 5         // Minutos sin actividad antes de alerta

  /**
   * Registrar incidencia en backend + mostrar modal
   */
  const registrarIncidencia = async (tipo, descripcion) => {
    try {
      const { data } = await api.post(`/estudiante/quizzes/${quizId}/incidencias`, {
        attempt_id: attemptId,
        tipo,
        descripcion,
        ocurrido_at: new Date().toISOString(),
        metadata: {
          timestamp: Date.now(),
          userAgent: navigator.userAgent,
          screenResolution: `${screen.width}x${screen.height}`,
          timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        },
      })

      // Actualizar contador local
      advertenciasCount.value = data.data?.advertencias_count || advertenciasCount.value + 1
      tipoIncidencia.value = tipo
      mensajeAdvertencia.value = descripcion
      modal.value = true

      // Auto-enviar si se supera el límite
      if (data.data?.auto_enviado) {
        setTimeout(() => {
          modal.value = false
          enviarAutomaticamente()
        }, 2000)
      }
    } catch (error) {
      console.error('Error registrando incidencia:', error)
    }
  }

  /**
   * Enviar examen automáticamente después de superar advertencias
   */
  const enviarAutomaticamente = async () => {
    try {
      await api.post(`/estudiante/quizzes/${quizId}/enviar`, {
        attempt_id: attemptId,
        auto_enviado: true,
      })
      
      // Limpiar listeners
      limpiar()
      
      // Notificar que fue auto-enviado (el componente manejará la navegación)
      window.dispatchEvent(new CustomEvent('exam-auto-submitted', {
        detail: { quizId, attemptId }
      }))
    } catch (error) {
      console.error('Error enviando examen automáticamente:', error)
    }
  }

  /**
   * Evento: Cambio de visibilidad (cambio de pestaña / minimizar)
   */
  const onVisibilityChange = () => {
    // Grace period: ignorar los primeros 3 segundos
    if (Date.now() - startTime < GRACE_PERIOD_MS) return
    
    if (document.hidden) {
      registrarIncidencia(
        'cambio_pestana',
        'El estudiante cambió a otra pestaña o minimizó el navegador.'
      )
    }
  }

  /**
   * Evento: Pérdida de foco de ventana
   * Con debounce para evitar dobles disparos
   */
  const onWindowBlur = () => {
    // Grace period: ignorar los primeros 3 segundos
    if (Date.now() - startTime < GRACE_PERIOD_MS) return
    
    // Debounce para evitar múltiples disparos rápidos
    if (debounceTimer) clearTimeout(debounceTimer)
    
    debounceTimer = setTimeout(() => {
      // Verificar que el foco se perdió realmente (no entró a un input de la app)
      if (!document.hasFocus()) {
        registrarIncidencia(
          'perdida_foco',
          'La ventana del navegador perdió el foco.'
        )
      }
    }, DEBOUNCE_MS)
  }

  /**
   * Evento: Salida de pantalla completa
   */
  const onFullscreenChange = () => {
    // Grace period: ignorar los primeros 3 segundos
    if (Date.now() - startTime < GRACE_PERIOD_MS) return
    
    // Detectar si se salió de fullscreen
    if (!document.fullscreenElement && document.hidden === false) {
      registrarIncidencia(
        'pantalla_completa',
        'El estudiante salió del modo de pantalla completa.'
      )
    }
  }

  /**
   * Monitorear inactividad
   * Si el estudiante no mueve el ratón ni presiona teclas por 5 minutos
   */
  const resetInactividadTimer = () => {
    if (inactividadTimer) clearTimeout(inactividadTimer)
    
    inactividadTimer = setTimeout(() => {
      // Solo registrar si sigue siendo visible (no cambió de pestaña)
      if (!document.hidden) {
        registrarIncidencia(
          'inactividad',
          `El estudiante estuvo inactivo por más de ${INACTIVIDAD_MINUTOS} minutos.`
        )
      }
    }, INACTIVIDAD_MINUTOS * 60 * 1000)
  }

  /**
   * Activar todos los listeners de proctoring
   */
  const activar = () => {
    startTime = Date.now()
    
    // Listeners principales
    document.addEventListener('visibilitychange', onVisibilityChange)
    window.addEventListener('blur', onWindowBlur)
    document.addEventListener('fullscreenchange', onFullscreenChange)
    
    // Listeners para monitoreo de inactividad
    document.addEventListener('mousemove', resetInactividadTimer)
    document.addEventListener('keydown', resetInactividadTimer)
    document.addEventListener('click', resetInactividadTimer)
    
    // Iniciar timer de inactividad
    resetInactividadTimer()
    
    console.log('🛡️  Proctoring activado para quiz:', quizId)
  }

  /**
   * Desactivar todos los listeners (limpieza)
   */
  const limpiar = () => {
    // Remover listeners principales
    document.removeEventListener('visibilitychange', onVisibilityChange)
    window.removeEventListener('blur', onWindowBlur)
    document.removeEventListener('fullscreenchange', onFullscreenChange)
    
    // Remover listeners de inactividad
    document.removeEventListener('mousemove', resetInactividadTimer)
    document.removeEventListener('keydown', resetInactividadTimer)
    document.removeEventListener('click', resetInactividadTimer)
    
    // Limpiar timers
    if (debounceTimer) clearTimeout(debounceTimer)
    if (inactividadTimer) clearTimeout(inactividadTimer)
    
    console.log('🔓 Proctoring desactivado para quiz:', quizId)
  }

  /**
   * Cerrar el modal de advertencia
   */
  const cerrarModal = () => {
    modal.value = false
  }

  // Auto-limpiar al desmontar el componente
  onMounted(() => {
    activar()
  })

  onUnmounted(() => {
    limpiar()
  })

  return {
    // Estado
    advertenciasCount,
    modal,
    mensajeAdvertencia,
    tipoIncidencia,
    
    // Acciones
    cerrarModal,
    limpiar,
    activar,
    registrarIncidencia,
  }
}

/**
 * Helper para inyectar el servicio API si no está disponible globalmente
 */
function useApi() {
  // Si el proyecto usa composables/useApi o similar, usar eso
  // Si no, usar una instancia de axios/fetch
  try {
    return require('@/services/api').default
  } catch {
    // Fallback a fetch si no hay servicio API configurado
    return {
      post: async (url, data) => {
        const res = await fetch(`${import.meta.env.VITE_API_BASE_URL}${url}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        })
        return res.json()
      }
    }
  }
}
