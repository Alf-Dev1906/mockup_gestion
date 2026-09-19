<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
      <p class="text-gray-500 mt-1">Información personal y profesional</p>
    </div>

    <div v-if="loading" class="space-y-4">
      <div class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-48"></div>
      <div class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-64"></div>
    </div>

    <template v-else-if="perfil">
      <!-- Tarjeta de presentación -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-2xl p-8 text-white mb-6">
        <div class="flex items-start gap-6">
          <div class="w-24 h-24 rounded-2xl bg-white/20 flex items-center justify-center text-3xl font-bold flex-shrink-0">
            {{ initials }}
          </div>
          <div class="flex-1">
            <h2 class="text-2xl font-bold mb-1">{{ perfil.nombre_completo }}</h2>
            <p class="text-indigo-200 mb-3">{{ perfil.codigo_empleado }}</p>
            <div class="flex flex-wrap gap-3">
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                👨‍🏫 Profesor
              </span>
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                🏛️ {{ perfil.facultad?.nombre || 'Sin facultad' }}
              </span>
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                📚 {{ perfil.categoria || 'Sin categoría' }}
              </span>
              <span class="bg-green-400/30 text-green-100 text-xs font-semibold px-3 py-1.5 rounded-full">
                ✅ {{ perfil.estatus }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats rápidas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
              <span class="text-2xl">📚</span>
            </div>
            <div>
              <p class="text-2xl font-bold text-indigo-600">{{ perfil.estadisticas?.materias_asignadas || 0 }}</p>
              <p class="text-sm text-gray-500">Materias asignadas</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
              <span class="text-2xl">👥</span>
            </div>
            <div>
              <p class="text-2xl font-bold text-emerald-600">{{ perfil.estadisticas?.estudiantes_total || 0 }}</p>
              <p class="text-sm text-gray-500">Estudiantes totales</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Información personal -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">📋 Información Personal</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cédula</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.cedula || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo electrónico</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.email }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Teléfono</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.telefono || 'No registrado' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha de nacimiento</label>
              <p class="text-gray-900 font-medium mt-1">{{ formatearFecha(perfil.fecha_nacimiento) }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Género</label>
              <p class="text-gray-900 font-medium mt-1">{{ generoTexto(perfil.genero) }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dirección</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.direccion || 'No registrada' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Información laboral -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">💼 Información Laboral</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Código de empleado</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.codigo_empleado }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha de contratación</label>
              <p class="text-gray-900 font-medium mt-1">{{ formatearFecha(perfil.fecha_contratacion) }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo de contrato</label>
              <p class="text-gray-900 font-medium mt-1">{{ tipoContratoTexto(perfil.tipo_contrato) }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Categoría</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.categoria || 'No especificada' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Facultad</label>
              <p class="text-gray-900 font-medium mt-1">{{ perfil.facultad?.nombre || 'No asignada' }}</p>
              <p class="text-xs text-gray-500 mt-0.5" v-if="perfil.facultad?.codigo">
                Código: {{ perfil.facultad.codigo }}
              </p>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estatus</label>
              <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-xs font-semibold"
                :class="perfil.estatus === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                {{ perfil.estatus }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
      <p class="text-4xl mb-4">⚠️</p>
      <p class="text-gray-500">No se pudo cargar el perfil</p>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const perfil = ref(null)

const initials = computed(() => {
  if (!perfil.value) return '?'
  const nombres = perfil.value.nombre_completo?.split(' ') || []
  return nombres.map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

function formatearFecha(fecha) {
  if (!fecha) return 'No registrada'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-VE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function generoTexto(genero) {
  const generos = {
    'M': 'Masculino',
    'F': 'Femenino',
    'masculino': 'Masculino',
    'femenino': 'Femenino',
    'otro': 'Otro'
  }
  return generos[genero] || genero || 'No especificado'
}

function tipoContratoTexto(tipo) {
  const tipos = {
    'tiempo_completo': 'Tiempo Completo',
    'medio_tiempo': 'Medio Tiempo',
    'contratado': 'Contratado',
    'por_horas': 'Por Horas'
  }
  return tipos[tipo] || tipo || 'No especificado'
}

onMounted(async () => {
  try {
    const { data: res } = await api.get('/profesor/perfil')
    perfil.value = res.data || res
  } catch (error) {
    console.error('Error al cargar perfil:', error)
  } finally {
    loading.value = false
  }
})
</script>
