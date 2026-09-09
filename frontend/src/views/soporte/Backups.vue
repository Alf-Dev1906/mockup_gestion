<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Respaldos del Sistema</h1>
        <p class="text-gray-500 mt-1">Historial de backups de la base de datos</p>
      </div>
      <button @click="modalAbierto = true"
        class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2">
        💾 Crear Respaldo
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-gray-900">{{ backups.length }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Respaldos</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <p class="text-3xl font-bold text-green-600">{{ backups.filter(b => b.estatus === 'completado').length }}</p>
        <p class="text-xs text-gray-500 mt-1">Exitosos</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm font-semibold text-gray-900">Último respaldo</p>
        <p class="text-xs text-gray-500 mt-1 truncate">{{ backups[0]?.nombre || 'Ninguno' }}</p>
        <p class="text-xs text-gray-400">{{ backups[0] ? formatDate(backups[0].created_at) : '' }}</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Cargando...</div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Tamaño</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Creado por</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!backups.length"><td colspan="6" class="px-6 py-16 text-center text-gray-400">Sin respaldos</td></tr>
            <tr v-for="b in backups" :key="b.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono text-xs text-gray-800 max-w-xs truncate">{{ b.nombre }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                  :class="b.tipo === 'automatico' ? 'bg-blue-100 text-blue-700' : 'bg-cyan-100 text-cyan-700'">
                  {{ b.tipo }}
                </span>
              </td>
              <td class="px-6 py-4 text-center text-sm text-gray-600">{{ formatSize(b.tamano) }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                  :class="b.estatus === 'completado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ b.estatus }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ b.creado_por_nombre || '—' }}</td>
              <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(b.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">💾 Crear Respaldo</h2>
          <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="crearBackup" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Notas (opcional)</label>
            <textarea v-model="notas" rows="3" placeholder="Motivo del respaldo..."
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-gray-900 resize-none"></textarea>
          </div>
          <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-800">
            ⚠️ El proceso puede tardar varios minutos.
          </div>
          <div class="flex gap-3">
            <button type="button" @click="modalAbierto = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button type="submit" :disabled="creando" class="flex-1 bg-cyan-600 hover:bg-cyan-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ creando ? '⏳ Creando...' : '💾 Crear' }}
            </button>
          </div>
        </form>
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
const modalAbierto = ref(false)
const creando = ref(false)
const backups = ref([])
const notas = ref('')

const formatSize = b => !b ? '—' : b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : (b / 1024).toFixed(0) + ' KB'
const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'

async function cargar() {
  loading.value = true
  try { const { data } = await api.get('/soporte/backups'); backups.value = data }
  finally { loading.value = false }
}

async function crearBackup() {
  creando.value = true
  try {
    await api.post('/soporte/backups', { notas: notas.value })
    toast.success('Respaldo creado exitosamente')
    modalAbierto.value = false
    notas.value = ''
    await cargar()
  } finally { creando.value = false }
}

onMounted(cargar)
</script>
