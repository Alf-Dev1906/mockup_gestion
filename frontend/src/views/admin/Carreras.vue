<template>
  <AdminLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Carreras</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ pagination.total ?? '—' }} carreras registradas</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative">
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-600">🔍</span>
          <input v-model="filtros.buscar" @input="buscarDebounced" type="text"
            placeholder="Buscar carrera…"
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <select v-model="filtros.nivel" @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option class="text-gray-900" value="">Todos los niveles</option>
          <option class="text-gray-900" value="tsu">TSU</option>
          <option class="text-gray-900" value="licenciatura">Licenciatura</option>
          <option class="text-gray-900" value="ingenieria">Ingeniería</option>
        </select>
        <select v-model="filtros.modalidad" @change="cargar(1)"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option class="text-gray-900" value="">Todas las modalidades</option>
          <option class="text-gray-900" value="presencial">Presencial</option>
          <option class="text-gray-900" value="semipresencial">Semipresencial</option>
          <option class="text-gray-900" value="distancia">A distancia</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="n in 9" :key="n" class="h-40 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <!-- Sin resultados -->
    <div v-else-if="rows.length === 0" class="py-20 text-center text-gray-600 bg-white rounded-2xl border border-gray-100">
      <p class="text-4xl mb-2">📚</p><p>No se encontraron carreras</p>
    </div>

    <!-- Carreras agrupadas por nivel -->
    <div v-else>
      <!-- Ingenierías -->
      <div v-if="carrerasPorNivel.ingenieria.length > 0" class="mb-8">
        <h2 class="text-xl font-bold text-orange-700 mb-4 flex items-center gap-2">
          <span class="bg-orange-100 px-3 py-1 rounded-lg">🎓 Ingenierías</span>
          <span class="text-sm text-gray-600">({{ carrerasPorNivel.ingenieria.length }})</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div v-for="c in carrerasPorNivel.ingenieria" :key="c.id"
            class="bg-white rounded-2xl shadow-sm border-2 border-orange-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-1 rounded-lg">{{ c.codigo }}</span>
                  <span class="text-xs font-bold text-orange-700 bg-orange-100 px-2 py-1 rounded-lg">Ingeniería</span>
                </div>
                <h3 class="font-bold text-gray-900 leading-snug">{{ c.nombre }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ c.titulo_otorgado }}</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.duracion_semestres }}</p>
                <p class="text-gray-600">semestres</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.creditos_totales }}</p>
                <p class="text-gray-600">créditos</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900 capitalize">{{ c.modalidad?.slice(0,4) }}</p>
                <p class="text-gray-600">modalidad</p>
              </div>
            </div>
            <p class="text-xs text-gray-600 mt-3 truncate">🏛️ {{ c.facultad?.nombre ?? '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Licenciaturas -->
      <div v-if="carrerasPorNivel.licenciatura.length > 0" class="mb-8">
        <h2 class="text-xl font-bold text-purple-700 mb-4 flex items-center gap-2">
          <span class="bg-purple-100 px-3 py-1 rounded-lg">🎓 Licenciaturas</span>
          <span class="text-sm text-gray-600">({{ carrerasPorNivel.licenciatura.length }})</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div v-for="c in carrerasPorNivel.licenciatura" :key="c.id"
            class="bg-white rounded-2xl shadow-sm border-2 border-purple-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-1 rounded-lg">{{ c.codigo }}</span>
                  <span class="text-xs font-bold text-purple-700 bg-purple-100 px-2 py-1 rounded-lg">Licenciatura</span>
                </div>
                <h3 class="font-bold text-gray-900 leading-snug">{{ c.nombre }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ c.titulo_otorgado }}</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.duracion_semestres }}</p>
                <p class="text-gray-600">semestres</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.creditos_totales }}</p>
                <p class="text-gray-600">créditos</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900 capitalize">{{ c.modalidad?.slice(0,4) }}</p>
                <p class="text-gray-600">modalidad</p>
              </div>
            </div>
            <p class="text-xs text-gray-600 mt-3 truncate">🏛️ {{ c.facultad?.nombre ?? '—' }}</p>
          </div>
        </div>
      </div>

      <!-- TSU -->
      <div v-if="carrerasPorNivel.tsu.length > 0" class="mb-8">
        <h2 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
          <span class="bg-green-100 px-3 py-1 rounded-lg">🎓 Técnico Superior Universitario (TSU)</span>
          <span class="text-sm text-gray-600">({{ carrerasPorNivel.tsu.length }})</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div v-for="c in carrerasPorNivel.tsu" :key="c.id"
            class="bg-white rounded-2xl shadow-sm border-2 border-green-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-1 rounded-lg">{{ c.codigo }}</span>
                  <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-1 rounded-lg">TSU</span>
                </div>
                <h3 class="font-bold text-gray-900 leading-snug">{{ c.nombre }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ c.titulo_otorgado }}</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.duracion_semestres }}</p>
                <p class="text-gray-600">semestres</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900">{{ c.creditos_totales }}</p>
                <p class="text-gray-600">créditos</p>
              </div>
              <div class="bg-gray-50 rounded-xl py-2">
                <p class="font-bold text-gray-900 capitalize">{{ c.modalidad?.slice(0,4) }}</p>
                <p class="text-gray-600">modalidad</p>
              </div>
            </div>
            <p class="text-xs text-gray-600 mt-3 truncate">🏛️ {{ c.facultad?.nombre ?? '—' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="!loading && rows.length > 0" class="flex items-center justify-between mt-6 text-sm text-gray-500">
      <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
      <div class="flex gap-2">
        <button @click="cargar(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
          class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition">← Anterior</button>
        <button @click="cargar(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-1.5 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition">Siguiente →</button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading    = ref(false)
const rows       = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const filtros    = reactive({ buscar: '', modalidad: '', nivel: '' })

let timer
const buscarDebounced = () => { clearTimeout(timer); timer = setTimeout(() => cargar(1), 400) }

// Agrupar carreras por nivel
const carrerasPorNivel = computed(() => {
  return {
    ingenieria: rows.value.filter(c => c.nivel === 'ingenieria'),
    licenciatura: rows.value.filter(c => c.nivel === 'licenciatura'),
    tsu: rows.value.filter(c => c.nivel === 'tsu')
  }
})

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 100 } // Aumentamos para mostrar todas
    if (filtros.buscar)    params.buscar    = filtros.buscar
    if (filtros.modalidad) params.modalidad = filtros.modalidad
    if (filtros.nivel)     params.nivel     = filtros.nivel
    const { data } = await api.get('/carreras', { params })
    rows.value = data.data
    pagination.current_page = data.current_page
    pagination.last_page    = data.last_page
    pagination.total        = data.total
  } finally { loading.value = false }
}

onMounted(() => cargar())
</script>



