<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\PermisosUsuarios;
//Mock
class PermisosUsuariosTest extends TestCase
{
    private $permisos_usuarios;
    private $mock_permisos_usuarios;

    public function setUp(): void{
        $this->mock_permisos_usuarios = $this->createMock(PermisosUsuarios::class);
        $this->permisos_usuarios = $this->mock_permisos_usuarios;
    }

    public function tearDown(): void{
        unset($this->permisos_usuarios);
        unset($this->mock_permisos_usuarios);
    }

    public function testConsultarPermisosUsuarios(){
        $datos_simulados = [
            [
                'id_permiso_usuario' => 1,
                'nombre_accion' => 'crear',
                'modulo_id' => 1
            ],
            [
                'id_permiso_usuario' => 2,
                'nombre_accion' => 'editar',
                'modulo_id' => 1
            ]
        ];

        $this->mock_permisos_usuarios->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->permisos_usuarios->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_permiso_usuario', $resultado[0]);
        $this->assertArrayHasKey('nombre_accion', $resultado[0]);
        $this->assertArrayHasKey('modulo_id', $resultado[0]);
    }
}
?>