<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Mi Panel Estudiantil</h1>
        <p class="text-gray-500 mt-1">Bienvenido, {{ auth.userName }}</p>
      </div>
      <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-4 py-2 rounded-full">👨‍🎓 ESTUDIANTE</span>
    </div>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl p-6 border border-gray-100 animate-pulse h-28"></div>
    </div>

    <template v-else>
      <!-- Alerta de solicitud de admisión pendiente -->
      <div v-if="data.solicitud && data.solicitud.estado === 'borrador'" 
        class="bg-yellow-50 border-2 border-yellow-400 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-yellow-900 mb-2">
              ⚠️ Completa tu solicitud de admisión
            </h3>
            <p class="text-sm text-yellow-800 mb-4">
              Tienes una solicitud de admisión en progreso. Completa todos los pasos para que 
              podamos procesar tu inscripción a la universidad.
            </p>
            <div class="mb-4">
              <div class="flex items-center justify-between text-sm text-yellow-900 mb-2">
                <span class="font-medium">Progreso: {{ data.solicitud.porcentaje || 0 }}%</span>
                <span>Paso {{ data.solicitud.paso_actual || 1 }} de 5</span>
              </div>
              <div class="w-full bg-yellow-200 rounded-full h-3">
                <div 
                  class="bg-yellow-600 h-3 rounded-full transition-all duration-500"
                  :style="{ width: `${data.solicitud.porcentaje || 0}%` }"
                />
              </div>
            </div>
            <router-link 
              to="/estudiante/admision"
              class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition"
            >
              Continuar solicitud
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Card de solicitud en revisión -->
      <div v-else-if="data.solicitud && ['pendiente', 'en_revision'].includes(data.solicitud.estado)" 
        class="bg-blue-50 border-2 border-blue-400 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <svg class="w-12 h-12 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-blue-900 mb-2">
              📋 Tu solicitud está en revisión
            </h3>
            <p class="text-sm text-blue-800 mb-3">
              Hemos recibido tu solicitud de admisión <strong>{{ data.solicitud.numero_referencia }}</strong>.
              Nuestro equipo está revisando tu información y documentos.
            </p>
            <p class="text-xs text-blue-700">
              Fecha de envío: {{ formatearFecha(data.solicitud.fecha_envio) }}
            </p>
            <p class="text-xs text-blue-600 mt-2 font-medium">
              ⏱️ Tiempo estimado de respuesta: 5-10 días hábiles
            </p>
          </div>
        </div>
      </div>

      <!-- Card de solicitud aprobada -->
      <div v-else-if="data.solicitud && data.solicitud.estado === 'aprobada'" 
        class="bg-green-50 border-2 border-green-400 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-green-900 mb-2">
              🎉 ¡Felicitaciones! Tu solicitud ha sido aprobada
            </h3>
            <p class="text-sm text-green-800 mb-3">
              ¡Bienvenido a la universidad! Tu solicitud de admisión ha sido aprobada exitosamente.
            </p>
            <p class="text-xs text-green-700" v-if="data.solicitud.comentarios_admin">
              <strong>Comentarios:</strong> {{ data.solicitud.comentarios_admin }}
            </p>
          </div>
        </div>
      </div>

      <!-- Card de solicitud rechazada -->
      <div v-else-if="data.solicitud && data.solicitud.estado === 'rechazada'" 
        class="bg-red-50 border-2 border-red-400 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-red-900 mb-2">
              ❌ Solicitud no aprobada
            </h3>
            <p class="text-sm text-red-800 mb-3">
              Lamentablemente tu solicitud no fue aprobada en esta oportunidad.
            </p>
            <p class="text-sm text-red-700" v-if="data.solicitud.razon_rechazo">
              <strong>Motivo:</strong> {{ data.solicitud.razon_rechazo }}
            </p>
          </div>
        </div>
      </div>

      <!-- Card requiere corrección -->
      <div v-else-if="data.solicitud && data.solicitud.estado === 'requiere_correccion'" 
        class="bg-orange-50 border-2 border-orange-400 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-orange-900 mb-2">
              ✏️ Tu solicitud requiere correcciones
            </h3>
            <p class="text-sm text-orange-800 mb-3">
              Hemos revisado tu solicitud y necesitamos que corrijas algunos datos antes de continuar.
            </p>
            <p class="text-sm text-orange-700 mb-4" v-if="data.solicitud.comentarios_admin">
              <strong>Comentarios:</strong> {{ data.solicitud.comentarios_admin }}
            </p>
            <router-link 
              to="/estudiante/admision"
              class="inline-flex items-center px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition"
            >
              Realizar correcciones
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Info del estudiante -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-2xl p-6 text-white mb-8">
        <div class="flex items-start gap-5">
          <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-bold flex-shrink-0">
            {{ initials }}
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold">{{ data.estudiante?.nombre_completo }}</h2>
            <p class="text-indigo-200 text-sm mt-0.5">{{ data.estudiante?.matricula }}</p>
            <div class="flex flex-wrap gap-3 mt-3">
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                📚 {{ data.estudiante?.carrera ?? 'Sin carrera' }}
              </span>
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                🏛️ {{ data.estudiante?.facultad ?? 'Sin facultad' }}
              </span>
              <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                📖 Semestre {{ data.estudiante?.semestre_actual }}
              </span>
              <span class="bg-green-400/30 text-green-100 text-xs font-semibold px-3 py-1 rounded-full">
                ✅ {{ data.estudiante?.estatus }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats académicos -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-indigo-600">{{ data.estadisticas?.materias_inscritas ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">Materias inscritas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-emerald-600">{{ data.estadisticas?.creditos_aprobados ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">Créditos aprobados</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold" :class="parseFloat(data.estadisticas?.indice_academico) >= 10 ? 'text-green-600' : 'text-red-600'">
            {{ data.estadisticas?.indice_academico ?? '0.00' }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Índice académico</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition-shadow">
          <p class="text-3xl font-bold text-blue-600">{{ data.estudiante?.semestre_actual ?? 1 }}</p>
          <p class="text-xs text-gray-500 mt-1">Semestre actual</p>
        </div>
      </div>

      <!-- Accesos rápidos -->
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Accesos Rápidos</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
          class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-indigo-200 transition text-center">
          <span class="text-3xl">{{ link.icon }}</span>
          <p class="text-sm font-semibold text-gray-900 mt-2">{{ link.label }}</p>
        </router-link>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const auth = useAuthStore()
const loading = ref(true)
const data = ref({})

const initials = computed(() => auth.userName?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?')

const quickLinks = [
  { to: '/estudiante/inscripcion',    icon: '📝', label: 'Inscripción' },
  { to: '/estudiante/horarios',       icon: '🕐', label: 'Mis Horarios' },
  { to: '/estudiante/calificaciones', icon: '🎓', label: 'Calificaciones' },
  { to: '/estudiante/perfil',         icon: '👤', label: 'Mi Perfil' },
]

// Función para formatear fechas
function formatearFecha(fecha) {
  if (!fecha) return ''
  const date = new Date(fecha)
  return date.toLocaleDateString('es-VE', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

onMounted(async () => {
  try { 
    const { data: res } = await api.get('/estudiante/dashboard')
    // res.data contiene { estudiante: {...}, estadisticas: {...} }
    data.value = res.data || res
  } finally { 
    loading.value = false 
  }
})
</script>
