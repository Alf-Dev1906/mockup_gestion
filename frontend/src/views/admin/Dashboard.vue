<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Panel Administrativo</h1>
        <p class="text-gray-500 mt-1">Gestión académica y admisiones — Nivel 3</p>
      </div>
      <span class="bg-blue-100 text-blue-700 text-xs font-bold px-4 py-2 rounded-full">🏛️ ADMINISTRATIVO</span>
    </div>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse h-28"></div>
    </div>

    <template v-else>
      <!-- KPIs principales -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div @click="$router.push('/admin/solicitudes')"
          class="bg-white rounded-2xl border p-5 cursor-pointer hover:shadow-md transition"
          :class="stats.solicitudes_pendientes > 0 ? 'border-yellow-300 bg-yellow-50' : 'border-gray-100'">
          <p class="text-3xl font-bold" :class="stats.solicitudes_pendientes > 0 ? 'text-yellow-600' : 'text-gray-900'">
            {{ stats.solicitudes_pendientes ?? 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Solicitudes Pendientes</p>
          <p v-if="stats.solicitudes_pendientes > 0" class="text-xs text-yellow-600 font-semibold mt-1">⚠️ Requiere atención</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <p class="text-3xl font-bold text-blue-600">{{ stats.inscripciones_periodo_actual?.toLocaleString() ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">Inscripciones Período</p>
        </div>
        <div @click="$router.push('/admin/pagos')"
          class="bg-white rounded-2xl border p-5 cursor-pointer hover:shadow-md transition"
          :class="stats.pagos_pendientes > 0 ? 'border-orange-200' : 'border-gray-100'">
          <p class="text-3xl font-bold" :class="stats.pagos_pendientes > 0 ? 'text-orange-600' : 'text-gray-900'">
            {{ stats.pagos_pendientes ?? 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Pagos Pendientes</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex flex-wrap gap-1">
            <span v-for="e in stats.estudiantes_por_estatus" :key="e.estatus"
              class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="estatusBadge(e.estatus)">
              {{ e.estatus }}: {{ e.count }}
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-2">Estudiantes por estatus</p>
        </div>
      </div>

      <!-- Inscripciones recientes -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Inscripciones Recientes</h2>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-8">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estudiante</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Materia</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="i in stats.inscripciones_recientes" :key="i.id" class="hover:bg-gray-50">
              <td class="px-5 py-3 font-medium text-gray-900">{{ i.estudiante_nombre }} {{ i.estudiante_apellido }}</td>
              <td class="px-5 py-3 text-gray-600">{{ i.materia_nombre }}</td>
              <td class="px-5 py-3 text-xs text-gray-500">{{ formatDate(i.created_at) }}</td>
            </tr>
            <tr v-if="!stats.inscripciones_recientes?.length">
              <td colspan="3" class="px-5 py-8 text-center text-gray-400">Sin inscripciones recientes</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Accesos rápidos -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Gestión Rápida</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
          class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-blue-200 transition text-center">
          <span class="text-3xl">{{ link.icon }}</span>
          <p class="text-sm font-semibold text-gray-900 mt-2">{{ link.label }}</p>
        </router-link>
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
const estatusBadge = e => ({
  activo: 'bg-green-100 text-green-700', solicitante: 'bg-yellow-100 text-yellow-700',
  suspendido: 'bg-red-100 text-red-700', egresado: 'bg-blue-100 text-blue-700',
  retirado: 'bg-gray-100 text-gray-700',
}[e] ?? 'bg-gray-100 text-gray-700')

const quickLinks = [
  { to: '/admin/solicitudes',  icon: '📨', label: 'Solicitudes' },
  { to: '/admin/estudiantes',  icon: '👨‍🎓', label: 'Estudiantes' },
  { to: '/admin/inscripciones',icon: '📝', label: 'Inscripciones' },
  { to: '/admin/pagos',        icon: '💰', label: 'Pagos' },
  { to: '/admin/horarios',     icon: '🕐', label: 'Horarios' },
  { to: '/admin/materias',     icon: '📖', label: 'Materias' },
  { to: '/admin/profesores',   icon: '👨‍🏫', label: 'Profesores' },
  { to: '/admin/reportes',     icon: '📈', label: 'Reportes' },
]

onMounted(async () => {
  try { const { data } = await api.get('/admin/dashboard'); stats.value = data }
  finally { loading.value = false }
})
</script>
