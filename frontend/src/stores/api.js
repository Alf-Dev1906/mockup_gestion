import { defineStore } from 'pinia'
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  // withCredentials en false para Bearer tokens
  withCredentials: false
})

export const useApiStore = defineStore('api', {
  state: () => ({
    loading: false,
    error: null,
    user: null,
    token: null
  }),

  actions: {
    async login(credentials) {
      this.loading = true
      try {
        const response = await api.post('/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        return response.data
      } catch (error) {
        this.error = error.response?.data?.error || 'Error de autenticación'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await api.post('/logout')
        this.token = null
        this.user = null
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async getMe() {
      this.loading = true
      try {
        const response = await api.get('/me')
        this.user = response.data
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async getEstudiantes() {
      this.loading = true
      try {
        const response = await api.get('/estudiantes')
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async getCarreras() {
      this.loading = true
      try {
        const response = await api.get('/carreras')
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async getHorarios() {
      this.loading = true
      try {
        const response = await api.get('/horarios')
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})