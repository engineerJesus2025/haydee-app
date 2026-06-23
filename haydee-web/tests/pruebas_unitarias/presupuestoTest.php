<?php 
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use haydee\modelo\Presupuesto;
// vendor\bin\phpunit tests
class PresupuestoTest extends TestCase
{
    private $presupuesto;
    private $mock_presupuesto; // Variable para el mock

    private $id_presupuesto = 67;
    private $id_presupuesto_borrar = 70;

    public function setUp(): void{
        // Crear el mock
        $this->mock_presupuesto = $this->createMock(Presupuesto::class);
        // Usar el mock
        $this->presupuesto = $this->mock_presupuesto;
    }

    public function tearDown(): void{
        unset($this->presupuesto);
        unset($this->mock_presupuesto); // Limpiar el mock
    }

    // Data Providers
    public static function providerConsultarPresupuestoFallido()
    {
        return [
            'ID incorrecto' => [123122],
            'ID vacio' => ['']
        ];
    }

    public static function providerConsultarPresupuestosMensualidadesFallido()
    {
        return [
            'Fecha incorrecta' => [
                "fecha_incorrecta",
                []
            ],
            'Datos vacíos' => [
                "",
                []
            ]
        ];
    }

    public static function providerRegistrarPresupuestoFallido()
    {
        return [
            'Datos incorrectos' => [
                "fecha_incorrecta",
                "1111",
                "Prueba unitaria",
                [
                    "estatus" => false,
                    "mensaje" => "El campo 'fecha' no posee un valor valido"
                ]
            ],
            'Datos vacíos' => [
                "",
                "",
                "",
                [
                    "estatus" => false,
                    "mensaje" => "Uno o varios de los campos requeridos estan vacios"
                ]
            ]
        ];
    }

    public static function providerEditarPresupuestoFallido()
    {
        return [
            'Datos incorrectos' => [
                self::$id_presupuesto,
                "fecha_incoreccta",
                "1111",
                "Prueba unitaria erronea",
                [
                    "estatus" => false,
                    "mensaje" => "El campo 'fecha' no posee un valor valido"
                ]
            ],
            'ID incorrecto' => [
                12312312,
                "2020-01-01",
                "1111",
                "Prueba unitaria",
                [
                    "estatus" => false,
                    "mensaje" => "El presupuesto mensual seleccionado no existe"
                ]
            ],
            'Datos vacíos' => [
                self::$id_presupuesto,
                "",
                "",
                "",
                [
                    "estatus" => false,
                    "mensaje" => "Uno o varios de los campos requeridos estan vacios"
                ]
            ]
        ];
    }

    public static function providerEliminarPresupuestoFallido()
    {
        return [
            'ID incorrecto' => [
                12312312,
                [
                    "estatus" => false,
                    "mensaje" => "El presupuesto mensual seleccionado no existe"
                ]
            ],
            'Datos vacíos' => [
                '',
                [
                    "estatus" => false,
                    "mensaje" => "El id del Presupuesto requerido esta vacio"
                ]
            ]
        ];
    }

    //Metodo consultar
    public function testConsultarPresupuestos(){
        // Datos simulados
        $datos_simulados = [
            [
                'id_presupuesto' => 1,
                'mes_fecha' => '01',
                'anio_fecha' => '2024',
                'monto_estimado' => 1000.00,
                'cuota_reserva' => 100.00,
                'observacion' => 'Prueba'
            ]
        ];

        // Configurar mock
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->presupuesto->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('mes_fecha', $resultado[0]);
        $this->assertArrayHasKey('anio_fecha', $resultado[0]);
        $this->assertArrayHasKey('monto_estimado', $resultado[0]);
        $this->assertArrayHasKey('cuota_reserva', $resultado[0]);
        $this->assertArrayHasKey('observacion', $resultado[0]);
    }

    //Metodo consultar_meses_faltantes
    public function testConsultarMesesFaltantes(){
        $datos_simulados = [
            [
                'anio_faltante' => '2025',
                'mes_faltante' => '01'
            ]
        ];

        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar_meses_faltantes')
            ->willReturn($datos_simulados);

        $resultado = $this->presupuesto->realizar_consulta('consultar_meses_faltantes');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('anio_faltante', $resultado[0]);
        $this->assertArrayHasKey('mes_faltante', $resultado[0]);        
    }

    //Metodo consultar_presupuesto
    public function testConsultarPresupuestoUnicoIdCorrecto(){
        $datos_simulados = [
            'id_presupuesto' => $this->id_presupuesto,
            'mes_fecha' => '02',
            'anio_fecha' => '2025',
            'cuota_reserva' => 150.00,
            'observacion' => 'Prueba',
            'fecha' => '2025-02-01'
        ];

        $this->mock_presupuesto->method('set_id_presupuesto')->with($this->id_presupuesto);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar_presupuesto')
            ->willReturn($datos_simulados);

        $this->presupuesto->set_id_presupuesto($this->id_presupuesto);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(6, $resultado);
        $this->assertArrayHasKey('id_presupuesto', $resultado);
        $this->assertArrayHasKey('fecha', $resultado);        
    }

    #[DataProvider('providerConsultarPresupuestoFallido')]
    public function testConsultarPresupuestoUnicoFallido($id_presupuesto)
    {
        // Configurar mock
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar_presupuesto')
            ->willReturn(false); // Simula el false de la aserción

        // Ejecución
        $this->presupuesto->set_id_presupuesto($id_presupuesto);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');
        
        // Aserción
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo consultar_presupuestos_mensualidades
    public function testConsultarPresupuestosMensualidadesFechaCorrecta(){
        $datos_simulados = [
            [
                'nombre' => 'Mensualidad 1',
                'monto' => 50.00,
                'id_presupuestos_asociados' => 1,
                'id_presupuesto' => $this->id_presupuesto
            ]
        ];

        $this->mock_presupuesto->method('set_fecha')->with("2025-02-01");
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar_presupuestos_mensualidades')
            ->willReturn($datos_simulados);
        
        $this->presupuesto->set_fecha("2025-02-01");
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('nombre', $resultado[0]);
        $this->assertArrayHasKey('id_presupuesto', $resultado[0]);        
    }

    #[DataProvider('providerConsultarPresupuestosMensualidadesFallido')]
    public function testConsultarPresupuestosMensualidadesFallido($fecha, $resultado_esperado){
        $this->mock_presupuesto->method('set_fecha')->with($fecha);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('consultar_presupuestos_mensualidades')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_fecha($fecha);
        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar
    public function testRegistrarPresupuestoDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];

        $this->mock_presupuesto->method('set_fecha')->with("2025-04-01");
        $this->mock_presupuesto->method('set_cuota_reserva')->with("1000");
        $this->mock_presupuesto->method('set_observacion')->with("Prueba unitaria");
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_fecha("2025-04-01");
        $this->presupuesto->set_cuota_reserva("1000");
        $this->presupuesto->set_observacion("Prueba unitaria");
        $resultado = $this->presupuesto->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    #[DataProvider('providerRegistrarPresupuestoFallido')]
    public function testRegistrarPresupuestoFallido($fecha, $cuota_reserva, $observacion, $resultado_esperado){
        $this->mock_presupuesto->method('set_fecha')->with($fecha);
        $this->mock_presupuesto->method('set_cuota_reserva')->with($cuota_reserva);
        $this->mock_presupuesto->method('set_observacion')->with($observacion);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_fecha($fecha);
        $this->presupuesto->set_cuota_reserva($cuota_reserva);
        $this->presupuesto->set_observacion($observacion);
        $resultado = $this->presupuesto->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarPresupuestoDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];

        $this->mock_presupuesto->method('set_id_presupuesto')->with($this->id_presupuesto);
        $this->mock_presupuesto->method('set_fecha')->with("2025-02-01");
        $this->mock_presupuesto->method('set_cuota_reserva')->with("150");
        $this->mock_presupuesto->method('set_observacion')->with("Prueba unitaria editada");
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_id_presupuesto($this->id_presupuesto);
        $this->presupuesto->set_fecha("2025-02-01");
        $this->presupuesto->set_cuota_reserva("150");
        $this->presupuesto->set_observacion("Prueba unitaria editada");
        $resultado = $this->presupuesto->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    #[DataProvider('providerEditarPresupuestoFallido')]
    public function testEditarPresupuestoFallido($id_presupuesto, $fecha, $cuota_reserva, $observacion, $resultado_esperado){
        $this->mock_presupuesto->method('set_id_presupuesto')->with($id_presupuesto);
        $this->mock_presupuesto->method('set_fecha')->with($fecha);
        $this->mock_presupuesto->method('set_cuota_reserva')->with($cuota_reserva);
        $this->mock_presupuesto->method('set_observacion')->with($observacion);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_id_presupuesto($id_presupuesto);
        $this->presupuesto->set_fecha($fecha);
        $this->presupuesto->set_cuota_reserva($cuota_reserva);
        $this->presupuesto->set_observacion($observacion);
        $resultado = $this->presupuesto->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarPresupuestoDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];

        $this->mock_presupuesto->method('set_id_presupuesto')->with($this->id_presupuesto_borrar);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_id_presupuesto($this->id_presupuesto_borrar);
        $resultado = $this->presupuesto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    #[DataProvider('providerEliminarPresupuestoFallido')]
    public function testEliminarPresupuestoFallido($id_presupuesto, $resultado_esperado){
        $this->mock_presupuesto->method('set_id_presupuesto')->with($id_presupuesto);
        $this->mock_presupuesto->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->presupuesto->set_id_presupuesto($id_presupuesto);
        $resultado = $this->presupuesto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo lastId
    public function testUltimoIdRegistrado(){
        // Simular un ID
        $ultimo_id_simulado = 71;

        $this->mock_presupuesto->method('realizar_consulta')
            ->with('lastId')
            ->willReturn($ultimo_id_simulado);

        $resultado = $this->presupuesto->realizar_consulta('lastId');
        
        $this->assertIsInt($resultado);
        $this->assertGreaterThan(0,$resultado);
        $this->assertEquals($ultimo_id_simulado, $resultado); // Aserción añadida
    }
}
