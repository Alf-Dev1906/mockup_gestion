# 🚀 Guía de Deployment - mockup_gestion

Sistema de Gestión Universitaria (SGE) - Deployment en producción

---

## 📋 Estructura del Proyecto

```
mockup_gestion/
├── backend/          # Laravel API
├── frontend/         # Vue.js aplicación
├── render.yaml       # Configuración Render
├── netlify.toml      # Configuración Netlify
└── README.md
```

---

## 🌐 URLs de Deployment

### Backend (Render)
```
https://mockup-gestion-backend.onrender.com
```

### Frontend (Netlify)
```
https://mockup-gestion-frontend.netlify.app
```

---

## 🔧 Deployment Backend en Render.com

### Paso 1: Crear servicio en Render

1. Ir a https://render.com
2. Click en "New" → "Web Service"
3. Conectar con GitHub
4. Seleccionar repositorio `Alf-Dev1906/mockup_gestion`
5. Configurar:
   - **Name:** `mockup-gestion-backend`
   - **Runtime:** `PHP`
   - **Build Command:** `composer install && php artisan key:generate && php artisan migrate:fresh --seed`
   - **Start Command:** `php -S 0.0.0.0:10000 -t public`

### Paso 2: Agregar base de datos

1. En Render, crear "MySQL Database"
2. Nombre: `mockup-gestion-mysql`
3. Database name: `sge_db`

### Paso 3: Variables de entorno

En Render, agregar estas variables:

```
APP_NAME=SGE
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mockup-gestion-backend.onrender.com
DB_CONNECTION=mysql
LOG_CHANNEL=single
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Las variables de BD se conectan automáticamente desde MySQL.

---

## 🎨 Deployment Frontend en Netlify

### Paso 1: Conectar repositorio

1. Ir a https://netlify.com
2. Click en "Add new site" → "Import an existing project"
3. Conectar GitHub
4. Seleccionar `Alf-Dev1906/mockup_gestion`

### Paso 2: Configurar build

1. **Base directory:** `frontend`
2. **Build command:** `npm run build`
3. **Publish directory:** `frontend/dist`

### Paso 3: Variables de entorno

En Netlify, agregar:

```
VITE_API_BASE_URL=https://mockup-gestion-backend.onrender.com/api
```

### Paso 4: Deploy

Click en "Deploy"

---

## 👥 Credenciales de Prueba

```
Email: admin@universidad.edu.ve
Password: Admin2026!

Roles disponibles:
- admin@universidad.edu.ve (Admin)
- profesor@universidad.edu.ve (Profesor)
- estudiante@universidad.edu.ve (Estudiante)
- solicitante@universidad.edu.ve (Solicitante)
```

---

## 📊 Características Incluidas

### ✅ Sistema Administrativo
- Dashboard con estadísticas
- Gestión de usuarios
- Auditoría de acciones

### ✅ Portal Profesor
- Mis Materias
- Mis Horarios
- Calificaciones de estudiantes
- Tareas y Exámenes
- Asistencia
- Aula Virtual

### ✅ Portal Estudiante
- Inscripción a materias
- Mis Calificaciones
- Tareas
- Exámenes
- Asistencia
- Aula Virtual

### ✅ Landing Page
- Hero Carrusel animado
- Estadísticas de la universidad
- Noticias recientes
- Carreras disponibles
- Sistema de admisión

---

## 🔍 Datos de Demo

**Base de datos pre-poblada con:**
- 60 estudiantes inscritos
- 3 materias activas
- 3 horarios (lunes 08:00-10:00)
- 12 tareas disponibles
- 4 exámenes
- 6 entregas calificadas

---

## ⚙️ Stack Tecnológico

### Backend
- **Framework:** Laravel 11
- **Database:** MySQL 8.4
- **Auth:** Sanctum (Token-based)
- **API:** RESTful

### Frontend
- **Framework:** Vue 3
- **Build:** Vite
- **UI:** Tailwind CSS
- **HTTP:** Axios
- **State:** Pinia
- **Router:** Vue Router

---

## 🐛 Solución de Problemas

### El backend duerme después de 15 minutos

En Render (free tier), los servicios duermen sin actividad. Se reactivan automáticamente cuando accedes.

**Solución:** Hacer ping cada 5 minutos desde un uptime monitor (Pingdom, UptimeRobot)

### Errores de CORS

Si ves errores de CORS en la consola:

1. Ir al backend (Render)
2. En `.env`, verificar `APP_URL` es correcto
3. Redeploy

### La base de datos se reinicia

En Render free, la BD se reinicia periódicamente. Usa `render.yaml` para auto-seed.

---

## 📧 Contacto

**Developer:** Alf-Dev1906  
**Email:** danluissantanat@gmail.com

---

## 📄 Licencia

Proyecto privado - Solo para demo y presentación a partners
