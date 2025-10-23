<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/presupuesto_mensualidad_modelo.php";

class PresupuestoMensualidadTest extends TestCase
{
    private $presupuesto_mensualidad;

    private $id_mensualidad = 318;
    private $id_presupuesto = 70;
    private $id_detalle_presupuesto = 56;

    public function setUp(): void{
        $this->presupuesto_mensualidad = new Presupuesto_mensualidad();
    }

    public function tearDown(): void{
        unset($this->presupuesto_mensualidad);
    }

    //Metodo consultar_presupuestos_asociados
    public function testConsultarPresupuestosAsociadosIdMensualidadCorrecto(){
        $this->presupuesto_mensualidad->set_mensualidad_id($this->id_mensualidad);

        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado[0]);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('id_mensualidad', $resultado[0]);
    }

    public function testConsultarPresupuestosAsociadosIdMensualidadIncorrecto(){
        $this->presupuesto_mensualidad->set_mensualidad_id(123122);

        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarPresupuestosAsociadosIdMensualidadVacio(){
        $this->presupuesto_mensualidad->set_mensualidad_id('');

        $resultado = $this->presupuesto_mensualidad->realizar_consulta('consultar_presupuestos_asociados');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar
    public function testRegistrarPresupuestoMensualidadDatosCorrectos(){
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

?>