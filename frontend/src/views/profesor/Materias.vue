<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mis Materias</h1>
      <p class="text-gray-500 mt-1">Materias asignadas y sus estudiantes</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-52"></div>
    </div>

    <div v-else-if="!materias.length" class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">📖</p>
      <p class="font-semibold">No tienes materias asignadas</p>
      <p class="text-sm mt-1">Contacta a administración si crees que hay un error</p>
    </div>

    <!-- Grid de materias agrupadas -->
    <div v-else>
      <!-- Agrupar por materia -->
      <div v-for="grupo in materiasAgrupadas" :key="grupo.materiaId" class="mb-8">
        <div class="bg-white rounded-2xl border border-emerald-200 overflow-hidden">
          <!-- Header de la materia -->
          <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-4 flex items-center justify-between">
            <div>
              <div class="flex items-center gap-3">
                <span class="bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full font-mono">{{ grupo.codigo }}</span>
                <h2 class="font-bold text-gray-900">{{ grupo.nombre }}</h2>
              </div>
              <p class="text-sm text-gray-500 mt-1">{{ grupo.carrera }} · {{ grupo.creditos }} créditos</p>
            </div>
            <button @click="verEstudiantes(grupo)"
              class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
              👨‍🎓 Ver estudiantes ({{ grupo.totalEstudiantes }})
            </button>
          </div>

          <!-- Horarios de la materia -->
          <div class="px-6 py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Horarios</p>
            <div class="flex flex-wrap gap-2">
              <div v-for="h in grupo.horarios" :key="h.id"
                class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm">
                <span class="font-semibold text-gray-900 capitalize">{{ h.dia_semana }}</span>
                <span class="text-gray-600 ml-2">{{ formatTime(h.hora_inicio) }} – {{ formatTime(h.hora_fin) }}</span>
                <span v-if="h.aula" class="text-gray-400 ml-2 text-xs">· {{ h.aula }}</span>
                <span v-if="h.seccion" class="text-emerald-600 ml-2 text-xs font-semibold">Sec. {{ h.seccion }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel lateral de estudiantes -->
    <div v-if="panelEstudiantes" class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 p-0 md:p-4">
      <div class="bg-white w-full md:rounded-2xl shadow-2xl md:max-w-2xl max-h-[80vh] flex flex-col">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
          <div>
            <h2 class="font-bold text-gray-900">{{ panelMateria?.nombre }}</h2>
            <p class="text-xs text-gray-500">{{ estudiantesMateria.length }} estudiantes</p>
          </div>
          <div class="flex gap-2">
            <router-link :to="`/profesor/calificaciones?materia=${panelMateria?.materiaId}`"
              class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
              📝 Calificar
            </router-link>
            <button @click="panelEstudiantes = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
          </div>
        </div>
        <div class="overflow-y-auto flex-1">
          <div v-if="loadingEstudiantes" class="p-6 text-center text-gray-400">Cargando...</div>
          <table v-else class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estudiante</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Matrícula</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Nota Final</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="e in estudiantesMateria" :key="e.inscripcion_id" class="hover:bg-gray-50">
                <td class="px-5 py-3">
                  <p class="font-medium text-gray-900">{{ e.estudiante.nombre }} {{ e.estudiante.apellido }}</p>
                  <p class="text-xs text-gray-500">{{ e.estudiante.email }}</p>
                </td>
                <td class="px-5 py-3 font-mono text-sm text-gray-700">{{ e.estudiante.matricula }}</td>
                <td class="px-5 py-3 text-center">
                  <span v-if="e.calificacion?.nota_final !== null && e.calificacion?.nota_final !== undefined"
                    class="font-bold text-sm"
                    :class="e.calificacion.nota_final >= 10 ? 'text-green-600' : 'text-red-600'">
                    {{ e.calificacion.nota_final }}
                  </span>
                  <span v-else class="text-xs text-gray-400">Pendiente</span>
                </td>
              </tr>
              <tr v-if="!estudiantesMateria.length">
                <td colspan="3" class="px-5 py-8 text-center text-gray-400">Sin estudiantes inscritos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const loadingEstudiantes = ref(false)
const materias = ref([])
const panelEstudiantes = ref(false)
const panelMateria = ref(null)
const estudiantesMateria = ref([])

// Formatear hora de timestamp a HH:MM
const formatTime = (timestamp) => {
  if (!timestamp) return ''
  try {
    const date = new Date(timestamp)
    return date.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit', hour12: false })
  } catch {
    return timestamp.substring(0, 5) // Fallback
  }
}

// Agrupar horarios por materia - Adaptado al nuevo formato del backend
const materiasAgrupadas = computed(() => {
  // El backend ya envía materias agrupadas con horarios
  return materias.value.map(m => ({
    materiaId: m.id,
    codigo: m.codigo,
    nombre: m.nombre,
    carrera: m.carrera,
    creditos: m.creditos,
    horarios: m.horarios || [],
    totalEstudiantes: m.total_estudiantes || 0,
  }))
})

async function verEstudiantes(grupo) {
  panelMateria.value = grupo
  panelEstudiantes.value = true
  loadingEstudiantes.value = true
  try {
    const { data } = await api.get(`/profesor/materias/${grupo.materiaId}/estudiantes`)
    estudiantesMateria.value = data.data || data // Soporte para respuesta con wrapper
    grupo.totalEstudiantes = estudiantesMateria.value.length
  } finally {
    loadingEstudiantes.value = false
  }
}

onMounted(async () => {
  try { 
    const { data } = await api.get('/profesor/materias')
    materias.value = data.data || data // Soporte para respuesta con wrapper {success, data}
  }
  finally { loading.value = false }
})
</script>
