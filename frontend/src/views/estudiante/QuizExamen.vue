<template>
  <!-- Pantalla completa sin layout cuando el examen está activo -->
  <div class="min-h-screen bg-gray-50" :class="{ 'select-none': intentoActivo }">

    <!-- ══ PANTALLA DE CONFIRMACION (antes de iniciar) ══ -->
    <div v-if="fase === 'confirmacion'" class="min-h-screen flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-xl w-full p-8">
        <div class="text-center mb-6">
          <span class="text-5xl">{{ iconoPorTipo(quiz?.tipo) }}</span>
          <h1 class="text-2xl font-bold text-gray-900 mt-4">{{ quiz?.titulo }}</h1>
          <p class="text-gray-500 mt-1">{{ quiz?.horario?.materia?.nombre }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-indigo-700">{{ quiz?.duracion_minutos }}</p>
            <p class="text-xs text-gray-500 mt-0.5">minutos</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-indigo-700">{{ quiz?.preguntas?.length ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">preguntas</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-indigo-700">{{ totalPuntosQuiz }}</p>
            <p class="text-xs text-gray-500 mt-0.5">puntos totales</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-indigo-700">{{ quiz?.intentos_permitidos ?? 1 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">intento(s) permitido(s)</p>
          </div>
        </div>

        <div v-if="quiz?.instrucciones" class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6 text-sm text-indigo-900">
          <p class="font-semibold mb-1">Instrucciones:</p>
          <p>{{ quiz.instrucciones }}</p>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800 space-y-1">
          <p class="font-semibold">⚠️ Antes de comenzar:</p>
          <p>• Tendrás <strong>{{ quiz?.duracion_minutos }} minutos</strong> desde que inicies.</p>
          <p>• Cambiar de pestaña o perder el foco genera advertencias (máx. {{ quiz?.advertencias_max }}).</p>
          <p>• Al superar el límite de advertencias el examen se enviará automáticamente.</p>
          <p>• <strong>No puedes pausar el examen una vez iniciado.</strong></p>
        </div>

        <div class="flex gap-3">
          <router-link to="/estudiante/quizzes"
            class="flex-1 border border-gray-300 text-gray-700 font-semibold py-3 rounded-xl text-center hover:border-gray-400 transition text-sm">
            ← Volver
          </router-link>
          <button @click="iniciarExamen" :disabled="loading"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white font-bold py-3 rounded-xl transition shadow text-sm">
            {{ loading ? 'Iniciando...' : '▶ Comenzar Examen' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══ INTERFAZ DEL EXAMEN ══ -->
    <div v-if="fase === 'examen'" class="max-w-4xl mx-auto px-4 py-6">
      <!-- Barra superior con timer y advertencias -->
      <ExamTimerBar
        :tiempo-restante="timerSeg"
        :duracion-total="quiz?.duracion_minutos * 60 || 0"
        :advertencias-count="advertencias"
        :advertencias-max="quiz?.advertencias_max ?? 3"
        :esta-activo="intentoActivo"
        class="mb-6"
      />

      <!-- Contenido principal -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex items-center gap-4 sticky top-4 z-20">
        <!-- Título y materia -->
        <div class="flex-1 min-w-0">
          <p class="font-bold text-gray-900 truncate text-sm">{{ quiz?.titulo }}</p>
          <p class="text-xs text-gray-400 truncate">{{ quiz?.horario?.materia?.nombre }}</p>
        </div>

        <!-- Progreso -->
        <div class="hidden md:flex items-center gap-2 text-xs text-gray-500">
          <span>{{ respondidas }} / {{ preguntas.length }}</span>
          <div class="w-24 bg-gray-200 rounded-full h-1.5">
            <div class="bg-indigo-500 h-1.5 rounded-full transition-all"
              :style="{ width: progresoPorc + '%' }" />
          </div>
        </div>

        <!-- Autosave indicator mejorado -->
        <div class="text-xs flex items-center gap-1.5">
          <span v-if="autoSaving" class="text-indigo-500 animate-pulse">●</span>
          <span v-else-if="saveFailCount >= 3" class="text-amber-500">⚠</span>
          <span v-else class="text-green-500">✓</span>
          <span class="text-gray-500">
            {{ autoSaving ? 'Guardando...' : saveFailCount >= 3 ? 'Sin conexión' : 'Guardado' }}
          </span>
        </div>
      </div>

      <!-- Preguntas -->
      <div class="space-y-6">
        <div v-for="(pregunta, idx) in preguntas" :key="pregunta.id"
          :id="`pregunta-${idx}`"
          class="bg-white rounded-2xl border shadow-sm p-6 scroll-mt-24 transition"
          :class="preguntaRespondida(idx) ? 'border-indigo-200' : 'border-gray-100'">

          <!-- Número y tipo -->
          <div class="flex items-start gap-3 mb-4">
            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
              :class="preguntaRespondida(idx) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'">
              {{ idx + 1 }}
            </span>
            <div class="flex-1">
              <p class="text-xs text-gray-400 mb-1">{{ labelTipo(pregunta.tipo) }} · {{ pregunta.puntos }} pts</p>
              <p class="font-semibold text-gray-900 leading-relaxed">{{ pregunta.enunciado }}</p>
            </div>
          </div>

          <!-- ── SELECCION MULTIPLE ── -->
          <div v-if="pregunta.tipo === 'seleccion'" class="space-y-2 ml-11">
            <label v-for="(op, oi) in pregunta.contenido.opciones" :key="op.id"
              class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
              :class="esOpcionSeleccionada(idx, op.id)
                ? 'border-indigo-300 bg-indigo-50'
                : 'border-gray-200 hover:border-indigo-200 hover:bg-gray-50'">
              <input
                :type="pregunta.contenido.permite_multiple ? 'checkbox' : 'radio'"
                :name="'q_' + idx"
                :value="op.id"
                :checked="esOpcionSeleccionada(idx, op.id)"
                @change="seleccionarOpcion(idx, op.id, pregunta.contenido.permite_multiple)"
                class="accent-indigo-600 w-4 h-4 flex-shrink-0" />
              <span class="text-sm text-gray-800">{{ op.texto }}</span>
            </label>
          </div>

          <!-- ── VERDADERO / FALSO ── -->
          <div v-if="pregunta.tipo === 'verdadero_falso'" class="ml-11 space-y-3">
            <p class="text-sm font-medium text-gray-800 bg-gray-50 rounded-xl p-3 border border-gray-200">
              {{ pregunta.contenido.afirmacion }}
            </p>
            <div class="flex gap-3">
              <label class="flex items-center justify-center gap-2 flex-1 p-3 rounded-xl border cursor-pointer transition"
                :class="respuestas[idx]?.valor === true ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200'">
                <input type="radio" :name="'vf_' + idx" :value="true"
                  :checked="respuestas[idx]?.valor === true"
                  @change="setRespuesta(idx, { valor: true })"
                  class="accent-green-600 w-4 h-4" />
                <span class="font-bold text-green-700">✓ Verdadero</span>
              </label>
              <label class="flex items-center justify-center gap-2 flex-1 p-3 rounded-xl border cursor-pointer transition"
                :class="respuestas[idx]?.valor === false ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-200'">
                <input type="radio" :name="'vf_' + idx" :value="false"
                  :checked="respuestas[idx]?.valor === false"
                  @change="setRespuesta(idx, { valor: false })"
                  class="accent-red-600 w-4 h-4" />
                <span class="font-bold text-red-700">✗ Falso</span>
              </label>
            </div>
            <div v-if="pregunta.contenido.justificacion_requerida">
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Justificación (requerida)</label>
              <textarea
                :value="respuestas[idx]?.justificacion || ''"
                @input="setRespuesta(idx, { ...respuestas[idx], justificacion: $event.target.value })"
                rows="3" placeholder="Escribe tu justificación aquí..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
            </div>
          </div>

          <!-- ── RELACION DE COLUMNAS ── -->
          <div v-if="pregunta.tipo === 'relacion_columnas'" class="ml-11">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <p class="text-xs font-bold text-gray-500 uppercase mb-2">Términos</p>
                <div v-for="(item, ii) in pregunta.contenido.columna_a" :key="item.id"
                  class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm text-indigo-900 font-medium">
                  {{ ii + 1 }}. {{ item.texto }}
                </div>
              </div>
              <div class="space-y-2">
                <p class="text-xs font-bold text-gray-500 uppercase mb-2">Selecciona el par</p>
                <div v-for="(item, ii) in pregunta.contenido.columna_b_mezclada || pregunta.contenido.columna_b" :key="item.id"
                  class="p-2 bg-purple-50 border border-purple-200 rounded-xl">
                  <p class="text-xs text-purple-700 mb-1">{{ item.texto }}</p>
                  <select
                    :value="getPar(idx, item.id)"
                    @change="setPar(idx, item.id, $event.target.value, pregunta)"
                    class="w-full text-xs border border-purple-300 rounded-lg px-2 py-1 bg-white">
                    <option value="">— Seleccionar —</option>
                    <option v-for="a in pregunta.contenido.columna_a" :key="a.id" :value="a.id">
                      {{ a.texto }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- ── LLENADO DE ESPACIOS ── -->
          <div v-if="pregunta.tipo === 'espacios'" class="ml-11">
            <p class="text-xs text-gray-500 mb-3">Completa los espacios en blanco:</p>
            <div class="text-sm text-gray-900 leading-loose">
              <template v-for="(parte, pi) in splitPlantilla(pregunta.contenido.plantilla)" :key="pi">
                <span v-if="typeof parte === 'string'">{{ parte }}</span>
                <input v-else
                  :value="getEspacio(idx, parte.espacio + 1)"
                  @input="setEspacio(idx, parte.espacio + 1, $event.target.value)"
                  type="text" placeholder="..."
                  class="border-b-2 border-indigo-400 bg-indigo-50 px-2 py-0.5 text-center text-indigo-800 font-semibold rounded mx-1 w-28 focus:outline-none focus:border-indigo-600 text-sm" />
              </template>
            </div>
          </div>

          <!-- ── MULTIMEDIA ── -->
          <div v-if="pregunta.tipo === 'multimedia'" class="ml-11 space-y-3">
            <p class="text-sm text-gray-700">{{ pregunta.contenido.descripcion }}</p>
            <div v-if="pregunta.contenido.archivo_adjunto_url"
              class="rounded-xl overflow-hidden border border-gray-200">
              <img v-if="pregunta.contenido.tipo_adjunto === 'imagen'"
                :src="pregunta.contenido.archivo_adjunto_url" class="max-h-64 w-auto mx-auto" />
              <a v-else :href="pregunta.contenido.archivo_adjunto_url" target="_blank"
                class="block p-3 text-sm text-indigo-600 font-semibold text-center">
                📎 Ver archivo adjunto
              </a>
            </div>
            <!-- Upload -->
            <div class="border-2 border-dashed rounded-xl p-5 text-center"
              :class="respuestas[idx]?.archivo_url ? 'border-indigo-300 bg-indigo-50' : 'border-gray-300'">
              <div v-if="respuestas[idx]?.archivo_url">
                <p class="text-sm font-semibold text-indigo-700">✓ {{ respuestas[idx].nombre_archivo }}</p>
                <button @click="setRespuesta(idx, {})" class="text-xs text-red-500 mt-1 hover:underline">Eliminar</button>
              </div>
              <div v-else>
                <p class="text-gray-400 text-sm mb-2">📎 Sube tu respuesta</p>
                <p class="text-xs text-gray-400 mb-3">
                  {{ pregunta.contenido.extensiones_respuesta_permitidas?.join(', ') }} · Máx {{ pregunta.contenido.tamano_max_mb }}MB
                </p>
                <label class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                  Seleccionar archivo
                  <input type="file"
                    :accept="'.'+pregunta.contenido.extensiones_respuesta_permitidas?.join(',.')"
                    class="hidden"
                    @change="(e) => subirArchivo(idx, e, pregunta)" />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Botón de envío -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="font-semibold text-gray-900">{{ respondidas }} de {{ preguntas.length }} preguntas respondidas</p>
            <p v-if="respondidas < preguntas.length" class="text-xs text-orange-600 mt-0.5">
              ⚠️ Tienes {{ preguntas.length - respondidas }} pregunta(s) sin responder
            </p>
          </div>
          <div class="flex gap-2 flex-wrap justify-end">
            <button v-for="(_, i) in preguntas" :key="i"
              @click="scrollAPregunta(i)"
              class="w-7 h-7 rounded-lg text-xs font-bold transition"
              :class="preguntaRespondida(i) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
              {{ i + 1 }}
            </button>
          </div>
        </div>

        <button @click="confirmarEnvio" :disabled="enviando"
          class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-xl transition shadow text-base">
          {{ enviando ? 'Enviando...' : '✓ Enviar Examen' }}
        </button>
      </div>
    </div>

    <!-- ══ MODAL: Confirmar envío ══ -->
    <div v-if="modalEnvio" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
      @click.self="modalEnvio = false">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">
        <p class="text-5xl mb-4">📤</p>
        <h2 class="text-xl font-bold text-gray-900 mb-2">¿Enviar el examen?</h2>
        <p class="text-gray-500 text-sm mb-2">
          Has respondido <strong>{{ respondidas }}</strong> de <strong>{{ preguntas.length }}</strong> preguntas.
        </p>
        <p v-if="respondidas < preguntas.length" class="text-orange-600 text-sm font-semibold mb-4">
          ⚠️ {{ preguntas.length - respondidas }} pregunta(s) quedarán sin responder.
        </p>
        <p class="text-gray-500 text-sm mb-6">Esta acción <strong>no se puede deshacer</strong>.</p>
        <div class="flex gap-3">
          <button @click="modalEnvio = false"
            class="flex-1 border border-gray-300 text-gray-700 font-semibold py-3 rounded-xl hover:border-gray-400 transition">
            Seguir respondiendo
          </button>
          <button @click="enviarExamen" :disabled="enviando"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white font-bold py-3 rounded-xl transition shadow">
            Sí, enviar
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL: Advertencia de proctoring (nuevo componente) ══ -->
    <ProctoringWarningModal
      v-if="modalAdvertencia"
      :modal-visible="modalAdvertencia"
      :mensaje-advertencia="mensajeAdvertencia"
      :advertencias-count="advertencias"
      :advertencias-max="quiz?.advertencias_max ?? 3"
      @close="cerrarAdvertencia"
    />

    <!-- ══ PANTALLA BLOQUEADA (auto-submit o timeout) ══ -->
    <ExamBlockedScreen
      v-if="autoEnviado && fase === 'enviado'"
      :blocked="autoEnviado"
      :reason="timerSeg === 0 ? 'tiempo' : 'advertencias'"
      :auto-enviado="autoEnviado"
      :tiempo-restante="timerSeg"
      :duracion-total="quiz?.duracion_minutos * 60 || 0"
      :incidencias="[]"
    />

    <!-- ══ PANTALLA: Examen enviado ══ -->
    <div v-if="fase === 'enviado'" class="min-h-screen flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full p-10 text-center">
        <p class="text-6xl mb-4">✅</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Examen enviado!</h1>
        <p class="text-gray-500 text-sm mb-6">
          {{ autoEnviado ? 'El examen fue enviado automáticamente por tiempo agotado.' : 'Tu examen fue enviado correctamente.' }}
        </p>
        <div v-if="notaInmediata !== null" class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 mb-6">
          <p class="text-xs text-emerald-600 font-semibold mb-1">Tu nota</p>
          <p class="text-4xl font-bold text-emerald-700">{{ notaInmediata }}</p>
          <p class="text-sm text-gray-500 mt-1">de {{ notaMaxima }} puntos</p>
        </div>
        <p v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800 mb-6">
          ⏳ Tu examen requiere revisión del profesor. Recibirás una notificación cuando esté calificado.
        </p>
        <router-link to="/estudiante/quizzes"
          class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow">
          Volver a mis exámenes
        </router-link>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useProctoring } from '@/composables/useProctoring'
import { useExamenStore } from '@/stores/examen'

// Componentes visuales de alerta
import ProctoringWarningModal from '@/components/proctoring/ProctoringWarningModal.vue'
import ExamBlockedScreen from '@/components/proctoring/ExamBlockedScreen.vue'
import ExamTimerBar from '@/components/proctoring/ExamTimerBar.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const examenStore = useExamenStore()

const quizId = route.params.id
const attemptIdParam = route.query.attempt_id || null
let proctoringComposable = null

// ── Estado central ────────────────────────────────────────────────────────
const fase = ref('confirmacion')   // confirmacion | examen | enviado
const loading = ref(false)
const enviando = ref(false)
const autoSaving = ref(false)
const saveFailCount = ref(0)       // ✅ Contador de fallos de autosave
const quiz = ref(null)
const preguntas = ref([])
const intento = ref(null)
const respuestas = ref({})        // { [idx]: respuesta_obj }
const modalEnvio = ref(false)
const autoEnviado = ref(false)
const notaInmediata = ref(null)
const notaMaxima = ref(null)
const intentoActivo = ref(false)

// Refs de proctoring (sincronizadas con composable)
const advertencias = computed(() => proctoringComposable?.advertenciasCount.value || 0)
const modalAdvertencia = computed(() => proctoringComposable?.modal.value || false)
const mensajeAdvertencia = computed(() => proctoringComposable?.mensajeAdvertencia.value || '')

// ── Timer ─────────────────────────────────────────────────────────────────
const timerSeg = computed(() => examenStore.timerSeg)
let autoSaveInterval = null

const timerDisplay = computed(() => examenStore.timerDisplay)

const timerClase = computed(() => {
  if (examenStore.timerSeg <= 120) return 'bg-red-100 text-red-700 border border-red-300 animate-pulse'
  if (examenStore.timerSeg <= 300) return 'bg-orange-100 text-orange-700 border border-orange-300'
  return 'bg-indigo-50 text-indigo-700 border border-indigo-200'
})

// ── Computed progreso ─────────────────────────────────────────────────────
const totalPuntosQuiz = computed(() =>
  (quiz.value?.preguntas || []).reduce((s, p) => s + (p.puntos || 0), 0)
)

const respondidas = computed(() => {
  return preguntas.value.filter((_, idx) => preguntaRespondida(idx)).length
})

const progresoPorc = computed(() =>
  preguntas.value.length > 0 ? Math.round((respondidas.value / preguntas.value.length) * 100) : 0
)

// ── Helpers de tipos de pregunta ──────────────────────────────────────────
const iconoPorTipo = (tipo) => ({ examen: '📝', quiz: '❓', practica: '🧪' }[tipo] || '📄')

const labelTipo = (tipo) => ({
  seleccion: 'Selección múltiple',
  verdadero_falso: 'Verdadero / Falso',
  relacion_columnas: 'Relación de columnas',
  espacios: 'Llenado de espacios',
  multimedia: 'Multimedia',
}[tipo] || tipo)

const splitPlantilla = (plantilla) => {
  if (!plantilla) return []
  const partes = []
  let idx = 0, espacio = 0
  while (idx < plantilla.length) {
    const pos = plantilla.indexOf('__', idx)
    if (pos === -1) { partes.push(plantilla.slice(idx)); break }
    if (pos > idx) partes.push(plantilla.slice(idx, pos))
    partes.push({ espacio: espacio++ })
    idx = pos + 2
  }
  return partes
}

// ── Gestión de respuestas con localStorage ────────────────────────────────

/**
 * Obtener clave de localStorage para este intento
 */
const getLocalStorageKey = () => {
  return `quiz_attempt_${quizId}_${intento.value?.id || 'temp'}`
}

/**
 * Guardar respuestas en localStorage como backup
 */
const guardarEnLocalStorage = () => {
  if (!intento.value?.id) return
  
  try {
    const backup = {
      quiz_id: quizId,
      attempt_id: intento.value.id,
      respuestas: respuestas.value,
      timestamp: new Date().toISOString(),
      timer_segundos: timerSeg.value,
    }
    localStorage.setItem(getLocalStorageKey(), JSON.stringify(backup))
  } catch (error) {
    console.warn('[LocalStorage] Error guardando backup:', error)
  }
}

/**
 * Recuperar respuestas desde localStorage
 */
const recuperarDeLocalStorage = () => {
  if (!intento.value?.id) return false
  
  try {
    const key = getLocalStorageKey()
    const stored = localStorage.getItem(key)
    
    if (!stored) return false
    
    const backup = JSON.parse(stored)
    
    // Verificar que sea el mismo intento
    if (backup.attempt_id !== intento.value.id) {
      localStorage.removeItem(key)
      return false
    }
    
    // Restaurar respuestas
    respuestas.value = backup.respuestas || {}
    
    console.log('[LocalStorage] Respuestas recuperadas desde backup local')
    toast.info('Respuestas recuperadas desde almacenamiento local')
    
    return true
  } catch (error) {
    console.warn('[LocalStorage] Error recuperando backup:', error)
    return false
  }
}

/**
 * Limpiar localStorage después de enviar
 */
const limpiarLocalStorage = () => {
  try {
    const key = getLocalStorageKey()
    localStorage.removeItem(key)
  } catch (error) {
    console.warn('[LocalStorage] Error limpiando backup:', error)
  }
}

const setRespuesta = (idx, valor) => {
  respuestas.value[idx] = valor
  // ✅ Guardar en localStorage cada vez que cambia una respuesta
  guardarEnLocalStorage()
}

const esOpcionSeleccionada = (idx, opId) => {
  const r = respuestas.value[idx]
  if (!r?.seleccionadas) return false
  return r.seleccionadas.includes(opId)
}

const seleccionarOpcion = (idx, opId, multiple) => {
  const actual = respuestas.value[idx]?.seleccionadas || []
  if (!multiple) {
    setRespuesta(idx, { seleccionadas: [opId] })
  } else {
    const nuevo = actual.includes(opId)
      ? actual.filter(id => id !== opId)
      : [...actual, opId]
    setRespuesta(idx, { seleccionadas: nuevo })
  }
}

const getEspacio = (idx, numEspacio) => respuestas.value[idx]?.respuestas?.[numEspacio] || ''
const setEspacio = (idx, numEspacio, valor) => {
  const actual = respuestas.value[idx]?.respuestas || {}
  setRespuesta(idx, { respuestas: { ...actual, [numEspacio]: valor } })
}

const getPar = (idx, idB) => {
  const pares = respuestas.value[idx]?.pares || []
  return pares.find(p => p.b === idB)?.a || ''
}

const setPar = (idx, idB, idA, pregunta) => {
  const pares = [...(respuestas.value[idx]?.pares || [])]
  const i = pares.findIndex(p => p.b === idB)
  if (i >= 0) { if (idA) pares[i] = { a: idA, b: idB }; else pares.splice(i, 1) }
  else if (idA) pares.push({ a: idA, b: idB })
  setRespuesta(idx, { pares })
}

const preguntaRespondida = (idx) => {
  const r = respuestas.value[idx]
  if (!r) return false
  const p = preguntas.value[idx]
  switch (p?.tipo) {
    case 'seleccion': return (r.seleccionadas?.length || 0) > 0
    case 'verdadero_falso': return r.valor !== undefined && r.valor !== null
    case 'relacion_columnas': return (r.pares?.length || 0) === (p.contenido?.columna_a?.length || 0)
    case 'espacios': return Object.keys(r.respuestas || {}).length === (p.contenido?.espacios?.length || 0)
    case 'multimedia': return !!r.archivo_url
    default: return false
  }
}

// ── Subir archivo (multimedia) ────────────────────────────────────────────
const subirArchivo = async (idx, event, pregunta) => {
  const file = event.target.files[0]
  if (!file) return
  const maxMb = pregunta.contenido?.tamano_max_mb || 5
  if (file.size > maxMb * 1024 * 1024) {
    toast.error(`El archivo supera los ${maxMb}MB permitidos`)
    return
  }
  // TODO: Subir a /storage/answers/ (pendiente endpoint de upload)
  setRespuesta(idx, {
    archivo_url: URL.createObjectURL(file),
    nombre_archivo: file.name,
    tamano_bytes: file.size,
  })
}

// ── Scroll a pregunta ─────────────────────────────────────────────────────
const scrollAPregunta = (idx) => {
  document.getElementById(`pregunta-${idx}`)?.scrollIntoView({ behavior: 'smooth' })
}

// ── Proctoring ────────────────────────────────────────────────────────────

/**
 * Manejador para cuando se agota el tiempo del examen
 */
const handleTimerExpired = async (event) => {
  const { quizId: expiredQuizId } = event.detail
  if (expiredQuizId === quizId) {
    console.log('⏱️  Tiempo agotado → Auto-enviando examen')
    autoEnviado.value = true
    
    limpiarEventos()
    fase.value = 'enviado'
    
    // Enviar examen automáticamente
    await enviarExamen(true)
    
    // Navegar a resultado
    setTimeout(() => {
      router.push({
        name: 'EstudianteQuizResultado',
        params: { id: quizId },
      })
    }, 1500)
  }
}

/**
 * Manejador para cuando el composable de proctoring auto-envía el examen
 */
const handleExamAutoSubmitted = async (event) => {
  const { quizId: autoQuizId, attemptId } = event.detail
  if (autoQuizId === quizId) {
    autoEnviado.value = true
    limpiarEventos()
    fase.value = 'enviado'
    
    // Cerrar modal si está abierto
    if (modalAdvertencia.value) {
      if (proctoringComposable) proctoringComposable.cerrarModal()
    }
    
    // Navegar a resultado después de 2 segundos
    setTimeout(() => {
      router.push({
        name: 'EstudianteQuizResultado',
        params: { id: quizId },
      })
    }, 2000)
  }
}

const cerrarAdvertencia = () => {
  if (proctoringComposable) {
    proctoringComposable.cerrarModal()
  }
}

// ── Iniciar examen ────────────────────────────────────────────────────────
const iniciarExamen = async () => {
  loading.value = true
  try {
    const { data } = await api.post(`/estudiante/quizzes/${quizId}/iniciar`)
    intento.value = data.data

    // Mezclar columna_b si aplica
    preguntas.value = (quiz.value.preguntas || []).map(p => {
      if (p.tipo === 'relacion_columnas') {
        p.contenido.columna_b_mezclada = [...p.contenido.columna_b].sort(() => Math.random() - 0.5)
      }
      return p
    })

    fase.value = 'examen'
    intentoActivo.value = true

    // ✅ Intentar recuperar respuestas desde localStorage
    const recuperado = recuperarDeLocalStorage()
    if (recuperado) {
      console.log('[Recovery] Respuestas restauradas desde localStorage')
    }

    // Inicializar timer sincronizado con backend
    examenStore.setIntentoActual(intento.value)
    examenStore.setQuizActual(quiz.value)
    examenStore.iniciarTimer(quiz.value.duracion_minutos, api)

    // Listener para cuando se agota el tiempo
    window.addEventListener('exam-time-expired', handleTimerExpired)

    // Autosave cada 30 segundos
    autoSaveInterval = setInterval(() => autoGuardar(), 30000)

    // Usar composable useProctoring para detección anti-copia
    // El composable maneja automáticamente todos los listeners (visibilitychange, blur, fullscreenchange)
    // Y registra incidencias en backend, mostrando modal de advertencia
    proctoringComposable = useProctoring(quizId, intento.value.id, quiz.value.advertencias_max)

    // Escuchar evento de auto-submit del composable
    window.addEventListener('exam-auto-submitted', handleExamAutoSubmitted)

  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al iniciar el examen')
  } finally {
    loading.value = false
  }
}

// ── Autosave ──────────────────────────────────────────────────────────────
let lastSaveTime = Date.now()

const autoGuardar = async () => {
  if (!intento.value?.id || fase.value !== 'examen') return
  
  autoSaving.value = true
  const saveStartTime = Date.now()
  
  try {
    // 1. Guardar respuestas individuales
    const respuestasArray = Object.entries(respuestas.value)
    for (const [idxStr, respuesta] of respuestasArray) {
      const idx = parseInt(idxStr)
      const pregunta = preguntas.value[idx]
      if (!pregunta) continue
      
      await api.post(`/estudiante/quizzes/${quizId}/responder`, {
        attempt_id: intento.value.id,
        question_id: pregunta.id,
        respuesta,
      })
    }
    
    // 2. Sincronizar timer con backend
    await api.patch(`/quiz-attempts/${intento.value.id}`, {
      tiempo_restante_segundos: timerSeg.value,
      ultimo_ping: new Date().toISOString()
    })
    
    // ✅ Guardado exitoso
    lastSaveTime = saveStartTime
    saveFailCount.value = 0
    
  } catch (error) {
    saveFailCount.value++
    console.warn(`[AutoSave] Fallo #${saveFailCount.value}:`, error.message)
    
    // Si fallan 3 intentos consecutivos, mostrar advertencia al usuario
    if (saveFailCount.value >= 3) {
      toast.warning('Problemas de conexión. Tus respuestas se están guardando localmente.')
    }
  } finally { 
    autoSaving.value = false 
  }
}

// ── Enviar examen ─────────────────────────────────────────────────────────
const confirmarEnvio = () => { modalEnvio.value = true }

const enviarExamen = async (autoSubmit = false) => {
  if (enviando.value) return
  enviando.value = true
  modalEnvio.value = false
  limpiarEventos()

  try {
    // Guardar todas las respuestas pendientes antes de enviar
    await autoGuardar()

    const { data } = await api.post(`/estudiante/quizzes/${quizId}/enviar`, {
      attempt_id: intento.value.id,
    })

    const intentoFinal = data.data
    if (quiz.value.mostrar_resultado_inmediato && intentoFinal.nota_obtenida !== null) {
      notaInmediata.value = intentoFinal.nota_obtenida
      notaMaxima.value = intentoFinal.nota_maxima
    }

    // ✅ Limpiar localStorage después de envío exitoso
    limpiarLocalStorage()
    console.log('[LocalStorage] Backup eliminado después de envío exitoso')

    fase.value = 'enviado'
    intentoActivo.value = false
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al enviar el examen')
    enviando.value = false
  }
}

// ── Limpieza ──────────────────────────────────────────────────────────────
const limpiarEventos = () => {
  // Detener timer del store
  examenStore.detenerTimer()
  
  // Limpiar composable de proctoring
  if (proctoringComposable) {
    proctoringComposable.limpiar()
  }
  
  // Remover listeners de eventos
  window.removeEventListener('exam-time-expired', handleTimerExpired)
  window.removeEventListener('exam-auto-submitted', handleExamAutoSubmitted)
  
  // Limpiar intervals
  if (autoSaveInterval) clearInterval(autoSaveInterval)
  
  intentoActivo.value = false
}

onUnmounted(() => {
  limpiarEventos()
  window.removeEventListener('beforeunload', onBeforeUnload)
  
  // ✅ NO limpiar localStorage al desmontar, solo al enviar exitosamente
  // Esto permite recuperar respuestas si el usuario recarga accidentalmente
})

// ── Prevenir salida accidental ────────────────────────────────────────────
const onBeforeUnload = (e) => {
  if (fase.value !== 'examen') return
  e.preventDefault()
  e.returnValue = '¿Seguro que quieres salir? Tu progreso puede perderse.'
}

// ── Cargar quiz al montar ─────────────────────────────────────────────────
onMounted(async () => {
  window.addEventListener('beforeunload', onBeforeUnload)
  try {
    const { data } = await api.get(`/estudiante/quizzes/${quizId}`)
    quiz.value = data.data?.quiz || data.data

    // Si hay intento en progreso (retomar)
    if (attemptIdParam && data.data?.intento_actual) {
      intento.value = data.data.intento_actual

      preguntas.value = (quiz.value.preguntas || []).map(p => {
        if (p.tipo === 'relacion_columnas') {
          p.contenido.columna_b_mezclada = [...p.contenido.columna_b].sort(() => Math.random() - 0.5)
        }
        return p
      })

      fase.value = 'examen'
      intentoActivo.value = true

      // Inicializar timer sincronizado con el tiempo restante
      examenStore.setIntentoActual(intento.value)
      examenStore.setQuizActual(quiz.value)
      examenStore.restablecerDesdeBackend(intento.value.tiempo_restante_seg || quiz.value.duracion_minutos * 60)
      examenStore.iniciarTimer(quiz.value.duracion_minutos, api)

      // Listener para cuando se agota el tiempo
      window.addEventListener('exam-time-expired', handleTimerExpired)

      autoSaveInterval = setInterval(() => autoGuardar(), 30000)

      // Usar composable useProctoring
      proctoringComposable = useProctoring(quizId, intento.value.id, quiz.value.advertencias_max)
      window.addEventListener('exam-auto-submitted', handleExamAutoSubmitted)
    }
  } catch (e) {
    toast.error('Error al cargar el examen')
    router.push('/estudiante/quizzes')
  }
})

onUnmounted(() => {
  limpiarEventos()
  window.removeEventListener('beforeunload', onBeforeUnload)
})
</script>
