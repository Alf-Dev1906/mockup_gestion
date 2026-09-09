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
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div>
          <h2 class="font-bold text-gray-900">{{ materiaSeleccionada.nombre }}</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ estudiantes.length }} estudiantes · Escala 0–20 · Final = 30% + 30% + 40%</p>
        </div>
        <button @click="guardarTodo" :disabled="guardando || !hayCambios"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-xl transition flex items-center gap-2">
          {{ guardando ? '⏳ Guardando...' : '💾 Guardar Todo' }}
        </button>
      </div>

      <div v-if="loadingEstudiantes" class="p-8 text-center text-gray-400">Cargando estudiantes...</div>
      <div v-else-if="!estudiantes.length" class="p-8 text-center text-gray-400">
        <p class="text-4xl mb-2">👨‍🎓</p><p>Sin estudiantes inscritos en esta materia</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estudiante</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Matrícula</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Parcial 1 <span class="font-normal normal-case text-gray-400">(30%)</span></th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Parcial 2 <span class="font-normal normal-case text-gray-400">(30%)</span></th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Final <span class="font-normal normal-case text-gray-400">(40%)</span></th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Nota Final</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="e in estudiantes" :key="e.inscripcion_id"
              class="hover:bg-gray-50"
              :class="modificados.has(e.inscripcion_id) ? 'bg-yellow-50' : ''">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900 text-sm">{{ e.estudiante.nombre }} {{ e.estudiante.apellido }}</p>
              </td>
              <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ e.estudiante.matricula }}</td>
              <!-- Inputs de parciales -->
              <td v-for="campo in ['parcial1', 'parcial2', 'parcial3']" :key="campo" class="px-5 py-3">
                <input
                  v-model.number="e.edicion[campo]"
                  @input="marcarModificado(e)"
                  type="number" min="0" max="20" step="0.01"
                  class="w-20 text-center border rounded-lg px-2 py-1.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  :class="e.edicion[campo] !== null && e.edicion[campo] >= 0 ? 'border-gray-200 text-gray-900' : 'border-dashed border-gray-300 text-gray-400'"
                  placeholder="—"
                />
              </td>
              <!-- Nota calculada -->
              <td class="px-5 py-3 text-center">
                <span class="text-base font-bold"
                  :class="notaFinal(e) === null ? 'text-gray-300'
                    : notaFinal(e) >= 10 ? 'text-green-600'
                    : 'text-red-600'">
                  {{ notaFinal(e) !== null ? notaFinal(e).toFixed(2) : '—' }}
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
    // Agregar campo edicion con copia de calificaciones actuales
    estudiantes.value = data.map(e => ({
      ...e,
      edicion: {
        parcial1: e.calificacion?.parcial1 ?? null,
        parcial2: e.calificacion?.parcial2 ?? null,
        parcial3: e.calificacion?.parcial3 ?? null,
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
    // Obtener materias únicas
    const map = new Map()
    for (const h of data) {
      if (!map.has(h.materia.id)) map.set(h.materia.id, h.materia)
    }
    opcionesMaterias.value = [...map.values()]
    // Si llega con query param de materia
    const qMateria = route.query.materia
    if (qMateria) {
      const m = opcionesMaterias.value.find(x => x.id === Number(qMateria))
      if (m) { materiaSeleccionada.value = m; await cargarEstudiantes() }
    }
  } finally { loadingMaterias.value = false }
})
</script>
