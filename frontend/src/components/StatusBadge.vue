<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="badgeClass"
  >
    <span v-if="icon" class="mr-1.5 text-sm" :class="iconClass">{{ icon }}</span>
    <span>{{ text }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

// Props definidas
const props = defineProps({
  tipo: {
    type: String,
    required: true,
    validator: (value) => [
      // Tareas
      'entregado', 'tardio', 'pendiente',
      // Calificaciones
      'aprobado', 'reprobado', 'sin_calificar',
      // Asistencia
      'presente', 'ausente', 'tardanza', 'justificado',
      // Exámenes
      'en_proceso', 'enviado', 'calificado', 'auto_submit',
      // Advertencias/Incidencias
      'advertencia', 'baja', 'media', 'alta', 'info'
    ].includes(value)
  },
  pequeno: {
    type: Boolean,
    default: false
  }
})

// Configuración de badges por tipo
const badgeConfig = {
  // Tareas
  entregado: { bg: 'bg-green-100 text-green-800', darkBg: 'dark:bg-green-900/30 dark:text-green-300', text: 'Entregado', icon: '✓' },
  tardio: { bg: 'bg-amber-100 text-amber-800', darkBg: 'dark:bg-amber-900/30 dark:text-amber-300', text: 'Tardío', icon: '⚠️' },
  pendiente: { bg: 'bg-gray-100 text-gray-800', darkBg: 'dark:bg-gray-800 dark:text-gray-300', text: 'Pendiente', icon: ' Pending' },

  // Calificaciones
  aprobado: { bg: 'bg-green-100 text-green-800', darkBg: 'dark:bg-green-900/30 dark:text-green-300', text: 'Aprobado', icon: '✓' },
  reprobado: { bg: 'bg-red-100 text-red-800', darkBg: 'dark:bg-red-900/30 dark:text-red-300', text: 'Reprobado', icon: '✗' },
  sin_calificar: { bg: 'bg-yellow-100 text-yellow-800', darkBg: 'dark:bg-yellow-900/30 dark:text-yellow-300', text: 'Pendiente', icon: ' Pending' },

  // Asistencia
  presente: { bg: 'bg-green-100 text-green-800', darkBg: 'dark:bg-green-900/30 dark:text-green-300', text: 'Presente', icon: '✓' },
  ausente: { bg: 'bg-red-100 text-red-800', darkBg: 'dark:bg-red-900/30 dark:text-red-300', text: 'Ausente', icon: '✗' },
  tardanza: { bg: 'bg-amber-100 text-amber-800', darkBg: 'dark:bg-amber-900/30 dark:text-amber-300', text: 'Tardanza', icon: '🕒' },
  justificado: { bg: 'bg-blue-100 text-blue-800', darkBg: 'dark:bg-blue-900/30 dark:text-blue-300', text: 'Justificado', icon: '✓' },

  // Exámenes
  en_proceso: { bg: 'bg-indigo-100 text-indigo-800', darkBg: 'dark:bg-indigo-900/30 dark:text-indigo-300', text: 'En curso', icon: '⏱️' },
  enviado: { bg: 'bg-emerald-100 text-emerald-800', darkBg: 'dark:bg-emerald-900/30 dark:text-emerald-300', text: 'Enviado', icon: '✓' },
  calificado: { bg: 'bg-purple-100 text-purple-800', darkBg: 'dark:bg-purple-900/30 dark:text-purple-300', text: 'Calificado', icon: '✓' },
  auto_submit: { bg: 'bg-gray-200 text-gray-800', darkBg: 'dark:bg-gray-700 dark:text-gray-300', text: 'Auto-envío', icon: '⚡' },

  // Advertencias/Incidencias
  advertencia: { bg: 'bg-amber-100 text-amber-800', darkBg: 'dark:bg-amber-900/30 dark:text-amber-300', text: 'Advertencia', icon: '⚠️' },
  baja: { bg: 'bg-green-100 text-green-800', darkBg: 'dark:bg-green-900/30 dark:text-green-300', text: 'Baja', icon: '⬇️' },
  media: { bg: 'bg-amber-100 text-amber-800', darkBg: 'dark:bg-amber-900/30 dark:text-amber-300', text: 'Media', icon: '⚠️' },
  alta: { bg: 'bg-red-100 text-red-800', darkBg: 'dark:bg-red-900/30 dark:text-red-300', text: 'Alta', icon: '⚠️' },
  info: { bg: 'bg-blue-100 text-blue-800', darkBg: 'dark:bg-blue-900/30 dark:text-blue-300', text: 'Info', icon: 'ℹ️' }
}

// Computed para obtener configuración
const config = computed(() => badgeConfig[props.tipo] || badgeConfig.pendiente)

// Clases del badge
const badgeClass = computed(() => {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-all duration-200'
  const tamaño = props.pequeno ? 'px-2 py-0.5 text-[10px]' : ''
  return `${base} ${tamaño} ${config.value.bg} ${config.value.darkBg}`
})

// Icono
const icon = computed(() => config.value.icon)
const iconClass = computed(() => props.pequeno ? 'mr-1 text-[10px]' : 'mr-1.5 text-sm')

// Texto
const text = computed(() => config.value.text)
</script>
