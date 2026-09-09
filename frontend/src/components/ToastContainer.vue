<template>
  <div class="fixed top-4 right-4 z-[9999] space-y-3 pointer-events-none">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto rounded-xl shadow-2xl p-4 min-w-[320px] max-w-md flex items-start gap-3 backdrop-blur-sm border transition-all duration-300',
          getToastClasses(toast.type),
          toast.visible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-4'
        ]"
      >
        <div class="flex-shrink-0 text-2xl">
          {{ getIcon(toast.type) }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold leading-tight break-words">
            {{ toast.message }}
          </p>
        </div>
        <button
          @click="removeToast(toast.id)"
          class="flex-shrink-0 text-current opacity-60 hover:opacity-100 transition text-lg leading-none"
        >
          ×
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, remove: removeToast } = useToast()

const getToastClasses = (type) => {
  const classes = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800'
  }
  return classes[type] || classes.info
}

const getIcon = (type) => {
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  }
  return icons[type] || icons.info
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(2rem);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(2rem);
}
</style>
