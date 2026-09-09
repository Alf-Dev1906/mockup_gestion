<template>
  <AdminLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Facultades</h1>
      <p class="text-gray-500 text-sm mt-0.5">{{ rows.length }} facultades activas</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="n in 6" :key="n" class="h-40 bg-gray-100 rounded-2xl animate-pulse" />
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="f in rows" :key="f.id"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold text-lg flex-shrink-0">
            {{ f.codigo }}
          </div>
          <div class="min-w-0">
            <h3 class="font-semibold text-gray-900 leading-snug truncate">{{ f.nombre }}</h3>
            <p class="text-xs text-gray-600 mt-0.5 truncate">{{ f.decano ?? 'Sin decano asignado' }}</p>
          </div>
        </div>
        <div class="space-y-1 text-sm text-gray-500">
          <p v-if="f.email" class="truncate">📧 {{ f.email }}</p>
          <p v-if="f.telefono">📞 {{ f.telefono }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between text-xs text-gray-600">
          <span :class="f.activo ? 'text-green-600' : 'text-red-500'" class="font-medium">
            {{ f.activo ? '● Activa' : '○ Inactiva' }}
          </span>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(false)
const rows    = ref([])

async function cargar() {
  loading.value = true
  try {
    const { data } = await api.get('/facultades')
    rows.value = Array.isArray(data) ? data : (data.data ?? [])
  } finally { loading.value = false }
}

onMounted(() => cargar())
</script>



