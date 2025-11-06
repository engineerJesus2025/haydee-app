<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/bitacora_modelo.php";
//Mock
class BitacoraTest extends TestCase
{
    private $bitacora;
    private $mock_bitacora;

    public function setUp(): void{
        $this->mock_bitacora = $this->createMock(Bitacora::class);
        $this->bitacora = $this->mock_bitacora;
    }

    public function tearDown(): void{
        unset($this->bitacora);
        unset($this->mock_bitacora);
    }

    public function testConsultarBitacora(){
        $datos_simulados = [
            [
                'fecha_hora' => '2023-01-01 10:00:00',
                'accion' => 'Consulta',
                'registro_alterado' => 'Tabla_X',
                'nombre_usuario' => 'Admin',
                'nombre_modulo' => 'Módulo_A',
                'nombre_rol' => 'Administrador'
            ]
        ];

        $this->mock_bitacora->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->bitacora->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertArrayHasKey('fecha_hora', $resultado[0]);
        $this->assertArrayHasKey('accion', $resultado[0]);
        $this->assertArrayHasKey('registro_alterado', $resultado[0]);
        $this->assertArrayHasKey('nombre_usuario', $resultado[0]);
        $this->assertArrayHasKey('nombre_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre_rol', $resultado[0]);
    }
}
?>