<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/presupuesto_modelo.php";
// vendor\bin\phpunit tests
class PresupuestoTest extends TestCase
{
    private $presupuesto;

    public function setUp(): void{
        $this->presupuesto = new Presupuesto();
    }

    public function tearDown(): void{
        unset($this->presupuesto);
    }

    //Metodo consultar
    public function testConsultarPresupuestos(){
        $resultado = $this->presupuesto->realizar_consulta('consultar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('mes_fecha', $resultado[0]);
        $this->assertArrayHasKey('anio_fecha', $resultado[0]);
        $this->assertArrayHasKey('monto_estimado', $resultado[0]);
        $this->assertArrayHasKey('cuota_reserva', $resultado[0]);
        $this->assertArrayHasKey('observacion', $resultado[0]);
    }

    //Metodo consultar_meses_faltantes
    public function testConsultarMesesFaltantes(){
        $resultado = $this->presupuesto->realizar_consulta('consultar_meses_faltantes');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('anio_faltante', $resultado[0]);
        $this->assertArrayHasKey('mes_faltante', $resultado[0]);        
    }

    //Metodo consultar_presupuesto
    public function testConsultarPresupuestoUnicoIdCorrecto(){
        $this->presupuesto->set_id_presupuesto(39);

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(6, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_presupuesto', $resultado);
        $this->assertArrayHasKey('mes_fecha', $resultado);
        $this->assertArrayHasKey('anio_fecha', $resultado);
        $this->assertArrayHasKey('cuota_reserva', $resultado);
        $this->assertArrayHasKey('observacion', $resultado);
        $this->assertArrayHasKey('fecha', $resultado);        
    }

    public function testConsultarPresupuestoUnicoIdIncorrecto(){
        $this->presupuesto->set_id_presupuesto(123122);

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarPresupuestoUnicoDatosVacios(){
        $this->presupuesto->set_id_presupuesto('');

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuesto');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo consultar_presupuestos_mensualidades
    public function testConsultarPresupuestosMensualidadesFechaCorrecta(){
        $this->presupuesto->set_fecha("2025-02-01");

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('nombre', $resultado[0]);
        $this->assertArrayHasKey('monto', $resultado[0]);
        $this->assertArrayHasKey('id_presupuestos_asociados', $resultado[0]);
        $this->assertArrayHasKey('id_presupuesto', $resultado[0]);        
    }

    public function testConsultarPresupuestosMensualidadesFechaIncorrecto(){
        $this->presupuesto->set_fecha("fecha_incorrecta");

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testConsultarPresupuestosMensualidadesDatosVacios(){
        $this->presupuesto->set_fecha('');

        $resultado = $this->presupuesto->realizar_consulta('consultar_presupuestos_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo registrar
    public function testRegistrarPresupuestoDatosCorrectos(){
        $this->presupuesto->set_fecha("2025-04-01");
        $this->presupuesto->set_cuota_reserva("1000");
        $this->presupuesto->set_observacion("Prueba unitaria");

        $resultado = $this->presupuesto->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarPresupuestoDatosIncorrecto(){
        $this->presupuesto->set_fecha("fecha_incorrecta");
        $this->presupuesto->set_cuota_reserva("1111");
        $this->presupuesto->set_observacion("Prueba unitaria");

        $resultado = $this->presupuesto->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarPresupuestoUnicoDatosVacios(){
        $this->presupuesto->set_fecha("");
        $this->presupuesto->set_cuota_reserva('');
        $this->presupuesto->set_observacion('');

        $resultado = $this->presupuesto->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarPresupuestoDatosCorrectos(){
        $this->presupuesto->set_id_presupuesto(39); // Id existente
        $this->presupuesto->set_fecha("2025-02-01");
        $this->presupuesto->set_cuota_reserva("150");
        $this->presupuesto->set_observacion("Prueba unitaria editada");

        $resultado = $this->presupuesto->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarPresupuestoDatosIncorrecto(){
        $this->presupuesto->set_id_presupuesto(39); // Id existente
        $this->presupuesto->set_fecha("fecha_incoreccta");
        $this->presupuesto->set_cuota_reserva("1111");
        $this->presupuesto->set_observacion("Prueba unitaria erronea");

        $resultado = $this->presupuesto->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarPresupuestoIDIncorrecto(){
        $this->presupuesto->set_id_presupuesto(12312312); // Id inexistente
        $this->presupuesto->set_fecha("2020-01-01");
        $this->presupuesto->set_cuota_reserva("1111");
        $this->presupuesto->set_observacion("Prueba unitaria");

        $resultado = $this->presupuesto->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El presupuesto mensual seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarPresupuestoUnicoDatosVacios(){
        $this->presupuesto->set_id_presupuesto(39);
        $this->presupuesto->set_fecha("");
        $this->presupuesto->set_cuota_reserva('');
        $this->presupuesto->set_observacion("");

        $resultado = $this->presupuesto->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarPresupuestoDatosCorrectos(){
        $this->presupuesto->set_id_presupuesto(44); // Id existente

        $resultado = $this->presupuesto->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarPresupuestoIDIncorrecto(){
        $this->presupuesto->set_id_presupuesto(12312312); // Id inexistente

        $resultado = $this->presupuesto->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El presupuesto mensual seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarPresupuestoUnicoDatosVacios(){
        $this->presupuesto->set_id_presupuesto('');

        $resultado = $this->presupuesto->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Presupuesto requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo lastId
    public function testUltimoIdRegistrado(){
        $resultado = $this->presupuesto->realizar_consulta('lastId');
        
        $this->assertIsInt($resultado);
        $this->assertGreaterThan(0,$resultado);
    }
}

?>