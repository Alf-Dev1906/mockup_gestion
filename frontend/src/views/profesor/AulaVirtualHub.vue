<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Aula Virtual</h1>
      <p class="text-gray-500 mt-1">Centro de control académico</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-48"></div>
    </div>

    <!-- Stats Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      
      <!-- Card: Exámenes -->
      <router-link
        to="/profesor/quizzes"
        class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">📝</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.examenesActivos }}</div>
            <div class="text-sm text-indigo-100 uppercase tracking-wider">Activos</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Exámenes</h3>
        <p class="text-sm text-indigo-100">{{ stats.examenesPendientes }} pendientes de calificar</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Ver todos</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Tareas -->
      <router-link
        to="/profesor/tareas"
        class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">📋</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.tareasPublicadas }}</div>
            <div class="text-sm text-emerald-100 uppercase tracking-wider">Publicadas</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Tareas</h3>
        <p class="text-sm text-emerald-100">{{ stats.entregasPendientes }} entregas por calificar</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Gestionar</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Asistencia -->
      <router-link
        to="/profesor/asistencia"
        class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">✅</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.sesionesAbiertas }}</div>
            <div class="text-sm text-amber-100 uppercase tracking-wider">Abiertas</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Asistencia</h3>
        <p class="text-sm text-amber-100">{{ stats.totalSesiones }} sesiones registradas</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Gestionar</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Auditoría -->
      <router-link
        to="/profesor/auditoria"
        class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">🔍</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.incidenciasRecientes }}</div>
            <div class="text-sm text-purple-100 uppercase tracking-wider">Incidencias</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Auditoría</h3>
        <p class="text-sm text-purple-100">{{ stats.totalEstudiantes }} estudiantes monitoreados</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Ver reportes</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>
    </div>

    <!-- Actividad reciente -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      
      <!-- Exámenes próximos -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900">Exámenes Recientes</h2>
          <router-link to="/profesor/quizzes" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            Ver todos →
          </router-link>
        </div>
        <div v-if="examenesRecientes.length > 0" class="space-y-3">
          <div
            v-for="ex in examenesRecientes"
            :key="ex.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
          >
            <div class="flex-1">
              <p class="font-medium text-gray-900 text-sm">{{ ex.titulo }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ ex.materia }} · {{ ex.intentos }} intentos</p>
            </div>
            <div class="text-right">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                {{ ex.estado }}
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm text-center py-4">No hay exámenes recientes</p>
      </div>

      <!-- Entregas pendientes -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900">Entregas Pendientes</h2>
          <router-link to="/profesor/tareas" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Ver todas →
          </router-link>
        </div>
        <div v-if="entregasPendientes.length > 0" class="space-y-3">
          <div
            v-for="ent in entregasPendientes"
            :key="ent.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
          >
            <div class="flex-1">
              <p class="font-medium text-gray-900 text-sm">{{ ent.tarea }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ ent.estudiante }}</p>
            </div>
            <div class="text-right">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                Pendiente
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm text-center py-4">No hay entregas pendientes</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)

const stats = ref({
  examenesActivos: 0,
  examenesPendientes: 0,
  tareasPublicadas: 0,
  entregasPendientes: 0,
  sesionesAbiertas: 0,
  totalSesiones: 0,
  incidenciasRecientes: 0,
  totalEstudiantes: 0
})

const examenesRecientes = ref([])
const entregasPendientes = ref([])

onMounted(async () => {
  await cargarDatos()
})

async function cargarDatos() {
  try {
    // TODO: Reemplazar con endpoints reales cuando estén disponibles
    // Por ahora, datos mock
    
    // Simular carga de datos
    await new Promise(resolve => setTimeout(resolve, 800))

    stats.value = {
      examenesActivos: 3,
      examenesPendientes: 5,
      tareasPublicadas: 8,
      entregasPendientes: 12,
      sesionesAbiertas: 1,
      totalSesiones: 24,
      incidenciasRecientes: 7,
      totalEstudiantes: 85
    }

    examenesRecientes.value = [
      { id: 1, titulo: 'Examen Parcial 1', materia: 'Programación I', intentos: 25, estado: 'Activo' },
      { id: 2, titulo: 'Quiz Unidad 3', materia: 'Estructuras de Datos', intentos: 18, estado: 'Cerrado' },
      { id: 3, titulo: 'Evaluación Final', materia: 'Algoritmos', intentos: 30, estado: 'Activo' }
    ]

    entregasPendientes.value = [
      { id: 1, tarea: 'Proyecto Final', estudiante: 'Juan Pérez' },
      { id: 2, tarea: 'Tarea 5 - Recursión', estudiante: 'María García' },
      { id: 3, tarea: 'Laboratorio 3', estudiante: 'Carlos López' }
    ]

  } catch (error) {
    console.error('Error cargando datos:', error)
  } finally {
    loading.value = false
  }
}
</script>
