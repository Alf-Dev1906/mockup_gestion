<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mis Calificaciones</h1>
      <p class="text-gray-500 mt-1">Registro académico completo</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div v-for="n in 3" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-24"></div>
    </div>

    <template v-else>
      <!-- Resumen académico -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold" :class="data.resumen?.promedio >= 10 ? 'text-green-600' : 'text-red-600'">
            {{ data.resumen?.promedio?.toFixed(2) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Promedio</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-green-600">{{ data.resumen?.aprobadas }}</p>
          <p class="text-xs text-gray-500 mt-1">Aprobadas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-red-600">{{ data.resumen?.reprobadas }}</p>
          <p class="text-xs text-gray-500 mt-1">Reprobadas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-bold text-yellow-600">{{ data.resumen?.pendientes }}</p>
          <p class="text-xs text-gray-500 mt-1">Pendientes</p>
        </div>
      </div>

      <!-- Barra de progreso del promedio -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-semibold text-gray-700">Rendimiento académico</span>
          <span class="text-sm font-bold" :class="data.resumen?.promedio >= 10 ? 'text-green-600' : 'text-red-600'">
            {{ data.resumen?.promedio >= 10 ? 'Aprobado' : 'Requiere mejora' }}
          </span>
        </div>
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-700"
            :class="data.resumen?.promedio >= 10 ? 'bg-green-500' : 'bg-red-500'"
            :style="{ width: Math.min(100, (data.resumen?.promedio / 20) * 100) + '%' }"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
          <span>0</span><span>Aprobado: 10</span><span>Excelente: 20</span>
        </div>
      </div>

      <!-- Tabla de calificaciones -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-5 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Materia</th>
                <th class="px-5 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Periodo</th>
                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Corte 1 (30%)</th>
                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Corte 2 (30%)</th>
                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Corte 3 (40%)</th>
                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Final</th>
                <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="!data.calificaciones?.length">
                <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                  <p class="text-4xl mb-2">🎓</p>
                  <p>No tienes materias inscritas</p>
                </td>
              </tr>
              <tr v-for="c in data.calificaciones" :key="c.inscripcion_id" class="hover:bg-gray-50">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-mono font-bold px-2 py-0.5 rounded">
                      {{ c.codigo }}
                    </span>
                    <div>
                      <p class="font-medium text-gray-900 text-sm">{{ c.materia }}</p>
                      <p class="text-xs text-gray-500">{{ c.profesor }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4 text-center text-xs text-gray-600">{{ c.periodo || 'N/A' }}</td>
                <td class="px-5 py-4 text-center text-sm font-semibold" :class="notaColor(c.nota_corte_1)">
                  {{ c.nota_corte_1 !== null ? Number(c.nota_corte_1).toFixed(2) : '—' }}
                </td>
                <td class="px-5 py-4 text-center text-sm font-semibold" :class="notaColor(c.nota_corte_2)">
                  {{ c.nota_corte_2 !== null ? Number(c.nota_corte_2).toFixed(2) : '—' }}
                </td>
                <td class="px-5 py-4 text-center text-sm font-semibold" :class="notaColor(c.nota_corte_3)">
                  {{ c.nota_corte_3 !== null ? Number(c.nota_corte_3).toFixed(2) : '—' }}
                </td>
                <td class="px-5 py-4 text-center">
                  <span v-if="c.nota_final !== null" class="text-base font-bold" :class="notaColor(c.nota_final, 10)">
                    {{ Number(c.nota_final).toFixed(2) }}
                  </span>
                  <span v-else class="text-xs text-gray-400">Pendiente</span>
                </td>
                <td class="px-5 py-4 text-center">
                  <span v-if="c.nota_final !== null"
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                    :class="c.nota_final >= 10 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                    {{ c.nota_final >= 10 ? '✓ Aprobado' : '✗ Reprobado' }}
                  </span>
                  <span v-else class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">⏳ Cursando</span>
                </td>
              </tr>
            </tbody>
          </table>
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
const data = ref({ calificaciones: [], resumen: {} })

const notaColor = (nota, umbral = 10) => {
  if (nota === null || nota === undefined) return 'text-gray-300'
  return nota >= umbral ? 'text-green-600' : 'text-red-600'
}

onMounted(async () => {
  try { 
    const { data: res } = await api.get('/estudiante/calificaciones')
    const apiData = res.data || res // Soporte para respuesta con wrapper
    
    // Calcular resumen
    const califs = Array.isArray(apiData) ? apiData : []
    const conNota = califs.filter(c => c.nota_final !== null && c.nota_final !== undefined)
    const aprobadas = conNota.filter(c => c.nota_final >= 10).length
    const reprobadas = conNota.filter(c => c.nota_final < 10).length
    const pendientes = califs.length - conNota.length
    const promedio = conNota.length > 0 
      ? conNota.reduce((sum, c) => sum + Number(c.nota_final), 0) / conNota.length 
      : 0
    
    data.value = {
      calificaciones: califs,
      resumen: {
        promedio,
        aprobadas,
        reprobadas,
        pendientes,
      }
    }
  }
  finally { loading.value = false }
})
</script>
