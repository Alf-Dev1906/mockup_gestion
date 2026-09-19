<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Aula Virtual</h1>
      <p class="text-gray-500 mt-1">Tu espacio académico digital</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-48"></div>
    </div>

    <!-- Stats Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      
      <!-- Card: Exámenes -->
      <router-link
        to="/estudiante/quizzes"
        class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">📝</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.examenesDisponibles }}</div>
            <div class="text-sm text-indigo-100 uppercase tracking-wider">Disponibles</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Mis Exámenes</h3>
        <p class="text-sm text-indigo-100">{{ stats.examenesProximos }} próximos esta semana</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Ver exámenes</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Tareas -->
      <router-link
        to="/estudiante/tareas"
        class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">📋</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.tareasPendientes }}</div>
            <div class="text-sm text-emerald-100 uppercase tracking-wider">Pendientes</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Mis Tareas</h3>
        <p class="text-sm text-emerald-100">{{ stats.tareasEntregadas }} entregadas este periodo</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Ver tareas</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Asistencia -->
      <router-link
        to="/estudiante/asistencia"
        class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">✅</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.porcentajeAsistencia }}%</div>
            <div class="text-sm text-amber-100 uppercase tracking-wider">Asistencia</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Asistencia</h3>
        <p class="text-sm text-amber-100">{{ stats.diasPresente }} días presente / {{ stats.totalDias }} total</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Marcar asistencia</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>

      <!-- Card: Notificaciones -->
      <router-link
        to="/notificaciones"
        class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-2xl transition-all hover:scale-105 group cursor-pointer"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
            <span class="text-3xl">🔔</span>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold">{{ stats.notificacionesNoLeidas }}</div>
            <div class="text-sm text-purple-100 uppercase tracking-wider">Nuevas</div>
          </div>
        </div>
        <h3 class="font-semibold text-lg mb-1">Notificaciones</h3>
        <p class="text-sm text-purple-100">{{ stats.totalNotificaciones }} notificaciones totales</p>
        <div class="mt-4 flex items-center text-sm opacity-75 group-hover:opacity-100 transition">
          <span>Ver todas</span>
          <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
        </div>
      </router-link>
    </div>

    <!-- Actividad reciente -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      
      <!-- Exámenes próximos -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900">Exámenes Próximos</h2>
          <router-link to="/estudiante/quizzes" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            Ver todos →
          </router-link>
        </div>
        <div v-if="examenesProximos.length > 0" class="space-y-3">
          <div
            v-for="ex in examenesProximos"
            :key="ex.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition cursor-pointer"
            @click="$router.push(`/estudiante/quizzes/${ex.id}/tomar`)"
          >
            <div class="flex-1">
              <p class="font-medium text-gray-900 text-sm">{{ ex.titulo }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ ex.materia }} · {{ ex.fecha }}</p>
            </div>
            <div class="text-right">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="ex.urgente ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
              >
                {{ ex.urgente ? 'Urgente' : 'Próximo' }}
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm text-center py-4">No hay exámenes próximos</p>
      </div>

      <!-- Tareas por entregar -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-900">Tareas por Entregar</h2>
          <router-link to="/estudiante/tareas" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Ver todas →
          </router-link>
        </div>
        <div v-if="tareasPendientes.length > 0" class="space-y-3">
          <div
            v-for="t in tareasPendientes"
            :key="t.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
          >
            <div class="flex-1">
              <p class="font-medium text-gray-900 text-sm">{{ t.titulo }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ t.materia }} · Vence: {{ t.fechaLimite }}</p>
            </div>
            <div class="text-right">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="t.urgente ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'"
              >
                {{ t.diasRestantes }} día{{ t.diasRestantes !== 1 ? 's' : '' }}
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm text-center py-4">No hay tareas pendientes</p>
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
  examenesDisponibles: 0,
  examenesProximos: 0,
  tareasPendientes: 0,
  tareasEntregadas: 0,
  porcentajeAsistencia: 0,
  diasPresente: 0,
  totalDias: 0,
  notificacionesNoLeidas: 0,
  totalNotificaciones: 0
})

const examenesProximos = ref([])
const tareasPendientes = ref([])

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
      examenesDisponibles: 2,
      examenesProximos: 3,
      tareasPendientes: 4,
      tareasEntregadas: 12,
      porcentajeAsistencia: 87,
      diasPresente: 26,
      totalDias: 30,
      notificacionesNoLeidas: 5,
      totalNotificaciones: 23
    }

    examenesProximos.value = [
      { id: 1, titulo: 'Examen Parcial 1', materia: 'Programación I', fecha: 'Hoy 14:00', urgente: true },
      { id: 2, titulo: 'Quiz Unidad 3', materia: 'Estructuras de Datos', fecha: 'Mañana 10:00', urgente: false },
      { id: 3, titulo: 'Evaluación Final', materia: 'Algoritmos', fecha: 'Viernes 15:00', urgente: false }
    ]

    tareasPendientes.value = [
      { id: 1, titulo: 'Proyecto Final', materia: 'Programación I', fechaLimite: '2026-09-10', diasRestantes: 3, urgente: true },
      { id: 2, titulo: 'Tarea 5 - Recursión', materia: 'Estructuras de Datos', fechaLimite: '2026-09-15', diasRestantes: 8, urgente: false },
      { id: 3, titulo: 'Laboratorio 3', materia: 'Algoritmos', fechaLimite: '2026-09-12', diasRestantes: 5, urgente: false }
    ]

  } catch (error) {
    console.error('Error cargando datos:', error)
  } finally {
    loading.value = false
  }
}
</script>
