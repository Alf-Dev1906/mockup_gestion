<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const horarioId = route.params.horarioId
const session = ref(null)
const cargando = ref(false)
const tiempoRestante = ref(0)
let countdownInterval = null

// Cargar sesión activa
const cargarSesion = async () => {
  cargando.value = true
  try {
    const { data } = await api.get(`/profesor/asistencia/sesion-activa/${horarioId}`)
    if (data.success) {
      session.value = data.data
      tiempoRestante.value = data.data.session.tiempo_restante_seg
    }
  } catch (error) {
    if (error.response?.status === 404) {
      session.value = null
    } else {
      console.error('Error cargando sesión:', error)
    }
  } finally {
    cargando.value = false
  }
}

// Abrir nueva sesión
const abrirSesion = async () => {
  cargando.value = true
  try {
    const { data } = await api.post('/profesor/asistencia/abrir-sesion', {
      horario_id: parseInt(horarioId),
      duracion_minutos: 10,
    })
    
    if (data.success) {
      toast.success('Sesión abierta correctamente')
      // Recargar para obtener la sesión completa con estadísticas
      await cargarSesion()
    }
  } catch (error) {
    const mensaje = error.response?.data?.message || 'Error al abrir sesión'
    toast.error(mensaje)
  } finally {
    cargando.value = false
  }
}

// Regenerar código
const regenerarCodigo = async () => {
  if (!confirm('¿Regenerar código? Se creará un nuevo código para esta sesión.')) return
  
  cargando.value = true
  try {
    // Cerrar la sesión actual
    if (session.value?.session?.id) {
      await api.post(`/profesor/asistencia/cerrar-sesion/${session.value.session.id}`)
    }
    
    // Abrir nueva sesión
    await abrirSesion()
  } catch (error) {
    toast.error('Error al regenerar código')
  } finally {
    cargando.value = false
  }
}

// Cerrar sesión
const cerrarSesion = async () => {
  if (!confirm('¿Cerrar sesión de asistencia? Los estudiantes que no marcaron asistencia quedarán como ausentes.')) return
  
  cargando.value = true
  try {
    await api.post(`/profesor/asistencia/cerrar-sesion/${session.value.session.id}`)
    toast.success('Sesión cerrada correctamente')
    session.value = null
  } catch (error) {
    toast.error(error.response?.data?.message || 'Error al cerrar sesión')
  } finally {
    cargando.value = false
  }
}

// Formato de tiempo
const formatoTiempo = (segundos) => {
  const m = Math.floor(segundos / 60)
  const s = segundos % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

// Verificar si expira pronto
const expiraPoco = computed(() => tiempoRestante.value <= 30 && tiempoRestante.value > 0)

// Datos de la sesión
const codigoFormateado = computed(() => session.value?.session?.codigo_formateado || '')
const presentes = computed(() => session.value?.estadisticas?.presentes || 0)
const totalInscritos = computed(() => session.value?.estadisticas?.total_inscritos || 0)
const porcentaje = computed(() => {
  if (totalInscritos.value === 0) return 0
  return Math.round((presentes.value / totalInscritos.value) * 100)
})
const asistencias = computed(() => session.value?.listado || [])

// Iniciar
onMounted(async () => {
  await cargarSesion()
  
  // Timer cada segundo
  countdownInterval = setInterval(() => {
    if (tiempoRestante.value > 0) {
      tiempoRestante.value--
    }
    
    // Recargar datos cada 5 segundos si hay sesión activa
    if (session.value && tiempoRestante.value % 5 === 0) {
      cargarSesion()
    }
  }, 1000)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Título -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📊 Panel de Asistencia</h1>
          <p class="text-gray-500 dark:text-gray-400">Genera códigos y controla la asistencia en tiempo real</p>
        </div>
        <button 
          @click="router.back()"
          class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition"
        >
          ← Volver
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="cargando && !session" class="text-center py-12">
      <div class="inline-block animate-spin text-4xl">⏳</div>
      <p class="mt-4 text-gray-500 dark:text-gray-400">Cargando...</p>
    </div>

    <!-- Si no hay sesión activa -->
    <div v-else-if="!session" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
      <p class="text-6xl mb-4">🕒</p>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No hay sesión activa</h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Abre una sesión para generar un código dinámico de asistencia</p>
      
      <button 
        @click="abrirSesion"
        :disabled="cargando"
        class="bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white font-bold py-4 px-8 rounded-xl transition shadow-lg hover:shadow-xl hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed"
      >
        <span class="mr-2">➕</span> 
        {{ cargando ? 'Abriendo...' : 'Abrir nueva sesión' }}
      </button>
    </div>

    <!-- Si hay sesión activa -->
    <div v-else class="space-y-6">
      <!-- Código grande para proyectar -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-4 border-emerald-200 dark:border-emerald-700 overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white px-6 py-3">
          <div class="flex justify-between items-center">
            <h3 class="font-semibold text-lg">CÓDIGO DE ASISTENCIA</h3>
            <div 
              class="flex items-center gap-2 text-sm font-mono px-3 py-1 rounded-full"
              :class="expiraPoco ? 'bg-red-500 animate-pulse' : 'bg-white/20'"
            >
              ⏰ {{ formatoTiempo(tiempoRestante) }}
            </div>
          </div>
        </div>
        
        <div class="p-8 bg-gradient-to-br from-white to-emerald-50 dark:from-gray-800 dark:to-gray-700 text-center">
          <div 
            class="text-6xl md:text-8xl font-black tracking-widest mb-4 font-mono"
            :class="expiraPoco ? 'text-red-600 dark:text-red-400 animate-pulse' : 'text-gray-900 dark:text-white'"
          >
            {{ codigoFormateado }}
          </div>
          <p class="text-gray-500 dark:text-gray-400 text-sm">
            Los estudiantes deben ingresar este código en su aplicación
          </p>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4">
          <button 
            @click="regenerarCodigo"
            :disabled="cargando"
            class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 font-semibold rounded-lg transition text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            🔄 Regenerar código
          </button>
          <button 
            @click="cerrarSesion"
            :disabled="cargando"
            class="px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-800 dark:text-red-300 font-semibold rounded-lg transition text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ⏹️ Cerrar sesión
          </button>
        </div>
      </div>

      <!-- Contadores en tiempo real -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Presentes</p>
          <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ presentes }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Total</p>
          <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ totalInscritos }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Asistencia</p>
          <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ porcentaje }}%</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Pendientes</p>
          <p class="text-3xl font-bold text-gray-600 dark:text-gray-400">{{ totalInscritos - presentes }}</p>
        </div>
      </div>

      <!-- Barra de progreso -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex justify-between text-sm mb-2">
          <span class="font-semibold text-gray-900 dark:text-white">Progreso de asistencia</span>
          <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ porcentaje }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
          <div 
            class="bg-gradient-to-r from-green-400 to-emerald-500 h-3 rounded-full transition-all duration-500"
            :style="{ width: porcentaje + '%' }" 
          />
        </div>
      </div>

      <!-- Lista de presentes -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="font-bold text-gray-900 dark:text-white">Estudiantes que marcaron asistencia</h3>
          <span class="text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full">
            {{ presentes }} / {{ totalInscritos }}
          </span>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto scrollbar-thin">
          <div v-if="asistencias.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500">
            <p class="text-4xl mb-2">👤</p>
            <p>Ningún estudiante ha marcado asistencia aún</p>
          </div>

          <div 
            v-for="a in asistencias" 
            :key="a.id" 
            class="px-6 py-4 flex items-center gap-4 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition"
          >
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-lg font-bold text-emerald-700 dark:text-emerald-300">
              {{ a.estudiante.charAt(0).toUpperCase() }}
            </div>
            
            <div class="flex-1">
              <p class="font-semibold text-gray-900 dark:text-white">{{ a.estudiante }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Registrado: {{ a.registrado_at }}
              </p>
            </div>

            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-bold rounded-full">
              ✓ Presente
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
