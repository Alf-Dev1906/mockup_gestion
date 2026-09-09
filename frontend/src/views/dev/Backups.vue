<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Backups</h1>
        <p class="text-gray-500 mt-1">Respaldos de la base de datos del sistema</p>
      </div>
      <button @click="modalAbierto = true"
        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2">
        💾 Nuevo Backup
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <p class="text-3xl font-bold text-gray-900">{{ backups.length }}</p>
        <p class="text-sm text-gray-500 mt-1">Total de Backups</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <p class="text-3xl font-bold text-green-600">{{ completados }}</p>
        <p class="text-sm text-gray-500 mt-1">Completados</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <p class="text-lg font-bold text-gray-900">{{ ultimoBackup }}</p>
        <p class="text-sm text-gray-500 mt-1">Último Backup</p>
      </div>
    </div>

    <!-- Tabla de backups -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Cargando backups...</div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Tamaño</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Notas</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!backups.length">
              <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                <p class="text-4xl mb-2">💾</p>
                <p>No hay backups registrados</p>
              </td>
            </tr>
            <tr v-for="b in backups" :key="b.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono text-xs text-gray-800">{{ b.nombre }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                  :class="b.tipo === 'automatico' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'">
                  {{ b.tipo }}
                </span>
              </td>
              <td class="px-6 py-4 text-center text-sm text-gray-600">{{ formatSize(b.tamano) }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                  :class="b.estatus === 'completado' ? 'bg-green-100 text-green-700' : b.estatus === 'fallido' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'">
                  {{ b.estatus }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ b.notas || '—' }}</td>
              <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(b.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal nuevo backup -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">💾 Crear Backup</h2>
          <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="crearBackup" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Notas (opcional)</label>
            <textarea v-model="form.notas" rows="3" placeholder="Describe el motivo del backup..."
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 text-gray-900 resize-none">
            </textarea>
          </div>
          <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-800">
            ⚠️ El backup puede tardar varios minutos dependiendo del tamaño de la base de datos.
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modalAbierto = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button type="submit" :disabled="creando" class="flex-1 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ creando ? '⏳ Creando...' : '💾 Crear Backup' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const modalAbierto = ref(false)
const creando = ref(false)
const backups = ref([])
const form = ref({ notas: '' })

const completados = computed(() => backups.value.filter(b => b.estatus === 'completado').length)
const ultimoBackup = computed(() => {
  const ultimo = backups.value[0]
  return ultimo ? formatDate(ultimo.created_at) : 'Ninguno'
})

const formatSize = bytes => {
  if (!bytes) return '—'
  if (bytes > 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB'
  return (bytes / 1024).toFixed(0) + ' KB'
}
const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'

async function cargar() {
  loading.value = true
  try {
    const { data } = await api.get('/dev/backups')
    backups.value = data
  } finally {
    loading.value = false
  }
}

async function crearBackup() {
  creando.value = true
  try {
    await api.post('/dev/backup', form.value)
    toast.success('Backup creado exitosamente')
    modalAbierto.value = false
    form.value = { notas: '' }
    await cargar()
  } catch {
    toast.error('Error al crear el backup')
  } finally {
    creando.value = false
  }
}

onMounted(cargar)
</script>
