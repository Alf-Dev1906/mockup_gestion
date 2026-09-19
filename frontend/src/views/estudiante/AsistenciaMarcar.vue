<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()

const horarios = ref([])
const horarioSeleccionado = ref(null)
const codigoInput = ref('')
const cargando = ref(false)
const historial = ref([])

// Cargar horarios activos
const cargarHorarios = async () => {
  try {
    const { data } = await api.get('/estudiante/inscripciones')
    horarios.value = (data.data || []).map(ins => ({
      id: ins.horario.id,
      inscripcion_id: ins.id,
      materia: ins.materia.nombre,
      profesor: ins.profesor,
      asistencia_activa: true,
    }))
    
    if (horarios.value.length > 0) {
      horarioSeleccionado.value = horarios.value[0].id
    }
  } catch (error) {
    toast.error('Error al cargar horarios')
  }
}

// Marcar asistencia
const marcarAsistencia = async () => {
  if (!horarioSeleccionado.value) {
    toast.error('Selecciona una materia')
    return
  }
  
  if (!codigoInput.value) {
    toast.error('Ingresa el código')
    return
  }

  cargando.value = true
  try {
    const { data } = await api.post('/estudiante/asistencia/marcar', {
      horario_id: horarioSeleccionado.value,
      codigo: codigoInput.value,
    })
    
    toast.success('✅ Asistencia registrada')
    codigoInput.value = ''
    
    // Recargar historial
    cargarHistorial()
    
    // Recargar horarios para actualizar estado
    setTimeout(cargarHorarios, 1000)
  } catch (error) {
    if (error.response?.status === 409) {
      toast.error('⚠️ Ya registraste tu asistencia en esta sesión')
    } else if (error.response?.status === 404) {
      toast.error('❌ No hay sesión activa')
    } else if (error.response?.status === 410) {
      toast.error('❌ El código ha expirado')
    } else {
      toast.error('❌ Código incorrecto')
    }
  } finally {
    cargando.value = false
  }
}

// Cargar historial de asistencias
const cargarHistorial = async () => {
  try {
    const { data } = await api.get('/estudiante/asistencia/resumen')
    historial.value = data.data || { estadisticas: {}, historial: [] }
  } catch (error) {
    console.error('Error cargando historial:', error)
  }
}

// Formato de porcentaje
const formatoPorcentaje = (valor) => {
  return `${Math.round(valor)}%`
}

// Color según porcentaje
const colorPorcentaje = (valor) => {
  if (valor >= 90) return 'bg-green-500'
  if (valor >= 75) return 'bg-blue-500'
  if (valor >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
}

// Alerta si bajo 75%
const alertaBajaAsistencia = computed(() => {
  return historial.value.estadisticas?.porcentaje_asistencia < 75
})

// Formato de fecha
const formatoFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-VE', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Auto-uppercase
const onInput = (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6)
  codigoInput.value = e.target.value
}

// Iniciar
onMounted(() => {
  cargarHorarios()
  cargarHistorial()
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    
    <!-- Título -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Marcar Asistencia</h1>
      <p class="text-gray-500">Ingresa el código del profesor para registrar tu asistencia</p>
    </div>

    <!-- Card de entrada -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
        <span>📋</span> Selecciona tu materia
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-700">Materia / Horario</label>
          <select v-model="horarioSeleccionado"
            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-gray-900 bg-white focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
            :disabled="horarios.length === 0">
            <option value="" disabled>Selecciona una materia</option>
            <option v-for="h in horarios" :key="h.id" :value="h.id" class="text-gray-900">
              {{ h.materia }} - {{ h.profesor }}
              <span v-if="h.asistencia_activa" class="ml-2 bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">
                ✓ Activo
              </span>
            </option>
          </select>
        </div>
      </div>

      <div class="border-t border-gray-100 pt-4">
        <label class="text-sm font-semibold text-gray-700 block mb-2">Código de asistencia</label>
        <div class="flex gap-2">
          <input v-model="codigoInput" @input="onInput"
            type="text" maxlength="6"
            class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-2xl font-mono text-center uppercase text-gray-900 bg-white focus:ring-2 focus:ring-indigo-400 focus:border-transparent tracking-widest"
            placeholder="ABC123" />
          <button @click="marcarAsistencia" :disabled="cargando"
            class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold py-3 px-8 rounded-xl transition shadow-lg">
            {{ cargando ? '⏳' : '✅' }} Marcar
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-2 text-center">
          Ingrésalo exactamente como lo muestra el profesor (sin guiones)
        </p>
      </div>
    </div>

    <!-- Historial de asistencias -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <h2 class="text-lg font-bold text-gray-900">Historial de Asistencias</h2>
      </div>

      <!-- Estadísticas generales -->
      <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
          <p class="text-xs text-gray-500 uppercase font-bold">Total</p>
          <p class="text-2xl font-bold text-gray-900">{{ historial.estadisticas?.total || 0 }}</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-gray-500 uppercase font-bold">Presentes</p>
          <p class="text-2xl font-bold text-green-600">{{ historial.estadisticas?.presentes || 0 }}</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-gray-500 uppercase font-bold">Justificados</p>
          <p class="text-2xl font-bold text-blue-600">{{ historial.estadisticas?.justificados || 0 }}</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-gray-500 uppercase font-bold">Ausentes</p>
          <p class="text-2xl font-bold text-red-600">{{ historial.estadisticas?.ausentes || 0 }}</p>
        </div>
      </div>

      <!-- Porcentaje general con alerta -->
      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
        <div class="flex items-center gap-3">
          <div class="flex-1">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-semibold">Promedio de asistencia</span>
              <span class="font-bold" :class="historial.estadisticas?.porcentaje_asistencia >= 75 ? 'text-indigo-600' : 'text-red-600 animate-pulse'">
                {{ formatoPorcentaje(historial.estadisticas?.porcentaje_asistencia || 0) }}
              </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-500"
                :class="colorPorcentaje(historial.estadisticas?.porcentaje_asistencia || 0)"
                :style="{ width: (historial.estadisticas?.porcentaje_asistencia || 0) + '%' }" />
            </div>
          </div>
          <span v-if="alertaBajaAsistencia" class="text-red-500 text-sm font-bold animate-bounce">
            ⚠️ Menos de 75%
          </span>
        </div>
      </div>

      <!-- Lista de sesiones -->
      <div class="divide-y divide-gray-100">
        <div v-if="!historial.historial || historial.historial.length === 0" class="p-8 text-center text-gray-400">
          <p>📅 Aún no tienes registros de asistencia</p>
        </div>

        <div v-for="a in historial.historial" :key="a.id" class="px-6 py-4 flex flex-col md:flex-row md:items-center gap-3 hover:bg-gray-50 transition">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ a.materia }}</p>
            <p class="text-xs text-gray-500">{{ formatoFecha(a.registrado_at) }}</p>
          </div>
          
          <span class="px-3 py-1 rounded-full text-xs font-bold"
            :class="{
              'bg-green-100 text-green-700': a.estatus === 'Presente' || a.estatus === 'Justificado',
              'bg-red-100 text-red-700': a.estatus === 'Ausente'
            }">
            {{ a.estatus }}
          </span>
          
          <div v-if="a.observacion" class="text-xs text-gray-400 italic">
            "{{ a.observacion }}"
          </div>
        </div>
      </div>
    </div>

    <!-- Nota sobre regulación -->
    <div class="mt-6 text-center text-xs text-gray-400">
      <p>Según la regulación académica, se requiere un mínimo del 75% de asistencia</p>
    </div>
  </div>
</template>
