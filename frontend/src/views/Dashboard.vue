<template>
  <div class="dashboard">
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-12">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-4xl font-bold mb-2">Aula Virtual</h1>
            <p class="text-blue-100">Bienvenido, {{ user?.name || 'Usuario' }}</p>
          </div>
          <button 
            @click="logout"
            class="bg-red-500 hover:bg-red-400 text-white px-6 py-2 rounded-lg font-semibold transition"
          >
            Cerrar Sesión
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Mis Materias</h3>
            <span class="text-3xl">📚</span>
          </div>
          <div class="text-4xl font-bold text-blue-900">5</div>
          <p class="text-gray-600 text-sm mt-2">Materias inscritas</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Índice Académico</h3>
            <span class="text-3xl">📊</span>
          </div>
          <div class="text-4xl font-bold text-green-600">18.5</div>
          <p class="text-gray-600 text-sm mt-2">Promedio actual</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Créditos</h3>
            <span class="text-3xl">🎓</span>
          </div>
          <div class="text-4xl font-bold text-purple-600">120</div>
          <p class="text-gray-600 text-sm mt-2">Créditos aprobados</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Horario de Clases</h2>
          <div class="space-y-4">
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <span class="text-xl">🕐</span>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Programación I</h3>
                <p class="text-sm text-gray-600">Lunes 8:00 AM - 10:00 AM</p>
              </div>
              <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">En curso</span>
            </div>
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <span class="text-xl">🕐</span>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Estructuras de Datos</h3>
                <p class="text-sm text-gray-600">Lunes 10:00 AM - 12:00 PM</p>
              </div>
              <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Próximo</span>
            </div>
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <span class="text-xl">🕐</span>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Bases de Datos</h3>
                <p class="text-sm text-gray-600">Martes 2:00 PM - 4:00 PM</p>
              </div>
              <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">Programado</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Próximas Evaluaciones</h2>
          <div class="space-y-4">
            <div class="flex items-center p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Examen Parcial</h3>
                <p class="text-sm text-gray-600">Programación I</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-red-600">15 Sep</div>
                <p class="text-xs text-gray-500">8:00 AM</p>
              </div>
            </div>
            <div class="flex items-center p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Proyecto Final</h3>
                <p class="text-sm text-gray-600">Estructuras de Datos</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-yellow-600">20 Sep</div>
                <p class="text-xs text-gray-500">11:59 PM</p>
              </div>
            </div>
            <div class="flex items-center p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Quiz</h3>
                <p class="text-sm text-gray-600">Bases de Datos</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-blue-600">25 Sep</div>
                <p class="text-xs text-gray-500">3:00 PM</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApiStore } from '../stores/api'

const router = useRouter()
const apiStore = useApiStore()
const user = ref(null)

onMounted(async () => {
  try {
    user.value = await apiStore.getMe()
  } catch (error) {
    console.error('Error al obtener usuario:', error)
  }
})

const logout = async () => {
  try {
    await apiStore.logout()
    router.push('/')
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
  }
}
</script>