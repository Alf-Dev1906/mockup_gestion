import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const LEVELS = { estudiante: 1, profesor: 2, administrativo: 3, soporte_it: 4, desarrollador: 5 }

const routes = [
  // ── Rutas públicas ───────────────────────────────────────────────────────
  { path: '/', name: 'Home', component: () => import('@/views/Home.vue') },
  { path: '/login', name: 'Login', component: () => import('@/views/Login.vue'), meta: { guestOnly: true } },
  { path: '/registro', name: 'Registro', component: () => import('@/views/Registro.vue'), meta: { guestOnly: true } },
  { path: '/carreras', name: 'Carreras', component: () => import('@/views/Carreras.vue') },
  { path: '/noticias', name: 'Noticias', component: () => import('@/views/Noticias.vue') },
  { path: '/contacto', name: 'Contacto', component: () => import('@/views/Contacto.vue') },

  // ── Notificaciones (ruta protegida compartida) ────────────────────────
  { path: '/notificaciones', name: 'Notificaciones', component: () => import('@/views/Notificaciones.vue'), meta: { requiresAuth: true } },

  // ── Redirect inteligente desde /dashboard ────────────────────────────────
  {
    path: '/dashboard',
    name: 'Dashboard',
    redirect: () => {
      const auth = useAuthStore()
      return auth.isAuthenticated ? auth.dashboardRoute : '/login'
    },
  },

  // ────────────────────────────────────────────────────────────────────────
  // NIVEL 5 - DESARROLLADOR  (/dev/*)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/dev',
    meta: { requiresAuth: true, minRole: 'desarrollador' },
    children: [
      { path: 'dashboard', name: 'DevDashboard', component: () => import('@/views/dev/Dashboard.vue') },
      { path: 'database', name: 'DevDatabase', component: () => import('@/views/dev/Database.vue') },
      { path: 'logs', name: 'DevLogs', component: () => import('@/views/dev/Logs.vue') },
      { path: 'backups', name: 'DevBackups', component: () => import('@/views/dev/Backups.vue') },
      { path: 'config', name: 'DevConfig', component: () => import('@/views/dev/Config.vue') },
      { path: 'statistics', name: 'DevStatistics', component: () => import('@/views/dev/Statistics.vue') },
    ],
  },

  // ────────────────────────────────────────────────────────────────────────
  // NIVEL 4 - SOPORTE IT  (/soporte/*)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/soporte',
    meta: { requiresAuth: true, minRole: 'soporte_it' },
    children: [
      { path: 'dashboard', name: 'SoporteDashboard', component: () => import('@/views/soporte/Dashboard.vue') },
      { path: 'usuarios', name: 'SoporteUsuarios', component: () => import('@/views/soporte/Usuarios.vue') },
      { path: 'logs', name: 'SoporteLogs', component: () => import('@/views/soporte/Logs.vue') },
      { path: 'sesiones', name: 'SoporteSesiones', component: () => import('@/views/soporte/Sesiones.vue') },
      { path: 'backups', name: 'SoporteBackups', component: () => import('@/views/soporte/Backups.vue') },
    ],
  },

  // ────────────────────────────────────────────────────────────────────────
  // NIVEL 3 - ADMINISTRATIVO  (/admin/*)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/admin',
    meta: { requiresAuth: true, minRole: 'administrativo' },
    children: [
      { path: 'dashboard', name: 'AdminDashboard', component: () => import('@/views/admin/Dashboard.vue') },
      { path: 'solicitudes', name: 'AdminSolicitudes', component: () => import('@/views/admin/Solicitudes.vue') },
      { path: 'pagos', name: 'AdminPagos', component: () => import('@/views/admin/Pagos.vue') },
      { path: 'reportes', name: 'AdminReportes', component: () => import('@/views/admin/Reportes.vue') },
      { path: 'estudiantes', name: 'AdminEstudiantes', component: () => import('@/views/admin/Estudiantes.vue') },
      { path: 'profesores', name: 'AdminProfesores', component: () => import('@/views/admin/Profesores.vue') },
      { path: 'materias', name: 'AdminMaterias', component: () => import('@/views/admin/Materias.vue') },
      { path: 'aulas', name: 'AdminAulas', component: () => import('@/views/admin/Aulas.vue') },
      { path: 'horarios', name: 'AdminHorarios', component: () => import('@/views/admin/Horarios.vue') },
      { path: 'inscripciones', name: 'AdminInscripciones', component: () => import('@/views/admin/Inscripciones.vue') },
      { path: 'calificaciones', name: 'AdminCalificaciones', component: () => import('@/views/admin/Calificaciones.vue') },
      { path: 'carreras', name: 'AdminCarreras', component: () => import('@/views/admin/Carreras.vue') },
      { path: 'facultades', name: 'AdminFacultades', component: () => import('@/views/admin/Facultades.vue') },
    ],
  },

  // ────────────────────────────────────────────────────────────────────────
  // NIVEL 2 - PROFESOR  (/profesor/*)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/profesor',
    meta: { requiresAuth: true, minRole: 'profesor' },
    children: [
      { path: 'dashboard', name: 'ProfesorDashboard', component: () => import('@/views/profesor/Dashboard.vue') },
      { path: 'materias', name: 'ProfesorMaterias', component: () => import('@/views/profesor/Materias.vue') },
      { path: 'calificaciones', name: 'ProfesorCalificaciones', component: () => import('@/views/profesor/Calificaciones.vue') },
      { path: 'horario', name: 'ProfesorHorario', component: () => import('@/views/profesor/Horario.vue') },
      { path: 'perfil', name: 'ProfesorPerfil', component: () => import('@/views/profesor/Perfil.vue') },

      // ── Aula Virtual ─────────────────────────────────────────────────
      { path: 'aula-virtual', name: 'ProfesorAulaVirtual', component: () => import('@/views/profesor/AulaVirtualHub.vue') },
      { path: 'quizzes', name: 'ProfesorQuizzes', component: () => import('@/views/profesor/QuizLista.vue') },
      { path: 'quizzes/nuevo', name: 'ProfesorQuizNuevo', component: () => import('@/views/profesor/QuizCreador.vue') },
      { path: 'quizzes/:id/editar', name: 'ProfesorQuizEditar', component: () => import('@/views/profesor/QuizCreador.vue') },
      { path: 'quizzes/:id/resultados', name: 'ProfesorQuizResultados', component: () => import('@/views/profesor/QuizResultados.vue') },

      // ── Tareas ──────────────────────────────────────────────────────
      { path: 'tareas', name: 'ProfesorTareas', component: () => import('@/views/profesor/Tareas.vue') },

      // ── Asistencia ───────────────────────────────────────────────────
      { path: 'asistencia', name: 'ProfesorAsistenciaSelector', component: () => import('@/views/profesor/AsistenciaSelector.vue') },
      { path: 'asistencia/:horarioId', name: 'ProfesorAsistencia', component: () => import('@/views/profesor/AsistenciaPanel.vue') },

      // ── Auditoría Docente ───────────────────────────────────────────
      { path: 'auditoria', name: 'ProfesorAuditoria', component: () => import('@/views/profesor/AuditoriaIndex.vue') },
    ],
  },

  // ────────────────────────────────────────────────────────────────────────
  // NIVEL 1 - ESTUDIANTE  (/estudiante/*)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/estudiante',
    meta: { requiresAuth: true, minRole: 'estudiante' },
    children: [
      { path: 'dashboard', name: 'EstudianteDashboard', component: () => import('@/views/estudiante/Dashboard.vue') },
      { path: 'admision', name: 'EstudianteAdmision', component: () => import('@/views/estudiante/Admision.vue') },
      { path: 'horarios', name: 'EstudianteHorarios', component: () => import('@/views/estudiante/Horarios.vue') },
      { path: 'calificaciones', name: 'EstudianteCalificaciones', component: () => import('@/views/estudiante/Calificaciones.vue') },
      { path: 'inscripcion', name: 'EstudianteInscripcion', component: () => import('@/views/estudiante/Inscripcion.vue') },
      { path: 'solicitud', name: 'EstudianteSolicitud', component: () => import('@/views/estudiante/Solicitud.vue') },
      { path: 'perfil', name: 'EstudiantePerfil', component: () => import('@/views/estudiante/Perfil.vue') },

      // ── Aula Virtual ─────────────────────────────────────────────────
      { path: 'aula-virtual', name: 'EstudianteAulaVirtual', component: () => import('@/views/estudiante/AulaVirtualHub.vue') },
      { path: 'quizzes', name: 'EstudianteQuizzes', component: () => import('@/views/estudiante/QuizLista.vue') },
      { path: 'quizzes/:id/resultado', name: 'EstudianteQuizResultado', component: () => import('@/views/estudiante/QuizResultado.vue') },

      // ── Tareas ──────────────────────────────────────────────────────
      { path: 'tareas', name: 'EstudianteTareas', component: () => import('@/views/estudiante/Tareas.vue') },

      // ── Asistencia ───────────────────────────────────────────────────
      { path: 'asistencia', name: 'EstudianteAsistencia', component: () => import('@/views/estudiante/AsistenciaMarcar.vue') },
    ],
  },

  // ────────────────────────────────────────────────────────────────────────
  // RUTA ESPECIAL - EXAMEN (PANTALLA COMPLETA SIN LAYOUT)
  // ────────────────────────────────────────────────────────────────────────
  {
    path: '/estudiante/quizzes/:id/tomar',
    name: 'EstudianteQuizTomar',
    component: () => import('@/views/estudiante/QuizExamen.vue'),
    meta: { requiresAuth: true, minRole: 'estudiante', layout: 'clean' }
  },

  // ── Catch-all ────────────────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// ── Guard global ─────────────────────────────────────────────────────────────
router.beforeEach((to, _from, next) => {
  const auth = useAuthStore()

  // Ruta de solo invitados
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return next(auth.dashboardRoute)
  }

  // Ruta que requiere autenticación
  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return next({ name: 'Login', query: { redirect: to.fullPath } })
    }

    // Verificar nivel mínimo de rol
    const minRole = to.meta.minRole
    if (minRole) {
      const userLevel = LEVELS[auth.userRole] ?? 0
      const required = LEVELS[minRole] ?? 999
      if (userLevel < required) {
        // Redirigir al dashboard propio, no mostrar 403 en URL
        return next(auth.dashboardRoute)
      }
    }

    // Bloquear rutas de estudiante activo a solicitante
    const isEstudiantePath = to.path.startsWith('/estudiante/')
    if (isEstudiantePath && auth.isSolicitante) {
      const allowed = ['/estudiante/solicitud', '/estudiante/perfil']
      if (!allowed.includes(to.path)) {
        return next({ name: 'EstudianteSolicitud' })
      }
    }
  }

  next()
})

export default router
