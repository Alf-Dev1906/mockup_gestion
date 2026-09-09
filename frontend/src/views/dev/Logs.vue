<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Logs del Sistema</h1>
        <p class="text-gray-500 mt-1">Registro completo de actividad y eventos de seguridad</p>
      </div>
      <button @click="cargar" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
        🔄 Actualizar
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
      <button v-for="t in tabs" :key="t.key" @click="tab = t.key; cargar()"
        class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
        :class="tab === t.key ? 'bg-purple-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'">
        {{ t.icon }} {{ t.label }}
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-4">
      <select v-model="filtros.action" @change="cargar"
        class="py-2 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <option value="">Todas las acciones</option>
        <option value="login_exitoso">Login exitoso</option>
        <option value="login_fallido">Login fallido</option>
        <option value="intento_acceso_no_autorizado">Acceso denegado</option>
        <option value="cambio_rol">Cambio de rol</option>
        <option value="modificacion_calificacion">Modificación calificación</option>
        <option value="backup_creado">Backup creado</option>
        <option value="solicitud_aprobada">Solicitud aprobada</option>
        <option value="solicitud_rechazada">Solicitud rechazada</option>
      </select>
      <select v-model="filtros.limit" @change="cargar"
        class="py-2 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <option value="50">50 registros</option>
        <option value="100">100 registros</option>
        <option value="200">200 registros</option>
      </select>
    </div>

    <!-- Tabla de logs BD -->
    <div v-if="tab === 'security'" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Cargando logs...</div>
      <div v-else class="overflow-x-auto max-h-[600px]">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Fecha</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Acción</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Modelo</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">IP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 font-mono text-xs">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
              <td class="px-5 py-3 whitespace-nowrap">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold" :class="getActionBadge(log.accion)">
                  {{ log.accion }}
                </span>
              </td>
              <td class="px-5 py-3 text-gray-700 whitespace-nowrap">{{ log.user_name || `ID:${log.user_id}` || '—' }}</td>
              <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ log.modelo ? `${log.modelo}#${log.modelo_id}` : '—' }}</td>
              <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ log.ip || '—' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="!logs.length" class="p-8 text-center text-gray-400">Sin registros</div>
      </div>
    </div>

    <!-- Log de archivo Laravel -->
    <div v-else class="bg-gray-950 rounded-2xl border border-gray-800 overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
        <span class="text-green-400 text-sm font-mono">storage/logs/laravel.log</span>
        <span class="text-gray-500 text-xs">{{ rawLogs.length }} líneas</span>
      </div>
      <div v-if="loading" class="p-6 text-green-400 font-mono text-xs">Cargando...</div>
      <div v-else class="overflow-y-auto max-h-[600px] p-4">
        <pre class="text-green-400 text-xs font-mono whitespace-pre-wrap leading-relaxed">{{ rawLogs.join('\n') }}</pre>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const tab = ref('security')
const logs = ref([])
const rawLogs = ref([])

const tabs = [
  { key: 'security', icon: '🔐', label: 'Logs de Seguridad' },
  { key: 'laravel',  icon: '📋', label: 'Log de Laravel' },
]

const filtros = ref({ action: '', limit: '100' })

const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'

const getActionBadge = a => {
  if (a?.includes('fallido') || a?.includes('no_autorizado')) return 'bg-red-100 text-red-700'
  if (a?.includes('eliminado') || a?.includes('rechazado'))    return 'bg-orange-100 text-orange-700'
  if (a?.includes('aprobado') || a?.includes('exitoso'))       return 'bg-green-100 text-green-700'
  if (a?.includes('cambio') || a?.includes('modificacion'))    return 'bg-yellow-100 text-yellow-700'
  return 'bg-gray-100 text-gray-700'
}

async function cargar() {
  loading.value = true
  try {
    if (tab.value === 'security') {
      const params = { limit: filtros.value.limit }
      if (filtros.value.action) params.action = filtros.value.action
      const { data } = await api.get('/dev/logs/security', { params })
      logs.value = data
    } else {
      const { data } = await api.get('/dev/logs', { params: { type: 'laravel', lines: filtros.value.limit } })
      rawLogs.value = data.logs
    }
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>
