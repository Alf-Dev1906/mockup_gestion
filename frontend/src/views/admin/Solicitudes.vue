<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Admisión</h1>
      <p class="text-gray-500 mt-1">Revisar, aprobar o rechazar solicitudes de ingreso al sistema universitario</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-4">
      <div class="flex gap-2">
        <button v-for="tab in tabs" :key="tab.value" @click="filtros.estatus = tab.value; cargar(1)"
          class="px-4 py-2 rounded-xl text-sm font-semibold transition"
          :class="filtros.estatus === tab.value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
          {{ tab.icon }} {{ tab.label }}
          <span v-if="tab.count !== undefined"
            class="ml-1 text-xs px-1.5 py-0.5 rounded-full"
            :class="filtros.estatus === tab.value ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'">
            {{ tab.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nº Solicitud</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Solicitante</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 5" :key="n"><td v-for="m in 6" :key="m" class="px-6 py-4"><div class="h-4 bg-gray-100 rounded animate-pulse"></div></td></tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-if="!solicitudes.length">
              <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                <p class="text-4xl mb-2">📨</p><p>No hay solicitudes {{ filtros.estatus ? 'con este estado' : '' }}</p>
              </td>
            </tr>
            <tr v-for="s in solicitudes" :key="s.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono text-sm font-semibold text-gray-900">{{ s.numero_referencia }}</td>
              <td class="px-6 py-4 font-medium text-gray-900">{{ s.estudiante?.nombre }} {{ s.estudiante?.apellido }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ s.estudiante?.user?.email }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="estatusBadge(s.estado)">
                  {{ s.estado }}
                </span>
              </td>
              <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(s.fecha_envio || s.created_at) }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="verDetalle(s)" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition" title="Ver detalle">🔍</button>
                  <button v-if="['pendiente', 'en_revision'].includes(s.estado)" @click="aprobar(s)"
                    class="text-green-700 hover:bg-green-50 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    ✅ Aprobar
                  </button>
                  <button v-if="['pendiente', 'en_revision'].includes(s.estado)" @click="abrirRechazo(s)"
                    class="text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    ❌ Rechazar
                  </button>
                  <button v-if="['pendiente', 'en_revision'].includes(s.estado)" @click="abrirCorreccion(s)"
                    class="text-orange-700 hover:bg-orange-50 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                    ✏️ Corrección
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="pagination.last_page > 1" class="border-t border-gray-100 px-6 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Página {{ pagination.current_page }} de {{ pagination.last_page }}</p>
        <div class="flex gap-2">
          <button v-for="p in pages" :key="p" @click="cargar(p)"
            :class="p === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-3 py-1.5 rounded-lg text-sm font-semibold transition">{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- Modal detalle -->
    <div v-if="detalleAbierto && solicitudDetalle" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl my-8">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between rounded-t-2xl">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Solicitud {{ solicitudDetalle.numero_referencia }}</h2>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize" :class="estatusBadge(solicitudDetalle.estado)">
              {{ solicitudDetalle.estado }}
            </span>
          </div>
          <button @click="detalleAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
          <!-- Paso 1: Datos Personales -->
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="text-sm font-bold text-blue-900 mb-3">👤 Información Personal</h3>
            <div class="grid grid-cols-2 gap-3">
              <InfoRow label="Nombre Completo" :value="`${solicitudDetalle.estudiante?.nombre} ${solicitudDetalle.estudiante?.apellido}`" />
              <InfoRow label="Cédula" :value="solicitudDetalle.estudiante?.cedula" />
              <InfoRow label="Fecha de Nacimiento" :value="formatDate(solicitudDetalle.fecha_nacimiento)" />
              <InfoRow label="Género" :value="solicitudDetalle.genero === 'M' ? 'Masculino' : solicitudDetalle.genero === 'F' ? 'Femenino' : 'Otro'" />
              <InfoRow label="Teléfono Principal" :value="solicitudDetalle.telefono" />
              <InfoRow label="Teléfono Secundario" :value="solicitudDetalle.telefono_alternativo || '—'" />
              <div class="col-span-2"><InfoRow label="Dirección" :value="solicitudDetalle.direccion" /></div>
              <InfoRow label="Ciudad" :value="solicitudDetalle.ciudad" />
              <InfoRow label="Estado" :value="solicitudDetalle.estado" />
              <InfoRow label="Email" :value="solicitudDetalle.estudiante?.user?.email" />
              <div class="col-span-2 bg-white rounded-lg p-3">
                <p class="text-xs text-gray-500 mb-2">Contacto de Emergencia</p>
                <p class="text-sm font-medium">{{ solicitudDetalle.contacto_emergencia_nombre }} - {{ solicitudDetalle.contacto_emergencia_telefono }}</p>
                <p class="text-xs text-gray-600">{{ solicitudDetalle.contacto_emergencia_relacion }}</p>
              </div>
            </div>
          </div>

          <!-- Paso 2: Carrera -->
          <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
            <h3 class="text-sm font-bold text-purple-900 mb-3">🎓 Carrera y Preferencias</h3>
            <div class="grid grid-cols-2 gap-3">
              <InfoRow label="Carrera Principal" :value="solicitudDetalle.carrera?.nombre" />
              <InfoRow label="Carrera Alternativa" :value="solicitudDetalle.carrera_alternativa?.nombre || 'No especificada'" />
              <InfoRow label="Modalidad" :value="solicitudDetalle.modalidad" class="capitalize" />
              <InfoRow label="Turno Preferido" :value="solicitudDetalle.turno_preferido" class="capitalize" />
              <div v-if="solicitudDetalle.motivacion" class="col-span-2 bg-white rounded-lg p-3">
                <p class="text-xs text-gray-500 mb-1">Motivación</p>
                <p class="text-sm text-gray-900">{{ solicitudDetalle.motivacion }}</p>
              </div>
            </div>
          </div>

          <!-- Paso 3: Académico -->
          <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <h3 class="text-sm font-bold text-green-900 mb-3">📚 Datos Académicos</h3>
            <div class="grid grid-cols-2 gap-3">
              <InfoRow label="Nivel Educativo" :value="solicitudDetalle.nivel_educativo" class="capitalize" />
              <InfoRow label="Institución de Egreso" :value="solicitudDetalle.institucion_egreso" />
              <InfoRow label="Año de Graduación" :value="solicitudDetalle.año_graduacion" />
              <InfoRow label="Promedio" :value="solicitudDetalle.promedio_notas ? `${solicitudDetalle.promedio_notas}/20` : '—'" />
              <template v-if="solicitudDetalle.universidad_procedencia">
                <InfoRow label="Universidad Anterior" :value="solicitudDetalle.universidad_procedencia" />
                <InfoRow label="Carrera Previa" :value="solicitudDetalle.carrera_previa" />
                <InfoRow label="Semestres Completados" :value="solicitudDetalle.semestres_completados" />
                <div v-if="solicitudDetalle.desea_convalidar" class="col-span-2 bg-white rounded-lg p-3 flex items-center">
                  <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-sm font-medium text-green-900">Solicita convalidación de materias</span>
                </div>
              </template>
            </div>
          </div>

          <!-- Paso 4: Documentos -->
          <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
            <h3 class="text-sm font-bold text-orange-900 mb-3">📄 Documentos</h3>
            <div class="space-y-2">
              <DocRow label="Cédula de Identidad" :value="solicitudDetalle.doc_cedula" :required="true" />
              <DocRow label="Partida de Nacimiento" :value="solicitudDetalle.doc_partida_nacimiento" :required="true" />
              <DocRow label="Título de Bachiller" :value="solicitudDetalle.doc_titulo_bachiller" :required="true" />
              <DocRow label="Foto tipo Carnet" :value="solicitudDetalle.doc_foto_carnet" :required="true" />
              <DocRow label="Comprobante de Domicilio" :value="solicitudDetalle.doc_comprobante_domicilio" :required="true" />
              <DocRow label="Certificado Médico" :value="solicitudDetalle.doc_certificado_medico" :required="false" />
              <DocRow label="Carta de Buena Conducta" :value="solicitudDetalle.doc_carta_conducta" :required="false" />
            </div>
          </div>

          <!-- Comentarios o rechazos previos -->
          <div v-if="solicitudDetalle.razon_rechazo" class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-red-800 mb-1">❌ Razón de Rechazo:</p>
            <p class="text-sm text-red-700">{{ solicitudDetalle.razon_rechazo }}</p>
          </div>
          <div v-if="solicitudDetalle.comentarios_admin" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-blue-800 mb-1">💬 Comentarios del Administrador:</p>
            <p class="text-sm text-blue-700">{{ solicitudDetalle.comentarios_admin }}</p>
          </div>

          <!-- Acciones -->
          <div v-if="['pendiente', 'en_revision'].includes(solicitudDetalle.estado)" class="flex gap-3 pt-2">
            <button @click="aprobarDetalle" :disabled="procesando"
              class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ procesando ? 'Procesando...' : '✅ Aprobar Solicitud' }}
            </button>
            <button @click="abrirCorreccion(solicitudDetalle)" :disabled="procesando"
              class="flex-1 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              ✏️ Solicitar Corrección
            </button>
            <button @click="abrirRechazo(solicitudDetalle)" :disabled="procesando"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              ❌ Rechazar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal rechazo -->
    <div v-if="rechazoAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">❌ Rechazar Solicitud</h2>
          <button @click="rechazoAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo del rechazo *</label>
            <textarea v-model="motivoRechazo" required rows="4" placeholder="Explica el motivo del rechazo..."
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 text-gray-900 resize-none"></textarea>
          </div>
          <div class="flex gap-3">
            <button @click="rechazoAbierto = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button @click="rechazar" :disabled="procesando || !motivoRechazo.trim()"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ procesando ? 'Procesando...' : 'Rechazar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal corrección -->
    <div v-if="correccionAbierta" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">✏️ Solicitar Corrección</h2>
          <button @click="correccionAbierta = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Comentarios sobre las correcciones necesarias *</label>
            <textarea v-model="comentariosCorreccion" required rows="4" placeholder="Indica qué debe corregir el solicitante..."
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 text-gray-900 resize-none"></textarea>
            <p class="text-xs text-gray-500 mt-2">El solicitante recibirá estos comentarios y podrá editar su solicitud.</p>
          </div>
          <div class="flex gap-3">
            <button @click="correccionAbierta = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button @click="solicitarCorreccion" :disabled="procesando || !comentariosCorreccion.trim()"
              class="flex-1 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ procesando ? 'Procesando...' : 'Enviar' }}
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
const procesando = ref(false)
const solicitudes = ref([])
const detalleAbierto = ref(false)
const rechazoAbierto = ref(false)
const correccionAbierta = ref(false)
const solicitudDetalle = ref(null)
const solicitudRechazo = ref(null)
const solicitudCorreccion = ref(null)
const motivoRechazo = ref('')
const comentariosCorreccion = ref('')
const conteos = ref({ pendiente: 0, en_revision: 0, aprobada: 0, rechazada: 0, requiere_correccion: 0 })
const filtros = reactive({ estatus: 'pendiente' })
const pagination = reactive({ current_page: 1, last_page: 1 })

const tabs = computed(() => [
  { value: 'pendiente', icon: '⏳', label: 'Pendientes', count: conteos.value.pendiente },
  { value: 'en_revision', icon: '📝', label: 'En Revisión', count: conteos.value.en_revision },
  { value: 'aprobada',  icon: '✅', label: 'Aprobadas' },
  { value: 'rechazada', icon: '❌', label: 'Rechazadas' },
  { value: 'requiere_correccion', icon: '✏️', label: 'Requieren Corrección' },
  { value: '',          icon: '📋', label: 'Todas' },
])

const pages = computed(() => {
  const range = []
  for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) range.push(i)
  return range
})

const formatDate = d => d ? new Date(d).toLocaleString('es-VE', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'
const estatusBadge = e => ({
  borrador: 'bg-gray-100 text-gray-700',
  pendiente: 'bg-yellow-100 text-yellow-700', 
  en_revision: 'bg-blue-100 text-blue-700',
  aprobada: 'bg-green-100 text-green-700', 
  rechazada: 'bg-red-100 text-red-700',
  requiere_correccion: 'bg-orange-100 text-orange-700',
}[e] ?? 'bg-gray-100 text-gray-700')

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filtros.estatus) params.estatus = filtros.estatus
    const { data } = await api.get('/admin/solicitudes', { params })
    solicitudes.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page })
    // Cargar conteos
    const allRes = await api.get('/admin/solicitudes', { params: { per_page: 1 } })
    // Solo cargamos el conteo de pendientes para la badge
    const pendRes = await api.get('/admin/solicitudes', { params: { estatus: 'pendiente', per_page: 1 } })
    conteos.value.pendiente = pendRes.data.total ?? 0
  } finally { loading.value = false }
}

async function verDetalle(s) {
  try {
    const { data } = await api.get(`/admin/solicitudes/${s.id}`)
    solicitudDetalle.value = data
    detalleAbierto.value = true
  } catch { toast.error('Error al cargar el detalle') }
}

async function aprobar(s) {
  if (!confirm(`¿Aprobar la solicitud de ${s.solicitante_nombre}?`)) return
  procesando.value = true
  try {
    await api.post(`/admin/solicitudes/${s.id}/aprobar`)
    toast.success('Solicitud aprobada. El estudiante ahora tiene acceso completo.')
    await cargar(pagination.current_page)
  } finally { procesando.value = false }
}

async function aprobarDetalle() {
  if (!solicitudDetalle.value) return
  procesando.value = true
  try {
    await api.post(`/admin/solicitudes/${solicitudDetalle.value.id}/aprobar`)
    toast.success('Solicitud aprobada exitosamente')
    detalleAbierto.value = false
    await cargar(pagination.current_page)
  } finally { procesando.value = false }
}

function abrirRechazo(s) {
  solicitudRechazo.value = s
  motivoRechazo.value = ''
  detalleAbierto.value = false
  rechazoAbierto.value = true
}

async function rechazar() {
  if (!motivoRechazo.value.trim()) return
  procesando.value = true
  try {
    await api.post(`/admin/solicitudes/${solicitudRechazo.value.id}/rechazar`, { razon_rechazo: motivoRechazo.value })
    toast.success('Solicitud rechazada')
    rechazoAbierto.value = false
    detalleAbierto.value = false
    await cargar(pagination.current_page)
  } finally { procesando.value = false }
}

function abrirCorreccion(s) {
  solicitudCorreccion.value = s
  comentariosCorreccion.value = ''
  detalleAbierto.value = false
  correccionAbierta.value = true
}

async function solicitarCorreccion() {
  if (!comentariosCorreccion.value.trim()) return
  procesando.value = true
  try {
    await api.post(`/admin/solicitudes/${solicitudCorreccion.value.id}/solicitar-correccion`, { 
      comentarios: comentariosCorreccion.value 
    })
    toast.success('Solicitud de corrección enviada al estudiante')
    correccionAbierta.value = false
    await cargar(pagination.current_page)
  } finally { procesando.value = false }
}

onMounted(cargar)
</script>

<!-- Sub-componentes locales -->
<script>
const InfoRow = { 
  props: ['label', 'value'], 
  template: `<div class="bg-white rounded-lg p-3"><p class="text-xs text-gray-500">{{ label }}</p><p class="text-sm font-medium text-gray-900 mt-0.5">{{ value || '—' }}</p></div>` 
}
const DocRow = {
  props: ['label', 'value', 'required'],
  template: `
    <div class="flex items-center justify-between py-2 px-3 bg-white rounded-lg">
      <div class="flex items-center">
        <svg v-if="value" class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <svg v-else class="w-5 h-5 text-gray-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm text-gray-900">{{ label }}</span>
        <span v-if="required" class="ml-2 text-xs text-red-500">*</span>
      </div>
      <span v-if="value" class="text-xs text-green-600 font-medium">Cargado</span>
      <span v-else class="text-xs text-gray-400">No cargado</span>
    </div>
  `
}
</script>
