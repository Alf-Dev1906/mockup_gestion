<template>
  <div class="space-y-8">
    <!-- Header del paso -->
    <div class="border-b pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Datos Académicos Previos</h2>
      <p class="mt-1 text-sm text-gray-600">
        Cuéntanos sobre tu formación académica anterior.
      </p>
    </div>

    <form @submit.prevent="guardar" class="space-y-6">
      <!-- Nivel educativo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Nivel Educativo Completado <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label 
            v-for="nivel in nivelesEducativos" 
            :key="nivel.value"
            class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition hover:border-indigo-500"
            :class="form.nivel_educativo === nivel.value ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'"
          >
            <input
              v-model="form.nivel_educativo"
              type="radio"
              :value="nivel.value"
              required
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mt-0.5"
            />
            <div class="ml-3">
              <span class="block text-sm font-medium text-gray-900">{{ nivel.label }}</span>
              <span class="block text-xs text-gray-500 mt-1">{{ nivel.descripcion }}</span>
            </div>
          </label>
        </div>
      </div>

      <!-- Institución y año de graduación -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Institución de Egreso <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.institucion_egreso"
            type="text"
            required
            placeholder="Nombre completo del colegio/universidad"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Año de Graduación <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.año_graduacion"
            type="number"
            required
            :min="añoMinimo"
            :max="añoActual"
            placeholder="YYYY"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>
      </div>

      <!-- Promedio y tipo de bachillerato (solo si es bachiller) -->
      <div v-if="form.nivel_educativo === 'bachiller'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Promedio de Notas <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.promedio_notas"
            type="number"
            step="0.01"
            min="0"
            max="20"
            required
            placeholder="Escala 1-20"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
          <p class="mt-1 text-xs text-gray-500">Escala venezolana de 1 a 20</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Bachillerato <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.tipo_bachillerato"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Selecciona...</option>
            <option value="ciencias">Ciencias</option>
            <option value="humanidades">Humanidades</option>
            <option value="tecnico">Técnico</option>
          </select>
        </div>
      </div>

      <!-- Mención (solo bachiller) -->
      <div v-if="form.nivel_educativo === 'bachiller'">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Mención (si aplica)
        </label>
        <input
          v-model="form.mencion"
          type="text"
          placeholder="Ej: Mención Ciencias, Mención Humanidades"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        />
      </div>

      <!-- Convalidaciones (solo para TSU o universitario incompleto) -->
      <div 
        v-if="['tsu', 'universitario_incompleto', 'licenciatura'].includes(form.nivel_educativo)"
        class="border-t pt-6 mt-6"
      >
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estudios Universitarios Previos</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Universidad de Procedencia <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.universidad_procedencia"
              type="text"
              required
              placeholder="Nombre de la universidad"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Carrera Cursada <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.carrera_previa"
              type="text"
              required
              placeholder="Nombre de la carrera"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Semestres Completados <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.semestres_completados"
              type="number"
              required
              min="1"
              max="12"
              placeholder="Número de semestres"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Índice Académico <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.promedio_notas"
              type="number"
              step="0.01"
              min="0"
              max="20"
              required
              placeholder="Escala 1-20"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Checkbox convalidar -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <label class="flex items-start cursor-pointer">
            <input
              v-model="form.desea_convalidar"
              type="checkbox"
              class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5"
            />
            <div class="ml-3">
              <span class="text-sm font-medium text-gray-900">
                Deseo solicitar convalidación de materias
              </span>
              <p class="text-xs text-gray-600 mt-1">
                Si seleccionas esta opción, deberás presentar el récord de notas certificado
                durante el proceso de admisión para evaluar qué materias pueden ser convalidadas.
              </p>
            </div>
          </label>
        </div>
      </div>

      <!-- Nota informativa -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h4 class="text-sm font-medium text-yellow-900">Importante</h4>
          <p class="text-sm text-yellow-700 mt-1">
            La información académica que proporciones será verificada. Asegúrate de que todos
            los datos sean correctos y coincidan con tus documentos oficiales.
          </p>
        </div>
      </div>

      <!-- Botón de guardar -->
      <div class="flex items-center justify-between pt-6 border-t">
        <div class="text-sm text-gray-600">
          <span class="font-medium">Paso 3 de 5:</span> Datos Académicos
        </div>
        
        <button
          type="submit"
          :disabled="guardando"
          class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="!guardando">Guardar y Continuar</span>
          <span v-else class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Guardando...
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  solicitud: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['guardar', 'siguiente'])

const guardando = ref(false)

// Configuración
const añoActual = new Date().getFullYear()
const añoMinimo = añoActual - 50

const nivelesEducativos = [
  {
    value: 'bachiller',
    label: 'Bachiller',
    descripcion: 'Educación media completa'
  },
  {
    value: 'tsu',
    label: 'TSU',
    descripcion: 'Técnico Superior Universitario'
  },
  {
    value: 'universitario_incompleto',
    label: 'Universitario Incompleto',
    descripcion: 'Carrera universitaria no culminada'
  },
  {
    value: 'licenciatura',
    label: 'Licenciatura/Ingeniería',
    descripcion: 'Título universitario completo'
  },
  {
    value: 'posgrado',
    label: 'Posgrado',
    descripcion: 'Maestría, Doctorado o Especialización'
  },
]

// Formulario
const form = ref({
  nivel_educativo: '',
  institucion_egreso: '',
  año_graduacion: null,
  promedio_notas: null,
  tipo_bachillerato: '',
  mencion: '',
  
  // Para estudios universitarios previos
  universidad_procedencia: '',
  carrera_previa: '',
  semestres_completados: null,
  desea_convalidar: false,
})

// Limpiar campos condicionales cuando cambia el nivel educativo
watch(() => form.value.nivel_educativo, (nuevoNivel, viejoNivel) => {
  if (nuevoNivel !== viejoNivel) {
    // Limpiar campos de bachillerato si ya no es bachiller
    if (nuevoNivel !== 'bachiller') {
      form.value.tipo_bachillerato = ''
      form.value.mencion = ''
    }
    
    // Limpiar campos universitarios si no aplica
    if (!['tsu', 'universitario_incompleto', 'licenciatura'].includes(nuevoNivel)) {
      form.value.universidad_procedencia = ''
      form.value.carrera_previa = ''
      form.value.semestres_completados = null
      form.value.desea_convalidar = false
    }
    
    // Limpiar promedio si cambia el contexto
    if (viejoNivel) {
      form.value.promedio_notas = null
    }
  }
})

// Cargar datos existentes
function cargarDatos() {
  if (props.solicitud) {
    form.value = {
      nivel_educativo: props.solicitud.nivel_educativo || '',
      institucion_egreso: props.solicitud.institucion_egreso || '',
      año_graduacion: props.solicitud.año_graduacion || null,
      promedio_notas: props.solicitud.promedio_notas || null,
      tipo_bachillerato: props.solicitud.tipo_bachillerato || '',
      mencion: props.solicitud.mencion || '',
      universidad_procedencia: props.solicitud.universidad_procedencia || '',
      carrera_previa: props.solicitud.carrera_previa || '',
      semestres_completados: props.solicitud.semestres_completados || null,
      desea_convalidar: props.solicitud.desea_convalidar || false,
    }
  }
}

// Guardar
async function guardar() {
  guardando.value = true
  
  // Limpiar campos que no aplican según el nivel educativo
  const datos = { ...form.value }
  
  if (datos.nivel_educativo !== 'bachiller') {
    datos.tipo_bachillerato = null
    datos.mencion = null
  }
  
  if (!['tsu', 'universitario_incompleto', 'licenciatura'].includes(datos.nivel_educativo)) {
    datos.universidad_procedencia = null
    datos.carrera_previa = null
    datos.semestres_completados = null
    datos.desea_convalidar = false
  }
  
  const guardado = await emit('guardar', datos)
  
  if (guardado !== false) {
    emit('siguiente')
  }
  
  guardando.value = false
}

onMounted(() => {
  cargarDatos()
})
</script>
