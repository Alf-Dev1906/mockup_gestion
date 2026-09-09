<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Panel del Desarrollador</h1>
        <p class="text-gray-500 mt-1">Control total del sistema — Nivel 5</p>
      </div>
      <span class="bg-purple-100 text-purple-700 text-xs font-bold px-4 py-2 rounded-full">⚡ DESARROLLADOR</span>
    </div>

    <!-- Stats principales -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      <div v-for="n in 8" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse">
        <div class="h-4 bg-gray-100 rounded mb-3 w-1/2"></div>
        <div class="h-8 bg-gray-100 rounded w-1/3"></div>
      </div>
    </div>

    <template v-else>
      <!-- Sistema -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Sistema</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <StatCard color="purple" icon="🐘" :value="stats.system?.php_version" label="PHP Version" />
        <StatCard color="purple" icon="🎩" :value="stats.system?.laravel_version" label="Laravel" />
        <StatCard color="purple" icon="🌍" :value="stats.system?.environment" label="Entorno" />
        <StatCard :color="stats.system?.debug_mode ? 'red' : 'green'" icon="🐛"
          :value="stats.system?.debug_mode ? 'Activado' : 'Desactivado'" label="Debug Mode" />
      </div>

      <!-- Base de datos -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Base de Datos</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <StatCard color="blue" icon="🗄️" :value="stats.database?.size" label="Tamaño BD" />
        <StatCard color="blue" icon="📋" :value="stats.database?.tables" label="Tablas" suffix="tablas" />
        <StatCard color="blue" icon="💾" :value="stats.storage?.backups_count" label="Backups" suffix="respaldos" />
      </div>

      <!-- Seguridad -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Seguridad Hoy</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <StatCard :color="stats.security?.failed_logins_today > 10 ? 'red' : 'green'" icon="🚫"
          :value="stats.security?.failed_logins_today" label="Logins Fallidos" />
        <StatCard :color="stats.security?.unauthorized_attempts_today > 5 ? 'red' : 'green'" icon="⚠️"
          :value="stats.security?.unauthorized_attempts_today" label="Accesos Denegados" />
        <StatCard color="blue" icon="🔐" :value="stats.security?.active_sessions" label="Sesiones Activas" />
      </div>

      <!-- Almacenamiento -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Almacenamiento</h2>
      <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-8">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-medium text-gray-700">Disco usado</span>
          <span class="text-sm font-bold text-gray-900">{{ stats.storage?.disk_usage?.used_gb }} GB / {{ stats.storage?.disk_usage?.total_gb }} GB</span>
        </div>
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-700"
            :class="(stats.storage?.disk_usage?.percent_used ?? 0) > 80 ? 'bg-red-500' : 'bg-purple-500'"
            :style="{ width: (stats.storage?.disk_usage?.percent_used ?? 0) + '%' }"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2">{{ stats.storage?.disk_usage?.percent_used }}% utilizado · Logs: {{ stats.storage?.logs_size }}</p>
      </div>

      <!-- Actividad reciente -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Actividad Reciente (7 días)</h2>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Acción</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ocurrencias</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="item in stats.actividad_reciente" :key="item.accion" class="hover:bg-gray-50">
              <td class="px-6 py-3 text-sm text-gray-800 font-mono">{{ item.accion }}</td>
              <td class="px-6 py-3 text-right">
                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded-full">{{ item.count }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import StatCard from '@/components/StatCard.vue'
import api from '@/services/api'

const loading = ref(true)
const stats = ref({})

onMounted(async () => {
  try {
    const { data } = await api.get('/dev/dashboard')
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>
