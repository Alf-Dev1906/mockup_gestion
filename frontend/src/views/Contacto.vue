<template>
  <div class="contacto">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-5xl font-bold mb-4">Contáctanos</h1>
        <p class="text-blue-100 text-lg">Estamos aquí para ayudarte. Escríbenos y te responderemos lo antes posible.</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Grid principal -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Información de contacto -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
              <span class="text-3xl">📍</span>
            </div>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Dirección</h3>
            <p class="text-gray-600">Av. Universidad, Edificio Central</p>
            <p class="text-gray-600">Los Chaguaramos, Caracas 1041</p>
            <p class="text-gray-600">Venezuela</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-4">
              <span class="text-3xl">📞</span>
            </div>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Teléfonos</h3>
            <p class="text-gray-600">+58 212-123-4567</p>
            <p class="text-gray-600">+58 212-765-4321</p>
            <p class="text-gray-600 text-sm mt-2">WhatsApp: +58 424-555-7890</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-4">
              <span class="text-3xl">✉️</span>
            </div>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Correos</h3>
            <p class="text-gray-600 text-sm">General: info@universidad.edu.ve</p>
            <p class="text-gray-600 text-sm">Admisión: admision@universidad.edu.ve</p>
            <p class="text-gray-600 text-sm">Soporte: soporte@universidad.edu.ve</p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mb-4">
              <span class="text-3xl">🕐</span>
            </div>
            <h3 class="font-bold text-gray-800 text-lg mb-2">Horario de atención</h3>
            <p class="text-gray-600">Lunes a Viernes</p>
            <p class="text-gray-600 font-semibold">8:00 AM - 6:00 PM</p>
            <p class="text-gray-600 mt-2">Sábados</p>
            <p class="text-gray-600 font-semibold">9:00 AM - 1:00 PM</p>
          </div>
        </div>

        <!-- Formulario de contacto -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Envíanos un Mensaje</h2>
            <p class="text-gray-600 mb-8">Completa el formulario y nos pondremos en contacto contigo pronto.</p>

            <!-- Mensaje de éxito -->
            <div v-if="mensajeEnviado" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-start gap-3">
              <span class="text-2xl">✅</span>
              <div>
                <p class="font-semibold text-green-800">¡Mensaje enviado exitosamente!</p>
                <p class="text-sm text-green-700">Te responderemos lo antes posible.</p>
              </div>
            </div>

            <!-- Mensaje de error -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-start gap-3">
              <span class="text-2xl">⚠️</span>
              <div>
                <p class="font-semibold text-red-800">Error al enviar el mensaje</p>
                <p class="text-sm text-red-700">{{ error }}</p>
              </div>
            </div>

            <form @submit.prevent="enviarMensaje" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre completo *</label>
                  <input 
                    v-model="form.nombre" 
                    type="text" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    :class="{ 'border-red-500': errores.nombre }"
                    placeholder="Tu nombre completo">
                  <p v-if="errores.nombre" class="text-red-500 text-sm mt-1">{{ errores.nombre }}</p>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    :class="{ 'border-red-500': errores.email }"
                    placeholder="tu@email.com">
                  <p v-if="errores.email" class="text-red-500 text-sm mt-1">{{ errores.email }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                  <input 
                    v-model="form.telefono" 
                    type="tel"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="+58 424-555-7890">
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de consulta *</label>
                  <select 
                    v-model="form.tipo" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">Selecciona una opción</option>
                    <option value="admision">Admisión</option>
                    <option value="informacion">Información general</option>
                    <option value="soporte">Soporte técnico</option>
                    <option value="otro">Otro</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Asunto *</label>
                <input 
                  v-model="form.asunto" 
                  type="text" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  :class="{ 'border-red-500': errores.asunto }"
                  placeholder="Breve descripción del tema">
                <p v-if="errores.asunto" class="text-red-500 text-sm mt-1">{{ errores.asunto }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mensaje *</label>
                <textarea 
                  v-model="form.mensaje" 
                  rows="6" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                  :class="{ 'border-red-500': errores.mensaje }"
                  placeholder="Escribe tu mensaje aquí con el mayor detalle posible..."></textarea>
                <p v-if="errores.mensaje" class="text-red-500 text-sm mt-1">{{ errores.mensaje }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ form.mensaje.length }} / 500 caracteres</p>
              </div>

              <button 
                type="submit" 
                :disabled="enviando"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-4 rounded-xl font-bold transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span v-if="!enviando">Enviar Mensaje</span>
                <span v-else>Enviando...</span>
                <span v-if="!enviando">✉️</span>
                <span v-else class="animate-spin">⏳</span>
              </button>
            </form>
          </div>

          <!-- Redes sociales -->
          <div class="mt-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Síguenos en redes sociales</h3>
            <div class="flex gap-4">
              <a href="#" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow">
                <span class="text-xl">📘</span>
              </a>
              <a href="#" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-blue-400 hover:text-white transition shadow">
                <span class="text-xl">🐦</span>
              </a>
              <a href="#" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-pink-600 hover:text-white transition shadow">
                <span class="text-xl">📷</span>
              </a>
              <a href="#" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center hover:bg-red-600 hover:text-white transition shadow">
                <span class="text-xl">▶️</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const form = reactive({
  nombre: '',
  email: '',
  telefono: '',
  tipo: '',
  asunto: '',
  mensaje: ''
})

const errores = reactive({})
const mensajeEnviado = ref(false)
const enviando = ref(false)
const error = ref(null)

const validarFormulario = () => {
  Object.keys(errores).forEach(key => delete errores[key])
  let valido = true

  if (!form.nombre || form.nombre.length < 3) {
    errores.nombre = 'El nombre debe tener al menos 3 caracteres'
    valido = false
  }

  if (!form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errores.email = 'Ingresa un email válido'
    valido = false
  }

  if (!form.asunto || form.asunto.length < 5) {
    errores.asunto = 'El asunto debe tener al menos 5 caracteres'
    valido = false
  }

  if (!form.mensaje || form.mensaje.length < 20) {
    errores.mensaje = 'El mensaje debe tener al menos 20 caracteres'
    valido = false
  }

  if (form.mensaje.length > 500) {
    errores.mensaje = 'El mensaje no puede exceder 500 caracteres'
    valido = false
  }

  return valido
}

const enviarMensaje = async () => {
  if (!validarFormulario()) return

  enviando.value = true
  error.value = null
  mensajeEnviado.value = false

  try {
    // Simulación de envío (en producción conectar con backend)
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    console.log('Mensaje enviado:', form)
    
    mensajeEnviado.value = true
    
    // Limpiar formulario
    Object.keys(form).forEach(key => form[key] = '')
    
    // Ocultar mensaje de éxito después de 5 segundos
    setTimeout(() => {
      mensajeEnviado.value = false
    }, 5000)
  } catch (err) {
    error.value = 'Ocurrió un error al enviar el mensaje. Por favor intenta nuevamente.'
    console.error('Error:', err)
  } finally {
    enviando.value = false
  }
}
</script>