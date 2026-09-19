<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mi Horario</h1>
      <p class="text-gray-500 mt-1">Distribución semanal de clases y carga académica</p>
    </div>

    <div v-if="loading" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div v-for="n in 4" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse h-24"></div>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-96"></div>
    </div>

    <template v-else>
      <!-- Stats Resumen -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-emerald-600">{{ totalClases }}</p>
          <p class="text-xs text-gray-500 mt-1">Clases semanales</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-blue-600">{{ totalHorasSemana }}h</p>
          <p class="text-xs text-gray-500 mt-1">Horas en aula</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-purple-600">{{ materiasUnicas }}</p>
          <p class="text-xs text-gray-500 mt-1">Materias distintas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-orange-600">{{ diasActivos }}</p>
          <p class="text-xs text-gray-500 mt-1">Días con clases</p>
        </div>
      </div>

      <!-- Vista de tabla semanal -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4">
          <h3 class="text-white font-semibold flex items-center gap-2">
            <span class="text-xl">📅</span>
            <span>Calendario Semanal</span>
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-emerald-50 border-b border-emerald-200">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-emerald-800 uppercase w-28">Día</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-emerald-800 uppercase w-32">Hora</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Materia</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Sección</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-emerald-800 uppercase">Aula</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-emerald-800 uppercase">Duración</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <template v-for="dia in diasOrdenados" :key="dia">
                <tr v-for="(h, i) in horariosPorDia[dia]" :key="h.id"
                  class="hover:bg-emerald-50 transition-colors">
                  <td class="px-5 py-4">
                    <span v-if="i === 0" 
                      class="inline-flex items-center font-bold text-xs uppercase px-3 py-1.5 rounded-lg"
                      :class="diaColorClass(dia)">
                      {{ dia.substring(0, 3) }}
                    </span>
                  </td>
                  <td class="px-5 py-4 font-mono text-gray-700 whitespace-nowrap text-sm">
                    {{ h.hora_inicio }} – {{ h.hora_fin }}
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                      <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded font-mono">
                        {{ h.codigo_materia }}
                      </span>
                      <div>
                        <p class="font-medium text-gray-900">{{ h.materia }}</p>
                        <p class="text-xs text-gray-500">{{ h.creditos || 3 }} créditos</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-1 rounded">
                      {{ h.seccion }}
                    </span>
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-1.5 text-gray-600">
                      <span class="text-blue-600">🏫</span>
                      <span class="text-sm">{{ h.aula }}</span>
                    </div>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="text-xs text-gray-500 font-medium">{{ h.duracion }}</span>
                  </td>
                </tr>
              </template>
              <tr v-if="!horarios.length">
                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                  <p class="text-5xl mb-3">🕐</p>
                  <p class="font-semibold text-lg">No tienes horarios asignados</p>
                  <p class="text-sm mt-1">Contacta al coordinador académico</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Resumen por materia -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">📚 Materias Asignadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="m in resumenMaterias" :key="m.codigo"
          class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded">
                {{ m.codigo }}
              </span>
              <p class="font-semibold text-gray-900 mt-2">{{ m.nombre }}</p>
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold text-emerald-600">{{ m.horas }}</p>
              <p class="text-xs text-gray-500">horas</p>
            </div>
          </div>
          <div class="flex items-center justify-between text-xs text-gray-600 pt-3 border-t border-gray-100">
            <span>{{ m.secciones }} sección(es)</span>
            <span>{{ m.clases }} clase(s)/semana</span>
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
  'Lunes': 'bg-blue-100 text-blue-700',
  'Martes': 'bg-purple-100 text-purple-700',
  'Miércoles': 'bg-emerald-100 text-emerald-700',
  'Jueves': 'bg-orange-100 text-orange-700',
  'Viernes': 'bg-pink-100 text-pink-700',
  'Sábado': 'bg-cyan-100 text-cyan-700',
  'Domingo': 'bg-gray-100 text-gray-700',
}

const diaColorClass = (dia) => DIA_COLORS[dia] ?? 'bg-gray-100 text-gray-700'

const horariosPorDia = computed(() => {
  const map = {}
  for (const h of horarios.value) {
    const dia = h.dia_semana.charAt(0).toUpperCase() + h.dia_semana.slice(1)
    if (!map[dia]) map[dia] = []
    
    // Calcular duración
    const duracion = calcularDuracion(h.hora_inicio, h.hora_fin)
    
    map[dia].push({
      ...h,
      hora_inicio: h.hora_inicio.substring(0, 5),
      hora_fin: h.hora_fin.substring(0, 5),
      duracion,
    })
  }
  
  // Ordenar por hora dentro de cada día
  for (const dia in map) {
    map[dia].sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio))
  }
  return map
})

const calcularDuracion = (inicio, fin) => {
  try {
    const [hI, mI] = inicio.substring(0, 5).split(':').map(Number)
    const [hF, mF] = fin.substring(0, 5).split(':').map(Number)
    const minutos = (hF * 60 + mF) - (hI * 60 + mI)
    const horas = Math.floor(minutos / 60)
    const mins = minutos % 60
    return mins > 0 ? `${horas}h ${mins}m` : `${horas}h`
  } catch {
    return '2h'
  }
}

const diasOrdenados = computed(() =>
  DIAS_ORDEN.filter(d => horariosPorDia.value[d])
)

const totalClases = computed(() => horarios.value.length)

const totalHorasSemana = computed(() => {
  let total = 0
  for (const h of horarios.value) {
    try {
      const [hI, mI] = h.hora_inicio.substring(0, 5).split(':').map(Number)
      const [hF, mF] = h.hora_fin.substring(0, 5).split(':').map(Number)
      const minutos = (hF * 60 + mF) - (hI * 60 + mI)
      total += minutos / 60
    } catch {
      total += 2 // Fallback: 2 horas
    }
  }
  return Math.round(total)
})

const materiasUnicas = computed(() => {
  const codigos = new Set(horarios.value.map(h => h.codigo_materia))
  return codigos.size
})

const diasActivos = computed(() => Object.keys(horariosPorDia.value).length)

const resumenMaterias = computed(() => {
  const map = new Map()
  for (const h of horarios.value) {
    const id = h.codigo_materia
    if (!map.has(id)) {
      map.set(id, { 
        codigo: id, 
        nombre: h.materia, 
        horas: 0, 
        secciones: new Set(),
        clases: 0
      })
    }
    const entry = map.get(id)
    
    // Calcular horas
    try {
      const [hI, mI] = h.hora_inicio.substring(0, 5).split(':').map(Number)
      const [hF, mF] = h.hora_fin.substring(0, 5).split(':').map(Number)
      const minutos = (hF * 60 + mF) - (hI * 60 + mI)
      entry.horas += Math.round(minutos / 60)
    } catch {
      entry.horas += 2
    }
    
    entry.secciones.add(h.seccion)
    entry.clases++
  }
  
  return [...map.values()].map(m => ({
    ...m,
    secciones: m.secciones.size,
    horas: m.horas + 'h'
  }))
})

onMounted(async () => {
  try { 
    const { data } = await api.get('/profesor/horario')
    const apiData = data.data || data
    
    // Convertir objeto de días a array plano
    const horariosFlat = []
    if (typeof apiData === 'object' && !Array.isArray(apiData)) {
      for (const [dia, clases] of Object.entries(apiData)) {
        for (const clase of clases) {
          horariosFlat.push({
            ...clase,
            dia_semana: dia,
            creditos: clase.creditos || 3,
          })
        }
      }
      horarios.value = horariosFlat
    } else {
      horarios.value = Array.isArray(apiData) ? apiData : []
    }
  }
  finally { loading.value = false }
})
</script>
