# Documentación de Seeders

## Resumen

Sistema completo de seeders optimizado para generar **más de 370,000 registros** de forma eficiente, simulando un ambiente universitario de alta concurrencia con 100,000+ estudiantes.

---

## Arquitectura de Seeders

### DatabaseSeeder (Orquestador Principal)

Coordina la ejecución secuencial de todos los seeders con:
- Medición de tiempos
- Reportes de progreso
- Desactivación temporal de FK checks para mejor performance
- Resumen final con estadísticas

**Orden de ejecución**:
1. Facultades (10)
2. Carreras (~30)
3. Profesores (5,000)
4. Aulas (800)
5. Materias (~1,500)
6. Estudiantes (100,000) ⏱️
7. Horarios (15,000)
8. Inscripciones (~250,000) ⏱️

---

## Seeders Individuales

### 1. FacultadSeeder
**Registros**: 10 facultades reales

**Características**:
- Datos predefinidos y realistas
- Facultades típicas venezolanas
- Códigos únicos (ING, CIE, MED, etc.)
- Emails institucionales @universidad.edu.ve

**Facultades creadas**:
- Ingeniería
- Ciencias
- Humanidades y Educación
- Ciencias Económicas y Sociales
- Ciencias Jurídicas y Políticas
- Medicina
- Odontología
- Farmacia
- Arquitectura y Urbanismo
- Ciencias Veterinarias

---

### 2. CarreraSeeder
**Registros**: ~30 carreras distribuidas por facultad

**Características**:
- Carreras predefinidas por facultad
- Datos reales (semestres, créditos, títulos)
- 25+ carreras universitarias diferentes

**Ejemplos por facultad**:
- **Ingeniería**: Sistemas, Civil, Eléctrica, Mecánica, Química
- **Ciencias**: Biología, Química, Matemáticas, Física
- **CES**: Administración, Contaduría, Economía
- **Medicina**: Medicina, Enfermería

---

### 3. ProfesorSeeder
**Registros**: 5,000 profesores

**Optimizaciones**:
- ✅ Procesamiento en chunks de 500
- ✅ Uso de `insert()` masivo
- ✅ Reportes de progreso con porcentajes

**Distribución**:
- Asignación aleatoria a facultades
- Datos realistas (cédulas V/E, teléfonos venezolanos)
- Categorías académicas variadas

**Tiempo estimado**: ~30-60 segundos

---

### 4. AulaSeeder
**Registros**: 800 aulas

**Optimizaciones**:
- ✅ Chunks de 200 registros
- ✅ Insert masivo

**Características**:
- Códigos de aula (A-101, B-205, etc.)
- Tipos variados (regular, laboratorio, auditorio)
- Capacidades según tipo
- Equipamiento realista

**Tiempo estimado**: ~20-40 segundos

---

### 5. MateriaSeeder
**Registros**: ~1,500 materias + prerrequisitos

**Características**:
- 40-60 materias por carrera
- Distribución por semestres (1-10)
- Creación de prerrequisitos inteligente:
  - Solo materias de semestre 3+
  - 30% probabilidad de tener 1-2 prerrequisitos
  - Prerrequisitos lógicos (semestres anteriores)

**Tiempo estimado**: ~1-2 minutos

---

### 6. EstudianteSeeder ⏱️
**Registros**: 100,000 estudiantes

**Optimizaciones críticas**:
- ✅ Chunks de 2,000 registros
- ✅ Desactivación de eventos (`unsetEventDispatcher`)
- ✅ Insert masivo con `DB::table()->insert()`
- ✅ Pausas cada 10 chunks (evita saturación)
- ✅ Limpieza de memoria con `unset()`
- ✅ Reportes detallados: progreso, velocidad, ETA

**Datos generados**:
- Cédulas venezolanas únicas (V/E)
- Matrículas de 10 dígitos únicas
- Emails institucionales únicos
- Distribución aleatoria por carreras
- Estados venezolanos
- Datos académicos coherentes

**Tiempo estimado**: ⏱️ **5-10 minutos** (el más lento)

**Performance esperada**: ~200-500 estudiantes/segundo

---

### 7. HorarioSeeder
**Registros**: 15,000 horarios

**Optimizaciones**:
- ✅ Chunks de 1,000 registros
- ✅ Desactivación de eventos
- ✅ Uso de `periodoActual()` state

**Características**:
- Periodo: 2026-1 (actual)
- Solo profesores activos
- Solo aulas disponibles
- Múltiples secciones por materia
- Horarios realistas (7:00-18:00)

**Tiempo estimado**: ~2-3 minutos

---

### 8. InscripcionSeeder ⏱️
**Registros**: ~250,000 inscripciones

**Optimizaciones críticas**:
- ✅ Chunks de 5,000 registros
- ✅ Desactivación de eventos
- ✅ Limitación a 50K estudiantes (evita saturación)
- ✅ Pausas cada chunk (0.05 seg)
- ✅ Insert masivo

**Lógica**:
- Cada estudiante inscrito en 4-6 materias
- 50,000 estudiantes × 5 materias promedio = ~250,000 inscripciones
- Horarios aleatorios del periodo actual
- Notas según estatus (aprobado/cursando/etc.)

**Tiempo estimado**: ⏱️ **3-8 minutos** (segundo más lento)

**Performance esperada**: ~500-1000 inscripciones/segundo

---

## Optimizaciones Implementadas

### 1. Procesamiento en Chunks
Todos los seeders masivos usan chunks para evitar problemas de memoria:

```php
$chunkSize = 2000;
for ($i = 0; $i < $total; $i += $chunkSize) {
    // Procesar chunk
    unset($datos); // Liberar memoria
}
```

### 2. Insert Masivo
Uso de `DB::table()->insert()` en lugar de `create()`:

```php
// ❌ Lento (100K llamadas a BD)
for ($i = 0; $i < 100000; $i++) {
    Estudiante::create($data);
}

// ✅ Rápido (50 llamadas a BD)
for ($i = 0; $i < 100000; $i += 2000) {
    DB::table('estudiantes')->insert($chunk);
}
```

### 3. Desactivación de Eventos
Los eventos de Eloquent ralentizan la inserción masiva:

```php
Estudiante::unsetEventDispatcher();
// Inserciones masivas...
```

### 4. Desactivación de Foreign Keys
Durante el seeding se desactivan temporalmente:

```php
DB::statement('SET FOREIGN_KEY_CHECKS=0');
// Seeders...
DB::statement('SET FOREIGN_KEY_CHECKS=1');
```

### 5. Pausas Estratégicas
Pequeñas pausas evitan saturar la base de datos:

```php
usleep(100000); // 0.1 segundos cada 10 chunks
```

---

## Cómo Ejecutar los Seeders

### Opción 1: Ejecutar Todo (Recomendado)

```bash
# En XAMPP, navegar a la carpeta del proyecto
cd C:\xampp\htdocs\Gestion_uni\backend

# Ejecutar con PHP de XAMPP
C:\xampp\php\php artisan db:seed

# O si PHP está en el PATH
php artisan db:seed
```

**⏱️ Tiempo total estimado**: 10-20 minutos

### Opción 2: Ejecutar Seeder Individual

```bash
# Solo estudiantes
php artisan db:seed --class=EstudianteSeeder

# Solo profesores
php artisan db:seed --class=ProfesorSeeder
```

### Opción 3: Refrescar y Poblar

```bash
# ⚠️ CUIDADO: Esto BORRARÁ todos los datos
php artisan migrate:fresh --seed
```

---

## Resultados Esperados

Al finalizar, la base de datos contendrá:

| Tabla | Registros | Tiempo Aprox |
|-------|-----------|--------------|
| Facultades | 10 | < 1 seg |
| Carreras | ~30 | < 1 seg |
| Profesores | 5,000 | 30-60 seg |
| Aulas | 800 | 20-40 seg |
| Materias | ~1,500 | 1-2 min |
| Estudiantes | 100,000 | ⏱️ 5-10 min |
| Horarios | 15,000 | 2-3 min |
| Inscripciones | ~250,000 | ⏱️ 3-8 min |
| **TOTAL** | **~372,340** | **10-20 min** |

---

## Monitoreo Durante Ejecución

Los seeders muestran progreso en tiempo real:

```
📁 PASO 1/8: Facultades
✓ Facultad creada: Facultad de Ingeniería
...
✅ Se crearon 10 facultades.

👨‍🎓 PASO 6/8: Estudiantes (proceso largo)
Progreso: 2000/100000 (2.0%) | Chunk: 8.5 seg | Total: 8.5 seg | Velocidad: 235/seg | ETA: 6.9 min
Progreso: 4000/100000 (4.0%) | Chunk: 7.8 seg | Total: 16.3 seg | Velocidad: 245/seg | ETA: 6.5 min
...
```

---

## Troubleshooting

### Problema: "Out of memory"
**Solución**: Aumentar límite de memoria en `php.ini`:
```ini
memory_limit = 512M
```

### Problema: "Max execution time exceeded"
**Solución**: Aumentar tiempo en `php.ini`:
```ini
max_execution_time = 600
```

### Problema: "Too many connections"
**Solución**: Los seeders ya incluyen pausas. Si persiste, reducir `$chunkSize`.

### Problema: Proceso muy lento
**Solución**:
1. Verificar índices de BD estén creados
2. Verificar que foreign key checks estén desactivados
3. Usar MySQL en lugar de MariaDB si es posible
4. Cerrar otras aplicaciones pesadas

---

## Consideraciones de Producción

⚠️ **IMPORTANTE**: Estos seeders están diseñados para **desarrollo y testing**.

En **producción**:
- NO ejecutar `db:seed` en producción
- Los datos reales deben venir de fuentes oficiales
- Implementar importación gradual con validación
- Usar colas para procesamiento asíncrono

---

## Próximos Pasos

Una vez poblada la base de datos:

1. ✅ Ejecutar migraciones: `php artisan migrate`
2. ✅ Ejecutar seeders: `php artisan db:seed`
3. 🔄 Configurar controladores API
4. 🔄 Implementar autenticación
5. 🔄 Crear frontend Vue.js

---

## Comando Completo de Inicialización

```bash
# Crear BD en XAMPP/DBeaver: sge_db

# Ejecutar migraciones y seeders
cd backend
php artisan migrate:fresh --seed

# Esperar 10-20 minutos ☕

# Verificar
php artisan tinker
>>> DB::table('estudiantes')->count()
100000
```

🎉 ¡Base de datos lista para alta concurrencia!
