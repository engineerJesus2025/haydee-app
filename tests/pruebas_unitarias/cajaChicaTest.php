<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/caja_chica_modelo.php";
//Mock
class CajaChicaTest extends TestCase
{
    private $caja_chica;
    private $mock_caja_chica;

    public function setUp(): void{
        $this->mock_caja_chica = $this->createMock(Caja_chica::class);
        $this->caja_chica = $this->mock_caja_chica;
    }

    public function tearDown(): void{
        unset($this->caja_chica);
        unset($this->mock_caja_chica);
    }

    public function testConsultarCajasChicas(){
        $datos_simulados = [
            [
                'id_caja_chica' => 1,
                'fondo_fijo' => 1000.00,
                'saldo_actual' => 500.00,
                'estado' => 'Activa',
                'descripcion' => 'Caja principal',
                'fecha_creacion' => '2023-01-01',
                'anio_fiscal_id' => 1
            ]
        ];

        $this->mock_caja_chica->method('realizar_consulta')
            ->with('consultar', true)
            ->willReturn($datos_simulados);

        $resultado = $this->caja_chica->realizar_consulta('consultar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_caja_chica', $resultado[0]);
        $this->assertArrayHasKey('fondo_fijo', $resultado[0]);
        $this->assertArrayHasKey('saldo_actual', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);
        $this->assertArrayHasKey('fecha_creacion', $resultado[0]);
        $this->assertArrayHasKey('anio_fiscal_id', $resultado[0]);
    }

    public function testEditarObservacionDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];

        $this->mock_caja_chica->method('realizar_consulta')
            ->with('editar_descripcion')
            ->willReturn($resultado_esperado);

        $this->mock_caja_chica->method('set_id_caja_chica')
            ->with(23);
        $this->mock_caja_chica->method('set_descripcion')
            ->with("Ejecutada prueba de edicion");

        $this->caja_chica->set_id_caja_chica(23);
        $this->caja_chica->set_descripcion("Ejecutada prueba de edicion");
        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarObservacionIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "La Caja seleccionada no existe"
        ];

        $this->mock_caja_chica->method('realizar_consulta')
            ->with('editar_descripcion')
            ->willReturn($resultado_esperado);

        $this->mock_caja_chica->method('set_id_caja_chica')
            ->with(12312312);

        $this->caja_chica->set_id_caja_chica(12312312);
        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La Caja seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarObservacionDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "Uno o varios de los campos requeridos estan vacios"
        ];

        $this->mock_caja_chica->method('realizar_consulta')
            ->with('editar_descripcion')
            ->willReturn($resultado_esperado);

        $this->caja_chica->set_id_caja_chica(23);
        $this->caja_chica->set_descripcion("");
        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }
}
?>