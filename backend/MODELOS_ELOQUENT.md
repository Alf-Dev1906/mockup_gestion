# Documentación de Modelos Eloquent

## Resumen

Se han creado 8 modelos Eloquent con todas las relaciones definidas, scopes útiles, accessors/mutators y métodos de negocio para facilitar el desarrollo de la API.

---

## Modelos Creados

### 1. **Facultad**
- **Tabla**: `facultades`
- **Características**:
  - SoftDeletes habilitado
  - Factory ready
  - Scopes: `activo()`, `buscar($termino)`
  
**Relaciones**:
- `hasMany(Carrera)` - Una facultad tiene muchas carreras
- `hasMany(Profesor)` - Una facultad tiene muchos profesores
- `hasMany(Aula)` - Una facultad tiene muchas aulas

---

### 2. **Carrera**
- **Tabla**: `carreras`
- **Características**:
  - SoftDeletes habilitado
  - Accessor: `estudiantes_activos_count`
  - Scopes: `activo()`, `porFacultad($id)`, `buscar($termino)`

**Relaciones**:
- `belongsTo(Facultad)` - Pertenece a una facultad
- `hasMany(Estudiante)` - Tiene muchos estudiantes
- `hasMany(Materia)` - Tiene muchas materias

---

### 3. **Estudiante**
- **Tabla**: `estudiantes`
- **Características**:
  - SoftDeletes habilitado
  - Accessors: `nombre_completo`, `edad`
  - Scopes: `activo()`, `porCarrera($id)`, `porSemestre($num)`, `buscar($termino)`
  - Método: `inscribirEnHorario($horario, $periodo)`

**Relaciones**:
- `belongsTo(Carrera)` - Pertenece a una carrera
- `hasMany(Inscripcion)` - Tiene muchas inscripciones
- `belongsToMany(Horario)` through `inscripciones` - Relación many-to-many con horarios

---

### 4. **Profesor**
- **Tabla**: `profesores`
- **Características**:
  - SoftDeletes habilitado
  - Accessor: `nombre_completo`
  - Scopes: `activo()`, `porFacultad($id)`, `porCategoria($cat)`, `porEspecialidad($esp)`, `buscar($termino)`
  - Método: `getCargaHoraria($periodo)`

**Relaciones**:
- `belongsTo(Facultad)` - Pertenece a una facultad
- `hasMany(Horario)` - Tiene muchos horarios
- `hasManyThrough(Materia, Horario)` - Acceso indirecto a materias

---

### 5. **Materia**
- **Tabla**: `materias`
- **Características**:
  - SoftDeletes habilitado
  - Accessor: `total_horas`
  - Scopes: `activo()`, `porCarrera($id)`, `porSemestre($num)`, `porTipo($tipo)`, `buscar($termino)`
  - Métodos: 
    - `tienePrerrequisito($materia)`
    - `estudiantePuedeInscribir($estudiante)`

**Relaciones**:
- `belongsTo(Carrera)` - Pertenece a una carrera
- `hasMany(Horario)` - Tiene muchos horarios
- `belongsToMany(Materia as prerrequisitos)` - Relación many-to-many consigo misma (prerrequisitos)
- `belongsToMany(Materia as materiasQueRequieren)` - Relación inversa de prerrequisitos

**Tabla Pivot**: `materia_prerrequisitos`

---

### 6. **Aula**
- **Tabla**: `aulas`
- **Características**:
  - SoftDeletes habilitado
  - Accessor: `ocupacion`
  - Scopes: `disponible()`, `porFacultad($id)`, `porEdificio($nombre)`, `porTipo($tipo)`, `conCapacidadMinima($num)`, `buscar($termino)`
  - Método: `estaDisponible($dia, $horaInicio, $horaFin, $periodo)`

**Relaciones**:
- `belongsTo(Facultad)` - Pertenece a una facultad
- `hasMany(Horario)` - Tiene muchos horarios asignados

---

### 7. **Horario**
- **Tabla**: `horarios`
- **Características**:
  - SoftDeletes habilitado
  - Accessors: `cupos_disponibles`, `porcentaje_ocupacion`, `esta_lleno`
  - Scopes: `abierto()`, `enCurso()`, `porPeriodo($periodo)`, `porMateria($id)`, `porProfesor($id)`, `porDia($dia)`, `conCuposDisponibles()`, `buscar($termino)`
  - Métodos:
    - `tieneConflicto()` - Detecta conflictos de horario
    - `incrementarCupo()` - Incrementa cupo actual
    - `decrementarCupo()` - Decrementa cupo actual

**Relaciones**:
- `belongsTo(Materia)` - Pertenece a una materia
- `belongsTo(Profesor)` - Pertenece a un profesor
- `belongsTo(Aula)` - Pertenece a un aula
- `hasMany(Inscripcion)` - Tiene muchas inscripciones
- `belongsToMany(Estudiante)` through `inscripciones` - Relación many-to-many con estudiantes

---

### 8. **Inscripcion**
- **Tabla**: `inscripciones`
- **Características**:
  - SoftDeletes habilitado
  - Accessors: `promedio_parciales`, `esta_aprobado`
  - Scopes: `inscrito()`, `cursando()`, `aprobado()`, `reprobado()`, `porEstudiante($id)`, `porHorario($id)`, `porPeriodo($periodo)`
  - Métodos:
    - `calcularNotaFinal()` - Calcula nota final basada en parciales
    - `retirar()` - Marca inscripción como retirada
    - `registrarInasistencia()` - Incrementa inasistencias
  - Event Hooks:
    - Al crear: incrementa cupo del horario
    - Al eliminar: decrementa cupo del horario

**Relaciones**:
- `belongsTo(Estudiante)` - Pertenece a un estudiante
- `belongsTo(Horario)` - Pertenece a un horario

---

## Características Comunes

### Todos los modelos incluyen:

1. **HasFactory**: Preparados para usar factories de datos
2. **SoftDeletes**: Eliminación lógica para mantener integridad histórica
3. **Casts**: Conversión automática de tipos de datos
4. **Timestamps**: `created_at`, `updated_at`, `deleted_at` automáticos

### Patterns Implementados:

1. **Scopes Reutilizables**: Para consultas comunes
2. **Accessors**: Atributos calculados on-the-fly
3. **Métodos de Negocio**: Lógica de dominio encapsulada
4. **Relaciones Explícitas**: Todas las relaciones están documentadas

---

## Uso de Scopes

Los scopes permiten consultas más limpias:

```php
// Sin scope
Estudiante::where('estatus', 'activo')->where('carrera_id', 5)->get();

// Con scope
Estudiante::activo()->porCarrera(5)->get();
```

## Uso de Accessors

Los accessors proporcionan atributos calculados:

```php
$estudiante = Estudiante::find(1);
echo $estudiante->nombre_completo; // "Juan Pérez"
echo $estudiante->edad; // 22

$horario = Horario::find(1);
echo $horario->cupos_disponibles; // 15
echo $horario->porcentaje_ocupacion; // 50.00
```

## Relaciones Eager Loading

Para optimizar consultas con alta concurrencia:

```php
// Cargar estudiantes con su carrera
$estudiantes = Estudiante::with('carrera')->get();

// Cargar horarios con materia, profesor y aula
$horarios = Horario::with(['materia', 'profesor', 'aula'])->get();

// Cargar inscripciones con todos los datos relacionados
$inscripciones = Inscripcion::with([
    'estudiante.carrera',
    'horario.materia',
    'horario.profesor',
    'horario.aula'
])->get();
```

---

## Próximos Pasos

1. Crear **Factories** para generar datos de prueba
2. Crear **Seeders** para poblar la base de datos con datos masivos
3. Actualizar **Controllers** para usar estos modelos
4. Implementar **Request Validation** para cada modelo
