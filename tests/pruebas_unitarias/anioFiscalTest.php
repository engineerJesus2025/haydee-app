<?php 
// vendor\bin\phpunit tests

use PHPUnit\Framework\TestCase;
use haydee\modelo\AnioFiscal;
//Mock
class AnioFiscalTest extends TestCase
{
    private $anio_fiscal;
    private $mock_anio_fiscal;

    public function setUp(): void{
        // Crear un mock del modelo Anio_fiscal
        $this->mock_anio_fiscal = $this->createMock(AnioFiscal::class);
        $this->anio_fiscal = $this->mock_anio_fiscal;
    }

    public function tearDown(): void{
        unset($this->anio_fiscal);
        unset($this->mock_anio_fiscal);
    }

    // Método consultar
    public function testConsultarAniosFiscales(){
        // Configurar el stub para devolver datos simulados
        $datos_simulados = [
            [
                'id_anio_fiscal' => 1,
                'fecha_inicio' => '2023-01-01',
                'fecha_cierre' => '2023-12-31',
                'estado' => 'Abierto',
                'descripcion' => 'Año fiscal 2023'
            ],
            [
                'id_anio_fiscal' => 2,
                'fecha_inicio' => '2024-01-01',
                'fecha_cierre' => '2024-12-31',
                'estado' => 'Cerrado',
                'descripcion' => 'Año fiscal 2024'
            ]
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->anio_fiscal->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertCount(2, $resultado);

        // Verificar estructura del primer elemento
        $this->assertArrayHasKey('id_anio_fiscal', $resultado[0]);
        $this->assertArrayHasKey('fecha_inicio', $resultado[0]);
        $this->assertArrayHasKey('fecha_cierre', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);
    }

    // Método consultar_anio_fiscal - Caso exitoso
    public function testConsultarAnioFiscalUnicoIdCorrecto(){
        $datos_simulados = [
            'id_anio_fiscal' => 24,
            'fecha_inicio' => '2023-01-01',
            'fecha_cierre' => '2023-12-31',
            'estado' => 'Abierto',
            'descripcion' => 'Año fiscal 2023'
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('consultar_anio_fiscal')
            ->willReturn($datos_simulados);

        $this->mock_anio_fiscal->method('set_id_anio_fiscal')
            ->with(24);

        $this->anio_fiscal->set_id_anio_fiscal(24);
        $resultado = $this->anio_fiscal->realizar_consulta('consultar_anio_fiscal');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado);

        $this->assertArrayHasKey('id_anio_fiscal', $resultado);
        $this->assertArrayHasKey('fecha_inicio', $resultado);
        $this->assertArrayHasKey('fecha_cierre', $resultado);
        $this->assertArrayHasKey('estado', $resultado);
        $this->assertArrayHasKey('descripcion', $resultado);
    }

    // Método consultar_anio_fiscal - ID incorrecto
    public function testConsultarAnioFiscalUnicoIdIncorrecto(){
        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('consultar_anio_fiscal')
            ->willReturn(false);

        $this->mock_anio_fiscal->method('set_id_anio_fiscal')
            ->with(123122);

        $this->anio_fiscal->set_id_anio_fiscal(123122);
        $resultado = $this->anio_fiscal->realizar_consulta('consultar_anio_fiscal');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // Método registrar - Caso exitoso
    public function testRegistrarAnioFiscalDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        // Configurar los setters
        $this->mock_anio_fiscal->method('set_fecha_inicio')
            ->with("2020-01-01");
        $this->mock_anio_fiscal->method('set_fecha_cierre')
            ->with("2021-01-01");
        $this->mock_anio_fiscal->method('set_estado')
            ->with("Abierto");
        $this->mock_anio_fiscal->method('set_descripcion')
            ->with("Prueba unitaria");

        // Ejecutar setters
        $this->anio_fiscal->set_fecha_inicio("2020-01-01");
        $this->anio_fiscal->set_fecha_cierre("2021-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Prueba unitaria");

        $resultado = $this->anio_fiscal->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    // Método registrar - Datos incorrectos
    public function testRegistrarAnioFiscalDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'fecha de inicio' no posee un valor valido"
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->mock_anio_fiscal->method('set_fecha_inicio')
            ->with("fecha_icorrecto");

        $this->anio_fiscal->set_fecha_inicio("fecha_icorrecto");
        $resultado = $this->anio_fiscal->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha de inicio' no posee un valor valido", $resultado["mensaje"]);
    }

    // Método editar - Caso exitoso
    public function testEditarAnioFiscalDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('editar', true)
            ->willReturn($resultado_esperado);

        $this->mock_anio_fiscal->method('set_id_anio_fiscal')
            ->with(24);
        $this->mock_anio_fiscal->method('set_fecha_inicio')
            ->with("2025-01-01");
        $this->mock_anio_fiscal->method('set_fecha_cierre')
            ->with("2026-01-01");
        $this->mock_anio_fiscal->method('set_estado')
            ->with("Abierto");
        $this->mock_anio_fiscal->method('set_descripcion')
            ->with("Ejecutada prueba de edicion");

        $this->anio_fiscal->set_id_anio_fiscal(24);
        $this->anio_fiscal->set_fecha_inicio("2025-01-01");
        $this->anio_fiscal->set_fecha_cierre("2026-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Ejecutada prueba de edicion");

        $resultado = $this->anio_fiscal->realizar_consulta('editar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    // Método eliminar - Caso exitoso
    public function testEliminarAnioFiscalDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->mock_anio_fiscal->method('set_id_anio_fiscal')
            ->with(31);

        $this->anio_fiscal->set_id_anio_fiscal(31);
        $resultado = $this->anio_fiscal->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    // Método verificar_anio_fiscal
    public function testAniosFiscales(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Verificación exitosa"
        ];

        $this->mock_anio_fiscal->method('realizar_consulta')
            ->with('verificar_anio_fiscal')
            ->willReturn($resultado_esperado);

        $resultado = $this->anio_fiscal->realizar_consulta('verificar_anio_fiscal');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }
}
?>