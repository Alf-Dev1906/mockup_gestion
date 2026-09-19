<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Mi Panel Docente</h1>
        <p class="text-gray-500 mt-1">Bienvenido, {{ auth.userName }}</p>
      </div>
      <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-4 py-2 rounded-full">👨‍🏫 PROFESOR</span>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div v-for="n in 3" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse h-28"></div>
    </div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6">
          <p class="text-4xl font-bold text-emerald-700">{{ stats.materias_asignadas ?? 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">Materias asignadas</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
          <p class="text-4xl font-bold text-blue-700">{{ stats.estudiantes_total ?? 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">Estudiantes en mis materias</p>
        </div>
        <div class="rounded-2xl p-6 border"
          :class="(stats.calificaciones_pendientes ?? 0) > 0 ? 'bg-yellow-50 border-yellow-300' : 'bg-gray-50 border-gray-200'">
          <p class="text-4xl font-bold" :class="(stats.calificaciones_pendientes ?? 0) > 0 ? 'text-yellow-700' : 'text-gray-700'">
            {{ stats.calificaciones_pendientes ?? 0 }}
          </p>
          <p class="text-sm text-gray-600 mt-1">Calificaciones pendientes</p>
          <p v-if="(stats.calificaciones_pendientes ?? 0) > 0" class="text-xs text-yellow-600 font-semibold mt-1">
            ⚠️ Hay calificaciones por registrar
          </p>
        </div>
      </div>

      <!-- Accesos rápidos -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Accesos Rápidos</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <router-link to="/profesor/materias"
          class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md hover:border-emerald-200 transition">
          <span class="text-3xl">📖</span>
          <p class="font-semibold text-gray-900 mt-3">Mis Materias</p>
          <p class="text-xs text-gray-500 mt-1">Ver materias y estudiantes</p>
        </router-link>
        <router-link to="/profesor/calificaciones"
          class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md hover:border-emerald-200 transition">
          <span class="text-3xl">🎓</span>
          <p class="font-semibold text-gray-900 mt-3">Calificaciones</p>
          <p class="text-xs text-gray-500 mt-1">Cargar y editar notas</p>
        </router-link>
        <router-link to="/profesor/horario"
          class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md hover:border-emerald-200 transition">
          <span class="text-3xl">🕐</span>
          <p class="font-semibold text-gray-900 mt-3">Mi Horario</p>
          <p class="text-xs text-gray-500 mt-1">Ver horario semanal</p>
        </router-link>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const auth = useAuthStore()
const loading = ref(true)
const stats = ref({})

onMounted(async () => {
  try { 
    const { data } = await api.get('/profesor/dashboard')
    stats.value = data.data || data
  } finally { 
    loading.value = false 
  }
})
</script>
