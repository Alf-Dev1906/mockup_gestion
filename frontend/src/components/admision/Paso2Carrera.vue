<template>
  <div class="space-y-8">
    <!-- Header del paso -->
    <div class="border-b pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Selección de Carrera</h2>
      <p class="mt-1 text-sm text-gray-600">
        Elige la carrera que deseas estudiar y tus preferencias académicas.
      </p>
    </div>

    <form @submit.prevent="guardar" class="space-y-6">
      <!-- Facultad y Carrera -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Facultad <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.facultad_id"
            @change="onFacultadChange"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Selecciona una facultad...</option>
            <option v-for="facultad in facultades" :key="facultad.id" :value="facultad.id">
              {{ facultad.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Carrera (Primera Opción) <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.carrera_id"
            @change="onCarreraChange"
            required
            :disabled="!form.facultad_id || carrerasFiltradas.length === 0"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100"
          >
            <option value="">Selecciona una carrera...</option>
            <option v-for="carrera in carrerasFiltradas" :key="carrera.id" :value="carrera.id">
              {{ carrera.nombre }}
            </option>
          </select>
        </div>
      </div>

      <!-- Información de la carrera seleccionada -->
      <div v-if="carreraSeleccionada" class="bg-indigo-50 border border-indigo-200 rounded-lg p-5">
        <div class="flex items-start">
          <svg class="w-6 h-6 text-indigo-600 mt-1 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-indigo-900">{{ carreraSeleccionada.nombre }}</h3>
            <p v-if="carreraSeleccionada.descripcion" class="text-sm text-indigo-700 mt-2">
              {{ carreraSeleccionada.descripcion }}
            </p>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <span class="text-xs font-medium text-indigo-900">Duración</span>
                <p class="text-sm text-indigo-800 font-semibold">
                  {{ carreraSeleccionada.duracion_semestres }} semestres
                </p>
              </div>
              <div>
                <span class="text-xs font-medium text-indigo-900">Título</span>
                <p class="text-sm text-indigo-800 font-semibold">
                  {{ carreraSeleccionada.titulo_otorgado }}
                </p>
              </div>
              <div v-if="carreraSeleccionada.modalidades">
                <span class="text-xs font-medium text-indigo-900">Modalidades</span>
                <p class="text-sm text-indigo-800 font-semibold">
                  {{ carreraSeleccionada.modalidades }}
                </p>
              </div>
              <div v-if="carreraSeleccionada.turnos">
                <span class="text-xs font-medium text-indigo-900">Turnos</span>
                <p class="text-sm text-indigo-800 font-semibold">
                  {{ carreraSeleccionada.turnos }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Carrera alternativa -->
      <div>
        <div class="flex items-center mb-3">
          <input
            v-model="form.tiene_alternativa"
            type="checkbox"
            id="tiene_alternativa"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          />
          <label for="tiene_alternativa" class="ml-2 text-sm font-medium text-gray-700">
            Deseo seleccionar una carrera alternativa (segunda opción)
          </label>
        </div>

        <div v-if="form.tiene_alternativa">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Carrera Alternativa
          </label>
          <select
            v-model="form.carrera_alternativa_id"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Selecciona una alternativa...</option>
            <option 
              v-for="carrera in carrerasAlternativas" 
              :key="carrera.id" 
              :value="carrera.id"
            >
              {{ carrera.nombre }}
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500">
            Si no eres admitido en tu primera opción, se considerará esta carrera
          </p>
        </div>
      </div>

      <!-- Modalidad y Turno -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Modalidad de Estudio <span class="text-red-500">*</span>
          </label>
          <div class="space-y-2">
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.modalidad"
                type="radio"
                value="presencial"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">Presencial</span>
                <p class="text-xs text-gray-500">Asistencia diaria al campus</p>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.modalidad"
                type="radio"
                value="semipresencial"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">Semipresencial</span>
                <p class="text-xs text-gray-500">Combinación de clases presenciales y virtuales</p>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.modalidad"
                type="radio"
                value="distancia"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">A Distancia</span>
                <p class="text-xs text-gray-500">Estudio 100% virtual</p>
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Turno Preferido <span class="text-red-500">*</span>
          </label>
          <div class="space-y-2">
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.turno_preferido"
                type="radio"
                value="mañana"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">Mañana</span>
                <p class="text-xs text-gray-500">7:00 AM - 12:00 PM</p>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.turno_preferido"
                type="radio"
                value="tarde"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">Tarde</span>
                <p class="text-xs text-gray-500">1:00 PM - 6:00 PM</p>
              </div>
            </label>
            <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition">
              <input
                v-model="form.turno_preferido"
                type="radio"
                value="noche"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
              />
              <div class="ml-3">
                <span class="text-sm font-medium text-gray-900">Noche</span>
                <p class="text-xs text-gray-500">6:00 PM - 10:00 PM</p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Motivación -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          ¿Por qué deseas estudiar esta carrera? <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="form.motivacion"
          required
          rows="4"
          maxlength="500"
          placeholder="Cuéntanos tus motivaciones, intereses y metas profesionales..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
        />
        <div class="flex justify-between mt-1">
          <p class="text-xs text-gray-500">
            Esto nos ayudará a conocerte mejor durante el proceso de admisión
          </p>
          <p class="text-xs text-gray-500">
            {{ form.motivacion?.length || 0 }}/500
          </p>
        </div>
      </div>

      <!-- Botón de guardar -->
      <div class="flex items-center justify-between pt-6 border-t">
        <div class="text-sm text-gray-600">
          <span class="font-medium">Paso 2 de 5:</span> Selección de Carrera
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
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  solicitud: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['guardar', 'siguiente'])

const toast = useToast()
const guardando = ref(false)

// Datos
const facultades = ref([])
const carreras = ref([])

// Formulario
const form = ref({
  facultad_id: null,
  carrera_id: null,
  carrera_alternativa_id: null,
  tiene_alternativa: false,
  modalidad: '',
  turno_preferido: '',
  motivacion: '',
})

// Carreras filtradas por facultad
const carrerasFiltradas = computed(() => {
  if (!form.value.facultad_id) return []
  return carreras.value.filter(c => c.facultad_id === form.value.facultad_id)
})

// Carreras alternativas (todas menos la seleccionada)
const carrerasAlternativas = computed(() => {
  return carreras.value.filter(c => c.id !== form.value.carrera_id)
})

// Carrera seleccionada
const carreraSeleccionada = computed(() => {
  if (!form.value.carrera_id) return null
  return carreras.value.find(c => c.id === form.value.carrera_id)
})

// Cargar facultades y carreras
async function cargarDatos() {
  try {
    const [resFacultades, resCarreras] = await Promise.all([
      api.get('/public/facultades'),
      api.get('/public/carreras'),
    ])
    
    facultades.value = resFacultades.data
    carreras.value = resCarreras.data
  } catch (error) {
    toast.error('Error al cargar las facultades y carreras')
  }
}

// Al cambiar la facultad, limpiar carrera si no pertenece a la nueva facultad
function onFacultadChange() {
  if (form.value.carrera_id) {
    const carreraActual = carreras.value.find(c => c.id === form.value.carrera_id)
    if (carreraActual && carreraActual.facultad_id !== form.value.facultad_id) {
      form.value.carrera_id = null
    }
  }
}

// Al cambiar la carrera principal, limpiar alternativa si es la misma
function onCarreraChange() {
  if (form.value.carrera_alternativa_id === form.value.carrera_id) {
    form.value.carrera_alternativa_id = null
  }
}

// Cargar datos existentes
function cargarDatosFormulario() {
  if (props.solicitud) {
    // Primero cargar la carrera para obtener la facultad
    if (props.solicitud.carrera_id) {
      const carrera = carreras.value.find(c => c.id === props.solicitud.carrera_id)
      if (carrera) {
        form.value.facultad_id = carrera.facultad_id
      }
    }
    
    form.value = {
      facultad_id: form.value.facultad_id,
      carrera_id: props.solicitud.carrera_id || null,
      carrera_alternativa_id: props.solicitud.carrera_alternativa_id || null,
      tiene_alternativa: !!props.solicitud.carrera_alternativa_id,
      modalidad: props.solicitud.modalidad || '',
      turno_preferido: props.solicitud.turno_preferido || '',
      motivacion: props.solicitud.motivacion || '',
    }
  }
}

// Guardar
async function guardar() {
  guardando.value = true
  
  // Limpiar carrera alternativa si el checkbox está desactivado
  const datos = {
    ...form.value,
    carrera_alternativa_id: form.value.tiene_alternativa ? form.value.carrera_alternativa_id : null,
  }
  delete datos.facultad_id // No se guarda, solo para UI
  delete datos.tiene_alternativa // No se guarda, solo para UI
  
  const guardado = await emit('guardar', datos)
  
  if (guardado !== false) {
    emit('siguiente')
  }
  
  guardando.value = false
}

onMounted(async () => {
  await cargarDatos()
  cargarDatosFormulario()
})
</script>
