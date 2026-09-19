# 📊 ESTRUCTURA DEL PROYECTO - SISTEMA DE GESTIÓN UNIVERSITARIA

## 🚀 Estado de los Servidores

- ✅ **Backend (Laravel):** http://localhost:8000
- ✅ **Frontend (Vue 3):** http://localhost:5173
- ✅ **Base de datos:** MySQL en XAMPP
- ✅ **Datos poblados:** 100 estudiantes, 25 profesores, 24 carreras

---

## 📁 ESTRUCTURA GENERAL

```
Gestion_uni/
│
├── 📂 backend/                          # API Laravel 11
│   ├── 📂 app/
│   │   ├── 📂 Http/
│   │   │   ├── 📂 Controllers/
│   │   │   │   ├── 📂 Api/
│   │   │   │   │   ├── 📄 AuthController.php          # Autenticación y login
│   │   │   │   │   ├── 📄 EstudianteController.php    # CRUD estudiantes
│   │   │   │   │   ├── 📄 ProfesorController.php      # CRUD profesores
│   │   │   │   │   ├── 📄 CarreraController.php       # CRUD carreras
│   │   │   │   │   ├── 📄 MateriaController.php       # CRUD materias
│   │   │   │   │   ├── 📄 HorarioController.php       # Gestión de horarios
│   │   │   │   │   ├── 📄 InscripcionController.php   # Inscripciones
│   │   │   │   │   ├── 📄 CalificacionController.php  # Calificaciones
│   │   │   │   │   ├── 📄 AulaController.php          # Gestión de aulas
│   │   │   │   │   └── 📄 FacultadController.php      # CRUD facultades
│   │   │   │   └── 📄 Controller.php
│   │   │   └── 📂 Middleware/
│   │   │       ├── 📄 EnsureUserRole.php              # Middleware de roles
│   │   │       ├── 📄 HandleCors.php                  # CORS
│   │   │       └── 📄 SecurityHeaders.php             # Headers de seguridad
│   │   ├── 📂 Models/
│   │   │   ├── 📄 User.php                           # Usuario (auth)
│   │   │   ├── 📄 Estudiante.php                     # Modelo Estudiante
│   │   │   ├── 📄 Profesor.php                       # Modelo Profesor
│   │   │   ├── 📄 Carrera.php                        # Modelo Carrera
│   │   │   ├── 📄 Materia.php                        # Modelo Materia
│   │   │   ├── 📄 Horario.php                        # Modelo Horario
│   │   │   ├── 📄 Inscripcion.php                    # Modelo Inscripción
│   │   │   ├── 📄 Calificacion.php                   # Modelo Calificación
│   │   │   ├── 📄 Aula.php                           # Modelo Aula
│   │   │   └── 📄 Facultad.php                       # Modelo Facultad
│   │   └── 📂 Traits/
│   │       └── 📄 HasRoleAuthorization.php           # Trait para autorización
│   │
│   ├── 📂 config/
│   │   ├── 📄 app.php                                # Configuración general
│   │   ├── 📄 auth.php                               # Configuración auth
│   │   ├── 📄 cors.php                               # Configuración CORS
│   │   ├── 📄 database.php                           # Configuración DB
│   │   └── 📄 sanctum.php                            # Configuración Sanctum
│   │
│   ├── 📂 database/
│   │   ├── 📂 migrations/
│   │   │   ├── 📄 0001_01_01_000000_create_users_table.php
│   │   │   ├── 📄 2024_01_01_000003_create_facultades_table.php
│   │   │   ├── 📄 2024_01_01_000004_create_carreras_table.php
│   │   │   ├── 📄 2024_01_01_000005_create_estudiantes_table.php
│   │   │   ├── 📄 2024_01_01_000006_create_profesores_table.php
│   │   │   ├── 📄 2024_01_01_000007_create_aulas_table.php
│   │   │   ├── 📄 2024_01_01_000008_create_materias_table.php
│   │   │   ├── 📄 2024_01_01_000009_create_horarios_table.php
│   │   │   ├── 📄 2024_01_01_000010_create_inscripciones_table.php
│   │   │   └── 📄 2026_09_04_190810_create_calificaciones_table.php
│   │   ├── 📂 seeders/
│   │   │   └── 📄 DatosCompletosSeeder.php           # ⭐ Seeder principal
│   │   └── 📂 factories/                             # Factories para testing
│   │
│   ├── 📂 routes/
│   │   └── 📄 api.php                                # ⭐ Rutas de la API
│   │
│   ├── 📂 storage/
│   │   ├── 📂 app/
│   │   ├── 📂 framework/
│   │   └── 📂 logs/
│   │
│   ├── 📄 .env                                       # ⚙️ Variables de entorno
│   ├── 📄 .env.example
│   ├── 📄 composer.json                              # Dependencias PHP
│   ├── 📄 composer.lock
│   └── 📄 artisan                                    # CLI de Laravel
│
├── 📂 frontend/                         # Aplicación Vue 3
│   ├── 📂 src/
│   │   ├── 📂 views/
│   │   │   ├── 📄 Landing.vue                        # 🏠 Landing page
│   │   │   ├── 📄 Carreras.vue                       # 📚 Catálogo de carreras
│   │   │   ├── 📄 Login.vue                          # 🔐 Página de login
│   │   │   │
│   │   │   ├── 📂 admin/                             # 👨‍💼 Vistas Administrativo
│   │   │   │   ├── 📄 AdminDashboard.vue
│   │   │   │   ├── 📄 EstudiantesView.vue
│   │   │   │   ├── 📄 ProfesoresView.vue
│   │   │   │   ├── 📄 CarrerasView.vue
│   │   │   │   ├── 📄 MateriasView.vue
│   │   │   │   ├── 📄 HorariosView.vue
│   │   │   │   └── 📄 InscripcionesView.vue
│   │   │   │
│   │   │   ├── 📂 profesor/                          # 👨‍🏫 Vistas Profesor
│   │   │   │   ├── 📄 ProfesorDashboard.vue
│   │   │   │   ├── 📄 MisMateriasView.vue
│   │   │   │   ├── 📄 MisEstudiantesView.vue
│   │   │   │   └── 📄 CalificacionesView.vue
│   │   │   │
│   │   │   ├── 📂 estudiante/                        # 👨‍🎓 Vistas Estudiante
│   │   │   │   ├── 📄 EstudianteDashboard.vue
│   │   │   │   ├── 📄 MiHorarioView.vue
│   │   │   │   ├── 📄 MisCalificacionesView.vue
│   │   │   │   └── 📄 InscripcionMateriasView.vue
│   │   │   │
│   │   │   ├── 📂 developer/                         # 🔧 Vistas Developer
│   │   │   │   └── 📄 DeveloperDashboard.vue
│   │   │   │
│   │   │   └── 📂 soporte/                           # 🛠️ Vistas Soporte IT
│   │   │       └── 📄 SoporteDashboard.vue
│   │   │
│   │   ├── 📂 components/
│   │   │   ├── 📄 NavBar.vue                         # Barra de navegación
│   │   │   ├── 📄 Footer.vue                         # Pie de página
│   │   │   ├── 📄 Sidebar.vue                        # Menú lateral
│   │   │   └── 📄 LoadingSpinner.vue                 # Spinner de carga
│   │   │
│   │   ├── 📂 router/
│   │   │   └── 📄 index.js                           # ⭐ Configuración de rutas
│   │   │
│   │   ├── 📂 stores/
│   │   │   ├── 📄 auth.js                            # Store de autenticación
│   │   │   ├── 📄 estudiantes.js                     # Store de estudiantes
│   │   │   └── 📄 carreras.js                        # Store de carreras
│   │   │
│   │   ├── 📂 assets/
│   │   │   ├── 📂 css/
│   │   │   │   └── 📄 main.css
│   │   │   └── 📂 images/
│   │   │
│   │   ├── 📄 App.vue                                # ⭐ Componente raíz
│   │   └── 📄 main.js                                # ⭐ Punto de entrada
│   │
│   ├── 📂 public/
│   │   └── 📄 favicon.ico
│   │
│   ├── 📄 index.html                                 # HTML principal
│   ├── 📄 package.json                               # Dependencias Node
│   ├── 📄 vite.config.js                             # Configuración Vite
│   └── 📄 .env                                       # ⚙️ Variables de entorno
│
├── 📂 Gestion_archivos_dependencias/    # 📦 Paquete de distribución
│   ├── 📄 Gestion_uni_completo.zip                   # Código fuente
│   ├── 📄 sge_db_completo.sql                        # Base de datos
│   ├── 📄 Credenciales.txt                           # Usuarios de prueba
│   └── 📄 INSTRUCCIONES_INSTALACION.txt              # Guía de instalación
│
└── 📄 README.md                                      # Documentación principal
```

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### **Tablas Principales:**

```sql
sge_db
├── users                    # Usuarios del sistema
├── facultades               # 7 facultades
├── carreras                 # 24 carreras (Ingenierías, Licenciaturas, TSU)
├── estudiantes              # 100 estudiantes
├── profesores               # 25 profesores
├── materias                 # 120 materias
├── aulas                    # 50 aulas
├── horarios                 # 60 horarios
├── inscripciones            # ~441 inscripciones
└── calificaciones           # ~280 calificaciones
```

### **Relaciones:**

```
users (1) ─── (1) estudiante/profesor
facultad (1) ─── (N) carreras
facultad (1) ─── (N) profesores
facultad (1) ─── (N) aulas
carrera (1) ─── (N) estudiantes
carrera (1) ─── (N) materias
materia (1) ─── (N) horarios
profesor (1) ─── (N) horarios
aula (1) ─── (N) horarios
horario (1) ─── (N) inscripciones
estudiante (1) ─── (N) inscripciones
inscripcion (1) ─── (1) calificacion
```

---

## 🔐 SISTEMA DE ROLES

### **Roles Disponibles:**

1. **👨‍💻 Desarrollador** (`desarrollador`)
   - Acceso completo al sistema
   - Gestión de usuarios
   - Configuración avanzada

2. **🛠️ Soporte IT** (`soporte_it`)
   - Gestión técnica
   - Mantenimiento del sistema
   - Logs y auditoría

3. **👨‍💼 Administrativo** (`administrativo`)
   - Gestión de estudiantes
   - Gestión de profesores
   - Gestión de carreras y materias
   - Gestión de horarios
   - Gestión de inscripciones

4. **👨‍🏫 Profesor** (`profesor`)
   - Ver sus materias asignadas
   - Ver estudiantes inscritos
   - Registrar calificaciones
   - Gestionar asistencias

5. **👨‍🎓 Estudiante** (`estudiante`)
   - Ver su horario
   - Ver sus calificaciones
   - Inscribir materias
   - Ver historial académico

---

## 🔌 ENDPOINTS PRINCIPALES

### **Autenticación:**
```
POST   /api/login              # Iniciar sesión
POST   /api/logout             # Cerrar sesión
GET    /api/me                 # Usuario actual
```

### **Público:**
```
GET    /api/public/carreras    # Listar carreras (landing)
GET    /api/public/facultades  # Listar facultades
```

### **Administrativo:**
```
GET    /api/estudiantes        # Listar estudiantes
POST   /api/estudiantes        # Crear estudiante
GET    /api/estudiantes/{id}   # Ver estudiante
PUT    /api/estudiantes/{id}   # Actualizar estudiante
DELETE /api/estudiantes/{id}   # Eliminar estudiante

GET    /api/profesores         # Similar a estudiantes
GET    /api/carreras           # Similar a estudiantes
GET    /api/materias           # Similar a estudiantes
GET    /api/horarios           # Similar a estudiantes
GET    /api/inscripciones      # Similar a estudiantes
```

### **Profesor:**
```
GET    /api/profesor/materias       # Mis materias
GET    /api/profesor/estudiantes    # Mis estudiantes
POST   /api/calificaciones          # Registrar calificación
```

### **Estudiante:**
```
GET    /api/estudiante/horario         # Mi horario
GET    /api/estudiante/calificaciones  # Mis calificaciones
POST   /api/inscripciones              # Inscribir materia
```

---

## 🎨 TECNOLOGÍAS UTILIZADAS

### **Backend:**
- ⚙️ Laravel 11
- 🔐 Sanctum (autenticación)
- 🗄️ MySQL 8.0
- 📝 Eloquent ORM
- 🛡️ Middleware personalizados

### **Frontend:**
- ⚡ Vue 3 (Composition API)
- 🎨 Element Plus (UI)
- 🚀 Vite (build tool)
- 📦 Pinia (state management)
- 🔀 Vue Router 4

### **Herramientas:**
- 📦 Composer (PHP)
- 📦 npm (Node.js)
- 🐬 XAMPP (Apache + MySQL)
- 🔧 Artisan CLI

---

## 📊 DATOS DE PRUEBA

### **Usuarios Precargados:**

| Rol            | Email                           | Password         |
|----------------|---------------------------------|------------------|
| Desarrollador  | developer@universidad.edu.ve    | Dev2026!         |
| Soporte IT     | soporte@universidad.edu.ve      | Soporte2026!     |
| Administrativo | admin@universidad.edu.ve        | Admin2026!       |
| Profesor       | profesor@universidad.edu.ve     | Profesor2026!    |
| Estudiante     | estudiante@universidad.edu.ve   | Estudiante2026!  |

### **Datos Poblados:**
- ✅ 7 Facultades
- ✅ 24 Carreras (6 Ingenierías, 13 Licenciaturas, 5 TSU)
- ✅ 50 Aulas
- ✅ 25 Profesores
- ✅ 100 Estudiantes
- ✅ 120 Materias
- ✅ 60 Horarios
- ✅ ~441 Inscripciones
- ✅ ~280 Calificaciones

---

## 🚀 FLUJO DE NAVEGACIÓN

### **Usuario No Autenticado:**
```
Landing Page (/) 
    ├── Ver carreras (/carreras)
    └── Login (/login) → Dashboard según rol
```

### **Administrativo:**
```
Dashboard Admin
    ├── Estudiantes (/admin/estudiantes)
    ├── Profesores (/admin/profesores)
    ├── Carreras (/admin/carreras)
    ├── Materias (/admin/materias)
    ├── Horarios (/admin/horarios)
    └── Inscripciones (/admin/inscripciones)
```

### **Profesor:**
```
Dashboard Profesor
    ├── Mis Materias (/profesor/materias)
    ├── Mis Estudiantes (/profesor/estudiantes)
    └── Calificaciones (/profesor/calificaciones)
```

### **Estudiante:**
```
Dashboard Estudiante
    ├── Mi Horario (/estudiante/horario)
    ├── Mis Calificaciones (/estudiante/calificaciones)
    └── Inscribir Materias (/estudiante/inscripcion)
```

---

## 📝 ARCHIVOS DE CONFIGURACIÓN CLAVE

### **Backend (.env):**
```env
APP_URL=http://localhost:8000
DB_DATABASE=sge_db
DB_USERNAME=root
DB_PASSWORD=
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

### **Frontend (.env):**
```env
VITE_API_URL=http://localhost:8000/api
```

---

## 🔗 ENLACES ÚTILES

- 🌐 **Frontend:** http://localhost:5173
- 🔌 **Backend API:** http://localhost:8000/api
- 🗄️ **phpMyAdmin:** http://localhost/phpmyadmin
- 📄 **Documentación API:** Ver archivo `API_DOCUMENTATION.md`

---

## ✅ VERIFICACIÓN DEL SISTEMA

### **Backend:**
```bash
curl http://localhost:8000/api/debug/check-database
```

### **Frontend:**
Abrir: http://localhost:5173

### **Base de datos:**
```bash
mysql -u root -e "USE sge_db; SELECT COUNT(*) FROM estudiantes;"
```

---

**🎉 Sistema completamente funcional y listo para usar!**
