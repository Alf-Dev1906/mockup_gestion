<script setup>
import { computed } from 'vue'

const props = defineProps({
  modalVisible: {
    type: Boolean,
    default: false,
  },
  mensajeAdvertencia: {
    type: String,
    required: true,
  },
  advertenciasCount: {
    type: Number,
    required: true,
  },
  advertenciasMax: {
    type: Number,
    default: 3,
  },
})

const emits = defineEmits(['close'])

/**
 * Calcular porcentaje de advertencias restantes
 * (para visualización en barra interna del modal)
 */
const porcentajeRestante = computed(() => {
  return Math.max(0, 100 - (props.advertenciasCount / props.advertenciasMax) * 100)
})

const textoRestante = computed(() => {
  const restantes = props.advertenciasMax - props.advertenciasCount
  return `Quedan ${restantes} de ${props.advertenciasMax} advertencias`
})

/**
 * Icono según el tipo de incidencia
 */
const iconoPorIncidencia = computed(() => {
  const tipo = props.mensajeAdvertencia.toLowerCase()
  if (tipo.includes('pestana') || tipo.includes('minimiz')) return '🌐'
  if (tipo.includes('foco')) return '🖱️'
  if (tipo.includes('pantalla')) return '🖥️'
  if (tipo.includes('inactiv')) return '💤'
  if (tipo.includes('fraude')) return '🚨'
  return '⚠️'
})

/**
 * Color de barra de advertencia
 */
const barraClase = computed(() => {
  const porcentaje = props.advertenciasCount / props.advertenciasMax
  if (porcentaje >= 0.8) return 'bg-red-600'
  if (porcentaje >= 0.6) return 'bg-orange-500'
  return 'bg-amber-400'
})

const cerrar = () => {
  emits('close')
}
</script>

<template>
  <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Overlay oscuro con animación */
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-300" />

    <!-- Modal -->
    <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 animate-scale-up">
      <!-- Icono grande -->
      <div class="flex justify-center mb-6">
        <div class="relative">
          <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center text-6xl">
            {{ iconoPorIncidencia }}
          </div>
          <div class="absolute -top-2 -right-2 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold border-4 border-white">
            {{ advertenciasCount }}
          </div>
        </div>
      </div>

      <!-- Título -->
      <h2 class="text-2xl font-bold text-gray-900 text-center mb-3">
        ¡Advertencia de Proctoring!
      </h2>

      <!-- Descripción de la incidencia -->
      <div class="mb-6">
        <p class="text-gray-700 text-center text-lg leading-relaxed">
          {{ mensajeAdvertencia }}
        </p>
      </div>

      <!-- Barra de progreso de advertencias -->
      <div class="mb-8">
        <div class="flex justify-between text-xs font-bold text-gray-500 mb-2">
          <span>Progreso hacia auto-submit</span>
          <span>{{ textoRestante }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
          <div
            :class="barraClase"
            class="h-full transition-all duration-500 ease-out rounded-full"
            :style="{ width: `${(advertenciasCount / advertenciasMax) * 100}%` }"
          />
        </div>
      </div>

      <!-- Botón de confirmación -->
      <div class="flex justify-center">
        <button
          @click="cerrar"
          class="group relative overflow-hidden bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-red-300"
        >
          <span class="relative z-10">Entendido, volver al examen</span>
          <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-red-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </button>
      </div>

      <!-- Pie de modal -->
      <div class="mt-6 text-center">
        <p class="text-xs text-gray-400">
          Cada incidencia queda registrada en el sistema
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes scale-up {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-up {
  animation: scale-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
