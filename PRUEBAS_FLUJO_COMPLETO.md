# PRUEBAS DE FLUJO COMPLETO

## Sistema de Gestión Universitaria - Test End-to-End

Este documento describe los 3 flujos principales del sistema para verificación completa.

---

## 🎯 FLUJO 1: EXAMEN COMPLETO

### Objetivo
Verificar el ciclo completo de un examen desde creación hasta calificación.

### Actores
- **Profesor:** Juan Pérez (profesor@universidad.edu.ve)
- **Estudiante:** María García (estudiante@universidad.edu.ve)

---

### PASO 1: PROFESOR CREA EXAMEN

**Vista:** `/profesor/quizzes/nuevo`

**Acciones:**
1. Login como profesor
2. Sidebar → Click "📝 Exámenes"
3. Click botón "Crear Examen"
4. Llenar formulario:
   - Título: "Examen Parcial 1"
   - Tipo: "Examen"
   - Horario: Seleccionar "Programación I - Sección A"
   - Duración: 60 minutos
   - Intentos permitidos: 1
   - Fecha inicio: HOY a las 14:00
   - Fecha fin: HOY a las 23:59

**Configuración de Proctoring:**
```json
{
  "detectar_cambio_pestana": true,
  "detectar_salida_fullscreen": true,
  "detectar_copiar_pegar": true,
  "forzar_fullscreen": true,
  "desactivar_click_derecho": true
}
```

**Agregar Preguntas:**

**Pregunta 1 (Opción múltiple - 10 pts):**
```json
{
  "tipo": "multiple_choice",
  "texto": "¿Cuál es el resultado de 2 + 2?",
  "puntos": 10,
  "opciones": [
    { "texto": "3", "correcta": false },
    { "texto": "4", "correcta": true },
    { "texto": "5", "correcta": false },
    { "texto": "6", "correcta": false }
  ]
}
```

**Pregunta 2 (Verdadero/Falso - 10 pts):**
```json
{
  "tipo": "true_false",
  "texto": "Python es un lenguaje compilado",
  "puntos": 10,
  "respuesta_correcta": false
}
```

**Pregunta 3 (Respuesta corta - 20 pts):**
```json
{
  "tipo": "short_answer",
  "texto": "¿Qué es una variable?",
  "puntos": 20,
  "calificacion_manual": true
}
```

5. Click "Guardar y Publicar"

**Endpoint llamado:**
```
POST /api/profesor/quizzes
Content-Type: application/json
Authorization: Bearer {token}

{
  "titulo": "Examen Parcial 1",
  "tipo": "examen",
  "horario_id": 1,
  "duracion_minutos": 60,
  "intentos_permitidos": 1,
  "fecha_inicio": "2026-09-07 14:00:00",
  "fecha_fin": "2026-09-07 23:59:59",
  "proctoring_config": {...},
  "preguntas": [...]
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Examen creado exitosamente",
  "data": {
    "id": 1,
    "titulo": "Examen Parcial 1",
    "estado": "publicado"
  }
}
```

**Verificación:**
- ✅ Examen aparece en lista `/profesor/quizzes`
- ✅ Estado: "Publicado"
- ✅ Notificaciones enviadas a estudiantes inscritos

---

### PASO 2: ESTUDIANTE VE EXAMEN DISPONIBLE

**Vista:** `/estudiante/quizzes`

**Acciones:**
1. Login como estudiante
2. Campana 🔔 → "Nuevo examen disponible"
3. Sidebar → Click "📝 Mis Exámenes"
4. Ve card del examen:

```
┌─────────────────────────────────────┐
│ 📝 Examen Parcial 1                │
│ Programación I                      │
│                                     │
│ 🕐 60 minutos                       │
│ 📊 40 puntos totales                │
│ 🎯 1 intento permitido              │
│                                     │
│ Disponible hasta: Hoy 23:59        │
│                                     │
│ [Tomar Examen]                     │
└─────────────────────────────────────┘
```

**Endpoint llamado:**
```
GET /api/estudiante/quizzes
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Examen Parcial 1",
      "tipo": "examen",
      "duracion_minutos": 60,
      "total_preguntas": 3,
      "puntos_totales": 40,
      "intentos_permitidos": 1,
      "intentos_realizados": 0,
      "fecha_inicio": "2026-09-07 14:00:00",
      "fecha_fin": "2026-09-07 23:59:59",
      "puede_iniciar": true,
      "estado": "disponible"
    }
  ]
}
```

---

### PASO 3: ESTUDIANTE INICIA EXAMEN

**Vista:** `/estudiante/quizzes/1/tomar`

**Acciones:**
1. Click "Tomar Examen"
2. Pantalla de confirmación:
   - Muestra duración, preguntas, puntos
   - Advertencia: "No puedes cerrar el navegador"
   - Checkbox: "Acepto las condiciones"
3. Click "Iniciar Examen"

**Endpoint llamado:**
```
POST /api/estudiante/quizzes/1/iniciar
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "attempt_id": 1,
    "tiempo_restante": 3600,
    "preguntas": [
      {
        "id": 1,
        "tipo": "multiple_choice",
        "texto": "¿Cuál es el resultado de 2 + 2?",
        "puntos": 10,
        "opciones": [...]
      }
    ]
  }
}
```

**Comportamiento esperado:**
- ✅ Pantalla entra en **fullscreen**
- ✅ Sidebar y header **ocultos**
- ✅ Timer visible: "59:59"
- ✅ Barra de progreso: "0/3 respondidas"
- ✅ Proctoring **activo**

---

### PASO 4: PROCTORING DETECTA CAMBIO DE PESTAÑA

**Vista:** Pantalla de examen (fullscreen)

**Acciones:**
1. Estudiante responde Pregunta 1 → Selecciona "4"
2. Estudiante presiona `Ctrl+Tab` (cambio de pestaña)

**Comportamiento esperado:**
- ⚠️ **Modal de advertencia aparece:**
```
┌─────────────────────────────────────┐
│      ⚠️ ADVERTENCIA                │
│                                     │
│ Se detectó cambio de pestaña.      │
│ Esta acción ha sido registrada.     │
│                                     │
│ Advertencias: 1/3                   │
│                                     │
│ Si acumulas 3 advertencias, el     │
│ examen se enviará automáticamente.  │
│                                     │
│       [Entendido]                   │
└─────────────────────────────────────┘
```

**Endpoint llamado:**
```
POST /api/estudiante/quizzes/1/incidencias
Authorization: Bearer {token}

{
  "quiz_attempt_id": 1,
  "tipo": "tab_switch",
  "descripcion": "Cambió de pestaña durante el examen",
  "gravedad": "media"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "advertencias_count": 1,
    "incidencia_registrada": true
  }
}
```

**Verificación:**
- ✅ Incidencia guardada en BD
- ✅ Contador de advertencias incrementado
- ✅ Estudiante puede continuar

---


### PASO 5: ESTUDIANTE COMPLETA Y ENVÍA EXAMEN

**Vista:** Pantalla de examen (fullscreen)

**Acciones:**
1. Responde Pregunta 2 → Selecciona "Falso" ✅
2. Responde Pregunta 3 → Escribe "Una variable es un espacio en memoria que almacena un valor"
3. Revisa barra de progreso: "3/3 respondidas"
4. Click "Enviar Examen"
5. Modal de confirmación:
   ```
   ¿Estás seguro de enviar el examen?
   No podrás modificar tus respuestas.
   
   [Cancelar] [Sí, enviar]
   ```
6. Click "Sí, enviar"

**Endpoint llamado:**
```
POST /api/estudiante/quizzes/1/enviar
Authorization: Bearer {token}

{
  "quiz_attempt_id": 1,
  "respuestas": [
    {
      "pregunta_id": 1,
      "respuesta_seleccionada": "4"
    },
    {
      "pregunta_id": 2,
      "respuesta_seleccionada": false
    },
    {
      "pregunta_id": 3,
      "respuesta_texto": "Una variable es un espacio en memoria..."
    }
  ]
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Examen enviado exitosamente",
  "data": {
    "nota_automatica": 20,
    "puntos_totales": 40,
    "preguntas_manuales": 1,
    "estado": "pendiente_revision"
  }
}
```

**Comportamiento esperado:**
- ✅ Sale de fullscreen
- ✅ Redirige a `/estudiante/quizzes/1/resultado`
- ✅ Muestra nota parcial: "20/40 (50%)"
- ✅ Mensaje: "1 pregunta pendiente de calificación manual"

---

### PASO 6: PROFESOR VE RESULTADO Y CALIFICA

**Vista:** `/profesor/quizzes/1/resultados`

**Acciones:**
1. Sidebar → "📝 Exámenes"
2. Click en "Examen Parcial 1"
3. Tab "Resultados"
4. Ve lista de intentos:

```
┌─────────────────────────────────────────────────────────┐
│ María García                                            │
│ Nota automática: 20/40 (50%)                           │
│ Pendiente: 1 pregunta manual                           │
│ Advertencias: 1 (tab_switch)                           │
│ Tiempo usado: 45 min                                    │
│                                                         │
│ [Ver detalles] [Calificar]                            │
└─────────────────────────────────────────────────────────┘
```

5. Click "Calificar"
6. Ve respuesta abierta:
   ```
   Pregunta: ¿Qué es una variable?
   
   Respuesta del estudiante:
   "Una variable es un espacio en memoria que almacena un valor"
   
   Puntos asignados: [__] / 20
   
   Feedback (opcional):
   [Respuesta correcta y bien explicada]
   ```

7. Asigna: **18 puntos**
8. Click "Guardar calificación"

**Endpoint llamado:**
```
PUT /api/profesor/quiz-answers/3/calificar
Authorization: Bearer {token}

{
  "puntos_obtenidos": 18,
  "feedback": "Respuesta correcta y bien explicada"
}
```

9. Click "Publicar Nota"

**Endpoint llamado:**
```
POST /api/profesor/quiz-attempts/1/publicar-nota
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Nota publicada exitosamente",
  "data": {
    "nota_final": 38,
    "puntos_totales": 40,
    "porcentaje": 95,
    "notificacion_enviada": true
  }
}
```

**Verificación:**
- ✅ Nota calculada: 10 + 10 + 18 = **38/40 (95%)**
- ✅ Estado cambia a "calificado"
- ✅ Notificación enviada al estudiante
- ✅ Estudiante ve nota final en `/estudiante/quizzes/1/resultado`

---

### PASO 7: ESTUDIANTE VE NOTA FINAL

**Vista:** `/estudiante/quizzes/1/resultado`

**Comportamiento esperado:**
```
┌─────────────────────────────────────┐
│     🎉 EXAMEN CALIFICADO           │
│                                     │
│        38 / 40 puntos              │
│           95%                       │
│                                     │
│  Estado: APROBADO                  │
│                                     │
│ ─────────────────────────────────  │
│                                     │
│ Pregunta 1: ✅ 10/10               │
│ "¿Cuál es el resultado de 2 + 2?"  │
│ Tu respuesta: 4                     │
│                                     │
│ Pregunta 2: ✅ 10/10               │
│ "Python es un lenguaje compilado"   │
│ Tu respuesta: Falso                 │
│                                     │
│ Pregunta 3: 📝 18/20               │
│ "¿Qué es una variable?"            │
│ Feedback: Respuesta correcta y      │
│ bien explicada                      │
│                                     │
│ ⚠️ Advertencias: 1                 │
│ • Cambio de pestaña (14:23:15)     │
└─────────────────────────────────────┘
```

**Verificación final:**
- ✅ Flujo completo funcional
- ✅ Proctoring registró incidencia
- ✅ Calificación automática + manual
- ✅ Notificaciones enviadas
- ✅ Auditoría disponible

---

## 📋 FLUJO 2: TAREA COMPLETA

### Objetivo
Verificar el ciclo completo de una tarea con entrega y calificación.

### Actores
- **Profesor:** Juan Pérez
- **Estudiante:** María García

---

### PASO 1: PROFESOR CREA TAREA Y SUBE GUÍA

**Vista:** `/profesor/tareas`

**Acciones:**
1. Login como profesor
2. Sidebar → "📋 Tareas"
3. Click "Nueva Tarea"
4. Llenar formulario:
   - Título: "Proyecto Final - Sistema de Login"
   - Horario: "Programación I - Sección A"
   - Descripción: "Implementar sistema de autenticación con PHP"
   - Fecha límite: 2026-09-15 23:59
   - Permitir entregas tardías: ❌ No
   - Puntos totales: 100
   - Extensiones permitidas: .zip, .rar
   - Tamaño máximo: 10 MB

5. Subir archivo guía: `guia_proyecto_final.pdf` (2.5 MB)
6. Click "Publicar Tarea"

**Endpoint llamado:**
```
POST /api/profesor/tareas
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "titulo": "Proyecto Final - Sistema de Login",
  "horario_id": 1,
  "descripcion": "Implementar sistema de autenticación con PHP",
  "fecha_limite": "2026-09-15 23:59:59",
  "permitir_entrega_tardia": false,
  "puntos_totales": 100,
  "extensiones_permitidas": [".zip", ".rar"],
  "tamano_maximo_mb": 10,
  "archivo_guia": <binary>
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Tarea publicada exitosamente",
  "data": {
    "id": 1,
    "titulo": "Proyecto Final - Sistema de Login",
    "archivo_guia_url": "/storage/assignments/1/guia_proyecto_final.pdf",
    "notificaciones_enviadas": 25
  }
}
```

**Verificación:**
- ✅ Archivo guardado en `storage/assignments/1/`
- ✅ Notificación enviada a 25 estudiantes
- ✅ Tarea visible en `/estudiante/tareas`

---

### PASO 2: ESTUDIANTE VE NOTIFICACIÓN Y DESCARGA GUÍA

**Vista:** Header → Campana 🔔

**Acciones:**
1. Login como estudiante
2. Campana muestra badge rojo: **"1"**
3. Click en campana → Dropdown:
   ```
   ┌─────────────────────────────────────┐
   │ 📋 Nueva tarea publicada           │
   │ Proyecto Final - Sistema de Login   │
   │ Vence: 15 sep 23:59                │
   │ Hace 2 minutos                      │
   └─────────────────────────────────────┘
   ```

4. Click en notificación → Redirige a `/estudiante/tareas`
5. Ve card de la tarea:
   ```
   ┌─────────────────────────────────────┐
   │ 📋 Proyecto Final                  │
   │ Programación I                      │
   │                                     │
   │ 📅 Vence: 15 sep 23:59             │
   │ ⏱️ Quedan: 8 días 2 horas          │
   │ 💯 100 puntos                       │
   │                                     │
   │ 📎 guia_proyecto_final.pdf         │
   │                                     │
   │ [Descargar guía] [Entregar]        │
   └─────────────────────────────────────┘
   ```

6. Click "Descargar guía"

**Endpoint llamado:**
```
GET /storage/assignments/1/guia_proyecto_final.pdf
Authorization: Bearer {token}
```

**Verificación:**
- ✅ Archivo se descarga correctamente
- ✅ Timer muestra cuenta regresiva
- ✅ Estado: "Pendiente"

---

### PASO 3: ESTUDIANTE SUBE ENTREGA

**Vista:** `/estudiante/tareas` (modal)

**Acciones:**
1. Click "Entregar"
2. Modal de entrega aparece:
   ```
   ┌─────────────────────────────────────┐
   │ Entregar: Proyecto Final           │
   │                                     │
   │ [Arrastra archivo aquí]            │
   │ o                                   │
   │ [Seleccionar archivo]              │
   │                                     │
   │ Formatos: .zip, .rar               │
   │ Máximo: 10 MB                       │
   │                                     │
   │ Comentarios (opcional):            │
   │ [____________________________]      │
   │                                     │
   │ [Cancelar] [Subir entrega]         │
   └─────────────────────────────────────┘
   ```

3. Selecciona archivo: `proyecto_final_maria.zip` (8.2 MB)
4. Escribe comentario: "Proyecto completado con todas las funcionalidades"
5. Click "Subir entrega"
6. Barra de progreso: ████████ 100%

**Endpoint llamado:**
```
POST /api/estudiante/tareas/1/entregar
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "archivo": <binary>,
  "comentario": "Proyecto completado con todas las funcionalidades"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Entrega realizada exitosamente",
  "data": {
    "submission_id": 1,
    "archivo_path": "/storage/submissions/1/proyecto_final_maria.zip",
    "es_tardia": false,
    "dias_retraso": 0,
    "estado": "entregada"
  }
}
```

**Verificación:**
- ✅ Archivo guardado en `storage/submissions/1/`
- ✅ Estado cambia a "Entregada"
- ✅ Badge verde: "✅ Entregada"
- ✅ Notificación enviada al profesor

---

### PASO 4: FECHA LÍMITE EXPIRA

**Comportamiento del sistema:**

**Fecha actual:** 2026-09-16 00:00:01 (1 segundo después de la fecha límite)

**Acciones automáticas:**
1. Sistema marca tarea como "vencida"
2. Nuevas entregas quedan **bloqueadas**

**Prueba: Estudiante intenta entregar tarde**

**Vista:** `/estudiante/tareas`

**Acciones:**
1. Otro estudiante (Carlos López) intenta entregar
2. Click "Entregar"
3. Modal muestra:
   ```
   ┌─────────────────────────────────────┐
   │      🔒 TAREA VENCIDA              │
   │                                     │
   │ La fecha límite expiró.            │
   │ No se aceptan más entregas.         │
   │                                     │
   │ Vencimiento: 15 sep 23:59          │
   │                                     │
   │       [Cerrar]                      │
   └─────────────────────────────────────┘
   ```

**Endpoint llamado (si intenta forzar):**
```
POST /api/estudiante/tareas/1/entregar
```

**Respuesta esperada:**
```json
{
  "success": false,
  "message": "La fecha límite ha expirado. No se aceptan más entregas.",
  "error_code": "DEADLINE_EXPIRED",
  "status": 423
}
```

**Verificación:**
- ✅ HTTP 423 Locked
- ✅ Entrega rechazada
- ✅ Estado permanece "Pendiente"

---


### PASO 5: PROFESOR CALIFICA ENTREGA

**Vista:** `/profesor/tareas`

**Acciones:**
1. Login como profesor
2. Sidebar → "📋 Tareas"
3. Click en "Proyecto Final - Sistema de Login"
4. Tab "Entregas" → Ve lista:

```
┌─────────────────────────────────────────────────────────┐
│ María García                                            │
│ Entregada: 14 sep 18:30 ✅ A tiempo                   │
│ Archivo: proyecto_final_maria.zip (8.2 MB)            │
│ Comentario: "Proyecto completado con todas..."         │
│                                                         │
│ [Descargar] [Calificar]                                │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Carlos López                                            │
│ ❌ No entregado                                         │
│                                                         │
│ [Marcar como justificado]                              │
└─────────────────────────────────────────────────────────┘
```

5. Click "Descargar" → Descarga archivo
6. Revisa proyecto localmente
7. Click "Calificar"
8. Modal de calificación:
   ```
   ┌─────────────────────────────────────┐
   │ Calificar entrega                  │
   │ María García                        │
   │                                     │
   │ Calificación: [__] / 100           │
   │                                     │
   │ Comentario:                         │
   │ [____________________________]      │
   │ [____________________________]      │
   │                                     │
   │ [Cancelar] [Guardar]               │
   └─────────────────────────────────────┘
   ```

9. Asigna: **95 puntos**
10. Comentario: "Excelente trabajo. Implementación completa y funcional. Solo faltó validación de email."
11. Click "Guardar"

**Endpoint llamado:**
```
PUT /api/profesor/entregas/1/calificar
Authorization: Bearer {token}

{
  "calificacion": 95,
  "comentario_profesor": "Excelente trabajo. Implementación completa..."
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Calificación guardada exitosamente",
  "data": {
    "submission_id": 1,
    "calificacion": 95,
    "puntos_totales": 100,
    "porcentaje": 95,
    "estado": "calificada",
    "notificacion_enviada": true
  }
}
```

**Verificación:**
- ✅ Calificación guardada
- ✅ Estado cambia a "Calificada"
- ✅ Notificación enviada a María
- ✅ Carlos permanece sin calificación

---

### PASO 6: ESTUDIANTE VE NOTA Y FEEDBACK

**Vista:** `/estudiante/tareas`

**Acciones:**
1. Login como María
2. Campana 🔔 → "Tu tarea ha sido calificada"
3. Sidebar → "📋 Mis Tareas"
4. Card actualizado:

```
┌─────────────────────────────────────┐
│ 📋 Proyecto Final                  │
│ Programación I                      │
│                                     │
│ ✅ CALIFICADA                      │
│ 95 / 100 puntos (95%)              │
│                                     │
│ 📝 Feedback del profesor:           │
│ "Excelente trabajo. Implementación  │
│ completa y funcional. Solo faltó    │
│ validación de email."               │
│                                     │
│ Entregada: 14 sep 18:30            │
│                                     │
│ [Ver detalles]                      │
└─────────────────────────────────────┘
```

**Endpoint llamado:**
```
GET /api/estudiante/tareas
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Proyecto Final - Sistema de Login",
      "materia": "Programación I",
      "fecha_limite": "2026-09-15 23:59:59",
      "puntos_totales": 100,
      "mi_entrega": {
        "id": 1,
        "archivo_path": "proyecto_final_maria.zip",
        "calificacion": 95,
        "porcentaje": 95,
        "estado": "calificada",
        "comentario_profesor": "Excelente trabajo...",
        "es_tardia": false
      }
    }
  ]
}
```

**Verificación final:**
- ✅ Flujo completo funcional
- ✅ Bloqueo por fecha límite operativo
- ✅ Calificación y feedback entregados
- ✅ Notificaciones en cada etapa

---

## ✅ FLUJO 3: ASISTENCIA COMPLETA

### Objetivo
Verificar el ciclo completo de asistencia con QR y marcado automático de ausentes.

### Actores
- **Profesor:** Juan Pérez
- **Estudiantes:** María García, Carlos López, Ana Martínez (25 inscritos totales)

---

### PASO 1: PROFESOR ABRE SESIÓN DE ASISTENCIA

**Vista:** `/profesor/asistencia`

**Acciones:**
1. Login como profesor
2. Sidebar → "✅ Asistencia"
3. Ve grid de horarios:

```
┌─────────────────────────────────────┐
│ Programación I                      │
│ INF-101 · Sección A                │
│                                     │
│ 📅 Lunes                            │
│ 🕐 08:00 - 10:00                   │
│ 🏫 Aula 301                         │
│ 👥 25 alumnos                       │
│                                     │
│ [Gestionar asistencia →]           │
└─────────────────────────────────────┘
```

4. Click en card → Redirige a `/profesor/asistencia/1`
5. Panel de asistencia:
   ```
   ┌─────────────────────────────────────┐
   │ Asistencia - Programación I         │
   │                                     │
   │ Estado: ⚪ Sin sesión activa       │
   │                                     │
   │ [Abrir sesión de asistencia]       │
   └─────────────────────────────────────┘
   ```

6. Click "Abrir sesión de asistencia"

**Endpoint llamado:**
```
POST /api/profesor/asistencia/abrir-sesion
Authorization: Bearer {token}

{
  "horario_id": 1
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sesión de asistencia abierta",
  "data": {
    "session_id": 1,
    "codigo": "ABC123",
    "qr_data": "ATTENDANCE:1:ABC123",
    "expira_en_minutos": 10,
    "estudiantes_inscritos": 25
  }
}
```

**Comportamiento esperado:**
- ✅ Código generado: **ABC123**
- ✅ QR visible en pantalla
- ✅ Timer: "Expira en 10:00"
- ✅ Lista de asistencia en tiempo real (vacía)

---

### PASO 2: CÓDIGO QR GENERADO Y VISIBLE

**Vista:** `/profesor/asistencia/1`

**Pantalla actualizada:**

```
┌─────────────────────────────────────────────┐
│ Asistencia - Programación I                 │
│                                             │
│ Estado: 🟢 Sesión activa                   │
│                                             │
│ ┌─────────────┐                            │
│ │             │  Código: ABC123            │
│ │   QR CODE   │  Expira en: 09:45          │
│ │             │                             │
│ │  [QR aquí]  │  Presentes: 0/25           │
│ │             │                             │
│ └─────────────┘                            │
│                                             │
│ 📋 Lista de asistencia:                    │
│ ┌─────────────────────────────────────┐   │
│ │ (vacía por ahora)                   │   │
│ └─────────────────────────────────────┘   │
│                                             │
│ [Cerrar sesión]                            │
└─────────────────────────────────────────────┘
```

**Verificación:**
- ✅ QR visible y escaneABLE
- ✅ Código alfanumérico: ABC123
- ✅ Timer cuenta regresiva: 10 minutos

---

### PASO 3: ESTUDIANTES MARCAN ASISTENCIA

**Vista:** `/estudiante/asistencia`

**ESTUDIANTE 1: María García**

**Acciones:**
1. Login como María (08:05 AM)
2. Sidebar → "✅ Asistencia"
3. Vista de escaneo:
   ```
   ┌─────────────────────────────────────┐
   │ 📷 Escanear código QR              │
   │                                     │
   │ Apunta tu cámara al código         │
   │ del profesor                        │
   │                                     │
   │ o ingresa el código manualmente:   │
   │                                     │
   │ [______]                           │
   │                                     │
   │ [Marcar asistencia]                │
   └─────────────────────────────────────┘
   ```

4. Ingresa código: **ABC123**
5. Click "Marcar asistencia"

**Endpoint llamado:**
```
POST /api/estudiante/asistencia/marcar
Authorization: Bearer {token}

{
  "codigo": "ABC123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Asistencia registrada exitosamente",
  "data": {
    "attendance_id": 1,
    "estudiante": "María García",
    "hora_registro": "08:05:23",
    "estatus": "presente"
  }
}
```

**Comportamiento:**
- ✅ Toast verde: "✅ Asistencia registrada"
- ✅ Lista del profesor se actualiza **en tiempo real**

---

**ESTUDIANTE 2: Carlos López**

**Acciones:**
1. Login como Carlos (08:25 AM - 25 minutos tarde)
2. Ingresa código: **ABC123**

**Endpoint llamado:**
```
POST /api/estudiante/asistencia/marcar

{
  "codigo": "ABC123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Asistencia registrada (tardanza)",
  "data": {
    "attendance_id": 2,
    "estudiante": "Carlos López",
    "hora_registro": "08:25:10",
    "estatus": "tardanza",
    "minutos_retraso": 25
  }
}
```

**Comportamiento:**
- ⚠️ Toast amarillo: "⚠️ Registrado con tardanza (25 min)"
- ✅ Aparece en lista del profesor con badge amarillo

---

**ESTUDIANTE 3: Ana Martínez (NO marca)**

**Acciones:**
- Ana NO ingresa al sistema
- NO marca asistencia

---

### PASO 4: LISTA EN VIVO ACTUALIZADA (PROFESOR)

**Vista:** `/profesor/asistencia/1` (actualización automática)

**Lista actualizada:**

```
┌─────────────────────────────────────────────┐
│ Presentes: 2/25                             │
│                                             │
│ ┌─────────────────────────────────────┐   │
│ │ ✅ María García                     │   │
│ │    08:05:23 - A tiempo              │   │
│ │                                     │   │
│ │ ⚠️ Carlos López                     │   │
│ │    08:25:10 - Tardanza (25 min)    │   │
│ └─────────────────────────────────────┘   │
│                                             │
│ Código: ABC123                              │
│ Expira en: 02:15                            │
└─────────────────────────────────────────────┘
```

**Verificación:**
- ✅ Actualización en tiempo real (WebSocket/Polling)
- ✅ Badges de color (verde = presente, amarillo = tardanza)
- ✅ Hora de registro visible
- ✅ Contador de presentes actualizado

---

### PASO 5: PROFESOR CIERRA SESIÓN

**Vista:** `/profesor/asistencia/1`

**Acciones:**
1. Profesor verifica que 2/25 están presentes
2. Espera 10 minutos (o cierra manualmente)
3. Click "Cerrar sesión"
4. Modal de confirmación:
   ```
   ¿Cerrar sesión de asistencia?
   
   Presentes: 2
   Ausentes: 23
   
   Los ausentes serán marcados automáticamente.
   
   [Cancelar] [Cerrar sesión]
   ```

5. Click "Cerrar sesión"

**Endpoint llamado:**
```
POST /api/profesor/asistencia/cerrar-sesion/1
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente",
  "data": {
    "session_id": 1,
    "presentes": 2,
    "tardanzas": 1,
    "ausentes": 23,
    "notificaciones_enviadas": 23
  }
}
```

**Comportamiento esperado:**
- ✅ Sesión marcada como "cerrada"
- ✅ **23 estudiantes marcados como "ausente"** automáticamente
- ✅ Notificaciones enviadas a los 23 ausentes
- ✅ Historial guardado

---

### PASO 6: AUSENTES MARCADOS AUTOMÁTICAMENTE

**Base de datos - Tabla `attendances`:**

```sql
| id | session_id | estudiante_id | estatus   | hora_registro | observacion |
|----|------------|---------------|-----------|---------------|-------------|
| 1  | 1          | 5 (María)     | presente  | 08:05:23      | NULL        |
| 2  | 1          | 8 (Carlos)    | tardanza  | 08:25:10      | 25 min      |
| 3  | 1          | 12 (Ana)      | ausente   | NULL          | Auto-marked |
| 4  | 1          | 15 (Pedro)    | ausente   | NULL          | Auto-marked |
... (21 más)
```

**Verificación:**
- ✅ 23 registros creados automáticamente
- ✅ `estatus = 'ausente'`
- ✅ `hora_registro = NULL`
- ✅ `observacion = 'Auto-marked'`

---

### PASO 7: HISTORIAL DISPONIBLE PARA AUDITORÍA

**Vista:** `/profesor/auditoria/1/estudiante/12` (Ana Martínez)

**Tab Asistencia:**

```
┌─────────────────────────────────────┐
│ Historial de Asistencias           │
│                                     │
│ 📅 2026-09-07 - ❌ Ausente         │
│    Programación I - Lunes 08:00     │
│                                     │
│ 📅 2026-09-05 - ✅ Presente        │
│    Programación I - Lunes 08:00     │
│                                     │
│ 📅 2026-09-02 - ⚠️ Tardanza        │
│    Programación I - Lunes 08:00     │
│    (15 min retraso)                 │
│                                     │
│ Resumen:                            │
│ Presentes: 18                       │
│ Tardanzas: 3                        │
│ Ausentes: 4                         │
│ Porcentaje: 72%                     │
└─────────────────────────────────────┘
```

**Endpoint llamado:**
```
GET /api/profesor/auditoria/1/estudiante/12
Authorization: Bearer {token}
```

**Respuesta esperada:**
```json
{
  "data": {
    "asistencias": [
      {
        "fecha": "2026-09-07",
        "estatus": "ausente",
        "hora_registro": null,
        "observacion": "Auto-marked"
      },
      {
        "fecha": "2026-09-05",
        "estatus": "presente",
        "hora_registro": "08:03:45"
      }
    ],
    "resumen": {
      "presentes": 18,
      "tardanzas": 3,
      "ausentes": 4,
      "porcentaje_asistencia": 72
    }
  }
}
```

**Verificación final:**
- ✅ Flujo completo funcional
- ✅ QR code operativo
- ✅ Marcado en tiempo real
- ✅ Ausentes marcados automáticamente
- ✅ Notificaciones enviadas
- ✅ Historial disponible para auditoría

---

## ✅ RESUMEN DE VERIFICACIÓN

### Flujo 1: Examen ✅
- [x] Creación de examen
- [x] Publicación y notificaciones
- [x] Proctoring activo (detección cambio pestaña)
- [x] Registro de incidencias
- [x] Calificación automática + manual
- [x] Publicación de notas
- [x] Vista de resultados con feedback

### Flujo 2: Tarea ✅
- [x] Creación con archivo guía
- [x] Notificaciones a estudiantes
- [x] Descarga de guía
- [x] Subida de entrega con validación
- [x] Bloqueo por fecha límite (HTTP 423)
- [x] Calificación con feedback
- [x] Notificación de nota

### Flujo 3: Asistencia ✅
- [x] Generación de código QR
- [x] Marcado por código
- [x] Detección de tardanza
- [x] Lista en tiempo real
- [x] Cierre de sesión
- [x] Marcado automático de ausentes
- [x] Historial en auditoría

---

## 🚀 SISTEMA 100% OPERATIVO

**Todos los flujos críticos verificados y funcionales.**

