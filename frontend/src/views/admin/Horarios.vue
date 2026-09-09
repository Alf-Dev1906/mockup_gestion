<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Horarios</h1>
        <p class="text-gray-500 text-sm mt-0.5">
          {{ pagination.total?.toLocaleString() ?? '—' }} horarios — Periodo {{ filtros.periodo }}
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
            placeholder="Buscar por materia o profesor…"
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Día -->
        <select
          v-model="filtros.dia"
          @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option class="text-gray-900" value="">Todos los días</option>
          <option v-for="d in dias" :key="d" :value="d" class="capitalize">{{ d }}</option>
        </select>

        <!-- Cupos / Estatus -->
        <select
          v-model="filtros.con_cupos"
          @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option class="text-gray-900" value="">Todos</option>
          <option class="text-gray-900" value="1">Solo con cupos disponibles</option>
        </select>
      </div>
    </div>

    <!-- Tarjetas de horarios -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="n in 9" :key="n" class="h-36 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <div v-else-if="rows.length === 0" class="py-20 text-center text-gray-600 bg-white rounded-2xl border border-gray-100">
      <p class="text-4xl mb-3">📅</p>
      <p class="font-medium">No se encontraron horarios</p>
      <p class="text-sm">Intenta ajustar los filtros</p>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
        <div
          v-for="h in rows"
          :key="h.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition"
        >
          <!-- Header tarjeta -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 truncate">{{ h.materia?.nombre ?? '—' }}</p>
              <p class="text-xs text-gray-600 mt-0.5">{{ h.materia?.codigo }} · Sección {{ h.seccion }}</p>
            </div>
            <span :class="estatusBadge(h.estatus)" class="ml-2 text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0">
              {{ h.estatus }}
            </span>
          </div>

          <!-- Info -->
          <div class="space-y-1.5 text-sm text-gray-600">
            <div class="flex items-center gap-2">
              <span class="text-base">👨‍🏫</span>
              <span class="truncate">{{ h.profesor?.apellido }}, {{ h.profesor?.nombre }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-base">🏫</span>
              <span>{{ h.aula?.codigo }} · {{ h.aula?.edificio }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-base">🕐</span>
              <span class="capitalize font-medium text-blue-700">{{ h.dia_semana }}</span>
              <span>{{ h.hora_inicio?.slice(0,5) }} – {{ h.hora_fin?.slice(0,5) }}</span>
            </div>
          </div>

          <!-- Barra de cupos -->
          <div class="mt-4">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Cupos</span>
              <span class="font-semibold" :class="h.cupo_actual >= h.cupo_maximo ? 'text-red-500' : 'text-green-600'">
                {{ h.cupo_actual }}/{{ h.cupo_maximo }}
              </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all"
                :class="porcentaje(h) >= 100 ? 'bg-red-400' : porcentaje(h) >= 75 ? 'bg-yellow-400' : 'bg-green-400'"
                :style="{ width: Math.min(porcentaje(h), 100) + '%' }"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div class="flex items-center justify-between px-1 text-sm text-gray-500">
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
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api         from '@/services/api'

const dias = ['lunes','martes','miércoles','jueves','viernes','sábado']

const estatusBadge = s => ({
  abierto:    'bg-green-100 text-green-700',
  en_curso:   'bg-blue-100 text-blue-700',
  cerrado:    'bg-red-100 text-red-600',
  finalizado: 'bg-gray-100 text-gray-500',
  cancelado:  'bg-yellow-100 text-yellow-700',
})[s] ?? 'bg-gray-100 text-gray-500'

const porcentaje = h => h.cupo_maximo ? Math.round((h.cupo_actual / h.cupo_maximo) * 100) : 0

const loading    = ref(false)
const rows       = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

const filtros = reactive({
  buscar:    '',
  dia:       '',
  con_cupos: '',
  periodo:   '2026-1',
})

let timer
const buscarDebounced = () => {
  clearTimeout(timer)
  timer = setTimeout(() => cargar(1), 400)
}

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 18, periodo_academico: filtros.periodo }
    if (filtros.buscar)    params.buscar    = filtros.buscar
    if (filtros.dia)       params.dia_semana = filtros.dia
    if (filtros.con_cupos) params.con_cupos = filtros.con_cupos

    const { data } = await api.get('/horarios', { params })
    rows.value              = data.data
    pagination.current_page = data.current_page
    pagination.last_page    = data.last_page
    pagination.total        = data.total
  } finally {
    loading.value = false
  }
}

onMounted(() => cargar())
</script>




