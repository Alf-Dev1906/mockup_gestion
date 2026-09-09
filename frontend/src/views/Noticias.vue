<template>
  <div class="noticias">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-5xl font-bold mb-4">Noticias Universitarias</h1>
        <p class="text-blue-100 text-lg">Mantente informado sobre lo que sucede en nuestra comunidad académica</p>
        
        <!-- Filtros -->
        <div class="flex gap-3 mt-8">
          <button @click="categoriaActiva = 'todas'" 
            :class="categoriaActiva === 'todas' ? 'bg-white text-blue-900' : 'bg-blue-800 text-white hover:bg-blue-700'"
            class="px-4 py-2 rounded-lg font-semibold transition">
            Todas
          </button>
          <button @click="categoriaActiva = 'academicas'" 
            :class="categoriaActiva === 'academicas' ? 'bg-white text-blue-900' : 'bg-blue-800 text-white hover:bg-blue-700'"
            class="px-4 py-2 rounded-lg font-semibold transition">
            Académicas
          </button>
          <button @click="categoriaActiva = 'investigacion'" 
            :class="categoriaActiva === 'investigacion' ? 'bg-white text-blue-900' : 'bg-blue-800 text-white hover:bg-blue-700'"
            class="px-4 py-2 rounded-lg font-semibold transition">
            Investigación
          </button>
          <button @click="categoriaActiva = 'eventos'" 
            :class="categoriaActiva === 'eventos' ? 'bg-white text-blue-900' : 'bg-blue-800 text-white hover:bg-blue-700'"
            class="px-4 py-2 rounded-lg font-semibold transition">
            Eventos
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Noticia destacada -->
      <div v-if="noticiaDestacada && categoriaActiva === 'todas'" class="mb-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="grid md:grid-cols-2">
            <div :class="noticiaDestacada.color" class="h-64 md:h-auto flex items-center justify-center">
              <span class="text-8xl">{{ noticiaDestacada.icono }}</span>
            </div>
            <div class="p-8 md:p-12 flex flex-col justify-center">
              <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full mb-4 w-fit">
                ⭐ DESTACADA
              </span>
              <span class="text-sm text-blue-600 font-semibold mb-2">{{ noticiaDestacada.fecha }}</span>
              <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ noticiaDestacada.titulo }}</h2>
              <p class="text-gray-600 text-lg mb-6">{{ noticiaDestacada.descripcionCompleta }}</p>
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition w-fit">
                Leer más →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de noticias -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="noticia in noticiasFiltradas" :key="noticia.id" 
          class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div :class="noticia.color" class="h-48 flex items-center justify-center relative">
            <span class="text-6xl">{{ noticia.icono }}</span>
            <span v-if="noticia.categoria" 
              class="absolute top-4 right-4 bg-white/90 backdrop-blur text-xs font-bold px-3 py-1 rounded-full"
              :class="getCategoriaColor(noticia.categoria)">
              {{ getCategoriaLabel(noticia.categoria) }}
            </span>
          </div>
          <div class="p-6">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm text-blue-600 font-semibold">{{ noticia.fecha }}</span>
              <span class="text-xs text-gray-500">📖 {{ noticia.tiempoLectura }} min</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">{{ noticia.titulo }}</h3>
            <p class="text-gray-600 mb-4 line-clamp-3">{{ noticia.descripcion }}</p>
            <button class="text-blue-600 hover:text-blue-800 font-semibold transition flex items-center gap-2">
              <span>Leer más</span>
              <span>→</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-if="noticiasFiltradas.length === 0" class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-4">📰</p>
        <p class="text-xl">No hay noticias en esta categoría</p>
      </div>

      <!-- Paginación simple -->
      <div v-if="noticiasFiltradas.length > 0" class="mt-12 text-center">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition">
          Cargar más noticias
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const categoriaActiva = ref('todas')

// Noticia destacada
const noticiaDestacada = ref({
  id: 0,
  titulo: 'Inauguración del Centro de Innovación Tecnológica',
  descripcion: 'Un espacio de vanguardia para impulsar la investigación y el desarrollo.',
  descripcionCompleta: 'La Universidad de Caracas inaugura su nuevo Centro de Innovación Tecnológica, un espacio equipado con tecnología de última generación que permitirá a estudiantes e investigadores desarrollar proyectos innovadores en áreas como inteligencia artificial, robótica y biotecnología.',
  fecha: '7 de septiembre, 2026',
  color: 'bg-gradient-to-br from-purple-500 via-blue-500 to-cyan-500',
  icono: '🚀',
  tiempoLectura: 5
})

// Noticias principales
const todasLasNoticias = ref([
  {
    id: 1,
    titulo: 'Nuevo Laboratorio de Robótica',
    descripcion: 'Inauguramos laboratorio de última generación para estudiantes de ingeniería con equipos de robótica colaborativa.',
    fecha: '1 de septiembre, 2026',
    color: 'bg-gradient-to-r from-blue-400 to-blue-600',
    icono: '🤖',
    categoria: 'academicas',
    tiempoLectura: 3
  },
  {
    id: 2,
    titulo: 'Convenio Internacional con Universidades Europeas',
    descripcion: 'Firmamos acuerdo con 15 universidades europeas para intercambio estudiantil y proyectos de investigación conjuntos.',
    fecha: '28 de agosto, 2026',
    color: 'bg-gradient-to-r from-green-400 to-green-600',
    icono: '🌍',
    categoria: 'academicas',
    tiempoLectura: 4
  },
  {
    id: 3,
    titulo: 'Investigación Premiada a Nivel Nacional',
    descripcion: 'Nuestros investigadores reciben premio nacional por proyecto innovador en salud pública y telemedicina.',
    fecha: '25 de agosto, 2026',
    color: 'bg-gradient-to-r from-purple-400 to-purple-600',
    icono: '🏆',
    categoria: 'investigacion',
    tiempoLectura: 6
  },
  {
    id: 4,
    titulo: 'Jornada de Admisión 2026-II',
    descripcion: 'Abiertas las inscripciones para el nuevo período académico. Ofertas en todas las carreras disponibles.',
    fecha: '20 de agosto, 2026',
    color: 'bg-gradient-to-r from-yellow-400 to-yellow-600',
    icono: '📚',
    categoria: 'eventos',
    tiempoLectura: 2
  },
  {
    id: 5,
    titulo: 'Seminario Internacional de Tecnología',
    descripcion: 'Expertos internacionales participarán en simposio sobre IA, educación y transformación digital.',
    fecha: '15 de agosto, 2026',
    color: 'bg-gradient-to-r from-red-400 to-red-600',
    icono: '💻',
    categoria: 'eventos',
    tiempoLectura: 3
  },
  {
    id: 6,
    titulo: 'Ceremonia de Graduación 2026',
    descripcion: 'Más de 500 estudiantes recibirán sus títulos en ceremonia especial. Transmisión en vivo disponible.',
    fecha: '10 de agosto, 2026',
    color: 'bg-gradient-to-r from-indigo-400 to-indigo-600',
    icono: '🎓',
    categoria: 'eventos',
    tiempoLectura: 4
  },
  {
    id: 7,
    titulo: 'Publicación en Revista Científica Internacional',
    descripcion: 'Equipo de investigación publica hallazgos sobre energías renovables en revista indexada de alto impacto.',
    fecha: '5 de agosto, 2026',
    color: 'bg-gradient-to-r from-teal-400 to-teal-600',
    icono: '🔬',
    categoria: 'investigacion',
    tiempoLectura: 5
  },
  {
    id: 8,
    titulo: 'Acreditación Internacional Renovada',
    descripcion: 'La universidad renueva su acreditación internacional por 5 años más, reconociendo excelencia académica.',
    fecha: '1 de agosto, 2026',
    color: 'bg-gradient-to-r from-pink-400 to-pink-600',
    icono: '✅',
    categoria: 'academicas',
    tiempoLectura: 3
  },
  {
    id: 9,
    titulo: 'Hackathon Universitario 2026',
    descripcion: 'Estudiantes de diferentes carreras competirán para resolver desafíos tecnológicos con premios en efectivo.',
    fecha: '28 de julio, 2026',
    color: 'bg-gradient-to-r from-orange-400 to-orange-600',
    icono: '⚡',
    categoria: 'eventos',
    tiempoLectura: 4
  }
])

// Noticias filtradas
const noticiasFiltradas = computed(() => {
  if (categoriaActiva.value === 'todas') {
    return todasLasNoticias.value
  }
  return todasLasNoticias.value.filter(n => n.categoria === categoriaActiva.value)
})

// Funciones helpers
const getCategoriaLabel = (categoria) => {
  const labels = {
    academicas: 'Académicas',
    investigacion: 'Investigación',
    eventos: 'Eventos'
  }
  return labels[categoria] || categoria
}

const getCategoriaColor = (categoria) => {
  const colors = {
    academicas: 'text-blue-700',
    investigacion: 'text-purple-700',
    eventos: 'text-green-700'
  }
  return colors[categoria] || 'text-gray-700'
}
</script>