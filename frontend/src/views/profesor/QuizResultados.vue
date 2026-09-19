
<template>
  <AdminLayout>
    <!-- Encabezado -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Resultados del Examen</h1>
        <p class="text-gray-500 mt-1">{{ quiz?.titulo }}</p>
      </div>
      <div class="flex gap-2">
        <router-link :to="`/profesor/quizzes/${quizId}/editar`"
          class="border border-gray-300 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl hover:border-gray-400 transition">
          ✏️ Editar
        </router-link>
        <router-link to="/profesor/quizzes"
          class="text-gray-400 hover:text-gray-600 text-sm px-3 py-2">← Volver</router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div v-for="n in 4" :key="n" class="h-24 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <template v-else>
      <!-- ── Estadísticas ── -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-3xl font-bold text-emerald-700">{{ stats.total_intentos ?? 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">Total intentos</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-3xl font-bold text-blue-700">{{ stats.promedio ?? 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">Promedio</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-3xl font-bold text-purple-700">{{ stats.maximo ?? 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">Nota máxima</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-3xl font-bold text-orange-700">{{ pendientesCalificar }}</p>
          <p class="text-sm text-gray-500 mt-1">Pendientes revisión</p>
        </div>
      </div>

      <!-- ── Distribución de notas ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Distribución de Notas</h3>
        <div class="flex items-end gap-3 h-24">
          <div v-for="(rango, label) in distribucion" :key="label" class="flex-1 flex flex-col items-center gap-1">
            <p class="text-xs font-bold text-gray-600">{{ rango }}</p>
            <div class="w-full rounded-t-lg bg-emerald-400 transition-all" :style="{ height: alturaBarraDistribucion(rango) + 'px' }" />
            <p class="text-xs text-gray-500">{{ label }}</p>
          </div>
        </div>
      </div>

      <!-- ── Tabla de intentos ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Filtros -->
        <div class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center">
          <h3 class="font-semibold text-gray-900 flex-1">Intentos ({{ intentosFiltrados.length }})</h3>
          <select v-model="filtroEstado" class="border border-gray-200 rounded-xl px-3 py-1.5 text-sm">
            <option value="">Todos los estados</option>
            <option value="en_progreso">En progreso</option>
            <option value="enviado">Enviado (pendiente)</option>
            <option value="calificado">Calificado</option>
            <option value="anulado">Anulado</option>
          </select>
          <input v-model="busqueda" type="text" placeholder="Buscar estudiante..."
            class="border border-gray-200 rounded-xl px-3 py-1.5 text-sm w-48" />
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-left">
                <th class="px-4 py-3 font-semibold text-gray-600">Estudiante</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Estado</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Nota</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Advertencias</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Inicio</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Duración</th>
                <th class="px-4 py-3 font-semibold text-gray-600">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="intentosFiltrados.length === 0">
                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                  <p class="text-3xl mb-2">📋</p>
                  <p>Sin intentos registrados</p>
                </td>
              </tr>
              <tr v-for="intento in intentosFiltrados" :key="intento.id"
                class="border-t border-gray-50 hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                  <p class="font-semibold text-gray-900">{{ intento.estudiante?.nombre }} {{ intento.estudiante?.apellido }}</p>
                  <p class="text-xs text-gray-400">{{ intento.estudiante?.cedula }}</p>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs font-bold px-2.5 py-1 rounded-full" :class="badgeEstado(intento.estado)">
                    {{ labelEstado(intento.estado) }}
                  </span>
                  <span v-if="intento.auto_enviado" class="ml-1 text-xs text-orange-600 font-semibold">⚡ Auto</span>
                </td>
                <td class="px-4 py-3 font-bold" :class="colorNota(intento.nota_obtenida, intento.nota_maxima)">
                  {{ intento.nota_obtenida ?? '—' }} / {{ intento.nota_maxima }}
                </td>
                <td class="px-4 py-3">
                  <span v-if="intento.advertencias_count > 0"
                    class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                    ⚠️ {{ intento.advertencias_count }}
                  </span>
                  <span v-else class="text-gray-300 text-xs">—</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ formatFecha(intento.inicio_at) }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ duracion(intento.inicio_at, intento.fin_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex gap-1.5">
                    <button @click="verIntento(intento)" title="Ver detalle"
                      class="text-xs px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-semibold">
                      👁 Ver
                    </button>
                    <button v-if="intento.estado === 'enviado'" @click="publicarNota(intento)"
                      class="text-xs px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition font-semibold">
                      ✓ Publicar
                    </button>
                    <button v-if="intento.estado !== 'anulado'" @click="confirmarAnular(intento)"
                      class="text-xs px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition font-semibold">
                      🚫
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Modal: Detalle de Intento ── -->
      <div v-if="intentoDetalle" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="intentoDetalle = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">

          <!-- Cabecera modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl">
            <div>
              <h3 class="font-bold text-gray-900">
                {{ intentoDetalle.estudiante?.nombre }} {{ intentoDetalle.estudiante?.apellido }}
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ formatFecha(intentoDetalle.inicio_at) }} · {{ duracion(intentoDetalle.inicio_at, intentoDetalle.fin_at) }}
                <span v-if="intentoDetalle.auto_enviado" class="text-orange-600 font-bold ml-2">⚡ Auto-enviado</span>
              </p>
            </div>
            <div class="flex items-center gap-3">
              <p class="text-lg font-bold" :class="colorNota(intentoDetalle.nota_obtenida, intentoDetalle.nota_maxima)">
                {{ intentoDetalle.nota_obtenida ?? '—' }} / {{ intentoDetalle.nota_maxima }}
              </p>
              <button @click="intentoDetalle = null" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>
          </div>

          <!-- Incidencias -->
          <div v-if="intentoDetalle.incidencias?.length" class="mx-6 mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-xs font-bold text-red-700 mb-2">⚠️ Incidencias detectadas ({{ intentoDetalle.incidencias.length }})</p>
            <div class="space-y-1">
              <div v-for="inc in intentoDetalle.incidencias.slice(0, 5)" :key="inc.id"
                class="flex items-center gap-2 text-xs text-red-600">
                <span>{{ labelTipoIncidencia(inc.tipo) }}</span>
                <span class="text-red-400">{{ formatFecha(inc.ocurrido_at) }}</span>
              </div>
              <p v-if="intentoDetalle.incidencias.length > 5" class="text-xs text-red-400">
                + {{ intentoDetalle.incidencias.length - 5 }} más...
              </p>
            </div>
          </div>

          <!-- Respuestas -->
          <div class="p-6 space-y-4">
            <div v-for="(resp, ri) in intentoDetalle.respuestas" :key="ri"
              class="rounded-xl border p-4"
              :class="resp.es_correcta === null ? 'border-gray-200 bg-gray-50' : resp.es_correcta ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">

              <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                  <p class="text-xs font-semibold text-gray-500 mb-1">
                    {{ ri + 1 }}. {{ getTipoPreguntaLabel(resp.pregunta?.tipo) }}
                  </p>
                  <p class="text-sm font-semibold text-gray-900">{{ resp.pregunta?.enunciado }}</p>
                  <p class="text-xs text-gray-500 mt-1">Respuesta: {{ JSON.stringify(resp.respuesta) }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm font-bold"
                    :class="resp.es_correcta === null ? 'text-gray-500' : resp.es_correcta ? 'text-green-700' : 'text-red-700'">
                    {{ resp.es_correcta === null ? '—' : resp.es_correcta ? '✓' : '✗' }}
                  </p>
                  <p class="text-xs text-gray-500">{{ resp.puntos_obtenidos ?? '?' }} / {{ resp.pregunta?.puntos }}</p>

                  <!-- Calificación manual para multimedia/V/F con justificación -->
                  <div v-if="resp.es_correcta === null && resp.pregunta?.tipo === 'multimedia'" class="mt-2 space-y-1">
                    <input v-model.number="resp._puntosManual" type="number" :min="0" :max="resp.pregunta?.puntos" step="0.5"
                      class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-xs text-center" placeholder="Pts" />
                    <div class="flex gap-1">
                      <button @click="calificarManual(resp, true)" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded font-semibold">✓</button>
                      <button @click="calificarManual(resp, false)" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded font-semibold">✗</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Acciones del modal -->
          <div class="p-6 pt-0 flex gap-3">
            <button v-if="intentoDetalle.estado === 'enviado'" @click="publicarNota(intentoDetalle)"
              class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">
              ✓ Publicar Nota Final
            </button>
            <button @click="intentoDetalle = null"
              class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-xl hover:border-gray-400 transition text-sm">
              Cerrar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal: Anular -->
      <div v-if="intentoAnular" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="intentoAnular = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
          <h3 class="font-bold text-gray-900 mb-2">Anular intento</h3>
          <p class="text-sm text-gray-600 mb-4">
            ¿Anular el intento de <strong>{{ intentoAnular.estudiante?.nombre }}</strong>? Ingresa una razón:
          </p>
          <textarea v-model="razonAnular" rows="3" placeholder="Razón de la anulación..."
            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-red-400 mb-4" />
          <div class="flex gap-3">
            <button @click="ejecutarAnular" :disabled="!razonAnular.trim()"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-40 text-white font-semibold py-2.5 rounded-xl transition text-sm">
              Confirmar Anulación
            </button>
            <button @click="intentoAnular = null"
              class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-xl hover:border-gray-400 transition text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </template>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const toast = useToast()
const quizId = route.params.id

// ── Estado ────────────────────────────────────────────────────────────────
const loading = ref(true)
const quiz = ref(null)
const intentos = ref([])
const stats = ref({})
const distribucion = ref({})
const filtroEstado = ref('')
const busqueda = ref('')
const intentoDetalle = ref(null)
const intentoAnular = ref(null)
const razonAnular = ref('')

// ── Computed ──────────────────────────────────────────────────────────────
const pendientesCalificar = computed(() =>
  intentos.value.filter(i => i.estado === 'enviado').length
)

const intentosFiltrados = computed(() => {
  let arr = intentos.value
  if (filtroEstado.value) arr = arr.filter(i => i.estado === filtroEstado.value)
  if (busqueda.value.trim()) {
    const q = busqueda.value.toLowerCase()
    arr = arr.filter(i =>
      `${i.estudiante?.nombre} ${i.estudiante?.apellido} ${i.estudiante?.cedula}`.toLowerCase().includes(q)
    )
  }
  return arr
})

// ── Helpers de UI ─────────────────────────────────────────────────────────
const badgeEstado = (estado) => ({
  en_progreso: 'bg-blue-100 text-blue-700',
  enviado: 'bg-yellow-100 text-yellow-700',
  calificado: 'bg-green-100 text-green-700',
  anulado: 'bg-red-100 text-red-600',
}[estado] || 'bg-gray-100 text-gray-600')

const labelEstado = (estado) => ({
  en_progreso: 'En progreso',
  enviado: 'Pendiente',
  calificado: 'Calificado',
  anulado: 'Anulado',
}[estado] || estado)

const colorNota = (nota, max) => {
  if (nota === null || nota === undefined) return 'text-gray-400'
  const pct = nota / max
  if (pct >= 0.7) return 'text-green-700'
  if (pct >= 0.5) return 'text-yellow-700'
  return 'text-red-700'
}

const labelTipoIncidencia = (tipo) => ({
  cambio_pestana: '📑 Cambio de pestaña',
  perdida_foco: '👁 Pérdida de foco',
  pantalla_completa: '🖥 Pantalla completa',
  advertencia_enviada: '⚠️ Advertencia enviada',
  auto_submit: '⚡ Auto-envío',
}[tipo] || tipo)

const getTipoPreguntaLabel = (tipo) => ({
  seleccion: 'Selección',
  verdadero_falso: 'V/F',
  relacion_columnas: 'Relación',
  espacios: 'Espacios',
  multimedia: 'Multimedia',
}[tipo] || tipo)

const formatFecha = (fecha) => {
  if (!fecha) return '—'
  return new Date(fecha).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const duracion = (inicio, fin) => {
  if (!inicio || !fin) return '—'
  const diff = Math.floor((new Date(fin) - new Date(inicio)) / 1000)
  const m = Math.floor(diff / 60)
  const s = diff % 60
  return `${m}m ${s}s`
}

const alturaBarraDistribucion = (count) => {
  const max = Math.max(...Object.values(distribucion.value), 1)
  return Math.round((count / max) * 80)
}

// ── API ───────────────────────────────────────────────────────────────────
const cargarDatos = async () => {
  loading.value = true
  try {
    const [quizRes, resultRes, statsRes] = await Promise.all([
      api.get(`/profesor/quizzes/${quizId}`),
      api.get(`/profesor/quizzes/${quizId}/resultados`),
      api.get(`/profesor/quizzes/${quizId}/estadisticas`),
    ])
    quiz.value = quizRes.data.data
    intentos.value = resultRes.data.data?.intentos || []
    stats.value = statsRes.data.data?.resumen || {}
    distribucion.value = statsRes.data.data?.distribucion_notas || {}
  } catch (e) {
    toast.error('Error al cargar datos')
  } finally {
    loading.value = false
  }
}

const verIntento = async (intento) => {
  try {
    const { data } = await api.get(`/profesor/quiz-attempts/${intento.id}`)
    intentoDetalle.value = data.data
  } catch (e) {
    toast.error('Error al cargar detalle del intento')
  }
}

const publicarNota = async (intento) => {
  try {
    await api.post(`/profesor/quiz-attempts/${intento.id}/publicar-nota`)
    toast.success('Nota publicada correctamente')
    await cargarDatos()
    if (intentoDetalle.value?.id === intento.id) intentoDetalle.value = null
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al publicar nota')
  }
}

const calificarManual = async (respuesta, esCorrecta) => {
  const puntos = respuesta._puntosManual ?? 0
  try {
    await api.put(`/profesor/quiz-answers/${respuesta.id}/calificar`, {
      es_correcta: esCorrecta,
      puntos_obtenidos: puntos,
    })
    respuesta.es_correcta = esCorrecta
    respuesta.puntos_obtenidos = puntos
    toast.success('Respuesta calificada')
  } catch (e) {
    toast.error('Error al calificar')
  }
}

const confirmarAnular = (intento) => {
  intentoAnular.value = intento
  razonAnular.value = ''
}

const ejecutarAnular = async () => {
  if (!razonAnular.value.trim()) return
  try {
    await api.post(`/profesor/quiz-attempts/${intentoAnular.value.id}/anular`, { razon: razonAnular.value })
    toast.success('Intento anulado')
    intentoAnular.value = null
    await cargarDatos()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al anular')
  }
}

onMounted(() => cargarDatos())
</script>
