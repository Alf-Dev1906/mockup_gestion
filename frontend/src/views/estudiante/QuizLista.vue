<template>
  <AdminLayout>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Mis Exámenes</h1>
        <p class="text-gray-500 mt-1">Exámenes y quizzes disponibles en tus materias</p>
      </div>
      <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-4 py-2 rounded-full">👨‍🎓 ESTUDIANTE</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="n in 6" :key="n" class="h-44 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <template v-else>
      <!-- Sin exámenes -->
      <div v-if="quizzes.length === 0"
        class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center text-gray-400">
        <p class="text-5xl mb-4">📝</p>
        <p class="text-xl font-semibold text-gray-600">Sin exámenes disponibles</p>
        <p class="text-sm mt-2">Tus profesores aún no han publicado exámenes</p>
      </div>

      <!-- Grid de quizzes -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="quiz in quizzes" :key="quiz.id"
          class="bg-white rounded-2xl border shadow-sm hover:shadow-md transition p-5"
          :class="borderPorEstadoAlumno(quiz)">

          <!-- Cabecera -->
          <div class="flex items-start justify-between mb-3">
            <span class="text-2xl">{{ iconoPorTipo(quiz.tipo) }}</span>
            <div class="flex flex-col items-end gap-1">
              <span class="text-xs font-bold px-2.5 py-1 rounded-full" :class="badgePorEstadoAlumno(quiz)">
                {{ labelEstadoAlumno(quiz) }}
              </span>
              <span v-if="quiz.intento_actual" class="text-xs text-blue-600 font-semibold animate-pulse">
                ● En progreso
              </span>
            </div>
          </div>

          <h3 class="font-bold text-gray-900 mb-1 line-clamp-2">{{ quiz.titulo }}</h3>
          <p class="text-xs text-gray-500 mb-3">{{ quiz.horario?.materia?.nombre }} — {{ quiz.horario?.profesor?.nombre }}</p>

          <!-- Meta -->
          <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
            <span>⏱ {{ quiz.duracion_minutos }} min</span>
            <span>🎯 {{ quiz.intentos_permitidos }} intento(s)</span>
          </div>

          <!-- Fechas -->
          <div v-if="quiz.fecha_fin" class="text-xs mb-3" :class="estaProximo(quiz.fecha_fin) ? 'text-orange-600 font-semibold' : 'text-gray-400'">
            {{ estaProximo(quiz.fecha_fin) ? '⚠️ Cierra' : '⏹ Cierra' }}: {{ formatFecha(quiz.fecha_fin) }}
          </div>

          <!-- Acciones -->
          <div class="pt-1">
            <!-- Tiene intento en progreso -->
            <router-link v-if="quiz.intento_actual"
              :to="`/estudiante/quizzes/${quiz.id}/tomar?attempt_id=${quiz.intento_actual.id}`"
              class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition text-sm shadow">
              ▶ Continuar Examen
            </router-link>

            <!-- Disponible para iniciar -->
            <router-link v-else-if="quiz.tiene_intentos_disponibles"
              :to="`/estudiante/quizzes/${quiz.id}/tomar`"
              class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition text-sm shadow">
              Iniciar Examen →
            </router-link>

            <!-- Ya tomado, esperando calificación -->
            <div v-else-if="!quiz.tiene_intentos_disponibles && !intentoCalificado(quiz)"
              class="w-full text-center bg-yellow-50 border border-yellow-200 text-yellow-700 font-semibold py-2.5 rounded-xl text-sm">
              ⏳ En revisión por el profesor
            </div>

            <!-- Ver resultado calificado -->
            <router-link v-else-if="intentoCalificado(quiz)"
              :to="`/estudiante/quizzes/${quiz.id}/resultado`"
              class="block w-full text-center bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 font-semibold py-2.5 rounded-xl transition text-sm">
              📊 Ver Resultado
            </router-link>
          </div>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const quizzes = ref([])

const iconoPorTipo = (tipo) => ({ examen: '📝', quiz: '❓', practica: '🧪' }[tipo] || '📄')

const estaProximo = (fecha) => {
  if (!fecha) return false
  const diff = new Date(fecha) - new Date()
  return diff > 0 && diff < 86400000 // menos de 24h
}

const formatFecha = (fecha) => fecha
  ? new Date(fecha).toLocaleString('es-VE', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
  : '—'

const intentoCalificado = (quiz) => {
  return !quiz.tiene_intentos_disponibles && !quiz.intento_actual
}

const labelEstadoAlumno = (quiz) => {
  if (quiz.intento_actual) return 'En progreso'
  if (!quiz.tiene_intentos_disponibles) return 'Completado'
  return 'Disponible'
}

const badgePorEstadoAlumno = (quiz) => {
  if (quiz.intento_actual) return 'bg-blue-100 text-blue-700'
  if (!quiz.tiene_intentos_disponibles) return 'bg-emerald-100 text-emerald-700'
  return 'bg-indigo-100 text-indigo-700'
}

const borderPorEstadoAlumno = (quiz) => {
  if (quiz.intento_actual) return 'border-blue-200'
  if (!quiz.tiene_intentos_disponibles) return 'border-emerald-200'
  return 'border-gray-100'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/estudiante/quizzes')
    quizzes.value = data.data || []
  } catch (e) {
    toast.error('Error al cargar exámenes')
  } finally {
    loading.value = false
  }
})
</script>
