<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/detalles_presupuesto_modelo.php";
// vendor\bin\phpunit tests
class DetallesPresupuestoTest extends TestCase
{
    private $detalles_presupuesto;

    private $id_presupuesto = 70;
    private $id_presupuesto_borrar = 70;

    public function setUp(): void{
        $this->detalles_presupuesto = new Detalles_presupuesto();
    }

    public function tearDown(): void{
        unset($this->detalles_presupuesto);
    }

    //Metodo consultar
    public function testConsultarDetallesPresupuesto(){
        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('monto_detalle', $resultado[0]);
        $this->assertArrayHasKey('nombre_detalle', $resultado[0]);
        $this->assertArrayHasKey('presupuesto_id', $resultado[0]);
        $this->assertArrayHasKey('tipo_gasto_id', $resultado[0]);
    }

    //Metodo consultar_detalles_presupuestos
    public function testConsultarDetallesPresupuestoIdCorrecto(){
        $this->detalles_presupuesto->set_presupuesto_id($this->id_presupuesto);

        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar_detalles_presupuestos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado[0]);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('monto_detalle', $resultado[0]);
        $this->assertArrayHasKey('nombre_detalle', $resultado[0]);
        $this->assertArrayHasKey('presupuesto_id', $resultado[0]);
        $this->assertArrayHasKey('tipo_gasto_id', $resultado[0]);
    }

    public function testConsultarDetallesPresupuestoIdIncorrecto(){
        $this->detalles_presupuesto->set_presupuesto_id(123122);

        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar_detalles_presupuestos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarDetallesPresupuestoDatosVacios(){
        $this->detalles_presupuesto->set_presupuesto_id('');

        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar_detalles_presupuestos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar
    public function testRegistrarDetallesPresupuestoDatosCorrectos(){
        $this->detalles_presupuesto->set_monto_detalle("123");
        $this->detalles_presupuesto->set_nombre_detalle("detalle prueba");
        $this->detalles_presupuesto->set_presupuesto_id($this->id_presupuesto);
        $this->detalles_presupuesto->set_tipo_gasto_id(1);
        $resultado = $this->detalles_presupuesto->realizar_consulta('registrar');
 
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarDetallesPresupuestoDatosIncorrecto(){
        $this->detalles_presupuesto->set_monto_detalle("monto incorrecto");
        $this->detalles_presupuesto->set_nombre_detalle("2detalle prueba");
        $this->detalles_presupuesto->set_presupuesto_id($this->id_presupuesto);
        $this->detalles_presupuesto->set_tipo_gasto_id(1);

        $resultado = $this->detalles_presupuesto->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'monto' de uno de los presupuestos no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarDetallesPresupuestoIDIncorrecto(){
        $this->detalles_presupuesto->set_monto_detalle("121");
        $this->detalles_presupuesto->set_nombre_detalle("detalle prueba");
        $this->detalles_presupuesto->set_presupuesto_id(1231231);//presupuesto inexistente
        $this->detalles_presupuesto->set_tipo_gasto_id(1);

        $resultado = $this->detalles_presupuesto->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del presupuesto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testRegistrarDetallesPresupuestoDatosVacios(){
        $this->detalles_presupuesto->set_monto_detalle('');
        $this->detalles_presupuesto->set_nombre_detalle("");
        $this->detalles_presupuesto->set_presupuesto_id('');
        $this->detalles_presupuesto->set_tipo_gasto_id('');

        $resultado = $this->detalles_presupuesto->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarDetallesPresupuestoDatosCorrectos(){
        $this->detalles_presupuesto->set_presupuesto_id($this->id_presupuesto_borrar); // Id existente

        $resultado = $this->detalles_presupuesto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarDetallesPresupuestoIDIncorrecto(){
        $this->detalles_presupuesto->set_presupuesto_id(12312312); // Id inexistente

        $resultado = $this->detalles_presupuesto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del presupuesto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarDetallesPresupuestoUnicoDatosVacios(){
        $this->detalles_presupuesto->set_presupuesto_id('');

        $resultado = $this->detalles_presupuesto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del presupuesto se envio vacío", $resultado["mensaje"]);
    }
}

?>