# 🚀 Inicio Rápido - Sistema de Gestión Estudiantil

## 📋 Requisitos Previos

- ✅ XAMPP instalado y corriendo (Apache + MySQL)
- ✅ PHP 8.2+ en el PATH (o usar el de XAMPP)
- ✅ Composer instalado
- ✅ Node.js 16+ instalado

---

## 🔧 Paso 1: Configurar Base de Datos

### 1.1 Crear Base de Datos en phpMyAdmin

1. Abrir: `http://localhost/phpmyadmin`
2. Click en "Nueva" (New)
3. Nombre: `sge_db`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Click en "Crear"

---

## 🎯 Paso 2: Ejecutar Migraciones y Seeders

### 2.1 Navegar al directorio backend

```bash
cd C:\Users\danie\Documents\Gestion_uni\backend
```

### 2.2 Ejecutar migraciones y seeders

**IMPORTANTE:** Este proceso tardará 10-20 minutos

```bash
# Si PHP está en el PATH
php artisan migrate:fresh --seed

# O usando PHP de XAMPP
C:\xampp\php\php artisan migrate:fresh --seed
```

**Esto creará:**
- ✅ 10 Facultades
- ✅ ~30 Carreras
- ✅ 5,000 Profesores
- ✅ 800 Aulas
- ✅ ~1,500 Materias
- ✅ 100,000 Estudiantes ⏱️ (el más lento)
- ✅ 15,000 Horarios
- ✅ ~250,000 Inscripciones ⏱️
- ✅ 1 Usuario Administrador

**Total: ~372,000 registros**

---

## 🌐 Paso 3: Iniciar Servidor Laravel

### 3.1 En una terminal en el directorio backend:

```bash
# Si PHP está en el PATH
php artisan serve

# O usando PHP de XAMPP
C:\xampp\php\php artisan serve
```

Deberías ver:
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

## 🔑 Credenciales de Acceso

### Usuario Administrador

```
Email:    admin@universidad.edu.ve
Password: Admin123!
```

---

## 🧪 Paso 4: Probar la API

### 4.1 URL Base de la API

```
http://localhost:8000/api
```

### 4.2 Endpoints Disponibles (48+ endpoints)

#### **Autenticación**

##### Login
```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "admin@universidad.edu.ve",
  "password": "Admin123!"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Administrador SGE",
    "email": "admin@universidad.edu.ve"
  },
  "token": "...",
  "token_type": "Bearer"
}
```

##### Obtener Usuario Actual
```bash
GET http://localhost:8000/api/me
```

##### Logout
```bash
POST http://localhost:8000/api/logout
```

---

#### **Facultades**

```bash
# Listar todas las facultades
GET http://localhost:8000/api/facultades

# Ver una facultad específica
GET http://localhost:8000/api/facultades/1
```

---

#### **Carreras**

```bash
# Listar todas las carreras
GET http://localhost:8000/api/carreras

# Buscar carreras
GET http://localhost:8000/api/carreras?buscar=Ingeniería

# Filtrar por facultad
GET http://localhost:8000/api/carreras?facultad_id=1

# Ver una carrera específica
GET http://localhost:8000/api/carreras/1

# Estudiantes de una carrera
GET http://localhost:8000/api/carreras/1/estudiantes

# Materias de una carrera
GET http://localhost:8000/api/carreras/1/materias
```

---

#### **Estudiantes**

```bash
# Listar estudiantes (paginado)
GET http://localhost:8000/api/estudiantes

# Buscar estudiante
GET http://localhost:8000/api/estudiantes?buscar=Juan

# Filtrar por carrera
GET http://localhost:8000/api/estudiantes?carrera_id=1

# Filtrar por semestre
GET http://localhost:8000/api/estudiantes?semestre=5

# Filtrar activos
GET http://localhost:8000/api/estudiantes?estatus=activo

# Ordenar
GET http://localhost:8000/api/estudiantes?sort=apellido&direction=asc

# Paginación
GET http://localhost:8000/api/estudiantes?per_page=100&page=2

# Ver estudiante específico
GET http://localhost:8000/api/estudiantes/1

# Inscripciones de un estudiante
GET http://localhost:8000/api/estudiantes/1/inscripciones

# Estadísticas de un estudiante
GET http://localhost:8000/api/estudiantes/1/estadisticas
```

---

#### **Profesores**

```bash
# Listar profesores
GET http://localhost:8000/api/profesores

# Buscar profesor
GET http://localhost:8000/api/profesores?buscar=Carlos

# Filtrar por facultad
GET http://localhost:8000/api/profesores?facultad_id=1

# Filtrar por categoría
GET http://localhost:8000/api/profesores?categoria=titular

# Ver profesor específico
GET http://localhost:8000/api/profesores/1
```

---

#### **Materias**

```bash
# Listar materias
GET http://localhost:8000/api/materias

# Filtrar por carrera
GET http://localhost:8000/api/materias?carrera_id=1

# Filtrar por semestre
GET http://localhost:8000/api/materias?semestre=1

# Filtrar por tipo
GET http://localhost:8000/api/materias?tipo=obligatoria

# Ver materia específica
GET http://localhost:8000/api/materias/1
```

---

#### **Aulas**

```bash
# Listar aulas
GET http://localhost:8000/api/aulas

# Filtrar por edificio
GET http://localhost:8000/api/aulas?edificio=A

# Filtrar por tipo
GET http://localhost:8000/api/aulas?tipo=laboratorio

# Filtrar por capacidad mínima
GET http://localhost:8000/api/aulas?capacidad_minima=40

# Ver aula específica
GET http://localhost:8000/api/aulas/1
```

---

#### **Horarios**

```bash
# Listar horarios del periodo actual (2026-1)
GET http://localhost:8000/api/horarios

# Filtrar por periodo
GET http://localhost:8000/api/horarios?periodo_academico=2026-1

# Filtrar por materia
GET http://localhost:8000/api/horarios?materia_id=1

# Filtrar por profesor
GET http://localhost:8000/api/horarios?profesor_id=1

# Filtrar por día
GET http://localhost:8000/api/horarios?dia_semana=lunes

# Solo con cupos disponibles
GET http://localhost:8000/api/horarios?con_cupos=1

# Ver horario específico
GET http://localhost:8000/api/horarios/1

# Estudiantes inscritos en un horario
GET http://localhost:8000/api/horarios/1/estudiantes

# Horarios disponibles para inscripción
GET http://localhost:8000/api/horarios/disponibles/listado?periodo_academico=2026-1
```

---

#### **Inscripciones**

```bash
# Listar inscripciones
GET http://localhost:8000/api/inscripciones

# Filtrar por estudiante
GET http://localhost:8000/api/inscripciones?estudiante_id=1

# Filtrar por horario
GET http://localhost:8000/api/inscripciones?horario_id=1

# Filtrar por periodo
GET http://localhost:8000/api/inscripciones?periodo_academico=2026-1

# Filtrar por estatus
GET http://localhost:8000/api/inscripciones?estatus=aprobado

# Ver inscripción específica
GET http://localhost:8000/api/inscripciones/1

# Crear inscripción
POST http://localhost:8000/api/inscripciones
Content-Type: application/json

{
  "estudiante_id": 1,
  "horario_id": 1,
  "periodo_academico": "2026-1"
}

# Retirar materia
POST http://localhost:8000/api/inscripciones/1/retirar
```

---

## 📊 Ejemplos de Consultas Útiles

### Buscar estudiantes de Ingeniería de Sistemas, semestre 5, activos

```bash
GET http://localhost:8000/api/estudiantes?carrera_id=1&semestre=5&estatus=activo&per_page=100
```

### Horarios de lunes con cupos disponibles

```bash
GET http://localhost:8000/api/horarios?dia_semana=lunes&con_cupos=1&periodo_academico=2026-1
```

### Profesores titulares de la Facultad de Ingeniería

```bash
GET http://localhost:8000/api/profesores?facultad_id=1&categoria=titular
```

### Materias obligatorias del primer semestre

```bash
GET http://localhost:8000/api/materias?semestre=1&tipo=obligatoria
```

---

## 🔍 Verificar que Todo Funciona

### Verificar registros creados

Abrir: `http://localhost/phpmyadmin`

Seleccionar base de datos: `sge_db`

Ejecutar estas consultas SQL:

```sql
-- Verificar totales
SELECT 'Facultades' as tabla, COUNT(*) as total FROM facultades
UNION ALL
SELECT 'Carreras', COUNT(*) FROM carreras
UNION ALL
SELECT 'Estudiantes', COUNT(*) FROM estudiantes
UNION ALL
SELECT 'Profesores', COUNT(*) FROM profesores
UNION ALL
SELECT 'Materias', COUNT(*) FROM materias
UNION ALL
SELECT 'Aulas', COUNT(*) FROM aulas
UNION ALL
SELECT 'Horarios', COUNT(*) FROM horarios
UNION ALL
SELECT 'Inscripciones', COUNT(*) FROM inscripciones
UNION ALL
SELECT 'Usuarios', COUNT(*) FROM users;
```

**Resultado esperado:**
```
Facultades      | 10
Carreras        | ~30
Estudiantes     | 100,000
Profesores      | 5,000
Materias        | ~1,500
Aulas           | 800
Horarios        | 15,000
Inscripciones   | ~250,000
Usuarios        | 1
```

---

## 🧪 Probar con Postman/Insomnia

### Importar Colección

Puedes usar estas herramientas para probar la API:

1. **Postman**: https://www.postman.com/downloads/
2. **Insomnia**: https://insomnia.rest/download
3. **Thunder Client** (extensión de VS Code)

### Crear colección manualmente

1. Crear nueva colección "SGE API"
2. Agregar los endpoints mostrados arriba
3. Guardar la URL base: `http://localhost:8000/api`

---

## 🐛 Solución de Problemas

### Error: "SQLSTATE[HY000] [1049] Unknown database 'sge_db'"

**Solución:** Crear la base de datos `sge_db` en phpMyAdmin

### Error: "php command not found"

**Solución:** Usar ruta completa de XAMPP:
```bash
C:\xampp\php\php artisan serve
```

### Error: "Out of memory"

**Solución:** Editar `C:\xampp\php\php.ini`:
```ini
memory_limit = 512M
max_execution_time = 600
```

### Los seeders tardan mucho

**Normal:** 10-20 minutos para 370K+ registros es esperado.

### Puerto 8000 ocupado

**Solución:** Usar otro puerto:
```bash
php artisan serve --port=8080
```

---

## 📱 Frontend (Próximo)

El frontend en Vue.js 3 se iniciará con:

```bash
cd C:\Users\danie\Documents\Gestion_uni\frontend
npm run dev
```

URL: `http://localhost:5173`

---

## 📚 Documentación Completa

- **API**: `backend/API_DOCUMENTATION.md`
- **Modelos**: `backend/MODELOS_ELOQUENT.md`
- **Base de Datos**: `backend/ESQUEMA_BASE_DATOS.md`
- **Factories**: `backend/FACTORIES_DOCUMENTATION.md`
- **Seeders**: `backend/SEEDERS_DOCUMENTATION.md`

---

## ✅ Checklist de Verificación

- [ ] Base de datos `sge_db` creada
- [ ] Migraciones ejecutadas correctamente
- [ ] Seeders completados (370K+ registros)
- [ ] Usuario admin creado
- [ ] Servidor Laravel corriendo en puerto 8000
- [ ] Endpoint `/api/login` funciona
- [ ] Endpoint `/api/estudiantes` retorna datos paginados
- [ ] Endpoint `/api/carreras` retorna 30+ carreras
- [ ] phpMyAdmin muestra todos los registros

---

## 🎉 ¡Listo!

Tu sistema está funcionando con:
- ✅ 100,000 estudiantes
- ✅ 5,000 profesores
- ✅ 15,000 horarios
- ✅ 250,000 inscripciones
- ✅ API REST completa (48+ endpoints)

**¡A probar! 🚀**
