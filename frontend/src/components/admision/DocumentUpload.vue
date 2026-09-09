<template>
  <div class="border-2 border-dashed rounded-lg transition-colors"
    :class="archivoActual ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-white hover:border-indigo-400'">
    <div class="p-4">
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
          <h4 class="text-sm font-medium text-gray-900 flex items-center">
            {{ titulo }}
            <span v-if="requerido" class="ml-1 text-red-500">*</span>
            <svg v-if="archivoActual" class="w-4 h-4 text-green-600 ml-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          </h4>
          <p class="text-xs text-gray-500 mt-1">{{ descripcion }}</p>
        </div>
      </div>

      <!-- Zona de drop o archivo actual -->
      <div v-if="!archivoActual" class="space-y-3">
        <!-- Input oculto -->
        <input
          ref="fileInput"
          type="file"
          :accept="esImagen ? 'image/jpeg,image/jpg,image/png' : '.pdf,image/jpeg,image/jpg,image/png'"
          @change="onFileSelected"
          class="hidden"
        />

        <!-- Botón de selección -->
        <button
          type="button"
          @click="$refs.fileInput.click()"
          class="w-full flex flex-col items-center justify-center py-6 px-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors cursor-pointer"
        >
          <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">Haz click para seleccionar archivo</span>
          <span class="text-xs text-gray-500 mt-1">o arrastra y suelta aquí</span>
          <span class="text-xs text-gray-400 mt-2">
            {{ esImagen ? 'JPG, PNG' : 'PDF, JPG, PNG' }} (máx 5MB)
          </span>
        </button>
      </div>

      <!-- Archivo cargado -->
      <div v-else class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-200">
        <div class="flex items-center flex-1 min-w-0">
          <!-- Icono -->
          <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
            <svg v-if="esImagen || archivoActual.toLowerCase().match(/\.(jpg|jpeg|png)$/)" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <svg v-else class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          
          <!-- Nombre del archivo -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ nombreArchivo }}</p>
            <p class="text-xs text-gray-500">{{ tamanioArchivo }}</p>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center space-x-2 ml-3">
          <!-- Vista previa (solo imágenes) -->
          <button
            v-if="esImagen || archivoActual.toLowerCase().match(/\.(jpg|jpeg|png)$/)"
            type="button"
            @click="previsualizarImagen"
            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
            title="Vista previa"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>

          <!-- Reemplazar -->
          <button
            type="button"
            @click="$refs.fileInput.click()"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
            title="Reemplazar archivo"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </button>

          <!-- Eliminar -->
          <button
            type="button"
            @click="eliminarArchivo"
            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
            title="Eliminar archivo"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>

        <!-- Input oculto -->
        <input
          ref="fileInput"
          type="file"
          :accept="esImagen ? 'image/jpeg,image/jpg,image/png' : '.pdf,image/jpeg,image/jpg,image/png'"
          @change="onFileSelected"
          class="hidden"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  titulo: {
    type: String,
    required: true,
  },
  descripcion: {
    type: String,
    default: '',
  },
  requerido: {
    type: Boolean,
    default: false,
  },
  archivoActual: {
    type: String,
    default: null,
  },
  esImagen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['file-selected', 'file-removed'])

const toast = useToast()
const fileInput = ref(null)

// Nombre del archivo (solo el nombre, sin la ruta)
const nombreArchivo = computed(() => {
  if (!props.archivoActual) return ''
  return props.archivoActual.split('/').pop()
})

// Tamaño del archivo (simulado ya que no tenemos el File object)
const tamanioArchivo = computed(() => {
  return 'Cargado' // En producción podrías guardar el tamaño en la BD
})

// Handle file selection
function onFileSelected(event) {
  const file = event.target.files[0]
  if (!file) return
  
  emit('file-selected', file)
  
  // Resetear input para permitir seleccionar el mismo archivo de nuevo
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Eliminar archivo
function eliminarArchivo() {
  if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
    emit('file-removed')
    
    // Resetear input
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

// Previsualizar imagen
function previsualizarImagen() {
  // En producción, abrir modal con la imagen
  // Por ahora, solo mostrar un toast
  toast.info('Vista previa: ' + nombreArchivo.value)
}
</script>
