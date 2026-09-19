<template>
  <AdminLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div class="h-32 bg-gray-100 rounded-2xl animate-pulse" />
      <div class="h-64 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <template v-else-if="intento">
      <!-- Encabezado -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Resultado del Examen</h1>
          <p class="text-gray-500 mt-1">{{ intento.quiz?.titulo }}</p>
        </div>
        <router-link to="/estudiante/quizzes"
          class="text-gray-400 hover:text-gray-600 text-sm px-3 py-2 transition">
          ← Volver a mis exámenes
        </router-link>
      </div>

      <!-- Tarjeta de nota principal -->
      <div class="bg-white rounded-2xl border shadow-sm p-8 mb-6 flex flex-col md:flex-row items-center gap-8">
        <!-- Nota circular -->
        <div class="flex-shrink-0 relative w-36 h-36">
          <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
            <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="10"/>
            <circle cx="60" cy="60" r="52" fill="none"
              :stroke="colorCirculo" stroke-width="10"
              stroke-linecap="round"
              :stroke-dasharray="`${circunferencia}`"
              :stroke-dashoffset="dashOffset"
              style="transition: stroke-dashoffset 1s ease" />
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <p class="text-3xl font-bold" :class="colorTextoNota">{{ intento.nota_obtenida }}</p>
            <p class="text-xs text-gray-400">de {{ intento.nota_maxima }}</p>
          </div>
        </div>

        <!-- Info de la nota -->
        <div class="flex-1 text-center md:text-left">
          <p class="text-2xl font-bold mb-1" :class="colorTextoNota">{{ labelRendimiento }}</p>
          <p class="text-gray-500 text-sm mb-4">{{ mensajeRendimiento }}</p>

          <div class="flex flex-wrap gap-4 justify-center md:justify-start text-sm text-gray-600">
            <div class="flex items-center gap-1.5">
              <span class="text-green-500 font-bold">✓</span>
              <span>{{ respuestasCorrectas }} correctas</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="text-red-500 font-bold">✗</span>
              <span>{{ respuestasIncorrectas }} incorrectas</span>
            </div>
            <div v-if="respuestasPendientes > 0" class="flex items-center gap-1.5">
              <span class="text-gray-400">⏳</span>
              <span>{{ respuestasPendientes }} pendiente(s)</span>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-400">
            <span>📅 {{ formatFecha(intento.inicio_at) }}</span>
            <span>⏱ {{ duracion(intento.inicio_at, intento.fin_at) }}</span>
            <span v-if="intento.auto_enviado" class="text-orange-500 font-semibold">⚡ Auto-enviado</span>
            <span v-if="intento.advertencias_count > 0" class="text-red-500 font-semibold">
              ⚠️ {{ intento.advertencias_count }} advertencia(s)
            </span>
          </div>
        </div>
      </div>

      <!-- Alerta: resultado no disponible aún -->
      <div v-if="intento.estado === 'enviado'"
        class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 mb-6 flex items-center gap-3">
        <span class="text-3xl">⏳</span>
        <div>
          <p class="font-semibold text-yellow-800">El profesor aún no ha publicado la nota final</p>
          <p class="text-sm text-yellow-700 mt-0.5">Recibirás una notificación cuando esté calificado.</p>
        </div>
      </div>

      <!-- Detalle por pregunta (solo si mostrar_resultado_inmediato=true y calificado) -->
      <div v-if="intento.estado === 'calificado' && intento.quiz?.mostrar_resultado_inmediato" class="space-y-4">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Revisión de Respuestas</h2>

        <div v-for="(resp, idx) in intento.respuestas" :key="resp.id"
          class="bg-white rounded-2xl border shadow-sm p-6"
          :class="{
            'border-green-200 bg-green-50/30': resp.es_correcta === true,
            'border-red-200 bg-red-50/30': resp.es_correcta === false,
            'border-gray-200': resp.es_correcta === null
          }">

          <!-- Cabecera -->
          <div class="flex items-start gap-3 mb-4">
            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
              :class="{
                'bg-green-100 text-green-700': resp.es_correcta === true,
                'bg-red-100 text-red-700': resp.es_correcta === false,
                'bg-gray-100 text-gray-500': resp.es_correcta === null
              }">
              {{ resp.es_correcta === true ? '✓' : resp.es_correcta === false ? '✗' : '?' }}
            </span>
            <div class="flex-1">
              <p class="text-xs text-gray-400 mb-1">
                Pregunta {{ idx + 1 }} · {{ labelTipo(resp.pregunta?.tipo) }}
              </p>
              <p class="font-semibold text-gray-900">{{ resp.pregunta?.enunciado }}</p>
            </div>
            <div class="text-right flex-shrink-0">
              <p class="font-bold text-sm"
                :class="{
                  'text-green-700': resp.es_correcta === true,
                  'text-red-700': resp.es_correcta === false,
                  'text-gray-500': resp.es_correcta === null
                }">
                {{ resp.puntos_obtenidos ?? '—' }} / {{ resp.pregunta?.puntos }} pts
              </p>
            </div>
          </div>

          <!-- Tu respuesta -->
          <div class="ml-11 space-y-3">
            <div class="bg-white rounded-xl border border-gray-200 p-3">
              <p class="text-xs font-semibold text-gray-500 mb-1">Tu respuesta:</p>
              <!-- Selección -->
              <div v-if="resp.pregunta?.tipo === 'seleccion'" class="space-y-1">
                <div v-for="opId in (resp.respuesta?.seleccionadas || [])" :key="opId"
                  class="text-sm text-gray-800">
                  ● {{ getTextoOpcion(resp.pregunta, opId) }}
                </div>
                <p v-if="!resp.respuesta?.seleccionadas?.length" class="text-sm text-gray-400 italic">Sin respuesta</p>
              </div>
              <!-- V/F -->
              <div v-else-if="resp.pregunta?.tipo === 'verdadero_falso'">
                <p class="text-sm font-semibold"
                  :class="resp.respuesta?.valor === true ? 'text-green-700' : 'text-red-700'">
                  {{ resp.respuesta?.valor === true ? '✓ Verdadero' : resp.respuesta?.valor === false ? '✗ Falso' : '—' }}
                </p>
                <p v-if="resp.respuesta?.justificacion" class="text-sm text-gray-700 mt-2 italic">
                  "{{ resp.respuesta.justificacion }}"
                </p>
              </div>
              <!-- Relación -->
              <div v-else-if="resp.pregunta?.tipo === 'relacion_columnas'" class="space-y-1">
                <div v-for="par in (resp.respuesta?.pares || [])" :key="par.a + par.b"
                  class="text-sm text-gray-700 flex items-center gap-2">
                  <span class="font-medium">{{ getTextoColumnaA(resp.pregunta, par.a) }}</span>
                  <span class="text-gray-400">↔</span>
                  <span>{{ getTextoColumnaB(resp.pregunta, par.b) }}</span>
                </div>
              </div>
              <!-- Espacios -->
              <div v-else-if="resp.pregunta?.tipo === 'espacios'" class="text-sm text-gray-700">
                <span v-for="(parte, pi) in splitPlantilla(resp.pregunta?.contenido?.plantilla)" :key="pi">
                  <span v-if="typeof parte === 'string'">{{ parte }}</span>
                  <strong v-else class="text-indigo-700 underline">
                    {{ resp.respuesta?.respuestas?.[parte.espacio + 1] || '___' }}
                  </strong>
                </span>
              </div>
              <!-- Multimedia -->
              <div v-else-if="resp.pregunta?.tipo === 'multimedia'">
                <a v-if="resp.respuesta?.archivo_url" :href="resp.respuesta.archivo_url" target="_blank"
                  class="text-sm text-indigo-600 font-semibold hover:underline">
                  📎 {{ resp.respuesta.nombre_archivo || 'Ver archivo entregado' }}
                </a>
                <p v-else class="text-sm text-gray-400 italic">Sin entrega</p>
              </div>
            </div>

            <!-- Respuesta correcta (solo si es incorrecta) -->
            <div v-if="resp.es_correcta === false" class="bg-green-50 border border-green-200 rounded-xl p-3">
              <p class="text-xs font-semibold text-green-600 mb-1">Respuesta correcta:</p>
              <RespuestaCorrecta :pregunta="resp.pregunta" />
            </div>

            <!-- Retroalimentación del profesor -->
            <div v-if="resp.pregunta?.retroalimentacion"
              class="bg-indigo-50 border border-indigo-200 rounded-xl p-3">
              <p class="text-xs font-semibold text-indigo-600 mb-1">💬 Retroalimentación:</p>
              <p class="text-sm text-indigo-900">{{ resp.pregunta.retroalimentacion }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje si no muestra resultado inmediato -->
      <div v-else-if="intento.estado === 'calificado' && !intento.quiz?.mostrar_resultado_inmediato"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-gray-400">
        <p class="text-4xl mb-3">🔒</p>
        <p class="font-semibold text-gray-600">El profesor no ha habilitado la revisión de respuestas</p>
        <p class="text-sm mt-2">Solo puedes ver tu nota final.</p>
      </div>
    </template>

    <!-- Error: intento no encontrado o no calificado -->
    <div v-else class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center text-gray-400">
      <p class="text-4xl mb-3">⏳</p>
      <p class="font-semibold text-gray-600">Resultado no disponible aún</p>
      <p class="text-sm mt-2 mb-6">El examen está en proceso de calificación.</p>
      <router-link to="/estudiante/quizzes"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
        Volver a mis exámenes
      </router-link>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

// Componente inline para respuesta correcta por tipo
const RespuestaCorrecta = {
  props: ['pregunta'],
  setup(props) {
    const texto = computed(() => {
      const c = props.pregunta?.contenido
      if (!c) return '—'
      switch (props.pregunta?.tipo) {
        case 'seleccion': {
          const correctas = (c.opciones || []).filter(o => o.es_correcta).map(o => o.texto)
          return correctas.join(', ') || '—'
        }
        case 'verdadero_falso':
          return c.respuesta_correcta ? '✓ Verdadero' : '✗ Falso'
        case 'relacion_columnas': {
          return (c.pares_correctos || []).map(par => {
            const a = (c.columna_a || []).find(i => i.id === par.a)?.texto || par.a
            const b = (c.columna_b || []).find(i => i.id === par.b)?.texto || par.b
            return `${a} ↔ ${b}`
          }).join(' · ')
        }
        case 'espacios': {
          return (c.espacios || []).map(e =>
            `[${e.id}]: ${e.respuestas_validas?.join(' / ') || '—'}`
          ).join(' · ')
        }
        default: return 'Ver calificación del profesor'
      }
    })
    return () => h('p', { class: 'text-sm text-green-800 font-medium' }, texto.value)
  }
}

import { h } from 'vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const quizId = route.params.id
const loading = ref(true)
const intento = ref(null)

// ── Computed ──────────────────────────────────────────────────────────────
const porcentajeNota = computed(() => {
  if (!intento.value?.nota_maxima) return 0
  return Math.round((intento.value.nota_obtenida / intento.value.nota_maxima) * 100)
})

const circunferencia = computed(() => 2 * Math.PI * 52)
const dashOffset = computed(() => circunferencia.value - (porcentajeNota.value / 100) * circunferencia.value)

const colorCirculo = computed(() => {
  const p = porcentajeNota.value
  if (p >= 70) return '#10b981'
  if (p >= 50) return '#f59e0b'
  return '#ef4444'
})

const colorTextoNota = computed(() => {
  const p = porcentajeNota.value
  if (p >= 70) return 'text-green-700'
  if (p >= 50) return 'text-yellow-700'
  return 'text-red-700'
})

const labelRendimiento = computed(() => {
  const p = porcentajeNota.value
  if (p >= 90) return '🏆 Excelente'
  if (p >= 70) return '✅ Aprobado'
  if (p >= 50) return '⚠️ Suficiente'
  return '❌ Reprobado'
})

const mensajeRendimiento = computed(() => {
  const p = porcentajeNota.value
  if (p >= 90) return 'Dominas perfectamente el tema.'
  if (p >= 70) return 'Buen desempeño. Puedes repasar los temas fallados.'
  if (p >= 50) return 'Aprobado con margen justo. Te recomendamos repasar.'
  return 'No alcanzaste la nota mínima. Consulta con tu profesor.'
})

const respuestasCorrectas = computed(() =>
  (intento.value?.respuestas || []).filter(r => r.es_correcta === true).length
)
const respuestasIncorrectas = computed(() =>
  (intento.value?.respuestas || []).filter(r => r.es_correcta === false).length
)
const respuestasPendientes = computed(() =>
  (intento.value?.respuestas || []).filter(r => r.es_correcta === null).length
)

// ── Helpers ───────────────────────────────────────────────────────────────
const labelTipo = (tipo) => ({
  seleccion: 'Selección múltiple',
  verdadero_falso: 'Verdadero / Falso',
  relacion_columnas: 'Relación de columnas',
  espacios: 'Llenado de espacios',
  multimedia: 'Multimedia',
}[tipo] || tipo)

const formatFecha = (fecha) => fecha
  ? new Date(fecha).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
  : '—'

const duracion = (inicio, fin) => {
  if (!inicio || !fin) return '—'
  const diff = Math.floor((new Date(fin) - new Date(inicio)) / 1000)
  const m = Math.floor(diff / 60)
  const s = diff % 60
  return `${m}m ${s}s`
}

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

const getTextoOpcion = (pregunta, opId) =>
  (pregunta?.contenido?.opciones || []).find(o => o.id === opId)?.texto || opId

const getTextoColumnaA = (pregunta, id) =>
  (pregunta?.contenido?.columna_a || []).find(i => i.id === id)?.texto || id

const getTextoColumnaB = (pregunta, id) =>
  (pregunta?.contenido?.columna_b || []).find(i => i.id === id)?.texto || id

// ── Cargar resultado ──────────────────────────────────────────────────────
onMounted(async () => {
  try {
    // Buscar el intento calificado más reciente para este quiz
    const quizzesRes = await api.get('/estudiante/quizzes')
    const quiz = (quizzesRes.data.data || []).find(q => q.id == quizId)

    if (!quiz) { router.push('/estudiante/quizzes'); return }

    // Obtener el intento calificado
    const intentos = await api.get(`/estudiante/quizzes/${quizId}`)
    const intentoId = intentos.data.data?.intento_actual?.id
      || route.query.attempt_id

    if (!intentoId) {
      loading.value = false
      return
    }

    const { data } = await api.get(`/estudiante/quiz-attempts/${intentoId}/resultado`)
    intento.value = data.data
  } catch (e) {
    // Si el intento no está calificado aún, mostrar pantalla de espera
    if (e.response?.status !== 422) {
      toast.error('Error al cargar el resultado')
    }
  } finally {
    loading.value = false
  }
})
</script>
