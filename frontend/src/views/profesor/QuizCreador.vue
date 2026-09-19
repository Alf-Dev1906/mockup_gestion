
<template>
  <AdminLayout>
    <!-- Encabezado -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ quizId ? 'Editar Examen' : 'Nuevo Examen' }}
        </h1>
        <p class="text-gray-500 mt-1">{{ horarioSeleccionado?.materia?.nombre ?? 'Selecciona un horario' }}</p>
      </div>
      <div class="flex gap-2">
        <span v-if="quiz.estado === 'borrador'" class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">BORRADOR</span>
        <span v-else-if="quiz.estado === 'publicado'" class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">PUBLICADO</span>
        <router-link to="/profesor/quizzes" class="text-gray-400 hover:text-gray-600 transition text-sm px-3 py-1">
          ← Volver
        </router-link>
      </div>
    </div>

    <!-- Stepper -->
    <div class="flex items-center gap-2 mb-8">
      <button v-for="(s, i) in pasos" :key="i"
        @click="pasoActual > i + 1 || (pasoActual === i + 1) ? null : null"
        class="flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm transition"
        :class="pasoActual === i + 1
          ? 'bg-emerald-600 text-white shadow'
          : pasoActual > i + 1
            ? 'bg-emerald-100 text-emerald-700 cursor-pointer hover:bg-emerald-200'
            : 'bg-gray-100 text-gray-400 cursor-default'">
        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
          :class="pasoActual === i + 1 ? 'bg-white text-emerald-600' : 'bg-current/20'">
          {{ i + 1 }}
        </span>
        {{ s }}
      </button>
    </div>

    <!-- ═══════════════════════════════════════════════
         PASO 1: INFORMACIÓN GENERAL
    ═══════════════════════════════════════════════ -->
    <div v-if="pasoActual === 1" class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
      <h2 class="text-lg font-bold text-gray-900 mb-6">Información General</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Horario -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Horario / Materia <span class="text-red-500">*</span></label>
          <select v-model="quiz.horario_id" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 focus:border-transparent text-sm text-gray-700">
            <option value="">Seleccionar horario...</option>
            <option v-for="h in horarios" :key="h.id" :value="h.id">
              {{ h.materia?.nombre }} — Sección {{ h.seccion }} ({{ h.periodo_academico }})
            </option>
          </select>
        </div>

        <!-- Título -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Título del Examen <span class="text-red-500">*</span></label>
          <input v-model="quiz.titulo" type="text" placeholder="Ej: Parcial 1 — Algoritmos Recursivos"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 focus:border-transparent text-sm" />
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
          <select v-model="quiz.tipo" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm text-gray-700">
            <option value="examen">📝 Examen</option>
            <option value="quiz">❓ Quiz</option>
            <option value="practica">🧪 Práctica</option>
          </select>
        </div>

        <!-- Duración -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Duración (minutos) <span class="text-red-500">*</span></label>
          <input v-model.number="quiz.duracion_minutos" type="number" min="5" max="300"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm" />
        </div>

        <!-- Intentos -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Intentos permitidos</label>
          <input v-model.number="quiz.intentos_permitidos" type="number" min="1" max="5"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm" />
        </div>

        <!-- Advertencias máximas -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Advertencias máx. antes de auto-envío</label>
          <input v-model.number="quiz.advertencias_max" type="number" min="1" max="10"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm" />
        </div>

        <!-- Fecha inicio -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de inicio</label>
          <input v-model="quiz.fecha_inicio" type="datetime-local"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm" />
        </div>

        <!-- Fecha fin -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de cierre</label>
          <input v-model="quiz.fecha_fin" type="datetime-local"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm" />
        </div>

        <!-- Descripción -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción / Instrucciones</label>
          <textarea v-model="quiz.instrucciones" rows="3" placeholder="Instrucciones generales para el estudiante..."
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-400 text-sm resize-none" />
        </div>

        <!-- Opciones -->
        <div class="md:col-span-2 flex flex-wrap gap-6">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" v-model="quiz.orden_aleatorio" class="w-4 h-4 accent-emerald-500" />
            <span class="text-sm text-gray-700">Orden aleatorio de preguntas</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" v-model="quiz.mostrar_resultado_inmediato" class="w-4 h-4 accent-emerald-500" />
            <span class="text-sm text-gray-700">Mostrar resultado inmediatamente al enviar</span>
          </label>
        </div>
      </div>

      <div class="flex justify-end mt-8">
        <button @click="irPaso(2)" :disabled="!paso1Valido"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold px-8 py-2.5 rounded-xl transition">
          Continuar → Preguntas
        </button>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         PASO 2: PREGUNTAS
    ═══════════════════════════════════════════════ -->
    <div v-if="pasoActual === 2" class="space-y-6">

      <!-- Barra de herramientas -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="font-semibold text-gray-800">{{ preguntas.length }} pregunta(s) — {{ totalPuntos }} pts en total</p>
          <p class="text-xs text-gray-500 mt-0.5">Arrastra para reordenar · Haz clic en una pregunta para editarla</p>
        </div>
        <div class="flex gap-2 flex-wrap">
          <button v-for="tipo in tiposPregunta" :key="tipo.valor"
            @click="agregarPregunta(tipo.valor)"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition hover:shadow-sm"
            :class="tipo.clase">
            <span>{{ tipo.icono }}</span> {{ tipo.label }}
          </button>
        </div>
      </div>

      <!-- Lista de preguntas -->
      <div v-if="preguntas.length === 0" class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center text-gray-400">
        <p class="text-4xl mb-3">📝</p>
        <p class="font-semibold">Sin preguntas aún</p>
        <p class="text-sm mt-1">Usa los botones de arriba para agregar preguntas</p>
      </div>

      <div v-for="(pregunta, idx) in preguntas" :key="pregunta._uid"
        class="bg-white rounded-2xl border shadow-sm transition"
        :class="preguntaAbierta === idx ? 'border-emerald-300 ring-1 ring-emerald-200' : 'border-gray-100 hover:border-gray-200'">

        <!-- Cabecera de pregunta -->
        <div class="flex items-center gap-3 p-4 cursor-pointer" @click="togglePregunta(idx)">
          <span class="text-gray-400 font-bold text-sm w-6 text-center">{{ idx + 1 }}</span>
          <span class="text-base">{{ getTipoPreguntaIcono(pregunta.tipo) }}</span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800 truncate">
              {{ pregunta.enunciado || '— Nueva pregunta —' }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ getTipoPreguntaLabel(pregunta.tipo) }} · {{ pregunta.puntos }} pts</p>
          </div>
          <div class="flex items-center gap-2">
            <button @click.stop="moverPregunta(idx, -1)" :disabled="idx === 0"
              class="p-1 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">↑</button>
            <button @click.stop="moverPregunta(idx, 1)" :disabled="idx === preguntas.length - 1"
              class="p-1 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">↓</button>
            <button @click.stop="eliminarPregunta(idx)"
              class="p-1 rounded text-red-400 hover:text-red-600">🗑</button>
            <span class="text-gray-400 text-sm">{{ preguntaAbierta === idx ? '▲' : '▼' }}</span>
          </div>
        </div>

        <!-- Editor de pregunta -->
        <div v-if="preguntaAbierta === idx" class="border-t border-gray-100 p-5 space-y-4">

          <!-- Enunciado y puntos comunes a todos los tipos -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-3">
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Enunciado *</label>
              <textarea v-model="pregunta.enunciado" rows="2" placeholder="Escribe el enunciado de la pregunta..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Puntos</label>
              <input v-model.number="pregunta.puntos" type="number" min="0.5" max="100" step="0.5"
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400" />
            </div>
          </div>

          <!-- ── EDITOR SELECCION MULTIPLE ── -->
          <div v-if="pregunta.tipo === 'seleccion'" class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-xs font-semibold text-gray-600">Opciones</label>
              <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                <input type="checkbox" v-model="pregunta.contenido.permite_multiple" class="accent-emerald-500" />
                Permitir múltiples respuestas
              </label>
            </div>
            <div v-for="(op, oi) in pregunta.contenido.opciones" :key="oi"
              class="flex items-center gap-2">
              <input :type="pregunta.contenido.permite_multiple ? 'checkbox' : 'radio'"
                :name="'correcta_' + idx"
                :checked="op.es_correcta"
                @change="marcarCorrecta(pregunta, oi, pregunta.contenido.permite_multiple)"
                class="accent-emerald-500 w-4 h-4 flex-shrink-0" />
              <input v-model="op.texto" type="text" :placeholder="'Opción ' + (oi + 1)"
                class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-emerald-400" />
              <button @click="pregunta.contenido.opciones.splice(oi, 1)"
                class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
            </div>
            <button @click="pregunta.contenido.opciones.push({ id: uid(), texto: '', es_correcta: false })"
              class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold flex items-center gap-1">
              + Agregar opción
            </button>
          </div>

          <!-- ── EDITOR VERDADERO/FALSO ── -->
          <div v-if="pregunta.tipo === 'verdadero_falso'" class="space-y-3">
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Afirmación</label>
              <input v-model="pregunta.contenido.afirmacion" type="text" placeholder="Escribe la afirmación..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400" />
            </div>
            <div class="flex items-center gap-4">
              <label class="text-xs font-semibold text-gray-600">Respuesta correcta:</label>
              <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="radio" :value="true" v-model="pregunta.contenido.respuesta_correcta" class="accent-emerald-500" />
                <span class="text-sm text-green-700 font-semibold">✓ Verdadero</span>
              </label>
              <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="radio" :value="false" v-model="pregunta.contenido.respuesta_correcta" class="accent-emerald-500" />
                <span class="text-sm text-red-700 font-semibold">✗ Falso</span>
              </label>
            </div>
            <div class="flex items-center gap-4">
              <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                <input type="checkbox" v-model="pregunta.contenido.justificacion_requerida" class="accent-emerald-500" />
                Requiere justificación escrita
              </label>
              <div v-if="pregunta.contenido.justificacion_requerida" class="flex items-center gap-2">
                <label class="text-xs text-gray-600">Pts justificación:</label>
                <input v-model.number="pregunta.contenido.puntos_justificacion" type="number" min="0" step="0.5"
                  class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-xs" />
              </div>
            </div>
          </div>

          <!-- ── EDITOR RELACION COLUMNAS ── -->
          <div v-if="pregunta.tipo === 'relacion_columnas'" class="space-y-3">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-semibold text-gray-600 mb-1 block">Columna A (Términos)</label>
                <div v-for="(item, ii) in pregunta.contenido.columna_a" :key="ii" class="flex gap-2 mb-1">
                  <span class="text-xs text-gray-400 w-4 mt-2">{{ ii + 1 }}</span>
                  <input v-model="item.texto" type="text" :placeholder="'Término ' + (ii + 1)"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-emerald-400" />
                  <button @click="eliminarPar(pregunta, ii)" class="text-red-400 text-xs">✕</button>
                </div>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-600 mb-1 block">Columna B (Definiciones)</label>
                <div v-for="(item, ii) in pregunta.contenido.columna_b" :key="ii" class="flex gap-2 mb-1">
                  <span class="text-xs text-gray-400 w-4 mt-2">{{ String.fromCharCode(65 + ii) }}</span>
                  <input v-model="item.texto" type="text" :placeholder="'Definición ' + String.fromCharCode(65 + ii)"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-emerald-400" />
                </div>
              </div>
            </div>
            <button @click="agregarPar(pregunta)"
              class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold flex items-center gap-1">
              + Agregar par
            </button>
            <p class="text-xs text-gray-500 bg-emerald-50 rounded-lg px-3 py-2">
              💡 El orden entre columnas se establecerá automáticamente (1↔A, 2↔B, etc.)
            </p>
          </div>

          <!-- ── EDITOR LLENADO DE ESPACIOS ── -->
          <div v-if="pregunta.tipo === 'espacios'" class="space-y-3">
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Plantilla (usa __ para cada espacio)</label>
              <input v-model="pregunta.contenido.plantilla" type="text"
                placeholder="Ej: El __ de Venezuela es __ y la moneda es __"
                @input="actualizarEspacios(pregunta)"
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400" />
              <p class="text-xs text-gray-500 mt-1">Se detectan {{ contarEspacios(pregunta.contenido.plantilla) }} espacio(s)</p>
            </div>
            <div v-for="(esp, ei) in pregunta.contenido.espacios" :key="ei" class="bg-gray-50 rounded-xl p-3 space-y-2">
              <p class="text-xs font-semibold text-gray-700">Espacio {{ ei + 1 }}</p>
              <div class="flex gap-2 items-start">
                <div class="flex-1">
                  <label class="text-xs text-gray-500 mb-0.5 block">Respuestas válidas (separadas por coma)</label>
                  <input :value="esp.respuestas_validas.join(', ')"
                    @input="esp.respuestas_validas = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)"
                    type="text" placeholder="capital, Capital, CAPITAL"
                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                </div>
                <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer mt-5">
                  <input type="checkbox" v-model="esp.sensible_mayusculas" class="accent-emerald-500" />
                  Sensible a mayúsculas
                </label>
              </div>
            </div>
          </div>

          <!-- ── EDITOR MULTIMEDIA ── -->
          <div v-if="pregunta.tipo === 'multimedia'" class="space-y-3">
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Descripción del ejercicio *</label>
              <textarea v-model="pregunta.contenido.descripcion" rows="2" placeholder="Analiza el siguiente material y responde..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-emerald-400" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-xs font-semibold text-gray-600 mb-1 block">Extensiones permitidas</label>
                <input v-model="pregunta.contenido.extensiones_respuesta_permitidas_str"
                  @input="pregunta.contenido.extensiones_respuesta_permitidas = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)"
                  type="text" placeholder="pdf, zip, docx"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-600 mb-1 block">Tamaño máx. (MB)</label>
                <input v-model.number="pregunta.contenido.tamano_max_mb" type="number" min="1" max="50"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm" />
              </div>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Rúbrica de evaluación</label>
              <textarea v-model="pregunta.contenido.rubrica" rows="2" placeholder="Criterios de evaluación..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none" />
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 text-xs text-amber-700">
              ⚠️ Este tipo requiere calificación manual del profesor
            </div>
          </div>

          <!-- Retroalimentación común -->
          <div>
            <label class="text-xs font-semibold text-gray-600 mb-1 block">Retroalimentación (visible tras corrección)</label>
            <input v-model="pregunta.retroalimentacion" type="text" placeholder="Explicación de la respuesta correcta..."
              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-400" />
          </div>
        </div>
      </div>

      <!-- Navegación -->
      <div class="flex justify-between">
        <button @click="irPaso(1)" class="text-gray-500 hover:text-gray-700 font-semibold px-6 py-2.5 rounded-xl border border-gray-200 hover:border-gray-300 transition">
          ← Info General
        </button>
        <button @click="irPaso(3)" :disabled="preguntas.length === 0"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold px-8 py-2.5 rounded-xl transition">
          Continuar → Vista Previa
        </button>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         PASO 3: VISTA PREVIA (is_preview=true)
         NO genera registros de intentos en BD
    ═══════════════════════════════════════════════ -->
    <div v-if="pasoActual === 3" class="space-y-6">

      <!-- Banner de modo preview -->
      <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 flex items-center gap-3">
        <span class="text-2xl">👁️</span>
        <div class="flex-1">
          <p class="font-semibold text-amber-800">Modo Vista Previa — Sin registro en base de datos</p>
          <p class="text-xs text-amber-700 mt-0.5">Esta simulación es local. Las respuestas no se guardan ni generan registros de auditoría.</p>
        </div>
        <button @click="reiniciarPreview" class="text-xs text-amber-700 hover:text-amber-900 font-semibold border border-amber-300 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition">
          🔄 Reiniciar
        </button>
      </div>

      <!-- Encabezado del examen en preview -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ quiz.titulo }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ getTipoLabel(quiz.tipo) }} · {{ quiz.duracion_minutos }} min · {{ totalPuntos }} pts</p>
          </div>
          <!-- Timer fake en preview -->
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2 text-center">
            <p class="text-xs text-emerald-600 font-semibold">Tiempo restante</p>
            <p class="text-2xl font-bold text-emerald-700 font-mono">{{ timerDisplay }}</p>
          </div>
        </div>
        <p v-if="quiz.instrucciones" class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4">
          {{ quiz.instrucciones }}
        </p>
      </div>

      <!-- Preguntas en modo preview -->
      <div v-for="(pregunta, idx) in preguntasParaPreview" :key="idx"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <!-- Cabecera pregunta -->
        <div class="flex items-start gap-3 mb-4">
          <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
            {{ idx + 1 }}
          </span>
          <div class="flex-1">
            <p class="font-semibold text-gray-900">{{ pregunta.enunciado }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ getTipoPreguntaLabel(pregunta.tipo) }} · {{ pregunta.puntos }} pts</p>
          </div>
        </div>

        <!-- Vista preview: Selección -->
        <div v-if="pregunta.tipo === 'seleccion'" class="space-y-2 ml-11">
          <label v-for="(op, oi) in pregunta.contenido.opciones" :key="oi"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition">
            <input :type="pregunta.contenido.permite_multiple ? 'checkbox' : 'radio'"
              :name="'prev_' + idx" v-model="previewRespuestas[idx]"
              :value="op.id" class="accent-emerald-500 w-4 h-4" />
            <span class="text-sm text-gray-700">{{ op.texto }}</span>
          </label>
        </div>

        <!-- Vista preview: Verdadero/Falso -->
        <div v-if="pregunta.tipo === 'verdadero_falso'" class="ml-11 space-y-3">
          <p class="text-sm font-medium text-gray-800 bg-gray-50 rounded-xl p-3">{{ pregunta.contenido.afirmacion }}</p>
          <div class="flex gap-3">
            <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-green-50 hover:border-green-300 transition flex-1 justify-center">
              <input type="radio" :name="'vf_' + idx" :value="true" v-model="previewRespuestas[idx]" class="accent-green-500" />
              <span class="font-semibold text-green-700">✓ Verdadero</span>
            </label>
            <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-red-50 hover:border-red-300 transition flex-1 justify-center">
              <input type="radio" :name="'vf_' + idx" :value="false" v-model="previewRespuestas[idx]" class="accent-red-500" />
              <span class="font-semibold text-red-700">✗ Falso</span>
            </label>
          </div>
          <div v-if="pregunta.contenido.justificacion_requerida">
            <label class="text-xs font-semibold text-gray-600 mb-1 block">Justificación (requerida)</label>
            <textarea rows="2" placeholder="Escribe tu justificación..."
              class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none" />
          </div>
        </div>

        <!-- Vista preview: Relación Columnas -->
        <div v-if="pregunta.tipo === 'relacion_columnas'" class="ml-11">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <p class="text-xs font-semibold text-gray-500 mb-2">TÉRMINOS</p>
              <div v-for="(item, ii) in pregunta.contenido.columna_a" :key="ii"
                class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-900 font-medium">
                {{ ii + 1 }}. {{ item.texto }}
              </div>
            </div>
            <div class="space-y-2">
              <p class="text-xs font-semibold text-gray-500 mb-2">DEFINICIONES (mezcladas)</p>
              <div v-for="(item, ii) in pregunta.contenido.columna_b_mezclada" :key="ii"
                class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-sm text-purple-900">
                <select v-model="previewRespuestas[idx + '_' + ii]"
                  class="text-xs border-0 bg-transparent w-full font-semibold text-gray-700">
                  <option value="">Seleccionar par...</option>
                  <option v-for="a in pregunta.contenido.columna_a" :key="a.id" :value="a.id">
                    {{ a.texto }}
                  </option>
                </select>
                <p class="text-xs mt-1">{{ item.texto }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Vista preview: Espacios -->
        <div v-if="pregunta.tipo === 'espacios'" class="ml-11">
          <p class="text-sm text-gray-700 mb-3">Completa los espacios en blanco:</p>
          <div class="text-sm text-gray-900 leading-loose">
            <template v-for="(parte, pi) in splitPlantilla(pregunta.contenido.plantilla)" :key="pi">
              <span v-if="typeof parte === 'string'">{{ parte }}</span>
              <input v-else type="text" placeholder="..."
                class="border-b-2 border-emerald-400 bg-emerald-50 px-2 py-0.5 text-center text-emerald-800 font-semibold rounded mx-1 w-28 focus:outline-none focus:border-emerald-600" />
            </template>
          </div>
        </div>

        <!-- Vista preview: Multimedia -->
        <div v-if="pregunta.tipo === 'multimedia'" class="ml-11 space-y-3">
          <p class="text-sm text-gray-700">{{ pregunta.contenido.descripcion }}</p>
          <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center text-gray-400">
            <p class="text-3xl mb-2">📎</p>
            <p class="text-sm">Área de entrega de archivo</p>
            <p class="text-xs mt-1">{{ pregunta.contenido.extensiones_respuesta_permitidas?.join(', ') }} · Max {{ pregunta.contenido.tamano_max_mb }}MB</p>
          </div>
        </div>
      </div>

      <!-- Botón enviar fake -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
        <button class="bg-gray-200 text-gray-500 font-semibold px-8 py-3 rounded-xl cursor-not-allowed" disabled>
          🚫 Enviar Examen (deshabilitado en Vista Previa)
        </button>
        <p class="text-xs text-gray-400 mt-2">En modo real, este botón envía el intento y dispara la calificación automática</p>
      </div>

      <!-- Acciones finales -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">¿Todo correcto? Elige una acción:</h3>
        <div class="flex flex-wrap gap-3">
          <button @click="irPaso(2)"
            class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold px-6 py-3 rounded-xl transition text-sm">
            ← Editar Preguntas
          </button>
          <button @click="guardarBorrador" :disabled="saving"
            class="flex-1 border border-emerald-300 hover:border-emerald-400 text-emerald-700 font-semibold px-6 py-3 rounded-xl transition text-sm disabled:opacity-40">
            💾 Guardar Borrador
          </button>
          <button @click="publicar" :disabled="saving || preguntas.length === 0"
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold px-6 py-3 rounded-xl transition text-sm shadow">
            🚀 Publicar Examen
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const route = useRoute()
const toast = useToast()

// ── Estado principal ─────────────────────────────────────────────────────
const quizId = route.params.id || null
const pasoActual = ref(1)
const saving = ref(false)
const horarios = ref([])
const preguntaAbierta = ref(null)

// Flag crítico: is_preview=true → NO llama endpoints de intento
const is_preview = ref(false)
const previewRespuestas = ref({})
let timerInterval = null
const timerSeg = ref(0)

const pasos = ['Información General', 'Preguntas', 'Vista Previa']

// ── Datos del quiz ───────────────────────────────────────────────────────
const quiz = ref({
  horario_id: '',
  titulo: '',
  tipo: 'examen',
  instrucciones: '',
  duracion_minutos: 90,
  intentos_permitidos: 1,
  advertencias_max: 3,
  fecha_inicio: null,
  fecha_fin: null,
  orden_aleatorio: false,
  mostrar_resultado_inmediato: true,
  estado: 'borrador',
})

const preguntas = ref([])

// ── Tipos de preguntas ───────────────────────────────────────────────────
const tiposPregunta = [
  { valor: 'seleccion', label: 'Selección', icono: '☑️', clase: 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' },
  { valor: 'verdadero_falso', label: 'V/F', icono: '✓✗', clase: 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' },
  { valor: 'relacion_columnas', label: 'Relación', icono: '↔', clase: 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100' },
  { valor: 'espacios', label: 'Espacios', icono: '___', clase: 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' },
  { valor: 'multimedia', label: 'Multimedia', icono: '📎', clase: 'bg-pink-50 text-pink-700 border-pink-200 hover:bg-pink-100' },
]

// ── Computed ─────────────────────────────────────────────────────────────
const horarioSeleccionado = computed(() => horarios.value.find(h => h.id === quiz.value.horario_id))

const totalPuntos = computed(() => preguntas.value.reduce((sum, p) => sum + (p.puntos || 0), 0))

const paso1Valido = computed(() => quiz.value.horario_id && quiz.value.titulo.trim().length >= 3 && quiz.value.duracion_minutos > 0)

const timerDisplay = computed(() => {
  const m = Math.floor(timerSeg.value / 60)
  const s = timerSeg.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

// Preguntas para preview: clonar y mezclar columna_b si aplica
const preguntasParaPreview = computed(() => preguntas.value.map(p => {
  const clon = JSON.parse(JSON.stringify(p))
  if (p.tipo === 'relacion_columnas') {
    clon.contenido.columna_b_mezclada = [...clon.contenido.columna_b].sort(() => Math.random() - 0.5)
  }
  return clon
}))

// ── Helpers ──────────────────────────────────────────────────────────────
let _uid = 0
const uid = () => String(++_uid)

const getTipoPreguntaLabel = (tipo) => ({
  seleccion: 'Selección múltiple',
  verdadero_falso: 'Verdadero / Falso',
  relacion_columnas: 'Relación de columnas',
  espacios: 'Llenado de espacios',
  multimedia: 'Multimedia',
}[tipo] || tipo)

const getTipoPreguntaIcono = (tipo) => ({
  seleccion: '☑️',
  verdadero_falso: '✓✗',
  relacion_columnas: '↔',
  espacios: '📝',
  multimedia: '📎',
}[tipo] || '❓')

const getTipoLabel = (tipo) => ({ examen: '📝 Examen', quiz: '❓ Quiz', practica: '🧪 Práctica' }[tipo] || tipo)

const contarEspacios = (plantilla) => (plantilla?.match(/__/g) || []).length

const splitPlantilla = (plantilla) => {
  if (!plantilla) return []
  const partes = []
  let idx = 0
  let espacio = 0
  while (idx < plantilla.length) {
    const pos = plantilla.indexOf('__', idx)
    if (pos === -1) { partes.push(plantilla.slice(idx)); break }
    if (pos > idx) partes.push(plantilla.slice(idx, pos))
    partes.push({ espacio: espacio++ })
    idx = pos + 2
  }
  return partes
}

// ── Acciones de preguntas ────────────────────────────────────────────────
const plantillasContenido = {
  seleccion: () => ({ opciones: [{ id: uid(), texto: '', es_correcta: true }, { id: uid(), texto: '', es_correcta: false }], permite_multiple: false }),
  verdadero_falso: () => ({ afirmacion: '', respuesta_correcta: true, justificacion_requerida: false, puntos_respuesta: 3, puntos_justificacion: 2 }),
  relacion_columnas: () => ({ columna_a: [{ id: uid(), texto: '' }, { id: uid(), texto: '' }], columna_b: [{ id: uid(), texto: '' }, { id: uid(), texto: '' }], pares_correctos: [], mezclar_columna_b: true }),
  espacios: () => ({ plantilla: '', espacios: [] }),
  multimedia: () => ({ descripcion: '', archivo_adjunto_url: null, extensiones_respuesta_permitidas: ['pdf', 'zip', 'docx'], extensiones_respuesta_permitidas_str: 'pdf, zip, docx', tamano_max_mb: 5, calificacion_manual: true, rubrica: '' }),
}

const agregarPregunta = (tipo) => {
  preguntas.value.push({
    _uid: uid(),
    tipo,
    enunciado: '',
    puntos: 5,
    obligatoria: true,
    retroalimentacion: '',
    contenido: plantillasContenido[tipo](),
  })
  preguntaAbierta.value = preguntas.value.length - 1
}

const eliminarPregunta = (idx) => {
  preguntas.value.splice(idx, 1)
  if (preguntaAbierta.value === idx) preguntaAbierta.value = null
}

const togglePregunta = (idx) => { preguntaAbierta.value = preguntaAbierta.value === idx ? null : idx }

const moverPregunta = (idx, dir) => {
  const nuevo = idx + dir
  if (nuevo < 0 || nuevo >= preguntas.value.length) return
  const arr = [...preguntas.value]
  ;[arr[idx], arr[nuevo]] = [arr[nuevo], arr[idx]]
  preguntas.value = arr
  preguntaAbierta.value = nuevo
}

const marcarCorrecta = (pregunta, idx, multiple) => {
  if (!multiple) {
    pregunta.contenido.opciones.forEach((o, i) => { o.es_correcta = i === idx })
  } else {
    pregunta.contenido.opciones[idx].es_correcta = !pregunta.contenido.opciones[idx].es_correcta
  }
}

const agregarPar = (pregunta) => {
  const i = pregunta.contenido.columna_a.length
  pregunta.contenido.columna_a.push({ id: uid(), texto: '' })
  pregunta.contenido.columna_b.push({ id: uid(), texto: '' })
  pregunta.contenido.pares_correctos.push({ a: String(i + 1), b: String.fromCharCode(65 + i) })
}

const eliminarPar = (pregunta, idx) => {
  pregunta.contenido.columna_a.splice(idx, 1)
  pregunta.contenido.columna_b.splice(idx, 1)
}

const actualizarEspacios = (pregunta) => {
  const n = contarEspacios(pregunta.contenido.plantilla)
  const actual = pregunta.contenido.espacios.length
  if (n > actual) {
    for (let i = actual; i < n; i++) pregunta.contenido.espacios.push({ id: i + 1, respuestas_validas: [], sensible_mayusculas: false })
  } else {
    pregunta.contenido.espacios.splice(n)
  }
}

// ── Navegación steps ─────────────────────────────────────────────────────
const irPaso = (paso) => {
  pasoActual.value = paso
  if (paso === 3) iniciarPreview()
}

const iniciarPreview = () => {
  is_preview.value = true
  previewRespuestas.value = {}
  timerSeg.value = quiz.value.duracion_minutos * 60
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => { if (timerSeg.value > 0) timerSeg.value-- }, 1000)
}

const reiniciarPreview = () => {
  previewRespuestas.value = {}
  timerSeg.value = quiz.value.duracion_minutos * 60
}

// ── API: Guardar / Publicar ───────────────────────────────────────────────
const serializarPreguntas = () => preguntas.value.map((p, i) => ({
  orden: i + 1,
  tipo: p.tipo,
  enunciado: p.enunciado,
  puntos: p.puntos,
  obligatoria: p.obligatoria,
  retroalimentacion: p.retroalimentacion,
  contenido: p.contenido,
}))

const guardarBorrador = async () => {
  saving.value = true
  try {
    const payload = { ...quiz.value, estado: 'borrador' }
    let quizGuardado
    if (quizId) {
      const { data } = await api.put(`/profesor/quizzes/${quizId}`, payload)
      quizGuardado = data.data
    } else {
      const { data } = await api.post('/profesor/quizzes', payload)
      quizGuardado = data.data
    }
    // Guardar preguntas
    for (const pregunta of serializarPreguntas()) {
      await api.post(`/profesor/quizzes/${quizGuardado.id}/preguntas`, pregunta)
    }
    quiz.value.estado = 'borrador'
    toast.success('Borrador guardado correctamente')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally {
    saving.value = false
  }
}

const publicar = async () => {
  saving.value = true
  try {
    const payload = { ...quiz.value, estado: 'publicado' }
    let quizGuardado
    if (quizId) {
      const { data } = await api.put(`/profesor/quizzes/${quizId}`, payload)
      quizGuardado = data.data
    } else {
      const { data } = await api.post('/profesor/quizzes', payload)
      quizGuardado = data.data
    }
    for (const pregunta of serializarPreguntas()) {
      await api.post(`/profesor/quizzes/${quizGuardado.id}/preguntas`, pregunta)
    }
    await api.put(`/profesor/quizzes/${quizGuardado.id}`, { estado: 'publicado' })
    toast.success('Examen publicado correctamente')
    router.push('/profesor/quizzes')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al publicar')
  } finally {
    saving.value = false
  }
}

// ── Inicialización ───────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const { data } = await api.get('/profesor/horario')
    horarios.value = data.data || []
  } catch (e) {
    toast.error('Error al cargar horarios')
  }
  if (quizId) {
    try {
      const { data } = await api.get(`/profesor/quizzes/${quizId}`)
      const q = data.data
      Object.assign(quiz.value, q)
      preguntas.value = (q.preguntas || []).map(p => ({ ...p, _uid: uid() }))
    } catch (e) {
      toast.error('Error al cargar examen')
    }
  }
})

onUnmounted(() => { if (timerInterval) clearInterval(timerInterval) })
</script>
