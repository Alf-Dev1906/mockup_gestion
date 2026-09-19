<?php

namespace App\Services;

use App\Models\AttendanceSession;
use Illuminate\Support\Str;

/**
 * Servicio para generación de códigos dinámicos de asistencia
 * 
 * Genera códigos alfanuméricos seguros:
 * - 6 caracteres por defecto
 * - Solo mayúsculas y números
 * - Excluye caracteres confusos: 0, O, I, 1
 * - Verifica unicidad para horarios activos
 */
class AttendanceCodeService
{
    /**
     * Caracteres permitidos (sin caracteres confusos)
     * Excluidos: 0 (cero), O (letra o), I (letra i), 1 (uno)
     * Incluidos: 2-9, A-Z (sin O e I)
     */
    const CHARSET_ALFANUMERICO = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    const CHARSET_NUMERICO = '23456789';

    /**
     * Generar código alfanumérico de 6 caracteres
     * 
     * @param int|null $horarioId Opcional: verificar unicidad para este horario
     * @param int $longitud Longitud del código (default: 6)
     * @return string
     */
    public function generarAlfanumerico(?int $horarioId = null, int $longitud = 6): string
    {
        $maxIntentos = 10;
        $intento = 0;

        do {
            $codigo = $this->generarCodigo(self::CHARSET_ALFANUMERICO, $longitud);
            $intento++;

            // Si no se especifica horario, retornar directamente
            if ($horarioId === null) {
                return $codigo;
            }

            // Verificar que no exista otro código activo para este horario
            $existe = AttendanceSession::where('horario_id', $horarioId)
                ->where('abierta', true)
                ->where('codigo_dinamico', $codigo)
                ->exists();

            if (!$existe) {
                return $codigo;
            }

        } while ($intento < $maxIntentos);

        // Si después de 10 intentos sigue colisionando, lanzar excepción
        throw new \RuntimeException('No se pudo generar un código único después de ' . $maxIntentos . ' intentos');
    }

    /**
     * Generar código numérico de 4 dígitos (para CAPTCHA)
     * 
     * @param int|null $horarioId Opcional: verificar unicidad para este horario
     * @param int $longitud Longitud del código (default: 4)
     * @return string
     */
    public function generarNumerico(?int $horarioId = null, int $longitud = 4): string
    {
        $maxIntentos = 10;
        $intento = 0;

        do {
            $codigo = $this->generarCodigo(self::CHARSET_NUMERICO, $longitud);
            $intento++;

            // Si no se especifica horario, retornar directamente
            if ($horarioId === null) {
                return $codigo;
            }

            // Verificar que no exista otro código activo para este horario
            $existe = AttendanceSession::where('horario_id', $horarioId)
                ->where('abierta', true)
                ->where('codigo_dinamico', $codigo)
                ->exists();

            if (!$existe) {
                return $codigo;
            }

        } while ($intento < $maxIntentos);

        throw new \RuntimeException('No se pudo generar un código único después de ' . $maxIntentos . ' intentos');
    }

    /**
     * Generar código personalizado con charset específico
     * 
     * @param string $charset Conjunto de caracteres permitidos
     * @param int $longitud Longitud del código
     * @return string
     */
    private function generarCodigo(string $charset, int $longitud): string
    {
        $codigo = '';
        $charsetLength = strlen($charset);

        for ($i = 0; $i < $longitud; $i++) {
            $randomIndex = random_int(0, $charsetLength - 1);
            $codigo .= $charset[$randomIndex];
        }

        return $codigo;
    }

    /**
     * Validar formato de código
     * 
     * @param string $codigo Código a validar
     * @param string $tipo Tipo: 'alfanumerico' o 'numerico'
     * @return bool
     */
    public function validarFormato(string $codigo, string $tipo = 'alfanumerico'): bool
    {
        $codigo = strtoupper(trim($codigo));

        if ($tipo === 'numerico') {
            // Solo números del 2 al 9
            return preg_match('/^[2-9]+$/', $codigo) === 1;
        }

        // Alfanumérico: números 2-9 y letras A-Z sin O, I
        return preg_match('/^[23456789ABCDEFGHJKLMNPQRSTUVWXYZ]+$/', $codigo) === 1;
    }

    /**
     * Verificar si un código está activo para un horario
     * 
     * @param string $codigo Código a verificar
     * @param int $horarioId ID del horario
     * @return bool
     */
    public function codigoEstaActivo(string $codigo, int $horarioId): bool
    {
        $session = AttendanceSession::where('horario_id', $horarioId)
            ->where('codigo_dinamico', strtoupper(trim($codigo)))
            ->where('abierta', true)
            ->where('codigo_expira_at', '>', now())
            ->first();

        return $session !== null;
    }

    /**
     * Obtener sesión por código
     * 
     * @param string $codigo Código de la sesión
     * @param int $horarioId ID del horario
     * @return AttendanceSession|null
     */
    public function obtenerSesionPorCodigo(string $codigo, int $horarioId): ?AttendanceSession
    {
        return AttendanceSession::where('horario_id', $horarioId)
            ->where('codigo_dinamico', strtoupper(trim($codigo)))
            ->where('abierta', true)
            ->first();
    }

    /**
     * Generar código formateado para visualización
     * Añade separadores para facilitar lectura (ej: ABC-123)
     * 
     * @param string $codigo Código sin formato
     * @return string Código formateado
     */
    public function formatearParaVisualizacion(string $codigo): string
    {
        $longitud = strlen($codigo);

        // Código de 6: ABC-123
        if ($longitud === 6) {
            return substr($codigo, 0, 3) . '-' . substr($codigo, 3, 3);
        }

        // Código de 4: AB-CD
        if ($longitud === 4) {
            return substr($codigo, 0, 2) . '-' . substr($codigo, 2, 2);
        }

        // Otros casos: retornar sin formato
        return $codigo;
    }

    /**
     * Remover formato de código para procesamiento
     * 
     * @param string $codigoFormateado Código con guiones u otros separadores
     * @return string Código sin formato
     */
    public function limpiarFormato(string $codigoFormateado): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigoFormateado));
    }

    /**
     * Generar múltiples códigos únicos para testing
     * 
     * @param int $cantidad Cantidad de códigos a generar
     * @param string $tipo Tipo: 'alfanumerico' o 'numerico'
     * @param int $longitud Longitud de cada código
     * @return array
     */
    public function generarMultiples(int $cantidad, string $tipo = 'alfanumerico', int $longitud = 6): array
    {
        $codigos = [];
        $vistos = [];

        while (count($codigos) < $cantidad) {
            $codigo = $tipo === 'numerico'
                ? $this->generarNumerico(null, $longitud)
                : $this->generarAlfanumerico(null, $longitud);

            if (!in_array($codigo, $vistos)) {
                $codigos[] = $codigo;
                $vistos[] = $codigo;
            }
        }

        return $codigos;
    }

    /**
     * Estadísticas sobre códigos generados
     * 
     * @param string $tipo Tipo de código
     * @param int $longitud Longitud del código
     * @return array
     */
    public function estadisticasCombinaciones(string $tipo = 'alfanumerico', int $longitud = 6): array
    {
        $charset = $tipo === 'numerico' ? self::CHARSET_NUMERICO : self::CHARSET_ALFANUMERICO;
        $caracteres = strlen($charset);
        $combinaciones = pow($caracteres, $longitud);

        return [
            'tipo' => $tipo,
            'longitud' => $longitud,
            'caracteres_disponibles' => $caracteres,
            'combinaciones_posibles' => $combinaciones,
            'caracteres_excluidos' => $tipo === 'numerico' ? '0, 1' : '0, O, I, 1',
            'formato_ejemplo' => $this->formatearParaVisualizacion(
                $this->generarCodigo($charset, $longitud)
            ),
        ];
    }
}
