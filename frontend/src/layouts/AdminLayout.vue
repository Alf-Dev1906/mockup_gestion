<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col shadow-2xl transform transition-transform duration-200"
      :class="[sidebarBg, sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-6 py-5 border-b" :class="borderColor">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" :class="logoBg">
          <span class="font-extrabold text-base" :class="logoText">{{ logoChar }}</span>
        </div>
        <div class="leading-tight">
          <p class="font-bold text-sm text-white">SGE</p>
          <p class="text-xs opacity-60 text-white">{{ roleName }}</p>
        </div>
        <!-- Badge de nivel -->
        <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" :class="levelBadge">
          N{{ roleLevel }}
        </span>
      </div>

      <!-- Nav dinámico según rol -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 scrollbar-none">

        <!-- DESARROLLADOR -->
        <template v-if="auth.isDesarrollador">
          <NavSection label="Principal" />
          <NavItem to="/dev/dashboard"   icon="📊">Dashboard</NavItem>
          <NavSection label="Sistema" />
          <NavItem to="/dev/database"    icon="🗄️">Base de Datos</NavItem>
          <NavItem to="/dev/logs"        icon="📋">Logs del Sistema</NavItem>
          <NavItem to="/dev/backups"     icon="💾">Backups</NavItem>
          <NavItem to="/dev/config"      icon="⚙️">Configuración</NavItem>
          <NavItem to="/dev/statistics"  icon="📈">Estadísticas</NavItem>
          <NavSection label="Gestión" />
          <NavItem to="/soporte/usuarios" icon="👥">Usuarios</NavItem>
          <NavItem to="/admin/solicitudes" icon="📨">Solicitudes</NavItem>
          <NavItem to="/admin/dashboard"  icon="🏛️">Panel Admin</NavItem>
          <NavSection label="Sitio" />
          <NavItem to="/" icon="🌐">Sitio Público</NavItem>
        </template>

        <!-- SOPORTE IT -->
        <template v-else-if="auth.isSoporteIT">
          <NavSection label="Principal" />
          <NavItem to="/soporte/dashboard" icon="📊">Dashboard</NavItem>
          <NavSection label="Usuarios" />
          <NavItem to="/soporte/usuarios"  icon="👥">Gestión de Usuarios</NavItem>
          <NavSection label="Monitoreo" />
          <NavItem to="/soporte/logs"      icon="📋">Logs de Actividad</NavItem>
          <NavItem to="/soporte/sesiones"  icon="🔐">Sesiones Activas</NavItem>
          <NavSection label="Sistema" />
          <NavItem to="/soporte/backups"   icon="💾">Respaldos</NavItem>
          <NavSection label="Sitio" />
          <NavItem to="/" icon="🌐">Sitio Público</NavItem>
        </template>

        <!-- ADMINISTRATIVO -->
        <template v-else-if="auth.isAdministrativo">
          <NavSection label="Principal" />
          <NavItem to="/admin/dashboard"     icon="📊">Dashboard</NavItem>
          <NavSection label="Admisiones" />
          <NavItem to="/admin/solicitudes"   icon="📨">Solicitudes</NavItem>
          <NavItem to="/admin/pagos"         icon="💰">Pagos</NavItem>
          <NavSection label="Académico" />
          <NavItem to="/admin/estudiantes"   icon="👨‍🎓">Estudiantes</NavItem>
          <NavItem to="/admin/profesores"    icon="👨‍🏫">Profesores</NavItem>
          <NavItem to="/admin/materias"      icon="📖">Materias</NavItem>
          <NavItem to="/admin/aulas"         icon="🏫">Aulas</NavItem>
          <NavItem to="/admin/horarios"      icon="🕐">Horarios</NavItem>
          <NavItem to="/admin/inscripciones" icon="📝">Inscripciones</NavItem>
          <NavItem to="/admin/calificaciones" icon="🎓">Calificaciones</NavItem>
          <NavItem to="/admin/carreras"      icon="📚">Carreras</NavItem>
          <NavItem to="/admin/facultades"    icon="🏛️">Facultades</NavItem>
          <NavSection label="Reportes" />
          <NavItem to="/admin/reportes"      icon="📈">Reportes</NavItem>
          <NavSection label="Sitio" />
          <NavItem to="/" icon="🌐">Sitio Público</NavItem>
        </template>

        <!-- PROFESOR -->
        <template v-else-if="auth.isProfesor">
          <NavSection label="Principal" />
          <NavItem to="/profesor/dashboard"      icon="📊">Mi Dashboard</NavItem>
          <NavSection label="Académico" />
          <NavItem to="/profesor/materias"       icon="📖">Mis Materias</NavItem>
          <NavItem to="/profesor/calificaciones" icon="🎓">Calificaciones</NavItem>
          <NavItem to="/profesor/horario"        icon="🕐">Mi Horario</NavItem>
          <NavSection label="Aula Virtual" />
          <NavItem to="/profesor/aula-virtual"   icon="🖥️">Aula Virtual</NavItem>
          <NavItem to="/profesor/quizzes"        icon="📝">Exámenes</NavItem>
          <NavItem to="/profesor/tareas"         icon="📋">Tareas</NavItem>
          <NavItem to="/profesor/asistencia"     icon="✅">Asistencia</NavItem>
          <NavItem to="/profesor/auditoria"      icon="🔍">Auditoría</NavItem>
          <NavSection label="Perfil" />
          <NavItem to="/profesor/perfil"         icon="👤">Mi Perfil</NavItem>
          <NavSection label="Sitio" />
          <NavItem to="/" icon="🌐">Sitio Público</NavItem>
        </template>

        <!-- ESTUDIANTE -->
        <template v-else-if="auth.isEstudiante">
          <!-- Solicitante: acceso muy limitado -->
          <template v-if="auth.isSolicitante">
            <NavSection label="Mi Solicitud" />
            <NavItem to="/estudiante/solicitud" icon="📨">Estado de Solicitud</NavItem>
            <NavItem to="/estudiante/perfil"    icon="👤">Mi Perfil</NavItem>
          </template>
          <!-- Activo: acceso completo -->
          <template v-else>
            <NavSection label="Principal" />
            <NavItem to="/estudiante/dashboard"      icon="📊">Mi Dashboard</NavItem>
            <NavSection label="Académico" />
            <NavItem to="/estudiante/inscripcion"    icon="📝">Inscripción</NavItem>
            <NavItem to="/estudiante/horarios"       icon="🕐">Mis Horarios</NavItem>
            <NavItem to="/estudiante/calificaciones" icon="🎓">Mis Calificaciones</NavItem>
            <NavSection label="Aula Virtual" />
            <NavItem to="/estudiante/aula-virtual"   icon="🖥️">Aula Virtual</NavItem>
            <NavItem to="/estudiante/quizzes"        icon="📝">Mis Exámenes</NavItem>
            <NavItem to="/estudiante/tareas"         icon="📋">Mis Tareas</NavItem>
            <NavItem to="/estudiante/asistencia"     icon="✅">Asistencia</NavItem>
            <NavSection label="Perfil" />
            <NavItem to="/estudiante/perfil"         icon="👤">Mi Perfil</NavItem>
          </template>
          <NavSection label="Sitio" />
          <NavItem to="/" icon="🌐">Sitio Público</NavItem>
        </template>

      </nav>

      <!-- Usuario footer -->
      <div class="px-4 py-4 border-t" :class="borderColor">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0" :class="logoBg + ' ' + logoText">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-white truncate">{{ auth.userName }}</p>
            <p class="text-xs opacity-60 text-white truncate">{{ auth.userRoleName || auth.userRole }}</p>
          </div>
          <button @click="handleLogout" title="Cerrar sesión" class="text-white/50 hover:text-white transition text-lg leading-none">⏻</button>
        </div>
      </div>
    </aside>

    <!-- Overlay móvil -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebarOpen = false" />

    <!-- Contenido principal -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Topbar -->
      <header class="sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center gap-4 px-6 py-3">
          <button class="lg:hidden text-gray-500 hover:text-gray-900 text-xl leading-none" @click="sidebarOpen = !sidebarOpen">☰</button>
          <div class="flex items-center gap-2 text-sm text-gray-400 min-w-0">
            <span>SGE</span>
            <span>/</span>
            <span class="font-medium truncate" :class="breadcrumbColor">{{ routeLabel }}</span>
          </div>
          <div class="flex-1" />
          
          <!-- Campana de notificaciones (solo para profesor y estudiante) -->
          <NotificationBell v-if="mostrarNotificaciones" />
          
          <!-- Badge de rol coloreado -->
          <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full" :class="roleBadge">
            {{ roleIcon }} {{ auth.userRoleName || auth.userRole }}
          </span>
        </div>
      </header>

      <!-- Página -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavItem from '@/components/NavItem.vue'
import NavSection from '@/components/NavSection.vue'
import NotificationBell from '@/components/NotificationBell.vue'

const auth         = useAuthStore()
const router       = useRouter()
const route        = useRoute()
const sidebarOpen  = ref(false)

const initials = computed(() => {
  const name = auth.userName ?? ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

// Mostrar notificaciones solo para profesor y estudiante
const mostrarNotificaciones = computed(() => {
  return auth.isProfesor || auth.isEstudiante
})

// Colores según rol
const roleConfig = computed(() => {
  switch (auth.userRole) {
    case 'desarrollador':  return { bg: 'from-gray-900 to-gray-800',   border: 'border-gray-700',  logo: 'bg-purple-500',  logoTxt: 'text-white', char: '⚡', level: 5, badge: 'bg-purple-500/20 text-purple-300', roleBadge: 'bg-purple-100 text-purple-700', icon: '⚡', name: 'Desarrollador',  crumb: 'text-purple-600' }
    case 'soporte_it':     return { bg: 'from-slate-900 to-slate-800',  border: 'border-slate-700', logo: 'bg-cyan-500',    logoTxt: 'text-white', char: '🔧', level: 4, badge: 'bg-cyan-500/20 text-cyan-300',    roleBadge: 'bg-cyan-100 text-cyan-700',    icon: '🔧', name: 'Soporte IT',     crumb: 'text-cyan-600'   }
    case 'administrativo': return { bg: 'from-blue-950 to-blue-900',    border: 'border-blue-800',  logo: 'bg-yellow-400',  logoTxt: 'text-blue-900', char: 'U', level: 3, badge: 'bg-yellow-400/20 text-yellow-300', roleBadge: 'bg-blue-100 text-blue-700',  icon: '🏛️', name: 'Administrativo', crumb: 'text-blue-600'   }
    case 'profesor':       return { bg: 'from-emerald-950 to-emerald-900', border: 'border-emerald-800', logo: 'bg-emerald-400', logoTxt: 'text-emerald-900', char: '📖', level: 2, badge: 'bg-emerald-400/20 text-emerald-300', roleBadge: 'bg-emerald-100 text-emerald-700', icon: '👨‍🏫', name: 'Profesor', crumb: 'text-emerald-600' }
    default:               return { bg: 'from-indigo-950 to-indigo-900', border: 'border-indigo-800', logo: 'bg-indigo-400', logoTxt: 'text-white', char: '🎓', level: 1, badge: 'bg-indigo-400/20 text-indigo-300', roleBadge: 'bg-indigo-100 text-indigo-700', icon: '👨‍🎓', name: 'Estudiante', crumb: 'text-indigo-600' }
  }
})

const sidebarBg      = computed(() => `bg-gradient-to-b ${roleConfig.value.bg}`)
const borderColor    = computed(() => roleConfig.value.border)
const logoBg         = computed(() => roleConfig.value.logo)
const logoText       = computed(() => roleConfig.value.logoTxt)
const logoChar       = computed(() => roleConfig.value.char)
const levelBadge     = computed(() => roleConfig.value.badge)
const roleBadge      = computed(() => roleConfig.value.roleBadge)
const roleIcon       = computed(() => roleConfig.value.icon)
const roleName       = computed(() => roleConfig.value.name)
const roleLevel      = computed(() => roleConfig.value.level)
const breadcrumbColor = computed(() => roleConfig.value.crumb)

// Etiqueta del breadcrumb
const routeLabels = {
  '/dev/dashboard': 'Dashboard', '/dev/database': 'Base de Datos', '/dev/logs': 'Logs',
  '/dev/backups': 'Backups', '/dev/config': 'Configuración', '/dev/statistics': 'Estadísticas',
  '/soporte/dashboard': 'Dashboard', '/soporte/usuarios': 'Usuarios', '/soporte/logs': 'Logs',
  '/soporte/sesiones': 'Sesiones Activas', '/soporte/backups': 'Respaldos',
  '/admin/dashboard': 'Dashboard', '/admin/solicitudes': 'Solicitudes', '/admin/pagos': 'Pagos',
  '/admin/reportes': 'Reportes', '/admin/estudiantes': 'Estudiantes', '/admin/profesores': 'Profesores',
  '/admin/materias': 'Materias', '/admin/aulas': 'Aulas', '/admin/horarios': 'Horarios',
  '/admin/inscripciones': 'Inscripciones', '/admin/calificaciones': 'Calificaciones',
  '/admin/carreras': 'Carreras', '/admin/facultades': 'Facultades',
  '/profesor/dashboard': 'Mi Dashboard', '/profesor/materias': 'Mis Materias',
  '/profesor/calificaciones': 'Calificaciones', '/profesor/horario': 'Mi Horario',
  '/profesor/perfil': 'Mi Perfil',
  '/estudiante/dashboard': 'Mi Dashboard', '/estudiante/horarios': 'Mis Horarios',
  '/estudiante/calificaciones': 'Mis Calificaciones', '/estudiante/inscripcion': 'Inscripción',
  '/estudiante/solicitud': 'Estado de Solicitud', '/estudiante/perfil': 'Mi Perfil',
}
const routeLabel = computed(() => routeLabels[route.path] ?? 'Panel')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<!-- Componente local NavSection para separadores de grupos -->
<script>
export default { name: 'AdminLayout' }
</script>
