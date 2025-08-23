<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/notificaciones_modelo.php";
// vendor\bin\phpunit tests
class NotificacionesTest extends TestCase
{
    private $notificaciones;

    public function setUp(): void{
        $this->notificaciones = new Notificaciones();
    }

    public function tearDown(): void{
        unset($this->notificaciones);
    }

    //Metodo consultar
    public function testConsultarNotificaciones(){
        $resultado = $this->notificaciones->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('nombre', $resultado[0]);
        $this->assertArrayHasKey('titulo', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);
        $this->assertArrayHasKey('fecha', $resultado[0]);
        $this->assertArrayHasKey('activo', $resultado[0]);
    }

    //Metodo consultar_notificaciones_usuario
    public function testConsultarNotificacionesUsuarioIdCorrecto(){
        $this->notificaciones->set_usuario_id(1);

        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado[0]);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_notificacion', $resultado[0]);
        $this->assertArrayHasKey('titulo', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);        
    }

    public function testConsultarNotificacionesUsuarioIdIncorrecto(){
        $this->notificaciones->set_usuario_id(123122);

        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarNotificacionesUsuarioDatosVacios(){
        $this->notificaciones->set_usuario_id('');

        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo agregar_notificacion
    public function testAgregarNotificacionDatosCorrectos(){
        $this->notificaciones->set_titulo("Notificacion de prueba");
        $this->notificaciones->set_descripcion("preuba unitaria mensualidad registrada");
        $this->notificaciones->set_fecha("2021-01-01");
        $this->notificaciones->set_usuario_id(1);

        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testAgregarNotificacionDatosIncorrecto(){
        $this->notificaciones->set_titulo("prueba erronea");
        $this->notificaciones->set_descripcion("prueba erronea");
        $this->notificaciones->set_fecha("fecha incorrecta");
        $this->notificaciones->set_usuario_id(1);

        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testAgregarNotificacionIDUsuarioIncorrecto(){
        $this->notificaciones->set_titulo("prueba erronea");
        $this->notificaciones->set_descripcion("prueba erronea");
        $this->notificaciones->set_fecha("2021-01-01");
        $this->notificaciones->set_usuario_id(123123123);

        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testAgregarNotificacionDatosVacios(){
        $this->notificaciones->set_titulo("");
        $this->notificaciones->set_descripcion("");
        $this->notificaciones->set_fecha("");
        $this->notificaciones->set_usuario_id("");

        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo notificar_pago
    public function testNotificarPagoDatosCorrectos(){
        $this->notificaciones->set_titulo("Notificacion de prueba");
        $this->notificaciones->set_descripcion("prueba unitaria pagos de usuario");
        $this->notificaciones->set_fecha("2021-01-01");

        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testNotificarPagoDatosIncorrecto(){
        $this->notificaciones->set_titulo("titulo erroneo: 123123`p+´´ç");
        $this->notificaciones->set_descripcion("prueba erronea");
        $this->notificaciones->set_fecha("2021-01-01");
        $this->notificaciones->set_usuario_id(1);

        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'titulo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testNotificarPagoDatosVacios(){
        $this->notificaciones->set_titulo("");
        $this->notificaciones->set_descripcion("");
        $this->notificaciones->set_fecha("");
        $this->notificaciones->set_usuario_id("");

        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo marcar_como_activo
    public function testMarcarComoActivoDatosCorrectos(){
        $this->notificaciones->set_id_notificacion(829);

        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testMarcarComoActivoIDIncorrecto(){
        $this->notificaciones->set_id_notificacion(8212319);

        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id de la notificacion seleccionada no existe", $resultado["mensaje"]);
    }

    public function testMarcarComoActivoIDVacios(){
        $this->notificaciones->set_id_notificacion('');        

        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id de la notificacion se envio vacío", $resultado["mensaje"]);
    }
}

?>