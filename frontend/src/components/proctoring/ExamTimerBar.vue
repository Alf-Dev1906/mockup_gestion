<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  tiempoRestante: {
    type: Number,
    required: true,
  },
  duracionTotal: {
    type: Number,
    required: true,
  },
  advertenciasCount: {
    type: Number,
    default: 0,
  },
  advertenciasMax: {
    type: Number,
    default: 3,
  },
  estaActivo: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['tiempo-cambiado'])

const timerDisplay = computed(() => {
  const minutos = Math.floor(props.tiempoRestante / 60)
  const segundos = props.tiempoRestante % 60
  return `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`
})

const porcentajeRestante = computed(() => {
  return Math.max(0, (props.tiempoRestante / props.duracionTotal) * 100)
})

const porcentajeTranscurrido = computed(() => {
  return Math.min(100, Math.round(((props.duracionTotal - props.tiempoRestante) / props.duracionTotal) * 100))
})

const estadoBarra = computed(() => {
  if (props.tiempoRestante <= 120) return 'critico'
  if (props.tiempoRestante <= 300) return 'alerta'
  return 'normal'
})

const colorBarra = computed(() => {
  switch (estadoBarra.value) {
    case 'critico':
      return 'from-red-400 to-red-600'
    case 'alerta':
      return 'from-orange-400 to-orange-600'
    default:
      return 'from-indigo-400 to-indigo-600'
  }
})

const colorTexto = computed(() => {
  switch (estadoBarra.value) {
    case 'critico':
      return 'text-red-700'
    case 'alerta':
      return 'text-orange-700'
    default:
      return 'text-indigo-700'
  }
})

const colorFondoBarra = computed(() => {
  switch (estadoBarra.value) {
    case 'critico':
      return 'bg-red-50'
    case 'alerta':
      return 'bg-orange-50'
    default:
      return 'bg-indigo-50'
  }
})

const iconoEstado = computed(() => {
  switch (estadoBarra.value) {
    case 'critico':
      return '🔴'
    case 'alerta':
      return '🟡'
    default:
      return '🟢'
  }
})

const mostrarAdvertenciaIcono = computed(() => {
  return props.advertenciasCount > 0
})

const contadorAdvertencias = computed(() => {
  if (props.advertenciasCount >= props.advertenciasMax) return '!'
  return props.advertenciasCount
})

const mensajeAdvertencia = computed(() => {
  if (props.advertenciasCount >= props.advertenciasMax) {
    return '¡Auto-submit activado!'
  }
  if (props.advertenciasCount > 0) {
    return `${props.advertenciasCount} advertencia${props.advertenciasCount > 1 ? 's' : ''}`
  }
  return 'Proctoring activo'
})
</script>

<template>
  <!-- Barra superior del examen -->
  <div
    class="sticky top-0 z-40 w-full transition-colors duration-500"
    :class="colorFondoBarra"
  >
    <div class="max-w-7xl mx-auto px-4 py-3">
      <div class="flex items-center justify-between gap-4">
        
        <!-- Tiempo restante (centro) -->
        <div class="flex-1 flex justify-center items-center">
          <div
            class="font-mono font-bold text-2xl sm:text-3xl tracking-tight"
            :class="colorTexto"
          >
            {{ timerDisplay }}
          </div>
          
          <!-- Icono de estado -->
          <span
            class="ml-3 text-xl animate-pulse"
            :class="{ 'hidden': !estaActivo }"
          >
            {{ iconoEstado }}
          </span>
        </div>

        <!-- Advertencias (derecha) -->
        <div
          v-if="mostrarAdvertenciaIcono"
          class="flex items-center gap-2"
        >
          <div class="relative group">
            <span class="text-2xl">⚠️</span>
            
            <!-- Contador sobre el icono -->
            <span
              class="absolute -top-1 -right-1.5 min-w-[20px] h-5 flex items-center justify-center px-1 rounded-full text-xs font-bold text-white"
              :class="{
                'bg-red-600': advertenciasCount >= advertenciasMax,
                'bg-amber-500': advertenciasCount < advertenciasMax && advertenciasCount > 1,
                'bg-orange-500': advertenciasCount === 1
              }"
            >
              {{ contadorAdvertencias }}
            </span>

            <!-- Tooltip -->
            <div
              class="absolute right-0 top-full mt-2 w-48 p-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50"
            >
              {{ mensajeAdvertencia }}
            </div>
          </div>
        </div>

        <!-- Información extra (izquierda) -->
        <div class="hidden sm:flex items-center gap-3 text-sm text-gray-500">
          <span class="hidden md:inline">Duración: {{ Math.round(duracionTotal / 60) }} min</span>
          <span class="hidden lg:inline">|</span>
          <span class="hidden lg:inline">Progreso: {{ porcentajeTranscurrido }}%</span>
        </div>

      </div>

      <!-- Barra de progreso de tiempo -->
      <div class="mt-3 h-2 w-full bg-gray-200 rounded-full overflow-hidden">
        <div
          :class="colorBarra"
          class="h-full transition-all duration-1000 ease-linear"
          :style="{ width: `${porcentajeRestante}%` }"
        />
      </div>

      <!-- Indicador de estado en móvil -->
      <div class="sm:hidden mt-2 text-xs font-semibold text-center" :class="colorTexto">
        {{ estadoBarra === 'critico' ? '¡Atención! Quedan menos de 2 minutos' : '' }}
        {{ estadoBarra === 'alerta' ? 'Atención: Quedan menos de 5 minutos' : '' }}
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animaciones para la barra de progreso */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
