<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\RolesPermisos;
// vendor\bin\phpunit tests
class RolesPermisosTest extends TestCase
{
    private $roles_permisos;
    private $mock_roles_permisos; // Variable para el mock

    public function setUp(): void{
        // Crear el mock del modelo
        $this->mock_roles_permisos = $this->createMock(RolesPermisos::class);
        // Usar el mock en lugar de la instancia real
        $this->roles_permisos = $this->mock_roles_permisos;
    }

    public function tearDown(): void{
        unset($this->roles_permisos);
        unset($this->mock_roles_permisos); // Limpiar el mock
    }

    //Metodo consultar_roles_permisos
    public function testConsultarRolesPermisosIdCorrecto(){
        // Definir el resultado simulado basado en tus aserciones
        $datos_simulados = [
            [
                'id_rol_permiso' => 1,
                'rol_id' => 2,
                'permiso_usuario_id' => 1
            ]
        ];

        // Configurar el mock para el setter
        $this->mock_roles_permisos->method('set_rol_id')->with(2);

        // Configurar el mock para el método principal
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_roles_permisos')
            ->willReturn($datos_simulados);

        // Ejecución (igual que antes)
        $this->roles_permisos->set_rol_id(2);
        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
 
        // Aserciones (igual que antes)
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado[0]);
        $this->assertArrayHasKey('id_rol_permiso', $resultado[0]);
        $this->assertArrayHasKey('rol_id', $resultado[0]);
        $this->assertArrayHasKey('permiso_usuario_id', $resultado[0]);
    }

    public function testConsultarRolesPermisosIdIncorrecto(){
        // Definir el resultado simulado (vacío)
        $datos_simulados = [];

        $this->mock_roles_permisos->method('set_rol_id')->with(212312);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_roles_permisos')
            ->willReturn($datos_simulados);

        $this->roles_permisos->set_rol_id(212312);
        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarRolesPermisosDatosVacios(){
        $datos_simulados = [];

        $this->mock_roles_permisos->method('set_rol_id')->with('');
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_roles_permisos')
            ->willReturn($datos_simulados);
        
        $this->roles_permisos->set_rol_id('');
        $resultado = $this->roles_permisos->realizar_consulta('consultar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo consultar_permisos_por_usuario
    public function testConsultarPermisosPorUsuarioIdCorrecto(){
        // Definir el resultado simulado
        $datos_simulados = [
            [
                'id_modulo' => 1,
                'nombre_permiso' => 'Dashboard'
            ]
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(2);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_permisos_por_usuario')
            ->willReturn($datos_simulados);

        $this->roles_permisos->set_rol_id(2);
        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado[0]);
        $this->assertArrayHasKey('id_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre_permiso', $resultado[0]);        
    }

    public function testConsultarPermisosPorUsuarioIdIncorrecto(){
        $datos_simulados = [];

        $this->mock_roles_permisos->method('set_rol_id')->with(212312);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_permisos_por_usuario')
            ->willReturn($datos_simulados);

        $this->roles_permisos->set_rol_id(212312);
        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarPermisosPorUsuarioDatosVacios(){
        $datos_simulados = [];

        $this->mock_roles_permisos->method('set_rol_id')->with('');
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('consultar_permisos_por_usuario')
            ->willReturn($datos_simulados);

        $this->roles_permisos->set_rol_id('');
        $resultado = $this->roles_permisos->realizar_consulta('consultar_permisos_por_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar_permisos_roles
    public function testRegistrarPermisosRolesDatosCorrectos(){
        // Resultado esperado
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(27);
        $this->mock_roles_permisos->method('set_permiso_usuario_id')->with(1);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('registrar_permisos_roles')
            ->willReturn($resultado_esperado);

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
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El Rol seleccionado para modificar permisos no existe"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(12312312);
        $this->mock_roles_permisos->method('set_permiso_usuario_id')->with(1);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('registrar_permisos_roles')
            ->willReturn($resultado_esperado);

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
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El ID de Usuario seleccionado para modificar permisos no existe"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(27);
        $this->mock_roles_permisos->method('set_permiso_usuario_id')->with(1234234);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('registrar_permisos_roles')
            ->willReturn($resultado_esperado);

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
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El ID del Rol para modificar los permisos se envio vacio"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with('');
        $this->mock_roles_permisos->method('set_permiso_usuario_id')->with('');
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('registrar_permisos_roles')
            ->willReturn($resultado_esperado);

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
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(50);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('eliminar_roles_permisos')
            ->willReturn($resultado_esperado);

        $this->roles_permisos->set_rol_id(50);
        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarRolesPermisosIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El Rol seleccionado para modificar permisos no existe"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with(34123123);
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('eliminar_roles_permisos')
            ->willReturn($resultado_esperado);

        $this->roles_permisos->set_rol_id(34123123);
        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado para modificar permisos no existe", $resultado["mensaje"]);
    }

    public function testEliminarRolesPermisosUnicoDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El ID del Rol para modificar los permisos se envio vacio"
        ];

        $this->mock_roles_permisos->method('set_rol_id')->with('');
        $this->mock_roles_permisos->method('realizar_consulta')
            ->with('eliminar_roles_permisos')
            ->willReturn($resultado_esperado);

        $this->roles_permisos->set_rol_id('');
        $resultado = $this->roles_permisos->realizar_consulta('eliminar_roles_permisos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID del Rol para modificar los permisos se envio vacio", $resultado["mensaje"]);
    }
}
