import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

/**
 * Store Pinia para la gestión centralizada del estado del examen
 * Maneja:
 * - Quiz actual, intento actual
 * - Respuestas del estudiante
 * - Estado de fase (confirmacion | examen | enviado)
 * - Advertencias de proctoring
 * - Timer del examen
 */
export const useExamenStore = defineStore('examen', () => {
  // ──────────────────────────────────────────────────────────────────────
  // ESTADO DEL EXAMEN
  // ──────────────────────────────────────────────────────────────────────

  const quizActual = ref(null)
  const intentoActual = ref(null)
  const respuestas = ref({})              // { [idx_pregunta]: respuesta_obj }
  const fase = ref('confirmacion')        // confirmacion | examen | enviado
  const autoEnviado = ref(false)
  const notaInmediata = ref(null)
  const notaMaxima = ref(null)
  const intentoActivo = ref(false)

  // ──────────────────────────────────────────────────────────────────────
  // ESTADO DE ADVERTENCIAS (Proctoring)
  // ──────────────────────────────────────────────────────────────────────

  const advertenciasCount = ref(0)
  const advertenciasMax = ref(3)
  const incidentes = ref([])              // Historial de incidentes

  // ──────────────────────────────────────────────────────────────────────
  // ESTADO DEL TIMER
  // ──────────────────────────────────────────────────────────────────────

  const timerSeg = ref(0)
  const estaActivo = ref(false)

  // ──────────────────────────────────────────────────────────────────────
  // SETTERS - Quiz & Intento
  // ──────────────────────────────────────────────────────────────────────

  const setQuizActual = (quiz) => {
    quizActual.value = quiz
    advertenciasMax.value = quiz?.advertencias_max || 3
  }

  const setIntentoActual = (intento) => {
    intentoActual.value = intento
  }

  const setRespuesta = (idxPregunta, respuesta) => {
    respuestas.value[idxPregunta] = respuesta
  }

  const setRespuestas = (resp) => {
    respuestas.value = resp
  }

  // ──────────────────────────────────────────────────────────────────────
  // SETTERS - Fase & Estado
  // ──────────────────────────────────────────────────────────────────────

  const setFase = (nuevaFase) => {
    fase.value = nuevaFase
  }

  const setAutoEnviado = (valor) => {
    autoEnviado.value = valor
  }

  const setIntentoActivo = (activo) => {
    intentoActivo.value = activo
  }

  const setNotaInmediata = (nota) => {
    notaInmediata.value = nota
  }

  const setNotaMaxima = (nota) => {
    notaMaxima.value = nota
  }

  // ──────────────────────────────────────────────────────────────────────
  // SETTERS - Advertencias (Proctoring)
  // ──────────────────────────────────────────────────────────────────────

  const incrementarAdvertencias = () => {
    advertenciasCount.value++
  }

  const setAdvertenciasCount = (count) => {
    advertenciasCount.value = count
  }

  const setAdvertenciasMax = (max) => {
    advertenciasMax.value = max
  }

  const agregarIncidente = (incidente) => {
    incidentes.value.push({
      ...incidente,
      timestamp: new Date().toISOString(),
    })
  }

  // ──────────────────────────────────────────────────────────────────────
  // SETTERS - Timer
  // ──────────────────────────────────────────────────────────────────────

  const setTimerSeg = (seg) => {
    timerSeg.value = seg
  }

  const decrementarTimer = () => {
    if (timerSeg.value > 0) timerSeg.value--
  }

  const setEstaActivo = (activo) => {
    estaActivo.value = activo
  }

  // ──────────────────────────────────────────────────────────────────────
  // COMPUTED
  // ──────────────────────────────────────────────────────────────────────

  const timerDisplay = computed(() => {
    const minutos = Math.floor(timerSeg.value / 60)
    const segundos = timerSeg.value % 60
    return `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`
  })

  const timerCritico = computed(() => timerSeg.value <= 120) // 2 minutos
  const timerAdvierte = computed(() => timerSeg.value <= 300) // 5 minutos

  const advertenciasAlcanzadas = computed(() =>
    advertenciasCount.value >= advertenciasMax.value
  )

  const porcentajeTiempo = computed(() => {
    if (!quizActual.value) return 100
    const duracionSeg = quizActual.value.duracion_minutos * 60
    return Math.round((timerSeg.value / duracionSeg) * 100)
  })

  // ──────────────────────────────────────────────────────────────────────
  // MÉTODOS - Utilidades
  // ──────────────────────────────────────────────────────────────────────

  /**
   * Limpiar todo el estado del store
   * Llamar cuando se cierra el examen o se navega fuera
   */
  const limpiar = () => {
    quizActual.value = null
    intentoActual.value = null
    respuestas.value = {}
    fase.value = 'confirmacion'
    autoEnviado.value = false
    notaInmediata.value = null
    notaMaxima.value = null
    intentoActivo.value = false
    advertenciasCount.value = 0
    advertenciasMax.value = 3
    incidentes.value = []
    timerSeg.value = 0
    estaActivo.value = false
  }

  /**
   * Reiniciar solo las respuestas (para resetear un intento)
   */
  const limpiarRespuestas = () => {
    respuestas.value = {}
  }

  /**
   * Reiniciar contador de advertencias (para nuevo intento)
   */
  const limpiarAdvertencias = () => {
    advertenciasCount.value = 0
    incidentes.value = []
  }

  // ──────────────────────────────────────────────────────────────────────
  // TIMER MANAGEMENT
  // ──────────────────────────────────────────────────────────────────────

  let tickInterval = null
  let syncInterval = null
  let api = null

  /**
   * Iniciar timer con sincronización backend
   * @param {number} duracionMinutos - Duración del examen en minutos
   * @param {string} token - Token de autenticación para sync
   * @param {object} apiService - Instancia del servicio API
   */
  const iniciarTimer = (duracionMinutos, apiService) => {
    api = apiService
    timerSeg.value = duracionMinutos * 60
    estaActivo.value = true

    // Tick cada 1 segundo: decrementar timer
    tickInterval = setInterval(() => {
      if (timerSeg.value > 0) {
        timerSeg.value--
      } else {
        // Tiempo agotado → detener timer y notificar
        detenerTimer()
        window.dispatchEvent(new CustomEvent('exam-time-expired', {
          detail: { quizId: quizActual.value?.id, attemptId: intentoActual.value?.id }
        }))
      }
    }, 1000)

    // Sync cada 30 segundos: persistir tiempo en backend
    syncInterval = setInterval(async () => {
      if (api && intentoActual.value) {
        try {
          await api.patch(`/estudiante/quiz-attempts/${intentoActual.value.id}`, {
            tiempo_restante_seg: timerSeg.value,
          })
          console.log('⏱️  Timer sincronizado:', timerDisplay.value)
        } catch (error) {
          console.warn('⚠️  Error sincronizando timer:', error.message)
          // No fallar silenciosamente, pero continuar con el timer local
        }
      }
    }, 30000)

    console.log('⏱️  Timer iniciado:', timerDisplay.value)
  }

  /**
   * Detener timer y limpiar intervals
   */
  const detenerTimer = () => {
    if (tickInterval) clearInterval(tickInterval)
    if (syncInterval) clearInterval(syncInterval)
    estaActivo.value = false
    console.log('⏱️  Timer detenido')
  }

  /**
   * Pausar timer (sin limpiar intervals)
   */
  const pausarTimer = () => {
    estaActivo.value = false
    console.log('⏱️  Timer pausado')
  }

  /**
   * Reanudar timer
   */
  const reanudarTimer = () => {
    estaActivo.value = true
    console.log('⏱️  Timer reanudado')
  }

  /**
   * Restablecer timer desde backend
   * Llamar cuando hay recarga y el usuario vuelve al examen
   */
  const restablecerDesdeBackend = (tiempoSeg) => {
    timerSeg.value = tiempoSeg
    console.log('⏱️  Timer restablecido desde backend:', timerDisplay.value)
  }

  // ──────────────────────────────────────────────────────────────────────
  // EXPORT
  // ──────────────────────────────────────────────────────────────────────

  return {
    // ── State
    quizActual,
    intentoActual,
    respuestas,
    fase,
    autoEnviado,
    notaInmediata,
    notaMaxima,
    intentoActivo,
    advertenciasCount,
    advertenciasMax,
    incidentes,
    timerSeg,
    estaActivo,

    // ── Setters
    setQuizActual,
    setIntentoActual,
    setRespuesta,
    setRespuestas,
    setFase,
    setAutoEnviado,
    setIntentoActivo,
    setNotaInmediata,
    setNotaMaxima,
    incrementarAdvertencias,
    setAdvertenciasCount,
    setAdvertenciasMax,
    agregarIncidente,
    setTimerSeg,
    decrementarTimer,
    setEstaActivo,

    // ── Computed
    timerDisplay,
    timerCritico,
    timerAdvierte,
    advertenciasAlcanzadas,
    porcentajeTiempo,

    // ── Métodos
    limpiar,
    limpiarRespuestas,
    limpiarAdvertencias,

    // ── Timer Management
    iniciarTimer,
    detenerTimer,
    pausarTimer,
    reanudarTimer,
    restablecerDesdeBackend,
  }
})
