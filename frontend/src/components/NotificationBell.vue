<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import { useNotificationsStore } from '@/stores/notifications'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const {
  contadorNoLeidas,
  mostrarPanel,
  marcarLeida,
  marcarTodasLeidas,
  irANotificacion,
  getIconoPorTipo,
} = useNotifications()

const store = useNotificationsStore()

const campanelRef = ref(null)
const panelRef = ref(null)
const errorCarga = ref(false)
const intentosRecarga = ref(0)
const maxIntentos = 3

// Cerrar panel al hacer clic fuera (sin VueUse)
function handleClickOutside(event) {
  if (campanelRef.value && !campanelRef.value.contains(event.target)) {
    mostrarPanel.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))

// Booleano para saber si hay notificaciones
const hayNoLeidas = computed(() => contadorNoLeidas.value > 0)

// Formato del contador
const formatoContador = computed(() => {
  if (contadorNoLeidas.value > 99) return '99+'
  return contadorNoLeidas.value
})

// Últimas 3 notificaciones no leídas
const ultimasNotificaciones = computed(() => {
  return store.items
    .filter(n => !n.leida)
    .slice(0, 3)
})

// Acciones
const onVerTodas = () => {
  mostrarPanel.value = false
  window.location.href = '/notificaciones'
}

const onMarcarTodas = async (e) => {
  e.stopPropagation()
  try {
    await marcarTodasLeidas()
    toast.success('Todas las notificaciones marcadas como leídas')
  } catch (error) {
    console.error('Error marcando todas:', error)
    toast.error('Error al marcar notificaciones. Intenta de nuevo.')
  }
}

// Manejar click en notificación individual
const onClickNotificacion = async (notif) => {
  try {
    await irANotificacion(notif)
  } catch (error) {
    console.error('Error al abrir notificación:', error)
    toast.error('Error al abrir la notificación')
  }
}

// Recargar notificaciones manualmente
const recargarNotificaciones = async () => {
  if (intentosRecarga.value >= maxIntentos) {
    toast.error('Demasiados intentos. Por favor recarga la página.')
    return
  }

  intentosRecarga.value++
  errorCarga.value = false
  
  try {
    await store.fetchUnreadCount()
    intentosRecarga.value = 0 // Reset en éxito
  } catch (error) {
    console.error('Error recargando notificaciones:', error)
    errorCarga.value = true
    
    if (intentosRecarga.value >= maxIntentos) {
      toast.error('No se pudieron cargar las notificaciones')
    }
  }
}

// Monitorear errores del store
const checkStoreError = () => {
  // Si el store tiene más de 3 fallos consecutivos, mostrar error
  if (store.failCount && store.failCount >= 3) {
    errorCarga.value = true
  }
}

// Verificar errores periódicamente
onMounted(() => {
  const errorCheckInterval = setInterval(checkStoreError, 5000)
  onUnmounted(() => clearInterval(errorCheckInterval))
})
</script>

<template>
  <div ref="campanelRef" class="relative">
    <!-- Campana con indicador de error -->
    <button
      @click="mostrarPanel = !mostrarPanel"
      class="relative p-2 text-gray-600 hover:text-gray-900 transition rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      :class="{ 'text-red-500 hover:text-red-700': errorCarga }"
      :title="errorCarga ? 'Error cargando notificaciones' : `${contadorNoLeidas} notificaciones sin leer`"
    >
      <svg
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          v-if="!errorCarga"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
        <!-- Icono de error -->
        <path
          v-else
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>

      <!-- Badge con contador (rojo vibrante) -->
      <span
        v-if="hayNoLeidas && !errorCarga"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full shadow-lg animate-pulse"
      >
        {{ formatoContador }}
      </span>
    </button>

    <!-- Panel de notificaciones -->
    <div
      v-if="mostrarPanel"
      ref="panelRef"
      class="absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 z-50 max-h-[70vh] overflow-hidden flex flex-col"
    >
      <!-- Header con gradient y acciones -->
      <div class="px-4 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="font-bold text-lg">🔔 Notificaciones</h3>
            <p class="text-indigo-100 text-xs mt-1">
              {{ errorCarga ? 'Error de conexión' : `${contadorNoLeidas} sin leer` }}
            </p>
          </div>
          <div class="flex gap-2">
            <button
              v-if="errorCarga"
              @click="recargarNotificaciones"
              class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition backdrop-blur-sm"
              :disabled="intentosRecarga >= maxIntentos"
            >
              🔄 Reintentar
            </button>
            <button
              v-else-if="contadorNoLeidas > 0"
              @click="onMarcarTodas"
              class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition backdrop-blur-sm"
            >
              ✅ Marcar todas
            </button>
          </div>
        </div>
      </div>

      <!-- Mensaje de error -->
      <div v-if="errorCarga" class="p-6 text-center">
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
          <p class="font-semibold mb-1">⚠️ Error de conexión</p>
          <p class="text-xs text-red-600">No se pudieron cargar las notificaciones.</p>
          <button
            @click="recargarNotificaciones"
            class="mt-3 text-xs bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition"
            :disabled="intentosRecarga >= maxIntentos"
          >
            {{ intentosRecarga >= maxIntentos ? 'Recarga la página' : 'Reintentar ahora' }}
          </button>
        </div>
      </div>

      <!-- Lista de últimas notificaciones -->
      <div v-else-if="ultimasNotificaciones.length > 0" class="overflow-y-auto flex-1">
        <div
          v-for="notif in ultimasNotificaciones"
          :key="notif.id"
          @click="onClickNotificacion(notif)"
          class="px-4 py-3 border-b border-gray-50 hover:bg-indigo-50 cursor-pointer transition group"
        >
          <div class="flex gap-3">
            <span class="text-2xl flex-shrink-0">{{ getIconoPorTipo(notif.tipo) }}</span>
            
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900 truncate">
                {{ notif.titulo }}
              </p>
              <p class="text-xs text-gray-600 line-clamp-2">
                {{ notif.mensaje }}
              </p>
              <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                <span>{{ new Date(notif.timestamp).toLocaleTimeString('es-VE', { 
                  hour: '2-digit', 
                  minute: '2-digit' 
                }) }}</span>
                <span v-if="!notif.leida" class="w-2 h-2 rounded-full bg-indigo-500"></span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sin notificaciones -->
      <div v-else class="p-8 text-center text-gray-400 flex-1 flex flex-col items-center justify-center">
        <p class="text-4xl mb-2">🔕</p>
        <p class="text-sm">No hay notificaciones</p>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
        <span class="text-xs text-gray-500">
          Mostrando últimas {{ ultimasNotificaciones.length }}
        </span>
        <button
          @click="onVerTodas"
          class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold hover:underline"
        >
          Ver todas →
        </button>
      </div>
    </div>
  </div>
</template>
