<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/caja_chica_modelo.php";
// vendor\bin\phpunit tests
class CajaChicaTest extends TestCase
{
    private $caja_chica;

    private $id_caja = 23;

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
        $this->assertArrayHasKey('id_caja_chica', $resultado[0]);
        $this->assertArrayHasKey('fondo_fijo', $resultado[0]);
        $this->assertArrayHasKey('saldo_actual', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);
        $this->assertArrayHasKey('fecha_creacion', $resultado[0]);
        $this->assertArrayHasKey('anio_fiscal_id', $resultado[0]);
    }

    //Metodo editar_descripcion
    public function testEditarObservacionDatosCorrectos(){
        $this->caja_chica->set_id_caja_chica($this->id_caja); // Id existente        
        $this->caja_chica->set_descripcion("Ejecutada prueba de edicion");

        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarObservacionIDIncorrecto(){
        $this->caja_chica->set_id_caja_chica(12312312); // Id inexistente
        $this->caja_chica->set_descripcion("Prueba erronea");

        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La Caja seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarObservacionDatosVacios(){
        $this->caja_chica->set_id_caja_chica($this->id_caja);
        $this->caja_chica->set_descripcion("");

        $resultado = $this->caja_chica->realizar_consulta('editar_descripcion');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

}

?>