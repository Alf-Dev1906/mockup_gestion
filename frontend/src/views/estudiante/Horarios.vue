<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mis Horarios</h1>
      <p class="text-gray-500 mt-1">Distribución semanal de tus clases</p>
    </div>

    <div v-if="loading" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-64"></div>

    <div v-else-if="!horarios.length" class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">🕐</p>
      <p class="font-semibold">No tienes clases inscritas</p>
      <router-link to="/estudiante/inscripcion" class="mt-3 inline-block text-indigo-600 hover:underline text-sm">
        Ir a inscripción →
      </router-link>
    </div>

    <template v-else>
      <!-- Tabla semanal -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[680px]">
            <thead class="bg-indigo-50 border-b border-indigo-200">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-800 uppercase w-28">Día</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-800 uppercase">Hora</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-800 uppercase">Materia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-800 uppercase">Profesor</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-800 uppercase">Aula</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <template v-for="dia in diasOrdenados" :key="dia">
                <tr v-for="(h, i) in horariosPorDia[dia]" :key="h.id" class="hover:bg-indigo-50/40 transition">
                  <td class="px-4 py-3">
                    <span v-if="i === 0"
                      class="text-xs font-bold uppercase px-2 py-1 rounded-lg"
                      :class="diaColorClass(dia)">{{ dia }}</span>
                  </td>
                  <td class="px-4 py-3 font-mono text-gray-700 whitespace-nowrap text-xs">
                    {{ h.hora_inicio }} – {{ h.hora_fin }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded font-mono">
                        {{ h.materia_codigo }}
                      </span>
                      <span class="font-medium text-gray-900">{{ h.materia_nombre }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ h.creditos }} créditos</p>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ h.profesor }}</td>
                  <td class="px-4 py-3 text-xs text-gray-500">
                    {{ h.aula_codigo ? `${h.aula_codigo} · ${h.aula_edificio}` : '—' }}
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Cards resumen por materia -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Materias inscritas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="m in resumenMaterias" :key="m.codigo"
          class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between mb-3">
            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded font-mono">{{ m.codigo }}</span>
            <span class="text-xs text-gray-400">{{ m.creditos }} créd.</span>
          </div>
          <p class="font-semibold text-gray-900 text-sm">{{ m.nombre }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ m.profesor }}</p>
          <div class="flex flex-wrap gap-1 mt-3">
            <span v-for="slot in m.horarios" :key="slot.id"
              class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg font-mono">
              {{ slot.dia_semana.slice(0,3) }} {{ slot.hora_inicio }}
            </span>
          </div>
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
const DIA_COLORS = {
  'Lunes': 'bg-blue-100 text-blue-700', 'Martes': 'bg-purple-100 text-purple-700',
  'Miércoles': 'bg-emerald-100 text-emerald-700', 'Jueves': 'bg-orange-100 text-orange-700',
  'Viernes': 'bg-pink-100 text-pink-700', 'Sábado': 'bg-cyan-100 text-cyan-700',
  'Domingo': 'bg-gray-100 text-gray-700',
}
const diaColorClass = dia => DIA_COLORS[dia] ?? 'bg-gray-100 text-gray-700'

const horariosPorDia = computed(() => {
  const map = {}
  for (const h of horarios.value) {
    if (!map[h.dia_semana]) map[h.dia_semana] = []
    map[h.dia_semana].push(h)
  }
  for (const dia in map) map[dia].sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio))
  return map
})

const diasOrdenados = computed(() => DIAS_ORDEN.filter(d => horariosPorDia.value[d]))

const resumenMaterias = computed(() => {
  const map = new Map()
  for (const h of horarios.value) {
    const k = h.materia_codigo
    if (!map.has(k)) {
      map.set(k, { codigo: k, nombre: h.materia_nombre, creditos: h.creditos, profesor: h.profesor, horarios: [] })
    }
    map.get(k).horarios.push(h)
  }
  return [...map.values()]
})

onMounted(async () => {
  try { const { data } = await api.get('/estudiante/horarios'); horarios.value = data }
  finally { loading.value = false }
})
</script>
