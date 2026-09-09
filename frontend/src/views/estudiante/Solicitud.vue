<template>
  <AdminLayout>
    <div class="max-w-2xl mx-auto">
      <div class="mb-8 text-center">
        <p class="text-5xl mb-4">📨</p>
        <h1 class="text-2xl font-bold text-gray-900">Estado de tu Solicitud</h1>
        <p class="text-gray-500 mt-1">Tu solicitud de admisión está siendo procesada</p>
      </div>

      <div v-if="loading" class="bg-white rounded-2xl border border-gray-100 p-8 animate-pulse h-64"></div>

      <template v-else-if="solicitud">
        <!-- Estado principal -->
        <div class="rounded-2xl p-6 mb-6 text-center"
          :class="estadoConfig.bg">
          <span class="text-5xl">{{ estadoConfig.icon }}</span>
          <h2 class="text-xl font-bold mt-3" :class="estadoConfig.textColor">
            {{ estadoConfig.titulo }}
          </h2>
          <p class="text-sm mt-1" :class="estadoConfig.textColor + '/80'">
            {{ estadoConfig.descripcion }}
          </p>
          <div class="mt-4">
            <span class="px-4 py-2 rounded-full text-sm font-bold" :class="estadoConfig.badge">
              {{ solicitud.estatus?.toUpperCase() }}
            </span>
          </div>
        </div>

        <!-- Número de solicitud -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Número de solicitud</span>
            <span class="font-mono font-bold text-gray-900 text-lg">{{ solicitud.numero_solicitud }}</span>
          </div>
          <div class="flex items-center justify-between mt-3">
            <span class="text-sm text-gray-500">Fecha de solicitud</span>
            <span class="text-sm text-gray-700">{{ formatDate(solicitud.created_at) }}</span>
          </div>
          <div v-if="solicitud.fecha_revision" class="flex items-center justify-between mt-3">
            <span class="text-sm text-gray-500">Fecha de revisión</span>
            <span class="text-sm text-gray-700">{{ formatDate(solicitud.fecha_revision) }}</span>
          </div>
          <div v-if="solicitud.revisado_por_nombre" class="flex items-center justify-between mt-3">
            <span class="text-sm text-gray-500">Revisado por</span>
            <span class="text-sm text-gray-700">{{ solicitud.revisado_por_nombre }}</span>
          </div>
        </div>

        <!-- Observaciones si fue rechazada -->
        <div v-if="solicitud.observaciones" class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-4">
          <p class="text-sm font-bold text-red-800 mb-2">📋 Observaciones del evaluador:</p>
          <p class="text-sm text-red-700">{{ solicitud.observaciones }}</p>
        </div>

        <!-- Resumen datos -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-4">
          <h3 class="font-semibold text-gray-900 mb-4">Tu información registrada</h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Nombre</p>
              <p class="font-medium text-gray-900 mt-0.5">
                {{ solicitud.datos_personales?.nombre }} {{ solicitud.datos_personales?.apellido }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Cédula</p>
              <p class="font-medium text-gray-900 mt-0.5">{{ solicitud.datos_personales?.cedula }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Email</p>
              <p class="font-medium text-gray-900 mt-0.5 truncate">{{ solicitud.datos_contacto?.email }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Teléfono</p>
              <p class="font-medium text-gray-900 mt-0.5">{{ solicitud.datos_contacto?.telefono }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 col-span-2">
              <p class="text-xs text-gray-500">Carrera solicitada</p>
              <p class="font-medium text-gray-900 mt-0.5">{{ solicitud.datos_academicos?.carrera }}</p>
            </div>
          </div>
        </div>

        <!-- Pasos del proceso -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-900 mb-4">Proceso de admisión</h3>
          <div class="space-y-3">
            <div v-for="paso in pasos" :key="paso.label" class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold"
                :class="paso.completado ? 'bg-green-100 text-green-700' : paso.activo ? 'bg-yellow-100 text-yellow-700 animate-pulse' : 'bg-gray-100 text-gray-400'">
                {{ paso.completado ? '✓' : paso.activo ? '⏳' : '○' }}
              </div>
              <div>
                <p class="text-sm font-medium" :class="paso.completado ? 'text-green-700' : paso.activo ? 'text-yellow-700' : 'text-gray-400'">
                  {{ paso.label }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
        <p class="text-5xl mb-3">📋</p>
        <p>No se encontró ninguna solicitud de admisión</p>
        <router-link to="/" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">
          Volver al inicio
        </router-link>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const solicitud = ref(null)

const formatDate = d => d ? new Date(d).toLocaleString('es-VE') : '—'

const estadoConfig = computed(() => {
  const e = solicitud.value?.estatus
  const configs = {
    pendiente: {
      bg: 'bg-yellow-50 border border-yellow-200',
      icon: '⏳', titulo: 'Solicitud en espera de revisión',
      descripcion: 'Tu solicitud está siendo evaluada por el personal administrativo.',
      textColor: 'text-yellow-800', badge: 'bg-yellow-200 text-yellow-800',
    },
    en_revision: {
      bg: 'bg-blue-50 border border-blue-200',
      icon: '🔍', titulo: 'Solicitud en revisión',
      descripcion: 'Un evaluador está revisando tu documentación.',
      textColor: 'text-blue-800', badge: 'bg-blue-200 text-blue-800',
    },
    aprobado: {
      bg: 'bg-green-50 border border-green-200',
      icon: '🎉', titulo: '¡Solicitud Aprobada!',
      descripcion: 'Felicitaciones, has sido admitido. Ahora puedes inscribirte en materias.',
      textColor: 'text-green-800', badge: 'bg-green-200 text-green-800',
    },
    rechazado: {
      bg: 'bg-red-50 border border-red-200',
      icon: '❌', titulo: 'Solicitud Rechazada',
      descripcion: 'Tu solicitud no fue aprobada. Revisa las observaciones.',
      textColor: 'text-red-800', badge: 'bg-red-200 text-red-800',
    },
  }
  return configs[e] ?? configs.pendiente
})

const pasos = computed(() => {
  const e = solicitud.value?.estatus
  return [
    { label: 'Solicitud enviada',   completado: true,                       activo: false },
    { label: 'En revisión',         completado: ['aprobado','rechazado','en_revision'].includes(e), activo: e === 'pendiente' },
    { label: 'Evaluación completa', completado: ['aprobado','rechazado'].includes(e),               activo: e === 'en_revision' },
    { label: 'Admitido',            completado: e === 'aprobado',           activo: false },
  ]
})

onMounted(async () => {
  try {
    const { data } = await api.get('/estudiante/solicitud')
    solicitud.value = data
  } catch { solicitud.value = null }
  finally { loading.value = false }
})
</script>
