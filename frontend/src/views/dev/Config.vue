<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Configuración del Sistema</h1>
        <p class="text-gray-500 mt-1">Variables de entorno y configuraciones críticas (solo lectura)</p>
      </div>
      <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-2 rounded-full">🔒 SOLO LECTURA</span>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse">
        <div class="h-5 bg-gray-100 rounded w-1/3 mb-4"></div>
        <div class="space-y-3">
          <div v-for="m in 4" :key="m" class="h-4 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- App -->
      <ConfigSection title="🎩 Aplicación" :items="[
        { key: 'APP_NAME',     value: config.app?.name },
        { key: 'APP_ENV',      value: config.app?.env,   badge: envBadge(config.app?.env) },
        { key: 'APP_DEBUG',    value: String(config.app?.debug), badge: config.app?.debug ? 'red' : 'green' },
        { key: 'APP_URL',      value: config.app?.url },
        { key: 'APP_TIMEZONE', value: config.app?.timezone },
        { key: 'APP_LOCALE',   value: config.app?.locale },
      ]" />

      <!-- Base de Datos -->
      <ConfigSection title="🗄️ Base de Datos" :items="[
        { key: 'DB_CONNECTION', value: config.database?.driver },
        { key: 'DB_HOST',       value: config.database?.host },
        { key: 'DB_PORT',       value: config.database?.port },
        { key: 'DB_DATABASE',   value: config.database?.database },
      ]" />

      <!-- Mail -->
      <ConfigSection title="📧 Correo" :items="[
        { key: 'MAIL_MAILER', value: config.mail?.driver },
        { key: 'MAIL_FROM',   value: config.mail?.from?.address },
        { key: 'MAIL_NAME',   value: config.mail?.from?.name },
      ]" />

      <!-- Sanctum -->
      <ConfigSection title="🔐 Autenticación (Sanctum)" :items="[
        { key: 'SANCTUM_EXPIRATION', value: config.sanctum?.expiration ? config.sanctum.expiration + ' min' : 'Sin expiración' },
      ]" />
    </div>

    <!-- Artisan commands -->
    <div class="mt-8">
      <h2 class="text-lg font-bold text-gray-900 mb-4">⚙️ Comandos del Sistema</h2>
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <button v-for="cmd in commands" :key="cmd.command" @click="ejecutarComando(cmd)"
            :disabled="ejecutando === cmd.command"
            class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition text-left disabled:opacity-50">
            <span class="text-2xl">{{ cmd.icon }}</span>
            <div>
              <p class="font-semibold text-sm text-gray-900">{{ cmd.label }}</p>
              <p class="text-xs text-gray-500 font-mono">{{ cmd.command }}</p>
            </div>
            <span v-if="ejecutando === cmd.command" class="ml-auto text-purple-500 text-xs">⏳</span>
          </button>
        </div>
        <!-- Output -->
        <div v-if="commandOutput" class="mt-4 bg-gray-950 rounded-xl p-4">
          <pre class="text-green-400 text-xs font-mono whitespace-pre-wrap">{{ commandOutput }}</pre>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const config = ref({})
const ejecutando = ref(null)
const commandOutput = ref('')

const commands = [
  { icon: '🧹', label: 'Limpiar Caché',    command: 'cache:clear'  },
  { icon: '⚙️',  label: 'Limpiar Config',  command: 'config:clear' },
  { icon: '🛣️',  label: 'Limpiar Rutas',   command: 'route:clear'  },
  { icon: '🎨', label: 'Limpiar Vistas',   command: 'view:clear'   },
  { icon: '🚀', label: 'Optimizar Todo',   command: 'optimize'     },
  { icon: '♻️',  label: 'Limpiar Todo',    command: 'optimize:clear' },
]

const envBadge = env => env === 'production' ? 'red' : env === 'staging' ? 'yellow' : 'green'

onMounted(async () => {
  try {
    const { data } = await api.get('/dev/config')
    config.value = data
  } finally {
    loading.value = false
  }
})

async function ejecutarComando(cmd) {
  ejecutando.value = cmd.command
  commandOutput.value = ''
  try {
    const { data } = await api.post('/dev/artisan', { command: cmd.command })
    commandOutput.value = data.output || '✅ Ejecutado sin salida'
    toast.success(`${cmd.label} ejecutado correctamente`)
  } catch (e) {
    commandOutput.value = '❌ ' + (e.response?.data?.error || 'Error al ejecutar comando')
    toast.error('Error al ejecutar el comando')
  } finally {
    ejecutando.value = null
  }
}
</script>
