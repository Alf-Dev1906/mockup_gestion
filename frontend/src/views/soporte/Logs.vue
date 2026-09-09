<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Logs de Actividad</h1>
        <p class="text-gray-500 mt-1">Registro de todas las acciones del sistema</p>
      </div>
      <button @click="cargar" class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
        🔄 Actualizar
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-4">
      <select v-model="filtros.action" @change="cargar"
        class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500">
        <option value="">Todas las acciones</option>
        <option value="login_exitoso">Login exitoso</option>
        <option value="login_fallido">Login fallido</option>
        <option value="intento_acceso_no_autorizado">Acceso denegado</option>
        <option value="cambio_rol">Cambio de rol</option>
        <option value="usuario_creado">Usuario creado</option>
        <option value="usuario_eliminado">Usuario eliminado</option>
        <option value="password_reseteado">Password reseteado</option>
        <option value="sesion_revocada">Sesión revocada</option>
        <option value="backup_creado">Backup creado</option>
        <option value="modificacion_calificacion">Modificación calificación</option>
        <option value="solicitud_aprobada">Solicitud aprobada</option>
        <option value="solicitud_rechazada">Solicitud rechazada</option>
      </select>
      <select v-model="filtros.limit" @change="cargar"
        class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500">
        <option value="50">50 registros</option>
        <option value="100">100 registros</option>
        <option value="200">200 registros</option>
      </select>
    </div>

    <!-- Estadísticas de actividad -->
    <div v-if="!loading && estadisticas" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">📊 Acciones más frecuentes (7 días)</h3>
        <div class="space-y-2">
          <div v-for="item in estadisticas.acciones_mas_frecuentes?.slice(0, 5)" :key="item.accion"
            class="flex items-center gap-2 text-sm">
            <span class="text-gray-700 flex-1 truncate font-mono text-xs">{{ item.accion }}</span>
            <span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ item.total }}</span>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">👤 Usuarios más activos (7 días)</h3>
        <div class="space-y-2">
          <div v-for="u in estadisticas.usuarios_mas_activos?.slice(0, 5)" :key="u.id"
            class="flex items-center gap-2 text-sm">
            <span class="text-gray-800 flex-1 truncate">{{ u.name }}</span>
            <span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ u.actividades }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla logs -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Cargando logs...</div>
      <div v-else class="overflow-x-auto max-h-[500px]">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Fecha</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Acción</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">IP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
              <td class="px-5 py-3 whitespace-nowrap">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold" :class="getActionBadge(log.accion)">{{ log.accion }}</span>
              </td>
              <td class="px-5 py-3 text-sm text-gray-700">{{ log.user_name || `ID:${log.user_id}` || '—' }}</td>
              <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ log.ip || '—' }}</td>
            </tr>
            <tr v-if="!logs.length">
              <td colspan="4" class="px-5 py-12 text-center text-gray-400">Sin registros</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const logs = ref([])
const estadisticas = ref(null)
const filtros = ref({ action: '', limit: '100' })

const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'
const getActionBadge = a => {
  if (a?.includes('fallido') || a?.includes('no_autorizado')) return 'bg-red-100 text-red-700'
  if (a?.includes('eliminado') || a?.includes('rechazado')) return 'bg-orange-100 text-orange-700'
  if (a?.includes('aprobado') || a?.includes('exitoso') || a?.includes('creado')) return 'bg-green-100 text-green-700'
  if (a?.includes('cambio') || a?.includes('modificacion') || a?.includes('reseteado')) return 'bg-yellow-100 text-yellow-700'
  return 'bg-gray-100 text-gray-700'
}

async function cargar() {
  loading.value = true
  try {
    const params = { limit: filtros.value.limit }
    if (filtros.value.action) params.action = filtros.value.action
    const [logsRes, statsRes] = await Promise.all([
      api.get('/soporte/logs', { params }),
      api.get('/soporte/estadisticas'),
    ])
    logs.value = logsRes.data
    estadisticas.value = statsRes.data
  } finally { loading.value = false }
}

onMounted(cargar)
</script>
