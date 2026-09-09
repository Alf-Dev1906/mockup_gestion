<template>
  <div class="carreras">
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-5xl font-bold mb-4">Nuestras Carreras</h1>
        <p class="text-blue-100 text-lg">Programas académicos diseñados para tu futuro profesional</p>
        <div class="flex gap-4 mt-6 text-sm">
          <div class="bg-white/10 backdrop-blur px-4 py-2 rounded-lg">
            <span class="font-bold text-2xl">{{ stats.total }}</span>
            <span class="ml-2">Carreras</span>
          </div>
          <div class="bg-white/10 backdrop-blur px-4 py-2 rounded-lg">
            <span class="font-bold text-2xl">{{ stats.ingenierias }}</span>
            <span class="ml-2">Ingenierías</span>
          </div>
          <div class="bg-white/10 backdrop-blur px-4 py-2 rounded-lg">
            <span class="font-bold text-2xl">{{ stats.licenciaturas }}</span>
            <span class="ml-2">Licenciaturas</span>
          </div>
          <div class="bg-white/10 backdrop-blur px-4 py-2 rounded-lg">
            <span class="font-bold text-2xl">{{ stats.tsus }}</span>
            <span class="ml-2">TSU</span>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Tabs de navegación -->
      <div class="flex gap-2 mb-8 border-b border-gray-200">
        <button @click="nivelActivo = 'todas'" 
          :class="nivelActivo === 'todas' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
          class="px-6 py-3 font-semibold transition">
          Todas ({{ stats.total }})
        </button>
        <button @click="nivelActivo = 'ingenieria'" 
          :class="nivelActivo === 'ingenieria' ? 'border-b-2 border-orange-600 text-orange-600' : 'text-gray-500'"
          class="px-6 py-3 font-semibold transition">
          Ingenierías ({{ stats.ingenierias }})
        </button>
        <button @click="nivelActivo = 'licenciatura'" 
          :class="nivelActivo === 'licenciatura' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500'"
          class="px-6 py-3 font-semibold transition">
          Licenciaturas ({{ stats.licenciaturas }})
        </button>
        <button @click="nivelActivo = 'tsu'" 
          :class="nivelActivo === 'tsu' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-500'"
          class="px-6 py-3 font-semibold transition">
          TSU ({{ stats.tsus }})
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="n in 6" :key="n" class="h-64 bg-gray-100 rounded-2xl animate-pulse" />
      </div>

      <!-- Carreras -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="carrera in carrerasFiltradas" :key="carrera.id" 
          class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition border-2"
          :class="getBorderClass(carrera.nivel)">
          <div class="flex items-start justify-between mb-4">
            <div :class="getIconBgClass(carrera.nivel)" class="w-16 h-16 rounded-2xl flex items-center justify-center">
              <span class="text-3xl">{{ getIcono(carrera.nombre) }}</span>
            </div>
            <span :class="getBadgeClass(carrera.nivel)" class="text-xs font-bold px-3 py-1 rounded-lg">
              {{ getNivelLabel(carrera.nivel) }}
            </span>
          </div>
          
          <h3 class="text-xl font-bold text-gray-800 mb-2">{{ carrera.nombre }}</h3>
          <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ carrera.titulo_otorgado }}</p>
          
          <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-gray-50 rounded-lg p-2 text-center">
              <p class="text-lg font-bold text-gray-900">{{ carrera.duracion_semestres }}</p>
              <p class="text-xs text-gray-500">Semestres</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
              <p class="text-lg font-bold text-gray-900">{{ carrera.creditos_totales }}</p>
              <p class="text-xs text-gray-500">Créditos</p>
            </div>
          </div>
          
          <div class="text-xs text-gray-500 mb-4">
            <p>🏛️ {{ carrera.facultad?.nombre || '—' }}</p>
            <p class="mt-1">📍 {{ carrera.modalidad ? carrera.modalidad.charAt(0).toUpperCase() + carrera.modalidad.slice(1) : '—' }}</p>
          </div>
          
          <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-2.5 rounded-xl font-semibold transition shadow-md">
            Más Información
          </button>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-if="!loading && carrerasFiltradas.length === 0" class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-4">📚</p>
        <p class="text-xl">No se encontraron carreras</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const carreras = ref([])
const nivelActivo = ref('todas')

// Estadísticas
const stats = computed(() => ({
  total: carreras.value.length,
  ingenierias: carreras.value.filter(c => c.nivel === 'ingenieria').length,
  licenciaturas: carreras.value.filter(c => c.nivel === 'licenciatura').length,
  tsus: carreras.value.filter(c => c.nivel === 'tsu').length
}))

// Carreras filtradas según el tab activo
const carrerasFiltradas = computed(() => {
  if (nivelActivo.value === 'todas') return carreras.value
  return carreras.value.filter(c => c.nivel === nivelActivo.value)
})

// Funciones de estilo
const getNivelLabel = (nivel) => {
  const labels = { tsu: 'TSU', licenciatura: 'Licenciatura', ingenieria: 'Ingeniería' }
  return labels[nivel] || nivel
}

const getBadgeClass = (nivel) => {
  const classes = {
    tsu: 'text-green-700 bg-green-100',
    licenciatura: 'text-purple-700 bg-purple-100',
    ingenieria: 'text-orange-700 bg-orange-100'
  }
  return classes[nivel] || 'text-gray-700 bg-gray-100'
}

const getBorderClass = (nivel) => {
  const classes = {
    tsu: 'border-green-200',
    licenciatura: 'border-purple-200',
    ingenieria: 'border-orange-200'
  }
  return classes[nivel] || 'border-gray-200'
}

const getIconBgClass = (nivel) => {
  const classes = {
    tsu: 'bg-green-100',
    licenciatura: 'bg-purple-100',
    ingenieria: 'bg-orange-100'
  }
  return classes[nivel] || 'bg-gray-100'
}

const getIcono = (nombre) => {
  if (nombre.toLowerCase().includes('sistema') || nombre.toLowerCase().includes('informática')) return '💻'
  if (nombre.toLowerCase().includes('medicina') || nombre.toLowerCase().includes('salud')) return '�'
  if (nombre.toLowerCase().includes('derecho')) return '⚖️'
  if (nombre.toLowerCase().includes('administración') || nombre.toLowerCase().includes('empresa')) return '📊'
  if (nombre.toLowerCase().includes('arquitectura')) return '🏛️'
  if (nombre.toLowerCase().includes('psicología')) return '🧠'
  if (nombre.toLowerCase().includes('economía')) return '📈'
  if (nombre.toLowerCase().includes('comunicación')) return '🎤'
  if (nombre.toLowerCase().includes('civil')) return '🏗️'
  if (nombre.toLowerCase().includes('biología')) return '🔬'
  if (nombre.toLowerCase().includes('veterinaria') || nombre.toLowerCase().includes('animal')) return '🐾'
  if (nombre.toLowerCase().includes('alimento')) return '🍽️'
  if (nombre.toLowerCase().includes('industrial')) return '�'
  if (nombre.toLowerCase().includes('enfermería')) return '💉'
  if (nombre.toLowerCase().includes('turismo')) return '✈️'
  if (nombre.toLowerCase().includes('diseño')) return '🎨'
  return '🎓'
}

// Cargar carreras desde la API
async function cargarCarreras() {
  loading.value = true
  try {
    const { data } = await axios.get('http://localhost:8000/api/public/carreras', {
      params: { per_page: 100 }
    })
    carreras.value = data.data
  } catch (error) {
    console.error('Error al cargar carreras:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => cargarCarreras())
</script>