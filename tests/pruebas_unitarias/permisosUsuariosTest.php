<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/permisos_usuarios_modelo.php";

class PermisosUsuariosTest extends TestCase
{
    private $permisos_usuarios;

    public function setUp(): void{
        $this->permisos_usuarios = new Permisos_usuarios();
    }

    public function tearDown(): void{
        unset($this->permisos_usuarios);
    }

    //Metodo consultar
    public function testConsultarpermisos_usuarios(){
        $resultado = $this->permisos_usuarios->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_permiso_usuario', $resultado[0]);
        $this->assertArrayHasKey('nombre_accion', $resultado[0]);
        $this->assertArrayHasKey('modulo_id', $resultado[0]);
    }
}

?>