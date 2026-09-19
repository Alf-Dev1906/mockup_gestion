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
Ahora verás un formulario con varios campos. Completa cada uno siguiendo estas instrucciones:

#### Campo 1: **Name** (Nombre del servicio)
- **Qué escribir:** `gestion-uni-backend`
- **Descripción:** Este será el nombre de tu backend y parte de la URL

#### Campo 2: **Region** (Región del servidor)
- **Qué seleccionar:** `Oregon (USA)`
- **⚠️ IMPORTANTE:** DEBE ser la misma región que tu base de datos (Paso 1.2)
- **Por qué:** Para menor latencia entre el backend y la database

#### Campo 3: **Branch** (Rama de Git)
- **Qué escribir:** `main`
- **Descripción:** La rama principal de tu repositorio

#### Campo 4: **Root Directory** (Carpeta raíz)
- **Qué escribir:** `backend`
- **Descripción:** Como tu proyecto Laravel está en la carpeta `backend/`, Render debe buscar ahí

#### Campo 5: **Runtime** (Entorno de ejecución)
- **⚠️ CAMBIO IMPORTANTE:** Selecciona **`Docker`** de la lista desplegable
- **Por qué:** Render no tiene PHP nativo, pero Laravel puede correr en Docker
- **Opciones que verás:** Docker, Elixir, Go, Node, Python 3, Ruby, Rust
- **Selecciona:** Docker (primera opción de la lista)

### Paso 2.3: Configurar Variables de Entorno

Después de seleccionar Docker, **haz scroll hacia abajo** en la misma página hasta encontrar la sección **"Environment Variables"**.

#### 📍 Ubicación visual:
- Está debajo de los campos que acabas de llenar
- Verás un título grande que dice **"Environment Variables"**
- Debajo hay un botón azul que dice **"Add Environment Variable"**

#### Cómo añadir las variables:

**Paso 1:** Click en el botón **"Add Environment Variable"**

**Paso 2:** Se abrirá un formulario con dos campos:
- **Key** (Clave): Aquí va el nombre de la variable (ej: `APP_NAME`)
- **Value** (Valor): Aquí va el valor (ej: `Gestion Universitaria`)

**Paso 3:** Añade TODAS estas variables una por una:

#### ✅ Grupo 1: Variables Básicas de Laravel
Click en **"Add Environment Variable"** y añade:

**Variable 1:**
```
Key: APP_NAME
Value: Gestion Universitaria
```
Click en **"Save"** o presiona Enter

**Variable 2:**
```
Key: APP_ENV
Value: production
```

**Variable 3:**
```
Key: APP_KEY
Value: (déjalo VACÍO por ahora, lo llenaremos en el Paso 2.4)
```

**Variable 4:**
```
Key: APP_DEBUG
Value: false
```

**Variable 5:**
```
Key: APP_URL  
Value: https://gestion-uni-backend.onrender.com
```
⚠️ **Nota:** Este será tu URL final, Render lo asignará automáticamente

**Variable 6:**
```
Key: LOG_CHANNEL
Value: stack
```

**Variable 7:**
```
Key: LOG_LEVEL
Value: error
```

---

#### ✅ Grupo 2: Variables de Base de Datos PostgreSQL

**⚠️ IMPORTANTE:** Aquí necesitas los datos del Paso 1.3. Abre en otra pestaña la página de tu PostgreSQL en Render para copiar los valores.

**Variable 8:**
```
Key: DB_CONNECTION
Value: pgsql
```

**Variable 9:**
```
Key: DB_HOST
Value: [COPIA el HOST de tu PostgreSQL]
```
**Dónde encontrarlo:**
1. Ve a tu PostgreSQL en Render
2. Click en la pestaña **"Info"**
3. En la sección **"Connections"**, copia el valor de **"Hostname"**
4. Ejemplo: `dpg-abc123-a.oregon-postgres.render.com`

**Variable 10:**
```
Key: DB_PORT
Value: 5432
```

**Variable 11:**
```
Key: DB_DATABASE
Value: gestion_uni_db
```

**Variable 12:**
```
Key: DB_USERNAME
Value: [COPIA el USERNAME de tu PostgreSQL]
```
**Dónde encontrarlo:**
1. Misma página de tu PostgreSQL
2. Sección **"Connections"**
3. Copia el valor de **"Username"**
4. Ejemplo: `gestion_uni_db_user`

**Variable 13:**
```
Key: DB_PASSWORD
Value: [COPIA el PASSWORD de tu PostgreSQL]
```
**Dónde encontrarlo:**
1. Misma página de tu PostgreSQL
2. Sección **"Connections"**
3. Copia el valor de **"Password"**
4. Es una cadena larga, ejemplo: `abc123xyz789...`

---

#### ✅ Grupo 3: Variables de Cache y Sessions

**Variable 14:**
```
Key: CACHE_DRIVER
Value: file
```

**Variable 15:**
```
Key: SESSION_DRIVER
Value: file
```

**Variable 16:**
```
Key: SESSION_LIFETIME
Value: 120
```

**Variable 17:**
```
Key: QUEUE_CONNECTION
Value: sync
```

---

#### ✅ Grupo 4: Variables de CORS (para el frontend)

**Variable 18:**
```
Key: SANCTUM_STATEFUL_DOMAINS
Value: gestion-uni-frontend.netlify.app,localhost:5173
```

**Variable 19:**
```
Key: SESSION_DOMAIN
Value: .onrender.com
```

**Variable 20:**
```
Key: BCRYPT_ROUNDS
Value: 10
```

---

### Paso 2.4: Generar APP_KEY de Laravel

Ahora necesitamos generar una clave de encriptación para Laravel.

#### Opción A: Generar desde tu terminal local

1. **Abre PowerShell** en tu computadora
2. **Navega** a la carpeta del proyecto:
   ```bash
   cd C:\Users\danie\Documents\Gestion_uni\backend
   ```
3. **Ejecuta:**
   ```bash
   php artisan key:generate --show
   ```
4. **Copia** el resultado (ejemplo: `base64:kPZ5D0qJ8KvLxN...`)

#### Opción B: Usar esta clave pre-generada (solo para demo)
```
base64:kPZ5D0qJ8KvLxN2MwB3cR4fT5gU6hV7iW8xY9zA0bC1=
```

#### Ahora añade la clave:
1. **Vuelve** a la página de Render donde están las variables de entorno
2. **Busca** la variable `APP_KEY` que dejamos vacía
3. **Click** en el ícono de lápiz ✏️ al lado de `APP_KEY`
4. **Pega** la clave generada en el campo **Value**
5. **Click** en **"Save"**

---

### Paso 2.5: Verificar Configuración Antes de Deploy

Antes de hacer click en el botón final, **verifica que todo esté correcto:**

#### Checklist de configuración:
- [ ] Name: `gestion-uni-backend` ✓
- [ ] Region: `Oregon (USA)` ✓
- [ ] Branch: `main` ✓
- [ ] Root Directory: `backend` ✓
- [ ] Runtime: `Docker` ✓
- [ ] **20 variables de entorno** añadidas ✓
- [ ] `APP_KEY` tiene un valor (no está vacío) ✓
- [ ] Credenciales de PostgreSQL son correctas ✓

#### Haz scroll hacia abajo hasta el final de la página

Verás una sección que dice **"Instance Type"**:
- **Selecciona:** `Free`
- **Descripción:** 750 horas gratis al mes (suficiente para demo)

---

### Paso 2.6: Iniciar Deploy

¡Ahora sí! Todo está configurado.

1. **Haz scroll** hasta el final de la página
2. Verás un botón azul grande que dice **"Create Web Service"**
3. **Click** en ese botón
4. Render te llevará a una nueva página que muestra:
   - El **logo de carga** girando
   - Un panel de **logs en tiempo real**
   - El estado actual del deploy

#### 📺 Qué verás durante el deploy:

**Fase 1: Building (Construyendo) - 3-5 minutos**
```
==> Cloning from GitHub...
==> Checking out commit abc123...
==> Building Docker image...
Step 1/10 : FROM php:8.2-cli
...
```

**Fase 2: Installing (Instalando) - 2-3 minutos**
```
Installing dependencies from composer.json
...
Generating optimized autoload files
```

**Fase 3: Starting (Iniciando) - 1 minuto**
```
Laravel development server started: http://0.0.0.0:8080
```

**✅ Deploy Completo:**
Verás en la parte superior un badge verde que dice **"Live"**

⏱️ **Tiempo total:** 5-10 minutos

---

## PARTE 3: Ejecutar Migraciones y Seeders

### Paso 3.1: Acceder a la Shell de Render

Una vez que el deploy esté completo y veas el badge **"Live"** en verde:

#### 📍 Dónde hacer click:

1. **Estás en la página de tu Web Service** (gestion-uni-backend)
2. **Mira la parte superior** de la página, verás varias pestañas:
   ```
   [Events] [Logs] [Shell] [Metrics] [Settings]
   ```
3. **Click en la pestaña "Shell"** (la tercera desde la izquierda)

#### Qué verás:

Una página con:
- Un título que dice **"Shell"**
- Una descripción: "Connect to a shell on your service"
- Un botón azul grande que dice **"Launch Shell"**

4. **Click en "Launch Shell"**

#### Espera 10-15 segundos mientras se abre la terminal

Verás:
```
Connecting to shell...
Connected!
~ $
```

El cursor parpadeante `$` indica que puedes escribir comandos.

---

### Paso 3.2: Verificar Entorno y Conexión

Antes de ejecutar migraciones, verifica que todo esté bien:

#### Comando 1: Verificar versión de PHP
Escribe y presiona Enter:
```bash
php -v
```

**Resultado esperado:**
```
PHP 8.2.x (cli) (built: ...)
```

#### Comando 2: Verificar que estás en el directorio correcto
```bash
pwd
```

**Resultado esperado:**
```
/var/www
```

#### Comando 3: Limpiar cache de configuración
```bash
php artisan config:clear
```

**Resultado esperado:**
```
Configuration cache cleared successfully!
```

#### Comando 4: Verificar conexión a la base de datos
```bash
php artisan migrate:status
```

**Resultado esperado:**
```
Migration table not found.
```
Esto es NORMAL en la primera vez, significa que la BD está vacía y lista.

**Si ves un error de conexión:**
```
SQLSTATE[08006] Could not connect to server
```
⛔ **DETENTE:** Las credenciales de DB están mal. Ve al Paso 2.3 y verifica.

---

### Paso 3.3: Ejecutar Migraciones (Crear Tablas)

Ahora vamos a crear todas las tablas en PostgreSQL.

#### Comando:
```bash
php artisan migrate --force
```

**⏱️ Tiempo:** 30-60 segundos

#### Qué verás (ejemplo real):
```
   INFO  Preparing database.

  Creating migration table ............................. 12ms DONE

   INFO  Running migrations.

  2014_10_12_000000_create_users_table ................. 85ms DONE
  2014_10_12_100000_create_password_reset_tokens_table . 45ms DONE
  2019_08_19_000000_create_failed_jobs_table ........... 52ms DONE
  2019_12_14_000001_create_personal_access_tokens ...... 78ms DONE
  2025_01_01_000001_create_facultades_table ............ 38ms DONE
  2025_01_01_000002_create_carreras_table .............. 55ms DONE
  2025_01_01_000003_create_materias_table .............. 62ms DONE
  2025_01_01_000004_create_aulas_table ................. 41ms DONE
  2025_01_01_000005_create_profesores_table ............ 69ms DONE
  2025_01_01_000006_create_estudiantes_table ........... 98ms DONE
  2025_01_01_000007_create_horarios_table .............. 85ms DONE
  2025_01_01_000008_create_inscripciones_table ......... 92ms DONE
  2025_01_01_000009_create_calificaciones_table ........ 74ms DONE
  2025_01_01_000010_create_quizzes_table ............... 81ms DONE
  ... (y más tablas)
```

✅ **Éxito:** Verás `DONE` en todas las líneas

---

### Paso 3.4: Ejecutar Seeders (Llenar con Datos)

Ahora vamos a llenar la base de datos con datos de demostración.

#### ⚠️ IMPORTANTE: Orden de los seeders
Los seeders DEBEN ejecutarse en este orden exacto, uno por uno.

---

#### Seeder 1: UserSeeder (Crear 6 usuarios)
```bash
php artisan db:seed --class=UserSeeder --force
```

**⏱️ Tiempo:** 5 segundos

**Resultado esperado:**
```
   INFO  Seeding database.

✅ 6 usuarios creados exitosamente
   - developer@universidad.edu.ve (desarrollador)
   - soporte@universidad.edu.ve (soporte_it)
   - admin@universidad.edu.ve (administrativo)
   - profesor@universidad.edu.ve (profesor)
   - estudiante@universidad.edu.ve (estudiante)
   - solicitante@universidad.edu.ve (estudiante)
```

---

#### Seeder 2: DemoSeeder (Crear 96K estudiantes, 5K profesores, etc.)
```bash
php artisan db:seed --class=DemoSeeder --force
```

**⏱️ Tiempo:** 8-12 minutos ⏳

**⚠️ NO CIERRES LA TERMINAL** durante este proceso

**Qué verás:**
```
   INFO  Seeding database.

🎓 Creando 40 facultades...
✅ 40 facultades creadas

🎓 Creando 200 carreras...
✅ 200 carreras creadas

📚 Creando 2000 materias...
✅ 2000 materias creadas

... (mucha información)

👥 Creando 96000 estudiantes...
[====================>  ] 50% (48000/96000) - 4 min restantes
...
✅ 96000 estudiantes creados
```

**Verás barras de progreso** para los procesos largos.

✅ **Éxito:** Al final verás un resumen con la cantidad de registros creados

---

#### Seeder 3: LinkDemoUsersSeeder (Vincular usuarios con tablas)
```bash
php artisan db:seed --class=LinkDemoUsersSeeder --force
```

**⏱️ Tiempo:** 5 segundos

**Resultado esperado:**
```
   INFO  Seeding database.

🔗 Vinculando usuarios demo con sus tablas correspondientes...

✓ Usuario ESTUDIANTE vinculado (user_id: 5, estudiante_id: 96001)
✓ Usuario SOLICITANTE vinculado (user_id: 6, estudiante_id: 96002)
✓ Usuario PROFESOR vinculado (user_id: 4, profesor_id: 5001)
✓ Usuarios ADMIN, SOPORTE y DESARROLLADOR no requieren vinculación

✅ Vinculación completada!
```

---

#### Seeder 4: SolicitudesYPagosSeeder (20 solicitudes de admisión)
```bash
php artisan db:seed --class=SolicitudesYPagosSeeder --force
```

**⏱️ Tiempo:** 10 segundos

**Resultado esperado:**
```
✅ 20 solicitudes de admisión creadas:
   - Estado pendiente: 8
   - Estado en_revision: 6
   - Estado aprobada: 4
   - Estado rechazada: 2
```

---

#### Seeder 5: PagosMockupSeeder (448 pagos)
```bash
php artisan db:seed --class=PagosMockupSeeder --force
```

**⏱️ Tiempo:** 15 segundos

**Resultado esperado:**
```
✅ 448 pagos creados exitosamente.
   - Pendientes: 137
   - Pagados: 168
   - Verificados: 143
```

---

#### Seeder 6: CalificacionesMockupSeeder (404+ calificaciones)
```bash
php artisan db:seed --class=CalificacionesMockupSeeder --force
```

**⏱️ Tiempo:** 20 segundos

**Resultado esperado:**
```
✅ 404 calificaciones creadas exitosamente
```

---

#### Seeder 7: ProfesorDemoDataSeeder (Tareas, exámenes, asistencia)
```bash
php artisan db:seed --class=ProfesorDemoDataSeeder --force
```

**⏱️ Tiempo:** 30 segundos

**Resultado esperado:**
```
📚 Creando datos para 8 horarios...
✅ Datos creados exitosamente:
   - Tareas: 21 (con 8 entregas)
   - Exámenes: 17 (con 11 intentos)
   - Sesiones de asistencia: 28 (con 54 registros)
```

---

#### Seeder 8: EstudianteDemoDataSeeder (Contenido del estudiante)
```bash
php artisan db:seed --class=EstudianteDemoDataSeeder --force
```

**⏱️ Tiempo:** 30 segundos

**Resultado esperado:**
```
📚 Creando datos para 6 materias inscritas...
✅ Datos creados exitosamente:
   - Tareas: 13 (con 3 entregas)
   - Exámenes: 15 (con 5 intentos)
```

---

### Paso 3.5: Verificar que Todo se Creó Correctamente

Ahora vamos a verificar que todos los datos estén en la base de datos.

#### Comando para abrir Laravel Tinker (consola interactiva):
```bash
php artisan tinker
```

**Verás:**
```
Psy Shell v0.11.x (PHP 8.2.x)
>>>
```

El cursor `>>>` indica que puedes escribir comandos de PHP/Laravel.

#### Verificación 1: Contar usuarios
Escribe y presiona Enter:
```php
\App\Models\User::count();
```

**Resultado esperado:** `6`

#### Verificación 2: Contar estudiantes
```php
\App\Models\Estudiante::count();
```

**Resultado esperado:** `96002` (96,000 del demo + 2 demo users)

#### Verificación 3: Contar profesores
```php
\App\Models\Profesor::count();
```

**Resultado esperado:** `5001` (5,000 del demo + 1 demo user)

#### Verificación 4: Contar inscripciones
```php
\App\Models\Inscripcion::count();
```

**Resultado esperado:** `138000+`

#### Verificación 5: Listar emails de usuarios demo
```php
\App\Models\User::pluck('email');
```

**Resultado esperado:**
```php
[
   "developer@universidad.edu.ve",
   "soporte@universidad.edu.ve",
   "admin@universidad.edu.ve",
   "profesor@universidad.edu.ve",
   "estudiante@universidad.edu.ve",
   "solicitante@universidad.edu.ve",
]
```

#### Salir de Tinker:
```php
exit
```

Volverás a ver el prompt normal: `~ $`

✅ **Si todos los números coinciden, ¡todo está perfecto!**

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
