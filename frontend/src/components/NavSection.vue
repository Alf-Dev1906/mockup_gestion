<template>
  <p class="text-white/40 text-xs font-semibold uppercase tracking-wider px-3 py-2 mt-3 select-none">
    {{ label }}
  </p>
</template>

<script setup>
defineProps({ label: String })
</script>
