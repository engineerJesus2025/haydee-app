<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\PresupuestoMensualidad;

class PresupuestoMensualidadTest extends TestCase
{
    private $presupuesto_mensualidad;
    private $mock_presupuesto_mensualidad; // Mock

    // IDs para simular entradas
    private $id_mensualidad = 318;
    private $id_presupuesto = 70;
    private $id_detalle_presupuesto = 56;

    public function setUp(): void{
        // Crear el mock
        $this->mock_presupuesto_mensualidad = $this->createMock(PresupuestoMensualidad::class);
        $this->presupuesto_mensualidad = $this->mock_presupuesto_mensualidad;
    }

    public function tearDown(): void{
        unset($this->presupuesto_mensualidad);
        unset($this->mock_presupuesto_mensualidad);
    }

    //Metodo consultar_presupuestos_asociados
    public function testConsultarPresupuestosAsociadosIdMensualidadCorrecto(){
        $datos_simulados = [
            [
                'id_detalle_presupuesto' => $this->id_detalle_presupuesto,
                'id_mensualidad' => $this->id_mensualidad
            ]
        ];

        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('consultar_presupuestos_asociados')
            ->willReturn($datos_simulados);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado[0]);
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
    }

    public function testConsultarPresupuestosAsociadosIdMensualidadIncorrecto(){
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('consultar_presupuestos_asociados')
            ->willReturn([]); // Devuelve vacío

        $this->presupuesto_mensualidad->set_mensualidad_id(123122);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarPresupuestosAsociadosIdMensualidadVacio(){
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with('');
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('consultar_presupuestos_asociados')
            ->willReturn([]);

        $this->presupuesto_mensualidad->set_mensualidad_id('');
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar
    public function testRegistrarPresupuestoMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarPresupuestoMensualidadIDMensualidadIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "La mensualidad seleccionada no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id(123122);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La mensualidad seleccionada no existe", $resultado["mensaje"]);
    }

    public function testRegistrarPresupuestoMensualidadIDPresupuestoIncorrecto(){   
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El presupuesto seleccionado no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id(123122);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El presupuesto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testRegistrarPresupuestoMensualidadDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del Presupuesto requerido esta vacio"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with('');
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with('');
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id('');
        $this->presupuesto_mensualidad->set_presupuesto_id('');
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Presupuesto requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo registrar_presupuesto_mensualidad
    public function testRegistrarUnicoPresupuestoMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_detalle_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar_presupuesto_mensualidad')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_detalle_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar_presupuesto_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarUnicoPresupuestoMensualidadIDMensualidadIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "La mensualidad seleccionada no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_detalle_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar_presupuesto_mensualidad')
            ->willReturn($resultado_esperado);
        
        $this->presupuesto_mensualidad->set_mensualidad_id(123122);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_detalle_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar_presupuesto_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La mensualidad seleccionada no existe", $resultado["mensaje"]);
    }

    public function testRegistrarUnicoPresupuestoMensualidadIDPresupuestoIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El detalle de presupuesto seleccionado no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar_presupuesto_mensualidad')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id(123122);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar_presupuesto_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El detalle de presupuesto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testRegistrarUnicoPresupuestoMensualidadDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del Presupuesto requerido esta vacio"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with('');
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with('');
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('registrar_presupuesto_mensualidad')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id('');
        $this->presupuesto_mensualidad->set_presupuesto_id('');
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('registrar_presupuesto_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Presupuesto requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarPresupuestoMensualidadDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_detalle_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);
        
        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_detalle_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarPresupuestoMensualidadIDMensualidadIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "La mensualidad seleccionada no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with($this->id_detalle_presupuesto);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id(123122);
        $this->presupuesto_mensualidad->set_presupuesto_id($this->id_detalle_presupuesto);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La mensualidad seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarPresupuestoMensualidadIDPresupuestoIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El detalle de presupuesto seleccionado no existe"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with($this->id_mensualidad);
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with(123122);
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);
        $this->presupuesto_mensualidad->set_presupuesto_id(123122);
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El detalle de presupuesto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarPresupuestoMensualidadDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id del detalle del presupuesto requerido esta vacio"
        ];
        
        $this->mock_presupuesto_mensualidad->method('set_mensualidad_id')->with('');
        $this->mock_presupuesto_mensualidad->method('set_presupuesto_id')->with('');
        $this->mock_presupuesto_mensualidad->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->presupuesto_mensualidad->set_mensualidad_id('');
        $this->presupuesto_mensualidad->set_presupuesto_id('');
        $resultado = $this->presupuesto_mensualidad->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del detalle del presupuesto requerido esta vacio", $resultado["mensaje"]);
    }
}
