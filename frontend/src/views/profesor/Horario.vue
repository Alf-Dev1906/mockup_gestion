<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mi Horario</h1>
      <p class="text-gray-500 mt-1">Distribución semanal de clases</p>
    </div>

    <div v-if="loading" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-64"></div>

    <template v-else>
      <!-- Vista de tabla semanal -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-emerald-50 border-b border-emerald-200">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase w-24">Día</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Hora</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Materia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Aula</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <template v-for="dia in diasOrdenados" :key="dia">
                <tr v-for="(h, i) in horariosPorDia[dia]" :key="h.id"
                  class="hover:bg-emerald-50 transition">
                  <td class="px-4 py-3">
                    <span v-if="i === 0" class="font-bold text-emerald-700 text-xs uppercase">{{ dia }}</span>
                  </td>
                  <td class="px-4 py-3 font-mono text-gray-700 whitespace-nowrap">
                    {{ h.hora_inicio }} – {{ h.hora_fin }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-0.5 rounded font-mono">
                        {{ h.materia.codigo }}
                      </span>
                      <span class="font-medium text-gray-900">{{ h.materia.nombre }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-gray-600 text-xs">
                    {{ h.aula ? `Aula ${h.aula.numero} – ${h.aula.edificio}` : '—' }}
                  </td>
                </tr>
              </template>
              <tr v-if="!horarios.length">
                <td colspan="4" class="px-6 py-16 text-center text-gray-400">
                  <p class="text-4xl mb-2">🕐</p>
                  <p>No tienes horarios asignados</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Resumen por materia -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Resumen de Carga Horaria</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="m in resumenMaterias" :key="m.codigo"
          class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">{{ m.codigo }}</span>
              <p class="font-semibold text-gray-900 mt-2 text-sm">{{ m.nombre }}</p>
            </div>
            <span class="text-2xl font-bold text-emerald-600">{{ m.horas }}h</span>
          </div>
          <p class="text-xs text-gray-500 mt-2">{{ m.secciones }} sección(es)</p>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const horarios = ref([])

const DIAS_ORDEN = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']

const horariosPorDia = computed(() => {
  const map = {}
  for (const h of horarios.value) {
    if (!map[h.dia_semana]) map[h.dia_semana] = []
    map[h.dia_semana].push(h)
  }
  // Ordenar por hora dentro de cada día
  for (const dia in map) {
    map[dia].sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio))
  }
  return map
})

const diasOrdenados = computed(() =>
  DIAS_ORDEN.filter(d => horariosPorDia.value[d])
)

const resumenMaterias = computed(() => {
  const map = new Map()
  for (const h of horarios.value) {
    const id = h.materia.codigo
    if (!map.has(id)) {
      map.set(id, { codigo: h.materia.codigo, nombre: h.materia.nombre, horas: 0, secciones: 0 })
    }
    const entry = map.get(id)
    // Calcular horas del slot
    const [hi, hf] = [h.hora_inicio, h.hora_fin].map(t => {
      const [hours, mins] = t.split(':').map(Number)
      return hours + mins / 60
    })
    entry.horas += Math.round(hf - hi)
    entry.secciones++
  }
  return [...map.values()]
})

onMounted(async () => {
  try { const { data } = await api.get('/profesor/horario'); horarios.value = data }
  finally { loading.value = false }
})
</script>
