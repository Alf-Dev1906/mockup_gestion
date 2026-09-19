<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()

const tareas = ref([])
const cargando = ref(false)
const timerInterval = ref(null)

// Cargar tareas
const cargarTareas = async () => {
  cargando.value = true
  try {
    const { data } = await api.get('/estudiante/tareas')
    tareas.value = data.data
  } catch (error) {
    toast.error('Error al cargar tareas')
  } finally {
    cargando.value = false
  }
}

// Funciones de formato
const formatoFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-VE', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatoTiempo = (segundos) => {
  if (segundos <= 0) return 'Tiempo agotado'
  const dias = Math.floor(segundos / 86400)
  const horas = Math.floor((segundos % 86400) / 3600)
  const mins = Math.floor((segundos % 3600) / 60)
  
  if (dias > 0) return `${dias}d ${horas}h restantes`
  if (horas > 0) return `${horas}h ${mins}m restantes`
  return `${mins}m restantes`
}

const getPorcentajeTiempo = (tarea) => {
  const total = 7 * 24 * 3600 // 7 días en segundos (asumiendo plazo estándar)
  const restante = tarea.tiempo_restante_seg
  const porcentaje = Math.min(Math.max((restante / total) * 100, 0), 100)
  return Math.round(porcentaje)
}

// Clases de estilo
const getBorderColor = (tarea) => {
  if (tarea.ya_entregue) return 'border-green-200'
  if (tarea.esta_vencida) return 'border-red-200'
  if (tarea.tiempo_restante_seg < 86400) return 'border-yellow-300'
  return 'border-gray-200'
}

const getHeaderGradient = (tarea) => {
  if (tarea.ya_entregue) return 'from-green-50 to-emerald-50'
  if (tarea.esta_vencida) return 'from-red-50 to-rose-50'
  if (tarea.tiempo_restante_seg < 86400) return 'from-yellow-50 to-amber-50'
  return 'from-indigo-50 to-blue-50'
}

const getBadgeClass = (tarea) => {
  if (tarea.ya_entregue) return 'bg-green-100 text-green-700 border border-green-300'
  if (tarea.esta_vencida) return 'bg-red-100 text-red-700 border border-red-300'
  if (tarea.tiempo_restante_seg < 86400) return 'bg-yellow-100 text-yellow-700 border border-yellow-300 animate-pulse'
  return 'bg-indigo-100 text-indigo-700 border border-indigo-300'
}

const getEstadoLabel = (tarea) => {
  if (tarea.ya_entregue) return '✅ Entregada'
  if (tarea.esta_vencida) return '⏰ Vencida'
  if (tarea.tiempo_restante_seg < 86400) return '⚠️ Urgente'
  return '📝 Pendiente'
}

const getIcono = (tarea) => {
  if (tarea.ya_entregue) return '✅'
  if (tarea.esta_vencida) return '⏰'
  if (tarea.tiempo_restante_seg < 86400) return '⚡'
  return '📝'
}

const getTiempoClasses = (tarea) => {
  if (tarea.esta_vencida) return 'bg-red-50'
  if (tarea.tiempo_restante_seg < 86400) return 'bg-yellow-50'
  return 'bg-green-50'
}

const getTiempoIconBg = (tarea) => {
  if (tarea.esta_vencida) return 'bg-red-100'
  if (tarea.tiempo_restante_seg < 86400) return 'bg-yellow-100'
  return 'bg-green-100'
}

const getTiempoIcono = (tarea) => {
  if (tarea.esta_vencida) return '⏰'
  if (tarea.tiempo_restante_seg < 86400) return '⏳'
  return '⏰'
}

const getTiempoLabel = (tarea) => {
  if (tarea.esta_vencida) return 'Plazo vencido'
  if (tarea.tiempo_restante_seg < 86400) return '¡Urgente!'
  return 'Tiempo restante'
}

const getTiempoTextClass = (tarea) => {
  if (tarea.esta_vencida) return 'text-red-700'
  if (tarea.tiempo_restante_seg < 86400) return 'text-yellow-700'
  return 'text-green-700'
}

const getProgressBarColor = (tarea) => {
  const porcentaje = getPorcentajeTiempo(tarea)
  if (porcentaje < 25) return 'bg-red-500'
  if (porcentaje < 50) return 'bg-yellow-500'
  return 'bg-green-500'
}

// Abrir modal de entrega
const abrirModalEntrega = (tarea) => {
  router.push(`/estudiante/tareas/${tarea.id}`)
}

// Iniciar timer
const iniciarTimer = () => {
  timerInterval.value = setInterval(() => {
    tareas.value.forEach(t => {
      if (t.tiempo_restante_seg > 0) t.tiempo_restante_seg--
    })
  }, 1000)
}

onMounted(() => {
  cargarTareas()
  iniciarTimer()
})

onUnmounted(() => {
  if (timerInterval.value) clearInterval(timerInterval.value)
})
</script>

<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📋 Mis Tareas</h1>
        <p class="text-gray-500 mt-1">Gestiona y entrega tus tareas antes de la fecha límite</p>
      </div>

      <div v-if="cargando" class="space-y-4">
        <div v-for="n in 3" :key="n" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 animate-pulse">
          <div class="h-6 bg-gray-200 rounded w-2/3 mb-4"></div>
          <div class="h-4 bg-gray-100 rounded w-full mb-2"></div>
          <div class="h-4 bg-gray-100 rounded w-5/6"></div>
        </div>
      </div>

      <div v-else-if="tareas.length === 0" class="text-center py-16 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl border-2 border-dashed border-gray-300">
        <div class="text-6xl mb-4">📚</div>
        <p class="text-xl font-semibold text-gray-700 mb-2">No hay tareas disponibles</p>
        <p class="text-gray-500">Tus profesores aún no han publicado tareas</p>
      </div>

      <div v-else class="space-y-5">
        <div v-for="tarea in tareas" :key="tarea.id" 
          class="bg-white rounded-2xl shadow-md border-2 hover:shadow-xl transition-all duration-300 overflow-hidden"
          :class="getBorderColor(tarea)">
          
          <!-- Header con gradiente -->
          <div class="px-6 py-4 bg-gradient-to-r" :class="getHeaderGradient(tarea)">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span class="text-2xl">{{ getIcono(tarea) }}</span>
                  <h3 class="font-bold text-xl text-gray-900">{{ tarea.titulo }}</h3>
                </div>
                <div class="flex items-center gap-4 text-sm">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/80 text-gray-700">
                    📚 {{ tarea.materia }}
                  </span>
                  <span v-if="tarea.puntos_totales" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/80 text-indigo-700">
                    🎯 {{ tarea.puntos_totales }} pts
                  </span>
                </div>
              </div>
              
              <div class="flex-shrink-0">
                <span class="px-4 py-2 rounded-xl text-sm font-bold shadow-sm" :class="getBadgeClass(tarea)">
                  {{ getEstadoLabel(tarea) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Contenido -->
          <div class="px-6 py-5">
            <!-- Descripción -->
            <div v-if="tarea.descripcion" class="mb-4">
              <p class="text-sm text-gray-700 leading-relaxed">{{ tarea.descripcion }}</p>
            </div>

            <!-- Información de fecha y tiempo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <span class="text-lg">📅</span>
                </div>
                <div>
                  <p class="text-xs text-gray-500 font-semibold uppercase">Fecha límite</p>
                  <p class="text-sm font-bold text-gray-900">{{ formatoFecha(tarea.fecha_limite) }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3 p-3 rounded-xl" :class="getTiempoClasses(tarea)">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :class="getTiempoIconBg(tarea)">
                  <span class="text-lg">{{ getTiempoIcono(tarea) }}</span>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase" :class="getTiempoTextClass(tarea)">
                    {{ getTiempoLabel(tarea) }}
                  </p>
                  <p class="text-sm font-bold" :class="getTiempoTextClass(tarea)">
                    {{ formatoTiempo(tarea.tiempo_restante_seg) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Barra de progreso de tiempo (solo si no está vencida ni entregada) -->
            <div v-if="!tarea.esta_vencida && !tarea.ya_entregue && tarea.tiempo_restante_seg > 0" class="mb-4">
              <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                <span class="font-semibold">Tiempo disponible</span>
                <span class="font-mono">{{ getPorcentajeTiempo(tarea) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div 
                  class="h-2 rounded-full transition-all duration-500"
                  :class="getProgressBarColor(tarea)"
                  :style="{ width: getPorcentajeTiempo(tarea) + '%' }"
                />
              </div>
            </div>
          </div>

          <!-- Footer con acciones -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-500">
              <span v-if="tarea.ya_entregue" class="inline-flex items-center gap-1 text-green-600 font-semibold">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Entrega realizada
              </span>
              <span v-else-if="tarea.esta_vencida" class="inline-flex items-center gap-1 text-red-600 font-semibold">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                Plazo vencido
              </span>
              <span v-else class="text-gray-600">
                Pendiente de entrega
              </span>
            </div>

            <div class="flex gap-2">
              <button 
                v-if="tarea.ya_entregue"
                @click="$router.push(`/estudiante/tareas/${tarea.id}/mi-entrega`)"
                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                Ver mi entrega
              </button>
              
              <button 
                v-else-if="!tarea.esta_vencida"
                @click="abrirModalEntrega(tarea)"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                Entregar tarea
              </button>
              
              <button 
                v-else
                disabled
                class="px-5 py-2.5 bg-gray-300 text-gray-600 text-sm font-bold rounded-xl cursor-not-allowed flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                </svg>
                Vencida
              </button>

              <button 
                @click="$router.push(`/estudiante/tareas/${tarea.id}`)"
                class="px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition border-2 border-gray-300 hover:border-gray-400">
                Ver detalles →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
