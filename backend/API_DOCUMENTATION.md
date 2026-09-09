# Documentación de API - Sistema de Gestión Estudiantil

## Resumen

API REST completa con **48+ endpoints** para gestionar todas las entidades del sistema educativo. Optimizada para alta concurrencia con paginación, filtros, eager loading y validación robusta.

---

## Características Generales

### ✨ Funcionalidades Implementadas

- ✅ **CRUD completo** para 8 entidades
- ✅ **Paginación optimizada** (50-100 registros/página)
- ✅ **Filtros múltiples** por campo
- ✅ **Búsqueda global** por términos
- ✅ **Eager loading** de relaciones
- ✅ **Validación robusta** con mensajes en español
- ✅ **Soft deletes** en todas las entidades
- ✅ **Endpoints especiales** (estadísticas, relaciones)
- ✅ **Responses JSON consistentes**
- ✅ **Códigos HTTP apropiados**

### 📊 Performance

- Paginación por defecto: **50 registros**
- Máximo por página: **100 registros**
- Eager loading automático de relaciones críticas
- Índices de BD optimizados para consultas

---

## Base URL

```
http://localhost:8000/api
```

---

## Autenticación

### POST `/login`
Iniciar sesión (próximamente con Sanctum).

### POST `/logout`
Cerrar sesión.

### GET `/me`
Obtener usuario autenticado.

---

## Endpoints por Entidad

## 1. Facultades

### `GET /api/facultades`
Lista todas las facultades.

**Query Params:**
- `buscar`: Buscar por nombre o código
- `todas`: Incluir inactivas (default: solo activas)

**Response:**
```json
[
  {
    "id": 1,
    "codigo": "ING",
    "nombre": "Facultad de Ingeniería",
    "decano": "Dr. Carlos Rodríguez",
    "email": "ing@universidad.edu.ve",
    "activo": true
  }
]
```

### `GET /api/facultades/{id}`
Obtener una facultad con sus carreras, profesores y aulas.

### `POST /api/facultades`
Crear nueva facultad.

**Body:**
```json
{
  "codigo": "ING",
  "nombre": "Facultad de Ingeniería",
  "decano": "Dr. Carlos Rodríguez",
  "email": "ing@universidad.edu.ve"
}
```

### `PUT /api/facultades/{id}`
Actualizar facultad.

### `DELETE /api/facultades/{id}`
Eliminar facultad (soft delete).

---

## 2. Carreras

### `GET /api/carreras`
Lista paginada de carreras.

**Query Params:**
- `buscar`: Buscar por nombre, código o título
- `facultad_id`: Filtrar por facultad
- `modalidad`: presencial, semipresencial, distancia
- `todas`: Incluir inactivas
- `per_page`: Registros por página (max 100)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "codigo": "ISI",
      "nombre": "Ingeniería de Sistemas",
      "titulo_otorgado": "Ingeniero de Sistemas",
      "duracion_semestres": 10,
      "creditos_totales": 200,
      "facultad": {
        "id": 1,
        "nombre": "Facultad de Ingeniería"
      }
    }
  ],
  "current_page": 1,
  "per_page": 50,
  "total": 120
}
```

### `GET /api/carreras/{id}`
Obtener carrera con facultad y materias.

### `GET /api/carreras/{id}/estudiantes`
Lista estudiantes de una carrera.

**Query Params:**
- `estatus`: activo, inactivo, egresado, etc.
- `per_page`: Registros por página

### `GET /api/carreras/{id}/materias`
Lista materias de una carrera.

**Query Params:**
- `semestre`: Filtrar por semestre
- `tipo`: obligatoria, electiva, especialidad

### `POST /api/carreras`
Crear nueva carrera.

### `PUT /api/carreras/{id}`
Actualizar carrera.

### `DELETE /api/carreras/{id}`
Eliminar carrera.

---

## 3. Estudiantes

### `GET /api/estudiantes`
Lista paginada de estudiantes.

**Query Params:**
- `buscar`: Nombre, apellido, cédula, matrícula, email
- `carrera_id`: Filtrar por carrera
- `semestre`: Filtrar por semestre actual
- `estatus`: activo, inactivo, egresado, retirado, suspendido
- `sort`: Ordenar por campo (apellido, nombre, matricula, semestre_actual, indice_academico)
- `direction`: asc, desc
- `per_page`: Registros por página

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "cedula": "V-25123456",
      "nombre": "Juan",
      "apellido": "Pérez",
      "email": "juan.perez@estudiantes.edu.ve",
      "matricula": "1234567890",
      "semestre_actual": 5,
      "indice_academico": 16.50,
      "creditos_aprobados": 85,
      "estatus": "activo",
      "carrera": {
        "id": 1,
        "nombre": "Ingeniería de Sistemas",
        "facultad": {
          "nombre": "Facultad de Ingeniería"
        }
      }
    }
  ],
  "current_page": 1,
  "per_page": 50,
  "total": 100000
}
```

### `GET /api/estudiantes/{id}`
Obtener estudiante con todas sus relaciones.

### `GET /api/estudiantes/{id}/inscripciones`
Lista inscripciones del estudiante.

**Query Params:**
- `periodo_academico`: Filtrar por periodo (ej: 2026-1)

### `GET /api/estudiantes/{id}/estadisticas`
Estadísticas académicas del estudiante.

**Response:**
```json
{
  "indice_academico": 16.50,
  "creditos_aprobados": 85,
  "semestre_actual": 5,
  "total_materias_inscritas": 25,
  "materias_aprobadas": 20,
  "materias_reprobadas": 2,
  "materias_cursando": 5
}
```

### `POST /api/estudiantes`
Crear nuevo estudiante.

**Validaciones:**
- cedula: única
- email: único, formato email
- matricula: única
- fecha_nacimiento: mayor de 18 años
- semestre_actual: 1-12

### `PUT /api/estudiantes/{id}`
Actualizar estudiante.

### `DELETE /api/estudiantes/{id}`
Eliminar estudiante.

---

## 4. Profesores

### `GET /api/profesores`
Lista paginada de profesores.

**Query Params:**
- `buscar`: Nombre, apellido, cédula, código empleado, email
- `facultad_id`: Filtrar por facultad
- `categoria`: instructor, asistente, agregado, asociado, titular
- `especialidad`: Buscar por especialidad
- `todos`: Incluir inactivos
- `per_page`: Registros por página

### `GET /api/profesores/{id}`
Obtener profesor con facultad y horarios.

### `POST /api/profesores`
Crear nuevo profesor.

### `PUT /api/profesores/{id}`
Actualizar profesor.

### `DELETE /api/profesores/{id}`
Eliminar profesor.

---

## 5. Materias

### `GET /api/materias`
Lista paginada de materias.

**Query Params:**
- `buscar`: Nombre, código, descripción
- `carrera_id`: Filtrar por carrera
- `semestre`: Filtrar por semestre recomendado
- `tipo`: obligatoria, electiva, especialidad
- `todas`: Incluir inactivas
- `per_page`: Registros por página

### `GET /api/materias/{id}`
Obtener materia con carrera, prerrequisitos y horarios.

### `POST /api/materias`
Crear nueva materia.

### `PUT /api/materias/{id}`
Actualizar materia.

### `DELETE /api/materias/{id}`
Eliminar materia.

---

## 6. Aulas

### `GET /api/aulas`
Lista paginada de aulas.

**Query Params:**
- `buscar`: Nombre, código, edificio
- `facultad_id`: Filtrar por facultad
- `edificio`: Filtrar por edificio
- `tipo`: aula_regular, laboratorio, auditorio, salon_conferencias, taller
- `capacidad_minima`: Capacidad mínima requerida
- `todas`: Incluir no disponibles
- `per_page`: Registros por página

### `GET /api/aulas/{id}`
Obtener aula con facultad y horarios.

### `POST /api/aulas`
Crear nueva aula.

### `PUT /api/aulas/{id}`
Actualizar aula.

### `DELETE /api/aulas/{id}`
Eliminar aula.

---

## 7. Horarios

### `GET /api/horarios`
Lista paginada de horarios.

**Query Params:**
- `buscar`: Buscar por materia o profesor
- `periodo_academico`: Filtrar por periodo (default: 2026-1)
- `materia_id`: Filtrar por materia
- `profesor_id`: Filtrar por profesor
- `dia_semana`: lunes, martes, miércoles, jueves, viernes, sábado
- `estatus`: abierto, cerrado, en_curso, finalizado, cancelado
- `con_cupos`: Solo horarios con cupos disponibles
- `per_page`: Registros por página

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "seccion": "A",
      "periodo_academico": "2026-1",
      "dia_semana": "lunes",
      "hora_inicio": "08:00",
      "hora_fin": "10:00",
      "cupo_maximo": 40,
      "cupo_actual": 35,
      "estatus": "en_curso",
      "materia": {
        "codigo": "ISI123",
        "nombre": "Programación I"
      },
      "profesor": {
        "nombre_completo": "Dr. Carlos Rodríguez"
      },
      "aula": {
        "codigo": "A-101",
        "nombre": "Aula A-101"
      }
    }
  ]
}
```

### `GET /api/horarios/{id}`
Obtener horario con todas sus relaciones e inscripciones.

### `GET /api/horarios/disponibles/listado`
Lista horarios disponibles para inscripción.

**Query Params:**
- `periodo_academico`: Periodo (default: 2026-1)
- `carrera_id`: Filtrar por carrera

### `GET /api/horarios/{id}/estudiantes`
Lista estudiantes inscritos en el horario.

**Query Params:**
- `estatus`: Filtrar por estatus de inscripción

### `POST /api/horarios`
Crear nuevo horario.

**Validaciones:**
- Verifica conflictos de aula
- Verifica conflictos de profesor
- Valida rangos de horario

### `PUT /api/horarios/{id}`
Actualizar horario.

### `DELETE /api/horarios/{id}`
Eliminar horario (solo si no tiene inscripciones).

---

## 8. Inscripciones

### `GET /api/inscripciones`
Lista paginada de inscripciones.

**Query Params:**
- `estudiante_id`: Filtrar por estudiante
- `horario_id`: Filtrar por horario
- `periodo_academico`: Filtrar por periodo
- `estatus`: inscrito, retirado, aprobado, reprobado, cursando, lista_espera
- `per_page`: Registros por página

### `GET /api/inscripciones/{id}`
Obtener inscripción con todas sus relaciones.

### `POST /api/inscripciones`
Crear nueva inscripción.

**Validaciones:**
- Verifica cupos disponibles
- Evita inscripciones duplicadas
- Valida que el estudiante y horario existan

### `PUT /api/inscripciones/{id}`
Actualizar inscripción (típicamente para registrar notas).

**Body:**
```json
{
  "estatus": "aprobado",
  "nota_parcial_1": 18.50,
  "nota_parcial_2": 17.00,
  "nota_parcial_3": 19.00,
  "nota_final": 18.17
}
```

### `POST /api/inscripciones/{id}/retirar`
Retirar materia (actualiza estatus y decrementa cupo).

### `DELETE /api/inscripciones/{id}`
Eliminar inscripción.

---

## Códigos de Respuesta HTTP

| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Operación exitosa (GET, PUT) |
| 201 | Created | Recurso creado exitosamente (POST) |
| 204 | No Content | Eliminación exitosa (DELETE) |
| 400 | Bad Request | Error en la solicitud |
| 404 | Not Found | Recurso no encontrado |
| 409 | Conflict | Conflicto (duplicados, sin cupos, etc.) |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error del servidor |

---

## Formato de Respuestas

### Respuesta Exitosa (Lista)
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 50,
  "total": 1000,
  "last_page": 20
}
```

### Respuesta Exitosa (Creación/Actualización)
```json
{
  "message": "Estudiante creado exitosamente",
  "data": {
    "id": 1,
    ...
  }
}
```

### Respuesta de Error (Validación)
```json
{
  "message": "Errores de validación",
  "errors": {
    "email": ["El email ya está registrado"],
    "cedula": ["La cédula es requerida"]
  }
}
```

### Respuesta de Error (No encontrado)
```json
{
  "message": "Estudiante no encontrado"
}
```

### Respuesta de Error (Conflicto)
```json
{
  "message": "No hay cupos disponibles",
  "error": "El horario seleccionado está lleno"
}
```

---

## Optimizaciones Implementadas

### 1. Eager Loading Automático
Todas las listas incluyen relaciones críticas precargadas:
```php
Estudiante::with(['carrera.facultad'])->paginate(50);
```

### 2. Scopes Reutilizables
```php
Estudiante::activo()->porCarrera(5)->buscar('Juan')->paginate(50);
```

### 3. Paginación Eficiente
```php
// Límite de 100 registros por página
$perPage = min($request->get('per_page', 50), 100);
```

### 4. Índices de Base de Datos
Todas las columnas de filtro tienen índices para búsquedas rápidas.

---

## Ejemplos de Uso

### Listar estudiantes activos de Ingeniería de Sistemas
```bash
GET /api/estudiantes?carrera_id=1&estatus=activo&per_page=100
```

### Buscar estudiantes por nombre
```bash
GET /api/estudiantes?buscar=Juan&sort=apellido&direction=asc
```

### Horarios disponibles para inscripción
```bash
GET /api/horarios/disponibles/listado?periodo_academico=2026-1&carrera_id=1
```

### Inscribir estudiante en horario
```bash
POST /api/inscripciones
Content-Type: application/json

{
  "estudiante_id": 12345,
  "horario_id": 567,
  "periodo_academico": "2026-1"
}
```

### Estadísticas de estudiante
```bash
GET /api/estudiantes/12345/estadisticas
```

---

## Testing con Postman/Insomnia

### Colección completa de endpoints

Importar esta estructura en Postman:

```
SGE API
├── Facultades
│   ├── GET    /facultades
│   ├── GET    /facultades/{id}
│   ├── POST   /facultades
│   ├── PUT    /facultades/{id}
│   └── DELETE /facultades/{id}
├── Carreras
│   ├── GET    /carreras
│   ├── GET    /carreras/{id}
│   ├── GET    /carreras/{id}/estudiantes
│   ├── GET    /carreras/{id}/materias
│   ├── POST   /carreras
│   ├── PUT    /carreras/{id}
│   └── DELETE /carreras/{id}
└── ... (continúa para todas las entidades)
```

---

## Próximos Pasos

1. ✅ Controladores completos creados
2. ✅ Rutas API configuradas
3. 🔄 Implementar autenticación con Sanctum
4. 🔄 Agregar rate limiting
5. 🔄 Implementar logs de auditoría
6. 🔄 Agregar tests automatizados
7. 🔄 Documentar con Swagger/OpenAPI

---

## Notas de Desarrollo

- Todos los controladores usan **type hints** de PHP 8+
- Validación robusta con **Validator**
- Respuestas consistentes en **JSON**
- Soft deletes en todas las entidades
- Eager loading para optimizar consultas N+1
