<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Estudiantes</h1>
        <p class="text-gray-500 text-sm mt-0.5">
          {{ pagination.total?.toLocaleString() ?? '—' }} registros encontrados
        </p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Búsqueda -->
        <div class="lg:col-span-2 relative">
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-600">🔍</span>
          <input
            v-model="filtros.buscar"
            @input="buscarDebounced"
            type="text"
            placeholder="Buscar por nombre, cédula, matrícula…"
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Estatus -->
        <select
          v-model="filtros.estatus"
          @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option class="text-gray-900" value="">Todos los estatus</option>
          <option class="text-gray-900" value="activo">Activo</option>
          <option class="text-gray-900" value="inactivo">Inactivo</option>
          <option class="text-gray-900" value="egresado">Egresado</option>
          <option class="text-gray-900" value="retirado">Retirado</option>
          <option class="text-gray-900" value="suspendido">Suspendido</option>
        </select>

        <!-- Semestre -->
        <select
          v-model="filtros.semestre"
          @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option class="text-gray-900" value="">Todos los semestres</option>
          <option v-for="s in 10" :key="s" :value="s">Semestre {{ s }}</option>
        </select>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

      <!-- Skeleton loader -->
      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 10" :key="n" class="h-10 bg-gray-100 rounded-lg animate-pulse" />
      </div>

      <!-- Sin resultados -->
      <div v-else-if="rows.length === 0" class="py-20 text-center text-gray-600">
        <p class="text-4xl mb-3">🔍</p>
        <p class="font-medium">No se encontraron estudiantes</p>
        <p class="text-sm">Intenta ajustar los filtros</p>
      </div>

      <!-- Tabla con datos -->
      <template v-else>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100 text-left text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 font-semibold">Estudiante</th>
                <th class="px-5 py-3 font-semibold">Cédula / Matrícula</th>
                <th class="px-5 py-3 font-semibold">Carrera</th>
                <th class="px-5 py-3 font-semibold text-center">Semestre</th>
                <th class="px-5 py-3 font-semibold text-center">Índice</th>
                <th class="px-5 py-3 font-semibold text-center">Estatus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="e in rows"
                :key="e.id"
                class="hover:bg-blue-50/40 transition"
              >
                <td class="px-5 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs flex-shrink-0">
                      {{ initials(e.nombre, e.apellido) }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ e.apellido }}, {{ e.nombre }}</p>
                      <p class="text-xs text-gray-600">{{ e.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3 text-gray-600">
                  <p>{{ e.cedula }}</p>
                  <p class="text-xs text-gray-600">{{ e.matricula }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600 max-w-[180px] truncate">
                  {{ e.carrera?.nombre ?? '—' }}
                </td>
                <td class="px-5 py-3 text-center text-gray-700 font-medium">{{ e.semestre_actual }}</td>
                <td class="px-5 py-3 text-center">
                  <span :class="indiceColor(e.indice_academico)" class="font-semibold">
                    {{ e.indice_academico }}
                  </span>
                </td>
                <td class="px-5 py-3 text-center">
                  <span :class="estatusBadge(e.estatus)" class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ e.estatus }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
          <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
          <div class="flex gap-2">
            <button
              @click="cargar(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition"
            >← Anterior</button>
            <button
              @click="cargar(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition"
            >Siguiente →</button>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api         from '@/services/api'

// ── Helpers ──────────────────────────────────────────────────
const initials = (n, a) => ((n?.[0] ?? '') + (a?.[0] ?? '')).toUpperCase()

const estatusBadge = s => ({
  activo:     'bg-green-100 text-green-700',
  inactivo:   'bg-gray-100 text-gray-500',
  egresado:   'bg-blue-100 text-blue-700',
  retirado:   'bg-red-100 text-red-600',
  suspendido: 'bg-yellow-100 text-yellow-700',
})[s] ?? 'bg-gray-100 text-gray-500'

const indiceColor = v => v >= 16 ? 'text-green-600' : v >= 12 ? 'text-yellow-600' : 'text-red-500'

// ── Estado ───────────────────────────────────────────────────
const loading    = ref(false)
const rows       = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

const filtros = reactive({
  buscar:   '',
  estatus:  '',
  semestre: '',
})

// ── Debounce para búsqueda ───────────────────────────────────
let timer
const buscarDebounced = () => {
  clearTimeout(timer)
  timer = setTimeout(() => cargar(1), 400)
}

// ── Carga ────────────────────────────────────────────────────
async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 25 }
    if (filtros.buscar)   params.buscar   = filtros.buscar
    if (filtros.estatus)  params.estatus  = filtros.estatus
    if (filtros.semestre) params.semestre = filtros.semestre

    const { data } = await api.get('/estudiantes', { params })
    rows.value                 = data.data
    pagination.current_page    = data.current_page
    pagination.last_page       = data.last_page
    pagination.total           = data.total
  } finally {
    loading.value = false
  }
}

onMounted(() => cargar())
</script>




