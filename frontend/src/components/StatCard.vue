<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-500 font-medium">{{ label }}</p>
        <div v-if="loading" class="mt-2 h-8 w-24 bg-gray-100 rounded-lg animate-pulse" />
        <p v-else class="text-3xl font-extrabold text-gray-900 mt-1">{{ value }}</p>
      </div>
      <div
        class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
        :class="bgColor"
      >
        {{ icon }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label:   { type: String, required: true },
  value:   { type: [String, Number], default: '—' },
  icon:    { type: String, default: '📊' },
  color:   { type: String, default: 'blue' },
  loading: { type: Boolean, default: false },
})

const bgColor = computed(() => ({
  blue:    'bg-blue-50',
  indigo:  'bg-indigo-50',
  violet:  'bg-violet-50',
  emerald: 'bg-emerald-50',
  yellow:  'bg-yellow-50',
  red:     'bg-red-50',
})[props.color] ?? 'bg-gray-50')
</script>
