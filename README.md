# 🎓 mockup_gestion - Sistema de Gestión Universitaria

> **Sistema completo de gestión académica con backend Laravel y frontend Vue.js - Listo para mostrar a tus partners**

---

## 🎯 ¿Qué es esto?

**mockup_gestion** es un sistema de gestión universitaria completamente funcional que incluye:

- ✅ **Portal Administrativo** - Gestión completa del sistema
- ✅ **Portal Profesor** - Calificaciones, tareas, exámenes, horarios
- ✅ **Portal Estudiante** - Mis cursos, calificaciones, entregas
- ✅ **Landing Page** - Presentación moderna con carrusel hero
- ✅ **Aula Virtual** - Exámenes, tareas, asistencia

---

## 🚀 Deploy Rápido (15 minutos)

### **Opción 1: Deploy Automático** ⭐ (Recomendado)

Lee [QUICK_START.md](./QUICK_START.md) - Pasos simplificados

```
⏱️ 15 minutos de tu tiempo
💰 100% gratis (Render + Netlify)
🌍 URLs públicas funcionando
```

### **Opción 2: Deploy Detallado**

Lee [README_DEPLOYMENT.md](./README_DEPLOYMENT.md) - Guía completa

---

## 📱 URLs después de Deploy

```
🎨 Frontend:  https://mockup-gestion-frontend.netlify.app
🔧 API:       https://mockup-gestion-backend.onrender.com/api
📊 Docs:      https://mockup-gestion-backend.onrender.com/api/docs
```

---

## 🔐 Prueba Inmediata

### Credenciales (todas con password `Admin2026!`):

| Rol | Email | Acceso |
|-----|-------|--------|
| 👨‍💼 **Admin** | admin@universidad.edu.ve | Dashboard, auditoría, usuarios |
| 👨‍🏫 **Profesor** | profesor@universidad.edu.ve | Calificaciones, tareas, exámenes |
| 👨‍🎓 **Estudiante** | estudiante@universidad.edu.ve | Mis notas, tareas, exámenes |
| 📝 **Solicitante** | solicitante@universidad.edu.ve | Solicitud de admisión |

---

## 💾 Datos Pre-poblados

Ya incluye:

```
✅ 60 estudiantes inscritos
✅ 3 materias activas
✅ 3 horarios (Lunes 08:00-10:00)
✅ 12 tareas con entregas calificadas
✅ 4 exámenes parciales
✅ 3 calificaciones (18.5/20)
```

---

## 🏗️ Estructura del Proyecto

```
mockup_gestion/
│
├── 📂 backend/                    # API Laravel 11
│   ├── app/Http/Controllers/      # Controladores
│   ├── app/Models/                # Modelos (Estudiante, Profesor, etc)
│   ├── database/migrations/       # Migraciones de BD
│   ├── database/seeders/          # Datos iniciales
│   ├── routes/api.php             # Rutas de API
│   └── .env.example               # Variables de entorno
│
├── 📂 frontend/                   # App Vue 3
│   ├── src/views/                 # Páginas principales
│   ├── src/components/            # Componentes reutilizables
│   ├── src/router/                # Rutas del frontend
│   ├── src/stores/                # Estado global (Pinia)
│   └── vite.config.js             # Configuración Vite
│
├── 📄 QUICK_START.md              # ⭐ Empieza aquí
├── 📄 README_DEPLOYMENT.md        # Guía completa
├── 📄 DEPLOYMENT.md               # Detalles técnicos
├── 🔧 render.yaml                 # Config Render
├── 🔧 netlify.toml                # Config Netlify
└── 📄 README.md                   # Este archivo
```

---

## 🛠️ Stack Tecnológico

### Backend
```
Framework: Laravel 11
Database: MySQL 8.4 (MariaDB)
Auth: Sanctum (JWT tokens)
API: RESTful + JSON
Testing: PHPUnit
Server: Apache/Nginx compatible
```

### Frontend
```
Framework: Vue 3 (Composition API)
Build: Vite
Styling: Tailwind CSS + DaisyUI
HTTP: Axios
State: Pinia
Router: Vue Router 4
Icons: Heroicons
```

---

## 📊 Funcionalidades Principales

### 🏫 Admin Dashboard
- 📈 Estadísticas del sistema
- 👥 Gestión de usuarios y roles
- 📋 Auditoría de acciones
- ⚙️ Configuración del sistema

### 👨‍🏫 Portal Profesor
```
Materias      → Ver mis materias y horarios
Calificaciones → Calificar a estudiantes
Tareas        → Crear, enviar feedback, calificar entregas
Exámenes      → Crear preguntas, calificar respuestas
Asistencia    → Marcar asistencia
Aula Virtual  → Todo centralizado
```

### 👨‍🎓 Portal Estudiante
```
Inscripción   → Ver materias inscritas
Calificaciones → Ver mis notas
Tareas        → Entregar trabajos
Exámenes      → Resolver pruebas online
Asistencia    → Ver registro de asistencia
Aula Virtual  → Acceder a recursos
```

### 🏠 Landing Page
```
Hero Carrusel  → 4 slides animados
Estadísticas   → 10K+ estudiantes, 50+ años, etc
Carreras       → Descripción de programas
Noticias       → Actualizaciones recientes
CTA Sections   → Botones de inscripción
```

---

## 🚀 Ejecución Local

### Backend

```bash
cd backend

# Copiar configuración
cp .env.example .env

# Generar key
php artisan key:generate

# Instalar dependencias
composer install

# Ejecutar migraciones con datos de prueba
php artisan migrate:fresh --seed

# Iniciar servidor
php artisan serve
```

Backend en: `http://localhost:8000`

### Frontend

```bash
cd frontend

# Instalar dependencias
npm install

# Desarrollo
npm run dev

# Producción
npm run build
```

Frontend en: `http://localhost:5173`

---

## 🔑 Variables de Entorno

### Backend (.env)
```env
APP_NAME=SGE
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mockup-gestion-backend.onrender.com

DB_CONNECTION=mysql
DB_HOST=mysql.render.internal
DB_PORT=3306
DB_DATABASE=sge_db
DB_USERNAME=admin
DB_PASSWORD=your_password
```

### Frontend (.env.production)
```env
VITE_API_BASE_URL=https://mockup-gestion-backend.onrender.com/api
```

---

## 🐛 Troubleshooting

### ❌ Backend duerme en Render
**Causa:** Free tier duerme después de 15 min sin actividad

**Solución:**
1. Usar UptimeRobot para hacer ping cada 5 min
2. O actualizar a plan pago

### ❌ Error CORS en consola
**Causa:** URLs no coinciden

**Solución:**
- Verificar `APP_URL` en backend
- Verificar `VITE_API_BASE_URL` en frontend
- Redeploy ambos

### ❌ 404 en rutas del frontend
**Causa:** Netlify no redirige a index.html

**Solución:**
- `netlify.toml` debe existir
- Redeploy en Netlify

---

## 📚 Documentación Adicional

- **[QUICK_START.md](./QUICK_START.md)** - Pasos rápidos de deployment ⭐
- **[README_DEPLOYMENT.md](./README_DEPLOYMENT.md)** - Guía completa
- **[DEPLOYMENT.md](./DEPLOYMENT.md)** - Detalles técnicos y variables
- **[backend/API_DOCUMENTATION.md](./backend/API_DOCUMENTATION.md)** - Endpoints disponibles
- **[MOTOR_JSON_EXAMENES.md](./MOTOR_JSON_EXAMENES.md)** - Cómo funciona el motor de evaluación

---

## 📊 Estadísticas del Proyecto

```
Archivos: 150+
Lineas de código: 20,000+
Componentes Vue: 40+
Endpoints API: 50+
Modelos Eloquent: 20+
Migraciones: 25+
```

---

## 🎯 Roadmap Futuro

- [ ] Sistema de notificaciones en tiempo real (WebSockets)
- [ ] Exportar calificaciones a PDF
- [ ] Videollamadas para tutoría
- [ ] Sistema de becas
- [ ] Integración con correo electrónico
- [ ] Certificados digitales
- [ ] Mobile app (React Native)

---

## 👨‍💻 Autor

**Alf-Dev1906**  
📧 danluissantanat@gmail.com  
🔗 https://github.com/Alf-Dev1906

---

## 📄 Licencia

Proyecto privado - Solo para demo y presentación a partners

---

## 🙏 Créditos

- **Framework:** Laravel, Vue.js, Tailwind CSS
- **Hosting:** Render, Netlify
- **Database:** MySQL
- **Icons:** Heroicons

---

<div align="center">

### 🚀 **¿Listo para empezar?**

**[👉 Lee QUICK_START.md para deployment en 15 minutos](./QUICK_START.md)**

---

**Hecho con ❤️ para mostrar lo que puedes lograr**

</div>
