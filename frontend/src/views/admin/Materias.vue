<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Materias</h1>
      <p class="text-gray-500 mt-1">Administra las materias del sistema académico</p>
    </div>

    <!-- Barra de acciones -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 w-full sm:max-w-md">
          <input
            v-model="filtros.buscar"
            @input="cargar(1)"
            type="search"
            placeholder="Buscar por nombre o código..."
            class="w-full py-2.5 px-4 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <select v-model="filtros.carrera_id" @change="cargar(1)"
            class="flex-1 sm:flex-none py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas las carreras</option>
            <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
          </select>

          <button @click="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap">
            <span>➕</span>
            <span>Nueva Materia</span>
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
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Código</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Nombre</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Carrera</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Semestre</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Créditos</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Horas</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 5" :key="n">
              <td v-for="m in 7" :key="m" class="px-6 py-4">
                <div class="h-4 bg-gray-100 rounded animate-pulse"></div>
              </td>
            </tr>
          </tbody>
          <tbody v-else-if="materias.length === 0">
            <tr>
              <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                <p class="text-4xl mb-2">📚</p>
                <p>No se encontraron materias</p>
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-for="m in materias" :key="m.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <span class="font-mono text-sm font-semibold text-gray-900">{{ m.codigo }}</span>
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900">{{ m.nombre }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-600">{{ m.carrera?.nombre || '—' }}</p>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                  {{ m.semestre }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold text-gray-900">{{ m.creditos }}</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="text-sm text-gray-600">{{ m.horas_semanales }}h</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="abrirModal(m)" class="text-blue-600 hover:text-blue-800 p-2">✏️</button>
                  <button @click="eliminar(m)" class="text-red-600 hover:text-red-800 p-2">🗑️</button>
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
            {{ form.id ? 'Editar Materia' : 'Nueva Materia' }}
          </h2>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>

        <form @submit.prevent="guardar" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Código *</label>
              <input v-model="form.codigo" required type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="MAT-101">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Carrera *</label>
              <select v-model="form.carrera_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
                <option value="">Seleccionar...</option>
                <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la materia *</label>
            <input v-model="form.nombre" required type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-400" placeholder="Cálculo Diferencial">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Semestre *</label>
              <input v-model.number="form.semestre" required type="number" min="1" max="12" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Créditos *</label>
              <input v-model.number="form.creditos" required type="number" min="1" max="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Horas/semana *</label>
              <input v-model.number="form.horas_semanales" required type="number" min="1" max="20" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
            <select v-model="form.tipo" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
              <option value="obligatoria">Obligatoria</option>
              <option value="electiva">Electiva</option>
              <option value="practica">Práctica</option>
            </select>
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
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const guardando = ref(false)
const modalAbierto = ref(false)
const materias = ref([])
const carreras = ref([])

const filtros = reactive({
  buscar: '',
  carrera_id: ''
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
  nombre: '',
  carrera_id: '',
  semestre: 1,
  creditos: 3,
  horas_semanales: 4,
  tipo: 'obligatoria'
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

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 15, ...filtros }
    if (!params.buscar) delete params.buscar
    if (!params.carrera_id) delete params.carrera_id

    const { data } = await api.get('/materias', { params })
    materias.value = data.data
    Object.assign(pagination, {
      current_page: data.current_page,
      last_page: data.last_page,
      from: data.from,
      to: data.to,
      total: data.total
    })
  } catch (error) {
    console.error('Error al cargar materias:', error)
  } finally {
    loading.value = false
  }
}

async function cargarCarreras() {
  try {
    const { data } = await api.get('/carreras', { params: { per_page: 100 } })
    carreras.value = data.data
  } catch (error) {
    console.error('Error al cargar carreras:', error)
  }
}

function abrirModal(materia = null) {
  if (materia) {
    Object.assign(form, {
      id: materia.id,
      codigo: materia.codigo,
      nombre: materia.nombre,
      carrera_id: materia.carrera_id,
      semestre: materia.semestre,
      creditos: materia.creditos,
      horas_semanales: materia.horas_semanales,
      tipo: materia.tipo || 'obligatoria'
    })
  } else {
    Object.assign(form, {
      id: null,
      codigo: '',
      nombre: '',
      carrera_id: '',
      semestre: 1,
      creditos: 3,
      horas_semanales: 4,
      tipo: 'obligatoria'
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
      await api.put(`/materias/${form.id}`, form)
      toast.success('Materia actualizada correctamente')
    } else {
      await api.post('/materias', form)
      toast.success('Materia creada correctamente')
    }
    await cargar(pagination.current_page)
    cerrarModal()
  } catch (error) {
    console.error('Error al guardar:', error)
    // El error ya fue manejado por el interceptor
  } finally {
    guardando.value = false
  }
}

async function eliminar(materia) {
  if (!confirm(`¿Eliminar la materia "${materia.nombre}"?`)) return

  try {
    await api.delete(`/materias/${materia.id}`)
    toast.success('Materia eliminada correctamente')
    await cargar(pagination.current_page)
  } catch (error) {
    console.error('Error al eliminar:', error)
    // El error ya fue manejado por el interceptor
  }
}

onMounted(() => {
  cargar()
  cargarCarreras()
})
</script>
