<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/modulos_modelo.php";
//Mock
class ModulosTest extends TestCase
{
    private $modulos;
    private $mock_modulos;

    public function setUp(): void{
        $this->mock_modulos = $this->createMock(Modulos::class);
        $this->modulos = $this->mock_modulos;
    }

    public function tearDown(): void{
        unset($this->modulos);
        unset($this->mock_modulos);
    }

    public function testConsultarModulos(){
        $datos_simulados = [
            ['id_modulo' => 1, 'nombre' => 'Usuarios'],
            ['id_modulo' => 2, 'nombre' => 'Roles']
        ];

        $this->mock_modulos->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->modulos->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);
    }
}
?>