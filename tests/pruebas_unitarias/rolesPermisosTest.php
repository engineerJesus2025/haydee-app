<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/roles_permisos_modelo.php";
// vendor\bin\phpunit tests
class RolesPermisosTest extends TestCase
{
    private $roles_permisos;

    public function setUp(): void{
        $this->roles_permisos = new Roles_permisos();
    }

    public function tearDown(): void{
        unset($this->roles_permisos);
    }

    //Metodo consultar_roles_permisos
    public function testConsultarRolesPermisosIdCorrecto(){
        $this->roles_permisos->set_rol_id(2);
        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
 
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado[0]);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_rol_permiso', $resultado[0]);
        $this->assertArrayHasKey('rol_id', $resultado[0]);
        $this->assertArrayHasKey('permiso_usuario_id', $resultado[0]);
    }

    public function testConsultarRolesPermisosIdIncorrecto(){
        $this->roles_permisos->set_rol_id(212312);

        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarRolesPermisosDatosVacios(){
        $this->roles_permisos->set_rol_id('');

        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo consultar_permisos_por_usuario
    public function testConsultarPermisosPorUsuarioIdCorrecto(){
        $this->roles_permisos->set_rol_id(2);

        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado[0]);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre_permiso', $resultado[0]);        
    }

    public function testConsultarPermisosPorUsuarioIdIncorrecto(){
        $this->roles_permisos->set_rol_id(212312);

        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarPermisosPorUsuarioDatosVacios(){
        $this->roles_permisos->set_rol_id('');

        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar_permisos_roles
    public function testRegistrarPermisosRolesDatosCorrectos(){
        $this->roles_permisos->set_rol_id(27);
        $this->roles_permisos->set_permiso_usuario_id(1);

        $resultado = $this->roles_permisos->realizar_consulta('registrar_permisos_roles');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarPermisosRolesIDRolIncorrecto(){
        $this->roles_permisos->set_rol_id(12312312);
        $this->roles_permisos->set_permiso_usuario_id(1);

        $resultado = $this->roles_permisos->realizar_consulta('registrar_permisos_roles');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado para modificar permisos no existe", $resultado["mensaje"]);
    }

    public function testRegistrarPermisosRolesIDPermisoIncorrecto(){
        $this->roles_permisos->set_rol_id(27);
        $this->roles_permisos->set_permiso_usuario_id(1234234);

        $resultado = $this->roles_permisos->realizar_consulta('registrar_permisos_roles');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID de Usuario seleccionado para modificar permisos no existe", $resultado["mensaje"]);
    }

    public function testRegistrarPermisosRolesDatosVacios(){
        $this->roles_permisos->set_rol_id('');
        $this->roles_permisos->set_permiso_usuario_id('');

        $resultado = $this->roles_permisos->realizar_consulta('registrar_permisos_roles');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID del Rol para modificar los permisos se envio vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_roles_permisos
    public function testEliminarRolesPermisosDatosCorrectos(){
        $this->roles_permisos->set_rol_id(50);

        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarRolesPermisosIDIncorrecto(){
        $this->roles_permisos->set_rol_id(34123123);

        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado para modificar permisos no existe", $resultado["mensaje"]);
    }

    public function testEliminarRolesPermisosUnicoDatosVacios(){
        $this->roles_permisos->set_rol_id('');

        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID del Rol para modificar los permisos se envio vacio", $resultado["mensaje"]);
    }
}

?>