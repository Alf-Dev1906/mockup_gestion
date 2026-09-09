<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Aulas</h1>
      <p class="text-gray-500 mt-1">Administra los espacios físicos de la universidad</p>
    </div>

    <!-- Barra de acciones -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 w-full sm:max-w-md">
          <input
            v-model="filtros.buscar"
            @input="cargar(1)"
            type="search"
            placeholder="Buscar por código o edificio..."
            class="w-full py-2.5 px-4 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <select v-model="filtros.tipo" @change="cargar(1)"
            class="flex-1 sm:flex-none py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los tipos</option>
            <option value="aula">Aula</option>
            <option value="laboratorio">Laboratorio</option>
            <option value="auditorio">Auditorio</option>
            <option value="taller">Taller</option>
          </select>

          <button @click="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap">
            <span>➕</span>
            <span>Nueva Aula</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Grid de aulas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-if="loading" v-for="n in 8" :key="n" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="space-y-3">
          <div class="h-6 bg-gray-100 rounded animate-pulse"></div>
          <div class="h-4 bg-gray-100 rounded animate-pulse w-2/3"></div>
          <div class="h-4 bg-gray-100 rounded animate-pulse"></div>
        </div>
      </div>

      <div v-else-if="aulas.length === 0" class="col-span-full">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center text-gray-500">
          <p class="text-5xl mb-4">🏫</p>
          <p>No se encontraron aulas</p>
        </div>
      </div>

      <div v-else v-for="aula in aulas" :key="aula.id" 
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
        <div class="flex items-start justify-between mb-4">
          <div :class="getTipoBg(aula.tipo)" class="w-12 h-12 rounded-xl flex items-center justify-center">
            <span class="text-2xl">{{ getTipoIcono(aula.tipo) }}</span>
          </div>
          <span :class="getTipoBadge(aula.tipo)" class="text-xs font-bold px-3 py-1 rounded-lg">
            {{ getTipoLabel(aula.tipo) }}
          </span>
        </div>

        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ aula.codigo }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ aula.edificio }} - {{ aula.piso }}° piso</p>

        <div class="grid grid-cols-2 gap-2 mb-4 text-sm">
          <div class="bg-gray-50 rounded-lg p-2 text-center">
            <p class="font-bold text-gray-900">{{ aula.capacidad }}</p>
            <p class="text-xs text-gray-600">Capacidad</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-2 text-center">
            <p class="font-bold text-gray-900">{{ aula.estado }}</p>
            <p class="text-xs text-gray-600">Estado</p>
          </div>
        </div>

        <div class="flex gap-2">
          <button @click="abrirModal(aula)" class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 rounded-lg font-semibold transition text-sm">
            Editar
          </button>
          <button @click="eliminar(aula)" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold transition text-sm">
            🗑️
          </button>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
      <button
        v-for="page in pages"
        :key="page"
        @click="cargar(page)"
        :disabled="page === pagination.current_page"
        :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
        class="px-4 py-2 rounded-xl font-semibold transition disabled:opacity-50 border border-gray-200">
        {{ page }}
      </button>
    </div>

    <!-- Modal -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900">
            {{ form.id ? 'Editar Aula' : 'Nueva Aula' }}
          </h2>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>

        <form @submit.prevent="guardar" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Código *</label>
              <input v-model="form.codigo" required type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="A-101">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo *</label>
              <select v-model="form.tipo" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
                <option value="aula">Aula</option>
                <option value="laboratorio">Laboratorio</option>
                <option value="auditorio">Auditorio</option>
                <option value="taller">Taller</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Edificio *</label>
              <input v-model="form.edificio" required type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="Edificio A">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Piso *</label>
              <input v-model.number="form.piso" required type="number" min="0" max="20" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Capacidad *</label>
              <input v-model.number="form.capacidad" required type="number" min="10" max="500" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Estado *</label>
              <select v-model="form.estado" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
                <option value="disponible">Disponible</option>
                <option value="mantenimiento">Mantenimiento</option>
                <option value="fuera_servicio">Fuera de servicio</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" @click="cerrarModal" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">
              Cancelar
            </button>
            <button type="submit" :disabled="guardando" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition disabled:opacity-50">
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
const aulas = ref([])

const filtros = reactive({
  buscar: '',
  tipo: ''
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
  codigo: '',
  edificio: '',
  piso: 1,
  tipo: 'aula',
  capacidad: 30,
  estado: 'disponible'
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

const getTipoIcono = (tipo) => {
  const iconos = {
    aula: '🏫',
    laboratorio: '🔬',
    auditorio: '🎭',
    taller: '🔧'
  }
  return iconos[tipo] || '🏫'
}

const getTipoLabel = (tipo) => {
  const labels = {
    aula: 'Aula',
    laboratorio: 'Laboratorio',
    auditorio: 'Auditorio',
    taller: 'Taller'
  }
  return labels[tipo] || tipo
}

const getTipoBg = (tipo) => {
  const bgs = {
    aula: 'bg-blue-100',
    laboratorio: 'bg-purple-100',
    auditorio: 'bg-green-100',
    taller: 'bg-orange-100'
  }
  return bgs[tipo] || 'bg-gray-100'
}

const getTipoBadge = (tipo) => {
  const badges = {
    aula: 'bg-blue-100 text-blue-700',
    laboratorio: 'bg-purple-100 text-purple-700',
    auditorio: 'bg-green-100 text-green-700',
    taller: 'bg-orange-100 text-orange-700'
  }
  return badges[tipo] || 'bg-gray-100 text-gray-700'
}

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20, ...filtros }
    if (!params.buscar) delete params.buscar
    if (!params.tipo) delete params.tipo

    const { data } = await api.get('/aulas', { params })
    aulas.value = data.data
    Object.assign(pagination, {
      current_page: data.current_page,
      last_page: data.last_page,
      from: data.from,
      to: data.to,
      total: data.total
    })
  } catch (error) {
    console.error('Error al cargar aulas:', error)
  } finally {
    loading.value = false
  }
}

function abrirModal(aula = null) {
  if (aula) {
    Object.assign(form, {
      id: aula.id,
      codigo: aula.codigo,
      edificio: aula.edificio,
      piso: aula.piso,
      tipo: aula.tipo,
      capacidad: aula.capacidad,
      estado: aula.estado
    })
  } else {
    Object.assign(form, {
      id: null,
      codigo: '',
      edificio: '',
      piso: 1,
      tipo: 'aula',
      capacidad: 30,
      estado: 'disponible'
    })
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
      await api.put(`/aulas/${form.id}`, form)
    } else {
      await api.post('/aulas', form)
    }
    await cargar(pagination.current_page)
    cerrarModal()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert('Error al guardar el aula')
  } finally {
    guardando.value = false
  }
}

async function eliminar(aula) {
  if (!confirm(`¿Eliminar el aula "${aula.codigo}"?`)) return

  try {
    await api.delete(`/aulas/${aula.id}`)
    await cargar(pagination.current_page)
  } catch (error) {
    console.error('Error al eliminar:', error)
    alert('Error al eliminar el aula')
  }
}

onMounted(() => cargar())
</script>
