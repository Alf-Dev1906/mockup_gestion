<template>
  <div class="space-y-8">
    <!-- Header del paso -->
    <div class="border-b pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Información Personal</h2>
      <p class="mt-1 text-sm text-gray-600">
        Completa tus datos personales. Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
      </p>
    </div>

    <form @submit.prevent="guardar" class="space-y-6">
      <!-- Datos ya registrados (readonly) -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          <div class="flex-1">
            <h3 class="text-sm font-medium text-blue-900">Datos del registro</h3>
            <p class="text-sm text-blue-700 mt-1">
              Los siguientes datos fueron registrados al crear tu cuenta y no se pueden modificar aquí.
            </p>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <span class="text-xs font-medium text-blue-900">Nombres:</span>
                <p class="text-sm text-blue-800">{{ datosRegistrados.nombre }}</p>
              </div>
              <div>
                <span class="text-xs font-medium text-blue-900">Apellidos:</span>
                <p class="text-sm text-blue-800">{{ datosRegistrados.apellido }}</p>
              </div>
              <div>
                <span class="text-xs font-medium text-blue-900">Cédula:</span>
                <p class="text-sm text-blue-800">{{ datosRegistrados.cedula }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Fecha de nacimiento y género -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha de Nacimiento <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.fecha_nacimiento"
            type="date"
            required
            :max="fechaMaxima"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
          <p class="mt-1 text-xs text-gray-500">Debes tener al menos 16 años</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Género <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.genero"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Selecciona...</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
      </div>

      <!-- Teléfonos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Teléfono Principal <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.telefono"
            type="tel"
            required
            placeholder="0414-1234567"
            @input="formatTelefono('telefono', $event)"
            maxlength="13"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
          <p class="mt-1 text-xs text-gray-500">Formato: 0XXX-XXXXXXX</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Teléfono Alternativo
          </label>
          <input
            v-model="form.telefono_alternativo"
            type="tel"
            placeholder="0212-1234567 (opcional)"
            @input="formatTelefono('telefono_alternativo', $event)"
            maxlength="13"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>
      </div>

      <!-- Dirección -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Dirección Completa <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="form.direccion"
          required
          rows="3"
          placeholder="Calle, avenida, número de casa/apartamento, punto de referencia..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
        />
      </div>

      <!-- Ciudad, Estado, Código Postal -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Ciudad <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.ciudad"
            type="text"
            required
            placeholder="Ej: Caracas"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Estado <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.estado"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Selecciona...</option>
            <option v-for="estado in estadosVenezuela" :key="estado" :value="estado">
              {{ estado }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Código Postal
          </label>
          <input
            v-model="form.codigo_postal"
            type="text"
            placeholder="1234 (opcional)"
            maxlength="10"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>
      </div>

      <!-- Contacto de Emergencia -->
      <div class="border-t pt-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contacto de Emergencia</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombre Completo <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.contacto_emergencia_nombre"
              type="text"
              required
              placeholder="Nombre y apellido"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Teléfono <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.contacto_emergencia_telefono"
              type="tel"
              required
              placeholder="0414-1234567"
              @input="formatTelefono('contacto_emergencia_telefono', $event)"
              maxlength="13"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Parentesco <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.contacto_emergencia_relacion"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="">Selecciona...</option>
              <option value="Padre">Padre</option>
              <option value="Madre">Madre</option>
              <option value="Hermano/a">Hermano/a</option>
              <option value="Tío/a">Tío/a</option>
              <option value="Abuelo/a">Abuelo/a</option>
              <option value="Primo/a">Primo/a</option>
              <option value="Cónyuge">Cónyuge</option>
              <option value="Amigo/a">Amigo/a</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Botón de guardar -->
      <div class="flex items-center justify-between pt-6 border-t">
        <div class="text-sm text-gray-600">
          <span class="font-medium">Paso 1 de 5:</span> Información Personal
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
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  solicitud: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['guardar', 'siguiente'])

const auth = useAuthStore()
const guardando = ref(false)

// Datos ya registrados (readonly)
const datosRegistrados = computed(() => ({
  nombre: props.solicitud.estudiante?.nombre || auth.user.name.split(' ')[0] || '',
  apellido: props.solicitud.estudiante?.apellido || auth.user.name.split(' ').slice(1).join(' ') || '',
  cedula: props.solicitud.estudiante?.cedula || '',
}))

// Formulario
const form = ref({
  fecha_nacimiento: '',
  genero: '',
  telefono: '',
  telefono_alternativo: '',
  direccion: '',
  ciudad: '',
  estado: '',
  codigo_postal: '',
  contacto_emergencia_nombre: '',
  contacto_emergencia_telefono: '',
  contacto_emergencia_relacion: '',
})

// Estados de Venezuela
const estadosVenezuela = [
  'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas', 'Bolívar', 'Carabobo',
  'Cojedes', 'Delta Amacuro', 'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
  'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta', 'Portuguesa', 'Sucre',
  'Táchira', 'Trujillo', 'Vargas', 'Yaracuy', 'Zulia'
]

// Fecha máxima (16 años atrás)
const fechaMaxima = computed(() => {
  const fecha = new Date()
  fecha.setFullYear(fecha.getFullYear() - 16)
  return fecha.toISOString().split('T')[0]
})

// Formatear teléfono (XXXX-XXXXXXX)
function formatTelefono(campo, event) {
  let value = event.target.value.replace(/[^0-9]/g, '')
  
  if (value.length >= 4) {
    value = value.substring(0, 4) + '-' + value.substring(4, 11)
  }
  
  form.value[campo] = value
}

// Cargar datos existentes
function cargarDatos() {
  if (props.solicitud) {
    form.value = {
      fecha_nacimiento: props.solicitud.fecha_nacimiento || '',
      genero: props.solicitud.genero || '',
      telefono: props.solicitud.telefono || '',
      telefono_alternativo: props.solicitud.telefono_alternativo || '',
      direccion: props.solicitud.direccion || '',
      ciudad: props.solicitud.ciudad || '',
      estado: props.solicitud.estado || '',
      codigo_postal: props.solicitud.codigo_postal || '',
      contacto_emergencia_nombre: props.solicitud.contacto_emergencia_nombre || '',
      contacto_emergencia_telefono: props.solicitud.contacto_emergencia_telefono || '',
      contacto_emergencia_relacion: props.solicitud.contacto_emergencia_relacion || '',
    }
  }
}

// Guardar
async function guardar() {
  guardando.value = true
  
  const guardado = await emit('guardar', form.value)
  
  if (guardado !== false) {
    emit('siguiente')
  }
  
  guardando.value = false
}

onMounted(() => {
  cargarDatos()
})
</script>
