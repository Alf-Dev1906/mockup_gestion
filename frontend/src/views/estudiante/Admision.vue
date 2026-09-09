<template>
  <AdminLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-5xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Solicitud de Admisión</h1>
          <p class="mt-2 text-gray-600">
            Completa todos los pasos para enviar tu solicitud
          </p>
        </div>

        <!-- Stepper Progress -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="flex-1"
              :class="{ 'pr-4': index < steps.length - 1 }"
            >
              <div class="flex items-center">
                <!-- Círculo del paso -->
                <div
                  class="relative flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                  :class="getStepCircleClass(index + 1)"
                >
                  <!-- Icono de completado -->
                  <svg
                    v-if="solicitud && solicitud[`paso${index + 1}_completado`]"
                    class="w-5 h-5 text-white"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  <!-- Número del paso -->
                  <span
                    v-else
                    class="text-sm font-semibold"
                    :class="getStepNumberClass(index + 1)"
                  >
                    {{ index + 1 }}
                  </span>
                </div>

                <!-- Línea conectora -->
                <div
                  v-if="index < steps.length - 1"
                  class="flex-1 h-0.5 mx-2 transition-all duration-300"
                  :class="getStepLineClass(index + 1)"
                />
              </div>

              <!-- Nombre del paso -->
              <div class="mt-2">
                <p
                  class="text-xs font-medium transition-colors duration-300"
                  :class="getStepTextClass(index + 1)"
                >
                  {{ step.name }}
                </p>
              </div>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mt-4">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progreso</span>
              <span class="font-semibold">{{ porcentajeCompletitud }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                :style="{ width: `${porcentajeCompletitud}%` }"
              />
            </div>
          </div>
        </div>

        <!-- Contenido del paso actual -->
        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
          <!-- Loading state -->
          <div v-if="loading" class="flex items-center justify-center py-20">
            <svg
              class="animate-spin h-10 w-10 text-indigo-600"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
          </div>

          <!-- Paso actual -->
          <component
            v-else-if="solicitud"
            :is="currentStepComponent"
            :solicitud="solicitud"
            @guardar="guardarPasoActual"
            @siguiente="siguientePaso"
          />

          <!-- Error state -->
          <div v-else-if="error" class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Error al cargar</h3>
            <p class="text-gray-600 mb-4">{{ error }}</p>
            <button
              @click="cargarSolicitud"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
            >
              Intentar nuevamente
            </button>
          </div>
        </div>

        <!-- Navegación -->
        <div class="flex items-center justify-between">
          <button
            v-if="pasoActual > 1"
            @click="pasoAnterior"
            :disabled="guardando"
            class="flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Anterior
          </button>
          <div v-else />

          <!-- Info de guardado automático -->
          <div class="text-sm text-gray-500 flex items-center">
            <svg
              v-if="guardadoReciente"
              class="w-4 h-4 text-green-500 mr-2"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span v-if="guardando">Guardando...</span>
            <span v-else-if="guardadoReciente">Guardado</span>
            <span v-else>Los cambios se guardan automáticamente</span>
          </div>

          <button
            v-if="pasoActual < 5"
            @click="siguientePaso"
            :disabled="guardando || !puedeAvanzar"
            class="flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            Siguiente
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, markRaw } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

// Importar componentes de los pasos
import Paso1Personal from '@/components/admision/Paso1Personal.vue'
import Paso2Carrera from '@/components/admision/Paso2Carrera.vue'
import Paso3Academico from '@/components/admision/Paso3Academico.vue'
import Paso4Documentos from '@/components/admision/Paso4Documentos.vue'
import Paso5Revision from '@/components/admision/Paso5Revision.vue'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

// Estado
const solicitud = ref(null)
const pasoActual = ref(1)
const loading = ref(true)
const guardando = ref(false)
const guardadoReciente = ref(false)
const error = ref(null)

// Configuración de pasos
const steps = [
  { id: 1, name: 'Información Personal', component: markRaw(Paso1Personal) },
  { id: 2, name: 'Selección de Carrera', component: markRaw(Paso2Carrera) },
  { id: 3, name: 'Datos Académicos', component: markRaw(Paso3Academico) },
  { id: 4, name: 'Documentos', component: markRaw(Paso4Documentos) },
  { id: 5, name: 'Revisión y Envío', component: markRaw(Paso5Revision) },
]

// Componente del paso actual
const currentStepComponent = computed(() => {
  return steps.find(s => s.id === pasoActual.value)?.component
})

// Porcentaje de completitud
const porcentajeCompletitud = computed(() => {
  if (!solicitud.value) return 0
  let completados = 0
  for (let i = 1; i <= 5; i++) {
    if (solicitud.value[`paso${i}_completado`]) completados++
  }
  return Math.round((completados / 5) * 100)
})

// Verifica si puede avanzar al siguiente paso
const puedeAvanzar = computed(() => {
  if (!solicitud.value) return false
  return solicitud.value.paso_actual >= pasoActual.value + 1
})

// Clases CSS para el stepper
function getStepCircleClass(paso) {
  if (!solicitud.value) return 'border-gray-300 bg-white'
  
  if (solicitud.value[`paso${paso}_completado`]) {
    return 'border-green-500 bg-green-500'
  }
  if (pasoActual.value === paso) {
    return 'border-indigo-600 bg-indigo-600'
  }
  if (solicitud.value.paso_actual >= paso) {
    return 'border-indigo-300 bg-white'
  }
  return 'border-gray-300 bg-white'
}

function getStepNumberClass(paso) {
  if (!solicitud.value) return 'text-gray-400'
  
  if (pasoActual.value === paso) {
    return 'text-white'
  }
  if (solicitud.value.paso_actual >= paso) {
    return 'text-indigo-600'
  }
  return 'text-gray-400'
}

function getStepLineClass(paso) {
  if (!solicitud.value) return 'bg-gray-200'
  
  if (solicitud.value[`paso${paso}_completado`]) {
    return 'bg-green-500'
  }
  if (solicitud.value.paso_actual > paso) {
    return 'bg-indigo-300'
  }
  return 'bg-gray-200'
}

function getStepTextClass(paso) {
  if (!solicitud.value) return 'text-gray-400'
  
  if (solicitud.value[`paso${paso}_completado`]) {
    return 'text-green-600'
  }
  if (pasoActual.value === paso) {
    return 'text-indigo-600'
  }
  if (solicitud.value.paso_actual >= paso) {
    return 'text-gray-700'
  }
  return 'text-gray-400'
}

// Cargar solicitud
async function cargarSolicitud() {
  loading.value = true
  error.value = null
  
  try {
    const { data } = await api.get('/estudiante/admision/solicitud')
    solicitud.value = data
    pasoActual.value = data.paso_actual
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar la solicitud'
    toast.error(error.value)
  } finally {
    loading.value = false
  }
}

// Guardar paso actual
async function guardarPasoActual(datos) {
  guardando.value = true
  guardadoReciente.value = false
  
  try {
    const { data } = await api.post(`/estudiante/admision/paso-${pasoActual.value}`, datos)
    solicitud.value = data
    guardadoReciente.value = true
    
    // Ocultar el mensaje de "guardado" después de 3 segundos
    setTimeout(() => {
      guardadoReciente.value = false
    }, 3000)
    
    return true
  } catch (err) {
    toast.error(err.response?.data?.message || 'Error al guardar')
    return false
  } finally {
    guardando.value = false
  }
}

// Siguiente paso
async function siguientePaso() {
  if (pasoActual.value < 5 && puedeAvanzar.value) {
    pasoActual.value++
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

// Paso anterior
function pasoAnterior() {
  if (pasoActual.value > 1) {
    pasoActual.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

// Cargar al montar
onMounted(() => {
  cargarSolicitud()
})
</script>
