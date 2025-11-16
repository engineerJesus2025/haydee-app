<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\Rol;
// vendor\bin\phpunit tests
class RolTest extends TestCase
{
    private $rol;
    private $mock_rol; // Variable para el mock

    public function setUp(): void{
        // Crear el mock
        $this->mock_rol = $this->createMock(Rol::class);
        // Usar el mock
        $this->rol = $this->mock_rol;
    }

    public function tearDown(): void{
        unset($this->rol);
        unset($this->mock_rol); // Limpiar el mock
    }

    //Metodo verificar_nombre
    public function testBuscarRolExistente(){
        $resultado_esperado = [
            "estatus" => true,
            "busqueda" => "nombre"
        ];

        $this->mock_rol->method('set_nombre')->with("Administrador");
        $this->mock_rol->method('realizar_consulta')
            ->with('verificar_nombre')
            ->willReturn($resultado_esperado);

        $this->rol->set_nombre("Administrador");
        $resultado = $this->rol->realizar_consulta('verificar_nombre');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('nombre', $resultado["busqueda"]);
    }

    public function testBuscarRolInexistente(){
        $resultado_esperado = [
            "estatus" => false,
            "busqueda" => "nombre"
        ];

        $this->mock_rol->method('set_nombre')->with("rol_que_no_existe");
        $this->mock_rol->method('realizar_consulta')
            ->with('verificar_nombre')
            ->willReturn($resultado_esperado);

        $this->rol->set_nombre("rol_que_no_existe");
        $resultado = $this->rol->realizar_consulta('verificar_nombre');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString('nombre', $resultado["busqueda"]);
    }

    //Metodo consultar
    public function testConsultarRoles(){
        $datos_simulados = [
            [
                'id_rol' => 1,
                'nombre' => 'Administrador'
            ],
            [
                'id_rol' => 2,
                'nombre' => 'Usuario'
            ]
        ];

        $this->mock_rol->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->rol->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertArrayHasKey('id_rol', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);        
    }    

    //Metodo consultar_rol
    public function testConsultarRolUnicoIdCorrecto(){
        $datos_simulados = [
            'id_rol' => 1,
            'nombre' => 'Administrador'
        ];

        $this->mock_rol->method('set_id_rol')->with(1);
        $this->mock_rol->method('realizar_consulta')
            ->with('consultar_rol')
            ->willReturn($datos_simulados);

        $this->rol->set_id_rol(1);
        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertArrayHasKey('id_rol', $resultado);
        $this->assertArrayHasKey('nombre', $resultado);        
    }

    public function testConsultarRolUnicoIdIncorrecto(){
        $this->mock_rol->method('set_id_rol')->with(123122);
        $this->mock_rol->method('realizar_consulta')
            ->with('consultar_rol')
            ->willReturn(false); // Simula el false de la aserción

        $this->rol->set_id_rol(123122);
        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarRolUnicoDatosVacios(){
        $this->mock_rol->method('set_id_rol')->with('');
        $this->mock_rol->method('realizar_consulta')
            ->with('consultar_rol')
            ->willReturn(false);

        $this->rol->set_id_rol('');
        $resultado = $this->rol->realizar_consulta('consultar_rol');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo registrar
    public function testRegistrarRolDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];

        $this->mock_rol->method('set_nombre')->with("rol de prueba");
        $this->mock_rol->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->rol->set_nombre("rol de prueba");
        $resultado = $this->rol->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarRolDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'nombre' no posee un valor valido"
        ];

        $this->mock_rol->method('set_nombre')->with("rol_incorrecto+`+´123");
        $this->mock_rol->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->rol->set_nombre("rol_incorrecto+`+´123");
        $resultado = $this->rol->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'nombre' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarRolDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campos 'nombre' esta vacio"
        ];

        $this->mock_rol->method('set_nombre')->with("");
        $this->mock_rol->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->rol->set_nombre("");        
        $resultado = $this->rol->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campos 'nombre' esta vacio", $resultado["mensaje"]);
    }

    //Metodo editar_rol
    public function testEditarRolDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];

        $this->mock_rol->method('set_id_rol')->with(27);
        $this->mock_rol->method('set_nombre')->with("rol de prueba editado");
        $this->mock_rol->method('realizar_consulta')
            ->with('editar_rol')
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(27);
        $this->rol->set_nombre("rol de prueba editado");
        $resultado = $this->rol->realizar_consulta('editar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarRolDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'nombre' no posee un valor valido"
        ];

        $this->mock_rol->method('set_id_rol')->with(27);
        $this->mock_rol->method('set_nombre')->with("rol_incorrecto asd`++`213+");
        $this->mock_rol->method('realizar_consulta')
            ->with('editar_rol')
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(27);
        $this->rol->set_nombre("rol_incorrecto asd`++`213+");
        $resultado = $this->rol->realizar_consulta('editar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'nombre' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarRolIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El Rol seleccionado no existe"
        ];

        $this->mock_rol->method('set_id_rol')->with(23423);
        $this->mock_rol->method('set_nombre')->with("rol de prueba editado");
        $this->mock_rol->method('realizar_consulta')
            ->with('editar_rol')
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(23423); 
        $this->rol->set_nombre("rol de prueba editado");
        $resultado = $this->rol->realizar_consulta('editar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarRolDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campos 'nombre' esta vacio"
        ];

        $this->mock_rol->method('set_id_rol')->with(27);
        $this->mock_rol->method('set_nombre')->with("");
        $this->mock_rol->method('realizar_consulta')
            ->with('editar_rol')
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(27);
        $this->rol->set_nombre("");
        $resultado = $this->rol->realizar_consulta('editar_rol');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campos 'nombre' esta vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_rol
    public function testEliminarRolDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];

        $this->mock_rol->method('set_id_rol')->with(41);
        $this->mock_rol->method('realizar_consulta')
            ->with('eliminar_rol', true) // Asegúrate de que el 'with' coincida
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(41);
        $resultado = $this->rol->realizar_consulta('eliminar_rol', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarRolIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El Rol seleccionado no existe"
        ];

        $this->mock_rol->method('set_id_rol')->with(12312312);
        $this->mock_rol->method('realizar_consulta')
            ->with('eliminar_rol', true)
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol(12312312);
        $resultado = $this->rol->realizar_consulta('eliminar_rol', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarRolIDVacio(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El ID del Rol se envio vacio"
        ];

        $this->mock_rol->method('set_id_rol')->with('');
        $this->mock_rol->method('realizar_consulta')
            ->with('eliminar_rol', true)
            ->willReturn($resultado_esperado);

        $this->rol->set_id_rol('');
        $resultado = $this->rol->realizar_consulta('eliminar_rol', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID del Rol se envio vacio", $resultado["mensaje"]);
    }
}
?>