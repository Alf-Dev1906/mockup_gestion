<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Calificaciones</h1>
      <p class="text-gray-500 mt-1">Administra las calificaciones de los estudiantes</p>
    </div>

    <!-- Barra de acciones -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 w-full sm:max-w-md">
          <input
            v-model="filtros.buscar"
            @input="cargar(1)"
            type="search"
            placeholder="Buscar estudiante..."
            class="w-full py-2.5 px-4 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <select v-model="filtros.periodo_academico" @change="cargar(1)"
            class="flex-1 sm:flex-none py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los periodos</option>
            <option value="2026-1">2026-1</option>
            <option value="2025-3">2025-3</option>
            <option value="2025-2">2025-2</option>
          </select>

          <button @click="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap">
            <span>➕</span>
            <span>Nueva Calificación</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Estudiante</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Materia</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Periodo</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Nota 1</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Nota 2</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Nota 3</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Final</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 5" :key="n">
              <td v-for="m in 9" :key="m" class="px-6 py-4">
                <div class="h-4 bg-gray-100 rounded animate-pulse"></div>
              </td>
            </tr>
          </tbody>
          <tbody v-else-if="calificaciones.length === 0">
            <tr>
              <td colspan="9" class="px-6 py-16 text-center text-gray-500">
                <p class="text-4xl mb-2">📊</p>
                <p>No se encontraron calificaciones</p>
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-for="c in calificaciones" :key="c.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ c.inscripcion?.estudiante?.nombre }} {{ c.inscripcion?.estudiante?.apellido }}</p>
                  <p class="text-xs text-gray-600">{{ c.inscripcion?.estudiante?.email }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ c.inscripcion?.horario?.materia?.nombre || '—' }}</p>
                  <p class="text-xs text-gray-600">{{ c.inscripcion?.horario?.materia?.codigo }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-block bg-purple-100 text-purple-700 px-2 py-1 rounded-lg text-xs font-semibold">
                  {{ c.inscripcion?.horario?.periodo_academico }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold text-gray-900">{{ c.nota_1 ?? '—' }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold text-gray-900">{{ c.nota_2 ?? '—' }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold text-gray-900">{{ c.nota_3 ?? '—' }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-bold text-lg" :class="getNotaColor(c.nota_final)">
                  {{ c.nota_final !== null ? parseFloat(c.nota_final).toFixed(2) : '—' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span v-if="c.nota_final !== null" :class="getEstadoBadge(c.nota_final)" class="inline-block px-3 py-1 rounded-full text-xs font-semibold">
                  {{ parseFloat(c.nota_final) >= 10 ? 'Aprobado' : 'Reprobado' }}
                </span>
                <span v-else class="text-gray-400 text-xs">Pendiente</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="abrirModal(c)" class="text-blue-600 hover:text-blue-800 p-2">✏️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="pagination.last_page > 1" class="border-t border-gray-100 px-6 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
          Mostrando {{ pagination.from }} - {{ pagination.to }} de {{ pagination.total }}
        </p>
        <div class="flex gap-2">
          <button
            v-for="page in pages"
            :key="page"
            @click="cargar(page)"
            :disabled="page === pagination.current_page"
            :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-4 py-2 rounded-lg font-semibold transition disabled:opacity-50">
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900">
            {{ form.id ? 'Editar Calificación' : 'Nueva Calificación' }}
          </h2>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>

        <form @submit.prevent="guardar" class="p-6 space-y-6">
          <div v-if="!form.id">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar Inscripción *</label>
            <div class="relative">
              <input
                v-model="busquedaInscripcion"
                @input="debounceFilterInscripciones"
                @focus="mostrarListaInscripciones = true"
                type="text"
                placeholder="Buscar por nombre de estudiante o materia (mín. 2 caracteres)..."
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400"
              />
              
              <!-- Indicador de carga -->
              <div v-if="buscandoInscripciones" class="absolute right-3 top-3">
                <span class="animate-spin text-blue-600">⏳</span>
              </div>
              
              <!-- Lista de inscripciones filtradas -->
              <div
                v-if="mostrarListaInscripciones && inscripcionesFiltradas.length > 0"
                class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-xl shadow-lg max-h-60 overflow-y-auto"
              >
                <button
                  v-for="i in inscripcionesFiltradas"
                  :key="i.id"
                  type="button"
                  @click="seleccionarInscripcion(i)"
                  class="w-full px-4 py-3 text-left hover:bg-blue-50 transition border-b border-gray-100 last:border-0"
                >
                  <p class="font-medium text-gray-900 text-sm">
                    {{ i.estudiante?.nombre }} {{ i.estudiante?.apellido }}
                  </p>
                  <p class="text-xs text-gray-600">{{ i.horario?.materia?.nombre }}</p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ i.horario?.materia?.codigo }} - Sec: {{ i.horario?.seccion }}
                  </p>
                </button>
              </div>

              <!-- Inscripción seleccionada -->
              <div v-if="inscripcionSeleccionada" class="mt-3 bg-blue-50 rounded-xl p-3 flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 text-sm">
                    {{ inscripcionSeleccionada.estudiante?.nombre }} {{ inscripcionSeleccionada.estudiante?.apellido }}
                  </p>
                  <p class="text-xs text-gray-600">{{ inscripcionSeleccionada.horario?.materia?.nombre }}</p>
                </div>
                <button type="button" @click="limpiarInscripcion" class="text-red-600 hover:text-red-800 text-xl">×</button>
              </div>
            </div>
          </div>

          <div v-else class="bg-blue-50 rounded-xl p-4">
            <p class="font-semibold text-gray-900">{{ form.estudiante_nombre }}</p>
            <p class="text-sm text-gray-600">{{ form.materia_nombre }}</p>
          </div>

          <div class="grid grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nota 1 (30%)</label>
              <input v-model.number="form.nota_1" type="number" step="0.01" min="0" max="20" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="0.00">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nota 2 (30%)</label>
              <input v-model.number="form.nota_2" type="number" step="0.01" min="0" max="20" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="0.00">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nota 3 (40%)</label>
              <input v-model.number="form.nota_3" type="number" step="0.01" min="0" max="20" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="0.00">
            </div>
          </div>

          <div v-if="notaFinalCalculada !== null" class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-sm text-gray-600 mb-2">Nota Final Calculada</p>
            <p class="text-3xl font-bold" :class="getNotaColor(notaFinalCalculada)">
              {{ parseFloat(notaFinalCalculada).toFixed(2) }}
            </p>
            <p class="text-sm mt-2" :class="parseFloat(notaFinalCalculada) >= 10 ? 'text-green-600' : 'text-red-600'">
              {{ parseFloat(notaFinalCalculada) >= 10 ? '✓ Aprobado' : '✗ Reprobado' }}
            </p>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="cerrarModal" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">
              Cancelar
            </button>
            <button type="submit" :disabled="guardando || (!form.id && !inscripcionSeleccionada)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition disabled:opacity-50">
              {{ guardando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const guardando = ref(false)
const modalAbierto = ref(false)
const calificaciones = ref([])
const inscripciones = ref([])
const busquedaInscripcion = ref('')
const inscripcionesFiltradas = ref([])
const inscripcionSeleccionada = ref(null)
const mostrarListaInscripciones = ref(false)
const buscandoInscripciones = ref(false)

let debounceTimer = null
const debounceFilterInscripciones = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    filtrarInscripciones()
  }, 500)
}

const filtros = reactive({
  buscar: '',
  periodo_academico: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

const form = reactive({
  id: null,
  inscripcion_id: '',
  nota_1: null,
  nota_2: null,
  nota_3: null,
  estudiante_nombre: '',
  materia_nombre: ''
})

const pages = computed(() => {
  const total = pagination.last_page
  const current = pagination.current_page
  const range = []
  const delta = 2

  for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
    range.push(i)
  }
  return range
})

const notaFinalCalculada = computed(() => {
  if (form.nota_1 === null || form.nota_2 === null || form.nota_3 === null) return null
  return (form.nota_1 * 0.3) + (form.nota_2 * 0.3) + (form.nota_3 * 0.4)
})

const getNotaColor = (nota) => {
  if (nota === null) return 'text-gray-400'
  const n = parseFloat(nota)
  if (n >= 16) return 'text-green-600'
  if (n >= 10) return 'text-blue-600'
  return 'text-red-600'
}

const getEstadoBadge = (nota) => {
  const n = parseFloat(nota)
  if (n >= 10) return 'bg-green-100 text-green-700'
  return 'bg-red-100 text-red-700'
}

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 15, ...filtros }
    if (!params.buscar) delete params.buscar
    if (!params.periodo_academico) delete params.periodo_academico

    const { data } = await api.get('/calificaciones', { params })
    calificaciones.value = data.data
    Object.assign(pagination, {
      current_page: data.current_page,
      last_page: data.last_page,
      from: data.from,
      to: data.to,
      total: data.total
    })
  } catch (error) {
    console.error('Error al cargar calificaciones:', error)
  } finally {
    loading.value = false
  }
}

async function cargarInscripciones() {
  // Ya no es necesario cargar todas las inscripciones al inicio
}

async function filtrarInscripciones() {
  const busqueda = busquedaInscripcion.value.trim()
  
  if (!busqueda || busqueda.length < 2) {
    inscripcionesFiltradas.value = []
    return
  }

  buscandoInscripciones.value = true
  try {
    const { data } = await api.get('/inscripciones', { 
      params: { 
        per_page: 15, 
        estatus: 'activa',
        periodo_academico: filtros.periodo_academico || '2026-1',
        buscar: busqueda
      } 
    })
    inscripcionesFiltradas.value = data.data
  } catch (error) {
    console.error('Error al buscar inscripciones:', error)
    inscripcionesFiltradas.value = []
  } finally {
    buscandoInscripciones.value = false
  }
}

function seleccionarInscripcion(inscripcion) {
  inscripcionSeleccionada.value = inscripcion
  form.inscripcion_id = inscripcion.id
  busquedaInscripcion.value = `${inscripcion.estudiante?.nombre} ${inscripcion.estudiante?.apellido} - ${inscripcion.horario?.materia?.nombre}`
  mostrarListaInscripciones.value = false
  inscripcionesFiltradas.value = []
}

function limpiarInscripcion() {
  inscripcionSeleccionada.value = null
  form.inscripcion_id = ''
  busquedaInscripcion.value = ''
  inscripcionesFiltradas.value = []
  mostrarListaInscripciones.value = true
}

function abrirModal(calificacion = null) {
  if (calificacion) {
    Object.assign(form, {
      id: calificacion.id,
      inscripcion_id: calificacion.inscripcion_id,
      nota_1: calificacion.nota_1,
      nota_2: calificacion.nota_2,
      nota_3: calificacion.nota_3,
      estudiante_nombre: `${calificacion.inscripcion?.estudiante?.nombre} ${calificacion.inscripcion?.estudiante?.apellido}`,
      materia_nombre: calificacion.inscripcion?.horario?.materia?.nombre
    })
  } else {
    Object.assign(form, {
      id: null,
      inscripcion_id: '',
      nota_1: null,
      nota_2: null,
      nota_3: null,
      estudiante_nombre: '',
      materia_nombre: ''
    })
    inscripcionSeleccionada.value = null
    busquedaInscripcion.value = ''
    inscripcionesFiltradas.value = []
  }
  modalAbierto.value = true
}

function cerrarModal() {
  modalAbierto.value = false
}

async function guardar() {
  guardando.value = true
  try {
    if (form.id) {
      await api.put(`/calificaciones/${form.id}`, {
        nota_1: form.nota_1,
        nota_2: form.nota_2,
        nota_3: form.nota_3
      })
    } else {
      await api.post('/calificaciones', {
        inscripcion_id: form.inscripcion_id,
        nota_1: form.nota_1,
        nota_2: form.nota_2,
        nota_3: form.nota_3
      })
    }
    await cargar(pagination.current_page)
    cerrarModal()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert(error.response?.data?.message || 'Error al guardar la calificación')
  } finally {
    guardando.value = false
  }
}

onMounted(() => cargar())
</script>
