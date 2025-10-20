<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/caja_chica_modelo.php";
require_once "modelo/movimientos_caja_modelo.php";
require_once "modelo/gastos_modelo.php";
require_once "modelo/detalles_gastos_modelo.php";
// vendor\bin\phpunit tests
class CajaChicaTest extends TestCase
{
    private $caja_chica;

    private $tipo_gasto_id = 10;
    private $id_caja_chica = 15;

    public function setUp(): void{
        $this->caja_chica = new Caja_chica();
    }

    public function tearDown(): void{
        unset($this->caja_chica);
    }

    public function testReponerSaldoCaja(){
        $movimientos_caja_obj = new Movimientos_caja();
        $gastos_obj = new Gastos();
        $detalles_gastos_obj = new Detalles_Gasto();        
        
        $gastos_obj->set_tipo("variable");
        $gastos_obj->set_descripcion_gasto("Reposición de Caja Chica");
        $gastos_obj->set_tipo_gasto_id($this->tipo_gasto_id);
        $gastos_obj->set_solicitud_id(null);
        $gastos_obj->set_proveedor_id(null);

        $resultado_registro_gasto = $gastos_obj->realizar_consulta("registrar");

        $this->assertIsArray($resultado_registro_gasto);
        $this->assertNotEmpty($resultado_registro_gasto);
        $this->assertCount(2, $resultado_registro_gasto);

        $this->assertTrue($resultado_registro_gasto["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_gasto["mensaje"]);

        $ultimo_gasto_id = $gastos_obj->realizar_consulta("lastId");

        $this->assertIsArray($ultimo_gasto_id);
        $this->assertNotEmpty($ultimo_gasto_id);
        $this->assertCount(2, $ultimo_gasto_id);

        $this->assertTrue($ultimo_gasto_id["estatus"]);
        $this->assertIsInt(intval($ultimo_gasto_id["mensaje"]));

        $detalles_gastos_obj->set_fecha(date("Y-m-d"));
        $detalles_gastos_obj->set_monto(50);
        $detalles_gastos_obj->set_metodo_pago("Efectivo");
        $detalles_gastos_obj->set_descripcion_detalle_gasto("Reposición de Caja Chica prueba");
        $detalles_gastos_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_registro_detalle = $detalles_gastos_obj->realizar_consulta('registrar');

        $this->assertIsArray($resultado_registro_detalle);
        $this->assertNotEmpty($resultado_registro_detalle);
        $this->assertCount(2, $resultado_registro_detalle);

        $this->assertTrue($resultado_registro_detalle["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_detalle["mensaje"]);

        $movimientos_caja_obj->set_monto(50);
        $movimientos_caja_obj->set_caja_chica_id($this->id_caja_chica);
        $movimientos_caja_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_reponer_caja = $movimientos_caja_obj->realizar_consulta('reponer_caja');

        $this->assertIsArray($resultado_reponer_caja);
        $this->assertNotEmpty($resultado_reponer_caja);
        $this->assertCount(2, $resultado_reponer_caja);

        $this->assertTrue($resultado_reponer_caja["estatus"]);
        $this->assertStringContainsString('OK', $resultado_reponer_caja["mensaje"]);
    }

    public function testReponerSaldoCajaMontoInvalido(){
        $movimientos_caja_obj = new Movimientos_caja();
        $gastos_obj = new Gastos();
        $detalles_gastos_obj = new Detalles_Gasto();        
        
        $gastos_obj->set_tipo("variable");
        $gastos_obj->set_descripcion_gasto("Reposición de Caja Chica");
        $gastos_obj->set_tipo_gasto_id($this->tipo_gasto_id);
        $gastos_obj->set_solicitud_id(null);
        $gastos_obj->set_proveedor_id(null);

        $resultado_registro_gasto = $gastos_obj->realizar_consulta("registrar");

        $this->assertIsArray($resultado_registro_gasto);
        $this->assertNotEmpty($resultado_registro_gasto);
        $this->assertCount(2, $resultado_registro_gasto);

        $this->assertTrue($resultado_registro_gasto["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_gasto["mensaje"]);

        $ultimo_gasto_id = $gastos_obj->realizar_consulta("lastId");

        $this->assertIsArray($ultimo_gasto_id);
        $this->assertNotEmpty($ultimo_gasto_id);
        $this->assertCount(2, $ultimo_gasto_id);

        $this->assertTrue($ultimo_gasto_id["estatus"]);
        $this->assertIsInt(intval($ultimo_gasto_id["mensaje"]));

        $detalles_gastos_obj->set_fecha(date("Y-m-d"));
        $detalles_gastos_obj->set_monto(50);
        $detalles_gastos_obj->set_metodo_pago("Efectivo");
        $detalles_gastos_obj->set_descripcion_detalle_gasto("Reposición de Caja Chica prueba");
        $detalles_gastos_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_registro_detalle = $detalles_gastos_obj->realizar_consulta('registrar');

        $this->assertIsArray($resultado_registro_detalle);
        $this->assertNotEmpty($resultado_registro_detalle);
        $this->assertCount(2, $resultado_registro_detalle);

        $this->assertTrue($resultado_registro_detalle["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_detalle["mensaje"]);

        $movimientos_caja_obj->set_monto("Monto invalido");
        $movimientos_caja_obj->set_caja_chica_id($this->id_caja_chica);
        $movimientos_caja_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_reponer_caja = $movimientos_caja_obj->realizar_consulta('reponer_caja');

        $this->assertIsArray($resultado_reponer_caja);
        $this->assertNotEmpty($resultado_reponer_caja);
        $this->assertCount(2, $resultado_reponer_caja);

        $this->assertFalse($resultado_reponer_caja["estatus"]);
        $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado_reponer_caja["mensaje"]);
    }

    public function testReponerSaldoCajaMontoVacio(){
        $movimientos_caja_obj = new Movimientos_caja();
        $gastos_obj = new Gastos();
        $detalles_gastos_obj = new Detalles_Gasto();        
        
        $gastos_obj->set_tipo("variable");
        $gastos_obj->set_descripcion_gasto("Reposición de Caja Chica");
        $gastos_obj->set_tipo_gasto_id($this->tipo_gasto_id);
        $gastos_obj->set_solicitud_id(null);
        $gastos_obj->set_proveedor_id(null);

        $resultado_registro_gasto = $gastos_obj->realizar_consulta("registrar");

        $this->assertIsArray($resultado_registro_gasto);
        $this->assertNotEmpty($resultado_registro_gasto);
        $this->assertCount(2, $resultado_registro_gasto);

        $this->assertTrue($resultado_registro_gasto["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_gasto["mensaje"]);

        $ultimo_gasto_id = $gastos_obj->realizar_consulta("lastId");

        $this->assertIsArray($ultimo_gasto_id);
        $this->assertNotEmpty($ultimo_gasto_id);
        $this->assertCount(2, $ultimo_gasto_id);

        $this->assertTrue($ultimo_gasto_id["estatus"]);
        $this->assertIsInt(intval($ultimo_gasto_id["mensaje"]));

        $detalles_gastos_obj->set_fecha(date("Y-m-d"));
        $detalles_gastos_obj->set_monto(50);
        $detalles_gastos_obj->set_metodo_pago("Efectivo");
        $detalles_gastos_obj->set_descripcion_detalle_gasto("Reposición de Caja Chica prueba");
        $detalles_gastos_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_registro_detalle = $detalles_gastos_obj->realizar_consulta('registrar');

        $this->assertIsArray($resultado_registro_detalle);
        $this->assertNotEmpty($resultado_registro_detalle);
        $this->assertCount(2, $resultado_registro_detalle);

        $this->assertTrue($resultado_registro_detalle["estatus"]);
        $this->assertStringContainsString('OK', $resultado_registro_detalle["mensaje"]);

        $movimientos_caja_obj->set_monto("");
        $movimientos_caja_obj->set_caja_chica_id($this->id_caja_chica);
        $movimientos_caja_obj->set_gasto_id($ultimo_gasto_id["mensaje"]);

        $resultado_reponer_caja = $movimientos_caja_obj->realizar_consulta('reponer_caja');

        $this->assertIsArray($resultado_reponer_caja);
        $this->assertNotEmpty($resultado_reponer_caja);
        $this->assertCount(2, $resultado_reponer_caja);

        $this->assertFalse($resultado_reponer_caja["estatus"]);
        $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado_reponer_caja["mensaje"]);
    }
}
?>