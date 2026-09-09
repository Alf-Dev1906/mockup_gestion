<template>
  <div class="space-y-8">
    <!-- Header del paso -->
    <div class="border-b pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Documentos Requeridos</h2>
      <p class="mt-1 text-sm text-gray-600">
        Carga los documentos necesarios para tu solicitud. Los archivos deben estar en formato PDF, JPG o PNG (máx 5MB cada uno).
      </p>
    </div>

    <form @submit.prevent="guardar" class="space-y-6">
      <!-- Documentos obligatorios -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          Documentos Obligatorios
        </h3>

        <!-- Cédula -->
        <DocumentUpload
          id="doc_cedula"
          titulo="Cédula de Identidad (ambos lados)"
          descripcion="Escaneo o foto clara de ambos lados de tu cédula"
          :requerido="true"
          :archivo-actual="form.doc_cedula"
          @file-selected="handleFileSelected('doc_cedula', $event)"
          @file-removed="handleFileRemoved('doc_cedula')"
        />

        <!-- Partida de Nacimiento -->
        <DocumentUpload
          id="doc_partida_nacimiento"
          titulo="Partida de Nacimiento"
          descripcion="Partida de nacimiento original o copia certificada"
          :requerido="true"
          :archivo-actual="form.doc_partida_nacimiento"
          @file-selected="handleFileSelected('doc_partida_nacimiento', $event)"
          @file-removed="handleFileRemoved('doc_partida_nacimiento')"
        />

        <!-- Título de Bachiller / Notas -->
        <DocumentUpload
          id="doc_titulo_bachiller"
          titulo="Título de Bachiller o Notas Certificadas"
          descripcion="Título de bachiller, acta de grado o notas certificadas"
          :requerido="true"
          :archivo-actual="form.doc_titulo_bachiller"
          @file-selected="handleFileSelected('doc_titulo_bachiller', $event)"
          @file-removed="handleFileRemoved('doc_titulo_bachiller')"
        />

        <!-- Foto tipo carnet -->
        <DocumentUpload
          id="doc_foto_carnet"
          titulo="Foto tipo Carnet"
          descripcion="Foto reciente, fondo blanco, formal (tipo cédula)"
          :requerido="true"
          :archivo-actual="form.doc_foto_carnet"
          :es-imagen="true"
          @file-selected="handleFileSelected('doc_foto_carnet', $event)"
          @file-removed="handleFileRemoved('doc_foto_carnet')"
        />

        <!-- Comprobante de Domicilio -->
        <DocumentUpload
          id="doc_comprobante_domicilio"
          titulo="Comprobante de Domicilio"
          descripcion="Recibo de servicio (luz, agua, teléfono) reciente (máx 3 meses)"
          :requerido="true"
          :archivo-actual="form.doc_comprobante_domicilio"
          @file-selected="handleFileSelected('doc_comprobante_domicilio', $event)"
          @file-removed="handleFileRemoved('doc_comprobante_domicilio')"
        />
      </div>

      <!-- Documentos opcionales -->
      <div class="space-y-4 pt-6 border-t">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          Documentos Opcionales (Recomendados)
        </h3>

        <!-- Certificado Médico -->
        <DocumentUpload
          id="doc_certificado_medico"
          titulo="Certificado Médico"
          descripcion="Certificado médico reciente que indique tu estado de salud general"
          :requerido="false"
          :archivo-actual="form.doc_certificado_medico"
          @file-selected="handleFileSelected('doc_certificado_medico', $event)"
          @file-removed="handleFileRemoved('doc_certificado_medico')"
        />

        <!-- Carta de Buena Conducta -->
        <DocumentUpload
          id="doc_carta_conducta"
          titulo="Carta de Buena Conducta"
          descripcion="Emitida por autoridad policial o institución educativa"
          :requerido="false"
          :archivo-actual="form.doc_carta_conducta"
          @file-selected="handleFileSelected('doc_carta_conducta', $event)"
          @file-removed="handleFileRemoved('doc_carta_conducta')"
        />
      </div>

      <!-- Resumen de documentos cargados -->
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900">Progreso de Documentos</h4>
            <p class="text-sm text-gray-600 mt-1">
              {{ documentosCargados }} de {{ documentosObligatorios }} documentos obligatorios
            </p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold" :class="todosCargados ? 'text-green-600' : 'text-gray-400'">
              {{ Math.round((documentosCargados / documentosObligatorios) * 100) }}%
            </div>
          </div>
        </div>
        <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
          <div
            class="h-2 rounded-full transition-all duration-500"
            :class="todosCargados ? 'bg-green-600' : 'bg-indigo-600'"
            :style="{ width: `${(documentosCargados / documentosObligatorios) * 100}%` }"
          />
        </div>
      </div>

      <!-- Nota importante -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h4 class="text-sm font-medium text-blue-900">Requisitos de los archivos</h4>
          <ul class="text-sm text-blue-700 mt-2 space-y-1 list-disc list-inside">
            <li>Formato aceptado: PDF, JPG, PNG</li>
            <li>Tamaño máximo: 5MB por archivo</li>
            <li>Los documentos deben ser legibles y estar completos</li>
            <li>Puedes reemplazar archivos en cualquier momento</li>
          </ul>
        </div>
      </div>

      <!-- Botón de guardar -->
      <div class="flex items-center justify-between pt-6 border-t">
        <div class="text-sm text-gray-600">
          <span class="font-medium">Paso 4 de 5:</span> Documentos
        </div>
        
        <button
          type="submit"
          :disabled="guardando || !todosCargados"
          class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="!guardando">
            {{ todosCargados ? 'Guardar y Continuar' : `Faltan ${documentosObligatorios - documentosCargados} documentos` }}
          </span>
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
import DocumentUpload from './DocumentUpload.vue'
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

// Formulario - guardamos File objects y nombres
const form = ref({
  doc_cedula: null,
  doc_partida_nacimiento: null,
  doc_titulo_bachiller: null,
  doc_foto_carnet: null,
  doc_comprobante_domicilio: null,
  doc_certificado_medico: null,
  doc_carta_conducta: null,
})

// Archivos nuevos seleccionados (para upload)
const nuevosArchivos = ref({})

const documentosObligatorios = 5

// Contar documentos cargados
const documentosCargados = computed(() => {
  let count = 0
  const obligatorios = [
    'doc_cedula',
    'doc_partida_nacimiento',
    'doc_titulo_bachiller',
    'doc_foto_carnet',
    'doc_comprobante_domicilio',
  ]
  
  obligatorios.forEach(doc => {
    if (form.value[doc]) count++
  })
  
  return count
})

// Todos los obligatorios cargados
const todosCargados = computed(() => documentosCargados.value === documentosObligatorios)

// Handle file selected
function handleFileSelected(campo, file) {
  // Validar tamaño
  const maxSize = 5 * 1024 * 1024 // 5MB
  if (file.size > maxSize) {
    toast.error('El archivo excede el tamaño máximo de 5MB')
    return
  }
  
  // Validar tipo
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
  if (!allowedTypes.includes(file.type)) {
    toast.error('Formato no permitido. Usa PDF, JPG o PNG')
    return
  }
  
  // Guardar referencia al archivo
  form.value[campo] = file.name
  nuevosArchivos.value[campo] = file
}

// Handle file removed
function handleFileRemoved(campo) {
  form.value[campo] = null
  delete nuevosArchivos.value[campo]
}

// Cargar datos existentes
function cargarDatos() {
  if (props.solicitud) {
    form.value = {
      doc_cedula: props.solicitud.doc_cedula || null,
      doc_partida_nacimiento: props.solicitud.doc_partida_nacimiento || null,
      doc_titulo_bachiller: props.solicitud.doc_titulo_bachiller || null,
      doc_foto_carnet: props.solicitud.doc_foto_carnet || null,
      doc_comprobante_domicilio: props.solicitud.doc_comprobante_domicilio || null,
      doc_certificado_medico: props.solicitud.doc_certificado_medico || null,
      doc_carta_conducta: props.solicitud.doc_carta_conducta || null,
    }
  }
}

// Guardar
async function guardar() {
  if (!todosCargados.value) {
    toast.error('Debes cargar todos los documentos obligatorios')
    return
  }
  
  guardando.value = true
  
  // Crear FormData para enviar archivos
  const formData = new FormData()
  
  // Agregar archivos nuevos
  Object.keys(nuevosArchivos.value).forEach(campo => {
    formData.append(campo, nuevosArchivos.value[campo])
  })
  
  // Agregar nombres de archivos existentes (para mantenerlos)
  Object.keys(form.value).forEach(campo => {
    if (form.value[campo] && !nuevosArchivos.value[campo]) {
      formData.append(`${campo}_existente`, form.value[campo])
    }
  })
  
  const guardado = await emit('guardar', formData)
  
  if (guardado !== false) {
    // Limpiar archivos nuevos después de guardar
    nuevosArchivos.value = {}
    emit('siguiente')
  }
  
  guardando.value = false
}

onMounted(() => {
  cargarDatos()
})
</script>
