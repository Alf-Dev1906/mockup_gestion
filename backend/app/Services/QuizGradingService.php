<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Support\Collection;

/**
 * Servicio de Calificación Automática de Exámenes
 * 
 * Implementa la lógica exacta de calificación para 5 tipos de preguntas:
 * 1. Selección múltiple
 * 2. Verdadero/Falso con justificación
 * 3. Relación de columnas (emparejamiento)
 * 4. Llenado de espacios
 * 5. Multimedia (requiere revisión manual)
 */
class QuizGradingService
{
    /**
     * Calificar intento completo
     * 
     * @param QuizAttempt $attempt
     * @return float Nota total obtenida
     */
    public function grade(QuizAttempt $attempt): float
    {
        $notaTotal = 0;
        $respuestas = $attempt->respuestas()->with('pregunta')->get();

        foreach ($respuestas as $respuesta) {
            $pregunta = $respuesta->pregunta;
            
            // Calificar pregunta individual
            $calificacion = $this->gradeQuestion($pregunta, $respuesta->respuesta);
            
            // Actualizar respuesta con resultado
            $respuesta->update([
                'es_correcta' => $calificacion['es_correcta'],
                'puntos_obtenidos' => $calificacion['puntos_obtenidos'],
            ]);
            
            // Acumular nota
            $notaTotal += $calificacion['puntos_obtenidos'] ?? 0;
        }

        // Actualizar intento con nota total
        $attempt->update([
            'nota_obtenida' => $notaTotal,
        ]);

        return $notaTotal;
    }

    /**
     * Calificar una pregunta individual
     * 
     * @param QuizQuestion $pregunta
     * @param array $respuestaEstudiante
     * @return array [es_correcta, puntos_obtenidos, requiere_revision]
     */
    public function gradeQuestion(
        QuizQuestion $pregunta,
        array $respuestaEstudiante
    ): array {
        return match ($pregunta->tipo) {
            'seleccion' => $this->gradeSelection($pregunta, $respuestaEstudiante),
            'verdadero_falso' => $this->gradeTrueFalse($pregunta, $respuestaEstudiante),
            'relacion_columnas' => $this->gradeMatching($pregunta, $respuestaEstudiante),
            'espacios' => $this->gradeFillBlanks($pregunta, $respuestaEstudiante),
            'multimedia' => $this->gradeMultimedia($pregunta, $respuestaEstudiante),
            default => [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => false,
            ],
        };
    }

    /**
     * TIPO 1: SELECCION MULTIPLE
     * 
     * Lógica de calificación:
     * - Si permite_multiple=false: Solo 1 opción debe estar seleccionada
     * - Comparar selecciones con opciones donde es_correcta=true
     * - Puntuación: (coincidencias / total_correctas) * puntos
     * - es_correcta=true solo si se seleccionan EXACTAMENTE las correctas
     */
    private function gradeSelection(QuizQuestion $pregunta, array $respuesta): array
    {
        $contenido = $pregunta->contenido ?? [];
        $opciones = $contenido['opciones'] ?? [];
        $seleccionadas = $respuesta['seleccionadas'] ?? [];

        // Validar formato
        if (!is_array($seleccionadas) || empty($opciones)) {
            return [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => false,
            ];
        }

        // Validar conteo de selecciones si no permite múltiple
        if (isset($contenido['permite_multiple']) && !$contenido['permite_multiple']) {
            if (count($seleccionadas) !== 1) {
                return [
                    'es_correcta' => false,
                    'puntos_obtenidos' => 0,
                    'requiere_revision' => false,
                ];
            }
        }

        // Obtener IDs de opciones correctas
        $opcionesCorrectas = array_filter(
            $opciones,
            fn($opcion) => $opcion['es_correcta'] === true
        );
        $idsCorrectos = array_map(fn($opcion) => $opcion['id'], $opcionesCorrectas);

        if (empty($idsCorrectos)) {
            return [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => false,
            ];
        }

        // Contar coincidencias exactas
        $coincidencias = array_intersect($seleccionadas, $idsCorrectos);
        
        // es_correcta=true solo si:
        // 1. Todas las opciones seleccionadas son correctas
        // 2. Todas las opciones correctas han sido seleccionadas
        $esCorrecta = count($coincidencias) === count($idsCorrectos)
                     && count($seleccionadas) === count($idsCorrectos);

        // Calcular puntos: Solo puntos completos si es correcta
        $puntosObtenidos = $esCorrecta ? $pregunta->puntos : 0;

        return [
            'es_correcta' => $esCorrecta,
            'puntos_obtenidos' => round($puntosObtenidos, 2),
            'requiere_revision' => false,
        ];
    }

    /**
     * TIPO 2: VERDADERO/FALSO CON JUSTIFICACION
     * 
     * Lógica de calificación:
     * - Calificar respuesta V/F automáticamente (comparación booleana exacta)
     * - Si requiere justificación: requiere_revision=true
     * - Puntos: puntos_respuesta si correcta, 0 si incorrecta
     * - Justificación se califica manualmente después
     */
    private function gradeTrueFalse(QuizQuestion $pregunta, array $respuesta): array
    {
        $contenido = $pregunta->contenido ?? [];
        $valor = $respuesta['valor'] ?? null;

        // Validar formato
        if (!is_bool($valor) && $valor !== 'true' && $valor !== 'false' 
            && $valor !== 1 && $valor !== 0) {
            return [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => $contenido['justificacion_requerida'] ?? false,
            ];
        }

        // Normalizar a booleano
        $valorBool = is_bool($valor) ? $valor : filter_var($valor, FILTER_VALIDATE_BOOLEAN);
        $respuestaCorrecta = $contenido['respuesta_correcta'] ?? false;

        // Comparación exacta
        $esCorrecta = $valorBool === $respuestaCorrecta;

        // Calcular puntos de la respuesta V/F
        $puntosRespuesta = $contenido['puntos_respuesta'] ?? ($pregunta->puntos / 2);
        $puntosObtenidos = $esCorrecta ? $puntosRespuesta : 0;

        // Si la justificación es requerida, quedará pendiente de revisión manual
        $requiereRevision = $contenido['justificacion_requerida'] ?? false;

        return [
            'es_correcta' => $esCorrecta,
            'puntos_obtenidos' => round($puntosObtenidos, 2),
            'requiere_revision' => $requiereRevision,
        ];
    }

    /**
     * TIPO 3: RELACION DE COLUMNAS (EMPAREJAMIENTO)
     * 
     * Lógica de calificación:
     * - Comparar pares sin importar el orden (a:1->x es igual a a:1->x)
     * - Los pares pueden estar en cualquier orden en la respuesta
     * - Puntuación: 100% si todos los pares correctos, 0% si hay error
     * - Puntuación parcial: (pares_correctos / total_pares) * puntos
     */
    private function gradeMatching(QuizQuestion $pregunta, array $respuesta): array
    {
        $contenido = $pregunta->contenido ?? [];
        $paresCorrectos = $contenido['pares_correctos'] ?? [];
        $paresEstudiante = $respuesta['pares'] ?? [];

        // Validar formato
        if (!is_array($paresEstudiante) || empty($paresCorrectos)) {
            return [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => false,
            ];
        }

        // Normalizar pares para comparación robusta
        // Convertir a JSON para comparación independiente del orden de propiedades
        $paresCorrectosNormalizados = array_map(
            fn($par) => json_encode($par, JSON_SORT_KEYS),
            $paresCorrectos
        );

        $paresEstudianteNormalizados = array_map(
            fn($par) => json_encode($par, JSON_SORT_KEYS),
            $paresEstudiante
        );

        // Contar coincidencias exactas
        $coincidencias = count(array_intersect($paresEstudianteNormalizados, $paresCorrectosNormalizados));

        // es_correcta=true solo si todos los pares son correctos y completos
        $esCorrecta = $coincidencias === count($paresCorrectos)
                     && count($paresEstudiante) === count($paresCorrectos);

        // Calcular puntos: 100% si todos correctos, parcial o 0 si hay errores
        $puntosObtenidos = $esCorrecta 
            ? $pregunta->puntos 
            : 0; // Sin puntos parciales para emparejamiento

        return [
            'es_correcta' => $esCorrecta,
            'puntos_obtenidos' => round($puntosObtenidos, 2),
            'requiere_revision' => false,
        ];
    }

    /**
     * TIPO 4: LLENADO DE ESPACIOS
     * 
     * Lógica de calificación:
     * - Validar cada espacio con su lista de respuestas válidas
     * - Aplicar sensibilidad a mayúsculas según configuración del espacio
     * - Trim de espacios en blanco
     * - Puntuación: (espacios_correctos / total_espacios) * puntos
     * - es_correcta=true solo si TODOS los espacios son correctos
     */
    private function gradeFillBlanks(QuizQuestion $pregunta, array $respuesta): array
    {
        $contenido = $pregunta->contenido ?? [];
        $espacios = $contenido['espacios'] ?? [];
        $respuestas = $respuesta['respuestas'] ?? [];

        // Validar formato
        if (!is_array($respuestas) || empty($espacios)) {
            return [
                'es_correcta' => false,
                'puntos_obtenidos' => 0,
                'requiere_revision' => false,
            ];
        }

        $correctos = 0;

        foreach ($espacios as $espacio) {
            $idEspacio = $espacio['id'];
            $respuestaUsuario = $respuestas[$idEspacio] ?? '';
            $respuestasValidas = $espacio['respuestas_validas'] ?? [];
            $sensibleMayusculas = $espacio['sensible_mayusculas'] ?? false;

            // Validar respuesta del espacio
            if ($this->validateBlank($respuestaUsuario, $respuestasValidas, $sensibleMayusculas)) {
                $correctos++;
            }
        }

        // es_correcta=true solo si TODOS los espacios son correctos
        $esCorrecta = $correctos === count($espacios);

        // Calcular puntos: parciales si hay algunos correctos
        $puntosObtenidos = $esCorrecta 
            ? $pregunta->puntos 
            : ($correctos / count($espacios)) * $pregunta->puntos;

        return [
            'es_correcta' => $esCorrecta,
            'puntos_obtenidos' => round($puntosObtenidos, 2),
            'requiere_revision' => false,
        ];
    }

    /**
     * Validar respuesta de un espacio individual
     * 
     * Lógica:
     * - Trim de espacios en blanco
     * - Normalizar según sensibilidad a mayúsculas
     * - Comparación in_array contra respuestas válidas
     */
    private function validateBlank(
        string $respuesta,
        array $respuestasValidas,
        bool $sensibleMayusculas
    ): bool {
        // Trim de espacios
        $respuesta = trim($respuesta);

        if (empty($respuesta) || empty($respuestasValidas)) {
            return false;
        }

        // Aplicar sensibilidad a mayúsculas
        if (!$sensibleMayusculas) {
            $respuesta = strtolower($respuesta);
            $respuestasValidas = array_map(
                fn($r) => strtolower(trim($r)), 
                $respuestasValidas
            );
        } else {
            $respuestasValidas = array_map(fn($r) => trim($r), $respuestasValidas);
        }

        // Verificar si la respuesta está en la lista de válidas
        return in_array($respuesta, $respuestasValidas);
    }

    /**
     * TIPO 5: MULTIMEDIA CON ENTREGA DE ARCHIVO
     * 
     * Lógica de calificación:
     * - Calificación obligatoriamente manual por el profesor
     * - Retorna requiere_revision=true
     * - Es correcta y puntos quedan NULL hasta revisión
     */
    private function gradeMultimedia(QuizQuestion $pregunta, array $respuesta): array
    {
        return [
            'es_correcta' => null,
            'puntos_obtenidos' => null,
            'requiere_revision' => true,
        ];
    }

    /**
     * Obtener resumen estadístico de calificaciones de un quiz
     * 
     * @param Quiz $quiz
     * @return array [total_intentos, promedio, minimo, maximo, desviacion_estandar]
     */
    public function obtenerResumenCalificaciones(Quiz $quiz): array
    {
        $intentos = $quiz->intentos()
                        ->whereIn('estado', ['enviado', 'calificado'])
                        ->get();

        if ($intentos->isEmpty()) {
            return [
                'total_intentos' => 0,
                'promedio' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'desviacion_estandar' => 0,
            ];
        }

        $notas = $intentos->pluck('nota_obtenida')->filter()->toArray();

        if (empty($notas)) {
            return [
                'total_intentos' => $intentos->count(),
                'promedio' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'desviacion_estandar' => 0,
            ];
        }

        return [
            'total_intentos' => $intentos->count(),
            'promedio' => round(array_sum($notas) / count($notas), 2),
            'minimo' => round(min($notas), 2),
            'maximo' => round(max($notas), 2),
            'desviacion_estandar' => round($this->calcularDesviacionEstandar($notas), 2),
        ];
    }

    /**
     * Calcular desviación estándar
     * 
     * Fórmula: σ = √(Σ(x - μ)² / N)
     */
    private function calcularDesviacionEstandar(array $valores): float
    {
        if (count($valores) < 2) {
            return 0;
        }

        $promedio = array_sum($valores) / count($valores);
        $desvios = array_map(fn($v) => pow($v - $promedio, 2), $valores);
        $varianza = array_sum($desvios) / count($desvios);

        return sqrt($varianza);
    }

    /**
     * Métodos compatibilidad hacia atrás
     */

    public function calificarIntento(QuizAttempt $intento): void
    {
        $this->grade($intento);
    }

    public function calificarRespuesta(
        QuizQuestion $pregunta,
        array $respuestaEstudiante
    ): array {
        return $this->gradeQuestion($pregunta, $respuestaEstudiante);
    }
}
