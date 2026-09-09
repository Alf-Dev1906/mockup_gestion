<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Panel de Soporte IT</h1>
        <p class="text-gray-500 mt-1">Mantenimiento del sistema — Nivel 4</p>
      </div>
      <span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-4 py-2 rounded-full">🔧 SOPORTE IT</span>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      <div v-for="n in 6" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse h-28"></div>
    </div>

    <template v-else>
      <!-- Stats usuarios -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Usuarios del Sistema</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-gray-900">{{ stats.usuarios?.total }}</p>
          <p class="text-xs text-gray-500 mt-1">Total Usuarios</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-green-600">{{ stats.usuarios?.activos_hoy }}</p>
          <p class="text-xs text-gray-500 mt-1">Activos Hoy</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center col-span-2">
          <div class="flex flex-wrap justify-center gap-2">
            <span v-for="r in stats.usuarios?.por_rol" :key="r.role"
              class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full">
              {{ r.role }}: {{ r.count }}
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-2">Distribución por rol</p>
        </div>
      </div>

      <!-- Stats seguridad -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Seguridad de Hoy</h2>
      <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-2xl border p-5 text-center"
          :class="stats.seguridad?.logins_fallidos_hoy > 10 ? 'border-red-200 bg-red-50' : 'border-gray-100'">
          <p class="text-4xl font-bold" :class="stats.seguridad?.logins_fallidos_hoy > 10 ? 'text-red-600' : 'text-gray-900'">
            {{ stats.seguridad?.logins_fallidos_hoy }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Logins Fallidos</p>
        </div>
        <div class="bg-white rounded-2xl border p-5 text-center"
          :class="stats.seguridad?.accesos_denegados_hoy > 5 ? 'border-orange-200 bg-orange-50' : 'border-gray-100'">
          <p class="text-4xl font-bold" :class="stats.seguridad?.accesos_denegados_hoy > 5 ? 'text-orange-600' : 'text-gray-900'">
            {{ stats.seguridad?.accesos_denegados_hoy }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Accesos Denegados</p>
        </div>
      </div>

      <!-- Último backup -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Sistema</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">💾 Último Backup</h3>
            <router-link to="/soporte/backups" class="text-xs text-cyan-600 hover:underline">Ver todos →</router-link>
          </div>
          <div v-if="stats.sistema?.ultimo_backup">
            <p class="font-mono text-sm text-gray-700 truncate">{{ stats.sistema.ultimo_backup.nombre }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ formatDate(stats.sistema.ultimo_backup.created_at) }}</p>
            <span class="mt-2 inline-block px-2 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
              {{ stats.sistema.ultimo_backup.estatus }}
            </span>
          </div>
          <p v-else class="text-sm text-gray-400">Sin backups registrados</p>
        </div>

        <!-- Accesos rápidos -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-900 mb-4">⚡ Acciones Rápidas</h3>
          <div class="space-y-2">
            <router-link to="/soporte/usuarios"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-cyan-50 transition">
              <span class="text-xl">👥</span>
              <span class="text-sm font-medium text-gray-800">Gestionar usuarios</span>
              <span class="ml-auto text-gray-400">→</span>
            </router-link>
            <router-link to="/soporte/sesiones"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-cyan-50 transition">
              <span class="text-xl">🔐</span>
              <span class="text-sm font-medium text-gray-800">Ver sesiones activas</span>
              <span class="ml-auto text-gray-400">→</span>
            </router-link>
            <router-link to="/soporte/logs"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-cyan-50 transition">
              <span class="text-xl">📋</span>
              <span class="text-sm font-medium text-gray-800">Revisar logs de actividad</span>
              <span class="ml-auto text-gray-400">→</span>
            </router-link>
            <router-link to="/soporte/backups"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-cyan-50 transition">
              <span class="text-xl">💾</span>
              <span class="text-sm font-medium text-gray-800">Gestionar respaldos</span>
              <span class="ml-auto text-gray-400">→</span>
            </router-link>
          </div>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const stats = ref({})
const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'

onMounted(async () => {
  try {
    const { data } = await api.get('/soporte/dashboard')
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>
