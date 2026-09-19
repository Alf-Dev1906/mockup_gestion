<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Calificaciones</h1>
      <p class="text-gray-500 mt-1">Registrar y actualizar notas de tus estudiantes</p>
    </div>

    <!-- Selector de materia -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Seleccionar Materia</label>
      <div class="flex flex-wrap gap-2">
        <button v-for="m in opcionesMaterias" :key="m.id"
          @click="materiaSeleccionada = m; cargarEstudiantes()"
          class="px-4 py-2 rounded-xl text-sm font-semibold transition border"
          :class="materiaSeleccionada?.id === m.id
            ? 'bg-emerald-600 text-white border-emerald-600'
            : 'bg-white text-gray-700 border-gray-200 hover:border-emerald-300 hover:bg-emerald-50'">
          <span class="font-mono text-xs mr-1">{{ m.codigo }}</span> {{ m.nombre }}
        </button>
      </div>
    </div>

    <!-- Tabla de estudiantes y calificaciones -->
    <div v-if="materiaSeleccionada" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <!-- Header con stats -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-5 text-white">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-bold text-xl">{{ materiaSeleccionada.nombre }}</h2>
            <p class="text-indigo-200 text-sm mt-1">
              {{ materiaSeleccionada.codigo }} · {{ materiaSeleccionada.creditos }} créditos · {{ materiaSeleccionada.carrera }}
            </p>
          </div>
          <button @click="guardarTodo" :disabled="guardando || !hayCambios"
            class="bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-indigo-600 font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2 shadow-lg">
            {{ guardando ? '⏳ Guardando...' : '💾 Guardar Todo' }}
          </button>
        </div>
        
        <!-- Stats mini -->
        <div class="grid grid-cols-4 gap-4">
          <div class="bg-white/10 rounded-lg px-4 py-3 backdrop-blur-sm">
            <p class="text-2xl font-bold">{{ estudiantes.length }}</p>
            <p class="text-xs text-indigo-200">Estudiantes</p>
          </div>
          <div class="bg-white/10 rounded-lg px-4 py-3 backdrop-blur-sm">
            <p class="text-2xl font-bold">{{ estudiantesConNotas }}</p>
            <p class="text-xs text-indigo-200">Con notas</p>
          </div>
          <div class="bg-white/10 rounded-lg px-4 py-3 backdrop-blur-sm">
            <p class="text-2xl font-bold">{{ estudiantesSinNotas }}</p>
            <p class="text-xs text-indigo-200">Pendientes</p>
          </div>
          <div class="bg-white/10 rounded-lg px-4 py-3 backdrop-blur-sm">
            <p class="text-2xl font-bold">{{ modificados.size }}</p>
            <p class="text-xs text-indigo-200">Sin guardar</p>
          </div>
        </div>
      </div>

      <div v-if="loadingEstudiantes" class="p-12 text-center text-gray-400">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p>Cargando estudiantes...</p>
      </div>
      <div v-else-if="!estudiantes.length" class="p-16 text-center text-gray-400">
        <p class="text-5xl mb-3">👨‍🎓</p>
        <p class="font-semibold text-lg">Sin estudiantes inscritos</p>
        <p class="text-sm mt-1">Esta materia aún no tiene estudiantes registrados</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-5 py-4 text-left text-xs font-semibold text-gray-600 uppercase w-10">#</th>
              <th class="px-5 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estudiante</th>
              <th class="px-5 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Matrícula</th>
              <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Parcial 1<br><span class="text-[10px] font-normal">(30%)</span></th>
              <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Parcial 2<br><span class="text-[10px] font-normal">(30%)</span></th>
              <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Final<br><span class="text-[10px] font-normal">(40%)</span></th>
              <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Nota Final</th>
              <th class="px-5 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(e, idx) in estudiantes" :key="e.inscripcion_id"
              class="hover:bg-indigo-50/30 transition-colors"
              :class="modificados.has(e.inscripcion_id) ? 'bg-yellow-50 border-l-4 border-yellow-400' : ''">
              <td class="px-5 py-4 text-gray-500 font-medium text-sm">{{ idx + 1 }}</td>
              <td class="px-5 py-4">
                <p class="font-medium text-gray-900">{{ e.estudiante.nombre }} {{ e.estudiante.apellido }}</p>
                <p class="text-xs text-gray-500">CI: {{ e.estudiante.cedula }}</p>
              </td>
              <td class="px-5 py-4">
                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                  {{ e.estudiante.matricula }}
                </span>
              </td>
              <!-- Inputs de parciales -->
              <td v-for="campo in ['parcial1', 'parcial2', 'parcial3']" :key="campo" class="px-5 py-4">
                <input
                  v-model.number="e.edicion[campo]"
                  @input="marcarModificado(e)"
                  type="number" min="0" max="20" step="0.01"
                  class="w-24 text-center border rounded-lg px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                  :class="e.edicion[campo] !== null && e.edicion[campo] >= 0 
                    ? 'border-gray-300 text-gray-900 bg-white' 
                    : 'border-dashed border-gray-300 text-gray-400 bg-gray-50'"
                  placeholder="—"
                />
              </td>
              <!-- Nota calculada -->
              <td class="px-5 py-4 text-center">
                <span class="text-lg font-bold"
                  :class="notaFinal(e) === null ? 'text-gray-300'
                    : notaFinal(e) >= 10 ? 'text-green-600'
                    : 'text-red-600'">
                  {{ notaFinal(e) !== null ? notaFinal(e).toFixed(2) : '—' }}
                </span>
              </td>
              <!-- Estado -->
              <td class="px-5 py-4 text-center">
                <span v-if="notaFinal(e) !== null"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="notaFinal(e) >= 10 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ notaFinal(e) >= 10 ? '✓ Aprobado' : '✗ Reprobado' }}
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                  ⏳ Pendiente
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">🎓</p>
      <p>Selecciona una materia para ver y editar calificaciones</p>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const route = useRoute()
const loadingMaterias = ref(true)
const loadingEstudiantes = ref(false)
const guardando = ref(false)
const opcionesMaterias = ref([])
const materiaSeleccionada = ref(null)
const estudiantes = ref([])
const modificados = ref(new Set())

const estudiantesConNotas = computed(() => 
  estudiantes.value.filter(e => notaFinal(e) !== null).length
)

const estudiantesSinNotas = computed(() => 
  estudiantes.value.filter(e => notaFinal(e) === null).length
)

const hayCambios = computed(() => modificados.value.size > 0)

const notaFinal = e => {
  const p1 = e.edicion.parcial1
  const p2 = e.edicion.parcial2
  const p3 = e.edicion.parcial3
  if (p1 === null && p2 === null && p3 === null) return null
  return ((p1 ?? 0) * 0.30) + ((p2 ?? 0) * 0.30) + ((p3 ?? 0) * 0.40)
}

function marcarModificado(e) {
  modificados.value.add(e.inscripcion_id)
}

async function cargarEstudiantes() {
  if (!materiaSeleccionada.value) return
  loadingEstudiantes.value = true
  modificados.value.clear()
  try {
    const { data } = await api.get(`/profesor/materias/${materiaSeleccionada.value.id}/estudiantes`)
    const apiData = data.data || data // Soporte para respuesta con wrapper
    // Agregar campo edicion con copia de calificaciones actuales
    estudiantes.value = (Array.isArray(apiData) ? apiData : []).map(e => ({
      ...e,
      edicion: {
        parcial1: e.calificacion?.nota_final !== null ? e.calificacion?.nota_corte_1 : null,
        parcial2: e.calificacion?.nota_final !== null ? e.calificacion?.nota_corte_2 : null,
        parcial3: e.calificacion?.nota_final !== null ? e.calificacion?.nota_corte_3 : null,
      }
    }))
  } finally { loadingEstudiantes.value = false }
}

async function guardarTodo() {
  guardando.value = true
  let guardados = 0
  let errores = 0

  const promesas = [...modificados.value].map(async inscripcionId => {
    const e = estudiantes.value.find(x => x.inscripcion_id === inscripcionId)
    if (!e) return
    try {
      await api.post('/profesor/calificaciones', {
        inscripcion_id: inscripcionId,
        parcial1: e.edicion.parcial1,
        parcial2: e.edicion.parcial2,
        parcial3: e.edicion.parcial3,
      })
      guardados++
    } catch { errores++ }
  })

  await Promise.all(promesas)

  if (errores > 0) toast.warning(`${guardados} guardadas, ${errores} con error`)
  else toast.success(`${guardados} calificaciones guardadas exitosamente`)

  modificados.value.clear()
  guardando.value = false
  // Recargar para tener datos actualizados del servidor
  await cargarEstudiantes()
}

onMounted(async () => {
  try {
    const { data } = await api.get('/profesor/materias')
    const apiData = data.data || data // Soporte para respuesta con wrapper {success, data}
    
    // El backend ahora devuelve materias agrupadas, no horarios individuales
    opcionesMaterias.value = apiData.map(m => ({
      id: m.id,
      codigo: m.codigo,
      nombre: m.nombre,
      creditos: m.creditos,
      carrera: m.carrera,
    }))
    
    // Si llega con query param de materia
    const qMateria = route.query.materia
    if (qMateria) {
      const m = opcionesMaterias.value.find(x => x.id === Number(qMateria))
      if (m) { materiaSeleccionada.value = m; await cargarEstudiantes() }
    }
  } finally { loadingMaterias.value = false }
})
</script>
