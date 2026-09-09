<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Reportes Académicos</h1>
      <p class="text-gray-500 mt-1">Estadísticas y reportes del sistema académico</p>
    </div>

    <!-- Tabs de reporte -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button v-for="t in tipos" :key="t.key" @click="tipo = t.key; cargar()"
        class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
        :class="tipo === t.key ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'">
        {{ t.icon }} {{ t.label }}
      </button>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 h-64 animate-pulse"></div>
    </div>

    <template v-else>
      <!-- REPORTE GENERAL -->
      <template v-if="tipo === 'general'">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
          <StatCard color="blue"    icon="🎓" :value="data.total_estudiantes?.toLocaleString()" label="Total Estudiantes" />
          <StatCard color="green"   icon="✅" :value="data.estudiantes_activos?.toLocaleString()" label="Estudiantes Activos" />
          <StatCard color="emerald" icon="👨‍🏫" :value="data.total_profesores?.toLocaleString()" label="Profesores" />
          <StatCard color="purple"  icon="📖" :value="data.total_materias?.toLocaleString()" label="Materias" />
          <StatCard color="orange"  icon="📝" :value="data.inscripciones_periodo?.toLocaleString()" label="Inscripciones Período" />
        </div>
      </template>

      <!-- REPORTE INSCRIPCIONES -->
      <template v-else-if="tipo === 'inscripciones'">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-5">Por Estado</h3>
            <div class="space-y-3">
              <div v-for="e in data.por_estatus" :key="e.estatus" class="flex items-center gap-3">
                <span class="text-sm text-gray-700 w-24 capitalize">{{ e.estatus }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                  <div class="h-full rounded-full bg-blue-500 transition-all duration-700"
                    :style="{ width: pct(e.count, data.por_estatus) + '%' }"></div>
                </div>
                <span class="text-sm font-bold text-gray-900 w-16 text-right">{{ e.count?.toLocaleString() }}</span>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-5">Top 10 Materias más inscritas</h3>
            <div class="space-y-2">
              <div v-for="(m, i) in data.por_materia?.slice(0, 10)" :key="m.nombre"
                class="flex items-center gap-2 text-sm">
                <span class="text-gray-400 w-5 text-right text-xs">{{ i + 1 }}</span>
                <span class="flex-1 text-gray-700 truncate">{{ m.nombre }}</span>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ m.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- REPORTE CALIFICACIONES -->
      <template v-else-if="tipo === 'calificaciones'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
            <p class="text-4xl font-bold text-blue-600">{{ Number(data.promedio_general ?? 0).toFixed(2) }}</p>
            <p class="text-sm text-gray-500 mt-1">Promedio General</p>
          </div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
            <p class="text-4xl font-bold text-green-600">{{ data.aprobados?.toLocaleString() }}</p>
            <p class="text-sm text-gray-500 mt-1">Aprobados</p>
          </div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
            <p class="text-4xl font-bold text-red-600">{{ data.reprobados?.toLocaleString() }}</p>
            <p class="text-sm text-gray-500 mt-1">Reprobados</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h3 class="font-bold text-gray-900 mb-4">Tasa de aprobación</h3>
          <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
              <div class="h-full bg-green-500 rounded-full flex items-center justify-end pr-3 transition-all duration-700"
                :style="{ width: aprobPct + '%' }">
                <span class="text-xs font-bold text-white">{{ aprobPct }}%</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- REPORTE ESTUDIANTES -->
      <template v-else-if="tipo === 'estudiantes'">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-5">Estudiantes por Carrera</h3>
            <div class="space-y-3">
              <div v-for="c in data.por_carrera" :key="c.nombre" class="flex items-center gap-2 text-sm">
                <span class="flex-1 text-gray-700 truncate">{{ c.nombre }}</span>
                <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                  <div class="h-full bg-blue-500 rounded-full"
                    :style="{ width: pct(c.count, data.por_carrera) + '%' }"></div>
                </div>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full w-12 text-center">{{ c.count }}</span>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-5">Estudiantes por Semestre</h3>
            <div class="grid grid-cols-2 gap-3">
              <div v-for="s in data.por_semestre" :key="s.semestre_actual"
                class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-blue-700">{{ s.count }}</p>
                <p class="text-xs text-gray-500 mt-1">Semestre {{ s.semestre_actual }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import StatCard from '@/components/StatCard.vue'
import api from '@/services/api'

const loading = ref(true)
const tipo = ref('general')
const data = ref({})

const tipos = [
  { key: 'general',        icon: '📊', label: 'General' },
  { key: 'inscripciones',  icon: '📝', label: 'Inscripciones' },
  { key: 'calificaciones', icon: '🎓', label: 'Calificaciones' },
  { key: 'estudiantes',    icon: '👨‍🎓', label: 'Estudiantes' },
]

const aprobPct = computed(() => {
  const total = (data.value.aprobados ?? 0) + (data.value.reprobados ?? 0)
  return total > 0 ? Math.round((data.value.aprobados / total) * 100) : 0
})

const pct = (count, arr) => {
  const max = Math.max(...(arr?.map(x => x.count) ?? [1]))
  return max > 0 ? Math.round((count / max) * 100) : 0
}

async function cargar() {
  loading.value = true
  try {
    const { data: res } = await api.get('/admin/reportes', { params: { tipo: tipo.value } })
    data.value = res
  } finally { loading.value = false }
}

onMounted(cargar)
</script>
