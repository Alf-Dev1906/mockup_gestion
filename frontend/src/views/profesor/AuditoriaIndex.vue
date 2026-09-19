<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📊 Auditoría Docente</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Seguimiento académico por estudiante en tus horarios
      </p>
    </div>

    <!-- Selector de horario -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Seleccionar Horario
      </label>
      <select
        v-model="horarioSeleccionado"
        @change="cargarEstudiantes"
        class="block w-full pl-3 pr-10 py-2 text-gray-700 dark:text-white bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
      >
        <option value="" disabled>-- Selecciona un horario --</option>
        <option v-for="h in horarios" :key="h.id" :value="h.id">
          {{ h.materia }} - Sección {{ h.seccion }} ({{ h.codigo_materia }})
        </option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="cargandoEstudiantes" class="text-center py-12">
      <div class="inline-block animate-spin text-2xl">⏳</div>
      <p class="mt-2 text-gray-500 dark:text-gray-400">Cargando estudiantes...</p>
    </div>

    <!-- Tabla de estudiantes -->
    <div v-else-if="estudiantes.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estudiante</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Matrícula</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Asistencias</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">% Asistencia</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="est in estudiantes" :key="est.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-800 dark:text-emerald-300 font-semibold text-sm">
                      {{ getIniciales(est.nombre_completo) }}
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ est.nombre_completo }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ est.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="text-sm text-gray-900 dark:text-white font-mono">{{ est.matricula }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="text-sm text-gray-900 dark:text-white">
                  <span class="font-bold text-green-600 dark:text-green-400">{{ est.presentes }}</span> /
                  <span class="font-bold text-red-600 dark:text-red-400">{{ est.ausentes }}</span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">de {{ est.total_clases }} clases</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center">
                  <div 
                    :class="getPorcentajeColor(est.porcentaje_asistencia)"
                    class="px-3 py-1 rounded-full text-sm font-bold"
                  >
                    {{ est.porcentaje_asistencia }}%
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span 
                  :class="getEstadoColor(est.porcentaje_asistencia)"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ getEstadoTexto(est.porcentaje_asistencia) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="horarioSeleccionado" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="text-4xl mb-2">📭</div>
      <p class="text-gray-500 dark:text-gray-400">No hay estudiantes inscritos en este horario</p>
    </div>

    <!-- Initial state -->
    <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="text-4xl mb-2">👆</div>
      <p class="text-gray-500 dark:text-gray-400">Selecciona un horario para ver los estudiantes</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import api from '@/services/api'

const toast = useToast()

const horarios = ref([])
const horarioSeleccionado = ref('')
const estudiantes = ref([])
const cargandoHorarios = ref(false)
const cargandoEstudiantes = ref(false)

onMounted(async () => {
  await cargarHorarios()
})

async function cargarHorarios() {
  cargandoHorarios.value = true
  try {
    const { data } = await api.get('/profesor/materias')
    
    // El endpoint de materias devuelve las materias con horarios
    const todosLosHorarios = []
    
    if (data.data && Array.isArray(data.data)) {
      data.data.forEach(materia => {
        if (materia.horarios && Array.isArray(materia.horarios)) {
          materia.horarios.forEach(h => {
            todosLosHorarios.push({
              id: h.id,
              materia: materia.nombre,
              codigo_materia: materia.codigo,
              seccion: h.seccion || 'A'
            })
          })
        }
      })
    }
    
    horarios.value = todosLosHorarios
    
    // Seleccionar automáticamente el primero si existe
    if (horarios.value.length > 0) {
      horarioSeleccionado.value = horarios.value[0].id
      await cargarEstudiantes()
    }
  } catch (error) {
    console.error('Error cargando horarios:', error)
    toast.error('Error al cargar los horarios')
  } finally {
    cargandoHorarios.value = false
  }
}

async function cargarEstudiantes() {
  if (!horarioSeleccionado.value) return
  
  cargandoEstudiantes.value = true
  try {
    const { data } = await api.get(`/profesor/auditoria/${horarioSeleccionado.value}/estudiantes`)
    
    // Mapear datos de estudiantes con estadísticas de asistencia
    estudiantes.value = data.data.map(est => {
      const presentes = est.asistencias?.filter(a => a.estatus === 'presente').length || 0
      const ausentes = est.asistencias?.filter(a => a.estatus === 'ausente').length || 0
      const total = presentes + ausentes
      const porcentaje = total > 0 ? Math.round((presentes / total) * 100) : 0
      
      return {
        id: est.id,
        nombre_completo: `${est.nombre} ${est.apellido}`,
        email: est.email,
        matricula: est.matricula,
        presentes,
        ausentes,
        total_clases: total,
        porcentaje_asistencia: porcentaje
      }
    })
  } catch (error) {
    console.error('Error cargando estudiantes:', error)
    toast.error('Error al cargar los estudiantes')
    estudiantes.value = []
  } finally {
    cargandoEstudiantes.value = false
  }
}

function getIniciales(nombreCompleto) {
  return nombreCompleto
    .split(' ')
    .map(palabra => palabra[0])
    .join('')
    .substring(0, 2)
    .toUpperCase()
}

function getPorcentajeColor(porcentaje) {
  if (porcentaje >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
  if (porcentaje >= 60) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
}

function getEstadoColor(porcentaje) {
  if (porcentaje >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
  if (porcentaje >= 60) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
}

function getEstadoTexto(porcentaje) {
  if (porcentaje >= 80) return '✓ Excelente'
  if (porcentaje >= 60) return '⚠ Regular'
  return '✗ Crítico'
}
</script>
