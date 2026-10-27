<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use haydee\modelo\Mensualidad;
use haydee\ayuda\Validador;
use haydee\excepciones\NegocioException;

// vendor\bin\phpunit tests\pruebas_unitarias\mensualidad\MensualidadLogicaTest.php --testdox

#[CoversClass(Mensualidad::class)]
#[Group('unit')]
#[Group('mensualidad')]
class MensualidadLogicaTest extends TestCase
{
    /**
     * Construye una Mensualidad SIN ejecutar el constructor
     */
    private function mensualidadSinConexion(): Mensualidad
    {
        return (new ReflectionClass(Mensualidad::class))->newInstanceWithoutConstructor();
    }

    // resumirDetalles — función pura

    public static function providerResumirDetalles(): array
    {
        return [
            'null'                          => [null,  0, '0.00', '0.00'],
            'array vacío'                   => [[],    0, '0.00', '0.00'],
            'un apartamento'                => [[['monto' => '100.50']], 1, '100.50', '0.00'],
            'un apto con descuento'         => [[['monto' => '100', 'descuento' => '10']], 1, '100.00', '10.00'],
            'dos aptos'                     => [[['monto' => '100'], ['monto' => '50.25']], 2, '150.25', '0.00'],
            'dos aptos con descuentos'      => [[
                                                    ['monto' => '100', 'descuento' => '5'],
                                                    ['monto' => '50',  'descuento' => '2.5'],
                                                ], 2, '150.00', '7.50'],
            'monto cero'                    => [[['monto' => '0']], 1, '0.00', '0.00'],
            'monto faltante (asume 0)'      => [[['descuento' => '5']], 1, '0.00', '5.00'],
            'renglón sin campos monetarios' => [[['otro' => 'x']], 1, '0.00', '0.00'],
            'monto no numérico (cast 0)'    => [[['monto' => 'abc']], 1, '0.00', '0.00'],
            'suma con coma flotante'        => [[['monto' => '0.1'], ['monto' => '0.2']], 2, '0.30', '0.00'],
            'mezcla válidos e inválidos'    => [[
                                                    ['monto' => '10'],
                                                    ['otro' => 'y'],
                                                    ['monto' => '5.5'],
                                                ], 3, '15.50', '0.00'],
        ];
    }

    #[DataProvider('providerResumirDetalles')]
    public function testResumirDetalles(
        ?array $detalles, int $cantidadEsperada, string $totalEsperado, string $descuentoEsperado
    ): void {
        $m = $this->mensualidadSinConexion();
        $r = $m->resumirDetalles($detalles);

        $this->assertArrayHasKey('cantidad_apartamentos', $r);
        $this->assertArrayHasKey('monto_total_generado',  $r);
        $this->assertArrayHasKey('descuentos_aplicados',  $r);
        $this->assertSame($cantidadEsperada,  $r['cantidad_apartamentos']);
        $this->assertSame($totalEsperado,     $r['monto_total_generado']);
        $this->assertSame($descuentoEsperado, $r['descuentos_aplicados']);
    }

    //  obtenerReglas

    public function testObtenerReglasRegistrarNoRequiereBaseDeDatos(): void
    {
        $reglas = Mensualidad::obtenerReglas('registrar');

        $this->assertArrayHasKey('mes',                 $reglas);
        $this->assertArrayHasKey('anio',                $reglas);
        $this->assertArrayHasKey('tasa_dolar',          $reglas);
        $this->assertArrayHasKey('porcentaje_interes',  $reglas);
        $this->assertArrayHasKey('limite_mensualidad',  $reglas);

        foreach ($reglas as $campo => $regla) {
            if ($campo === '__metodo_http_permitido__') continue;
            $this->assertArrayNotHasKey('exists', $regla,
                "La regla '$campo' tiene 'exists' → no es pura.");
            $this->assertArrayNotHasKey('unique', $regla,
                "La regla '$campo' tiene 'unique' → no es pura.");
        }
    }

    public function testObtenerReglasModificarIncluyePeriodoConExists(): void
    {
        $reglas = Mensualidad::obtenerReglas('modificar');

        $this->assertArrayHasKey('periodo_id', $reglas);
        $this->assertArrayHasKey('exists', $reglas['periodo_id']);
    }

    public function testObtenerReglasEliminarSoloTienePeriodoId(): void
    {
        $reglas = Mensualidad::obtenerReglas('eliminar');

        $this->assertArrayHasKey('periodo_id', $reglas);
        // Solo debe quedar la regla + el marcador HTTP
        $this->assertArrayNotHasKey('mes',               $reglas);
        $this->assertArrayNotHasKey('anio',              $reglas);
        $this->assertArrayNotHasKey('tasa_dolar',        $reglas);
    }

    public function testObtenerReglasConsultasSinCamposSoloLlevanMarcaHTTP(): void
    {
        foreach (['consultarPorMeses', 'verificarMeses', 'consultar_meses_mensualidad',
                  'consultar_kpis', 'consultar_estadisticas_inicio', 'consultar_tarjetas_resumen'] as $op) {
            $reglas = Mensualidad::obtenerReglas($op);
            $this->assertArrayHasKey('__metodo_http_permitido__', $reglas, "op=$op");
            // No hay reglas de campo reales
            $this->assertCount(1, $reglas, "op=$op");
        }
    }

    public function testObtenerReglasOperacionDesconocidaDevuelveVacio(): void
    {
        $this->assertSame([], Mensualidad::obtenerReglas('no_existe'));
        $this->assertSame([], Mensualidad::obtenerReglas(''));
        $this->assertSame([], Mensualidad::obtenerReglas('resumirDetalles'));
    }

    public function testPorcentajeYLimiteSonOpcionales(): void
    {
        $reglas = Mensualidad::obtenerReglas('registrar');

        $this->assertTrue($reglas['porcentaje_interes']['opcional'] ?? false,
            "'porcentaje_interes' debe ser opcional.");
        $this->assertTrue($reglas['limite_mensualidad']['opcional'] ?? false,
            "'limite_mensualidad' debe ser opcional.");
    }

    //  obtenerReglasDetalles

    public function testObtenerReglasDetallesEstructura(): void
    {
        $reglas = Mensualidad::obtenerReglasDetalles();

        $this->assertArrayHasKey('monto',          $reglas);
        $this->assertArrayHasKey('descuento',      $reglas);
        $this->assertArrayHasKey('id_apartamento', $reglas);
        $this->assertArrayHasKey('id_mensualidad', $reglas);

        // monto y descuento son puros
        $this->assertArrayNotHasKey('exists', $reglas['monto']);
        $this->assertArrayNotHasKey('exists', $reglas['descuento']);
        // id_apartamento e id_mensualidad requieren BD
        $this->assertArrayHasKey('exists', $reglas['id_apartamento']);
        $this->assertArrayHasKey('exists', $reglas['id_mensualidad']);
    }

    public static function providerMontoDetalle(): array
    {
        return [
            'entero 100'   => ['100',   false],
            'decimal 10.50' => ['10.50', false],
            'cero (bajo min)' => ['0',    true],
            'negativo'     => ['-5',    true],
            'texto'        => ['abc',   true],
            'vacío'        => ['',      true],
            'punto suelto' => ['.5',    true],
        ];
    }

    #[DataProvider('providerMontoDetalle')]
    public function testValidarMontoDetalle(string $monto, bool $debeFallar): void
    {
        $reglas = Mensualidad::obtenerReglasDetalles();
        $v = new Validador();
        $v->validarConjunto(['monto' => $monto], ['monto' => $reglas['monto']]);

        $this->assertSame($debeFallar, $v->tieneErrores(),
            "monto=$monto errores=" . json_encode($v->obtenerErrores()));
    }

    public static function providerDescuentoDetalle(): array
    {
        return [
            'cero válido'    => ['0',    false],
            'entero 10'      => ['10',   false],
            'decimal 5.25'   => ['5.25', false],
            'negativo'       => ['-5',   true],
            'texto'          => ['abc',  true],
        ];
    }

    #[DataProvider('providerDescuentoDetalle')]
    public function testValidarDescuentoDetalle(string $descuento, bool $debeFallar): void
    {
        $reglas = Mensualidad::obtenerReglasDetalles();
        $v = new Validador();
        $v->validarConjunto(['descuento' => $descuento], ['descuento' => $reglas['descuento']]);

        $this->assertSame($debeFallar, $v->tieneErrores(),
            "descuento=$descuento errores=" . json_encode($v->obtenerErrores()));
    }

    // Validación de cabecera 'registrar' con Validador

    public static function providerDatosCabeceraRegistrar(): array
    {
        $base = ['mes' => '3', 'anio' => '2025', 'tasa_dolar' => '36.50'];
        return [
            'todo válido'                      => [$base, []],
            // mes
            'mes=0 (inválido)'                 => [array_merge($base, ['mes' => '0']),  ['mes']],
            'mes=13 (inválido)'                => [array_merge($base, ['mes' => '13']), ['mes']],
            'mes=abc (inválido)'               => [array_merge($base, ['mes' => 'abc']), ['mes']],
            'mes=1 (válido)'                   => [array_merge($base, ['mes' => '1']),  []],
            'mes=01 (válido)'                  => [array_merge($base, ['mes' => '01']), []],
            'mes=12 (válido)'                  => [array_merge($base, ['mes' => '12']), []],
            'mes vacío (requerido)'            => [array_merge($base, ['mes' => '']),   ['mes']],
            'mes omitido (requerido)'          => [array_diff_key($base, ['mes' => 1]), ['mes']],
            // anio
            'anio=1999 (bajo min)'             => [array_merge($base, ['anio' => '1999']),  ['anio']],
            'anio=2101 (sobre max)'            => [array_merge($base, ['anio' => '2101']),  ['anio']],
            'anio=2000 (min, válido)'          => [array_merge($base, ['anio' => '2000']),  []],
            'anio=2100 (max, válido)'          => [array_merge($base, ['anio' => '2100']),  []],
            'anio=20255 (5 dígitos)'           => [array_merge($base, ['anio' => '20255']), ['anio']],
            'anio vacío (requerido)'           => [array_merge($base, ['anio' => '']),      ['anio']],
            // tasa_dolar
            'tasa=0 (min, válido)'             => [array_merge($base, ['tasa_dolar' => '0']),      []],
            'tasa negativa'                    => [array_merge($base, ['tasa_dolar' => '-1']),     ['tasa_dolar']],
            'tasa con 4 decimales'             => [array_merge($base, ['tasa_dolar' => '36.5000']), []],
            'tasa con 5 decimales'             => [array_merge($base, ['tasa_dolar' => '36.50000']), ['tasa_dolar']],
            'tasa vacía (requerido)'           => [array_merge($base, ['tasa_dolar' => '']),       ['tasa_dolar']],
            // opcionales
            'sin porcentaje_interes (opcional)' => [$base, []],
            'sin limite_mensualidad (opcional)' => [$base, []],
            'porcentaje válido 10.5'           => [array_merge($base, ['porcentaje_interes' => '10.5']),  []],
            'porcentaje inválido 3 dec.'       => [array_merge($base, ['porcentaje_interes' => '10.999']), ['porcentaje_interes']],
            'limite válido 15'                 => [array_merge($base, ['limite_mensualidad' => '15']),   []],
            'limite inválido decimal'          => [array_merge($base, ['limite_mensualidad' => '1.5']),  ['limite_mensualidad']],
            // múltiples
            'múltiples errores' => [
                array_merge($base, ['mes' => '0', 'anio' => '1999', 'tasa_dolar' => '-1']),
                ['mes', 'anio', 'tasa_dolar'],
            ],
        ];
    }

    #[DataProvider('providerDatosCabeceraRegistrar')]
    public function testValidarCabeceraRegistrar(array $datos, array $camposConError): void
    {
        $reglas = Mensualidad::obtenerReglas('registrar');
        $v = new Validador();
        $v->validarConjunto($datos, $reglas);
        $errores = $v->obtenerErrores();

        if (empty($camposConError)) {
            $this->assertFalse($v->tieneErrores(),
                'No se esperaban errores. Obtenidos: ' . json_encode($errores));
            return;
        }

        foreach ($camposConError as $campo) {
            $this->assertArrayHasKey($campo, $errores,
                "Se esperaba error en '$campo'. Obtenidos: " . json_encode($errores));
        }
    }

    //  Validación de operaciones con 'mes' + 'anio'
    //     (consultar_mensualidad_apartamentos / tasa_dolar / cabecera)

    public static function providerValidacionMesAnio(): array
    {
        $ops = [
            'consultar_mensualidad_apartamentos',
            'consultar_tasa_dolar_mensualidades',
            'consultar_cabecera_mensualidad',
        ];

        $casos = [
            'datos válidos'        => ['3',    '2025',    []],
            'mes=13 inválido'      => ['13',   '2025',    ['mes']],
            'mes vacío'            => ['',     '2025',    ['mes']],
            'año=1999 bajo min'    => ['3',    '1999',    ['anio']],
            'año=2101 sobre max'   => ['3',    '2101',    ['anio']],
            'año=2231025 (7 díg.)' => ['3',    '2231025', ['anio']],
            'ambos vacíos'         => ['',     '',        ['mes', 'anio']],
        ];

        $resultado = [];
        foreach ($ops as $op) {
            foreach ($casos as $nombre => $caso) {
                [$mes, $anio, $camposError] = $caso;
                $resultado["$op - $nombre"] = [$op, $mes, $anio, $camposError];
            }
        }
        return $resultado;
    }

    #[DataProvider('providerValidacionMesAnio')]
    public function testValidarOperacionesConMesAnio(
        string $operacion, string $mes, string $anio, array $camposConError
    ): void {
        $reglas = Mensualidad::obtenerReglas($operacion);
        $v = new Validador();
        $v->validarConjunto(['mes' => $mes, 'anio' => $anio], $reglas);
        $errores = $v->obtenerErrores();

        if (empty($camposConError)) {
            $this->assertFalse($v->tieneErrores(),
                "op=$operacion mes=$mes anio=$anio — no se esperaban errores. " . json_encode($errores));
            return;
        }

        foreach ($camposConError as $campo) {
            $this->assertArrayHasKey($campo, $errores,
                "op=$operacion mes=$mes anio=$anio — se esperaba error en '$campo'. " . json_encode($errores));
        }
    }

    //  validarMetodoHTTP

    public static function providerMetodoHTTP(): array
    {
        return [
            'registrar POST permitido'          => ['registrar',                           'POST',   true],
            'registrar GET no permitido'        => ['registrar',                           'GET',    false],
            'modificar PUT permitido'           => ['modificar',                           'PUT',    true],
            'modificar POST permitido'          => ['modificar',                           'POST',   true],
            'modificar DELETE no permitido'     => ['modificar',                           'DELETE', false],
            'eliminar DELETE permitido'         => ['eliminar',                            'DELETE', true],
            'eliminar POST permitido'           => ['eliminar',                            'POST',   true],
            'consultar_kpis GET permitido'      => ['consultar_kpis',                      'GET',    true],
            'consultar_kpis POST no permitido'  => ['consultar_kpis',                      'POST',   false],
            'verificarMeses GET permitido'      => ['verificarMeses',                      'GET',    true],
        ];
    }

    #[DataProvider('providerMetodoHTTP')]
    public function testValidarMetodoHTTP(string $operacion, string $metodo, bool $esperado): void
    {
        $reglas = Mensualidad::obtenerReglas($operacion);
        $v = new Validador();
        $resultado = $v->validarMetodoHTTP($metodo, $reglas);

        $this->assertSame($esperado, $resultado,
            "op=$operacion metodo=$metodo");

        if (!$esperado) {
            $this->assertArrayHasKey('http_protocol', $v->obtenerErrores());
        }
    }

    //  Despachador — acciones inválidas

    public static function providerAccionesInvalidas(): array
    {
        return [
            'acción inventada'      => ['accion_que_no_existe'],
            'método público sin _'  => ['resumirDetalles'],
            'string vacío'          => [''],
            'inyección SQL'         => ["consultar'; DROP TABLE mensualidad; --"],
            'acción con guiones'    => ['registrar-mensualidad'],
            'typo común'            => ['registrars'],
        ];
    }

    #[DataProvider('providerAccionesInvalidas')]
    public function testRealizarConsultaAccionInexistenteLanzaExcepcion(string $accion): void
    {
        $m = $this->mensualidadSinConexion();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('no está implementada');

        $m->realizar_consulta($accion);
    }

    //  Validaciones pre-SQL (lanza antes de conectar a la BD)

    public function testConsultarDesgloseSinIdLanzaAntesDeConectar(): void
    {
        $m = $this->mensualidadSinConexion();
        // id_mensualidad no seteado → null

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('ID de mensualidad no proporcionado');
        $m->realizar_consulta('consultar_desglose');
    }

    public function testConsultarPresupuestosAsociadosSinPeriodoLanzaAntesDeConectar(): void
    {
        $m = $this->mensualidadSinConexion();
        // periodo_id no seteado → null

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('ID de período no proporcionado');
        $m->realizar_consulta('consultar_presupuestos_asociados');
    }

    //  9. Setters / getters básicos

    public function testSettersYGetterBasicos(): void
    {
        $m = $this->mensualidadSinConexion();

        $m->set_id_mensualidad(318);
        $m->set_mes('3');
        $m->set_anio('2025');
        $m->set_tasa_dolar('36.50');
        $m->set_porcentaje_interes(10);
        $m->set_limite_mensualidad(15);
        $m->set_periodo_id(7);
        $m->set_correo('test@example.com');

        $this->assertSame(318,                  $m->get_id_mensualidad());
        $this->assertSame('3',                  $m->get_mes());
        $this->assertSame('2025',               $m->get_anio());
        $this->assertSame('36.50',              $m->get_tasa_dolar());
        $this->assertSame(10,                   $m->get_porcentaje_interes());
        $this->assertSame(15,                   $m->get_limite_mensualidad());
        $this->assertSame(7,                    $m->get_periodo_id());
        $this->assertSame('test@example.com',   $m->get_correo());
    }

    public function testGetDetallesYGetDatosApartamentosCompartenFuente(): void
    {
        $m = $this->mensualidadSinConexion();
        $datos = [
            ['id_apartamento' => 1, 'monto' => '50', 'descuento' => '0'],
            ['id_apartamento' => 2, 'monto' => '30', 'descuento' => '5'],
        ];

        $m->set_datos_apartamentos($datos);

        $this->assertSame($datos, $m->get_detalles());
        $this->assertSame($datos, $m->get_datos_apartamentos());
    }

    public function testGetDatosApartamentosPorDefectoEsArrayVacio(): void
    {
        $m = $this->mensualidadSinConexion();

        $this->assertSame([], $m->get_detalles());
        $this->assertSame([], $m->get_datos_apartamentos());
    }
}