<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Mis Exámenes</h1>
        <p class="text-gray-500 mt-1">Gestiona los exámenes de tus materias</p>
      </div>
      <router-link to="/profesor/quizzes/nuevo"
        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl transition shadow-sm text-sm flex items-center gap-2">
        + Nuevo Examen
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3">
      <select v-model="filtroHorario" class="border border-gray-200 rounded-xl px-3 py-2 text-sm flex-1 min-w-40 text-gray-900 bg-white">
        <option value="">Todas las materias</option>
        <option v-for="h in horarios" :key="h.id" :value="h.id">
          {{ h.materia?.nombre }} — Sección {{ h.seccion }}
        </option>
      </select>
      <select v-model="filtroEstado" class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 bg-white">
        <option value="">Todos los estados</option>
        <option value="borrador">Borrador</option>
        <option value="publicado">Publicado</option>
        <option value="cerrado">Cerrado</option>
      </select>
      <input v-model="busqueda" type="text" placeholder="Buscar examen..."
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm w-48 text-gray-900 placeholder-gray-400" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="n in 6" :key="n" class="h-40 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <!-- Grid de exámenes -->
    <div v-else-if="quizzesFiltrados.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="quiz in quizzesFiltrados" :key="quiz.id"
        class="bg-white rounded-2xl border shadow-sm hover:shadow-md transition p-5"
        :class="borderPorEstado(quiz.estado)">

        <!-- Encabezado tarjeta -->
        <div class="flex items-start justify-between mb-3">
          <span class="text-2xl">{{ iconoPorTipo(quiz.tipo) }}</span>
          <span class="text-xs font-bold px-2.5 py-1 rounded-full" :class="badgePorEstado(quiz.estado)">
            {{ labelEstado(quiz.estado) }}
          </span>
        </div>

        <h3 class="font-bold text-gray-900 mb-1 line-clamp-2">{{ quiz.titulo }}</h3>
        <p class="text-xs text-gray-500 mb-3">
          {{ quiz.horario?.materia?.nombre }} — Sección {{ quiz.horario?.seccion }}
        </p>

        <!-- Meta info -->
        <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
          <span>⏱ {{ quiz.duracion_minutos }}min</span>
          <span>📝 {{ quiz.preguntas?.length ?? 0 }} pregs.</span>
          <span>🎯 {{ totalPuntos(quiz) }}pts</span>
        </div>

        <!-- Fechas -->
        <div v-if="quiz.fecha_inicio || quiz.fecha_fin" class="text-xs text-gray-400 mb-3 space-y-0.5">
          <p v-if="quiz.fecha_inicio">▶ {{ formatFecha(quiz.fecha_inicio) }}</p>
          <p v-if="quiz.fecha_fin">⏹ {{ formatFecha(quiz.fecha_fin) }}</p>
        </div>

        <!-- Acciones -->
        <div class="flex gap-2">
          <router-link :to="`/profesor/quizzes/${quiz.id}/editar`"
            class="flex-1 text-center text-xs font-semibold py-2 rounded-xl border border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50 transition">
            ✏️ Editar
          </router-link>
          <router-link :to="`/profesor/quizzes/${quiz.id}/resultados`"
            class="flex-1 text-center text-xs font-semibold py-2 rounded-xl border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
            📊 Resultados
          </router-link>
        </div>
      </div>
    </div>

    <!-- Vacío -->
    <div v-else class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">📝</p>
      <p class="text-xl font-semibold text-gray-600">Sin exámenes aún</p>
      <p class="text-sm mt-2 mb-6">Crea tu primer examen para tus estudiantes</p>
      <router-link to="/profesor/quizzes/nuevo"
        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow">
        + Crear Primer Examen
      </router-link>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const quizzes = ref([])
const horarios = ref([])
const filtroHorario = ref('')
const filtroEstado = ref('')
const busqueda = ref('')

const quizzesFiltrados = computed(() => {
  let arr = quizzes.value
  if (filtroHorario.value) arr = arr.filter(q => q.horario_id === filtroHorario.value)
  if (filtroEstado.value) arr = arr.filter(q => q.estado === filtroEstado.value)
  if (busqueda.value.trim()) {
    const q = busqueda.value.toLowerCase()
    arr = arr.filter(q2 => q2.titulo.toLowerCase().includes(q))
  }
  return arr
})

const totalPuntos = (quiz) => (quiz.preguntas || []).reduce((s, p) => s + (p.puntos || 0), 0)

const iconoPorTipo = (tipo) => ({ examen: '📝', quiz: '❓', practica: '🧪' }[tipo] || '📄')

const labelEstado = (estado) => ({ borrador: 'Borrador', publicado: 'Publicado', cerrado: 'Cerrado' }[estado] || estado)

const badgePorEstado = (estado) => ({
  borrador: 'bg-gray-100 text-gray-600',
  publicado: 'bg-green-100 text-green-700',
  cerrado: 'bg-slate-100 text-slate-600',
}[estado] || 'bg-gray-100 text-gray-600')

const borderPorEstado = (estado) => ({
  borrador: 'border-gray-200',
  publicado: 'border-green-200',
  cerrado: 'border-slate-200',
}[estado] || 'border-gray-100')

const formatFecha = (fecha) => fecha
  ? new Date(fecha).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
  : '—'

onMounted(async () => {
  try {
    const [quizzesRes, horarioRes] = await Promise.all([
      api.get('/profesor/quizzes'),
      api.get('/profesor/horario'),
    ])
    quizzes.value = quizzesRes.data.data || []
    horarios.value = horarioRes.data.data || []
  } catch (e) {
    toast.error('Error al cargar exámenes')
  } finally {
    loading.value = false
  }
})
</script>
