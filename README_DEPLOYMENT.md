# 🚀 mockup_gestion - Sistema de Gestión Universitaria

**Deploy en vivo:** Sigue los pasos abajo para tener tu aplicación funcionando en Render y Netlify (100% gratis)

---

## 📱 URLs de Demostración

Después de completar el setup:

```
🎨 Frontend:  https://mockup-gestion-frontend.netlify.app
🔧 API:       https://mockup-gestion-backend.onrender.com/api
📊 Docs:      https://mockup-gestion-backend.onrender.com/api/docs
```

---

## 🔐 Credenciales de Prueba

```
Admin
├─ Email:    admin@universidad.edu.ve
└─ Password: Admin2026!

Profesor
├─ Email:    profesor@universidad.edu.ve
└─ Password: Admin2026!

Estudiante
├─ Email:    estudiante@universidad.edu.ve
└─ Password: Admin2026!

Solicitante
├─ Email:    solicitante@universidad.edu.ve
└─ Password: Admin2026!
```

---

## 🔧 SETUP - Backend en Render.com (10 minutos)

### 1️⃣ Crear Web Service en Render

**URL:** https://render.com

1. Click en **"New"** → **"Web Service"**
2. Conectar GitHub si no está conectado
3. Buscar `Alf-Dev1906/mockup_gestion`
4. Seleccionar y crear

### 2️⃣ Configurar Variables de Entorno

En Render, ir a "Environment" y agregar:

```env
APP_NAME=SGE
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YourGeneratedKeyHere
APP_URL=https://mockup-gestion-backend.onrender.com

DB_CONNECTION=mysql
LOG_CHANNEL=single
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 3️⃣ Generar APP_KEY

Ejecutar en tu máquina local:

```bash
cd backend
php artisan key:generate --show
# Copiar el valor y pegarlo en Render como APP_KEY
```

### 4️⃣ Crear Base de Datos MySQL

En Render:

1. Click en **"New"** → **"MySQL"**
2. Nombre: `mockup-gestion-mysql`
3. Database: `sge_db`
4. Las credenciales se conectarán automáticamente

### 5️⃣ Configurar Build & Start

**Build Command:**
```bash
composer install && php artisan migrate:fresh --seed
```

**Start Command:**
```bash
php -S 0.0.0.0:10000 -t public
```

**Publish Port:** 10000

### 6️⃣ Deploy

Click en **"Create Web Service"** y esperar ~5 minutos

---

## 🎨 SETUP - Frontend en Netlify (5 minutos)

### 1️⃣ Conectar Repositorio

**URL:** https://netlify.com

1. Click en **"Add new site"** → **"Import an existing project"**
2. Conectar GitHub
3. Seleccionar `Alf-Dev1906/mockup_gestion`

### 2️⃣ Configurar Build

- **Base directory:** `frontend`
- **Build command:** `npm run build`
- **Publish directory:** `frontend/dist`

### 3️⃣ Variables de Entorno

Agregar en "Site settings" → "Build & deploy" → "Environment":

```
VITE_API_BASE_URL=https://mockup-gestion-backend.onrender.com/api
```

### 4️⃣ Deploy

Click en **"Deploy site"** y esperar ~2 minutos

---

## ✨ Funcionalidades Incluidas

### 🏫 Admin Dashboard
- 📊 Estadísticas generales
- 👥 Gestión de usuarios
- 📋 Auditoría de acciones
- ⚙️ Configuración del sistema

### 👨‍🏫 Portal Profesor
- 📚 Mis Materias
- ⏰ Horarios
- 📝 Calificaciones de estudiantes
- 📋 Tareas (crear, calificar, entregas)
- 📝 Exámenes (crear preguntas, calificar)
- ✅ Asistencia
- 🎓 Aula Virtual

### 👨‍🎓 Portal Estudiante
- 📚 Mis Materias Inscritas
- 📊 Mis Calificaciones
- 📋 Mis Tareas (entregar, ver feedback)
- 📝 Mis Exámenes (resolver, ver resultados)
- ✅ Asistencia
- 🎓 Aula Virtual

### 🏠 Landing Page
- 🎠 Hero Carrusel animado
- 📊 Estadísticas de la universidad
- 📰 Noticias recientes
- 🎓 Carreras disponibles
- 📝 Sistema de admisión

---

## 💾 Datos de Demo Pre-poblados

```
✅ 60 estudiantes inscritos en materias
✅ 3 materias activas (Programación, BD, Ingeniería de Software)
✅ 3 horarios (Lunes 08:00-10:00)
✅ 12 tareas disponibles
✅ 4 exámenes (parciales)
✅ 6 entregas calificadas
✅ 3 calificaciones con notas (18.5/20)
```

---

## 🛠️ Stack Tecnológico

### Backend
- **Framework:** Laravel 11
- **Database:** MySQL 8.4
- **Auth:** Sanctum (Token-based JWT)
- **API:** RESTful + JSON
- **Server:** Apache/Nginx compatible

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite
- **Styling:** Tailwind CSS
- **HTTP Client:** Axios
- **State:** Pinia
- **Router:** Vue Router 4
- **UI Components:** Headless

---

## 🐛 Troubleshooting

### ❌ Backend duerme después de 15 minutos

**Causa:** Render free tier duerme servicios inactivos

**Solución:** 
1. Usar uptime monitor (UptimeRobot, Pingdom)
2. Hacer ping al backend cada 5 minutos
3. O actualizar a plan de pago

### ❌ Errores de CORS

**Causa:** URLs no coinciden

**Solución:**
1. Verificar `APP_URL` en backend = URL de Render
2. Verificar `VITE_API_BASE_URL` en frontend = URL de Render
3. Redeploy ambos

### ❌ La base de datos se reinicia

**Causa:** Render free tier reinicia periódicamente

**Solución:**
- Los seeds en `render.yaml` recrearán los datos automáticamente
- O crear snapshot de la BD (plan pago)

### ❌ 404 en rutas del frontend

**Causa:** Netlify no redirige a index.html

**Solución:**
- Verificar que `netlify.toml` existe
- Redeploy en Netlify

---

## 📚 Documentación Adicional

- [DEPLOYMENT.md](./DEPLOYMENT.md) - Guía detallada de deployment
- [backend/API_DOCUMENTATION.md](./backend/API_DOCUMENTATION.md) - Documentación de API
- [MOTOR_JSON_EXAMENES.md](./MOTOR_JSON_EXAMENES.md) - Motor de evaluación de exámenes

---

## 📊 Estructura de Carpetas

```
mockup_gestion/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   ├── .env.example
│   └── composer.json
│
├── frontend/                # Vue.js App
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── router/
│   │   ├── stores/
│   │   └── App.vue
│   ├── vite.config.js
│   └── package.json
│
├── render.yaml              # Configuración Render
├── netlify.toml             # Configuración Netlify
├── DEPLOYMENT.md            # Guía de deployment
└── README.md
```

---

## 🚀 Deploy Manual (Alternativa)

Si prefieres deploying manualmente:

### Backend (Heroku, Railway, DigitalOcean, etc.)
```bash
cd backend
php artisan migrate:fresh --seed
php -S 0.0.0.0:8000 -t public
```

### Frontend (Vercel, Surge, etc.)
```bash
cd frontend
npm run build
# Subir carpeta dist
```

---

## 👨‍💻 Desarrollo Local

### Backend
```bash
cd backend
cp .env.example .env
php artisan key:generate
composer install
php artisan migrate:fresh --seed
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

Acceder a `http://localhost:5173`

---

## 📧 Contacto

**Developer:** Alf-Dev1906  
**Email:** danluissantanat@gmail.com  
**GitHub:** https://github.com/Alf-Dev1906

---

## 📄 Licencia

Proyecto privado - Solo para demo y presentación a partners

---

**¡Listo para mostrar a tus partners! 🎉**
