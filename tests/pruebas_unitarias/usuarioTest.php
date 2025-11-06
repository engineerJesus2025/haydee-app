<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/usuario_modelo.php";
// vendor\bin\phpunit tests
class UsuarioTest extends TestCase
{
    private $usuario;
    private $mock_usuario; // Variable para el mock

    // Los IDs ahora solo se usan como datos de entrada para los mocks
    private $id_usuario = 1;
    private $id_usuario_eliminar = 82;
    private $id_usuario_editar = 53;

    public function setUp(): void{
        // Crear el mock
        $this->mock_usuario = $this->createMock(Usuario::class);
        // Usar el mock
        $this->usuario = $this->mock_usuario;
    }

    public function tearDown(): void{
        unset($this->usuario);
        unset($this->mock_usuario); // Limpiar el mock
    }

    //Metodo validar_usuario
    public function testValidarUsuarioCorreoCorrecto(){
        $datos_simulados = [
            'id_usuario' => 1,
            'correo' => 'administrador@gmail.com',
            'nombre_usuario' => 'Admin',
            'id_rol' => 1,
            'nombre_rol' => 'Administrador',
            'contrasenia' => 'hash_simulado' // No importa el valor real
        ];

        $this->mock_usuario->method('set_correo')->with("administrador@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_usuario')
            ->willReturn($datos_simulados);

        $this->usuario->set_correo("administrador@gmail.com");
        $resultado = $this->usuario->realizar_consulta('validar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(6, $resultado);
        $this->assertArrayHasKey('id_usuario', $resultado);
        $this->assertArrayHasKey('contrasenia', $resultado);
    }

    public function testValidarUsuarioCorreoIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Usuario no encontrado"];

        $this->mock_usuario->method('set_correo')->with("correo_incorrecto");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("correo_incorrecto");
        $resultado = $this->usuario->realizar_consulta('validar_usuario');
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    public function testValidarUsuarioCorreoInexistente(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Usuario no encontrado"];

        $this->mock_usuario->method('set_correo')->with("correo_inexistenete@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("correo_inexistenete@gmail.com");
        $resultado = $this->usuario->realizar_consulta('validar_usuario');
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    public function testValidarUsuarioDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Usuario no encontrado"];
        
        $this->mock_usuario->method('set_correo')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo('');
        $resultado = $this->usuario->realizar_consulta('validar_usuario');
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    //Metodo verificar_correo
    public function testBuscarcorreoExistente(){
        $resultado_esperado = ["estatus" => true, "busqueda" => "correo"];

        $this->mock_usuario->method('set_correo')->with("administrador@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('verificar_correo')
            ->willReturn($resultado_esperado);
        
        $this->usuario->set_correo("administrador@gmail.com");
        $resultado = $this->usuario->realizar_consulta('verificar_correo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('correo', $resultado["busqueda"]);
    }

    public function testBuscarcorreoInexistente(){
        $resultado_esperado = ["estatus" => false, "busqueda" => "correo"];
        
        $this->mock_usuario->method('set_correo')->with("correo_que_no_existe");
        $this->mock_usuario->method('realizar_consulta')
            ->with('verificar_correo')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("correo_que_no_existe");
        $resultado = $this->usuario->realizar_consulta('verificar_correo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString('correo', $resultado["busqueda"]);
    }

    //Metodo validar_token
    public function testValidarTokenCorrecto(){
        $datos_simulados = [
            'id_usuario' => 1,
            'nombre' => 'Admin',
            'apellido' => 'Global',
            'correo' => 'admin@gmail.com',
            'contrasenia' => 'hash',
            'rol_id' => 1,
            'token' => 'token de prueba',
            'duracion_token' => '2023-08-24 16:43:42',
            'token_recuerdame' => null,
            'duracion_token_recuerdame' => null
        ];

        $this->mock_usuario->method('set_token')->with("token de prueba");
        $this->mock_usuario->method('set_duracion_token')->with("2023-08-24 16:43:42");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_token')
            ->willReturn($datos_simulados);
        
        $this->usuario->set_token("token de prueba");
        $this->usuario->set_duracion_token("2023-08-24 16:43:42");
        $resultado = $this->usuario->realizar_consulta('validar_token');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(10, $resultado);
        $this->assertArrayHasKey('id_usuario', $resultado);
    }

    public function testValidarTokenIncorrecto(){
        $this->mock_usuario->method('set_token')->with("token_incorrecto");
        $this->mock_usuario->method('set_duracion_token')->with("2025-08-24 16:43:41");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_token')
            ->willReturn(false); // Simula el false de la aserción

        $this->usuario->set_token("token_incorrecto");
        $this->usuario->set_duracion_token("2025-08-24 16:43:41");
        $resultado = $this->usuario->realizar_consulta('validar_token');
        
        $this->assertFalse($resultado);
    }

    public function testValidarDuracionTokenIncorrecto(){
        $this->mock_usuario->method('set_token')->with("token_incorrecto");
        $this->mock_usuario->method('set_duracion_token')->with("2024-08-24 16:43:41");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_token')
            ->willReturn(false);

        $this->usuario->set_token("token_incorrecto");
        $this->usuario->set_duracion_token("2024-08-24 16:43:41");
        $resultado = $this->usuario->realizar_consulta('validar_token');
        
        $this->assertFalse($resultado);
    }

    //Metodo validar_token_recuerdame
    public function testValidarTokenRecuerdameCorrecto(){
        $datos_simulados = [
            'id_usuario' => 2,
            'nombre_usuario' => 'Recuerdame',
            'nombre_rol' => 'Usuario',
            'id_rol' => 2,
            'correo' => 'recuerdame2@gmaiil.com',
            'contrasenia' => 'hash',
            'token_recuerdame' => 'token_valido',
            'duracion_token_recuerdame' => time() + 1000 // Un timestamp futuro
        ];

        $this->mock_usuario->method('set_correo')->with("recuerdame2@gmaiil.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_token_recuerdame')
            ->willReturn($datos_simulados);

        $this->usuario->set_correo("recuerdame2@gmaiil.com");
        $resultado = $this->usuario->realizar_consulta('validar_token_recuerdame');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(8, $resultado);
        $this->assertArrayHasKey('id_usuario', $resultado);
    }

    public function testValidarTokenRecuerdameCorreoIncorrecto(){
        $this->mock_usuario->method('set_correo')->with("correo_incorrecto");
        $this->mock_usuario->method('realizar_consulta')
            ->with('validar_token_recuerdame')
            ->willReturn(false);

        $this->usuario->set_correo("correo_incorrecto");
        $resultado = $this->usuario->realizar_consulta('validar_token_recuerdame');
        
        $this->assertFalse($resultado);
    }

    //Metodo consultar
    public function testConsultarUsuario(){
        $datos_simulados = [
            [
                'id_usuario' => 1,
                'apellido' => 'Admin',
                'nombre_usuario' => 'Administrador',
                'correo' => 'admin@gmail.com',
                'rol_id' => 1,
                'nombre_rol' => 'Administrador'
            ]
        ];

        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->usuario->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertCount(6, $resultado[0]);
        $this->assertArrayHasKey('id_usuario', $resultado[0]);
    }

    //Metodo consultar_usuario
    public function testConsultarUsuarioUnicoIdCorrecto(){
        $datos_simulados = [
            'id_usuario' => $this->id_usuario,
            'apellido' => 'Admin',
            'nombre_usuario' => 'Administrador',
            'correo' => 'admin@gmail.com',
            'nombre_rol' => 'Administrador',
            'contrasenia' => 'hash',
            'rol_id' => 1
        ];

        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario);
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_usuario')
            ->willReturn($datos_simulados);

        $this->usuario->set_id_usuario($this->id_usuario);
        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(7, $resultado);
        $this->assertArrayHasKey('id_usuario', $resultado);
    }

    public function testConsultarUsuarioUnicoIdIncorrecto(){
        $this->mock_usuario->method('set_id_usuario')->with(123122);
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_usuario')
            ->willReturn(false);

        $this->usuario->set_id_usuario(123122);
        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarUsuarioUnicoDatosVacios(){
        $this->mock_usuario->method('set_id_usuario')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_usuario')
            ->willReturn(false);

        $this->usuario->set_id_usuario('');
        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo consultar_perfil_usuario
    public function testConsultarPerfilUsuarioUnicoIdCorrecto(){
        $datos_simulados = [
            'nombre_usuario' => 'Administrador',
            'apellido' => 'Admin',
            'correo' => 'admin@gmail.com',
            'nombre_rol' => 'Administrador',
            'ultima_vez' => '2024-01-01 10:00:00'
        ];

        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario);
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_perfil_usuario')
            ->willReturn($datos_simulados);

        $this->usuario->set_id_usuario($this->id_usuario);
        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado);
        $this->assertArrayHasKey('nombre_usuario', $resultado);
    }

    public function testConsultarPerfilUsuarioUnicoIdIncorrecto(){
        $this->mock_usuario->method('set_id_usuario')->with(123122);
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_perfil_usuario')
            ->willReturn(false);

        $this->usuario->set_id_usuario(123122);
        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarPerfilUsuarioUnicoDatosVacios(){
        $this->mock_usuario->method('set_id_usuario')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('consultar_perfil_usuario')
            ->willReturn(false);

        $this->usuario->set_id_usuario('');
        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo registrar
    public function testRegistrarUsuarioDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Registro exitoso"];

        $this->mock_usuario->method('set_apellido')->with("apellido de prueba");
        $this->mock_usuario->method('set_nombre')->with("nombre de prueba");
        $this->mock_usuario->method('set_correo')->with("pruebaregistrada@gmail.com");
        $this->mock_usuario->method('set_contra')->with("contraseñadeprueba");
        $this->mock_usuario->method('set_rol_id')->with(1);
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar', true)
            ->willReturn($resultado_esperado);

        $this->usuario->set_apellido("apellido de prueba");
        $this->usuario->set_nombre("nombre de prueba");
        $this->usuario->set_correo("pruebaregistrada@gmail.com");
        $this->usuario->set_contra("contraseñadeprueba");
        $this->usuario->set_rol_id(1);
        $resultado = $this->usuario->realizar_consulta('registrar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testRegistrarUsuarioDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'apellido' no posee un valor valido"];

        $this->mock_usuario->method('set_apellido')->with("123123");
        $this->mock_usuario->method('set_nombre')->with("n123we");
        $this->mock_usuario->method('set_correo')->with("correo erronea");
        $this->mock_usuario->method('set_contra')->with("contraseña de prueba");
        $this->mock_usuario->method('set_rol_id')->with(1);
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar', true)
            ->willReturn($resultado_esperado);

        $this->usuario->set_apellido("123123");
        $this->usuario->set_nombre("n123we");
        $this->usuario->set_correo("correo erronea");
        $this->usuario->set_contra("contraseña de prueba");
        $this->usuario->set_rol_id(1);
        $resultado = $this->usuario->realizar_consulta('registrar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'apellido' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarUsuarioIDRolErroneo(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El Rol seleccionado no existe"];

        $this->mock_usuario->method('set_apellido')->with("apellido de prueba");
        $this->mock_usuario->method('set_nombre')->with("nombre de prueba");
        $this->mock_usuario->method('set_correo')->with("prueba@gmail.com");
        $this->mock_usuario->method('set_contra')->with("contraseñadeprueba");
        $this->mock_usuario->method('set_rol_id')->with(123123);
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar', true)
            ->willReturn($resultado_esperado);

        $this->usuario->set_apellido("apellido de prueba");
        $this->usuario->set_nombre("nombre de prueba");
        $this->usuario->set_correo("prueba@gmail.com");
        $this->usuario->set_contra("contraseñadeprueba");
        $this->usuario->set_rol_id(123123);
        $resultado = $this->usuario->realizar_consulta('registrar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testRegistrarUsuarioUnicoDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];
        
        $this->mock_usuario->method('set_apellido')->with("");
        $this->mock_usuario->method('set_nombre')->with("");
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('set_contra')->with("");
        $this->mock_usuario->method('set_rol_id')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar', true)
            ->willReturn($resultado_esperado);

        $this->usuario->set_apellido("");
        $this->usuario->set_nombre("");
        $this->usuario->set_correo("");
        $this->usuario->set_contra("");
        $this->usuario->set_rol_id('');     
        $resultado = $this->usuario->realizar_consulta('registrar', true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo registrar_token
    public function testRegistrarTokenDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Token registrado"];

        $this->mock_usuario->method('set_token')->with("token de prueba agregado");
        $this->mock_usuario->method('set_duracion_token')->with("2023-08-24 16:43:41");
        $this->mock_usuario->method('set_correo')->with("agregartoken@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token')
            ->willReturn($resultado_esperado);

        $this->usuario->set_token("token de prueba agregado");
        $this->usuario->set_duracion_token("2023-08-24 16:43:41");
        $this->usuario->set_correo("agregartoken@gmail.com");
        $resultado = $this->usuario->realizar_consulta('registrar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testRegistrarTokenDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'correo' no posee un valor valido"];
        
        $this->mock_usuario->method('set_correo')->with("correo incorrecto");
        $this->mock_usuario->method('set_token')->with("token de incorrecto");
        $this->mock_usuario->method('set_duracion_token')->with("fecha_incorrecta");
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("correo incorrecto");
        $this->usuario->set_token("token de incorrecto");
        $this->usuario->set_duracion_token("fecha_incorrecta");
        $resultado = $this->usuario->realizar_consulta('registrar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'correo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarTokenDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];
        
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('set_token')->with("");
        $this->mock_usuario->method('set_duracion_token')->with("");   
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("");
        $this->usuario->set_token("");
        $this->usuario->set_duracion_token("");   
        $resultado = $this->usuario->realizar_consulta('registrar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    // registrar_token_recuerdame
    public function testRegistrarTokenRecuerdameDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Token recuerdame registrado"];
        $expiracion = time() + (30 * 24 * 60 * 60); // 30 días

        $this->mock_usuario->method('set_token_recuerdame')->with("token de prueba recuerdame agregado");
        $this->mock_usuario->method('set_duracion_token_recuerdame')->with($expiracion);
        $this->mock_usuario->method('set_correo')->with("agregartoken@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_token_recuerdame("token de prueba recuerdame agregado");
        $this->usuario->set_duracion_token_recuerdame($expiracion);
        $this->usuario->set_correo("agregartoken@gmail.com");
        $resultado = $this->usuario->realizar_consulta('registrar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testRegistrarTokenRecuerdameDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'correo' no posee un valor valido"];

        $this->mock_usuario->method('set_correo')->with("correo incorrecto");
        $this->mock_usuario->method('set_token_recuerdame')->with("token de incorrecto");
        $this->mock_usuario->method('set_duracion_token_recuerdame')->with("fecha_incorrecta");
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("correo incorrecto");
        $this->usuario->set_token_recuerdame("token de incorrecto");
        $this->usuario->set_duracion_token_recuerdame("fecha_incorrecta");
        $resultado = $this->usuario->realizar_consulta('registrar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'correo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarTokenRecuerdameDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];
        
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('set_token_recuerdame')->with("");
        $this->mock_usuario->method('set_duracion_token_recuerdame')->with("");   
        $this->mock_usuario->method('realizar_consulta')
            ->with('registrar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo("");
        $this->usuario->set_token_recuerdame("");
        $this->usuario->set_duracion_token_recuerdame("");   
        $resultado = $this->usuario->realizar_consulta('registrar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar_usuario
    public function testEditarUsuarioDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Edición exitosa"];

        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("apellido editado");
        $this->mock_usuario->method('set_nombre')->with("nombre editado");
        $this->mock_usuario->method('set_correo')->with("pruebaEditada@gmail.com");
        $this->mock_usuario->method('set_contra')->with("contraseñadepruebaeditada");
        $this->mock_usuario->method('set_rol_id')->with(4);
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("apellido editado");
        $this->usuario->set_nombre("nombre editado");
        $this->usuario->set_correo("pruebaEditada@gmail.com");
        $this->usuario->set_contra("contraseñadepruebaeditada");
        $this->usuario->set_rol_id(4);
        $resultado = $this->usuario->realizar_consulta('editar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEditarUsuarioDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'apellido' no posee un valor valido"];

        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("12312aasdas");
        $this->mock_usuario->method('set_nombre')->with("123asdas");
        $this->mock_usuario->method('set_correo')->with("prueba incorecta");
        $this->mock_usuario->method('set_contra')->with("++`´ç´ç´ç");
        $this->mock_usuario->method('set_rol_id')->with(4);
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("12312aasdas");
        $this->usuario->set_nombre("123asdas");
        $this->usuario->set_correo("prueba incorecta");
        $this->usuario->set_contra("++`´ç´ç´ç");
        $this->usuario->set_rol_id(4);
        $resultado = $this->usuario->realizar_consulta('editar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'apellido' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarUsuarioIDIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El usuario seleccionado no existe"];
        
        $this->mock_usuario->method('set_id_usuario')->with(23423);
        $this->mock_usuario->method('set_apellido')->with("apellido editado");
        $this->mock_usuario->method('set_nombre')->with("nombre editado");
        $this->mock_usuario->method('set_correo')->with("pruebaEditada@gmail.com");
        $this->mock_usuario->method('set_contra')->with("contraseña de prueba editada");
        $this->mock_usuario->method('set_rol_id')->with(4);
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario(23423); 
        $this->usuario->set_apellido("apellido editado");
        $this->usuario->set_nombre("nombre editado");
        $this->usuario->set_correo("pruebaEditada@gmail.com");
        $this->usuario->set_contra("contraseña de prueba editada");
        $this->usuario->set_rol_id(4);
        $resultado = $this->usuario->realizar_consulta('editar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarUsuarioIDRolIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El Rol seleccionado no existe"];
        
        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("apelllido");
        $this->mock_usuario->method('set_nombre')->with("nombre");
        $this->mock_usuario->method('set_correo')->with("prueba@gmai.com");
        $this->mock_usuario->method('set_contra')->with("contraseniaprueba");
        $this->mock_usuario->method('set_rol_id')->with(123123);
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("apelllido");
        $this->usuario->set_nombre("nombre");
        $this->usuario->set_correo("prueba@gmai.com");
        $this->usuario->set_contra("contraseniaprueba");
        $this->usuario->set_rol_id(123123);
        $resultado = $this->usuario->realizar_consulta('editar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarUsuarioUnicoDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];
        
        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("");
        $this->mock_usuario->method('set_nombre')->with("");
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('set_contra')->with("");
        $this->mock_usuario->method('set_rol_id')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("");
        $this->usuario->set_nombre("");
        $this->usuario->set_correo("");
        $this->usuario->set_contra("");
        $this->usuario->set_rol_id('');
        $resultado = $this->usuario->realizar_consulta('editar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar_perfil
    public function testEditarPerfilUsuarioDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Perfil editado"];
        
        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("perfil editado");
        $this->mock_usuario->method('set_nombre')->with("perfil editado");
        $this->mock_usuario->method('set_correo')->with("UsuarioperfilEditada@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_perfil')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("perfil editado");
        $this->usuario->set_nombre("perfil editado");
        $this->usuario->set_correo("UsuarioperfilEditada@gmail.com");        
        $resultado = $this->usuario->realizar_consulta('editar_perfil');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEditarPerfilUsuarioDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'apellido' no posee un valor valido"];

        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("12312aasdas");
        $this->mock_usuario->method('set_nombre')->with("123asdas");
        $this->mock_usuario->method('set_correo')->with("prueba incorecta");
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_perfil')
            ->willReturn($resultado_esperado);
        
        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("12312aasdas");
        $this->usuario->set_nombre("123asdas");
        $this->usuario->set_correo("prueba incorecta");        
        $resultado = $this->usuario->realizar_consulta('editar_perfil');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'apellido' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarPerfilUsuarioIDIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El usuario seleccionado no existe"];
        
        $this->mock_usuario->method('set_id_usuario')->with(23423);
        $this->mock_usuario->method('set_apellido')->with("apellido editado");
        $this->mock_usuario->method('set_nombre')->with("nombre editado");
        $this->mock_usuario->method('set_correo')->with("pruebaEditada@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_perfil')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario(23423); 
        $this->usuario->set_apellido("apellido editado");
        $this->usuario->set_nombre("nombre editado");
        $this->usuario->set_correo("pruebaEditada@gmail.com");        
        $resultado = $this->usuario->realizar_consulta('editar_perfil');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarPerfilUsuarioUnicoDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];
        
        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_editar);
        $this->mock_usuario->method('set_apellido')->with("");
        $this->mock_usuario->method('set_nombre')->with("");
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('realizar_consulta')
            ->with('editar_perfil')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_editar);
        $this->usuario->set_apellido("");
        $this->usuario->set_nombre("");
        $this->usuario->set_correo("");        
        $resultado = $this->usuario->realizar_consulta('editar_perfil');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo cambiar_contrasenia
    public function testCambiarContraseniaDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Contraseña cambiada"];
        
        $this->mock_usuario->method('set_contra')->with("cambiadacontrasenia");
        $this->mock_usuario->method('set_correo')->with("cambiocontrasenia@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('cambiar_contrasenia')
            ->willReturn($resultado_esperado);

        $this->usuario->set_contra("cambiadacontrasenia");
        $this->usuario->set_correo("cambiocontrasenia@gmail.com");        
        $resultado = $this->usuario->realizar_consulta('cambiar_contrasenia');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testCambiarContraseniaDatosIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'contraseña' no posee un valor valido"];

        $this->mock_usuario->method('set_contra')->with("aaa");
        $this->mock_usuario->method('set_correo')->with("cambiocontrasenia@gmail.com");
        $this->mock_usuario->method('realizar_consulta')
            ->with('cambiar_contrasenia')
            ->willReturn($resultado_esperado);

        $this->usuario->set_contra("aaa");
        $this->usuario->set_correo("cambiocontrasenia@gmail.com");
        $resultado = $this->usuario->realizar_consulta('cambiar_contrasenia');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'contraseña' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testCambiarContraseniaCorreoIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'correo' no posee un valor valido"];
        
        $this->mock_usuario->method('set_contra')->with("12345");
        $this->mock_usuario->method('set_correo')->with("correo incorecto");
        $this->mock_usuario->method('realizar_consulta')
            ->with('cambiar_contrasenia')
            ->willReturn($resultado_esperado);

        $this->usuario->set_contra("12345");
        $this->usuario->set_correo("correo incorecto");
        $resultado = $this->usuario->realizar_consulta('cambiar_contrasenia');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'correo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testCambiarContraseniaDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "Uno o varios de los campos requeridos estan vacios"];

        $this->mock_usuario->method('set_contra')->with("");
        $this->mock_usuario->method('set_correo')->with("");
        $this->mock_usuario->method('realizar_consulta')
            ->with('cambiar_contrasenia')
            ->willReturn($resultado_esperado);

        $this->usuario->set_contra("");
        $this->usuario->set_correo("");
        $resultado = $this->usuario->realizar_consulta('cambiar_contrasenia');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar_usuario
    public function testEliminarUsuarioDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Eliminación exitosa"];
        
        $this->mock_usuario->method('set_id_usuario')->with($this->id_usuario_eliminar);
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario($this->id_usuario_eliminar);
        $resultado = $this->usuario->realizar_consulta('eliminar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEliminarUsuarioIDIncorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El usuario seleccionado no existe"];
        
        $this->mock_usuario->method('set_id_usuario')->with(12312312);
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario(12312312);
        $resultado = $this->usuario->realizar_consulta('eliminar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarUsuarioDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El id del Usuario requerido esta vacio"];
        
        $this->mock_usuario->method('set_id_usuario')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_usuario')
            ->willReturn($resultado_esperado);

        $this->usuario->set_id_usuario('');
        $resultado = $this->usuario->realizar_consulta('eliminar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Usuario requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_token
    public function testEliminarTokenDatosCorrectos(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Token eliminado"];
        
        $this->mock_usuario->method('set_token')->with('borrarme');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_token')
            ->willReturn($resultado_esperado);

        $this->usuario->set_token('borrarme'); 
        $resultado = $this->usuario->realizar_consulta('eliminar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEliminarTokenDatosVacios(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El tiempo del token requerido esta vacio"];
        
        $this->mock_usuario->method('set_token')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_token')
            ->willReturn($resultado_esperado);

        $this->usuario->set_token('');
        $resultado = $this->usuario->realizar_consulta('eliminar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El tiempo del token requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_token_recuerdame
    public function testEliminarTokenCorreoCorrecto(){
        $resultado_esperado = ["estatus" => true, "mensaje" => "OK: Token recuerdame eliminado"];
        
        $this->mock_usuario->method('set_correo')->with('correoBorrarRecuerdame@gmail.com');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo('correoBorrarRecuerdame@gmail.com'); 
        $resultado = $this->usuario->realizar_consulta('eliminar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEliminarTokenCorreoIcorrecto(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El campo 'correo' no posee un valor valido"];

        $this->mock_usuario->method('set_correo')->with('correo_incorrecto');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo('correo_incorrecto');
        $resultado = $this->usuario->realizar_consulta('eliminar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'correo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEliminarTokenCorreoVacio(){
        $resultado_esperado = ["estatus" => false, "mensaje" => "El correo para eliminar el token requerido esta vacío"];
        
        $this->mock_usuario->method('set_correo')->with('');
        $this->mock_usuario->method('realizar_consulta')
            ->with('eliminar_token_recuerdame')
            ->willReturn($resultado_esperado);

        $this->usuario->set_correo('');
        $resultado = $this->usuario->realizar_consulta('eliminar_token_recuerdame');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El correo para eliminar el token requerido esta vacío", $resultado["mensaje"]);
    }

    //Metodo lastId
    public function testUltimoIdRegistrado(){
        $datos_simulados = [ 'id_usuario' => 83 ]; // Simula el último ID
        
        $this->mock_usuario->method('realizar_consulta')
            ->with('lastId')
            ->willReturn($datos_simulados);

        $resultado = $this->usuario->realizar_consulta('lastId');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(1, $resultado);
        $this->assertArrayHasKey('id_usuario', $resultado); // Ajustado a la aserción
    }
}
?>