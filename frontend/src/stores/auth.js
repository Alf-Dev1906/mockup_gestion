import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

export const useAuthStore = defineStore('auth', () => {
  const toast = useToast()

  const user = ref(JSON.parse(localStorage.getItem('sge_user') || 'null'))
  const token = ref(localStorage.getItem('sge_token') || null)

  // ─── Getters ──────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userName = computed(() => user.value?.name || 'Usuario')
  const userEmail = computed(() => user.value?.email || '')
  const userRole = computed(() => user.value?.role || 'estudiante')
  const userRoleLevel = computed(() => user.value?.role_level ?? 1)
  const userRoleName = computed(() => user.value?.role_name || '')

  // Datos extras según rol
  const estudianteData = computed(() => user.value?.estudiante || null)
  const profesorData = computed(() => user.value?.profesor || null)

  // Helpers de rol (jerarquía: nivel >= requerido)
  const LEVELS = { estudiante: 1, profesor: 2, administrativo: 3, soporte_it: 4, desarrollador: 5 }

  const isDesarrollador = computed(() => userRole.value === 'desarrollador')
  const isSoporteIT = computed(() => LEVELS[userRole.value] >= 4)
  const isAdministrativo = computed(() => LEVELS[userRole.value] >= 3)
  const isProfesor = computed(() => LEVELS[userRole.value] >= 2)
  const isEstudiante = computed(() => userRole.value === 'estudiante')

  // Para estudiante: sub-estado
  const estudianteEstatus = computed(() => estudianteData.value?.estatus || null)
  const isSolicitante = computed(() => isEstudiante.value && estudianteEstatus.value === 'solicitante')
  const isEstudianteActivo = computed(() => isEstudiante.value && estudianteEstatus.value === 'activo')

  // Ruta de dashboard según rol
  const dashboardRoute = computed(() => {
    switch (userRole.value) {
      case 'desarrollador': return '/dev/dashboard'
      case 'soporte_it': return '/soporte/dashboard'
      case 'administrativo': return '/admin/dashboard'
      case 'profesor': return '/profesor/dashboard'
      case 'estudiante': return isSolicitante.value ? '/estudiante/solicitud' : '/estudiante/dashboard'
      default: return '/login'
    }
  })

  // ─── Acciones ─────────────────────────────────────────────────────────────
  async function login(credentials) {
    const { data } = await api.post('/login', credentials)
    token.value = data.token
    user.value = data.user

    // Guardar en localStorage ANTES de hacer cualquier otra llamada
    localStorage.setItem('sge_token', token.value)
    localStorage.setItem('sge_user', JSON.stringify(user.value))

    // Intentar obtener datos extendidos de /me (si falla, usar los datos del login)
    try {
      const { data: meData } = await api.get('/me')
      user.value = meData
      localStorage.setItem('sge_user', JSON.stringify(user.value))
    } catch (error) {
      // Si /me falla, no pasa nada, ya tenemos los datos básicos del login
      console.warn('/me failed, using basic user data from login', error)
    }

    toast.success(`¡Bienvenido, ${user.value.name}!`)
    return data
  }

  async function logout() {
    try { await api.post('/logout') } catch { /* ignora error de red */ }
    toast.info('Sesión cerrada correctamente')
    _clear()
  }

  async function fetchMe() {
    const { data } = await api.get('/me')
    user.value = data
    localStorage.setItem('sge_user', JSON.stringify(user.value))
    return data
  }

  function _clear() {
    token.value = null
    user.value = null
    localStorage.removeItem('sge_token')
    localStorage.removeItem('sge_user')
  }

  return {
    user, token,
    isAuthenticated, userName, userEmail, userRole, userRoleLevel, userRoleName,
    estudianteData, profesorData, estudianteEstatus,
    isDesarrollador, isSoporteIT, isAdministrativo, isProfesor, isEstudiante,
    isSolicitante, isEstudianteActivo,
    dashboardRoute,
    login, logout, fetchMe,
  }
})
