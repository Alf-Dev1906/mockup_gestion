# MOTOR DE EXAMENES DINAMICOS - CONTRATO JSON
Fecha: Septiembre 7, 2026
Paso 2.1 - Bloque 2

## ARQUITECTURA DEL SISTEMA

El motor de exámenes utiliza columnas JSON en `quiz_questions.contenido` 
y `quiz_answers.respuesta` para almacenar la lógica de cada tipo de pregunta.
Esto permite extensibilidad sin nuevas migraciones.

---

## TIPO 1: SELECCION MULTIPLE

### Estructura quiz_questions.contenido
```json
{
  "opciones": [
    {"id": "a", "texto": "Opción A", "es_correcta": true},
    {"id": "b", "texto": "Opción B", "es_correcta": false},
    {"id": "c", "texto": "Opción C", "es_correcta": false},
    {"id": "d", "texto": "Opción D", "es_correcta": true}
  ],
  "permite_multiple": true
}
```

### Estructura quiz_answers.respuesta (estudiante)
```json
{
  "seleccionadas": ["a", "d"]
}
```

### Lógica de calificación
- Si permite_multiple=false: Solo 1 opción puede estar marcada
- Comparar array seleccionadas con opciones donde es_correcta=true
- Puntos: (correctas_marcadas / total_correctas) * puntos_pregunta

---

## TIPO 2: VERDADERO/FALSO CON JUSTIFICACION

### Estructura quiz_questions.contenido
```json
{
  "afirmacion": "El agua hierve a 100°C al nivel del mar",
  "respuesta_correcta": true,
  "justificacion_requerida": true,
  "puntos_justificacion": 2.0,
  "puntos_respuesta": 3.0
}
```

### Estructura quiz_answers.respuesta (estudiante)
```json
{
  "valor": true,
  "justificacion": "Porque a presión atmosférica estándar (1 atm), la temperatura de ebullición del agua es 100°C"
}
```

### Lógica de calificación
- Verificar si valor == respuesta_correcta
- Si correcto: asignar puntos_respuesta
- Si justificacion_requerida=true: calificar texto manualmente
- Puntos totales: puntos_respuesta + puntos_justificacion

---

## TIPO 3: RELACION DE COLUMNAS (EMPAREJAMIENTO)

### Estructura quiz_questions.contenido
```json
{
  "columna_a": [
    {"id": "1", "texto": "Capital de Francia"},
    {"id": "2", "texto": "Capital de España"},
    {"id": "3", "texto": "Capital de Italia"}
  ],
  "columna_b": [
    {"id": "x", "texto": "París"},
    {"id": "y", "texto": "Madrid"},
    {"id": "z", "texto": "Roma"}
  ],
  "pares_correctos": [
    {"a": "1", "b": "x"},
    {"a": "2", "b": "y"},
    {"a": "3", "b": "z"}
  ],
  "mezclar_columna_b": true
}
```

### Estructura quiz_answers.respuesta (estudiante)
```json
{
  "pares": [
    {"a": "1", "b": "x"},
    {"a": "2", "b": "y"},
    {"a": "3", "b": "z"}
  ]
}
```

### Lógica de calificación
- Comparar cada par del estudiante con pares_correctos
- Puntos: (pares_correctos_count / total_pares) * puntos_pregunta
- Frontend mezcla columna_b si mezclar_columna_b=true

---

## TIPO 4: LLENADO DE ESPACIOS

### Estructura quiz_questions.contenido
```json
{
  "plantilla": "El __ de Venezuela es __, y su moneda es el __",
  "espacios": [
    {
      "id": 1,
      "respuestas_validas": ["capital", "Capital", "CAPITAL"],
      "sensible_mayusculas": false
    },
    {
      "id": 2,
      "respuestas_validas": ["Caracas"],
      "sensible_mayusculas": true
    },
    {
      "id": 3,
      "respuestas_validas": ["bolívar", "Bolívar", "bolivar"],
      "sensible_mayusculas": false
    }
  ]
}
```

### Estructura quiz_answers.respuesta (estudiante)
```json
{
  "respuestas": {
    "1": "capital",
    "2": "Caracas",
    "3": "bolívar"
  }
}
```

### Lógica de calificación
- Para cada espacio, verificar si la respuesta está en respuestas_validas
- Si sensible_mayusculas=false: comparar toLowerCase()
- Puntos: (espacios_correctos / total_espacios) * puntos_pregunta

---

## TIPO 5: MULTIMEDIA CON ENTREGA DE ARCHIVO

### Estructura quiz_questions.contenido
```json
{
  "descripcion": "Analiza el siguiente diagrama de flujo y responde: ¿Cuál es la complejidad temporal del algoritmo?",
  "archivo_adjunto_url": "/storage/quizzes/diagrama_flujo.png",
  "tipo_adjunto": "imagen",
  "extensiones_respuesta_permitidas": ["pdf", "zip", "docx"],
  "tamano_max_mb": 5,
  "calificacion_manual": true,
  "rubrica": "Se evaluará: 1) Análisis correcto (3pts), 2) Justificación matemática (2pts)"
}
```

### Estructura quiz_answers.respuesta (estudiante)
```json
{
  "archivo_url": "/storage/answers/attempt_123_question_5.pdf",
  "nombre_archivo": "analisis_algoritmo.pdf",
  "tamano_bytes": 1048576
}
```

### Lógica de calificación
- calificacion_manual=true: El profesor debe revisar y asignar puntos
- Backend valida extensión y tamaño antes de aceptar upload
- quiz_answers.es_correcta y puntos_obtenidos quedan NULL hasta revisión

---

## CAMPOS COMUNES

### Obligatorios en quiz_questions
- tipo: ENUM (seleccion, verdadero_falso, relacion_columnas, espacios, multimedia)
- enunciado: TEXT
- contenido: JSON (estructura según tipo)
- puntos: DECIMAL(5,2)
- obligatoria: BOOLEAN (default true)

### Opcionales en quiz_questions
- retroalimentacion: TEXT (mostrada después de calificar)
- archivo_url: VARCHAR (para tipo multimedia o imágenes en enunciado)

### En quiz_answers
- respuesta: JSON (estructura según tipo de pregunta)
- es_correcta: BOOLEAN nullable (NULL si no calificada)
- puntos_obtenidos: DECIMAL(5,2) nullable
- archivo_url: VARCHAR nullable (para tipo multimedia)

---

## VALIDACIONES BACKEND

### Al crear pregunta (QuizQuestionRequest)
```php
rules: [
  'tipo' => 'required|in:seleccion,verdadero_falso,relacion_columnas,espacios,multimedia',
  'enunciado' => 'required|string',
  'contenido' => 'required|json',
  'puntos' => 'required|numeric|min:0',
]
```

### Por tipo de pregunta

**Selección múltiple:**
- contenido.opciones: array, min:2
- contenido.permite_multiple: boolean
- Cada opción: {id, texto, es_correcta}

**Verdadero/Falso:**
- contenido.afirmacion: string
- contenido.respuesta_correcta: boolean
- contenido.justificacion_requerida: boolean
- contenido.puntos_justificacion: numeric (si justificacion_requerida=true)

**Relación de columnas:**
- contenido.columna_a: array, min:2
- contenido.columna_b: array, same size as columna_a
- contenido.pares_correctos: array, size = columna_a.length

**Llenado de espacios:**
- contenido.plantilla: string, must contain '__'
- contenido.espacios: array, count matches '__' count in plantilla
- Cada espacio: {id, respuestas_validas: array, sensible_mayusculas}

**Multimedia:**
- contenido.descripcion: string
- contenido.extensiones_respuesta_permitidas: array
- contenido.tamano_max_mb: numeric, max:50
- contenido.calificacion_manual: boolean

---

## SERVICIO DE CALIFICACION

### QuizGradingService

```php
class QuizGradingService
{
    public function calificarRespuesta(
        QuizQuestion $question,
        array $respuestaEstudiante
    ): array {
        return match($question->tipo) {
            'seleccion' => $this->calificarSeleccion($question, $respuestaEstudiante),
            'verdadero_falso' => $this->calificarVerdaderoFalso($question, $respuestaEstudiante),
            'relacion_columnas' => $this->calificarRelacion($question, $respuestaEstudiante),
            'espacios' => $this->calificarEspacios($question, $respuestaEstudiante),
            'multimedia' => ['es_correcta' => null, 'puntos' => null, 'requiere_revision' => true],
        };
    }

    private function calificarSeleccion(QuizQuestion $q, array $resp): array
    {
        $correctas = array_filter($q->contenido['opciones'], fn($o) => $o['es_correcta']);
        $idCorrectas = array_map(fn($o) => $o['id'], $correctas);
        $coincidencias = array_intersect($resp['seleccionadas'], $idCorrectas);
        $puntosObjeto = (count($coincidencias) / count($idCorrectas)) * $q->puntos;
        
        return [
            'es_correcta' => count($coincidencias) === count($idCorrectas),
            'puntos_obtenidos' => $puntosObjeto,
            'requiere_revision' => false,
        ];
    }

    private function calificarVerdaderoFalso(QuizQuestion $q, array $resp): array
    {
        $esCorrecta = $resp['valor'] === $q->contenido['respuesta_correcta'];
        $puntos = $esCorrecta ? $q->contenido['puntos_respuesta'] : 0;
        
        return [
            'es_correcta' => $esCorrecta,
            'puntos_obtenidos' => $puntos,
            'requiere_revision' => $q->contenido['justificacion_requerida'],
        ];
    }

    private function calificarRelacion(QuizQuestion $q, array $resp): array
    {
        $paresCorrectos = $q->contenido['pares_correctos'];
        $paresEstudiante = $resp['pares'];
        $coincidencias = 0;

        foreach ($paresEstudiante as $par) {
            if (in_array($par, $paresCorrectos)) {
                $coincidencias++;
            }
        }

        $puntos = (count($coincidencias) / count($paresCorrectos)) * $q->puntos;

        return [
            'es_correcta' => count($coincidencias) === count($paresCorrectos),
            'puntos_obtenidos' => $puntos,
            'requiere_revision' => false,
        ];
    }

    private function calificarEspacios(QuizQuestion $q, array $resp): array
    {
        $espacios = $q->contenido['espacios'];
        $correctos = 0;

        foreach ($espacios as $espacio) {
            $respuestaUsuario = $resp['respuestas'][$espacio['id']] ?? '';
            $valida = $this->validarRespuestaEspacio(
                $respuestaUsuario,
                $espacio['respuestas_validas'],
                $espacio['sensible_mayusculas']
            );
            if ($valida) $correctos++;
        }

        $puntos = (count($correctos) / count($espacios)) * $q->puntos;

        return [
            'es_correcta' => count($correctos) === count($espacios),
            'puntos_obtenidos' => $puntos,
            'requiere_revision' => false,
        ];
    }

    private function validarRespuestaEspacio(
        string $respuesta,
        array $validas,
        bool $sensibleMayusculas
    ): bool {
        if (!$sensibleMayusculas) {
            $respuesta = strtolower($respuesta);
            $validas = array_map(fn($v) => strtolower($v), $validas);
        }
        
        return in_array($respuesta, $validas);
    }
}
```

---

## FLUJO COMPLETO

### 1. Profesor crea examen
```
POST /api/profesor/quizzes
  -> Crear quiz (estado=borrador)

POST /api/profesor/quizzes/:id/preguntas
  -> Crear pregunta con contenido JSON
  -> Backend valida estructura JSON según tipo
```

### 2. Estudiante toma examen
```
GET /api/estudiante/quizzes/:id
  -> Obtiene quiz con preguntas (sin respuestas correctas)

POST /api/estudiante/quiz-attempts
  -> Inicia intento (estado=en_progreso)

PUT /api/estudiante/quiz-attempts/:id/respuestas
  -> Guarda respuesta de pregunta (autosave cada 30s)

POST /api/estudiante/quiz-attempts/:id/submit
  -> Envía examen (estado=enviado)
  -> QuizGradingService califica automáticamente
```

### 3. Calificación automática
```
- Tipos 1-4: Calificación automática inmediata
- Tipo 5: Queda pendiente (requiere_revision=true)
- quiz_answers.es_correcta y puntos_obtenidos se populan
```

### 4. Profesor revisa
```
GET /api/profesor/quiz-attempts/:id
  -> Ver intento completo

PUT /api/profesor/quiz-answers/:id/calificar
  -> Calificar pregunta multimedia (tipo 5)

POST /api/profesor/quiz-attempts/:id/publicar-nota
  -> Publica nota final (estudiante puede verla)
```

---

## EXTENSIBILIDAD

Para agregar nuevo tipo de pregunta:

1. Agregar valor al ENUM `quiz_questions.tipo` (nueva migración)
2. Definir contrato JSON en este documento
3. Implementar método en QuizGradingService
4. Crear componente Vue (PreguntaXXX.vue)
5. Registrar en QuizRenderer.vue

**Sin romper datos existentes:** Las preguntas antiguas siguen funcionando.

---

## EJEMPLOS COMPLETOS

### Pregunta de selección múltiple
```json
{
  "id": 42,
  "quiz_id": 10,
  "orden": 1,
  "tipo": "seleccion",
  "enunciado": "¿Cuáles de los siguientes son lenguajes de programación?",
  "contenido": {
    "opciones": [
      {"id": "a", "texto": "Python", "es_correcta": true},
      {"id": "b", "texto": "HTML", "es_correcta": false},
      {"id": "c", "texto": "Java", "es_correcta": true},
      {"id": "d", "texto": "CSS", "es_correcta": false}
    ],
    "permite_multiple": true
  },
  "puntos": 5.0,
  "obligatoria": true,
  "retroalimentacion": "Python y Java son lenguajes de programación. HTML y CSS son lenguajes de marcado."
}
```

### Respuesta del estudiante a pregunta de selección
```json
{
  "id": 1001,
  "attempt_id": 50,
  "question_id": 42,
  "respuesta": {"seleccionadas": ["a", "c"]},
  "es_correcta": true,
  "puntos_obtenidos": 5.0,
  "created_at": "2026-09-07T14:30:00Z"
}
```

---

## FIN DE PASO 2.1

✅ 5 tipos de preguntas definidos
✅ Estructuras JSON completamente especificadas
✅ Lógica de calificación automática para tipos 1-4
✅ Validaciones por tipo
✅ Servicio de calificación (QuizGradingService) codificado
✅ Flujo completo documentado
✅ Ejemplos completos proporcionados

**Próximo Paso:** Paso 2.2 - Crear Controllers y Services en Laravel
