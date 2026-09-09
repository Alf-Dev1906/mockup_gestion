# Documentación de Factories

## Resumen

Se han creado 8 factories completos para generar datos de prueba masivos y realistas, optimizados para el contexto venezolano con Faker configurado en español.

---

## Factories Creados

### 1. **FacultadFactory**

Genera facultades universitarias realistas.

**Datos generados**:
- Códigos únicos (ING, CIE, HYE, etc.)
- Nombres de facultades reales
- Emails institucionales (@universidad.edu.ve)
- Teléfonos venezolanos (0212-XXXXXXX)

**Estados personalizados**:
```php
Facultad::factory()->activo()->create();
```

---

### 2. **CarreraFactory**

Genera carreras universitarias con datos reales.

**Datos generados**:
- 20 carreras universitarias diferentes
- Códigos únicos por carrera
- Títulos otorgados correctos
- Duración en semestres (8-12)
- Créditos totales (155-280)
- Modalidades: presencial, semipresencial, distancia

**Estados personalizados**:
```php
Carrera::factory()->activo()->create();
Carrera::factory()->presencial()->create();
```

---

### 3. **EstudianteFactory**

Genera estudiantes con datos venezolanos realistas.

**Datos generados**:
- Cédulas venezolanas (V-XXXXXXXX / E-XXXXXXXX)
- Matrículas únicas de 10 dígitos
- Emails institucionales (@estudiantes.edu.ve)
- Teléfonos móviles venezolanos (0412, 0414, 0424, 0416, 0426)
- Estados de Venezuela (Distrito Capital, Miranda, Zulia, etc.)
- Índice académico (escala 0-20)
- Fechas de ingreso coherentes con semestre actual

**Estados personalizados**:
```php
Estudiante::factory()->activo()->create();
Estudiante::factory()->egresado()->create();
Estudiante::factory()->primerSemestre()->create();
```

---

### 4. **ProfesorFactory**

Genera profesores con credenciales académicas.

**Datos generados**:
- Cédulas venezolanas (V/E)
- Códigos de empleado únicos (P-XXXXXX)
- Emails institucionales (@profesor.edu.ve)
- Teléfonos venezolanos
- Títulos académicos (Dr., Dra., Msc., Ing., Lic., etc.)
- Categorías (instructor, asistente, agregado, asociado, titular)
- Tipos de contrato (tiempo completo, medio tiempo, hora clase)
- Especialidades (25 diferentes)

**Estados personalizados**:
```php
Profesor::factory()->activo()->create();
Profesor::factory()->tiempoCompleto()->create();
Profesor::factory()->titular()->create();
```

---

### 5. **MateriaFactory**

Genera materias universitarias realistas.

**Datos generados**:
- 60+ nombres de materias reales
- Códigos únicos (formato: ABC123)
- Distribución de horas (teoría, práctica, laboratorio)
- Créditos (2-5)
- Semestre recomendado (1-10)
- Tipos: obligatoria, electiva, especialidad

**Categorías de materias**:
- Ingeniería y Ciencias
- Ciencias Sociales y Humanidades
- Medicina y Salud

**Estados personalizados**:
```php
Materia::factory()->obligatoria()->create();
Materia::factory()->electiva()->create();
Materia::factory()->primerSemestre()->create();
```

---

### 6. **AulaFactory**

Genera aulas y espacios físicos universitarios.

**Datos generados**:
- Códigos de aula (A-101, B-205, etc.)
- Edificios (A-H)
- Pisos (1-5)
- Capacidad según tipo de aula
- Tipos: aula_regular, laboratorio, auditorio, salon_conferencias, taller
- Equipamiento específico por tipo
- Flags: proyector, aire acondicionado, computadoras
- Accesibilidad para discapacitados

**Estados personalizados**:
```php
Aula::factory()->disponible()->create();
Aula::factory()->laboratorio()->create();
Aula::factory()->auditorio()->create();
```

---

### 7. **HorarioFactory**

Genera horarios de clases con validación de conflictos.

**Datos generados**:
- Periodo académico (2024-1, 2024-2, etc.)
- Secciones (A, B, C, 01, 02, etc.)
- Días de semana (lunes a viernes)
- Horas de inicio (7:00 AM - 6:00 PM)
- Duración de clases (2-4 horas)
- Cupos máximos (25-50)
- Cupos actuales realistas
- Lista de espera
- Estados: abierto, en_curso, cerrado, finalizado

**Estados personalizados**:
```php
Horario::factory()->periodoActual()->create();
Horario::factory()->abierto()->create();
Horario::factory()->enCurso()->create();
Horario::factory()->conCuposDisponibles()->create();
Horario::factory()->lleno()->create();
```

---

### 8. **InscripcionFactory**

Genera inscripciones con notas y asistencia.

**Datos generados**:
- Periodo académico coherente
- Estatus: inscrito, cursando, aprobado, reprobado, retirado
- Notas parciales (3) y nota final
- **Lógica inteligente**: Las notas se generan según el estatus
  - Aprobado: notas 10-20
  - Reprobado: notas 0-9
  - Cursando: solo algunas notas
  - Inscrito: sin notas
  - Retirado: solo primera nota (opcional)
- Inasistencias (0-10)
- Porcentaje de asistencia (70-100%)

**Estados personalizados**:
```php
Inscripcion::factory()->aprobado()->create();
Inscripcion::factory()->reprobado()->create();
Inscripcion::factory()->cursando()->create();
Inscripcion::factory()->inscrito()->create();
```

---

## Características Especiales

### Datos Contextualizados (Venezuela)

- **Cédulas**: Formato V-XXXXXXXX o E-XXXXXXXX
- **Teléfonos**: Códigos de área venezolanos (0212, 0412, 0414, 0424, 0416, 0426)
- **Estados**: 15 estados de Venezuela
- **Emails**: Dominios institucionales .edu.ve

### Lógica Inteligente

- Las **notas** se generan según el **estatus** de la inscripción
- Los **cupos** de horarios son realistas (actual <= máximo)
- Las **fechas** son coherentes entre sí
- Los **semestres** corresponden con las **fechas de ingreso**

### Relaciones Automáticas

Cada factory puede crear automáticamente sus relaciones:

```php
// Crea estudiante CON su carrera Y facultad
$estudiante = Estudiante::factory()->create();

// Crea horario CON materia, profesor, aula Y sus relaciones
$horario = Horario::factory()->create();

// Crea inscripción CON estudiante Y horario completos
$inscripcion = Inscripcion::factory()->create();
```

---

## Uso de Factories

### Crear un registro simple

```php
use App\Models\Estudiante;

$estudiante = Estudiante::factory()->create();
```

### Crear múltiples registros

```php
// Crear 100 estudiantes
$estudiantes = Estudiante::factory()->count(100)->create();
```

### Sobrescribir atributos

```php
$estudiante = Estudiante::factory()->create([
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'estatus' => 'activo'
]);
```

### Usar estados personalizados

```php
// Estudiante de primer semestre
$estudiante = Estudiante::factory()->primerSemestre()->create();

// Profesor titular
$profesor = Profesor::factory()->titular()->create();

// Horario con cupos disponibles
$horario = Horario::factory()->conCuposDisponibles()->create();
```

### Crear con relaciones específicas

```php
$carrera = Carrera::factory()->create();

// Crear 50 estudiantes en esa carrera
$estudiantes = Estudiante::factory()
    ->count(50)
    ->for($carrera)
    ->create();
```

### Crear datos relacionados en cascada

```php
// Crea facultad con 5 carreras
$facultad = Facultad::factory()
    ->has(Carrera::factory()->count(5))
    ->create();

// Crea carrera con 100 estudiantes
$carrera = Carrera::factory()
    ->has(Estudiante::factory()->count(100))
    ->create();
```

---

## Ejemplos de Uso Masivo

### Poblar base de datos completa

```php
use App\Models\{Facultad, Carrera, Estudiante, Profesor, Materia, Aula, Horario};

// Crear estructura base
$facultad = Facultad::factory()->create();
$carrera = Carrera::factory()->for($facultad)->create();

// Crear 1,000 estudiantes
Estudiante::factory()->count(1000)->for($carrera)->create();

// Crear 50 profesores
Profesor::factory()->count(50)->for($facultad)->create();

// Crear 200 materias
Materia::factory()->count(200)->for($carrera)->create();

// Crear 100 aulas
Aula::factory()->count(100)->for($facultad)->create();
```

### Crear escenario de prueba específico

```php
// Horario con cupos disponibles y estudiantes inscritos
$horario = Horario::factory()->conCuposDisponibles()->create();

Inscripcion::factory()
    ->count(15)
    ->for($horario)
    ->inscrito()
    ->create();
```

---

## Optimización para Generación Masiva

Para generar 100K+ registros de forma eficiente, usar en los seeders:

1. **Chunks**: Procesar en lotes de 1000-5000 registros
2. **Disable events**: Desactivar eventos temporalmente
3. **Use insert**: Usar insert masivo en lugar de create

Ver `DatabaseSeeder.php` para la implementación optimizada.

---

## Próximo Paso

Crear los **Seeders** que usarán estos factories para poblar la base de datos con datos masivos (100K+ estudiantes) de forma optimizada.
