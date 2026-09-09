<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Inscripciones</h1>
      <p class="text-gray-500 mt-1">Administra las inscripciones de estudiantes a horarios</p>
    </div>

    <!-- Barra de acciones -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 w-full sm:max-w-md">
          <input
            v-model="filtros.buscar"
            @input="cargar(1)"
            type="search"
            placeholder="Buscar estudiante o materia..."
            class="w-full py-2.5 px-4 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <select v-model="filtros.estatus" @change="cargar(1)"
            class="flex-1 sm:flex-none py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los estatus</option>
            <option value="activa">Activa</option>
            <option value="retirada">Retirada</option>
            <option value="aprobada">Aprobada</option>
            <option value="reprobada">Reprobada</option>
          </select>

          <select v-model="filtros.periodo_academico" @change="cargar(1)"
            class="flex-1 sm:flex-none py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los periodos</option>
            <option value="2026-1">2026-1</option>
            <option value="2025-3">2025-3</option>
            <option value="2025-2">2025-2</option>
          </select>

          <button @click="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap">
            <span>➕</span>
            <span>Nueva Inscripción</span>
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
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">ID</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Estudiante</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Materia</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Horario</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Periodo</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Fecha</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Estatus</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 5" :key="n">
              <td v-for="m in 8" :key="m" class="px-6 py-4">
                <div class="h-4 bg-gray-100 rounded animate-pulse"></div>
              </td>
            </tr>
          </tbody>
          <tbody v-else-if="inscripciones.length === 0">
            <tr>
              <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                <p class="text-4xl mb-2">📝</p>
                <p>No se encontraron inscripciones</p>
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-for="i in inscripciones" :key="i.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <span class="font-mono text-sm text-gray-600">#{{ i.id }}</span>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ i.estudiante?.nombre }} {{ i.estudiante?.apellido }}</p>
                  <p class="text-xs text-gray-600">{{ i.estudiante?.email }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-medium text-gray-900">{{ i.horario?.materia?.nombre || '—' }}</p>
                  <p class="text-xs text-gray-600">{{ i.horario?.materia?.codigo }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-600">
                  <p>{{ i.horario?.dia_semana }} {{ i.horario?.hora_inicio?.slice(0,5) }}</p>
                  <p class="text-xs">Sec: {{ i.horario?.seccion }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-block bg-purple-100 text-purple-700 px-2 py-1 rounded-lg text-xs font-semibold">
                  {{ i.horario?.periodo_academico }}
                </span>
              </td>
              <td class="px-6 py-4 text-center text-sm text-gray-600">
                {{ formatearFecha(i.fecha_inscripcion) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="getEstatusBadge(i.estatus)" class="inline-block px-3 py-1 rounded-full text-xs font-semibold">
                  {{ i.estatus }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button v-if="i.estatus === 'activa'" @click="retirar(i)" class="text-orange-600 hover:text-orange-800 p-2" title="Retirar">
                    ⚠️
                  </button>
                  <button @click="eliminar(i)" class="text-red-600 hover:text-red-800 p-2" title="Eliminar">
                    🗑️
                  </button>
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
          <h2 class="text-xl font-bold text-gray-900">Nueva Inscripción</h2>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>

        <form @submit.prevent="guardar" class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Estudiante *</label>
            <div class="relative">
              <input
                v-model="busquedaEstudiante"
                @input="debounceFilterEstudiantes"
                @focus="mostrarListaEstudiantes = true"
                type="text"
                placeholder="Buscar por nombre o email (mín. 2 caracteres)..."
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400"
              />
              
              <!-- Indicador de carga -->
              <div v-if="buscandoEstudiantes" class="absolute right-3 top-3">
                <span class="animate-spin text-blue-600">⏳</span>
              </div>
              
              <!-- Lista de estudiantes filtrados -->
              <div
                v-if="mostrarListaEstudiantes && estudiantesFiltrados.length > 0"
                class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-xl shadow-lg max-h-60 overflow-y-auto"
              >
                <button
                  v-for="e in estudiantesFiltrados"
                  :key="e.id"
                  type="button"
                  @click="seleccionarEstudiante(e)"
                  class="w-full px-4 py-3 text-left hover:bg-blue-50 transition border-b border-gray-100 last:border-0"
                >
                  <p class="font-medium text-gray-900 text-sm">{{ e.nombre }} {{ e.apellido }}</p>
                  <p class="text-xs text-gray-600">{{ e.email }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ e.carrera?.nombre || 'Sin carrera' }}</p>
                </button>
              </div>

              <!-- Estudiante seleccionado -->
              <div v-if="estudianteSeleccionado" class="mt-3 bg-blue-50 rounded-xl p-3 flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 text-sm">{{ estudianteSeleccionado.nombre }} {{ estudianteSeleccionado.apellido }}</p>
                  <p class="text-xs text-gray-600">{{ estudianteSeleccionado.email }}</p>
                </div>
                <button type="button" @click="limpiarEstudiante" class="text-red-600 hover:text-red-800 text-xl">×</button>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Horario *</label>
            <select v-model="form.horario_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
              <option value="">Seleccionar horario...</option>
              <option v-for="h in horarios" :key="h.id" :value="h.id">
                {{ h.materia?.nombre }} - {{ h.dia_semana }} {{ h.hora_inicio }} (Sec: {{ h.seccion }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de inscripción</label>
            <input v-model="form.fecha_inscripcion" type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="cerrarModal" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">
              Cancelar
            </button>
            <button type="submit" :disabled="guardando || !estudianteSeleccionado" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition disabled:opacity-50">
              {{ guardando ? 'Guardando...' : 'Inscribir' }}
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
const inscripciones = ref([])
const estudiantes = ref([])
const horarios = ref([])
const busquedaEstudiante = ref('')
const estudiantesFiltrados = ref([])
const estudianteSeleccionado = ref(null)
const mostrarListaEstudiantes = ref(false)
const buscandoEstudiantes = ref(false)

let debounceTimer = null
const debounceFilterEstudiantes = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    filtrarEstudiantes()
  }, 500) // Espera 500ms después de que el usuario deja de escribir
}

const filtros = reactive({
  buscar: '',
  estatus: '',
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
  estudiante_id: '',
  horario_id: '',
  fecha_inscripcion: new Date().toISOString().split('T')[0]
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

const getEstatusBadge = (estatus) => {
  const badges = {
    activa: 'bg-green-100 text-green-700',
    retirada: 'bg-orange-100 text-orange-700',
    aprobada: 'bg-blue-100 text-blue-700',
    reprobada: 'bg-red-100 text-red-700'
  }
  return badges[estatus] || 'bg-gray-100 text-gray-700'
}

const formatearFecha = (fecha) => {
  if (!fecha) return '—'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 15 }
    
    // Solo agregar parámetros si tienen valor
    if (filtros.buscar) params.buscar = filtros.buscar
    if (filtros.estatus) params.estatus = filtros.estatus  
    if (filtros.periodo_academico) params.periodo_academico = filtros.periodo_academico

    const { data } = await api.get('/inscripciones', { params })
    inscripciones.value = data.data
    Object.assign(pagination, {
      current_page: data.current_page,
      last_page: data.last_page,
      from: data.from,
      to: data.to,
      total: data.total
    })
  } catch (error) {
    console.error('Error al cargar inscripciones:', error)
  } finally {
    loading.value = false
  }
}

async function cargarEstudiantes() {
  // Ya no es necesario cargar todos los estudiantes al inicio
}

async function filtrarEstudiantes() {
  const busqueda = busquedaEstudiante.value.trim()
  
  if (!busqueda || busqueda.length < 2) {
    estudiantesFiltrados.value = []
    return
  }

  buscandoEstudiantes.value = true
  try {
    const { data } = await api.get('/estudiantes', { 
      params: { 
        per_page: 15, 
        estatus: 'activo',
        buscar: busqueda
      } 
    })
    estudiantesFiltrados.value = data.data
  } catch (error) {
    console.error('Error al buscar estudiantes:', error)
    estudiantesFiltrados.value = []
  } finally {
    buscandoEstudiantes.value = false
  }
}

function seleccionarEstudiante(estudiante) {
  estudianteSeleccionado.value = estudiante
  form.estudiante_id = estudiante.id
  busquedaEstudiante.value = `${estudiante.nombre} ${estudiante.apellido}`
  mostrarListaEstudiantes.value = false
  estudiantesFiltrados.value = []
}

function limpiarEstudiante() {
  estudianteSeleccionado.value = null
  form.estudiante_id = ''
  busquedaEstudiante.value = ''
  estudiantesFiltrados.value = []
  mostrarListaEstudiantes.value = true
}

async function cargarHorarios() {
  try {
    const { data } = await api.get('/horarios/disponibles/listado', { 
      params: { periodo_academico: filtros.periodo_academico || '2026-1' } 
    })
    horarios.value = data
  } catch (error) {
    console.error('Error al cargar horarios:', error)
  }
}

function abrirModal() {
  form.estudiante_id = ''
  form.horario_id = ''
  form.fecha_inscripcion = new Date().toISOString().split('T')[0]
  estudianteSeleccionado.value = null
  busquedaEstudiante.value = ''
  estudiantesFiltrados.value = []
  modalAbierto.value = true
  cargarHorarios()
}

function cerrarModal() {
  modalAbierto.value = false
}

async function guardar() {
  guardando.value = true
  try {
    await api.post('/inscripciones', form)
    await cargar(pagination.current_page)
    cerrarModal()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert(error.response?.data?.message || 'Error al guardar la inscripción')
  } finally {
    guardando.value = false
  }
}

async function retirar(inscripcion) {
  if (!confirm(`¿Retirar la inscripción de ${inscripcion.estudiante?.nombre}?`)) return

  try {
    await api.post(`/inscripciones/${inscripcion.id}/retirar`)
    await cargar(pagination.current_page)
  } catch (error) {
    console.error('Error al retirar:', error)
    alert('Error al retirar la inscripción')
  }
}

async function eliminar(inscripcion) {
  if (!confirm(`¿Eliminar esta inscripción? Esta acción no se puede deshacer.`)) return

  try {
    await api.delete(`/inscripciones/${inscripcion.id}`)
    await cargar(pagination.current_page)
  } catch (error) {
    console.error('Error al eliminar:', error)
    alert('Error al eliminar la inscripción')
  }
}

onMounted(() => cargar())
</script>
