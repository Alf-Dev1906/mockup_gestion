<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceCodeService;

class AttendanceCodeServiceTest extends TestCase
{
    protected AttendanceCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceCodeService();
    }

    /**
     * Test: Generar código alfanumérico
     */
    public function test_genera_codigo_alfanumerico()
    {
        $codigo = $this->service->generarAlfanumerico();
        
        // Debe tener 6 caracteres
        $this->assertEquals(6, strlen($codigo));
        
        // Solo debe contener caracteres válidos (sin 0, O, I, 1)
        $this->assertTrue($this->service->validarFormato($codigo, 'alfanumerico'));
        
        // No debe contener caracteres confusos
        $this->assertStringNotContainsString('0', $codigo);
        $this->assertStringNotContainsString('O', $codigo);
        $this->assertStringNotContainsString('I', $codigo);
        $this->assertStringNotContainsString('1', $codigo);
    }

    /**
     * Test: Generar código numérico
     */
    public function test_genera_codigo_numerico()
    {
        $codigo = $this->service->generarNumerico();
        
        // Debe tener 4 caracteres
        $this->assertEquals(4, strlen($codigo));
        
        // Solo debe contener números 2-9
        $this->assertTrue($this->service->validarFormato($codigo, 'numerico'));
        
        // No debe contener 0 ni 1
        $this->assertStringNotContainsString('0', $codigo);
        $this->assertStringNotContainsString('1', $codigo);
    }

    /**
     * Test: Formatear código para visualización
     */
    public function test_formatear_codigo()
    {
        $codigo6 = 'ABC123';
        $formateado6 = $this->service->formatearParaVisualizacion($codigo6);
        $this->assertEquals('ABC-123', $formateado6);

        $codigo4 = '2345';
        $formateado4 = $this->service->formatearParaVisualizacion($codigo4);
        $this->assertEquals('23-45', $formateado4);
    }

    /**
     * Test: Limpiar formato de código
     */
    public function test_limpiar_formato()
    {
        $codigoFormateado = 'ABC-123';
        $limpio = $this->service->limpiarFormato($codigoFormateado);
        $this->assertEquals('ABC123', $limpio);

        $codigoConEspacios = 'abc 123';
        $limpio2 = $this->service->limpiarFormato($codigoConEspacios);
        $this->assertEquals('ABC123', $limpio2);
    }

    /**
     * Test: Generar múltiples códigos únicos
     */
    public function test_genera_multiples_codigos_unicos()
    {
        $cantidad = 20;
        $codigos = $this->service->generarMultiples($cantidad);
        
        // Debe generar la cantidad solicitada
        $this->assertCount($cantidad, $codigos);
        
        // Todos deben ser únicos
        $unicos = array_unique($codigos);
        $this->assertCount($cantidad, $unicos);
    }

    /**
     * Test: Validar formato de código
     */
    public function test_validar_formato()
    {
        // Alfanumérico válido
        $this->assertTrue($this->service->validarFormato('ABC234', 'alfanumerico'));
        
        // Alfanumérico inválido (contiene O)
        $this->assertFalse($this->service->validarFormato('ABCO34', 'alfanumerico'));
        
        // Numérico válido
        $this->assertTrue($this->service->validarFormato('2345', 'numerico'));
        
        // Numérico inválido (contiene 1)
        $this->assertFalse($this->service->validarFormato('1234', 'numerico'));
    }

    /**
     * Test: Estadísticas de combinaciones
     */
    public function test_estadisticas_combinaciones()
    {
        $stats = $this->service->estadisticasCombinaciones('alfanumerico', 6);
        
        $this->assertEquals('alfanumerico', $stats['tipo']);
        $this->assertEquals(6, $stats['longitud']);
        $this->assertEquals(32, $stats['caracteres_disponibles']); // 8 números + 24 letras
        $this->assertEquals(pow(32, 6), $stats['combinaciones_posibles']);
        
        $statsNum = $this->service->estadisticasCombinaciones('numerico', 4);
        $this->assertEquals(8, $statsNum['caracteres_disponibles']); // 2-9
        $this->assertEquals(pow(8, 4), $statsNum['combinaciones_posibles']);
    }
}
