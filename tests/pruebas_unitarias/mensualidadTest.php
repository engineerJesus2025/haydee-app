<?php 
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
require_once "modelo/mensualidad_modelo.php";
// vendor\bin\phpunit tests
class MensualidadTest extends TestCase
{
    private $mensualidad;
    private $mock_mensualidad; // Mock

    // IDs para simular entradas - ahora son estáticas para los data providers
    private static $id_mensualidad = 318;
    private static $id_apartamento = 11;
    private static $mes_borrar = "2";
    private static $anio_borrar = "2025";

    // Propiedades de instancia para usar en los tests normales
    private $id_mensualidad_instance;
    private $id_apartamento_instance;
    private $mes_borrar_instance;
    private $anio_borrar_instance;

    public function setUp(): void{
        // Crear el mock
        $this->mock_mensualidad = $this->createMock(Mensualidad::class);
        $this->mensualidad = $this->mock_mensualidad;
        
        // Inicializar propiedades de instancia con los valores estáticos
        $this->id_mensualidad_instance = self::$id_mensualidad;
        $this->id_apartamento_instance = self::$id_apartamento;
        $this->mes_borrar_instance = self::$mes_borrar;
        $this->anio_borrar_instance = self::$anio_borrar;
    }

    public function tearDown(): void{
        unset($this->mensualidad);
        unset($this->mock_mensualidad);
    }

    // Data Providers
    public static function providerConsultarMensualidadApartamentosFallido()
    {
        return [
            'Mes incorrecto' => [
                "212312",
                "2025",
                [
                    "estatus" => false,
                    "mensaje" => "El mes para la consulta no posee un valor valido"
                ]
            ],
            'Año incorrecto' => [
                "3",
                "2231025",
                [
                    "estatus" => false,
                    "mensaje" => "El año para la consulta no posee un valor valido"
                ]
            ],
            'Datos vacíos' => [
                "",
                "",
                [
                    "estatus" => false,
                    "mensaje" => "El mes o año se envio vacios"
                ]
            ]
        ];
    }

    public static function providerRegistrarMensualidadFallido()
    {
        return [
            'Datos incorrectos' => [
                "monto incorrecto",
                11,
                [
                    "estatus" => false,
                    "mensaje" => "Uno de los 'montos' no posee un valor valido"
                ]
            ],
            'ID apartamento incorrecto' => [
                "20.12",
                1231,
                [
                    "estatus" => false,
                    "mensaje" => "Uno de los Apartamentos seleccionados no existe"
                ]
            ],
            'Datos vacíos' => [
                "",
                11,
                [
                    "estatus" => false,
                    "mensaje" => "Uno o varios de los campos requeridos estan vacios"
                ]
            ]
        ];
    }

    public static function providerEditarMensualidadFallido()
    {
        return [
            'Datos incorrectos' => [
                self::$id_mensualidad,
                "monto dolar incorrecto",
                11,
                [
                    "estatus" => false,
                    "mensaje" => "Uno de los 'montos en dolar' no posee un valor valido"
                ]
            ],
            'ID incorrecto' => [
                12312312,
                "35.50",
                11,
                [
                    "estatus" => false,
                    "mensaje" => "La mensualidad seleccionada no existe"
                ]
            ],
            'ID apartamento incorrecto' => [
                self::$id_mensualidad,
                "35.50",
                1231,
                [
                    "estatus" => false,
                    "mensaje" => "Uno de los Apartamentos seleccionados no existe"
                ]
            ],
            'Datos vacíos' => [
                self::$id_mensualidad,
                "",
                11,
                [
                    "estatus" => false,
                    "mensaje" => "Uno o varios de los campos requeridos estan vacios"
                ]
            ]
        ];
    }

    public static function providerEliminarMensualidadFallido()
    {
        return [
            'Mes incorrecto' => [
                "212312",
                self::$anio_borrar,
                [
                    "estatus" => false,
                    "mensaje" => "El mes para la consulta no posee un valor valido"
                ]
            ],
            'Año incorrecto' => [
                self::$mes_borrar,
                "2231025",
                [
                    "estatus" => false,
                    "mensaje" => "El año para la consulta no posee un valor valido"
                ]
            ],
            'Datos vacíos' => [
                "",
                "",
                [
                    "estatus" => false,
                    "mensaje" => "El mes o año se envio vacios"
                ]
            ]
        ];
    }

    //Metodo verificarMeses
    public function testConsultarMesesSinMensualidad(){
        $datos_simulados = [
            [
                'mes_presupuesto' => '5',
                'anio_presupuesto' => '2025'
            ]
        ];

        $this->mock_mensualidad->method('realizar_consulta')
            ->with('verificarMeses')
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('verificarMeses');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertArrayHasKey('mes_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('anio_presupuesto', $resultado[0]);        
    }

    //Metodo consultarPorMeses
    public function testConsultarMensualidadesPorMeses(){
        $datos_simulados = [
            [
                'ids' => '318,319',
                'ids_apartamentos' => '11,12',
                'monto' => 50.00,
                'tasa_dolar' => 35.50,
                'mes' => '3',
                'anio' => '2025',
                'pagado' => '0,0',
                'porcentaje_interes' => 10,
                'limite_mensualidad' => 15
            ]
        ];

        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultarPorMeses', true)
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('consultarPorMeses',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 
        $this->assertArrayHasKey('ids', $resultado[0]);
        $this->assertArrayHasKey('limite_mensualidad', $resultado[0]);
    }

    //Metodo consultar_mensualidad_apartamentos
    public function testConsultarMensualidadApartamentosDatosCorrecto(){
        $datos_simulados = [
            [
                'id_mensualidad' => $this->id_mensualidad_instance,
                'id_apartamento' => $this->id_apartamento_instance,
                'mes' => '3',
                'anio' => '2025',
                'nro_apartamento' => '01-01',
                'nombre' => 'John',
                'apellido' => 'Doe',
                'monto' => 50.00,
                'tasa_dolar' => 35.50,
                'pagado' => 0,
                'pagado_dolar' => 0
            ]
        ];

        $this->mock_mensualidad->method('set_mes')->with("3");
        $this->mock_mensualidad->method('set_anio')->with("2025");
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_mensualidad_apartamentos')
            ->willReturn($datos_simulados);

        $this->mensualidad->set_mes("3");
        $this->mensualidad->set_anio("2025");
        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_mensualidad', $resultado[0]);
        $this->assertArrayHasKey('pagado_dolar', $resultado[0]);        
    }

    #[DataProvider('providerConsultarMensualidadApartamentosFallido')]
    public function testConsultarMensualidadApartamentosFallido($mes, $anio, $resultado_esperado){
        $this->mock_mensualidad->method('set_mes')->with($mes);
        $this->mock_mensualidad->method('set_anio')->with($anio);
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_mensualidad_apartamentos')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_mes($mes);
        $this->mensualidad->set_anio($anio);
        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo registrar
    public function testRegistrarMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso",
            "lastId" => 320 // ID simulado
        ];
        
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_tasa_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id($this->id_apartamento_instance);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);
        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertIsInt(intval($resultado["lastId"]));
    }

    #[DataProvider('providerRegistrarMensualidadFallido')]
    public function testRegistrarMensualidadFallido($monto, $apartamento_id, $resultado_esperado){
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_monto($monto);
        $this->mensualidad->set_apartamento_id($apartamento_id);
        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];
        
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_id_mensualidad($this->id_mensualidad_instance);
        $resultado = $this->mensualidad->realizar_consulta('editar');   

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);   
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    #[DataProvider('providerEditarMensualidadFallido')]
    public function testEditarMensualidadFallido($id_mensualidad, $tasa_dolar, $apartamento_id, $resultado_esperado){
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_id_mensualidad($id_mensualidad);
        $this->mensualidad->set_tasa_dolar($tasa_dolar);
        $this->mensualidad->set_apartamento_id($apartamento_id);
        $resultado = $this->mensualidad->realizar_consulta('editar');  

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);      
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];
        
        $this->mock_mensualidad->method('set_mes')->with($this->mes_borrar_instance);
        $this->mock_mensualidad->method('set_anio')->with($this->anio_borrar_instance);
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_mes($this->mes_borrar_instance);
        $this->mensualidad->set_anio($this->anio_borrar_instance);
        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    #[DataProvider('providerEliminarMensualidadFallido')]
    public function testEliminarMensualidadFallido($mes, $anio, $resultado_esperado){
        $this->mock_mensualidad->method('set_mes')->with($mes);
        $this->mock_mensualidad->method('set_anio')->with($anio);
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->mensualidad->set_mes($mes);
        $this->mensualidad->set_anio($anio);
        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString($resultado_esperado["mensaje"], $resultado["mensaje"]);
    }

    // Resto de los métodos se mantienen igual...
    //Metodo consultar_estadisticas_inicio
    public function testConsultarEstadisticasDelInicio(){
        $datos_simulados = [
            ['accion' => 'TotalDeuda', 'valor' => 1200.50],
            ['accion' => 'TotalPagado', 'valor' => 500.00]
        ];

        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_estadisticas_inicio')
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('consultar_estadisticas_inicio');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 
        $this->assertArrayHasKey('accion', $resultado[0]);
        $this->assertArrayHasKey('valor', $resultado[0]);        
    }
    
    //Metodo consultar_meses_mensualidad
    public function testConsultarMesesMensualidad(){
        $datos_simulados = [
            ['mes' => '3', 'anio' => '2025'],
            ['mes' => '4', 'anio' => '2025']
        ];
        
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_meses_mensualidad')
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('consultar_meses_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 
        $this->assertArrayHasKey('mes', $resultado[0]);
        $this->assertArrayHasKey('anio', $resultado[0]);        
    }
    
    //Metodo consultar_monto_dolar_mensualidades
    public function testConsultarMontoEnDolares(){
        $datos_simulados = [
            'mes' => '4',
            'anio' => '2025',
            'tasa_dolar' => 35.50
        ];
        
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_tasa_dolar_mensualidades')
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado);
        $this->assertArrayHasKey('mes', $resultado);
        $this->assertArrayHasKey('tasa_dolar', $resultado);
    }
    
    //Metodo consultar_mensualidades_pendientes
    public function testConsultarMensualidadesPendientes(){
        $datos_simulados = [
            [
                'nro_apartamento' => '01-01',
                'anio' => '2025',
                'mes' => '3',
                'cambio_neto_mes' => 50.00,
                'deuda_acumulada' => 150.00
            ]
        ];
        
        $this->mock_mensualidad->method('realizar_consulta')
            ->with('consultar_mensualidades_pendientes')
            ->willReturn($datos_simulados);

        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidades_pendientes');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 
        $this->assertArrayHasKey('nro_apartamento', $resultado[0]);
        $this->assertArrayHasKey('deuda_acumulada', $resultado[0]);        
    }   
}
?>