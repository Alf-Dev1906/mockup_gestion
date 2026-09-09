<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Explorador de Base de Datos</h1>
      <p class="text-gray-500 mt-1">Acceso directo a la estructura y datos de la BD</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Panel izquierdo: lista de tablas -->
      <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 text-sm">Tablas</h2>
            <span class="text-xs text-gray-500">{{ tables.length }} tablas</span>
          </div>
          <div v-if="loadingTables" class="p-4 space-y-2">
            <div v-for="n in 8" :key="n" class="h-8 bg-gray-100 rounded animate-pulse"></div>
          </div>
          <div v-else class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto">
            <button
              v-for="t in tables" :key="t.name"
              @click="selectTable(t)"
              class="w-full flex items-center justify-between px-5 py-3 hover:bg-purple-50 transition text-left"
              :class="selectedTable?.name === t.name ? 'bg-purple-50 border-l-2 border-purple-500' : ''">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ t.name }}</p>
                <p class="text-xs text-gray-500">{{ formatNumber(t.rows) }} filas</p>
              </div>
              <span class="text-xs text-gray-400">{{ t.size_mb }} MB</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Panel derecho: query + resultado -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Query Editor -->
        <div class="bg-white rounded-2xl border border-gray-100">
          <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 text-sm">Ejecutar Query <span class="text-xs font-normal text-gray-500 ml-2">Solo SELECT permitido</span></h2>
            <button @click="ejecutarQuery" :disabled="ejecutando || !query.trim()"
              class="bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-xl transition">
              {{ ejecutando ? '⏳ Ejecutando...' : '▶ Ejecutar' }}
            </button>
          </div>
          <div class="p-4">
            <textarea
              v-model="query"
              rows="5"
              placeholder="SELECT * FROM estudiantes LIMIT 10;"
              class="w-full font-mono text-sm border border-gray-200 rounded-xl p-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 bg-gray-950 text-green-400 placeholder-gray-600 resize-none"
            ></textarea>
          </div>
        </div>

        <!-- Resultado -->
        <div v-if="queryResult !== null" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 text-sm">Resultado</h2>
            <span v-if="!queryError" class="text-xs text-green-600 font-semibold">{{ queryResult.rows }} filas</span>
            <span v-else class="text-xs text-red-600 font-semibold">Error</span>
          </div>
          <div v-if="queryError" class="p-4">
            <p class="text-sm text-red-600 font-mono bg-red-50 p-3 rounded-xl">{{ queryError }}</p>
          </div>
          <div v-else-if="queryResult.data?.length" class="overflow-x-auto max-h-96">
            <table class="w-full text-xs">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th v-for="col in Object.keys(queryResult.data[0])" :key="col"
                    class="px-4 py-2 text-left font-semibold text-gray-600 uppercase whitespace-nowrap">{{ col }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 font-mono">
                <tr v-for="(row, i) in queryResult.data" :key="i" class="hover:bg-gray-50">
                  <td v-for="(val, col) in row" :key="col" class="px-4 py-2 text-gray-800 whitespace-nowrap">
                    {{ val ?? 'NULL' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-6 text-center text-gray-500 text-sm">Sin resultados</div>
        </div>

        <!-- Info tabla seleccionada -->
        <div v-if="selectedTable" class="bg-white rounded-2xl border border-gray-100 p-5">
          <h3 class="font-semibold text-gray-900 mb-3">📋 {{ selectedTable.name }}</h3>
          <div class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-purple-50 rounded-xl p-3">
              <p class="text-xl font-bold text-purple-700">{{ formatNumber(selectedTable.rows) }}</p>
              <p class="text-xs text-gray-500 mt-1">Filas</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-3">
              <p class="text-xl font-bold text-blue-700">{{ selectedTable.size_mb }} MB</p>
              <p class="text-xs text-gray-500 mt-1">Tamaño</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
              <button @click="query = `SELECT * FROM ${selectedTable.name} LIMIT 20;`"
                class="text-xs font-semibold text-gray-700 hover:text-purple-700 transition">Ver datos →</button>
              <p class="text-xs text-gray-500 mt-1">Quick View</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const tables = ref([])
const loadingTables = ref(true)
const selectedTable = ref(null)
const query = ref('')
const ejecutando = ref(false)
const queryResult = ref(null)
const queryError = ref(null)

const formatNumber = n => n?.toLocaleString('es-VE') ?? '—'

onMounted(async () => {
  try {
    const { data } = await api.get('/dev/database')
    tables.value = data
  } finally {
    loadingTables.value = false
  }
})

function selectTable(t) {
  selectedTable.value = t
  query.value = `SELECT * FROM ${t.name} LIMIT 20;`
}

async function ejecutarQuery() {
  if (!query.value.trim()) return
  ejecutando.value = true
  queryResult.value = null
  queryError.value = null
  try {
    const { data } = await api.post('/dev/database/query', { query: query.value })
    queryResult.value = data
  } catch (e) {
    queryError.value = e.response?.data?.error || 'Error al ejecutar query'
    queryResult.value = { rows: 0, data: [] }
  } finally {
    ejecutando.value = false
  }
}
</script>
