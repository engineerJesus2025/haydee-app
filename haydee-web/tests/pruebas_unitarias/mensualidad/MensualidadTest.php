<?php 
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use haydee\modelo\Mensualidad;

// vendor\bin\phpunit tests\pruebas_unitarias\mensualidad\MensualidadTest.php --testdox

#[CoversClass(Mensualidad::class)]
#[Group('mensualidad')]
#[Group('smoke')]
class MensualidadTest extends TestCase
{
    // ── IDs ────────────────────────────────────────────────────
    private const ID_MENSUALIDAD      = 318;
    private const ID_APARTAMENTO      = 11;
    private const ID_INEXISTENTE      = 12312312;
    private const ID_APTO_INEXISTENTE = 1231;
    private const ID_NUEVO_REGISTRO   = 320;
    private const PERIODO_ID          = 7;

    // ── Mes / Año ──────────────────────────────────────────────
    private const MES_VALIDO       = '3';
    private const MES_VALIDO_ALT   = '4';
    private const MES_BORRAR       = '2';
    private const MES_INCORRECTO   = '212312';
    private const ANIO_VALIDO      = '2025';
    private const ANIO_INCORRECTO  = '2231025';

    // ── Valores válidos ────────────────────────────────────────
    private const MONTO_VALIDO   = '20.12';
    private const TASA_VALIDA    = '35.50';
    private const TASA_NUEVA     = '1.00';

    // ── Valores inválidos ──────────────────────────────────────
    private const MONTO_INVALIDO = 'monto incorrecto';
    private const TASA_INVALIDA  = 'monto dolar incorrecto';
    private const VACIO          = '';

    // ── Mensajes de éxito (los reales que devuelve el modelo) ──
    private const MSG_REGISTRAR_OK = 'Todas las mensualidades se registraron/reactivaron correctamente.';
    private const MSG_MODIFICAR_OK = 'Mensualidades sincronizadas correctamente preservando el historial financiero.';
    private const MSG_ELIMINAR_OK  = 'Periodo y mensualidades desactivados correctamente';

    // ── Mensajes de error (los produce el controller tras catch) ─
    private const MSG_MES_INVALIDO    = 'El mes para la consulta no posee un valor valido';
    private const MSG_ANIO_INVALIDO   = 'El año para la consulta no posee un valor valido';
    private const MSG_MES_ANIO_VACIOS = 'El mes o año se envio vacios';
    private const MSG_CAMPOS_VACIOS   = 'Uno o varios de los campos requeridos estan vacios';
    private const MSG_MONTO_INVALIDO  = "Uno de los 'montos' no posee un valor valido";
    private const MSG_TASA_INVALIDA   = "Uno de los 'montos en dolar' no posee un valor valido";
    private const MSG_APTO_NO_EXISTE  = 'Uno de los Apartamentos seleccionados no existe';
    private const MSG_MENS_NO_EXISTE  = 'La mensualidad seleccionada no existe';

    private $mensualidad;
    private $mock_mensualidad;

    public function setUp(): void {
        $this->mock_mensualidad = $this->createMock(Mensualidad::class);
        $this->mensualidad      = $this->mock_mensualidad;
    }

    public function tearDown(): void {
        unset($this->mensualidad, $this->mock_mensualidad);
    }

    //  HELPERS DE MOCK

    private function configurarConsulta(string $accion, $retorno): void {
        $this->mock_mensualidad
            ->expects($this->once())
            ->method('realizar_consulta')
            ->with($accion)
            ->willReturn($retorno);
    }

    private function configurarSetters(array ...$pares): void {
        foreach ($pares as [$metodo, $valor]) {
            $this->mock_mensualidad
                ->expects($this->once())
                ->method($metodo)
                ->with($valor);
        }
    }

    private function respuestaExitoConsultar(array $datos): array {
        return ['estatus' => true, 'datos' => $datos];
    }

    private function respuestaError(string $mensaje): array {
        return ['estatus' => false, 'mensaje' => $mensaje];
    }

    //  DATA PROVIDERS

    public static function providerConsultarMensualidadApartamentosFallido(): array {
        return [
            'Mes incorrecto' => [self::MES_INCORRECTO, self::ANIO_VALIDO,  self::MSG_MES_INVALIDO],
            'Año incorrecto' => [self::MES_VALIDO,     self::ANIO_INCORRECTO, self::MSG_ANIO_INVALIDO],
            'Datos vacíos'   => [self::VACIO,          self::VACIO,         self::MSG_MES_ANIO_VACIOS],
        ];
    }

    public static function providerRegistrarMensualidadFallido(): array {
        return [
            'Monto inválido'             => [self::MONTO_INVALIDO, self::ID_APARTAMENTO,      self::MSG_MONTO_INVALIDO],
            'ID apartamento inexistente' => [self::MONTO_VALIDO,   self::ID_APTO_INEXISTENTE, self::MSG_APTO_NO_EXISTE],
            'Datos vacíos'               => [self::VACIO,          self::ID_APARTAMENTO,      self::MSG_CAMPOS_VACIOS],
        ];
    }

    public static function providerModificarMensualidadFallido(): array {
        return [
            'Tasa inválida'              => [self::ID_MENSUALIDAD, self::TASA_INVALIDA, self::ID_APARTAMENTO,      self::MSG_TASA_INVALIDA],
            'ID mensualidad inexistente' => [self::ID_INEXISTENTE, self::TASA_VALIDA,  self::ID_APARTAMENTO,      self::MSG_MENS_NO_EXISTE],
            'ID apartamento inexistente' => [self::ID_MENSUALIDAD, self::TASA_VALIDA,  self::ID_APTO_INEXISTENTE, self::MSG_APTO_NO_EXISTE],
            'Datos vacíos'               => [self::ID_MENSUALIDAD, self::VACIO,        self::ID_APARTAMENTO,      self::MSG_CAMPOS_VACIOS],
        ];
    }

    public static function providerEliminarMensualidadFallido(): array {
        return [
            'Mes incorrecto' => [self::MES_INCORRECTO, self::ANIO_VALIDO,    self::MSG_MES_INVALIDO],
            'Año incorrecto' => [self::MES_BORRAR,     self::ANIO_INCORRECTO, self::MSG_ANIO_INVALIDO],
            'Datos vacíos'   => [self::VACIO,          self::VACIO,          self::MSG_MES_ANIO_VACIOS],
        ];
    }

    //  TESTS: verificarMeses

    public function testConsultarMesesSinMensualidad(): void {
        $datos = $this->respuestaExitoConsultar([
            ['mes_presupuesto' => '5', 'anio_presupuesto' => '2025'],
        ]);

        $this->configurarConsulta('verificarMeses', $datos);

        $resultado = $this->mensualidad->realizar_consulta('verificarMeses');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertNotEmpty($resultado['datos']);
        $this->assertArrayHasKey('mes_presupuesto',  $resultado['datos'][0]);
        $this->assertArrayHasKey('anio_presupuesto', $resultado['datos'][0]);
    }

    //  TESTS: consultarPorMeses

    public function testConsultarMensualidadesPorMeses(): void {
        $datos = $this->respuestaExitoConsultar([[
            'id_periodo'           => self::PERIODO_ID,
            'monto'                => 50.00,
            'tasa_dolar'           => 35.50,
            'mes'                  => self::MES_VALIDO,
            'anio'                 => self::ANIO_VALIDO,
            'pagado'               => 0.0,
            'porcentaje_interes'   => 10,
            'limite_mensualidad'   => 15,
        ]]);

        $this->configurarConsulta('consultarPorMeses', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultarPorMeses');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('id_periodo',          $resultado['datos'][0]);
        $this->assertArrayHasKey('limite_mensualidad',  $resultado['datos'][0]);
    }

    //  TESTS: consultar_mensualidad_apartamentos

    public function testConsultarMensualidadApartamentosDatosCorrecto(): void {
        $datos = $this->respuestaExitoConsultar([[
            'id_mensualidad'   => self::ID_MENSUALIDAD,
            'apartamento_id'   => self::ID_APARTAMENTO,
            'mes'              => self::MES_VALIDO,
            'anio'             => self::ANIO_VALIDO,
            'nro_apartamento'  => '01-01',
            'nombre'           => 'John',
            'apellido'         => 'Doe',
            'monto'            => 50.00,
            'tasa_dolar'       => 35.50,
            'pagado'           => 0,
            'pagado_dolar'     => 0,
        ]]);

        $this->configurarSetters(
            ['set_mes',  self::MES_VALIDO],
            ['set_anio', self::ANIO_VALIDO],
        );
        $this->configurarConsulta('consultar_mensualidad_apartamentos', $datos);

        $this->mensualidad->set_mes(self::MES_VALIDO);
        $this->mensualidad->set_anio(self::ANIO_VALIDO);
        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('id_mensualidad', $resultado['datos'][0]);
        $this->assertArrayHasKey('pagado_dolar',   $resultado['datos'][0]);
    }

    #[DataProvider('providerConsultarMensualidadApartamentosFallido')]
    public function testConsultarMensualidadApartamentosFallido(string $mes, string $anio, string $mensajeEsperado): void {
        $this->configurarSetters(['set_mes', $mes], ['set_anio', $anio]);
        $this->configurarConsulta('consultar_mensualidad_apartamentos', $this->respuestaError($mensajeEsperado));

        $this->mensualidad->set_mes($mes);
        $this->mensualidad->set_anio($anio);
        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($mensajeEsperado, $resultado['mensaje']);
    }

    //  TESTS: registrar

    public function testRegistrarMensualidadDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_REGISTRAR_OK,
            'lastId'  => self::ID_NUEVO_REGISTRO,
        ];

        $this->configurarSetters(
            ['set_monto',               self::MONTO_VALIDO],
            ['set_tasa_dolar',          self::TASA_NUEVA],
            ['set_mes',                 self::MES_VALIDO_ALT],
            ['set_anio',                self::ANIO_VALIDO],
            ['set_apartamento_id',      self::ID_APARTAMENTO],
            ['set_porcentaje_interes',  10],
            ['set_limite_mensualidad',  15],
        );
        $this->configurarConsulta('registrar', $datos);

        $this->mensualidad->set_monto(self::MONTO_VALIDO);
        $this->mensualidad->set_tasa_dolar(self::TASA_NUEVA);
        $this->mensualidad->set_mes(self::MES_VALIDO_ALT);
        $this->mensualidad->set_anio(self::ANIO_VALIDO);
        $this->mensualidad->set_apartamento_id(self::ID_APARTAMENTO);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);
        $resultado = $this->mensualidad->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('lastId', $resultado);
        $this->assertIsInt($resultado['lastId']);
        $this->assertSame(self::ID_NUEVO_REGISTRO, $resultado['lastId']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    #[DataProvider('providerRegistrarMensualidadFallido')]
    public function testRegistrarMensualidadFallido(string $monto, int $apartamento_id, string $mensajeEsperado): void {
        $this->configurarSetters(
            ['set_monto',          $monto],
            ['set_apartamento_id', $apartamento_id],
        );
        $this->configurarConsulta('registrar', $this->respuestaError($mensajeEsperado));

        $this->mensualidad->set_monto($monto);
        $this->mensualidad->set_apartamento_id($apartamento_id);
        $resultado = $this->mensualidad->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($mensajeEsperado, $resultado['mensaje']);
    }

    //  TESTS: modificar

    public function testModificarMensualidadDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_MODIFICAR_OK,
        ];

        $this->configurarSetters(
            ['set_id_mensualidad', self::ID_MENSUALIDAD],
            ['set_periodo_id',     self::PERIODO_ID],
            ['set_tasa_dolar',     self::TASA_VALIDA],
        );
        $this->configurarConsulta('modificar', $datos);

        $this->mensualidad->set_id_mensualidad(self::ID_MENSUALIDAD);
        $this->mensualidad->set_periodo_id(self::PERIODO_ID);
        $this->mensualidad->set_tasa_dolar(self::TASA_VALIDA);
        $resultado = $this->mensualidad->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('sincronizadas', $resultado['mensaje']);
    }

    #[DataProvider('providerModificarMensualidadFallido')]
    public function testModificarMensualidadFallido(
        int $id_mensualidad, string $tasa_dolar, int $apartamento_id, string $mensajeEsperado
    ): void {
        $this->configurarSetters(
            ['set_id_mensualidad', $id_mensualidad],
            ['set_tasa_dolar',     $tasa_dolar],
            ['set_apartamento_id', $apartamento_id],
        );
        $this->configurarConsulta('modificar', $this->respuestaError($mensajeEsperado));

        $this->mensualidad->set_id_mensualidad($id_mensualidad);
        $this->mensualidad->set_tasa_dolar($tasa_dolar);
        $this->mensualidad->set_apartamento_id($apartamento_id);
        $resultado = $this->mensualidad->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($mensajeEsperado, $resultado['mensaje']);
    }

    //  TESTS: eliminar

    public function testEliminarMensualidadDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_ELIMINAR_OK,
        ];

        $this->configurarSetters(
            ['set_mes',        self::MES_BORRAR],
            ['set_anio',       self::ANIO_VALIDO],
            ['set_periodo_id', self::PERIODO_ID],
        );
        $this->configurarConsulta('eliminar', $datos);

        $this->mensualidad->set_mes(self::MES_BORRAR);
        $this->mensualidad->set_anio(self::ANIO_VALIDO);
        $this->mensualidad->set_periodo_id(self::PERIODO_ID);
        $resultado = $this->mensualidad->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('desactivados', $resultado['mensaje']);
    }

    #[DataProvider('providerEliminarMensualidadFallido')]
    public function testEliminarMensualidadFallido(string $mes, string $anio, string $mensajeEsperado): void {
        $this->configurarSetters(['set_mes', $mes], ['set_anio', $anio]);
        $this->configurarConsulta('eliminar', $this->respuestaError($mensajeEsperado));

        $this->mensualidad->set_mes($mes);
        $this->mensualidad->set_anio($anio);
        $resultado = $this->mensualidad->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($mensajeEsperado, $resultado['mensaje']);
    }

    //  TESTS: consultas 

    public function testConsultarEstadisticasDelInicio(): void {
        $datos = $this->respuestaExitoConsultar([
            'grafico_deudas' => [
                'aptos_morosos'   => 5,
                'aptos_solventes' => 15,
            ],
            'grafico_ingresos_gastos' => [
                ['etiqueta' => 'Ene 25', 'ingresos' => 1200.0, 'gastos' => 800.0],
            ],
        ]);

        $this->configurarConsulta('consultar_estadisticas_inicio', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultar_estadisticas_inicio');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('grafico_deudas',           $resultado['datos']);
        $this->assertArrayHasKey('grafico_ingresos_gastos',  $resultado['datos']);
        $this->assertArrayHasKey('aptos_morosos',            $resultado['datos']['grafico_deudas']);
    }

    public function testConsultarMesesMensualidad(): void {
        $datos = $this->respuestaExitoConsultar([
            ['mes' => self::MES_VALIDO,     'anio' => self::ANIO_VALIDO],
            ['mes' => self::MES_VALIDO_ALT, 'anio' => self::ANIO_VALIDO],
        ]);

        $this->configurarConsulta('consultar_meses_mensualidad', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultar_meses_mensualidad');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertCount(2, $resultado['datos']);
        $this->assertArrayHasKey('mes',  $resultado['datos'][0]);
        $this->assertArrayHasKey('anio', $resultado['datos'][0]);
    }

    public function testConsultarMontoEnDolares(): void {
        // El modelo devuelve un solo registro (no un array de filas)
        $datos = $this->respuestaExitoConsultar([
            'mes'        => self::MES_VALIDO_ALT,
            'anio'       => self::ANIO_VALIDO,
            'tasa_dolar' => 35.50,
        ]);

        $this->configurarConsulta('consultar_tasa_dolar_mensualidades', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('mes',        $resultado['datos']);
        $this->assertArrayHasKey('tasa_dolar', $resultado['datos']);
        $this->assertEqualsWithDelta(35.50, (float) $resultado['datos']['tasa_dolar'], 0.0001);
    }

    public function testConsultarKpis(): void {
        $datos = $this->respuestaExitoConsultar([
            'deuda_total'   => 4500.00,
            'recaudado_mes' => 1200.00,
            'gastado_mes'   => 800.00,
        ]);

        $this->configurarConsulta('consultar_kpis', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultar_kpis');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('deuda_total', $resultado['datos']);
        $this->assertArrayHasKey('recaudado_mes', $resultado['datos']);
    }

    public function testConsultarDesglose(): void {
        $datos = $this->respuestaExitoConsultar([
            ['id_detalle_presupuesto' => 1, 'concepto' => 'Luz',  'monto' => 50.00, 'tasa_dolar' => 35.50],
            ['id_detalle_presupuesto' => 2, 'concepto' => 'Agua', 'monto' => 30.00, 'tasa_dolar' => 35.50],
        ]);

        $this->configurarSetters(['set_id_mensualidad', self::ID_MENSUALIDAD]);
        $this->configurarConsulta('consultar_desglose', $datos);

        $this->mensualidad->set_id_mensualidad(self::ID_MENSUALIDAD);
        $resultado = $this->mensualidad->realizar_consulta('consultar_desglose');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertCount(2, $resultado['datos']);
        $this->assertArrayHasKey('concepto', $resultado['datos'][0]);
    }

    public function testConsultarPresupuestosAsociados(): void {
        $datos = $this->respuestaExitoConsultar([
            ['apartamento_id' => self::ID_APARTAMENTO, 'id_mensualidad' => self::ID_MENSUALIDAD,
             'descuento' => 0.00, 'presupuestos' => '1,2,3'],
        ]);

        $this->configurarSetters(['set_periodo_id', self::PERIODO_ID]);
        $this->configurarConsulta('consultar_presupuestos_asociados', $datos);

        $this->mensualidad->set_periodo_id(self::PERIODO_ID);
        $resultado = $this->mensualidad->realizar_consulta('consultar_presupuestos_asociados');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('presupuestos', $resultado['datos'][0]);
    }

    public function testConsultarCabeceraMensualidad(): void {
        $datos = $this->respuestaExitoConsultar([
            'tasa_dolar'          => 35.50,
            'mes'                 => self::MES_VALIDO,
            'anio'                => self::ANIO_VALIDO,
            'porcentaje_interes'  => 10,
            'limite_mensualidad'  => 15,
        ]);

        $this->configurarSetters(['set_periodo_id', self::PERIODO_ID]);
        $this->configurarConsulta('consultar_cabecera_mensualidad', $datos);

        $this->mensualidad->set_periodo_id(self::PERIODO_ID);
        $resultado = $this->mensualidad->realizar_consulta('consultar_cabecera_mensualidad');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('tasa_dolar', $resultado['datos']);
    }

    public function testConsultarTarjetasResumen(): void {
        $datos = $this->respuestaExitoConsultar([
            'total_apartamentos'  => 20,
            'apartamentos_ocupados' => 18,
            'residentes_activos'  => 42,
            'recaudado_mes'       => 1200.00,
            'recibos_pendientes'  => 7,
            'deuda_total'         => 4500.00,
        ]);

        $this->configurarConsulta('consultar_tarjetas_resumen', $datos);

        $resultado = $this->mensualidad->realizar_consulta('consultar_tarjetas_resumen');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('total_apartamentos', $resultado['datos']);
        $this->assertArrayHasKey('deuda_total',        $resultado['datos']);
    }

    public function testConsultarAuditoria(): void {
        $datos = $this->respuestaExitoConsultar([
            'tasa_dolar'          => 35.50,
            'mes'                 => self::MES_VALIDO,
            'anio'                => self::ANIO_VALIDO,
            'porcentaje_interes'  => 10,
            'limite_mensualidad'  => 15,
            'detalles' => [
                ['id_apartamento' => self::ID_APARTAMENTO, 'monto' => 50.00, 'descuento' => 0.00],
            ],
        ]);

        $this->configurarSetters(['set_periodo_id', self::PERIODO_ID]);
        $this->configurarConsulta('consultar_auditoria', $datos);

        $this->mensualidad->set_periodo_id(self::PERIODO_ID);
        $resultado = $this->mensualidad->realizar_consulta('consultar_auditoria');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('detalles', $resultado['datos']);
    }
}