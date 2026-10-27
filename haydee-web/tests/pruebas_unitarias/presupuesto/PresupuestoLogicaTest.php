<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use haydee\modelo\Presupuesto;
use haydee\ayuda\Validador;
use haydee\excepciones\NegocioException;

// vendor\bin\phpunit tests\pruebas_unitarias\presupuesto\PresupuestoLogicaTest.php --testdox

#[CoversClass(Presupuesto::class)]
#[Group('unit')]
#[Group('presupuesto')]
class PresupuestoLogicaTest extends TestCase
{
    /**
     * Construye un Presupuesto SIN ejecutar el constructor (que
     * conectaría a MySQL).
     */
    private function presupuestoSinConexion(): Presupuesto
    {
        return (new ReflectionClass(Presupuesto::class))->newInstanceWithoutConstructor();
    }

    // resumirDetalles — función pura

    public static function providerResumirDetalles(): array
    {
        return [
            'null'                          => [null,  0, '0.00'],
            'array vacío'                   => [[],    0, '0.00'],
            'un renglón'                    => [[['monto' => '100.50']], 1, '100.50'],
            'dos renglones'                 => [[['monto' => '100'], ['monto' => '50.25']], 2, '150.25'],
            'monto entero'                  => [[['monto' => '1000']], 1, '1000.00'],
            'monto con 3 decimales'         => [[['monto' => '99.999']], 1, '100.00'],
            'monto cero'                    => [[['monto' => '0']], 1, '0.00'],
            'renglón sin monto se asume 0'  => [[['otro_campo' => 'x']], 1, '0.00'],
            'varios renglones + faltantes'  => [[['monto' => '10'], ['x' => 'y'], ['monto' => '5.5']], 3, '15.50'],
        ];
    }

    #[DataProvider('providerResumirDetalles')]
    public function testResumirDetalles(?array $detalles, int $cantidadEsperada, string $totalEsperado): void
    {
        $p = $this->presupuestoSinConexion();
        $resultado = $p->resumirDetalles($detalles);

        $this->assertArrayHasKey('cantidad_renglones', $resultado);
        $this->assertArrayHasKey('monto_total', $resultado);
        $this->assertSame($cantidadEsperada, $resultado['cantidad_renglones']);
        $this->assertSame($totalEsperado, $resultado['monto_total']);
    }

    //  obtenerReglas

    public function testObtenerReglasRegistrarIncluyeCamposEsperados(): void
    {
        $reglas = Presupuesto::obtenerReglas('registrar_presupuesto');

        $this->assertArrayHasKey('fecha',         $reglas);
        $this->assertArrayHasKey('cuota_reserva', $reglas);
        $this->assertArrayHasKey('observacion',   $reglas);
        $this->assertArrayHasKey('tasa_dolar',    $reglas);
        // Un registro nuevo no debe llevar id_presupuesto
        $this->assertArrayNotHasKey('id_presupuesto', $reglas);
    }

    public function testObtenerReglasModificarIncluyeIdConExists(): void
    {
        $reglas = Presupuesto::obtenerReglas('modificar_presupuesto');

        $this->assertArrayHasKey('id_presupuesto', $reglas);
        $this->assertArrayHasKey('exists', $reglas['id_presupuesto']);
    }

    public function testObtenerReglasEliminarSoloId(): void
    {
        $reglas = Presupuesto::obtenerReglas('eliminar_presupuesto');

        $this->assertArrayHasKey('id_presupuesto', $reglas);
        $this->assertCount(1, $reglas);
    }

    public function testObtenerReglasOperacionDesconocidaDevuelveVacio(): void
    {
        $this->assertSame([], Presupuesto::obtenerReglas('no_existe'));
        $this->assertSame([], Presupuesto::obtenerReglas(''));
        $this->assertSame([], Presupuesto::obtenerReglas('resumirDetalles'));
    }

    public function testObservacionYTasaDolarSonOpcionales(): void
    {
        $reglas = Presupuesto::obtenerReglas('registrar_presupuesto');

        $this->assertTrue($reglas['observacion']['opcional'] ?? false,
            "Se esperaba 'observacion' opcional.");
        $this->assertTrue($reglas['tasa_dolar']['opcional'] ?? false,
            "Se esperaba 'tasa_dolar' opcional.");
    }

    public function testRegistrarPresupuestoNoRequiereBaseDeDatos(): void
    {
        foreach (Presupuesto::obtenerReglas('registrar_presupuesto') as $campo => $regla) {
            $this->assertArrayNotHasKey('exists', $regla,
                "La regla '$campo' tiene 'exists' → las pruebas con Validador necesitarían BD.");
            $this->assertArrayNotHasKey('unique', $regla,
                "La regla '$campo' tiene 'unique' → las pruebas con Validador necesitarían BD.");
        }
    }

    // obtenerReglasDetalles

    public function testObtenerReglasDetallesEstructura(): void
    {
        $reglas = Presupuesto::obtenerReglasDetalles();

        $this->assertArrayHasKey('monto', $reglas);
        $this->assertArrayHasKey('concepto_id', $reglas);
        $this->assertSame(0.01, $reglas['monto']['min']);
        $this->assertArrayHasKey('exists', $reglas['concepto_id']);
    }

    public static function providerMontoDetalle(): array
    {
        return [
            'decimal válido'    => ['10.50',  false],
            'entero válido'     => ['100',    false],
            'cero (bajo el min)' => ['0',     true],
            'negativo'          => ['-5',     true],
            '3 decimales'       => ['10.999', true],
            'texto'             => ['abc',    true],
            'vacío'             => ['',       true],
        ];
    }

    #[DataProvider('providerMontoDetalle')]
    public function testValidarMontoDetalleConValidador(string $monto, bool $debeFallar): void
    {
        $reglas = Presupuesto::obtenerReglasDetalles();
        $v = new Validador();
        // Solo pasamos la regla 'monto' para no disparar 'concepto_id' (que tiene exists)
        $v->validarConjunto(['monto' => $monto], ['monto' => $reglas['monto']]);

        $this->assertSame($debeFallar, $v->tieneErrores(),
            "monto=$monto errores=" . json_encode($v->obtenerErrores()));
    }

    //  4. Validación de cabecera

    public static function providerDatosCabeceraRegistrar(): array
    {
        $base = [
            'fecha'         => '2025-04-01',
            'cuota_reserva' => '100',
            'observacion'   => 'Prueba',
            'tasa_dolar'    => '36.50',
        ];

        return [
            'todo válido'                    => [$base, []],
            'fecha incorrecta (texto)'       => [array_merge($base, ['fecha' => 'ayer']),            ['fecha']],
            'fecha sin ceros a la izquierda' => [array_merge($base, ['fecha' => '2025-4-1']),        ['fecha']],
            'fecha con hora'                 => [array_merge($base, ['fecha' => '2025-04-01 12:00']), ['fecha']],
            'fecha vacía'                    => [array_merge($base, ['fecha' => '']),                ['fecha']],
            'sin campo fecha'                => [array_diff_key($base, ['fecha' => 1]),              ['fecha']],
            'cuota negativa'                 => [array_merge($base, ['cuota_reserva' => '-5']),      ['cuota_reserva']],
            'cuota no numérica'              => [array_merge($base, ['cuota_reserva' => 'abc']),     ['cuota_reserva']],
            'cuota con 3 decimales'          => [array_merge($base, ['cuota_reserva' => '10.999']),  ['cuota_reserva']],
            'sin campo cuota'                => [array_diff_key($base, ['cuota_reserva' => 1]),      ['cuota_reserva']],
            'tasa_dolar negativa'            => [array_merge($base, ['tasa_dolar' => '-1']),         ['tasa_dolar']],
            'observación omitida (opcional)' => [
                ['fecha' => '2025-04-01', 'cuota_reserva' => '100'],
                [],
            ],
            'observación con símbolos inválidos' => [
                array_merge($base, ['observacion' => 'prueba @#$']),
                ['observacion'],
            ],
            'observación excede 255 chars' => [
                array_merge($base, ['observacion' => str_repeat('a', 256)]),
                ['observacion'],
            ],
            'múltiples errores' => [
                array_merge($base, ['fecha' => 'x', 'cuota_reserva' => 'y']),
                ['fecha', 'cuota_reserva'],
            ],
        ];
    }

    #[DataProvider('providerDatosCabeceraRegistrar')]
    public function testValidarCabeceraRegistrarConValidador(array $datos, array $camposConError): void
    {
        $reglas = Presupuesto::obtenerReglas('registrar_presupuesto');
        $v = new Validador();
        $v->validarConjunto($datos, $reglas);
        $errores = $v->obtenerErrores();

        if (empty($camposConError)) {
            $this->assertFalse($v->tieneErrores(),
                'No se esperaban errores, se obtuvo: ' . json_encode($errores));
            return;
        }

        foreach ($camposConError as $campo) {
            $this->assertArrayHasKey($campo, $errores,
                "Se esperaba error en '$campo'. Errores obtenidos: " . json_encode($errores));
        }
    }

    //  Despachador — acciones inexistentes

    public static function providerAccionesInvalidas(): array
    {
        return [
            'acción inventada'         => ['accion_que_no_existe'],
            'método público sin _'     => ['resumirDetalles'],
            'string vacío'             => [''],
            'intento de inyección SQL' => ["consultar'; DROP TABLE presupuesto; --"],
            'acción con guiones'       => ['registrar-presupuesto'],
            'typo común'               => ['registrar_presupuestos'],
        ];
    }

    #[DataProvider('providerAccionesInvalidas')]
    public function testRealizarConsultaAccionInexistenteLanzaExcepcion(string $accion): void
    {
        $p = $this->presupuestoSinConexion();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('no está implementada');

        $p->realizar_consulta($accion);
    }

    //  Validaciones pre-SQL (lanza antes de conectar a la BD)

    public static function providerMesInvalido(): array
    {
        return [
            'mes 0'   => [0],
            'mes -1'  => [-1],
            'mes 13'  => [13],
            'mes 99'  => [99],
        ];
    }

    #[DataProvider('providerMesInvalido')]
    public function testConsultarMesPresupuestoInvalidoLanzaAntesDeSQL(int $mes): void
    {
        $p = $this->presupuestoSinConexion();
        $p->set_fecha((string) $mes);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Mes inválido');

        $p->realizar_consulta('consultar_mes_presupuesto');
    }

    public static function providerAnioInvalido(): array
    {
        return [
            'año 1999' => [1999],
            'año 0'    => [0],
            'año 2101' => [2101],
            'año 9999' => [9999],
        ];
    }

    #[DataProvider('providerAnioInvalido')]
    public function testConsultarAnioPresupuestoInvalidoLanzaAntesDeSQL(int $anio): void
    {
        $p = $this->presupuestoSinConexion();
        $p->set_fecha((string) $anio);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Año inválido');

        $p->realizar_consulta('consultar_anio_presupuesto');
    }
}