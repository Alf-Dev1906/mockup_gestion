<template>
  <div class="home">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-blue-900 to-blue-700 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">Bienvenido a la Universidad de Caracas</h1>
        <p class="text-xl text-blue-100 mb-8">Formando líderes con excelencia académica y compromiso social</p>
        <div class="flex justify-center space-x-4">
          <button 
            @click="handleInscripcion"
            :disabled="verificandoSolicitud"
            class="bg-yellow-500 hover:bg-yellow-400 text-blue-900 px-8 py-3 rounded-lg font-semibold transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!verificandoSolicitud">Inscríbete Ahora</span>
            <span v-else class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Verificando...
            </span>
          </button>
          <router-link 
            to="/carreras"
            class="border-2 border-white hover:bg-white hover:text-blue-900 px-8 py-3 rounded-lg font-semibold transition inline-block"
          >
            Conoce Más
          </router-link>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white py-12">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
          <div class="p-6">
            <div class="text-4xl font-bold text-blue-900 mb-2">10,000+</div>
            <div class="text-gray-600">Estudiantes</div>
          </div>
          <div class="p-6">
            <div class="text-4xl font-bold text-blue-900 mb-2">{{ stats.carreras }}</div>
            <div class="text-gray-600">Carreras</div>
          </div>
          <div class="p-6">
            <div class="text-4xl font-bold text-blue-900 mb-2">200+</div>
            <div class="text-gray-600">Profesores</div>
          </div>
          <div class="p-6">
            <div class="text-4xl font-bold text-blue-900 mb-2">50+</div>
            <div class="text-gray-600">Años de Historia</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Carreras Section -->
    <div class="bg-gray-50 py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Nuestras Carreras</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">💻</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Ingeniería de Sistemas</h3>
            <p class="text-gray-600 mb-4">Desarrollo de software y sistemas informáticos de vanguardia.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">🏥</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Medicina</h3>
            <p class="text-gray-600 mb-4">Formación integral en ciencias de la salud y atención médica.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-3xl">⚖️</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Derecho</h3>
            <p class="text-gray-600 mb-4">Estudios jurídicos con enfoque en justicia y derechos humanos.</p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Más información →</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Noticias Section -->
    <div class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Noticias Recientes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-gray-50 rounded-lg overflow-hidden">
            <div class="h-48 bg-gradient-to-r from-blue-400 to-blue-600"></div>
            <div class="p-6">
              <span class="text-sm text-blue-600 font-semibold">1 de septiembre, 2024</span>
              <h3 class="text-xl font-bold text-gray-800 mt-2 mb-2">Nuevo Laboratorio de Robótica</h3>
              <p class="text-gray-600">Inauguramos laboratorio de última generación para estudiantes de ingeniería.</p>
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg overflow-hidden">
            <div class="h-48 bg-gradient-to-r from-green-400 to-green-600"></div>
            <div class="p-6">
              <span class="text-sm text-blue-600 font-semibold">28 de agosto, 2024</span>
              <h3 class="text-xl font-bold text-gray-800 mt-2 mb-2">Convenio Internacional</h3>
              <p class="text-gray-600">Firmamos acuerdo con universidades europeas para intercambio estudiantil.</p>
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg overflow-hidden">
            <div class="h-48 bg-gradient-to-r from-purple-400 to-purple-600"></div>
            <div class="p-6">
              <span class="text-sm text-blue-600 font-semibold">25 de agosto, 2024</span>
              <h3 class="text-xl font-bold text-gray-800 mt-2 mb-2">Investigación Premiada</h3>
              <p class="text-gray-600">Nuestros investigadores reciben premio nacional por proyecto en salud.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-blue-900 text-white py-16">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">¿Listo para comenzar tu futuro?</h2>
        <p class="text-xl text-blue-100 mb-8">Únete a nuestra comunidad académica y alcanza tus metas profesionales.</p>
        <button 
          @click="handleInscripcion"
          :disabled="verificandoSolicitud"
          class="bg-yellow-500 hover:bg-yellow-400 text-blue-900 px-8 py-3 rounded-lg font-semibold transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!verificandoSolicitud">Solicitar Admisión</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Verificando...
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const stats = ref({ carreras: 0 })
const verificandoSolicitud = ref(false)

async function cargarStats() {
  try {
    const { data } = await axios.get('http://localhost:8000/api/public/carreras', {
      params: { per_page: 1 }
    })
    stats.value.carreras = data.total
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
    stats.value.carreras = 40 // Fallback
  }
}

/**
 * Maneja el clic en los botones de inscripción
 * Si el usuario está autenticado, va al wizard de admisión
 * Si no está autenticado, va al registro público
 */
async function handleInscripcion() {
  if (verificandoSolicitud.value) return // Evitar clicks múltiples
  
  if (auth.isAuthenticated && auth.userRole === 'estudiante') {
    // Usuario autenticado (estudiante) -> verificar si ya tiene solicitud
    verificandoSolicitud.value = true
    try {
      // Intentar obtener su solicitud actual
      const { data } = await api.get('/estudiante/admision/solicitud')
      
      if (data.solicitud && data.solicitud.estado !== 'borrador') {
        // Ya tiene solicitud enviada -> ir al dashboard para ver su estado
        router.push('/estudiante/dashboard')
      } else {
        // Solicitud en borrador o no existe -> ir al wizard
        router.push('/estudiante/admision')
      }
    } catch (error) {
      // Si hay error (ej: no autenticado correctamente), ir al wizard por defecto
      console.error('Error al verificar solicitud:', error)
      router.push('/estudiante/admision')
    } finally {
      verificandoSolicitud.value = false
    }
  } else if (auth.isAuthenticated) {
    // Usuario autenticado pero no es estudiante -> mostrar mensaje
    alert('Esta opción solo está disponible para estudiantes. Por favor, cierra sesión e intenta de nuevo.')
  } else {
    // Usuario no autenticado -> ir al registro público
    router.push('/registro')
  }
}

onMounted(() => cargarStats())
</script>