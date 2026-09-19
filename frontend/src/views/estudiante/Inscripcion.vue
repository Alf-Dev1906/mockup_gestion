<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mis Inscripciones</h1>
      <p class="text-gray-500 mt-1">Materias en las que estás inscrito este periodo</p>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse h-32"></div>
    </div>

    <div v-else-if="!inscripciones.length" class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">📝</p>
      <p class="font-semibold">No tienes inscripciones activas</p>
      <p class="text-sm mt-1">Contacta a administración para inscribir materias</p>
    </div>

    <div v-else class="space-y-4">
      <!-- Resumen de créditos -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-2">
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-2xl font-bold text-indigo-600">{{ inscripciones.length }}</p>
          <p class="text-xs text-gray-500 mt-1">Materias</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-2xl font-bold text-green-600">{{ totalCreditos }}</p>
          <p class="text-xs text-gray-500 mt-1">Créditos</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-2xl font-bold text-blue-600">{{ totalHoras }}</p>
          <p class="text-xs text-gray-500 mt-1">Horas/Semana</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-2xl font-bold text-purple-600">{{ periodoActual }}</p>
          <p class="text-xs text-gray-500 mt-1">Periodo</p>
        </div>
      </div>

      <!-- Lista de inscripciones -->
      <div v-for="ins in inscripciones" :key="ins.id"
        class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-lg">
            <span class="text-white text-xl font-bold">{{ ins.materia?.codigo?.substring(0, 3) || '📖' }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4 mb-2">
              <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded">
                    {{ ins.materia?.codigo }}
                  </span>
                  <h3 class="font-semibold text-gray-900 text-lg">{{ ins.materia?.nombre }}</h3>
                </div>
                <p class="text-sm text-gray-500">
                  👨‍🏫 {{ ins.profesor || 'Profesor no asignado' }}
                </p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0 bg-green-100 text-green-700">
                ✓ {{ ins.estatus || 'inscrito' }}
              </span>
            </div>
            
            <div class="flex flex-wrap gap-4 mt-3 text-sm">
              <div class="flex items-center gap-1.5 text-gray-600">
                <span class="text-indigo-600">📚</span>
                <span class="font-semibold">{{ ins.materia?.creditos || 0 }}</span> créditos
              </div>
              <div v-if="ins.horario?.seccion" class="flex items-center gap-1.5 text-gray-600">
                <span class="text-purple-600">👥</span>
                Sección <span class="font-semibold">{{ ins.horario.seccion }}</span>
              </div>
              <div v-if="ins.horario?.dia_semana" class="flex items-center gap-1.5 text-gray-600">
                <span class="text-blue-600">📅</span>
                <span class="capitalize font-semibold">{{ ins.horario.dia_semana }}</span>
                {{ ins.horario.hora_inicio }} - {{ ins.horario.hora_fin }}
              </div>
              <div v-if="ins.fecha_inscripcion" class="flex items-center gap-1.5 text-gray-500">
                <span>🗓️</span>
                Inscrito: {{ formatDate(ins.fecha_inscripcion) }}
              </div>
            </div>
          </div>
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
const inscripciones = ref([])

const totalCreditos = computed(() => 
  inscripciones.value.reduce((sum, i) => sum + (i.materia?.creditos || 0), 0)
)

const totalHoras = computed(() => totalCreditos.value * 3) // Estimado: 3 horas por crédito

const periodoActual = computed(() => {
  const periodo = inscripciones.value[0]?.periodo_academico
  return periodo || '2026-1'
})

const formatDate = (d) => {
  if (!d) return 'N/A'
  return new Date(d).toLocaleDateString('es-VE', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  try { 
    const { data } = await api.get('/estudiante/inscripciones')
    inscripciones.value = data.data || data // Soporte para respuesta con wrapper
  }
  catch { inscripciones.value = [] }
  finally { loading.value = false }
})
</script>
