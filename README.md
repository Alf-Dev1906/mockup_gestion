# 🎓 Sistema de Gestión Universitaria (SGU)

Sistema completo de gestión académica desarrollado con **Laravel 11** (backend) y **Vue 3 + Vite** (frontend).

## 📋 Características Principales

### 🔐 Autenticación
- Sistema de login con Laravel Sanctum
- Gestión de roles (admin)
- Sesiones persistentes con tokens Bearer
- Protección de rutas privadas

### 📚 Módulos Administrativos
1. **Facultades** - Gestión de facultades universitarias
2. **Carreras** - Catálogo de programas académicos (TSU, Licenciatura, Ingeniería)
3. **Estudiantes** - Registro y seguimiento de estudiantes
4. **Profesores** - Gestión del personal docente
5. **Materias** - Catálogo de asignaturas por carrera
6. **Aulas** - Administración de espacios físicos
7. **Horarios** - Programación de clases con validación de conflictos
8. **Inscripciones** - Gestión de inscripciones de estudiantes
9. **Calificaciones** - Sistema de evaluación (30%-30%-40%)

### 🌐 Sitio Web Público
- **Home** - Landing page institucional
- **Carreras** - Catálogo público con filtros por nivel
- **Noticias** - Sistema de noticias con categorías
- **Contacto** - Formulario de contacto con validación

## 🚀 Estado del Proyecto

### ✅ Backend (Laravel)
- ✓ API REST completa con 10 controladores
- ✓ Autenticación con Sanctum
- ✓ Middleware de autorización por roles
- ✓ Validación robusta en todos los endpoints
- ✓ CORS configurado para desarrollo
- ✓ Rate limiting en endpoints sensibles
- ✓ Base de datos poblada con datos de prueba:
  - 100,000 estudiantes
  - 5,000 profesores
  - 1,254 materias
  - 800 aulas
  - 15,000 horarios
  - 250,135 inscripciones
  - 149,998 calificaciones

### ✅ Frontend (Vue 3)
- ✓ Sistema de rutas con Vue Router
- ✓ Estado global con Pinia
- ✓ Diseño responsive con Tailwind CSS
- ✓ Interceptores de Axios configurados
- ✓ Sistema de notificaciones toast
- ✓ Manejo de errores global
- ✓ 10 vistas administrativas completas
- ✓ 4 vistas públicas implementadas
- ✓ Componentes reutilizables

## 🛠️ Tecnologías

### Backend
- **Laravel 11** - Framework PHP
- **MySQL** - Base de datos (vía XAMPP)
- **Sanctum** - Autenticación API
- **Eloquent ORM** - Manejo de modelos

### Frontend
- **Vue 3** - Framework JavaScript
- **Vite** - Build tool y dev server
- **Tailwind CSS** - Framework CSS
- **Pinia** - State management
- **Axios** - Cliente HTTP
- **Vue Router** - Enrutamiento

## 📦 Instalación y Ejecución

### Requisitos Previos
- PHP 8.2+
- Composer
- Node.js 24+
- MySQL (XAMPP)

### Backend (Puerto 8000)
```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve
```

### Frontend (Puerto 5174)
```bash
cd frontend
npm install
npm run dev
```

## 🔑 Credenciales de Acceso

### Usuario Administrador
- **Email:** admin@universidad.edu.ve
- **Password:** Admin123!

## 📡 Endpoints Principales

### Públicos (sin autenticación)
- `GET /api/public/carreras` - Lista de carreras
- `GET /api/public/facultades` - Lista de facultades
- `POST /api/login` - Iniciar sesión

### Protegidos (requieren token)
- `GET /api/me` - Información del usuario autenticado
- `POST /api/logout` - Cerrar sesión
- `GET|POST|PUT|DELETE /api/{recurso}` - CRUD completo

## 🎨 Características del Frontend

### Sistema de Notificaciones
- Toasts animados para feedback al usuario
- 4 tipos: success, error, warning, info
- Auto-dismiss configurable
- Manejo automático de errores HTTP

### Vistas Administrativas
Todas las vistas incluyen:
- Búsqueda en tiempo real
- Filtros dinámicos
- Paginación
- Modales para crear/editar
- Validación de formularios
- Mensajes de confirmación

### Diseño Responsive
- Mobile-first approach
- Breakpoints optimizados
- Navegación adaptativa
- Grid systems flexibles

## 📂 Estructura del Proyecto

```
Gestion_uni/
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── Providers/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
│
└── frontend/
    ├── src/
    │   ├── components/
    │   ├── composables/
    │   ├── layouts/
    │   ├── router/
    │   ├── services/
    │   ├── stores/
    │   └── views/
    │       ├── admin/
    │       └── [públicas]
    └── vite.config.js
```

## 🔄 Flujos de Trabajo

### Login
1. Usuario ingresa credenciales en `/login`
2. Backend valida y retorna token + datos de usuario
3. Token se almacena en localStorage
4. Redirección automática al dashboard

### CRUD Genérico
1. Vista carga datos con paginación
2. Usuario aplica filtros (búsqueda, categorías, etc.)
3. Crear/Editar abre modal con formulario
4. Validación en frontend + backend
5. Toast de confirmación o error
6. Recarga automática de datos

### Manejo de Errores
1. Error HTTP capturado por interceptor
2. Clasificación por código de estado
3. Toast automático con mensaje apropiado
4. Log en consola para debugging
5. Redirección en caso de sesión expirada

## 🧪 Testing Realizado

### Backend
- ✓ Migraciones ejecutadas correctamente
- ✓ Seeders poblaron 7 tablas con datos
- ✓ Endpoints públicos responden correctamente
- ✓ CORS configurado para localhost:5174

### Frontend
- ✓ Servidor Vite corriendo en puerto 5174
- ✓ Proxy a backend funcionando
- ✓ Rutas públicas y protegidas configuradas
- ✓ Sistema de notificaciones operativo
- ✓ Todas las vistas renderizando correctamente

## 🚦 Estado de los Servidores

### Backend
- **URL:** http://127.0.0.1:8000
- **Estado:** ✅ Running
- **Base de datos:** ✅ Conectada (sge_db)

### Frontend
- **URL:** http://localhost:5174
- **Estado:** ✅ Running
- **Proxy:** ✅ Configurado a localhost:8000

## 📝 Notas Importantes

1. **Puerto Frontend:** El frontend está corriendo en el puerto 5174 en lugar de 5173 porque este último estaba ocupado.

2. **CORS:** La configuración de CORS en el backend permite peticiones desde `localhost:5173` y `localhost:5174`.

3. **Datos de Prueba:** La base de datos tiene un volumen significativo de datos de prueba (100k+ estudiantes), ideal para testing de rendimiento.

4. **Validación:** Todas las vistas administrativas tienen validación tanto en frontend como backend.

5. **Sesiones:** Los tokens tienen una duración de 120 minutos y se renuevan automáticamente.

## 🔮 Próximas Mejoras Sugeridas

- [ ] Dashboard con gráficas estadísticas
- [ ] Exportación de reportes en PDF/Excel
- [ ] Sistema de notificaciones en tiempo real
- [ ] Módulo de mensajería interna
- [ ] Calendario académico interactivo
- [ ] Aplicación móvil (React Native)
- [ ] Tests automatizados (PHPUnit + Vitest)
- [ ] CI/CD con GitHub Actions
- [ ] Dockerización del proyecto
- [ ] PWA para uso offline

## 👥 Créditos

Sistema desarrollado para la gestión académica universitaria.

**Año:** 2026  
**Framework Backend:** Laravel 11  
**Framework Frontend:** Vue 3  

---

## 📞 Soporte

Para dudas o problemas:
- Revisar logs del backend en `backend/storage/logs/`
- Revisar consola del navegador para errores de frontend
- Verificar que XAMPP (MySQL) esté corriendo
- Confirmar que ambos servidores estén activos

---

**¡Sistema listo para usar!** 🎉
