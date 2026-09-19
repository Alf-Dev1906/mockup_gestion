<template>
  <div class="w-full">
    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.name"
          @click="tabActual = tab.name"
          :class="[
            tab.current
              ? 'border-emerald-500 text-gray-900 dark:text-gray-100'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
            'w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- Contenido de tabs -->
    <div v-if="tabActual === 'Examenes'">
      <div v-if="examenes.length > 0" class="space-y-4">
        <div v-for="ex in examenes" :key="ex.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ ex.quiz_titulo }}</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fecha: {{ ex.fecha }}</p>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                {{ ex.nota_obtenida }} / {{ ex.puntos_totales }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ ex.porcentaje }}%
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-3">
            <StatusBadge :tipo="ex.estado" pequeno />
            <StatusBadge v-if="ex.advertencias_count > 0" tipo="advertencia" pequeno />
            <span v-if="ex.tiempo_usado_minutos" class="text-xs text-gray-500 dark:text-gray-400">
              {{ ex.tiempo_usado_minutos }} min
            </span>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No hay exámenes registrados</p>
    </div>

    <div v-if="tabActual === 'Tareas'">
      <div v-if="tareas.length > 0" class="space-y-4">
        <div v-for="t in tareas" :key="t.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ t.assignment_titulo }}</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Entregado: {{ t.fecha_entrega }}</p>
            </div>
            <div class="text-right">
              <div v-if="t.calificacion !== null" class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                {{ t.calificacion }} / {{ t.puntos_totales }}
              </div>
              <div v-else class="text-sm text-gray-500 dark:text-gray-400">Pendiente</div>
              <div v-if="t.porcentaje !== null" class="text-xs text-gray-500 dark:text-gray-400">
                {{ t.porcentaje }}%
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-3">
            <StatusBadge :tipo="t.estado" pequeno />
            <StatusBadge v-if="t.es_tardia" tipo="tardio" pequeno />
            <span v-if="t.dias_retraso > 0" class="text-xs text-amber-600 dark:text-amber-400 font-medium">
              +{{ t.dias_retraso }} día{{ t.dias_retraso > 1 ? 's' : '' }}
            </span>
          </div>
          <p v-if="t.comentario_profesor" class="mt-2 text-xs text-gray-600 dark:text-gray-300 italic">
            "{{ t.comentario_profesor }}"
          </p>
        </div>
      </div>
      <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No hay tareas registradas</p>
    </div>

    <div v-if="tabActual === 'Asistencia'">
      <div v-if="asistencias.length > 0" class="space-y-2">
        <div v-for="a in asistencias" :key="a.id" class="flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-gray-800/50">
          <div class="flex items-center">
            <div class="w-24 text-sm text-gray-700 dark:text-gray-300">
              {{ a.fecha }}
            </div>
            <div class="flex-1 pl-4">
              <StatusBadge :tipo="a.estatus" pequeno />
              <span v-if="a.observacion" class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                ({{ a.observacion }})
              </span>
            </div>
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ a.registrado_at }}
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No hay asistencias registradas</p>
    </div>

    <!-- Incidencias anti-copia -->
    <div v-if="tabActual === 'Incidencias'">
      <div v-if="incidencias.length > 0" class="space-y-4">
        <div v-for="inc in incidencias" :key="inc.id" class="relative pl-8 border-l-2 border-gray-200 dark:border-gray-700">
          <div class="absolute -left-[9px] top-0">
            <div class="h-4 w-4 rounded-full bg-gray-300 dark:bg-gray-600" :class="{
              'bg-red-500': inc.gravedad === 'alta',
              'bg-amber-500': inc.gravedad === 'media',
              'bg-green-500': inc.gravedad === 'baja'
            }"></div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
            <div class="flex justify-between items-start">
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                  {{ inc.quiz_titulo }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-300">
                  {{ inc.descripcion }}
                </div>
              </div>
              <div class="text-right">
                <StatusBadge :tipo="inc.gravedad" pequeno />
              </div>
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
              <span class="uppercase font-semibold">{{ inc.tipo }}</span>
              <span>{{ inc.detected_at }}</span>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="text-green-600 dark:text-green-400 text-center py-4 font-medium">
        ✓ Sin incidencias anti-copia registradas
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import StatusBadge from '@/components/StatusBadge.vue'

const props = defineProps({
  estudiante: {
    type: Object,
    required: true
  },
  horarioId: {
    type: [String, Number],
    required: true
  }
})

const emits = defineEmits(['cerrar'])

const tabActual = ref('Examenes')

const tabs = [
  { name: 'Examenes', current: true },
  { name: 'Tareas', current: false },
  { name: 'Asistencia', current: false },
  { name: 'Incidencias', current: false }
]

// Datos del expediente
const examenes = ref([])
const tareas = ref([])
const asistencias = ref([])
const incidencias = ref([])

watch(tabActual, (val) => {
  tabs.forEach(t => t.current = t.name === val)
})

onMounted(async () => {
  await cargarExpediente()
})

async function cargarExpediente() {
  try {
    const response = await fetch(`/api/profesor/auditoria/${props.horarioId}/estudiante/${props.estudiante.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    examenes.value = data.data.examenes || []
    tareas.value = data.data.tareas || []
    asistencias.value = data.data.asistencias || []
    incidencias.value = data.data.incidencias || []
  } catch (error) {
    console.error('Error cargando expediente:', error)
  }
}
</script>
