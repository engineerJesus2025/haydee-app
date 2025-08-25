<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/rol_modelo.php";
// vendor\bin\phpunit tests
class RolTest extends TestCase
{
    private $rol;

    public function setUp(): void{
        $this->rol = new Rol();
    }

    public function tearDown(): void{
        unset($this->rol);
    }

    //Metodo consultar
    public function testConsultarRoles(){
        $resultado = $this->rol->realizar_consulta('consultar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_rol', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);        
    }

    //Metodo verificar_nombre
    public function testBuscarRolExistente(){
        $this->rol->set_nombre("Administrador");

        $resultado = $this->rol->realizar_consulta('verificar_nombre');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('nombre', $resultado["busqueda"]);
    }

    public function testBuscarRolInexistente(){
        $this->rol->set_nombre("rol_que_no_existe");

        $resultado = $this->rol->realizar_consulta('verificar_nombre');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString('nombre', $resultado["busqueda"]);
    }

    //Metodo consultar_roles
    public function testConsultarRolesExternos(){
        $resultado = $this->rol->realizar_consulta('consultar_roles');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_rol', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);        
    }


    //Metodo consultar_rol
    public function testConsultarRolUnicoIdCorrecto(){
        $this->rol->set_id_rol(1);

        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_rol', $resultado);
        $this->assertArrayHasKey('nombre', $resultado);        
    }

    public function testConsultarRolUnicoIdIncorrecto(){
        $this->rol->set_id_rol(123122);

        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarRolUnicoDatosVacios(){
        $this->rol->set_id_rol('');

        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo registrar
    public function testRegistrarRolDatosCorrectos(){
        $this->rol->set_nombre("rol de prueba");

        $resultado = $this->rol->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarRolDatosIncorrecto(){
        $this->rol->set_nombre("rol_incorrecto+`+´123");

        $resultado = $this->rol->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'nombre' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarRolDatosVacios(){
        $this->rol->set_nombre("");        

        $resultado = $this->rol->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campos 'nombre' esta vacio", $resultado["mensaje"]);
    }

    //Metodo editar_rol
    public function testEditarRolDatosCorrectos(){
        $this->rol->set_id_rol(27); // Id existente
        $this->rol->set_nombre("rol de prueba editado");

        $resultado = $this->rol->realizar_consulta('editar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarRolDatosIncorrecto(){
        $this->rol->set_id_rol(27); // Id existente
        $this->rol->set_nombre("rol_incorrecto asd`++`213+");

        $resultado = $this->rol->realizar_consulta('editar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'nombre' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarRolIDIncorrecto(){
        $this->rol->set_id_rol(23423); 
        $this->rol->set_nombre("rol de prueba editado");

        $resultado = $this->rol->realizar_consulta('editar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarRolDatosVacios(){
        $this->rol->set_id_rol(27);
        $this->rol->set_nombre("");

        $resultado = $this->rol->realizar_consulta('editar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campos 'nombre' esta vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_rol
    public function testEliminarRolDatosCorrectos(){
        $this->rol->set_id_rol(39); // Id existente

        $resultado = $this->rol->realizar_consulta('eliminar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarRolIDIncorrecto(){
        $this->rol->set_id_rol(12312312); // Id inexistente

        $resultado = $this->rol->realizar_consulta('eliminar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarRolIDVacio(){
        $this->rol->set_id_rol('');

        $resultado = $this->rol->realizar_consulta('eliminar_rol',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID del Rol se envio vacio", $resultado["mensaje"]);
    }
}

?>