<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Pagos</h1>
        <p class="text-gray-500 mt-1">Aranceles y pagos de estudiantes</p>
      </div>
      <button @click="abrirModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2">
        ➕ Registrar Pago
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-4">
      <div class="flex gap-2">
        <button v-for="t in ['', 'pendiente', 'pagado', 'vencido']" :key="t" @click="filtros.estatus = t; cargar(1)"
          class="px-4 py-2 rounded-xl text-sm font-semibold transition"
          :class="filtros.estatus === t ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
          {{ t || 'Todos' }}
        </button>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Estudiante</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Concepto</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Monto</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Vencimiento</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 6" :key="n"><td v-for="m in 6" :key="m" class="px-6 py-4"><div class="h-4 bg-gray-100 rounded animate-pulse"></div></td></tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-if="!pagos.length">
              <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                <p class="text-4xl mb-2">💰</p><p>No hay pagos registrados</p>
              </td>
            </tr>
            <tr v-for="p in pagos" :key="p.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 text-sm">{{ p.estudiante_nombre }} {{ p.estudiante_apellido }}</p>
                <p class="text-xs text-gray-500">{{ p.matricula }}</p>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ p.concepto }}</td>
              <td class="px-6 py-4 text-right font-semibold text-gray-900">
                {{ Number(p.monto).toLocaleString('es-VE', { style: 'currency', currency: 'VES' }) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="estatusBadge(p.estatus)">
                  {{ p.estatus }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <span :class="estaVencido(p) ? 'text-red-600 font-semibold' : ''">
                  {{ p.fecha_vencimiento ? formatDate(p.fecha_vencimiento) : '—' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <button v-if="p.estatus === 'pendiente'" @click="abrirConfirmarPago(p)"
                  class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                  ✅ Marcar pagado
                </button>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="pagination.last_page > 1" class="border-t border-gray-100 px-6 py-4 flex justify-end gap-2">
        <button v-for="p in pages" :key="p" @click="cargar(p)"
          :class="p === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          class="px-3 py-1.5 rounded-lg text-sm font-semibold transition">{{ p }}</button>
      </div>
    </div>

    <!-- Modal registrar pago -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">➕ Registrar Pago</h2>
          <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="registrarPago" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Estudiante (Matrícula o ID) *</label>
            <input v-model="form.estudiante_id" required type="number"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900"
              placeholder="ID del estudiante" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto *</label>
            <select v-model="form.concepto" required
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900">
              <option value="">Seleccionar...</option>
              <option value="Inscripción semestral">Inscripción semestral</option>
              <option value="Derecho de grado">Derecho de grado</option>
              <option value="Constancia de estudios">Constancia de estudios</option>
              <option value="Récord académico">Récord académico</option>
              <option value="Carnet estudiantil">Carnet estudiantil</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Monto *</label>
            <input v-model.number="form.monto" required type="number" min="0" step="0.01"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de vencimiento</label>
            <input v-model="form.fecha_vencimiento" type="date"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900" />
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modalAbierto = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button type="submit" :disabled="guardando" class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ guardando ? 'Guardando...' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal confirmar pago -->
    <div v-if="confirmPago" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">✅ Confirmar Pago</h2>
          <button @click="confirmPago = null" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">
            Concepto: <strong>{{ confirmPago.concepto }}</strong><br/>
            Monto: <strong>{{ Number(confirmPago.monto).toLocaleString() }}</strong>
          </p>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Número de referencia *</label>
            <input v-model="referencia" required type="text" placeholder="Ref. bancaria o nro. recibo"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 text-gray-900" />
          </div>
          <div class="flex gap-3">
            <button @click="confirmPago = null" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button @click="marcarPagado" :disabled="guardando || !referencia.trim()"
              class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ guardando ? 'Procesando...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const guardando = ref(false)
const modalAbierto = ref(false)
const confirmPago = ref(null)
const referencia = ref('')
const pagos = ref([])
const filtros = reactive({ estatus: '' })
const pagination = reactive({ current_page: 1, last_page: 1 })
const form = reactive({ estudiante_id: '', concepto: '', monto: 0, fecha_vencimiento: '' })

const pages = computed(() => {
  const r = []
  for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) r.push(i)
  return r
})

const formatDate = d => d ? new Date(d).toLocaleDateString('es-VE') : '—'
const estaVencido = p => p.estatus === 'pendiente' && p.fecha_vencimiento && new Date(p.fecha_vencimiento) < new Date()
const estatusBadge = e => ({
  pendiente: 'bg-yellow-100 text-yellow-700',
  pagado:    'bg-green-100 text-green-700',
  vencido:   'bg-red-100 text-red-700',
}[e] ?? 'bg-gray-100 text-gray-700')

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filtros.estatus) params.estatus = filtros.estatus
    const { data } = await api.get('/admin/pagos', { params })
    pagos.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page })
  } finally { loading.value = false }
}

function abrirModal() {
  Object.assign(form, { estudiante_id: '', concepto: '', monto: 0, fecha_vencimiento: '' })
  modalAbierto.value = true
}

async function registrarPago() {
  guardando.value = true
  try {
    await api.post('/admin/pagos', form)
    toast.success('Pago registrado exitosamente')
    modalAbierto.value = false
    await cargar()
  } finally { guardando.value = false }
}

function abrirConfirmarPago(p) {
  confirmPago.value = p
  referencia.value = ''
}

async function marcarPagado() {
  guardando.value = true
  try {
    await api.post(`/admin/pagos/${confirmPago.value.id}/marcar-pagado`, { referencia: referencia.value })
    toast.success('Pago confirmado')
    confirmPago.value = null
    await cargar()
  } finally { guardando.value = false }
}

onMounted(cargar)
</script>
