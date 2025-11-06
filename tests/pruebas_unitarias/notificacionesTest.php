<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/notificaciones_modelo.php";
// vendor\bin\phpunit tests
class NotificacionesTest extends TestCase
{
    private $notificaciones;
    private $mock_notificaciones; // Mock

    public function setUp(): void{
        // Crear el mock
        $this->mock_notificaciones = $this->createMock(Notificaciones::class);
        $this->notificaciones = $this->mock_notificaciones;
    }

    public function tearDown(): void{
        unset($this->notificaciones);
        unset($this->mock_notificaciones);
    }

    //Metodo consultar
    public function testConsultarNotificaciones(){
        $datos_simulados = [
            [
                'nombre' => 'Admin',
                'titulo' => 'Nueva Mensualidad',
                'descripcion' => 'Se registró la mensualidad de...',
                'fecha' => '2025-01-01',
                'activo' => 1
            ]
        ];

        $this->mock_notificaciones->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->notificaciones->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        
        $this->assertArrayHasKey('nombre', $resultado[0]);
        $this->assertArrayHasKey('activo', $resultado[0]);
    }

    //Metodo consultar_notificaciones_usuario
    public function testConsultarNotificacionesUsuarioIdCorrecto(){
        $datos_simulados = [
            [
                'id_notificacion' => 1,
                'titulo' => 'Nuevo Pago',
                'descripcion' => 'Se ha registrado un nuevo pago'
            ]
        ];

        $this->mock_notificaciones->method('set_usuario_id')->with(1);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('consultar_notificaciones_usuario')
            ->willReturn($datos_simulados);

        $this->notificaciones->set_usuario_id(1);
        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado[0]);
        $this->assertArrayHasKey('id_notificacion', $resultado[0]);
    }

    public function testConsultarNotificacionesUsuarioIdIncorrecto(){
        $this->mock_notificaciones->method('set_usuario_id')->with(123122);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('consultar_notificaciones_usuario')
            ->willReturn([]); // Devuelve vacío

        $this->notificaciones->set_usuario_id(123122);
        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarNotificacionesUsuarioDatosVacios(){
        $this->mock_notificaciones->method('set_usuario_id')->with('');
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('consultar_notificaciones_usuario')
            ->willReturn([]);

        $this->notificaciones->set_usuario_id('');
        $resultado = $this->notificaciones->realizar_consulta('consultar_notificaciones_usuario');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo agregar_notificacion
    public function testAgregarNotificacionDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Notificación agregada"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('agregar_notificacion')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_titulo("Notificacion de prueba");
        $this->notificaciones->set_descripcion("preuba unitaria mensualidad registrada");
        $this->notificaciones->set_fecha("2021-01-01");
        $this->notificaciones->set_usuario_id(53);
        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testAgregarNotificacionDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'fecha' no posee un valor valido"
        ];

        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('agregar_notificacion')
            ->willReturn($resultado_esperado);
        
        $this->notificaciones->set_fecha("fecha incorrecta");
        // ...
        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testAgregarNotificacionIDUsuarioIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del usuario seleccionado no existe"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('agregar_notificacion')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_usuario_id(123123123);
        // ...
        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testAgregarNotificacionDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "Uno o varios de los campos requeridos estan vacios"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('agregar_notificacion')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_titulo("");
        // ...
        $resultado = $this->notificaciones->realizar_consulta('agregar_notificacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo notificar_pago
    public function testNotificarPagoDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Notificación de pago registrada"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('notificar_pago')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_titulo("Notificacion de prueba");
        // ...
        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testNotificarPagoDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'titulo' no posee un valor valido"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('notificar_pago')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_titulo("titulo erroneo: 123123`p+´´ç");
        // ...
        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'titulo' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testNotificarPagoDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "Uno o varios de los campos requeridos estan vacios"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('notificar_pago')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_titulo("");
        // ...
        $resultado = $this->notificaciones->realizar_consulta('notificar_pago');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo marcar_como_activo
    public function testMarcarComoActivoDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Marcado como leído"
        ];
        
        $this->mock_notificaciones->method('set_id_notificacion')->with(829);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_como_activo')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_id_notificacion(829);
        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testMarcarComoActivoIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id de la notificacion seleccionada no existe"
        ];
        
        $this->mock_notificaciones->method('set_id_notificacion')->with(8212319);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_como_activo')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_id_notificacion(8212319);
        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id de la notificacion seleccionada no existe", $resultado["mensaje"]);
    }

    public function testMarcarComoActivoIDVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id de la notificacion se envio vacío"
        ];
        
        $this->mock_notificaciones->method('set_id_notificacion')->with('');
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_como_activo')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_id_notificacion('');        
        $resultado = $this->notificaciones->realizar_consulta('marcar_como_activo');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id de la notificacion se envio vacío", $resultado["mensaje"]);
    }

    //Metodo marcar_todas_leidas
    public function testMarcarTodasDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Todas marcadas como leídas"
        ];
        
        $this->mock_notificaciones->method('set_usuario_id')->with(53);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_todas_leidas')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_usuario_id(53);
        $resultado = $this->notificaciones->realizar_consulta('marcar_todas_leidas');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testMarcarTodasIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del usuario seleccionado no existe"
        ];
        
        $this->mock_notificaciones->method('set_usuario_id')->with(8212319);
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_todas_leidas')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_usuario_id(8212319);
        $resultado = $this->notificaciones->realizar_consulta('marcar_todas_leidas');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del usuario seleccionado no existe", $resultado["mensaje"]);
    }

    public function testMarcarTodasIDVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del usuario se envio vacío"
        ];
        
        $this->mock_notificaciones->method('set_usuario_id')->with('');
        $this->mock_notificaciones->method('realizar_consulta')
            ->with('marcar_todas_leidas')
            ->willReturn($resultado_esperado);

        $this->notificaciones->set_usuario_id('');        
        $resultado = $this->notificaciones->realizar_consulta('marcar_todas_leidas');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del usuario se envio vacío", $resultado["mensaje"]);
    }
}
?>