<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/caja_chica_modelo.php";
// vendor\bin\phpunit tests
class CajaChicaTest extends TestCase
{
    private $caja_chica;

    public function setUp(): void{
        $this->caja_chica = new Caja_chica();
    }

    public function tearDown(): void{
        unset($this->caja_chica);
    }

    //Metodo consultar
    public function testConsultarCajasChicas(){
        $resultado = $this->caja_chica->realizar_consulta('consultar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('fecha_apertura', $resultado[0]);
        $this->assertArrayHasKey('id_caja_chica', $resultado[0]);
        $this->assertArrayHasKey('saldo_actual', $resultado[0]);
        $this->assertArrayHasKey('monto_inicial', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('observaciones', $resultado[0]);
        $this->assertArrayHasKey('anio_fiscal_id', $resultado[0]);
    }

    //Metodo buscar_mes
    public function testBuscarMesIdCorrecto(){
        $this->caja_chica->set_id_caja_chica(18);

        $resultado = $this->caja_chica->realizar_consulta('buscar_mes');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('movimiento', $resultado[0]);
        $this->assertArrayHasKey('fecha', $resultado[0]);
        $this->assertArrayHasKey('monto', $resultado[0]);
        $this->assertArrayHasKey('remitente', $resultado[0]);        
    }

    public function testBuscarMesIdIncorrecto(){
        $this->caja_chica->set_id_caja_chica(123122);

        $resultado = $this->caja_chica->realizar_consulta('buscar_mes');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function testBuscarMesDatosVacios(){
        $this->caja_chica->set_id_caja_chica('');

        $resultado = $this->caja_chica->realizar_consulta('buscar_mes');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    //Metodo editar_observacion
    public function testEditarObservacionDatosCorrectos(){
        $this->caja_chica->set_id_caja_chica(15); // Id existente        
        $this->caja_chica->set_observaciones("Ejecutada prueba de edicion");

        $resultado = $this->caja_chica->realizar_consulta('editar_observacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarObservacionIDIncorrecto(){
        $this->caja_chica->set_id_caja_chica(12312312); // Id inexistente
        $this->caja_chica->set_observaciones("Prueba erronea");

        $resultado = $this->caja_chica->realizar_consulta('editar_observacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La Caja seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarObservacionDatosVacios(){
        $this->caja_chica->set_id_caja_chica(15);
        $this->caja_chica->set_observaciones("");

        $resultado = $this->caja_chica->realizar_consulta('editar_observacion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    // //Metodo verificar_caja_mes
    public function testVerificarCajaMes(){
        $resultado = $this->caja_chica->realizar_consulta('verificar_caja_mes');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }
}

?>