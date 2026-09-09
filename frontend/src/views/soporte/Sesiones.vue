<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Sesiones Activas</h1>
        <p class="text-gray-500 mt-1">Usuarios con sesión iniciada · {{ sesiones.length }} activas</p>
      </div>
      <button @click="cargar" class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
        🔄 Actualizar
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Cargando sesiones...</div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Último uso</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Inicio</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Acción</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!sesiones.length">
              <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                <p class="text-4xl mb-2">🔐</p><p>No hay sesiones activas</p>
              </td>
            </tr>
            <tr v-for="s in sesiones" :key="s.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 font-bold text-xs">
                    {{ initials(s.name) }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 text-sm">{{ s.name }}</p>
                    <p class="text-xs text-gray-500">{{ s.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="roleBadge(s.role)">{{ s.role }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(s.last_used_at) }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(s.created_at) }}</td>
              <td class="px-6 py-4 text-center">
                <button @click="revocar(s)" class="text-red-600 hover:text-red-800 text-xs font-semibold hover:bg-red-50 px-3 py-1.5 rounded-lg transition">
                  Revocar
                </button>
              </td>
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
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const sesiones = ref([])

const initials = n => n?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?'
const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'
const roleBadge = r => ({
  desarrollador: 'bg-purple-100 text-purple-700', soporte_it: 'bg-cyan-100 text-cyan-700',
  administrativo: 'bg-blue-100 text-blue-700', profesor: 'bg-emerald-100 text-emerald-700',
  estudiante: 'bg-indigo-100 text-indigo-700',
}[r] ?? 'bg-gray-100 text-gray-700')

async function cargar() {
  loading.value = true
  try {
    const { data } = await api.get('/soporte/sesiones')
    sesiones.value = data
  } finally { loading.value = false }
}

async function revocar(s) {
  if (!confirm(`¿Revocar la sesión de ${s.name}?`)) return
  await api.delete(`/soporte/sesiones/${s.id}`)
  toast.success('Sesión revocada')
  await cargar()
}

onMounted(cargar)
</script>
