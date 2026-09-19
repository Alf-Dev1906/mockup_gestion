<script setup>
import { computed } from 'vue'

const props = defineProps({
  blocked: {
    type: Boolean,
    default: false,
  },
  reason: {
    type: String,
    default: '',
  },
  autoEnviado: {
    type: Boolean,
    default: false,
  },
  tiempoRestante: {
    type: Number,
    default: 0,
  },
  duracionTotal: {
    type: Number,
    default: 0,
  },
  incidencias: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['restart'])

const esPorTiempo = computed(() => props.reason === 'tiempo' || props.reason === 'timeout')
const esPorAdvertencias = computed(() => props.reason === 'advertencias' || props.reason === 'bloqueado')
const esPorFraude = computed(() => props.reason === 'fraude')

/**
 * Mensaje principal según razón de bloqueo
 */
const mensajePrincipal = computed(() => {
  if (esPorTiempo.value) return '¡Tiempo agotado!'
  if (esPorFraude.value) return '¡Intento de fraude detectado!'
  if (esPorAdvertencias.value) return '¡Examenes bloqueado!'
  return 'Examen finalizado'
})

/**
 * Color del fondo según razón
 */
const colorFondo = computed(() => {
  if (esPorTiempo.value) return 'from-red-900 to-red-800'
  if (esPorFraude.value) return 'from-red-950 to-red-900'
  if (esPorAdvertencias.value) return 'from-orange-900 to-orange-800'
  return 'from-gray-800 to-gray-900'
})

/**
 * Icono según razón
 */
const iconoGrande = computed(() => {
  if (esPorTiempo.value) return '⏰'
  if (esPorFraude.value) return '🚫'
  if (esPorAdvertencias.value) return '⚠️'
  return '✅'
})

/**
 * Tiempo restante formateado
 */
const tiempoRestanteFormateado = computed(() => {
  const minutos = Math.floor(props.tiempoRestante / 60)
  const segundos = props.tiempoRestante % 60
  return `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`
})

/**
 * Total de tiempo formateado
 */
const tiempoTotalFormateado = computed(() => {
  const minutos = Math.floor(props.duracionTotal / 60)
  return `${minutos}m`
})

/**
 * Calcular porcentaje de tiempo transcurrido
 */
const porcentajeTiempo = computed(() => {
  if (props.duracionTotal === 0) return 0
  return Math.min(100, Math.round(((props.duracionTotal - props.tiempoRestante) / props.duracionTotal) * 100))
})

/**
 * Contar incidencias por tipo
 */
const statsIncidencias = computed(() => {
  const stats = {}
  props.incidencias.forEach(i => {
    stats[i.tipo] = (stats[i.tipo] || 0) + 1
  })
  return stats
})

const cerrar = () => {
  emit('restart')
}
</script>

<template>
  <div v-if="blocked" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Fondo con gradiente según razón -->
    <div class="fixed inset-0 bg-gradient-to-br" :class="colorFondo" />
    
    <!-- Patrón de fondo -->
    <div class="fixed inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]" />

    <!-- Contenedor principal -->
    <div class="relative bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl max-w-2xl w-full p-8 animate-scale-up">
      
      <!-- Cabecera -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-4 animate-pulse">
          <span class="text-7xl">{{ iconoGrande }}</span>
        </div>
        
        <h1 class="text-4xl font-black text-gray-900 mb-2 tracking-tight">
          {{ mensajePrincipal }}
        </h1>
        
        <p class="text-gray-600 text-lg">
          {{ autoEnviado ? 'Tu examen fue enviado automáticamente.' : 'Tu examen ha finalizado.' }}
        </p>
      </div>

      <!-- Info de tiempo transcurrido -->
      <div class="bg-gray-50 rounded-2xl p-6 mb-8">
        <div class="flex justify-between items-center mb-3">
          <span class="text-gray-500 font-semibold">Tiempo del examen</span>
          <span class="text-2xl font-bold text-gray-900">
            {{ tiempoTotalFormateado }} - {{ tiempoRestanteFormateado }} restante
          </span>
        </div>
        
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-red-400 to-red-600 transition-all duration-500"
            :style="{ width: `${porcentajeTiempo}%` }"
          />
        </div>
      </div>

      <!-- Resumen de incidencias (si existen) -->
      <div v-if="incidencias.length > 0" class="mb-8">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
          <span class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm mr-2">!</span>
          Incidencias registradas ({{ incidencias.length }})
        </h3>
        
        <div class="space-y-2">
          <div
            v-for="(incidencia, idx) in incidencias.slice(0, 5)"
            :key="idx"
            class="flex items-start p-3 rounded-xl bg-red-50 border border-red-100"
          >
            <div class="w-2 h-2 bg-red-400 rounded-full mt-1.5 mr-3" />
            
            <div class="flex-1">
              <p class="font-semibold text-gray-900 text-sm">
                {{ incidencia.descripcion_legible || incidencia.tipo }}
              </p>
              <p v-if="incidencia.descripcion" class="text-gray-600 text-xs">
                {{ incidencia.descripcion }}
              </p>
              <p class="text-gray-400 text-xs mt-1">
                {{ new Date(incidencia.ocurrido_at).toLocaleTimeString() }}
              </p>
            </div>
          </div>
          
          <div v-if="incidencias.length > 5" class="text-center">
            <p class="text-sm text-gray-500">
              +{{ incidencias.length - 5 }} incidencias más
            </p>
          </div>
        </div>
      </div>

      <!-- Acción final -->
      <div class="flex flex-col sm:flex-row gap-4">
        <button
          v-if="autoEnviado"
          @click="$router.push({ name: 'EstudianteQuizResultado', params: { id: $route.params.id } })"
          class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2"
        >
          <span>Ver resultado</span>
          <span class="text-xl">→</span>
        </button>
        
        <button
          v-else
          @click="$router.push('/estudiante/quizzes')"
          class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2"
        >
          <span>Volver a mis exámenes</span>
          <span class="text-xl">←</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes scale-up {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-up {
  animation: scale-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
