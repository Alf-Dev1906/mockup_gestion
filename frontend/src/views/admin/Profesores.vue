<template>
  <AdminLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Profesores</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ pagination.total?.toLocaleString() ?? '—' }} registros</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="sm:col-span-2 lg:col-span-1 relative">
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-600">🔍</span>
          <input v-model="filtros.buscar" @input="buscarDebounced" type="text"
            placeholder="Buscar por nombre, cédula…"
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <select v-model="filtros.categoria" @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option class="text-gray-900" value="">Todas las categorías</option>
          <option class="text-gray-900" value="instructor">Instructor</option>
          <option class="text-gray-900" value="asistente">Asistente</option>
          <option class="text-gray-900" value="agregado">Agregado</option>
          <option class="text-gray-900" value="asociado">Asociado</option>
          <option class="text-gray-900" value="titular">Titular</option>
        </select>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 10" :key="n" class="h-10 bg-gray-100 rounded-lg animate-pulse" />
      </div>
      <div v-else-if="rows.length === 0" class="py-20 text-center text-gray-600">
        <p class="text-4xl mb-2">🔍</p><p>No se encontraron profesores</p>
      </div>
      <template v-else>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100 text-left text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 font-semibold">Profesor</th>
                <th class="px-5 py-3 font-semibold">Cédula / Código</th>
                <th class="px-5 py-3 font-semibold">Facultad</th>
                <th class="px-5 py-3 font-semibold">Categoría</th>
                <th class="px-5 py-3 font-semibold">Especialidad</th>
                <th class="px-5 py-3 font-semibold text-center">Estatus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="p in rows" :key="p.id" class="hover:bg-blue-50/40 transition">
                <td class="px-5 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs flex-shrink-0">
                      {{ initials(p.nombre, p.apellido) }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ p.titulo_academico }} {{ p.apellido }}, {{ p.nombre }}</p>
                      <p class="text-xs text-gray-600">{{ p.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3 text-gray-600">
                  <p>{{ p.cedula }}</p>
                  <p class="text-xs text-gray-600">{{ p.codigo_empleado }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600 max-w-[160px] truncate">{{ p.facultad?.nombre ?? '—' }}</td>
                <td class="px-5 py-3">
                  <span class="capitalize bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium">
                    {{ p.categoria }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-500 max-w-[140px] truncate">{{ p.especialidad }}</td>
                <td class="px-5 py-3 text-center">
                  <span :class="p.estatus === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    class="px-2.5 py-0.5 rounded-full text-xs font-medium">{{ p.estatus }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
          <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
          <div class="flex gap-2">
            <button @click="cargar(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
              class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition">← Anterior</button>
            <button @click="cargar(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition">Siguiente →</button>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const initials = (n, a) => ((n?.[0] ?? '') + (a?.[0] ?? '')).toUpperCase()
const loading    = ref(false)
const rows       = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const filtros    = reactive({ buscar: '', categoria: '' })

let timer
const buscarDebounced = () => { clearTimeout(timer); timer = setTimeout(() => cargar(1), 400) }

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 25 }
    if (filtros.buscar)    params.buscar    = filtros.buscar
    if (filtros.categoria) params.categoria = filtros.categoria
    const { data } = await api.get('/profesores', { params })
    rows.value = data.data
    pagination.current_page = data.current_page
    pagination.last_page    = data.last_page
    pagination.total        = data.total
  } finally { loading.value = false }
}

onMounted(() => cargar())
</script>




