# Esquema de Base de Datos - Sistema de Gestión Estudiantil

## Descripción General

Este esquema relacional está optimizado para alta concurrencia con índices estratégicos y relaciones bien definidas para un sistema de gestión estudiantil universitario.

## Estructura de Tablas

### 1. **facultades**
Representa las facultades de la universidad.

**Campos principales:**
- `id`: Identificador único
- `codigo`: Código único de la facultad (indexado)
- `nombre`: Nombre de la facultad (indexado)
- `decano`: Nombre del decano
- `activo`: Estado de la facultad (indexado)

**Relaciones:**
- Tiene muchas `carreras`
- Tiene muchos `profesores`
- Tiene muchas `aulas`

---

### 2. **carreras**
Programas académicos ofrecidos por las facultades.

**Campos principales:**
- `id`: Identificador único
- `facultad_id`: FK a facultades (indexado con `activo`)
- `codigo`: Código único de la carrera (indexado)
- `nombre`: Nombre de la carrera (indexado)
- `titulo_otorgado`: Título que otorga
- `duracion_semestres`: Duración en semestres
- `creditos_totales`: Total de créditos requeridos
- `modalidad`: presencial, semipresencial, distancia

**Relaciones:**
- Pertenece a una `facultad`
- Tiene muchos `estudiantes`
- Tiene muchas `materias`

---

### 3. **estudiantes**
Datos personales y académicos de los estudiantes.

**Campos principales:**
- `id`: Identificador único
- `carrera_id`: FK a carreras (indexado con `estatus`)
- `cedula`: Cédula de identidad (único, indexado)
- `nombre`, `apellido`: Nombre completo (indexados juntos)
- `email`: Email único (indexado)
- `matricula`: Código estudiantil único (indexado)
- `fecha_ingreso`: Fecha de ingreso
- `semestre_actual`: Semestre cursando (indexado)
- `indice_academico`: Promedio ponderado
- `creditos_aprobados`: Créditos acumulados
- `estatus`: activo, inactivo, egresado, retirado, suspendido (indexado)

**Relaciones:**
- Pertenece a una `carrera`
- Tiene muchas `inscripciones`

**Índices optimizados:**
- `carrera_id` + `estatus` (consultas por carrera activa)
- `apellido` + `nombre` (búsqueda alfabética)
- Índices individuales en campos de búsqueda frecuente

---

### 4. **profesores**
Información de los docentes.

**Campos principales:**
- `id`: Identificador único
- `facultad_id`: FK a facultades (indexado con `estatus`)
- `cedula`: Cédula única (indexado)
- `nombre`, `apellido`: Nombre completo (indexados juntos)
- `email`: Email único (indexado)
- `codigo_empleado`: Código de empleado único (indexado)
- `tipo_contrato`: tiempo_completo, medio_tiempo, hora_clase, contratado
- `categoria`: instructor, asistente, agregado, asociado, titular
- `especialidad`: Área de especialización (indexado)
- `estatus`: activo, inactivo, licencia, jubilado (indexado)

**Relaciones:**
- Pertenece a una `facultad`
- Tiene muchos `horarios`

---

### 5. **materias**
Asignaturas del plan de estudios.

**Campos principales:**
- `id`: Identificador único
- `carrera_id`: FK a carreras (indexado con `activo`)
- `codigo`: Código único (indexado)
- `nombre`: Nombre de la materia (indexado)
- `creditos`: Unidades crédito
- `horas_teoricas`, `horas_practicas`, `horas_laboratorio`
- `semestre_recomendado`: Semestre recomendado (indexado)
- `tipo`: obligatoria, electiva, especialidad (indexado)

**Relaciones:**
- Pertenece a una `carrera`
- Tiene muchos `horarios`
- Tiene muchos prerrequisitos (relación many-to-many consigo misma)

**Tabla pivot:** `materia_prerrequisitos`
- Relaciona materias con sus prerrequisitos

---

### 6. **aulas**
Espacios físicos para impartir clases.

**Campos principales:**
- `id`: Identificador único
- `facultad_id`: FK a facultades (indexado con `estatus`)
- `codigo`: Código único del aula (indexado)
- `edificio`: Edificio donde se encuentra (indexado)
- `capacidad`: Capacidad máxima (indexado)
- `tipo`: aula_regular, laboratorio, auditorio, salon_conferencias, taller (indexado)
- `estatus`: disponible, mantenimiento, fuera_servicio (indexado)
- Flags de equipamiento: proyector, aire acondicionado, computadoras

**Relaciones:**
- Pertenece a una `facultad`
- Tiene muchos `horarios`

---

### 7. **horarios**
Secciones de materias con horario asignado.

**Campos principales:**
- `id`: Identificador único
- `materia_id`: FK a materias
- `profesor_id`: FK a profesores
- `aula_id`: FK a aulas
- `seccion`: Sección (A, B, 01, 02, etc.)
- `periodo_academico`: Ej: 2024-1, 2024-2 (indexado)
- `anno`, `periodo`: Para consultas de reportes (indexados)
- `dia_semana`: Día de la semana
- `hora_inicio`, `hora_fin`: Horario de la clase
- `cupo_maximo`, `cupo_actual`, `lista_espera`: Control de cupos
- `estatus`: abierto, cerrado, en_curso, finalizado, cancelado (indexado)

**Relaciones:**
- Pertenece a una `materia`
- Pertenece a un `profesor`
- Pertenece a un `aula`
- Tiene muchas `inscripciones`

**Índices especiales para evitar conflictos:**
- `idx_conflicto_aula`: Previene que un aula sea asignada dos veces al mismo tiempo
- `idx_conflicto_profesor`: Previene que un profesor tenga dos clases simultáneas

---

### 8. **inscripciones**
Registro de estudiantes inscritos en horarios.

**Campos principales:**
- `id`: Identificador único
- `estudiante_id`: FK a estudiantes
- `horario_id`: FK a horarios
- `periodo_academico`: Periodo de inscripción
- `fecha_inscripcion`: Fecha de la inscripción (indexado)
- `estatus`: inscrito, retirado, aprobado, reprobado, cursando, lista_espera (indexado)
- `nota_parcial_1`, `nota_parcial_2`, `nota_parcial_3`, `nota_final`: Calificaciones
- `inasistencias`, `porcentaje_asistencia`: Control de asistencia

**Relaciones:**
- Pertenece a un `estudiante`
- Pertenece a un `horario`

**Constraint único:**
- Un estudiante no puede inscribirse dos veces en el mismo horario en el mismo periodo

**Índices compuestos para reportes:**
- `estudiante_id` + `periodo_academico` + `estatus`
- `horario_id` + `estatus`
- `periodo_academico` + `estatus` + `fecha_inscripcion`

---

## Optimizaciones para Alta Concurrencia

### Índices Estratégicos
- Índices simples en campos de búsqueda frecuente
- Índices compuestos en combinaciones de campos usados en WHERE clauses
- Índices en foreign keys para optimizar JOINs

### Soft Deletes
Todas las tablas principales usan `softDeletes()` para mantener integridad histórica.

### Constraints de Unicidad
- Previenen duplicados en inscripciones
- Garantizan integridad en códigos y identificadores

### Índices para Prevenir Conflictos
- Detectan solapamiento de horarios en aulas
- Detectan doble asignación de profesores

## Cardinalidades Esperadas (Producción)

- **Facultades**: ~10-20
- **Carreras**: ~50-100
- **Estudiantes**: ~100,000-500,000
- **Profesores**: ~5,000-10,000
- **Materias**: ~2,000-5,000
- **Aulas**: ~500-1,000
- **Horarios**: ~10,000-20,000 por periodo
- **Inscripciones**: ~500,000-2,000,000 por periodo

## Consultas Optimizadas Comunes

1. **Listar estudiantes activos de una carrera**:
   - Usa índice: `carrera_id` + `estatus`

2. **Buscar horarios disponibles en un periodo**:
   - Usa índice: `periodo_academico` + `estatus`

3. **Verificar conflictos de aula**:
   - Usa índice: `idx_conflicto_aula`

4. **Reportes de inscripciones por periodo**:
   - Usa índice: `idx_reporte_inscripciones`

5. **Búsqueda de estudiantes por nombre**:
   - Usa índice: `apellido` + `nombre`
