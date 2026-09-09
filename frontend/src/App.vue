<template>
  <!-- ToastContainer global -->
  <ToastContainer />

  <!-- Las rutas de admin tienen su propio layout (AdminLayout).
       Las rutas públicas usan este wrapper con navbar + footer. -->
  <div v-if="isAdminRoute" id="app">
    <router-view />
  </div>

  <div v-else id="app" class="min-h-screen flex flex-col">

    <!-- Navbar público -->
    <nav class="bg-gradient-to-r from-blue-950 to-blue-800 text-white shadow-xl">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

          <!-- Logo -->
          <router-link to="/" class="flex items-center gap-3">
            <div class="w-9 h-9 bg-yellow-400 rounded-xl flex items-center justify-center">
              <span class="text-blue-900 font-extrabold text-sm">U</span>
            </div>
            <div class="hidden sm:block">
              <p class="font-bold text-sm leading-tight">Universidad de Caracas</p>
              <p class="text-blue-300 text-xs">Excelencia Académica</p>
            </div>
          </router-link>

          <!-- Links -->
          <div class="hidden md:flex items-center gap-6 text-sm">
            <router-link to="/"         class="hover:text-yellow-300 transition font-medium">Inicio</router-link>
            <router-link to="/carreras" class="hover:text-yellow-300 transition font-medium">Carreras</router-link>
            <router-link to="/noticias" class="hover:text-yellow-300 transition font-medium">Noticias</router-link>
            <router-link to="/contacto" class="hover:text-yellow-300 transition font-medium">Contacto</router-link>
          </div>

          <!-- CTA: Aula Virtual o ir al dashboard si ya está logueado -->
          <router-link
            :to="auth.isAuthenticated ? '/dashboard' : '/login'"
            class="bg-yellow-400 hover:bg-yellow-300 text-blue-900 px-5 py-2 rounded-xl font-bold text-sm transition shadow-md"
          >
            {{ auth.isAuthenticated ? '⚡ Dashboard' : '🔑 Aula Virtual' }}
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Contenido de la página pública -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-10">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-sm">
          <div>
            <h3 class="font-bold mb-3">Universidad de Caracas</h3>
            <p class="text-gray-400">Formando líderes del futuro con excelencia académica y compromiso social.</p>
          </div>
          <div>
            <h4 class="font-semibold mb-3">Acceso Rápido</h4>
            <ul class="space-y-1.5 text-gray-400">
              <li><router-link to="/carreras" class="hover:text-white transition">Carreras</router-link></li>
              <li><router-link to="/noticias" class="hover:text-white transition">Noticias</router-link></li>
              <li><router-link to="/contacto" class="hover:text-white transition">Contacto</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-3">Contacto</h4>
            <ul class="space-y-1.5 text-gray-400">
              <li>📞 +58 212-555-1000</li>
              <li>📧 info@universidad.edu.ve</li>
              <li>📍 Caracas, Venezuela</li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 text-xs">
          © 2026 Universidad de Gestión Estudiantil
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ToastContainer from '@/components/ToastContainer.vue'

const route = useRoute()
const auth  = useAuthStore()

// Todas las rutas de panel (cualquier rol) usan su propio layout
const PANEL_PREFIXES = ['/dashboard', '/admin', '/dev', '/soporte', '/profesor', '/estudiante']
const isAdminRoute = computed(() =>
  PANEL_PREFIXES.some(p => route.path.startsWith(p))
)
</script>
