<template>
  <div class="login min-h-screen bg-gradient-to-br from-blue-900 via-blue-700 to-blue-900 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Logo y Branding -->
      <div class="text-center mb-8">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
          <span class="text-4xl font-bold text-blue-900">U</span>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Aula Virtual</h1>
        <p class="text-blue-200">Universidad de Caracas</p>
      </div>

      <!-- Card de Login -->
      <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Iniciar Sesión</h2>
        
        <form @submit.prevent="login" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input 
              v-model="loginForm.email" 
              type="email" 
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 bg-gray-50"
              placeholder="admin@universidad.edu.ve"
            >
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña</label>
            <input 
              v-model="loginForm.password" 
              type="password" 
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 bg-gray-50"
              placeholder="••••••••"
            >
          </div>
          
          <div v-if="loginError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ loginError }}
          </div>
          
          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
          >
            {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
          </button>
        </form>
        
        <div class="mt-6 text-center space-y-2">
          <a href="#" class="text-blue-600 hover:text-blue-800 font-medium text-sm">¿Olvidaste tu contraseña?</a>
          <div class="pt-4 border-t border-gray-200">
            <p class="text-gray-600 text-sm">¿No tienes cuenta? <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Regístrate</a></p>
          </div>
        </div>
      </div>

      <!-- Información de ayuda -->
      <div class="mt-8 text-center">
        <p class="text-blue-200 text-sm mb-2">¿Necesitas ayuda?</p>
        <a href="#" class="text-white hover:text-blue-200 font-medium">Contactar Soporte</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const loginError = ref('')
const loginForm = ref({
  email: '',
  password: ''
})

const login = async () => {
  loading.value = true
  loginError.value = ''
  
  try {
    await authStore.login(loginForm.value)
    router.push('/dashboard')
  } catch (error) {
    console.error('Error de login:', error)
    loginError.value = 'Credenciales inválidas. Por favor, intenta nuevamente.'
  } finally {
    loading.value = false
  }
}
</script>