<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
      <p class="text-gray-500 mt-1">Tus datos personales y académicos</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="n in 2" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse h-64"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Datos del usuario -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="bg-indigo-50 border-b border-indigo-200 px-6 py-4">
            <h2 class="font-bold text-gray-900">👤 Cuenta</h2>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="field in userFields" :key="field.label" class="flex items-start justify-between gap-4">
              <span class="text-sm text-gray-500 w-32 flex-shrink-0">{{ field.label }}</span>
              <span class="text-sm font-medium text-gray-900 text-right">{{ field.value || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Datos académicos -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="bg-indigo-50 border-b border-indigo-200 px-6 py-4">
            <h2 class="font-bold text-gray-900">🎓 Datos Académicos</h2>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="field in estudianteFields" :key="field.label" class="flex items-start justify-between gap-4">
              <span class="text-sm text-gray-500 w-36 flex-shrink-0">{{ field.label }}</span>
              <span class="text-sm font-medium text-right" :class="field.class || 'text-gray-900'">
                {{ field.value || '—' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Contacto -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="bg-indigo-50 border-b border-indigo-200 px-6 py-4">
            <h2 class="font-bold text-gray-900">📞 Contacto</h2>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="field in contactoFields" :key="field.label" class="flex items-start justify-between gap-4">
              <span class="text-sm text-gray-500 w-32 flex-shrink-0">{{ field.label }}</span>
              <span class="text-sm font-medium text-gray-900 text-right">{{ field.value || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Emergencia -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="bg-indigo-50 border-b border-indigo-200 px-6 py-4">
            <h2 class="font-bold text-gray-900">🚨 Contacto de Emergencia</h2>
          </div>
          <div class="p-6 space-y-4">
            <div v-for="field in emergenciaFields" :key="field.label" class="flex items-start justify-between gap-4">
              <span class="text-sm text-gray-500 w-32 flex-shrink-0">{{ field.label }}</span>
              <span class="text-sm font-medium text-gray-900 text-right">{{ field.value || '—' }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const perfil = ref({})

const e = computed(() => perfil.value?.estudiante ?? {})
const u = computed(() => perfil.value?.user ?? {})

const estatusClass = computed(() => ({
  activo: 'text-green-600', solicitante: 'text-yellow-600',
  suspendido: 'text-red-600', egresado: 'text-blue-600',
}[e.value?.estatus] ?? 'text-gray-600'))

const userFields = computed(() => [
  { label: 'Nombre',   value: u.value.name },
  { label: 'Email',    value: u.value.email },
  { label: 'Rol',      value: u.value.role },
])

const estudianteFields = computed(() => [
  { label: 'Matrícula',        value: e.value.matricula },
  { label: 'Cédula',           value: e.value.cedula },
  { label: 'Carrera',          value: e.value.carrera?.nombre },
  { label: 'Facultad',         value: e.value.carrera?.facultad?.nombre },
  { label: 'Semestre',         value: e.value.semestre_actual },
  { label: 'Índice Acad.',     value: e.value.indice_academico },
  { label: 'Estatus',          value: e.value.estatus, class: estatusClass.value + ' font-semibold capitalize' },
  { label: 'Fecha ingreso',    value: e.value.fecha_ingreso },
])

const contactoFields = computed(() => [
  { label: 'Teléfono',    value: e.value.telefono },
  { label: 'Dirección',   value: e.value.direccion },
  { label: 'Ciudad',      value: e.value.ciudad },
  { label: 'Estado',      value: e.value.estado },
  { label: 'Nac.',        value: e.value.fecha_nacimiento },
  { label: 'Género',      value: e.value.genero },
])

const emergenciaFields = computed(() => [
  { label: 'Nombre',    value: e.value.contacto_emergencia_nombre },
  { label: 'Teléfono',  value: e.value.contacto_emergencia_telefono },
  { label: 'Relación',  value: e.value.contacto_emergencia_relacion },
])

onMounted(async () => {
  try { const { data } = await api.get('/estudiante/perfil'); perfil.value = data }
  finally { loading.value = false }
})
</script>
