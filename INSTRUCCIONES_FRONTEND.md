# 🎨 Instrucciones para Iniciar el Frontend

## ⚠️ IMPORTANTE: Ejecutar en Orden

### 📋 Paso 1: Migrar la Base de Datos (SI AÚN NO LO HICISTE)

```bash
# Abrir terminal en la carpeta backend
cd C:\Users\danie\Documents\Gestion_uni\backend

# Ejecutar migraciones y seeders (10-20 minutos)
C:\xampp\php\php artisan migrate:fresh --seed
```

**Esperar a que termine** antes de continuar con el frontend.

---

### 🚀 Paso 2: Iniciar el Servidor Backend

En una **terminal separada**:

```bash
cd C:\Users\danie\Documents\Gestion_uni\backend
C:\xampp\php\php artisan serve
```

Deberías ver:
```
Server running on [http://127.0.0.1:8000]
```

**⚠️ NO CIERRES ESTA TERMINAL**

---

### 🎨 Paso 3: Iniciar el Frontend Vue.js

En **otra terminal**:

```bash
cd C:\Users\danie\Documents\Gestion_uni\frontend
npm run dev
```

Deberías ver:
```
VITE v5.x.x  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help
```

---

## 🌐 Acceso al Sistema

### URLs:

```
Frontend:    http://localhost:5173
Backend API: http://localhost:8000/api
phpMyAdmin:  http://localhost/phpmyadmin
```

### Credenciales:

```
Email:    admin@universidad.edu.ve
Password: Admin123!
```

---

## 🧪 Pasos para Probar

### 1. Abrir el Frontend

Ir a: **http://localhost:5173**

Verás la página de inicio de la universidad.

### 2. Click en "Aula Virtual"

Te llevará a la página de login.

### 3. Iniciar Sesión

```
Email:    admin@universidad.edu.ve
Password: Admin123!
```

Click en "Iniciar Sesión"

### 4. Explorar el Dashboard

Una vez logueado, verás el dashboard del sistema con:
- Estadísticas generales
- Acceso a estudiantes
- Acceso a horarios
- Otras funcionalidades

---

## 📊 Vistas Disponibles

El sistema tiene estas vistas principales:

1. **Home** (`/`) - Página de inicio pública
2. **Login** (`/login`) - Iniciar sesión
3. **Dashboard** (`/dashboard`) - Panel principal (requiere login)
4. **Carreras** (`/carreras`) - Lista de carreras
5. **Estudiantes** (`/estudiantes`) - Gestión de estudiantes
6. **Horarios** (`/horarios`) - Horarios disponibles
7. **Noticias** (`/noticias`) - Noticias de la universidad
8. **Contacto** (`/contacto`) - Información de contacto

---

## 🐛 Solución de Problemas

### Error: "ECONNREFUSED" en el frontend

**Causa:** El backend no está corriendo

**Solución:** Asegúrate de que `php artisan serve` esté corriendo en el puerto 8000

### Error: "Network Error" al hacer login

**Causa:** CORS o backend no responde

**Solución:**
1. Verificar que el backend esté corriendo
2. Verificar que el archivo `.env` tenga:
   ```
   FRONTEND_URL=http://localhost:5173
   ```

### El login no funciona

**Causa:** Usuario admin no existe

**Solución:** Verificar que el seeder se ejecutó correctamente:

```bash
cd backend
C:\xampp\php\php artisan db:seed --class=UserSeeder
```

### Puerto 5173 ocupado

**Solución:** Vite asignará automáticamente otro puerto (5174, 5175, etc.)

---

## 📱 Flujo Completo de Prueba

### 1. Primera Vez (Setup Completo)

```bash
# Terminal 1: Migrar DB
cd C:\Users\danie\Documents\Gestion_uni\backend
C:\xampp\php\php artisan migrate:fresh --seed
# Esperar 10-20 minutos

# Terminal 2: Backend
cd C:\Users\danie\Documents\Gestion_uni\backend
C:\xampp\php\php artisan serve

# Terminal 3: Frontend
cd C:\Users\danie\Documents\Gestion_uni\frontend
npm run dev
```

### 2. Días Siguientes (Solo Iniciar Servidores)

```bash
# Terminal 1: Backend
cd C:\Users\danie\Documents\Gestion_uni\backend
C:\xampp\php\php artisan serve

# Terminal 2: Frontend
cd C:\Users\danie\Documents\Gestion_uni\frontend
npm run dev
```

---

## 🎯 Funcionalidades para Probar

### En el Dashboard:

1. **Ver Estudiantes**
   - Lista paginada
   - Buscar por nombre
   - Filtrar por carrera
   - Ver detalles individuales

2. **Ver Horarios**
   - Lista de horarios del periodo actual
   - Filtrar por día
   - Ver cupos disponibles

3. **Ver Carreras**
   - Lista de todas las carreras
   - Ver estudiantes por carrera
   - Ver materias por carrera

---

## 📸 Capturas de Pantalla Esperadas

### 1. Home (/)
- Navbar azul con logo de la universidad
- Botón "Aula Virtual" amarillo
- Footer con información de contacto

### 2. Login (/login)
- Fondo degradado azul
- Card blanco con formulario
- Campos: Email y Contraseña
- Botón azul "Iniciar Sesión"

### 3. Dashboard (/dashboard)
- Sidebar con menú
- Header con usuario
- Tarjetas con estadísticas
- Tablas con datos

---

## 🔧 Comandos Útiles

### Backend

```bash
# Ver rutas disponibles
php artisan route:list

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Ver logs
tail -f storage/logs/laravel.log
```

### Frontend

```bash
# Instalar dependencias (si hay error)
npm install

# Limpiar y reinstalar
rm -rf node_modules
rm package-lock.json
npm install

# Build para producción
npm run build
```

---

## ✅ Checklist

Antes de considerar que todo funciona:

- [ ] Backend corriendo en puerto 8000
- [ ] Frontend corriendo en puerto 5173
- [ ] Login funciona con credenciales admin
- [ ] Dashboard carga después del login
- [ ] Lista de estudiantes muestra datos (100K registros)
- [ ] Lista de carreras muestra datos (~30 carreras)
- [ ] Lista de horarios muestra datos (15K horarios)
- [ ] Búsqueda funciona
- [ ] Paginación funciona

---

## 🆘 Si Nada Funciona

1. **Verificar MySQL/XAMPP esté corriendo**
2. **Verificar base de datos `sge_db` existe**
3. **Verificar que las tablas existen en phpMyAdmin**
4. **Reiniciar todo:**

```bash
# Cerrar todas las terminales

# Terminal 1: Backend
cd C:\Users\danie\Documents\Gestion_uni\backend
C:\xampp\php\php artisan serve

# Terminal 2: Frontend (esperar a que backend inicie)
cd C:\Users\danie\Documents\Gestion_uni\frontend
npm run dev
```

5. **Abrir navegador en modo incógnito** para evitar cache
6. **Ir a** http://localhost:5173

---

## 📞 Siguiente Paso

Una vez que todo funcione:
- Explorar el sistema
- Probar las búsquedas
- Ver los datos generados
- Reportar cualquier error que encuentres

¡Disfruta explorando el sistema! 🎉
