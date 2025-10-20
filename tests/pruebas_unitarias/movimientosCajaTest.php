<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/movimientos_caja_modelo.php";
// vendor\bin\phpunit tests
class MovimientosCajaTest extends TestCase
{
    private $movimientos_caja;

    private $id_consultar = 21;
    private $id_editar = 22;
    private $id_caja = 23;
    private $id_eliminar = 23;
    private $id_gasto = 109;

    public function setUp(): void{
        $this->movimientos_caja = new Movimientos_caja();
    }

    public function tearDown(): void{
        unset($this->movimientos_caja);
    }

    // //Metodo consultar_movimientos_caja
    // public function testConsultarMovimientosCaja(){
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        
    //     $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimientos_caja');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(7, $resultado[0]);

    //     // Revisamos la estructura de un elemento
    //     $this->assertArrayHasKey('id_movimiento_caja', $resultado[0]);
    //     $this->assertArrayHasKey('concepto', $resultado[0]);
    //     $this->assertArrayHasKey('monto', $resultado[0]);
    //     $this->assertArrayHasKey('fecha', $resultado[0]);
    //     $this->assertArrayHasKey('estado', $resultado[0]);
    //     $this->assertArrayHasKey('caja_chica_id', $resultado[0]);
    //     $this->assertArrayHasKey('gasto_id', $resultado[0]);
    // }

    //Metodo consultar_movimiento
    // public function testConsultarMovimientoIdCorrecto(){
    //     $this->movimientos_caja->set_id_movimiento_caja($this->id_consultar);

    //     $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');

    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(7, $resultado);

    //     // Revisamos la estructura de un elemento
    //     $this->assertArrayHasKey('id_movimiento_caja', $resultado);
    //     $this->assertArrayHasKey('concepto', $resultado);
    //     $this->assertArrayHasKey('monto', $resultado);
    //     $this->assertArrayHasKey('fecha', $resultado);
    //     $this->assertArrayHasKey('estado', $resultado);
    //     $this->assertArrayHasKey('caja_chica_id', $resultado);
    //     $this->assertArrayHasKey('gasto_id', $resultado);
    // }

    // public function testConsultarMovimientoIdIncorrecto(){
    //     $this->movimientos_caja->set_id_movimiento_caja(123122);

    //     $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');

    //     $this->assertIsBool($resultado);
    //     $this->assertFalse($resultado);
    // }

    // public function testConsultarMovimientoDatosVacios(){
    //     $this->movimientos_caja->set_id_movimiento_caja('');

    //     $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');
        
    //     $this->assertIsBool($resultado);
    //     $this->assertFalse($resultado);
    // }

    //Metodo registrar
    // public function testRegistrarMovimientoCajaDatosCorrectos(){
    //     $this->movimientos_caja->set_concepto("prueba de registro");
    //     $this->movimientos_caja->set_monto('20');
    //     $this->movimientos_caja->set_fecha("2025-10-10");
    //     $this->movimientos_caja->set_estado("Pendiente por reposicion");
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id(null);
        
    //     $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(3, $resultado);
        
    //     $this->assertTrue($resultado["estatus"]);
    //     $this->assertStringContainsString('OK', $resultado["mensaje"]);
    //     $this->assertIsNumeric($resultado["lastId"]);
    // }

    // public function testRegistrarMovimientoCajaDatosIncorrecto(){
    //     $this->movimientos_caja->set_concepto("prueba de registro incorrecto");
    //     $this->movimientos_caja->set_monto('100');
    //     $this->movimientos_caja->set_fecha("fecha_incorrecta");
    //     $this->movimientos_caja->set_estado("Pendiente por reposicion");
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id(null);

    //     $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    // }

    // public function testRegistrarMovimientoCajaIDIncorrecto(){
    //     $this->movimientos_caja->set_concepto("prueba de registro incorrecto");
    //     $this->movimientos_caja->set_monto('100');
    //     $this->movimientos_caja->set_fecha("2025-10-10");
    //     $this->movimientos_caja->set_estado("Pendiente por reposicion");
    //     $this->movimientos_caja->set_caja_chica_id(24234); //Inexistente
    //     $this->movimientos_caja->set_gasto_id(null);

    //     $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("El id de la caja chica seleccionada no existe", $resultado["mensaje"]);
    // }

    // public function testRegistrarMovimientoCajaDatosVacios(){
    //     $this->movimientos_caja->set_concepto("");
    //     $this->movimientos_caja->set_monto('');
    //     $this->movimientos_caja->set_fecha("");
    //     $this->movimientos_caja->set_estado("");
    //     $this->movimientos_caja->set_caja_chica_id(''); //Inexistente
    //     $this->movimientos_caja->set_gasto_id('');

    //     $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    // }

    // //Metodo editar
    // public function testEditarMovimientoCajaDatosCorrectos(){
    //     $this->movimientos_caja->set_id_movimiento_caja($this->id_editar);
    //     $this->movimientos_caja->set_concepto("prueba de edicion");
    //     $this->movimientos_caja->set_monto('15');
    //     $this->movimientos_caja->set_fecha("2025-10-10");
    //     $this->movimientos_caja->set_estado("Reposado");
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id(116);

    //     $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertTrue($resultado["estatus"]);
    //     $this->assertStringContainsString('OK', $resultado["mensaje"]);
    // }

    // public function testEditarMovimientoCajaDatosIncorrecto(){
    //     $this->movimientos_caja->set_id_movimiento_caja($this->id_editar);
    //     $this->movimientos_caja->set_concepto("prueba de edicion");
    //     $this->movimientos_caja->set_monto('15');
    //     $this->movimientos_caja->set_fecha("fecha_incorrecta");
    //     $this->movimientos_caja->set_estado("Reposado");
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id(116);

    //     $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    // }

    // public function testEditarMovimientoCajaIDIncorrecto(){
    //     $this->movimientos_caja->set_id_movimiento_caja(123123); //id inexistente
    //     $this->movimientos_caja->set_concepto("prueba de edicion");
    //     $this->movimientos_caja->set_monto('15');
    //     $this->movimientos_caja->set_fecha("fecha_incorrecta");
    //     $this->movimientos_caja->set_estado("Reposado");
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id(116);

    //     $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("EL movimiento de la caja seleccionada no existe", $resultado["mensaje"]);
    // }

    // public function testEditarMovimientoCajaDatosVacios(){
    //     $this->movimientos_caja->set_id_movimiento_caja($this->id_editar); //id inexistente
    //     $this->movimientos_caja->set_concepto("");
    //     $this->movimientos_caja->set_monto('');
    //     $this->movimientos_caja->set_fecha("");
    //     $this->movimientos_caja->set_estado("");
    //     $this->movimientos_caja->set_caja_chica_id('');
    //     $this->movimientos_caja->set_gasto_id('');

    //     $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    // }

    // //Metodo eliminar
    // public function testEliminarMovimientosCajaDatosCorrectos(){
    //     $this->movimientos_caja->set_id_movimiento_caja($this->id_eliminar); //Existente

    //     $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertTrue($resultado["estatus"]);
    //     $this->assertStringContainsString('OK', $resultado["mensaje"]);
    // }

    // public function testEliminarMovimientosCajaIDIncorrecto(){
    //     $this->movimientos_caja->set_id_movimiento_caja(12312312); // Id inexistente

    //     $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("EL movimiento de la caja seleccionada no existe", $resultado["mensaje"]);
    // }

    // public function testEliminarMovimientosCajaUnicoDatosVacios(){
    //     $this->movimientos_caja->set_id_movimiento_caja('');

    //     $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("El ID requerido esta vacío", $resultado["mensaje"]);
    // }

    //Metodo reponer_caja
    // public function testReponerMovimientoCajaDatosCorrectos(){
    //     $this->movimientos_caja->set_monto('100');        
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id($this->id_gasto);

    //     $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');

    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);

    //     $this->assertTrue($resultado["estatus"]);
    //     $this->assertStringContainsString('OK', $resultado["mensaje"]);
    // }

    // public function testReponerMovimientoCajaMontoIncorrecto(){
    //     $this->movimientos_caja->set_monto('monto incorrecto');        
    //     $this->movimientos_caja->set_caja_chica_id($this->id_caja);
    //     $this->movimientos_caja->set_gasto_id($this->id_gasto);

    //     $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');
        
    //     $this->assertIsArray($resultado);
    //     $this->assertNotEmpty($resultado);
    //     $this->assertCount(2, $resultado);
        
    //     $this->assertFalse($resultado["estatus"]);
    //     $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado["mensaje"]);
    // }

    public function testReponerMovimientoCajaDatosVacios(){
        $this->movimientos_caja->set_monto('');        
        $this->movimientos_caja->set_caja_chica_id('');
        $this->movimientos_caja->set_gasto_id('');

        $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado["mensaje"]);
    }
}

?>