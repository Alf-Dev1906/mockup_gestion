<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import { useNotificationsStore } from '@/stores/notifications'
import AdminLayout from '@/layouts/AdminLayout.vue'

const {
  obtenerNotificaciones,
  marcarLeida,
  marcarTodasLeidas,
  eliminarNotificacion,
  eliminarTodasLeidas,
  irANotificacion,
  getIconoPorTipo,
  getColorPorTipo,
} = useNotifications()

const store = useNotificationsStore()

// Estado
const filtroActual = ref('todas')
const paginaActual = ref(1)
const paginacion = ref(null)

// Filtros disponibles
const filtros = [
  { valor: 'todas', label: 'Todas' },
  { valor: 'no-leidas', label: 'No leídas' },
  { valor: 'leidas', label: 'Leídas' },
]

/**
 * Cambiar filtro
 */
const cambiarFiltro = async (filtro) => {
  filtroActual.value = filtro
  paginaActual.value = 1
  await cargarNotificaciones()
}

/**
 * Cargar notificaciones
 */
const cargarNotificaciones = async () => {
  paginacion.value = await obtenerNotificaciones(paginaActual.value, filtroActual.value)
}

/**
 * Ir a página
 */
const irAPagina = async (pagina) => {
  paginaActual.value = pagina
  await cargarNotificaciones()
}

/**
 * Formato de fecha/hora
 */
const formatoFecha = (fecha) => {
  const d = new Date(fecha)
  return d.toLocaleDateString('es-VE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Hace cuánto tiempo
 */
const haceCuanto = (fecha) => {
  const ahora = new Date()
  const then = new Date(fecha)
  const diff = Math.floor((ahora - then) / 1000)

  if (diff < 60) return 'hace segundos'
  if (diff < 3600) return `hace ${Math.floor(diff / 60)}m`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)}h`
  if (diff < 604800) return `hace ${Math.floor(diff / 86400)}d`
  
  return formatoFecha(fecha)
}

// Inicializar
onMounted(() => {
  cargarNotificaciones()
})
</script>

<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto px-4 py-6">
    
    <!-- Título -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Notificaciones</h1>
      <p class="text-gray-500">
        {{ store.unreadCount }} sin leer
      </p>
    </div>

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
      <!-- Filtros -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button
          v-for="f in filtros"
          :key="f.valor"
          @click="cambiarFiltro(f.valor)"
          :class="[
            'px-4 py-2 rounded-lg font-semibold transition text-sm',
            filtroActual === f.valor
              ? 'bg-indigo-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ f.label }}
        </button>
      </div>

      <!-- Acciones -->
      <div class="flex gap-2">
        <button
          v-if="store.unreadCount > 0"
          @click="marcarTodasLeidas"
          class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 font-semibold"
        >
          ✓ Marcar todas como leídas
        </button>
        
        <button
          v-if="store.items.length > 0"
          @click="eliminarTodasLeidas"
          class="px-4 py-2 text-sm text-red-600 hover:text-red-700 font-semibold"
        >
          🗑️ Eliminar leídas
        </button>
      </div>
    </div>

    <!-- Lista de notificaciones -->
    <div v-if="store.loading" class="text-center py-12">
      <div class="inline-block animate-spin">⏳</div>
      <p class="mt-2 text-gray-500">Cargando...</p>
    </div>

    <div v-else-if="store.items.length === 0" class="text-center py-12 bg-gray-50 rounded-2xl">
      <p class="text-4xl mb-2">🔕</p>
      <p class="text-gray-500 font-semibold">No hay notificaciones</p>
      <p class="text-sm text-gray-400">Aquí aparecerán tus notificaciones</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="notif in store.items"
        :key="notif.id"
        :class="[
          'bg-white rounded-xl border transition cursor-pointer group hover:shadow-md',
          notif.leida ? 'border-gray-100' : 'border-blue-200 bg-blue-50'
        ]"
      >
        <div
          @click="irANotificacion(notif)"
          class="p-4 flex gap-4 items-start"
        >
          <!-- Icono -->
          <div :class="['text-2xl flex-shrink-0 group-hover:scale-110 transition', getColorPorTipo(notif.tipo)]">
            {{ getIconoPorTipo(notif.tipo) }}
          </div>

          <!-- Contenido -->
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-900">
              {{ notif.titulo }}
            </h3>
            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
              {{ notif.mensaje }}
            </p>
            <p class="text-xs text-gray-400 mt-2">
              {{ haceCuanto(notif.timestamp) }}
            </p>
          </div>

          <!-- Acciones -->
          <div class="flex gap-2 flex-shrink-0">
            <button
              v-if="!notif.leida"
              @click.stop="marcarLeida(notif.id)"
              class="p-2 text-gray-400 hover:text-indigo-600 transition hover:bg-indigo-50 rounded-lg"
              title="Marcar como leída"
            >
              ✓
            </button>

            <button
              @click.stop="eliminarNotificacion(notif.id)"
              class="p-2 text-gray-400 hover:text-red-600 transition hover:bg-red-50 rounded-lg"
              title="Eliminar"
            >
              ×
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="paginacion && paginacion.last_page > 1" class="mt-8 flex justify-center gap-2">
      <button
        v-if="paginaActual > 1"
        @click="irAPagina(paginaActual - 1)"
        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
      >
        ←
      </button>

      <button
        v-for="p in Math.min(paginacion.last_page, 5)"
        :key="p"
        @click="irAPagina(p)"
        :class="[
          'px-3 py-2 rounded-lg transition font-semibold',
          p === paginaActual
            ? 'bg-indigo-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
      >
        {{ p }}
      </button>

      <button
        v-if="paginaActual < paginacion.last_page"
        @click="irAPagina(paginaActual + 1)"
        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
      >
        →
      </button>
    </div>
  </div>
  </AdminLayout>
</template>
