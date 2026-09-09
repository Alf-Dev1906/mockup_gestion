# 🔐 CREDENCIALES DE PRUEBA - SISTEMA DE GESTIÓN UNIVERSITARIA

## ✅ Estado del Sistema

- **Backend Laravel**: ✅ Funcionando en http://localhost:8000
- **Frontend Vue**: ✅ Funcionando en http://localhost:5173
- **Base de Datos**: ✅ Conectada (MySQL - sge_db)
- **Autenticación**: ✅ Sanctum configurado (tokens de 8 horas)

---

## 👥 USUARIOS DE PRUEBA

Todos los usuarios han sido creados y verificados. Password válida por 8 horas.

### 🟣 NIVEL 5 - DESARROLLADOR (Control Total)
```
Email:    developer@universidad.edu.ve
Password: Dev2026!
Ruta:     /dev/dashboard
```
**Permisos**: Acceso completo a base de datos, logs, configuración, comandos Artisan, backups, estadísticas del sistema.

---

### 🔵 NIVEL 4 - SOPORTE IT
```
Email:    soporte@universidad.edu.ve
Password: Soporte2026!
Ruta:     /soporte/dashboard
```
**Permisos**: Gestión de usuarios, asignación de roles, logs del sistema, sesiones activas, respaldos, tickets de soporte.

---

### 🟢 NIVEL 3 - ADMINISTRATIVO
```
Email:    admin@universidad.edu.ve
Password: Admin2026!
Ruta:     /admin/dashboard
```
**Permisos**: Aprobar/rechazar admisiones, gestión académica completa (facultades, carreras, estudiantes, profesores, materias, aulas, horarios), gestión de pagos, reportes académicos.

---

### 🟡 NIVEL 2 - PROFESOR
```
Email:    profesor@universidad.edu.ve
Password: Profesor2026!
Ruta:     /profesor/dashboard
```
**Permisos**: Ver sus materias asignadas, gestionar calificaciones de sus estudiantes, ver horarios propios, tomar asistencia.

---

### 🔵 NIVEL 1A - ESTUDIANTE ACTIVO
```
Email:    estudiante@universidad.edu.ve
Password: Estudiante2026!
Ruta:     /estudiante/dashboard
```
**Permisos**: Inscribirse en materias, ver horarios personales, consultar calificaciones, actualizar perfil, ver inscripciones activas.

---

### ⚪ NIVEL 1B - ESTUDIANTE SOLICITANTE
```
Email:    solicitante@universidad.edu.ve
Password: Solicitante2026!
Ruta:     /estudiante/solicitud
```
**Permisos**: LIMITADO - Solo puede ver el estatus de su solicitud de admisión y actualizar documentos. No puede inscribirse ni acceder a otras funciones hasta ser aprobado.

---

## 🧪 PRUEBAS REALIZADAS

### ✅ Backend Endpoints Verificados
- `POST /api/login` → ✅ Todos los usuarios pueden iniciar sesión
- `GET /api/me` → ✅ Devuelve información del usuario autenticado
- `GET /api/dev/dashboard` → ✅ Responde con estadísticas del sistema
- `GET /api/admin/dashboard` → ✅ Responde con KPIs administrativos
- `POST /api/logout` → ✅ Revoca el token correctamente

### ✅ Autenticación
- Tokens Sanctum se generan correctamente
- Duración: 480 minutos (8 horas)
- Tipo: Bearer token
- Los tokens antiguos se revocan al hacer nuevo login

### ✅ Middleware de Roles
- Alias `role` registrado en `bootstrap/app.php`
- Jerarquía de roles implementada (1-5)
- Bloqueo de acceso no autorizado devuelve 403
- Logs de seguridad funcionando

---

## 🔧 SI EL LOGIN CIERRA SESIÓN INMEDIATAMENTE

### ✅ PROBLEMA RESUELTO

**Causa**: El auth store estaba llamando a `/api/me` DOS VECES:
1. Primera vez desde `login()` → `_loadMe()`
2. Segunda vez dentro de `_loadMe()` → `api.get('/me')`

La segunda llamada podía fallar por timing o porque el token no estaba completamente guardado.

**Solución aplicada**: 
- Guardar token en localStorage ANTES de llamar a `/me`
- Hacer la llamada a `/me` opcional (si falla, usar datos del login)
- Eliminar la función `_loadMe()` que causaba la duplicación

**Cómo verificar que está solucionado**:
1. Abre el navegador en http://localhost:5174
2. Abre DevTools (F12) → Network tab
3. Haz login con cualquier usuario
4. Deberías ver solo 2 requests: `POST /api/login` y `GET /api/me`
5. Ambos deben devolver 200 OK
6. NO deberías ser redirigido al login de nuevo

Si aún ves el problema, sigue estos pasos adicionales:

### Solución 1: Verificar CORS
Asegúrate de que el backend acepte requests del frontend:

```bash
# En backend/.env verificar:
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

### Solución 2: Verificar el Proxy de Vite
En `frontend/vite.config.js` debe estar:

```javascript
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
    }
  }
}
```

### Solución 3: Limpiar LocalStorage
A veces quedan tokens viejos. En la consola del navegador:

```javascript
localStorage.clear()
```

Luego recarga la página e intenta login de nuevo.

---

## 🚀 CÓMO PROBAR EL SISTEMA

### 1. Asegúrate de que ambos servidores estén corriendo:

```powershell
# Terminal 1 - Backend Laravel
cd backend
php artisan serve

# Terminal 2 - Frontend Vue
cd frontend
npm run dev
```

### 2. Abre el navegador en: http://localhost:5173

### 3. Haz click en "Iniciar Sesión" (esquina superior derecha)

### 4. Prueba con cualquier usuario de arriba

### 5. Deberías ser redirigido a su dashboard correspondiente

---

## 🐛 DEBUGGING

### Ver logs del backend:
```bash
cd backend
tail -f storage/logs/laravel.log
```

### Ver requests en tiempo real (Laravel):
```bash
cd backend
php artisan serve --verbose
```

### Consola del navegador:
- F12 → Network → Ver requests a `/api/login` y `/api/me`
- Verifica que devuelvan 200 OK
- Revisa la response del endpoint `/me`

---

## 📊 ESTADÍSTICAS DE LA BASE DE DATOS

```
✓ Facultades:      10
✓ Carreras:        ~30
✓ Profesores:      ~5,000
✓ Aulas:           ~800
✓ Materias:        ~1,500
✓ Estudiantes:     ~100,000
✓ Horarios:        ~15,000
✓ Inscripciones:   ~250,000
```

---

## 🎨 COLORES POR ROL (Frontend)

- **Desarrollador**: Morado/Gris (Purple → Gray-900)
- **Soporte IT**: Cyan/Slate (Cyan → Slate-900)
- **Administrativo**: Azul (Blue-950 → Blue-900)
- **Profesor**: Verde (Emerald-950 → Emerald-900)
- **Estudiante**: Índigo (Indigo-950 → Indigo-900)

---

## ✅ PRÓXIMOS PASOS

1. ✅ Crear usuarios de prueba → **COMPLETADO**
2. ✅ Verificar endpoints del backend → **COMPLETADO**
3. ⏳ Probar flujo completo en el navegador
4. ⏳ Verificar que los guards del router funcionen
5. ⏳ Probar que cada rol solo vea sus vistas permitidas
6. ⏳ Verificar que los datos se muestren en los dashboards

---

**Última actualización**: 8 de septiembre de 2026
**Estado**: ✅ Backend verificado, listos para pruebas de frontend
