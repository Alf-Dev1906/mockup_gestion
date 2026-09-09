<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Mis Inscripciones</h1>
      <p class="text-gray-500 mt-1">Materias en las que estás inscrito</p>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse h-20"></div>
    </div>

    <div v-else-if="!inscripciones.length" class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
      <p class="text-5xl mb-4">📝</p>
      <p class="font-semibold">No tienes inscripciones</p>
      <p class="text-sm mt-1">Contacta a administración para inscribir materias</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="ins in inscripciones" :key="ins.id"
        class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
          <span class="text-xl">📖</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded">
              {{ ins.materia?.codigo }}
            </span>
            <p class="font-semibold text-gray-900">{{ ins.materia?.nombre }}</p>
          </div>
          <p class="text-xs text-gray-500 mt-1">{{ ins.materia?.creditos }} créditos · Semestre {{ ins.materia?.semestre }}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0"
          :class="ins.estatus === 'inscrito' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
          {{ ins.estatus }}
        </span>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const inscripciones = ref([])

onMounted(async () => {
  try { const { data } = await api.get('/estudiante/inscripciones'); inscripciones.value = data }
  catch { inscripciones.value = [] }
  finally { loading.value = false }
})
</script>
