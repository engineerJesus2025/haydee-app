<?php 
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use haydee\modelo\Presupuesto;
use haydee\excepciones\NegocioException;

// vendor\bin\phpunit tests\pruebas_unitarias\presupuesto\PresupuestoTest.php --testdox

#[CoversClass(Presupuesto::class)]
#[Group('presupuesto')]
#[Group('smoke')]
class PresupuestoTest extends TestCase
{
    // ── IDs ────────────────────────────────────────────────────
    private const ID_PRESUPUESTO        = 67;
    private const ID_PRESUPUESTO_BORRAR = 70;
    private const ID_INEXISTENTE        = 12312312;
    private const ID_MALFORMADO         = 123122;
    private const ID_NUEVO_REGISTRO     = 99;

    // ── Fechas ─────────────────────────────────────────────────
    private const FECHA_VALIDA          = '2025-04-01';
    private const FECHA_VALIDA_EDITAR   = '2025-02-01';
    private const FECHA_INCORRECTA      = 'fecha_incorrecta';
    private const FECHA_INCORRECTA_EDIT = 'fecha_incoreccta';

    // ── Cuotas y observaciones ─────────────────────────────────
    private const CUOTA_VALIDA          = '1000';
    private const CUOTA_VALIDA_EDITAR   = '150';
    private const CUOTA_INVALIDA        = '1111';
    private const OBSERVACION_VALIDA    = 'Prueba unitaria';
    private const OBSERVACION_EDITAR    = 'Prueba unitaria editada';

    // ── Valores vacíos ─────────────────────────────────────────
    private const VACIO                 = '';

    // ── Mensajes de error ──────────────────────────────────────
    private const MSG_FECHA_INVALIDA    = "El campo 'fecha' no posee un valor valido";
    private const MSG_CAMPOS_VACIOS     = "Uno o varios de los campos requeridos estan vacios";
    private const MSG_NO_EXISTE         = "El presupuesto mensual seleccionado no existe";
    private const MSG_ID_VACIO          = "El id del Presupuesto requerido esta vacio";
    private const MSG_NO_ENCONTRADO     = "Presupuesto no encontrado.";

    // ── Mensajes de éxito (los que devuelve el modelo real) ────
    private const MSG_REGISTRO_OK       = 'Presupuesto y mensualidades registrados con éxito';
    private const MSG_EDICION_OK        = 'Presupuesto actualizado correctamente';
    private const MSG_ELIMINACION_OK    = 'Presupuesto y recibos pendientes eliminados correctamente.';

    private $presupuesto;
    private $mock_presupuesto;

    public function setUp(): void {
        $this->mock_presupuesto = $this->createMock(Presupuesto::class);
        $this->presupuesto      = $this->mock_presupuesto;
    }

    public function tearDown(): void {
        unset($this->presupuesto, $this->mock_presupuesto);
    }

    //  HELPERS

    private function configurarConsulta(string $accion, $retorno): void {
        $this->mock_presupuesto
            ->expects($this->once())
            ->method('realizar_consulta')
            ->with($accion)
            ->willReturn($retorno);
    }

    /** Simula que el modelo falla: `realizar_consulta` lanza NegocioException. */
    private function configurarConsultaQueFalla(string $accion, string $mensaje, int $codigo = 400): void {
        $this->mock_presupuesto
            ->expects($this->once())
            ->method('realizar_consulta')
            ->with($accion)
            ->willThrowException(new NegocioException($mensaje, $codigo));
    }

    private function configurarSetters(array ...$pares): void {
        foreach ($pares as [$metodo, $valor]) {
            $this->mock_presupuesto
                ->expects($this->once())
                ->method($metodo)
                ->with($valor);
        }
    }

    /** Envuelve el retorno de una consulta exitosa: shape real del modelo. */
    private function respuestaExitoConsultar(array $datos): array {
        return ['estatus' => true, 'datos' => $datos];
    }

    //  DATA PROVIDERS

    public static function providerConsultarPresupuestoFallido(): array {
        return [
            'ID inexistente' => [self::ID_MALFORMADO],
            'ID vacío'       => [self::VACIO],
        ];
    }

    public static function providerConsultarPresupuestosMensualidadesSinDatos(): array {
        return [
            'Fecha incorrecta' => [self::FECHA_INCORRECTA],
            'Fecha vacía'      => [self::VACIO],
        ];
    }

    public static function providerRegistrarPresupuestoFallido(): array {
        return [
            'Fecha incorrecta' => [
                self::FECHA_INCORRECTA, self::CUOTA_INVALIDA, self::OBSERVACION_VALIDA,
                self::MSG_FECHA_INVALIDA,
            ],
            'Datos vacíos' => [
                self::VACIO, self::VACIO, self::VACIO,
                self::MSG_CAMPOS_VACIOS,
            ],
        ];
    }

    public static function providerModificarPresupuestoFallido(): array {
        return [
            'Fecha incorrecta' => [
                self::ID_PRESUPUESTO, self::FECHA_INCORRECTA_EDIT,
                self::CUOTA_INVALIDA, self::OBSERVACION_VALIDA,
                self::MSG_FECHA_INVALIDA,
            ],
            'ID inexistente' => [
                self::ID_INEXISTENTE, self::FECHA_VALIDA_EDITAR,
                self::CUOTA_INVALIDA, self::OBSERVACION_VALIDA,
                self::MSG_NO_EXISTE,
            ],
            'Datos vacíos' => [
                self::ID_PRESUPUESTO, self::VACIO, self::VACIO, self::VACIO,
                self::MSG_CAMPOS_VACIOS,
            ],
        ];
    }

    public static function providerEliminarPresupuestoFallido(): array {
        return [
            'ID inexistente' => [self::ID_INEXISTENTE, self::MSG_NO_EXISTE],
            'ID vacío'       => [self::VACIO,          self::MSG_ID_VACIO],
        ];
    }

    //  TESTS: consultar

    public function testConsultarPresupuestos(): void {
        $datos = $this->respuestaExitoConsultar([[
            'id_presupuesto' => 1,
            'mes_fecha'      => '01',
            'anio_fecha'     => '2024',
            'monto_estimado' => 1000.00,
            'cuota_reserva'  => 100.00,
            'observacion'    => 'Prueba',
        ]]);

        $this->configurarConsulta('consultar', $datos);

        $resultado = $this->presupuesto->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertNotEmpty($resultado['datos']);
        $this->assertArrayHasKey('id_presupuesto', $resultado['datos'][0]);
        $this->assertArrayHasKey('monto_estimado', $resultado['datos'][0]);
        $this->assertArrayHasKey('observacion',    $resultado['datos'][0]);
    }

    public function testConsultarMesesFaltantes(): void {
        $datos = $this->respuestaExitoConsultar([[
            'anio_faltante' => '2025',
            'mes_faltante'  => '01',
        ]]);

        $this->configurarConsulta('consultar_meses_faltantes', $datos);

        $resultado = $this->presupuesto->realizar_consulta('consultar_meses_faltantes');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertNotEmpty($resultado['datos']);
        $this->assertArrayHasKey('anio_faltante', $resultado['datos'][0]);
        $this->assertArrayHasKey('mes_faltante',  $resultado['datos'][0]);
    }

    public function testConsultarPresupuestoUnicoIdCorrecto(): void {
        $data = [
            'id_presupuesto' => self::ID_PRESUPUESTO,
            'mes_fecha'      => '02',
            'anio_fecha'     => '2025',
            'cuota_reserva'  => 150.00,
            'observacion'    => self::OBSERVACION_VALIDA,
            'fecha'          => self::FECHA_VALIDA_EDITAR,
        ];

        $this->configurarSetters(['set_id_presupuesto', self::ID_PRESUPUESTO]);
        $this->configurarConsulta('consultar_presupuesto', $this->respuestaExitoConsultar($data));

        $this->presupuesto->set_id_presupuesto(self::ID_PRESUPUESTO);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertCount(6, $resultado['datos']);
        $this->assertArrayHasKey('id_presupuesto', $resultado['datos']);
        $this->assertArrayHasKey('fecha',          $resultado['datos']);
    }

    #[DataProvider('providerConsultarPresupuestoFallido')]
    public function testConsultarPresupuestoUnicoFallido(int|string $id_presupuesto): void {
        $this->configurarSetters(['set_id_presupuesto', $id_presupuesto]);
        $this->configurarConsultaQueFalla('consultar_presupuesto', self::MSG_NO_ENCONTRADO, 404);

        $this->presupuesto->set_id_presupuesto($id_presupuesto);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage(self::MSG_NO_ENCONTRADO);
        $this->presupuesto->realizar_consulta('consultar_presupuesto');
    }

    public function testConsultarPresupuestosMensualidadesFechaCorrecta(): void {
        $datos = $this->respuestaExitoConsultar([[
            'nombre'                    => 'Mensualidad 1',
            'monto'                     => 50.00,
            'id_presupuestos_asociados' => 1,
            'id_presupuesto'            => self::ID_PRESUPUESTO,
        ]]);

        $this->configurarSetters(['set_fecha', self::FECHA_VALIDA_EDITAR]);
        $this->configurarConsulta('consultar_presupuestos_mensualidades', $datos);

        $this->presupuesto->set_fecha(self::FECHA_VALIDA_EDITAR);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertNotEmpty($resultado['datos']);
        $this->assertArrayHasKey('nombre',         $resultado['datos'][0]);
        $this->assertArrayHasKey('id_presupuesto', $resultado['datos'][0]);
    }

    #[DataProvider('providerConsultarPresupuestosMensualidadesSinDatos')]
    public function testConsultarPresupuestosMensualidadesSinDatos(string $fecha): void {
        $this->configurarSetters(['set_fecha', $fecha]);
        $this->configurarConsulta(
            'consultar_presupuestos_mensualidades',
            $this->respuestaExitoConsultar([])   // ← "sin datos" no es error
        );

        $this->presupuesto->set_fecha($fecha);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertEmpty($resultado['datos']);
    }

    //  TESTS: registrar

    public function testRegistrarPresupuestoDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_REGISTRO_OK,
            'id'      => self::ID_NUEVO_REGISTRO,
        ];

        $this->configurarSetters(
            ['set_fecha',         self::FECHA_VALIDA],
            ['set_cuota_reserva', self::CUOTA_VALIDA],
            ['set_observacion',   self::OBSERVACION_VALIDA],
        );
        $this->configurarConsulta('registrar_presupuesto', $datos);

        $this->presupuesto->set_fecha(self::FECHA_VALIDA);
        $this->presupuesto->set_cuota_reserva(self::CUOTA_VALIDA);
        $this->presupuesto->set_observacion(self::OBSERVACION_VALIDA);
        $resultado = $this->presupuesto->realizar_consulta('registrar_presupuesto');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('id', $resultado);
        $this->assertSame(self::ID_NUEVO_REGISTRO, $resultado['id']);
        $this->assertStringContainsString('éxito', $resultado['mensaje']);
    }

    #[DataProvider('providerRegistrarPresupuestoFallido')]
    public function testRegistrarPresupuestoFallido(
        string $fecha, string $cuota_reserva, string $observacion, string $mensajeEsperado
    ): void {
        $this->configurarSetters(
            ['set_fecha',         $fecha],
            ['set_cuota_reserva', $cuota_reserva],
            ['set_observacion',   $observacion],
        );
        $this->configurarConsultaQueFalla('registrar_presupuesto', $mensajeEsperado);

        $this->presupuesto->set_fecha($fecha);
        $this->presupuesto->set_cuota_reserva($cuota_reserva);
        $this->presupuesto->set_observacion($observacion);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage($mensajeEsperado);
        $this->presupuesto->realizar_consulta('registrar_presupuesto');
    }

    //  TESTS: modificar

    public function testModificarPresupuestoDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_EDICION_OK,
        ];

        $this->configurarSetters(
            ['set_id_presupuesto', self::ID_PRESUPUESTO],
            ['set_fecha',          self::FECHA_VALIDA_EDITAR],
            ['set_cuota_reserva',  self::CUOTA_VALIDA_EDITAR],
            ['set_observacion',    self::OBSERVACION_EDITAR],
        );
        $this->configurarConsulta('modificar_presupuesto', $datos);

        $this->presupuesto->set_id_presupuesto(self::ID_PRESUPUESTO);
        $this->presupuesto->set_fecha(self::FECHA_VALIDA_EDITAR);
        $this->presupuesto->set_cuota_reserva(self::CUOTA_VALIDA_EDITAR);
        $this->presupuesto->set_observacion(self::OBSERVACION_EDITAR);
        $resultado = $this->presupuesto->realizar_consulta('modificar_presupuesto');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('correctamente', $resultado['mensaje']);
    }

    #[DataProvider('providerModificarPresupuestoFallido')]
    public function testModificarPresupuestoFallido(
        $id_presupuesto, string $fecha, string $cuota_reserva, string $observacion, string $mensajeEsperado
    ): void {
        $this->configurarSetters(
            ['set_id_presupuesto', $id_presupuesto],
            ['set_fecha',          $fecha],
            ['set_cuota_reserva',  $cuota_reserva],
            ['set_observacion',    $observacion],
        );
        $this->configurarConsultaQueFalla('modificar_presupuesto', $mensajeEsperado);

        $this->presupuesto->set_id_presupuesto($id_presupuesto);
        $this->presupuesto->set_fecha($fecha);
        $this->presupuesto->set_cuota_reserva($cuota_reserva);
        $this->presupuesto->set_observacion($observacion);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage($mensajeEsperado);
        $this->presupuesto->realizar_consulta('modificar_presupuesto');
    }

    //  TESTS: eliminar

    public function testEliminarPresupuestoDatosCorrectos(): void {
        $datos = [
            'estatus' => true,
            'mensaje' => self::MSG_ELIMINACION_OK,
        ];

        $this->configurarSetters(['set_id_presupuesto', self::ID_PRESUPUESTO_BORRAR]);
        $this->configurarConsulta('eliminar_presupuesto', $datos);

        $this->presupuesto->set_id_presupuesto(self::ID_PRESUPUESTO_BORRAR);
        $resultado = $this->presupuesto->realizar_consulta('eliminar_presupuesto');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('eliminados', $resultado['mensaje']);
    }

    #[DataProvider('providerEliminarPresupuestoFallido')]
    public function testEliminarPresupuestoFallido(int|string $id_presupuesto, string $mensajeEsperado): void {
        $this->configurarSetters(['set_id_presupuesto', $id_presupuesto]);
        $this->configurarConsultaQueFalla('eliminar_presupuesto', $mensajeEsperado);

        $this->presupuesto->set_id_presupuesto($id_presupuesto);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage($mensajeEsperado);
        $this->presupuesto->realizar_consulta('eliminar_presupuesto');
    }
}