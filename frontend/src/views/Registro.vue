<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
      <!-- Logo y Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Registro de Admisión</h1>
        <p class="mt-2 text-gray-600">Crea tu cuenta para iniciar el proceso de admisión</p>
      </div>

      <!-- Card de Registro -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Nombre -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombres <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nombre"
              type="text"
              required
              placeholder="Ej: Juan Carlos"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{ 'border-red-500': errors.nombre }"
            />
            <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
          </div>

          <!-- Apellido -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Apellidos <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.apellido"
              type="text"
              required
              placeholder="Ej: Pérez González"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{ 'border-red-500': errors.apellido }"
            />
            <p v-if="errors.apellido" class="mt-1 text-sm text-red-600">{{ errors.apellido }}</p>
          </div>

          <!-- Cédula -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Cédula de Identidad <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.cedula"
              type="text"
              required
              placeholder="V-12345678 o E-12345678"
              @input="formatCedula"
              maxlength="11"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{ 'border-red-500': errors.cedula }"
            />
            <p v-if="errors.cedula" class="mt-1 text-sm text-red-600">{{ errors.cedula }}</p>
            <p class="mt-1 text-xs text-gray-500">Formato: V-12345678 o E-12345678</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Correo Electrónico <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="tu.email@ejemplo.com"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{ 'border-red-500': errors.email }"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            <p class="mt-1 text-xs text-gray-500">Usarás este email para iniciar sesión</p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Contraseña <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Mínimo 8 caracteres"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition pr-12"
                :class="{ 'border-red-500': errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            
            <!-- Password strength indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="flex gap-1 mb-1">
                <div 
                  v-for="i in 4" 
                  :key="i"
                  class="h-1 flex-1 rounded-full transition-colors"
                  :class="i <= passwordStrength ? strengthColors[passwordStrength] : 'bg-gray-200'"
                />
              </div>
              <p class="text-xs" :class="strengthTextColors[passwordStrength]">
                {{ strengthLabels[passwordStrength] }}
              </p>
            </div>
          </div>

          <!-- Password confirmation -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Confirmar Contraseña <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Repite tu contraseña"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{ 'border-red-500': errors.password_confirmation }"
            />
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <!-- Términos y condiciones -->
          <div class="flex items-start">
            <input
              v-model="form.acepta_terminos"
              type="checkbox"
              id="terminos"
              required
              class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="terminos" class="ml-3 text-sm text-gray-700">
              Acepto los 
              <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">
                términos y condiciones
              </a>
              y la 
              <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">
                política de privacidad
              </a>
            </label>
          </div>

          <!-- Botón Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Crear Cuenta y Continuar</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Creando cuenta...
            </span>
          </button>
        </form>

        <!-- Link a login -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            ¿Ya tienes una cuenta?
            <router-link to="/login" class="text-indigo-600 hover:text-indigo-500 font-medium">
              Inicia sesión aquí
            </router-link>
          </p>
        </div>
      </div>

      <!-- Info adicional -->
      <div class="mt-6 text-center text-sm text-gray-600">
        <p>
          Al registrarte, podrás iniciar el proceso de admisión para el período académico {{ periodoActual }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

const form = ref({
  nombre: '',
  apellido: '',
  cedula: '',
  email: '',
  password: '',
  password_confirmation: '',
  acepta_terminos: false,
})

const errors = ref({})
const loading = ref(false)
const showPassword = ref(false)

const periodoActual = computed(() => {
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth() + 1
  return month <= 6 ? `${year}-1` : `${year}-2`
})

// Password strength
const passwordStrength = computed(() => {
  const pwd = form.value.password
  if (!pwd) return 0
  
  let strength = 0
  if (pwd.length >= 8) strength++
  if (pwd.length >= 12) strength++
  if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) strength++
  if (/\d/.test(pwd)) strength++
  if (/[^a-zA-Z0-9]/.test(pwd)) strength++
  
  return Math.min(4, Math.floor(strength / 1.25))
})

const strengthColors = {
  0: 'bg-red-500',
  1: 'bg-red-500',
  2: 'bg-yellow-500',
  3: 'bg-green-500',
  4: 'bg-green-600',
}

const strengthTextColors = {
  0: 'text-red-600',
  1: 'text-red-600',
  2: 'text-yellow-600',
  3: 'text-green-600',
  4: 'text-green-700',
}

const strengthLabels = {
  0: 'Muy débil',
  1: 'Débil',
  2: 'Media',
  3: 'Fuerte',
  4: 'Muy fuerte',
}

function formatCedula(event) {
  let value = event.target.value.toUpperCase().replace(/[^VE0-9-]/g, '')
  
  // Auto-agregar guión después de V o E
  if (value.length === 1 && (value === 'V' || value === 'E')) {
    value += '-'
  }
  
  // Eliminar múltiples guiones
  value = value.replace(/--+/g, '-')
  
  form.value.cedula = value
}

function validateForm() {
  errors.value = {}
  
  // Validar cédula
  const cedulaRegex = /^[VE]-\d{7,8}$/
  if (!cedulaRegex.test(form.value.cedula)) {
    errors.value.cedula = 'Formato inválido. Usa V-12345678 o E-12345678'
  }
  
  // Validar email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email)) {
    errors.value.email = 'Email inválido'
  }
  
  // Validar password
  if (form.value.password.length < 8) {
    errors.value.password = 'La contraseña debe tener al menos 8 caracteres'
  }
  
  // Validar confirmación
  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Las contraseñas no coinciden'
  }
  
  // Validar términos
  if (!form.value.acepta_terminos) {
    toast.error('Debes aceptar los términos y condiciones')
    return false
  }
  
  return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
  if (!validateForm()) return
  
  loading.value = true
  errors.value = {}
  
  try {
    // Registrar usuario
    const { data } = await api.post('/public/registro', {
      nombre: form.value.nombre,
      apellido: form.value.apellido,
      cedula: form.value.cedula,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    })
    
    // Auto-login con el token recibido
    auth.token = data.token
    auth.user = data.user
    localStorage.setItem('sge_token', data.token)
    localStorage.setItem('sge_user', JSON.stringify(data.user))
    
    toast.success('¡Cuenta creada exitosamente! Bienvenido')
    
    // Redirigir al wizard de admisión
    setTimeout(() => {
      router.push('/estudiante/admision')
    }, 1000)
    
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      const firstError = Object.values(error.response.data.errors)[0]
      toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
    } else {
      toast.error(error.response?.data?.message || 'Error al crear la cuenta')
    }
  } finally {
    loading.value = false
  }
}
</script>
