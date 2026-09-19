# 🚀 Tutorial: Deploy Backend Laravel + PostgreSQL en Render

## Requisitos previos
- Cuenta en Render.com (gratuita)
- Repositorio GitHub actualizado (✅ Ya está listo)
- Datos de 6 usuarios demo funcionando localmente

---

## PARTE 1: Crear Base de Datos PostgreSQL en Render

### Paso 1.1: Acceder a Render Dashboard
1. Ve a https://render.com
2. Inicia sesión con tu cuenta
3. Click en **"New +"** → **"PostgreSQL"**

### Paso 1.2: Configurar PostgreSQL
Completa el formulario:

```
Name: gestion-uni-db
Database: gestion_uni_db
User: (se genera automático, ej: gestion_uni_db_user)
Region: Oregon (USA) - más cercano y estable
PostgreSQL Version: 16 (recomendado)
Instance Type: Free
```

4. Click en **"Create Database"**
5. **Espera 2-3 minutos** mientras se crea la base de datos
6. Verás el estado "Available" en verde cuando esté listo

### Paso 1.3: Obtener Credenciales de Conexión
En la página de tu base de datos, ve a la sección **"Connections"**:

Encontrarás dos tipos de conexiones:

**Internal Database URL** (para conectar desde Render):
```
postgres://gestion_uni_db_user:XXXXX@dpg-XXXXX/gestion_uni_db
```

**External Database URL** (para conectar desde local):
```
postgres://gestion_uni_db_user:XXXXX@dpg-XXXXX-a.oregon-postgres.render.com/gestion_uni_db
```

⚠️ **IMPORTANTE:** Guarda estas credenciales, las necesitarás después.

También verás los datos separados:
- **Host**: dpg-XXXXX-a.oregon-postgres.render.com
- **Database**: gestion_uni_db
- **Username**: gestion_uni_db_user
- **Password**: (una cadena larga)
- **Port**: 5432

---

## PARTE 2: Crear Web Service para el Backend Laravel

### Paso 2.1: Crear Nuevo Web Service
1. En Render Dashboard, click en **"New +"** → **"Web Service"**
2. Selecciona **"Build and deploy from a Git repository"**
3. Click en **"Connect account"** si es la primera vez
4. Autoriza a Render acceder a tu GitHub
5. Busca y selecciona el repositorio: **Alf-Dev1906/mockup_gestion**
6. Click en **"Connect"**

### Paso 2.2: Configurar Web Service
Completa el formulario con estos datos:

```
Name: gestion-uni-backend
Region: Oregon (USA) - DEBE ser la misma que la DB
Branch: main
Root Directory: backend
Runtime: PHP
Build Command: composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache
Start Command: php artisan serve --host=0.0.0.0 --port=$PORT
Instance Type: Free
```

### Paso 2.3: Configurar Variables de Entorno
En la sección **"Environment Variables"**, añade las siguientes:

**Variables básicas de Laravel:**
```env
APP_NAME="Gestion Universitaria"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://gestion-uni-backend.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=error
```

**Variables de Base de Datos PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=[TU_HOST_DE_RENDER]
DB_PORT=5432
DB_DATABASE=gestion_uni_db
DB_USERNAME=[TU_USERNAME_DE_RENDER]
DB_PASSWORD=[TU_PASSWORD_DE_RENDER]
```

⚠️ **Reemplaza los valores entre corchetes con los datos de tu PostgreSQL de Render (Paso 1.3)**

**Variables de Cache y Sessions:**
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
```

**Variables de CORS (importante para el frontend):**
```env
SANCTUM_STATEFUL_DOMAINS=gestion-uni-frontend.netlify.app,localhost:5173
SESSION_DOMAIN=.onrender.com
```

**Variables adicionales (opcional pero recomendado):**
```env
BCRYPT_ROUNDS=10
```

### Paso 2.4: Generar APP_KEY
Como no podemos ejecutar `php artisan key:generate` antes del deploy, genera una clave manualmente:

1. Abre una terminal local y ejecuta:
```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

2. Copia el resultado (ejemplo: `base64:ABC123XYZ...`)
3. Pégalo en la variable `APP_KEY` en Render

O usa esta clave pre-generada (solo para demo):
```
base64:kPZ5D0qJ8KvLxN2MwB3cR4fT5gU6hV7iW8xY9zA0bC1=
```

### Paso 2.5: Deploy Inicial
1. Click en **"Create Web Service"**
2. Render comenzará a construir e instalar el backend
3. Este proceso toma **5-10 minutos** la primera vez
4. Verás logs en tiempo real del build

**Logs esperados:**
```
Installing dependencies from lock file
...
Generating optimized autoload files
...
Configuration cached successfully!
Routes cached successfully!
...
Deploy succeeded!
```

---

## PARTE 3: Ejecutar Migraciones y Seeders

### Paso 3.1: Conectar a la Shell de Render
Una vez que el deploy esté completo (estado "Live"):

1. En la página de tu Web Service, ve a la pestaña **"Shell"**
2. Click en **"Launch Shell"**
3. Espera a que se abra una terminal interactiva

### Paso 3.2: Ejecutar Migraciones
En la shell de Render, ejecuta:

```bash
# Limpiar cache de configuración
php artisan config:clear

# Verificar conexión a DB
php artisan migrate:status

# Ejecutar migraciones
php artisan migrate --force

# Deberías ver:
# Migrating: 2014_10_12_000000_create_users_table
# Migrated: 2014_10_12_000000_create_users_table (XXX.XXms)
# ... (todas las migraciones)
```

### Paso 3.3: Ejecutar Seeders de Demo Data
```bash
# Seeder de usuarios base
php artisan db:seed --class=UserSeeder --force

# Seeder de datos de demo (96K estudiantes, 5K profesores, etc.)
php artisan db:seed --class=DemoSeeder --force

# Vincular usuarios demo
php artisan db:seed --class=LinkDemoUsersSeeder --force

# Solicitudes de admisión
php artisan db:seed --class=SolicitudesYPagosSeeder --force

# Pagos mockup
php artisan db:seed --class=PagosMockupSeeder --force

# Calificaciones mockup
php artisan db:seed --class=CalificacionesMockupSeeder --force

# Datos del profesor demo
php artisan db:seed --class=ProfesorDemoDataSeeder --force

# Datos del estudiante demo
php artisan db:seed --class=EstudianteDemoDataSeeder --force
```

⏱️ **Tiempo estimado:** 10-15 minutos para todos los seeders

### Paso 3.4: Verificar Datos
```bash
# Verificar usuarios
php artisan tinker
>>> \App\Models\User::count();
# Debe devolver: 6

>>> \App\Models\Estudiante::count();
# Debe devolver: 96000+

>>> \App\Models\Profesor::count();
# Debe devolver: 5000+

>>> exit
```

---

## PARTE 4: Configurar Almacenamiento (Storage)

### Paso 4.1: Crear Link de Storage
En la Shell de Render:

```bash
# Crear symbolic link para storage público
php artisan storage:link

# Verificar permisos
chmod -R 775 storage bootstrap/cache
```

---

## PARTE 5: Verificar Funcionamiento

### Paso 5.1: Probar Endpoints Básicos
Desde tu navegador o Postman:

**Verificar que el backend responde:**
```
https://gestion-uni-backend.onrender.com/api/health
```

**Respuesta esperada:**
```json
{
  "status": "ok",
  "database": "connected"
}
```

Si no existe ese endpoint, prueba:
```
https://gestion-uni-backend.onrender.com/api/facultades
```

### Paso 5.2: Probar Login
**Endpoint:**
```
POST https://gestion-uni-backend.onrender.com/api/login
```

**Body (JSON):**
```json
{
  "email": "developer@universidad.edu.ve",
  "password": "Dev2026!"
}
```

**Respuesta esperada:**
```json
{
  "access_token": "1|XXXXX...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Desarrollador",
    "email": "developer@universidad.edu.ve",
    "role": "desarrollador"
  }
}
```

---

## PARTE 6: Configurar Custom Domain (Opcional)

Si tienes un dominio propio:

1. Ve a la configuración de tu Web Service
2. En la sección **"Settings"** → **"Custom Domain"**
3. Añade tu dominio (ej: `api.tudominio.com`)
4. Sigue las instrucciones para configurar los DNS

---

## PARTE 7: Monitoreo y Mantenimiento

### Logs en Tiempo Real
1. Ve a tu Web Service en Render
2. Click en pestaña **"Logs"**
3. Aquí verás todos los errores y solicitudes en tiempo real

### Métricas
1. Pestaña **"Metrics"**
2. Verás CPU, memoria y ancho de banda

### Redeploy Manual
Si necesitas redesplegar:
1. Pestaña **"Manual Deploy"**
2. Click en **"Deploy latest commit"**

---

## PARTE 8: Solución de Problemas Comunes

### ❌ Error: "APP_KEY not set"
**Solución:** Genera una nueva APP_KEY (Paso 2.4) y actualiza la variable de entorno

### ❌ Error: "could not connect to database"
**Solución:** Verifica que el host, username y password sean correctos. Debe ser la "Internal Database URL"

### ❌ Error: "419 Page Expired" en frontend
**Solución:** Verifica las variables SANCTUM_STATEFUL_DOMAINS y SESSION_DOMAIN

### ❌ Error: "502 Bad Gateway"
**Solución:** El servidor está iniciando, espera 2-3 minutos

### ❌ Base de datos vacía después de seeders
**Solución:** Verifica en los logs que los seeders se ejecutaron sin errores

---

## 📝 Checklist Final

Antes de continuar con el frontend, verifica:

- [ ] PostgreSQL database está "Available" (verde)
- [ ] Web Service está "Live" (verde)
- [ ] Variables de entorno configuradas correctamente
- [ ] Migraciones ejecutadas sin errores
- [ ] Seeders ejecutados sin errores (6 usuarios, 96K+ estudiantes)
- [ ] Endpoint `/api/login` responde correctamente
- [ ] Puedes ver logs del servidor sin errores críticos

---

## 🔗 URLs Importantes

Guarda estas URLs para el siguiente paso (configuración del frontend):

**Backend API URL:**
```
https://gestion-uni-backend.onrender.com
```

**PostgreSQL External URL (para conexión local):**
```
[Tu External Database URL del Paso 1.3]
```

---

## 🎯 Credenciales de Usuarios Demo

Una vez completado el deploy, tendrás estos usuarios disponibles:

| Email | Password | Rol |
|-------|----------|-----|
| developer@universidad.edu.ve | Dev2026! | Desarrollador |
| soporte@universidad.edu.ve | Soporte2026! | Soporte IT |
| admin@universidad.edu.ve | Admin2026! | Administrativo |
| profesor@universidad.edu.ve | Admin2026! | Profesor |
| estudiante@universidad.edu.ve | Admin2026! | Estudiante |
| solicitante@universidad.edu.ve | Admin2026! | Solicitante |

---

## ⏭️ Siguiente Paso

Una vez que todo funcione correctamente, continúa con:
**Tutorial de Deploy del Frontend en Netlify**

---

**¿Necesitas ayuda?**
- Render Docs: https://render.com/docs
- Laravel Deploy Docs: https://laravel.com/docs/deployment
