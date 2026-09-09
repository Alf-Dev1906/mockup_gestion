<template>
  <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
      <h3 class="font-semibold text-gray-900 text-sm">{{ title }}</h3>
    </div>
    <div class="divide-y divide-gray-50">
      <div v-for="item in items" :key="item.key" class="flex items-center justify-between px-5 py-3">
        <span class="text-xs font-mono text-gray-500">{{ item.key }}</span>
        <div class="flex items-center gap-2">
          <span v-if="item.badge"
            class="text-xs font-bold px-2 py-0.5 rounded-full"
            :class="badgeClass(item.badge)">
            {{ item.value }}
          </span>
          <span v-else class="text-sm font-medium text-gray-900">{{ item.value ?? '—' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ title: String, items: Array })

const badgeClass = color => ({
  red:    'bg-red-100 text-red-700',
  green:  'bg-green-100 text-green-700',
  yellow: 'bg-yellow-100 text-yellow-700',
  blue:   'bg-blue-100 text-blue-700',
}[color] ?? 'bg-gray-100 text-gray-700')
</script>
