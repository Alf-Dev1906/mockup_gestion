<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Asistencia</h1>
      <p class="text-gray-500 mt-1">Selecciona el horario para gestionar la asistencia</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-40"></div>
    </div>

    <div v-else-if="!horarios.length" class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">✅</p>
      <p class="font-semibold">No tienes horarios asignados</p>
      <p class="text-sm mt-1">Contacta a administración si crees que hay un error</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="h in horarios"
        :key="h.id"
        @click="abrirAsistencia(h)"
        class="bg-white rounded-2xl border border-gray-200 hover:border-emerald-500 p-6 cursor-pointer transition-all hover:shadow-lg group"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="font-bold text-gray-900 group-hover:text-emerald-600 transition">{{ h.materia }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ h.codigo }} · Sección {{ h.seccion }}</p>
          </div>
          <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-3 py-1 rounded-full">
            {{ h.inscritos }} alumnos
          </span>
        </div>

        <div class="space-y-2">
          <div class="flex items-center text-sm text-gray-600">
            <span class="w-5">📅</span>
            <span class="capitalize">{{ h.dia_semana }}</span>
          </div>
          <div class="flex items-center text-sm text-gray-600">
            <span class="w-5">🕐</span>
            <span>{{ formatTime(h.hora_inicio) }} - {{ formatTime(h.hora_fin) }}</span>
          </div>
          <div v-if="h.aula" class="flex items-center text-sm text-gray-600">
            <span class="w-5">🏫</span>
            <span>{{ h.aula }}</span>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
          <button class="w-full bg-emerald-50 group-hover:bg-emerald-600 text-emerald-700 group-hover:text-white font-semibold py-2 px-4 rounded-xl transition text-sm">
            Gestionar asistencia →
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()

const loading = ref(true)
const horarios = ref([])

onMounted(async () => {
  await cargarHorarios()
})

async function cargarHorarios() {
  try {
    const response = await api.get('/profesor/materias')
    const data = response.data

    // Aplanar horarios
    horarios.value = []
    data.data.forEach(materia => {
      materia.horarios.forEach(h => {
        horarios.value.push({
          id: h.id,
          materia: materia.nombre,
          codigo: materia.codigo,
          seccion: h.seccion || 'A',
          dia_semana: h.dia_semana,
          hora_inicio: h.hora_inicio,
          hora_fin: h.hora_fin,
          aula: h.aula?.nombre || h.aula?.codigo || null,
          inscritos: h.inscritos_count || 0
        })
      })
    })
  } catch (error) {
    console.error('Error cargando horarios:', error)
    toast.error('Error al cargar los horarios')
  } finally {
    loading.value = false
  }
}

function abrirAsistencia(horario) {
  router.push(`/profesor/asistencia/${horario.id}`)
}

function formatTime(time) {
  if (!time) return ''
  const [h, m] = time.split(':')
  return `${h}:${m}`
}
</script>
