<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()

const tareas = ref([])
const cargando = ref(false)
const tareaSeleccionada = ref(null)
const mostrarModal = ref(false)

// Cargar tareas
const cargarTareas = async () => {
  cargando.value = true
  try {
    const { data } = await api.get('/profesor/tareas')
    tareas.value = data.data
  } catch (error) {
    toast.error('Error al cargar tareas')
  } finally {
    cargando.value = false
  }
}

// Ver detalles de tarea
const verDetalles = (tarea) => {
  tareaSeleccionada.value = tarea
  mostrarModal.value = true
}

// Cerrar modal
const cerrarModal = () => {
  mostrarModal.value = false
  tareaSeleccionada.value = null
}

// Formato de fecha
const formatoFecha = (fecha) => {
  return new Date(fecha).toLocaleString('es-VE')
}

const formatoFechaCorta = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-VE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const formatoFechaCompleta = (fecha) => {
  return new Date(fecha).toLocaleString('es-VE', { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric',
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

// Verificar si está vencida
const estaVencida = (tarea) => {
  return new Date(tarea.fecha_limite) < new Date()
}

// Verificar si está próxima a vencer (menos de 3 días)
const estaProxima = (tarea) => {
  const diff = new Date(tarea.fecha_limite) - new Date()
  const dias = diff / (1000 * 60 * 60 * 24)
  return dias > 0 && dias < 3
}

// Obtener badge de estado
const getEstadoBadge = (tarea) => {
  if (estaVencida(tarea)) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
  if (estaProxima(tarea)) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'
  return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
}

const getEstadoLabel = (tarea) => {
  if (estaVencida(tarea)) return 'Vencida'
  if (estaProxima(tarea)) return 'Próxima'
  return 'Activa'
}

// Calcular porcentaje de calificadas
const getPorcentajeCalificado = (tarea) => {
  if (!tarea.entregas_total || tarea.entregas_total === 0) return 0
  return Math.round((tarea.entregas_calificadas / tarea.entregas_total) * 100)
}

// Acciones
const editarTarea = (tarea) => {
  console.log('Editar tarea:', tarea.id)
  toast.info('Función de edición en desarrollo')
}

const verEntregas = (tarea) => {
  console.log('Ver entregas:', tarea.id)
  toast.info('Función de entregas en desarrollo')
  cerrarModal()
}

onMounted(() => {
  cargarTareas()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📋 Gestión de Tareas</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Administra las tareas y revisa las entregas de tus estudiantes</p>
      </div>
      <button 
        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-lg hover:shadow-xl flex items-center gap-2"
      >
        <span class="text-xl">+</span> Nueva Tarea
      </button>
    </div>

    <!-- Loading -->
    <div v-if="cargando" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="n in 6" :key="n" class="h-64 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse"></div>
    </div>

    <!-- Empty state -->
    <div v-else-if="tareas.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 border-dashed border-gray-300 dark:border-gray-700 p-16 text-center">
      <div class="text-6xl mb-4">📝</div>
      <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No hay tareas creadas</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Comienza creando tu primera tarea para tus estudiantes</p>
      <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-lg">
        + Crear Primera Tarea
      </button>
    </div>

    <!-- Grid de tareas -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="tarea in tareas" 
        :key="tarea.id" 
        class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:scale-[1.02] cursor-pointer"
        @click="verDetalles(tarea)"
      >
        <!-- Header de la tarjeta -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700"
          :class="estaVencida(tarea) ? 'bg-red-50 dark:bg-red-900/10' : estaProxima(tarea) ? 'bg-yellow-50 dark:bg-yellow-900/10' : 'bg-emerald-50 dark:bg-emerald-900/10'"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
              <h3 class="font-bold text-lg text-gray-900 dark:text-white line-clamp-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                {{ tarea.titulo }}
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ tarea.materia }}</p>
            </div>
            <span 
              class="px-3 py-1 rounded-full text-xs font-bold shrink-0"
              :class="getEstadoBadge(tarea)"
            >
              {{ getEstadoLabel(tarea) }}
            </span>
          </div>
          
          <!-- Fecha límite destacada -->
          <div class="flex items-center gap-2 text-sm">
            <span class="text-2xl">{{ estaVencida(tarea) ? '🔴' : estaProxima(tarea) ? '⏰' : '📅' }}</span>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Vence</p>
              <p class="font-bold" :class="estaVencida(tarea) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
                {{ formatoFechaCorta(tarea.fecha_limite) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="p-6">
          <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="text-center">
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ tarea.entregas_total || 0 }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Entregas</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ tarea.entregas_calificadas || 0 }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Calificadas</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ (tarea.entregas_total || 0) - (tarea.entregas_calificadas || 0) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pendientes</p>
            </div>
          </div>

          <!-- Barra de progreso -->
          <div class="mb-4">
            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
              <span>Progreso de calificación</span>
              <span class="font-bold">{{ getPorcentajeCalificado(tarea) }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div 
                class="bg-gradient-to-r from-blue-500 to-emerald-500 h-2 rounded-full transition-all duration-500"
                :style="{ width: getPorcentajeCalificado(tarea) + '%' }"
              ></div>
            </div>
          </div>

          <!-- Meta info -->
          <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>📊 {{ tarea.peso_calificacion }}% de la nota</span>
            <span>🎯 Max: {{ tarea.nota_maxima }}pts</span>
          </div>
        </div>

        <!-- Footer con acciones -->
        <div class="px-6 pb-6 flex gap-2">
          <button 
            @click.stop="editarTarea(tarea)"
            class="flex-1 text-center text-sm font-semibold py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
          >
            ✏️ Editar
          </button>
          <button 
            @click.stop="verEntregas(tarea)"
            class="flex-1 text-center text-sm font-semibold py-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition"
          >
            📥 Ver entregas
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de detalles (simplificado) -->
    <div 
      v-if="mostrarModal && tareaSeleccionada" 
      class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="cerrarModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full p-8 transform transition-all">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ tareaSeleccionada.titulo }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ tareaSeleccionada.materia }}</p>
          </div>
          <button 
            @click="cerrarModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-3xl leading-none transition"
          >
            ×
          </button>
        </div>
        
        <div class="space-y-6">
          <div v-if="tareaSeleccionada.descripcion" class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Descripción:</p>
            <p class="text-gray-600 dark:text-gray-400">{{ tareaSeleccionada.descripcion }}</p>
          </div>
          
          <div class="grid grid-cols-2 gap-6">
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4">
              <p class="text-sm text-emerald-700 dark:text-emerald-300 mb-1">📅 Fecha de apertura</p>
              <p class="font-bold text-gray-900 dark:text-white">{{ formatoFechaCompleta(tareaSeleccionada.fecha_apertura) }}</p>
            </div>
            <div :class="estaVencida(tareaSeleccionada) ? 'bg-red-50 dark:bg-red-900/20' : 'bg-blue-50 dark:bg-blue-900/20'" class="rounded-xl p-4">
              <p class="text-sm mb-1" :class="estaVencida(tareaSeleccionada) ? 'text-red-700 dark:text-red-300' : 'text-blue-700 dark:text-blue-300'">⏰ Fecha límite</p>
              <p class="font-bold text-gray-900 dark:text-white">{{ formatoFechaCompleta(tareaSeleccionada.fecha_limite) }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
              <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ tareaSeleccionada.entregas_total || 0 }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total entregas</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
              <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ tareaSeleccionada.entregas_calificadas || 0 }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Calificadas</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
              <p class="text-3xl font-bold text-gray-600 dark:text-gray-400">{{ (tareaSeleccionada.entregas_total || 0) - (tareaSeleccionada.entregas_calificadas || 0) }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pendientes</p>
            </div>
          </div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
          <button 
            @click="cerrarModal"
            class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition"
          >
            Cerrar
          </button>
          <button 
            @click="verEntregas(tareaSeleccionada)"
            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-lg"
          >
            Ver todas las entregas →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
