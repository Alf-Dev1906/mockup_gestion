<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
    :class="isActive
      ? 'bg-white/15 text-white shadow-sm'
      : 'text-blue-200 hover:bg-white/10 hover:text-white'"
  >
    <span class="text-base leading-none w-5 text-center flex-shrink-0">{{ icon }}</span>
    <span>{{ $slots.default ? '' : '' }}<slot /></span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  to:   { type: String, required: true },
  icon: { type: String, default: '•' },
})

const route    = useRoute()
const isActive = computed(() => route.path === props.to || route.path.startsWith(props.to + '/'))
</script>
