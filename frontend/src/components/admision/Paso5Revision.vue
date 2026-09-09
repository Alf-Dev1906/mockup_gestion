<template>
  <div class="space-y-8">
    <!-- Header del paso -->
    <div class="border-b pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Revisión y Envío de Solicitud</h2>
      <p class="mt-1 text-sm text-gray-600">
        Revisa cuidadosamente toda tu información antes de enviar la solicitud.
      </p>
    </div>

    <!-- Resumen de datos -->
    <div class="space-y-6">
      <!-- Paso 1: Información Personal -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 flex items-center">
            <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs mr-2">1</span>
            Información Personal
          </h3>
          <button
            type="button"
            @click="$emit('ir-a-paso', 1)"
            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Editar
          </button>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-500">Nombre Completo</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.nombre }} {{ solicitud.apellido }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Cédula</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.cedula }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Fecha de Nacimiento</p>
            <p class="text-sm font-medium text-gray-900">{{ formatearFecha(solicitud.fecha_nacimiento) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Género</p>
            <p class="text-sm font-medium text-gray-900">{{ formatearGenero(solicitud.genero) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Teléfono Principal</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.telefono_principal }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.email }}</p>
          </div>
          <div class="md:col-span-2">
            <p class="text-xs text-gray-500">Dirección</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.direccion }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ solicitud.ciudad }}, {{ solicitud.estado }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 2: Carrera Seleccionada -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 flex items-center">
            <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs mr-2">2</span>
            Carrera y Preferencias
          </h3>
          <button
            type="button"
            @click="$emit('ir-a-paso', 2)"
            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Editar
          </button>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-500">Carrera Principal</p>
            <p class="text-sm font-medium text-gray-900">{{ nombreCarrera(solicitud.carrera_id) }}</p>
          </div>
          <div v-if="solicitud.carrera_alternativa_id">
            <p class="text-xs text-gray-500">Carrera Alternativa</p>
            <p class="text-sm font-medium text-gray-900">{{ nombreCarrera(solicitud.carrera_alternativa_id) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Modalidad</p>
            <p class="text-sm font-medium text-gray-900 capitalize">{{ solicitud.modalidad_preferida }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Turno Preferido</p>
            <p class="text-sm font-medium text-gray-900 capitalize">{{ solicitud.turno_preferido }}</p>
          </div>
          <div class="md:col-span-2" v-if="solicitud.motivacion">
            <p class="text-xs text-gray-500">Motivación</p>
            <p class="text-sm text-gray-900">{{ solicitud.motivacion }}</p>
          </div>
        </div>
      </div>

      <!-- Paso 3: Datos Académicos -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 flex items-center">
            <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs mr-2">3</span>
            Datos Académicos
          </h3>
          <button
            type="button"
            @click="$emit('ir-a-paso', 3)"
            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Editar
          </button>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-500">Nivel Educativo</p>
            <p class="text-sm font-medium text-gray-900">{{ formatearNivelEducativo(solicitud.nivel_educativo) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Institución</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.institucion_egreso }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Año de Graduación</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.año_graduacion }}</p>
          </div>
          <div v-if="solicitud.promedio_notas">
            <p class="text-xs text-gray-500">Promedio</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.promedio_notas }}/20</p>
          </div>
          <div v-if="solicitud.universidad_procedencia" class="md:col-span-2">
            <p class="text-xs text-gray-500">Universidad de Procedencia</p>
            <p class="text-sm font-medium text-gray-900">{{ solicitud.universidad_procedencia }}</p>
            <p class="text-xs text-gray-600 mt-1">
              {{ solicitud.carrera_previa }} - {{ solicitud.semestres_completados }} semestres
            </p>
          </div>
          <div v-if="solicitud.desea_convalidar" class="md:col-span-2">
            <div class="flex items-center text-sm text-indigo-600">
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              Solicita convalidación de materias
            </div>
          </div>
        </div>
      </div>

      <!-- Paso 4: Documentos -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 flex items-center">
            <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs mr-2">4</span>
            Documentos Cargados
          </h3>
          <button
            type="button"
            @click="$emit('ir-a-paso', 4)"
            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Editar
          </button>
        </div>
        <div class="p-4">
          <div class="space-y-2">
            <div v-for="doc in documentos" :key="doc.campo" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
              <div class="flex items-center">
                <svg v-if="solicitud[doc.campo]" class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <svg v-else class="w-5 h-5 text-gray-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-gray-900">{{ doc.nombre }}</span>
                <span v-if="doc.requerido" class="ml-2 text-xs text-red-500">*</span>
              </div>
              <span v-if="solicitud[doc.campo]" class="text-xs text-green-600 font-medium">Cargado</span>
              <span v-else class="text-xs text-gray-400">No cargado</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Declaración jurada -->
    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Declaración Jurada</h3>
      <div class="space-y-4">
        <label class="flex items-start cursor-pointer">
          <input
            v-model="declaraciones.veracidad"
            type="checkbox"
            required
            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5 flex-shrink-0"
          />
          <span class="ml-3 text-sm text-gray-900">
            Declaro bajo juramento que toda la información proporcionada en esta solicitud es 
            <strong>verdadera, completa y correcta</strong>. Entiendo que cualquier falsedad u omisión 
            puede resultar en la anulación de mi solicitud o expulsión de la universidad.
          </span>
        </label>

        <label class="flex items-start cursor-pointer">
          <input
            v-model="declaraciones.documentos"
            type="checkbox"
            required
            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5 flex-shrink-0"
          />
          <span class="ml-3 text-sm text-gray-900">
            Declaro que los documentos presentados son <strong>auténticos y originales</strong>, 
            y me comprometo a presentar las versiones físicas cuando sean requeridas por la institución.
          </span>
        </label>

        <label class="flex items-start cursor-pointer">
          <input
            v-model="declaraciones.reglamento"
            type="checkbox"
            required
            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5 flex-shrink-0"
          />
          <span class="ml-3 text-sm text-gray-900">
            He leído y acepto el <strong>Reglamento de Admisión</strong> y me comprometo a cumplir 
            con todas las normas y procedimientos establecidos por la universidad.
          </span>
        </label>
      </div>
    </div>

    <!-- Nota informativa -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
      <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
      <div>
        <h4 class="text-sm font-medium text-blue-900">¿Qué sucede después?</h4>
        <p class="text-sm text-blue-700 mt-1">
          Una vez enviada tu solicitud, será revisada por el equipo de admisiones. Recibirás 
          notificaciones por email sobre el estado de tu solicitud. El proceso puede tomar entre 
          5 y 10 días hábiles.
        </p>
      </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex items-center justify-between pt-6 border-t">
      <button
        type="button"
        @click="$emit('anterior')"
        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
      >
        Anterior
      </button>

      <button
        type="button"
        @click="enviarSolicitud"
        :disabled="enviando || !todasLasDeclaraciones"
        class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center"
      >
        <svg v-if="!enviando" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span v-if="!enviando">Enviar Solicitud de Admisión</span>
        <span v-else class="flex items-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
          </svg>
          Enviando...
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  solicitud: {
    type: Object,
    required: true,
  },
  carreras: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['enviar', 'ir-a-paso', 'anterior'])

const toast = useToast()
const enviando = ref(false)

// Declaraciones juradas
const declaraciones = ref({
  veracidad: false,
  documentos: false,
  reglamento: false,
})

// Todas las declaraciones aceptadas
const todasLasDeclaraciones = computed(() => {
  return declaraciones.value.veracidad && 
         declaraciones.value.documentos && 
         declaraciones.value.reglamento
})

// Lista de documentos
const documentos = [
  { campo: 'doc_cedula', nombre: 'Cédula de Identidad', requerido: true },
  { campo: 'doc_partida_nacimiento', nombre: 'Partida de Nacimiento', requerido: true },
  { campo: 'doc_titulo_bachiller', nombre: 'Título de Bachiller', requerido: true },
  { campo: 'doc_foto_carnet', nombre: 'Foto tipo Carnet', requerido: true },
  { campo: 'doc_comprobante_domicilio', nombre: 'Comprobante de Domicilio', requerido: true },
  { campo: 'doc_certificado_medico', nombre: 'Certificado Médico', requerido: false },
  { campo: 'doc_carta_conducta', nombre: 'Carta de Buena Conducta', requerido: false },
]

// Formatear fecha
function formatearFecha(fecha) {
  if (!fecha) return 'No especificada'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-VE', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

// Formatear género
function formatearGenero(genero) {
  const generos = {
    'M': 'Masculino',
    'F': 'Femenino',
    'O': 'Otro'
  }
  return generos[genero] || genero
}

// Formatear nivel educativo
function formatearNivelEducativo(nivel) {
  const niveles = {
    'bachiller': 'Bachiller',
    'tsu': 'Técnico Superior Universitario (TSU)',
    'universitario_incompleto': 'Universitario Incompleto',
    'licenciatura': 'Licenciatura/Ingeniería',
    'posgrado': 'Posgrado'
  }
  return niveles[nivel] || nivel
}

// Obtener nombre de carrera
function nombreCarrera(carreraId) {
  if (!carreraId || !props.carreras.length) return 'No especificada'
  const carrera = props.carreras.find(c => c.id === carreraId)
  return carrera ? carrera.nombre : `Carrera ID: ${carreraId}`
}

// Enviar solicitud
async function enviarSolicitud() {
  if (!todasLasDeclaraciones.value) {
    toast.error('Debes aceptar todas las declaraciones juradas')
    return
  }

  // Confirmar envío
  if (!confirm('¿Estás seguro de que deseas enviar tu solicitud de admisión? Una vez enviada, no podrás modificar la información.')) {
    return
  }

  enviando.value = true

  try {
    await emit('enviar')
  } catch (error) {
    console.error('Error al enviar solicitud:', error)
  } finally {
    enviando.value = false
  }
}
</script>
