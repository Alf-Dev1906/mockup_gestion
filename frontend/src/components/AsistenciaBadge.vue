<template>
  <span
    class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-semibold"
    :class="colorClass"
  >
    {{ porcentaje }}%
    <span v-if="mostrarIcono" class="ml-1.5 text-xs" :class="iconClass">{{ icono }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  porcentaje: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 100
  },
  mostrarIcono: {
    type: Boolean,
    default: false
  }
})

const colorClass = computed(() => {
  if (props.porcentaje >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
  if (props.porcentaje >= 75) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (props.porcentaje >= 60) return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
})

const icono = computed(() => {
  if (props.porcentaje >= 90) return '✓'
  if (props.porcentaje >= 75) return '✓'
  if (props.porcentaje >= 60) return '⚠️'
  return '✗'
})

const iconClass = computed(() => 'text-xs')
</script>
