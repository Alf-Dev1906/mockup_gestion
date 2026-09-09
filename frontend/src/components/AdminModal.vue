<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="$emit('close')" />
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <button class="text-gray-400 hover:text-gray-700 text-xl leading-none" @click="$emit('close')">×</button>
        </div>
        <div class="px-6 py-4">
          <slot />
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
          <button type="button" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50" @click="$emit('close')">
            Cancelar
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-xl bg-blue-700 text-white text-sm font-medium hover:bg-blue-800 disabled:opacity-50"
            :disabled="saving"
            @click="$emit('save')"
          >
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  open: Boolean,
  title: { type: String, default: '' },
  saving: Boolean,
})
defineEmits(['close', 'save'])
</script>
