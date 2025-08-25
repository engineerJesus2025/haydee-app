<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/usuario_modelo.php";
// vendor\bin\phpunit tests
class UsuarioTest extends TestCase
{
    private $usuario;

    public function setUp(): void{
        $this->usuario = new Usuario();
    }

    public function tearDown(): void{
        unset($this->usuario);
    }

    //Metodo validar_usuario
    public function testValidarUsuarioCorreoCorrecto(){
        $this->usuario->set_correo("administrador@gmail.com");

        $resultado = $this->usuario->realizar_consulta('validar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(6, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_usuario', $resultado);
        $this->assertArrayHasKey('correo', $resultado);
        $this->assertArrayHasKey('nombre_usuario', $resultado);
        $this->assertArrayHasKey('id_rol', $resultado);
        $this->assertArrayHasKey('nombre_rol', $resultado);
        $this->assertArrayHasKey('contrasenia', $resultado);
    }

    public function testValidarUsuarioCorreoIncorrecto(){
        $this->usuario->set_correo("correo_incorrecto");

        $resultado = $this->usuario->realizar_consulta('validar_usuario',true);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    public function testValidarUsuarioCorreoInexistente(){
        $this->usuario->set_correo("correo_inexistenete@gmail.com");

        $resultado = $this->usuario->realizar_consulta('validar_usuario',true);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    public function testValidarUsuarioDatosVacios(){
        $this->usuario->set_correo('');

        $resultado = $this->usuario->realizar_consulta('validar_usuario',true);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Usuario no encontrado", $resultado["mensaje"]);
    }

    //Metodo verificar_correo
    public function testBuscarcorreoExistente(){
        $this->usuario->set_correo("administrador@gmail.com");

        $resultado = $this->usuario->realizar_consulta('verificar_correo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('correo', $resultado["busqueda"]);
    }

    public function testBuscarcorreoInexistente(){
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
        $this->usuario->set_token("token de prueba");
        $this->usuario->set_duracion_token("2023-08-24 16:43:42");
        
        $resultado = $this->usuario->realizar_consulta('validar_token');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(8, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_usuario', $resultado);
        $this->assertArrayHasKey('nombre', $resultado);
        $this->assertArrayHasKey('apellido', $resultado);
        $this->assertArrayHasKey('correo', $resultado);
        $this->assertArrayHasKey('contrasenia', $resultado);
        $this->assertArrayHasKey('rol_id', $resultado);
        $this->assertArrayHasKey('token', $resultado);
        $this->assertArrayHasKey('duracion_token', $resultado);
    }

    public function testValidarTokenIncorrecto(){
        $this->usuario->set_token("token_incorrecto");
        $this->usuario->set_duracion_token("2025-08-24 16:43:41");

        $resultado = $this->usuario->realizar_consulta('validar_token');
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Token no encontrado", $resultado["mensaje"]);
    }

    public function testValidarDuracionTokenIncorrecto(){
        $this->usuario->set_token("token_incorrecto");
        $this->usuario->set_duracion_token("2024-08-24 16:43:41");

        $resultado = $this->usuario->realizar_consulta('validar_token');
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Token no encontrado", $resultado["mensaje"]);
    }

    //Metodo consultar
    public function testConsultarUsuario(){
        $resultado = $this->usuario->realizar_consulta('consultar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertCount(6, $resultado[0]);

        // Revisamos la estructura de un elemento        
        $this->assertArrayHasKey('id_usuario', $resultado[0]);
        $this->assertArrayHasKey('apellido', $resultado[0]);
        $this->assertArrayHasKey('nombre_usuario', $resultado[0]);
        $this->assertArrayHasKey('correo', $resultado[0]);
        $this->assertArrayHasKey('rol_id', $resultado[0]);
        $this->assertArrayHasKey('nombre_rol', $resultado[0]);
    }

    //Metodo consultar_usuario
    public function testConsultarUsuarioUnicoIdCorrecto(){
        $this->usuario->set_id_usuario(1);

        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(6, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_usuario', $resultado);
        $this->assertArrayHasKey('apellido', $resultado);
        $this->assertArrayHasKey('nombre_usuario', $resultado);
        $this->assertArrayHasKey('correo', $resultado);
        $this->assertArrayHasKey('nombre_rol', $resultado);
        $this->assertArrayHasKey('contrasenia', $resultado);
    }

    public function testConsultarUsuarioUnicoIdIncorrecto(){
        $this->usuario->set_id_usuario(123122);

        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarUsuarioUnicoDatosVacios(){
        $this->usuario->set_id_usuario('');

        $resultado = $this->usuario->realizar_consulta('consultar_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo consultar_perfil_usuario
    public function testConsultarPerfilUsuarioUnicoIdCorrecto(){
        $this->usuario->set_id_usuario(1);

        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('nombre_usuario', $resultado);
        $this->assertArrayHasKey('apellido', $resultado);
        $this->assertArrayHasKey('correo', $resultado);
        $this->assertArrayHasKey('nombre_rol', $resultado);
        $this->assertArrayHasKey('ultima_vez', $resultado);
    }

    public function testConsultarPerfilUsuarioUnicoIdIncorrecto(){
        $this->usuario->set_id_usuario(123122);

        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarPerfilUsuarioUnicoDatosVacios(){
        $this->usuario->set_id_usuario('');

        $resultado = $this->usuario->realizar_consulta('consultar_perfil_usuario');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo registrar
    public function testRegistrarUsuarioDatosCorrectos(){
        $this->usuario->set_apellido("apellido de prueba");
        $this->usuario->set_nombre("nombre de prueba");
        $this->usuario->set_correo("pruebaregistrada@gmail.com");
        $this->usuario->set_contra("contraseñadeprueba");
        $this->usuario->set_rol_id(1);

        $resultado = $this->usuario->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testRegistrarUsuarioDatosIncorrecto(){
        $this->usuario->set_apellido("123123");
        $this->usuario->set_nombre("n123we");
        $this->usuario->set_correo("correo erronea");
        $this->usuario->set_contra("contraseña de prueba");
        $this->usuario->set_rol_id(1);

        $resultado = $this->usuario->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'apellido' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarUsuarioIDRolErroneo(){
        $this->usuario->set_apellido("apellido de prueba");
        $this->usuario->set_nombre("nombre de prueba");
        $this->usuario->set_correo("prueba@gmail.com");
        $this->usuario->set_contra("contraseñadeprueba");
        $this->usuario->set_rol_id(123123);

        $resultado = $this->usuario->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testRegistrarUsuarioUnicoDatosVacios(){
        $this->usuario->set_apellido("");
        $this->usuario->set_nombre("");
        $this->usuario->set_correo("");
        $this->usuario->set_contra("");
        $this->usuario->set_rol_id('');     

        $resultado = $this->usuario->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo registrar_token
    public function testRegistrarTokenDatosCorrectos(){
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

    //Metodo editar_usuario
    public function testEditarUsuarioDatosCorrectos(){
        $this->usuario->set_id_usuario(53); // Id existente
        $this->usuario->set_apellido("apellido editado");
        $this->usuario->set_nombre("nombre editado");
        $this->usuario->set_correo("pruebaEditada@gmail.com");
        $this->usuario->set_contra("contraseñadepruebaeditada");
        $this->usuario->set_rol_id(4);

        $resultado = $this->usuario->realizar_consulta('editar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEditarUsuarioDatosIncorrecto(){
        $this->usuario->set_id_usuario(53); // Id existente
        $this->usuario->set_apellido("12312aasdas");
        $this->usuario->set_nombre("123asdas");
        $this->usuario->set_correo("prueba incorecta");
        $this->usuario->set_contra("++`´ç´ç´ç");
        $this->usuario->set_rol_id(4);

        $resultado = $this->usuario->realizar_consulta('editar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'apellido' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarUsuarioIDIncorrecto(){
        $this->usuario->set_id_usuario(23423); 
        $this->usuario->set_apellido("apellido editado");
        $this->usuario->set_nombre("nombre editado");
        $this->usuario->set_correo("pruebaEditada@gmail.com");
        $this->usuario->set_contra("contraseña de prueba editada");
        $this->usuario->set_rol_id(4);

        $resultado = $this->usuario->realizar_consulta('editar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarUsuarioIDRolIncorrecto(){
        $this->usuario->set_id_usuario(53); // Id existente
        $this->usuario->set_apellido("apelllido");
        $this->usuario->set_nombre("nombre");
        $this->usuario->set_correo("prueba@gmai.com");
        $this->usuario->set_contra("contraseniaprueba");
        $this->usuario->set_rol_id(123123);

        $resultado = $this->usuario->realizar_consulta('editar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Rol seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarUsuarioUnicoDatosVacios(){
        $this->usuario->set_id_usuario(53); // Id existente
        $this->usuario->set_apellido("");
        $this->usuario->set_nombre("");
        $this->usuario->set_correo("");
        $this->usuario->set_contra("");
        $this->usuario->set_rol_id('');

        $resultado = $this->usuario->realizar_consulta('editar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar_perfil
    public function testEditarPerfilUsuarioDatosCorrectos(){
        $this->usuario->set_id_usuario(56); // Id existente
        $this->usuario->set_apellido("perfil editado");
        $this->usuario->set_nombre("perfil editado");
        $this->usuario->set_correo("perfilEditada@gmail.com");        

        $resultado = $this->usuario->realizar_consulta('editar_perfil');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEditarPerfilUsuarioDatosIncorrecto(){
        $this->usuario->set_id_usuario(56); // Id existente
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
        $this->usuario->set_id_usuario(53); // Id existente
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
        $this->usuario->set_id_usuario(52); // Id existente

        $resultado = $this->usuario->realizar_consulta('eliminar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEliminarUsuarioIDIncorrecto(){
        $this->usuario->set_id_usuario(12312312); // Id inexistente

        $resultado = $this->usuario->realizar_consulta('eliminar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarUsuarioDatosVacios(){
        $this->usuario->set_id_usuario('');

        $resultado = $this->usuario->realizar_consulta('eliminar_usuario',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Usuario requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo eliminar_token
    public function testEliminarTokenDatosCorrectos(){
        $this->usuario->set_token('borrarme'); 

        $resultado = $this->usuario->realizar_consulta('eliminar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertTrue($resultado["estatus"]);
    }

    public function testEliminarTokenDatosVacios(){
        $this->usuario->set_token('');

        $resultado = $this->usuario->realizar_consulta('eliminar_token');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El token requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo lastId
    public function testUltimoIdRegistrado(){
        $resultado = $this->usuario->realizar_consulta('lastId');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(1, $resultado);
    }

}

?>