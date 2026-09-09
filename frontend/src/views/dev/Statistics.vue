<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Estadísticas del Sistema</h1>
      <p class="text-gray-500 mt-1">Uso y métricas de la plataforma</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 h-48 animate-pulse"></div>
    </div>

    <template v-else>
      <!-- Usuarios por rol -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-bold text-gray-900 mb-5">👥 Usuarios por Rol</h2>
          <div class="space-y-3">
            <div v-for="r in stats.usuarios?.por_rol" :key="r.role" class="flex items-center gap-3">
              <span class="text-sm font-medium text-gray-700 w-32">{{ r.role }}</span>
              <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-purple-500 transition-all duration-700"
                  :style="{ width: (r.count / stats.usuarios?.total * 100) + '%' }"></div>
              </div>
              <span class="text-sm font-bold text-gray-900 w-12 text-right">{{ r.count }}</span>
            </div>
          </div>
          <p class="text-sm text-gray-500 mt-4">Total: <strong>{{ stats.usuarios?.total }}</strong></p>
        </div>

        <!-- Estudiantes por estatus -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-bold text-gray-900 mb-5">🎓 Estudiantes por Estatus</h2>
          <div class="grid grid-cols-2 gap-3">
            <div v-for="e in stats.estudiantes?.por_estatus" :key="e.estatus"
              class="rounded-xl p-4 text-center" :class="getEstatusBg(e.estatus)">
              <p class="text-2xl font-bold" :class="getEstatusColor(e.estatus)">{{ e.count }}</p>
              <p class="text-xs text-gray-600 mt-1 capitalize">{{ e.estatus }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Académico -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Datos Académicos</h2>
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-blue-600">{{ stats.academico?.profesores?.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">Profesores</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-emerald-600">{{ stats.academico?.materias?.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">Materias</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-orange-600">{{ stats.academico?.aulas?.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">Aulas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-purple-600">{{ stats.academico?.carreras?.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">Carreras</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-cyan-600">{{ stats.academico?.inscripciones?.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">Inscripciones</p>
        </div>
      </div>

      <!-- Actividad reciente -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <h2 class="font-bold text-gray-900">📈 Top Acciones (últimos 7 días)</h2>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-for="(item, i) in stats.actividad_reciente" :key="item.accion"
            class="flex items-center gap-4 px-6 py-4">
            <span class="text-lg font-bold text-gray-400 w-6">{{ i + 1 }}</span>
            <span class="flex-1 text-sm font-mono text-gray-800">{{ item.accion }}</span>
            <div class="flex items-center gap-2">
              <div class="w-32 bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-purple-500 rounded-full"
                  :style="{ width: (item.count / stats.actividad_reciente[0]?.count * 100) + '%' }"></div>
              </div>
              <span class="text-sm font-bold text-gray-900 w-10 text-right">{{ item.count }}</span>
            </div>
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

const getEstatusBg    = e => ({ activo: 'bg-green-50', solicitante: 'bg-yellow-50', suspendido: 'bg-red-50', egresado: 'bg-blue-50', retirado: 'bg-gray-50' }[e] ?? 'bg-gray-50')
const getEstatusColor = e => ({ activo: 'text-green-700', solicitante: 'text-yellow-700', suspendido: 'text-red-700', egresado: 'text-blue-700', retirado: 'text-gray-700' }[e] ?? 'text-gray-700')

onMounted(async () => {
  try {
    const { data } = await api.get('/dev/statistics')
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>
