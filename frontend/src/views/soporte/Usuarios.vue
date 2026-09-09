<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h1>
        <p class="text-gray-500 mt-1">Crear, editar, cambiar roles y resetear contraseñas</p>
      </div>
      <button @click="abrirModal()"
        class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold px-5 py-2.5 rounded-xl transition flex items-center gap-2">
        ➕ Nuevo Usuario
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-4">
      <input v-model="filtros.search" @input="cargar(1)" type="search" placeholder="Buscar por nombre o email..."
        class="flex-1 min-w-[200px] py-2.5 px-4 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
      <select v-model="filtros.role" @change="cargar(1)"
        class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500">
        <option value="">Todos los roles</option>
        <option value="estudiante">Estudiante</option>
        <option value="profesor">Profesor</option>
        <option value="administrativo">Administrativo</option>
        <option value="soporte_it">Soporte IT</option>
      </select>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Registro</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="loading" class="divide-y divide-gray-50">
            <tr v-for="n in 8" :key="n">
              <td v-for="m in 4" :key="m" class="px-6 py-4"><div class="h-4 bg-gray-100 rounded animate-pulse"></div></td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-gray-50">
            <tr v-if="!usuarios.length">
              <td colspan="4" class="px-6 py-16 text-center text-gray-400">
                <p class="text-4xl mb-2">👥</p><p>No se encontraron usuarios</p>
              </td>
            </tr>
            <tr v-for="u in usuarios" :key="u.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 font-bold text-sm flex-shrink-0">
                    {{ initials(u.name) }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ u.name }}</p>
                    <p class="text-xs text-gray-500">{{ u.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="roleBadge(u.role)">
                  {{ u.role }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(u.created_at) }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1">
                  <button @click="abrirModal(u)" title="Editar" class="p-2 text-gray-500 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition">✏️</button>
                  <button @click="abrirCambioRol(u)" title="Cambiar rol" class="p-2 text-gray-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition">🎭</button>
                  <button @click="abrirResetPassword(u)" title="Resetear contraseña" class="p-2 text-gray-500 hover:text-orange-700 hover:bg-orange-50 rounded-lg transition">🔑</button>
                  <button @click="eliminar(u)" title="Eliminar" class="p-2 text-gray-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Paginación -->
      <div v-if="pagination.last_page > 1" class="border-t border-gray-100 px-6 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Página {{ pagination.current_page }} de {{ pagination.last_page }}</p>
        <div class="flex gap-2">
          <button v-for="p in pages" :key="p" @click="cargar(p)"
            :class="p === pagination.current_page ? 'bg-cyan-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-3 py-1.5 rounded-lg text-sm font-semibold transition">{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- Modal crear/editar usuario -->
    <div v-if="modalAbierto" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">{{ form.id ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
          <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <form @submit.prevent="guardar" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre completo *</label>
            <input v-model="form.name" required type="text"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-gray-900" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
            <input v-model="form.email" required type="email"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-gray-900" />
          </div>
          <div v-if="!form.id">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña *</label>
            <input v-model="form.password" required type="password" minlength="8"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-gray-900" />
          </div>
          <div v-if="!form.id">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Rol *</label>
            <select v-model="form.role" required
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-cyan-500 text-gray-900">
              <option value="estudiante">Estudiante</option>
              <option value="profesor">Profesor</option>
              <option value="administrativo">Administrativo</option>
              <option value="soporte_it">Soporte IT</option>
            </select>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modalAbierto = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button type="submit" :disabled="guardando" class="flex-1 bg-cyan-600 hover:bg-cyan-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ guardando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal cambiar rol -->
    <div v-if="modalRol" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">🎭 Cambiar Rol</h2>
          <button @click="modalRol = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Usuario: <strong>{{ usuarioSeleccionado?.name }}</strong></p>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nuevo Rol</label>
            <select v-model="nuevoRol"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 text-gray-900">
              <option value="estudiante">Estudiante</option>
              <option value="profesor">Profesor</option>
              <option value="administrativo">Administrativo</option>
              <option value="soporte_it">Soporte IT</option>
            </select>
          </div>
          <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-800">
            ⚠️ Este cambio se registrará en el log de seguridad.
          </div>
          <div class="flex gap-3">
            <button @click="modalRol = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button @click="cambiarRol" :disabled="guardando" class="flex-1 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ guardando ? 'Guardando...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal reset password -->
    <div v-if="modalPassword" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">🔑 Resetear Contraseña</h2>
          <button @click="modalPassword = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Usuario: <strong>{{ usuarioSeleccionado?.name }}</strong></p>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña *</label>
            <input v-model="nuevaPassword" type="password" minlength="8" required
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 text-gray-900" />
          </div>
          <div class="flex gap-3">
            <button @click="modalPassword = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold transition">Cancelar</button>
            <button @click="resetearPassword" :disabled="guardando" class="flex-1 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition">
              {{ guardando ? 'Guardando...' : 'Resetear' }}
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
const modalRol = ref(false)
const modalPassword = ref(false)
const usuarios = ref([])
const usuarioSeleccionado = ref(null)
const nuevoRol = ref('estudiante')
const nuevaPassword = ref('')

const filtros = reactive({ search: '', role: '' })
const pagination = reactive({ current_page: 1, last_page: 1 })
const form = reactive({ id: null, name: '', email: '', password: '', role: 'estudiante' })

const pages = computed(() => {
  const range = []
  for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) range.push(i)
  return range
})

const initials = name => name?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?'
const formatDate = d => d ? new Date(d).toLocaleDateString('es-VE') : '—'
const roleBadge = role => ({
  desarrollador:  'bg-purple-100 text-purple-700',
  soporte_it:     'bg-cyan-100 text-cyan-700',
  administrativo: 'bg-blue-100 text-blue-700',
  profesor:       'bg-emerald-100 text-emerald-700',
  estudiante:     'bg-indigo-100 text-indigo-700',
}[role] ?? 'bg-gray-100 text-gray-700')

async function cargar(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filtros.search) params.search = filtros.search
    if (filtros.role) params.role = filtros.role
    const { data } = await api.get('/soporte/usuarios', { params })
    usuarios.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page })
  } finally { loading.value = false }
}

function abrirModal(u = null) {
  if (u) Object.assign(form, { id: u.id, name: u.name, email: u.email, password: '', role: u.role })
  else Object.assign(form, { id: null, name: '', email: '', password: '', role: 'estudiante' })
  modalAbierto.value = true
}

function abrirCambioRol(u) {
  usuarioSeleccionado.value = u
  nuevoRol.value = u.role
  modalRol.value = true
}

function abrirResetPassword(u) {
  usuarioSeleccionado.value = u
  nuevaPassword.value = ''
  modalPassword.value = true
}

async function guardar() {
  guardando.value = true
  try {
    if (form.id) await api.put(`/soporte/usuarios/${form.id}`, { name: form.name, email: form.email })
    else await api.post('/soporte/usuarios', form)
    toast.success(form.id ? 'Usuario actualizado' : 'Usuario creado')
    modalAbierto.value = false
    await cargar(pagination.current_page)
  } finally { guardando.value = false }
}

async function cambiarRol() {
  guardando.value = true
  try {
    await api.post(`/soporte/usuarios/${usuarioSeleccionado.value.id}/cambiar-rol`, { role: nuevoRol.value })
    toast.success('Rol actualizado correctamente')
    modalRol.value = false
    await cargar(pagination.current_page)
  } finally { guardando.value = false }
}

async function resetearPassword() {
  if (!nuevaPassword.value || nuevaPassword.value.length < 8) return toast.error('La contraseña debe tener mínimo 8 caracteres')
  guardando.value = true
  try {
    await api.post(`/soporte/usuarios/${usuarioSeleccionado.value.id}/reset-password`, { password: nuevaPassword.value })
    toast.success('Contraseña actualizada correctamente')
    modalPassword.value = false
  } finally { guardando.value = false }
}

async function eliminar(u) {
  if (!confirm(`¿Eliminar a ${u.name}? Esta acción no se puede deshacer.`)) return
  await api.delete(`/soporte/usuarios/${u.id}`)
  toast.success('Usuario eliminado')
  await cargar(pagination.current_page)
}

onMounted(cargar)
</script>
