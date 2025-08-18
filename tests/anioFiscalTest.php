<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/anio_fiscal_modelo.php";

class AnioFiscalTest extends TestCase
{
    private $anio_fiscal;

    public function setUp(): void{
        $this->anio_fiscal = new Anio_fiscal();
    }

    public function tearDown(): void{
        unset($this->anio_fiscal);
    }

    public function testConsultarAnioFiscal(){
        $resultado = $this->anio_fiscal->consultar();
        
        $this->assertIsArray($resultado);

        $this->assertIsBool($resultado["resultado"]);
        $this->assertIsArray($resultado["datos"]);
    }

    // public function testRegistraranio_fiscal(){
    //     $this->anio_fiscal->set_nombre("anio_fiscal Test");
    //     $this->anio_fiscal->set_departamento_id(3);

    //     $resultado = $this->anio_fiscal->registrar(true);
    //     $this->assertNotNull($resultado);
    //     $this->assertIsBool($resultado);
    // }

    // public function testEditaranio_fiscal(){
    //     $this->anio_fiscal->set_nombre("anio_fiscal Test Editar");
    //     $this->anio_fiscal->set_id_anio_fiscal(1);
    //     $this->anio_fiscal->set_departamento_id(1);

    //     $resultado = $this->anio_fiscal->editar_anio_fiscal(true);
    //     $this->assertNotNull($resultado);
    //     $this->assertIsBool($resultado);
    // }

    // public function testEliminaranio_fiscal(){
    //     $this->anio_fiscal->set_id_anio_fiscal(4);
    //     $resultado = $this->anio_fiscal->eliminar_anio_fiscal(true);
    //     $this->assertNotNull($resultado);
    //     $this->assertIsBool($resultado);
    // }

    // public function testConsultarDepartamento(){
    //     $resultado = $this->anio_fiscal->consultar_departamento();
    //     $this->assertNotNull($resultado);
    //     $this->assertIsArray($resultado);
    // }

    // public function testVerificarNombre(){
    //     $this->anio_fiscal->set_nombre("Joaquin");

    //     $resultado = $this->anio_fiscal->verificar_nombre();
        
    //     $this->assertNotNull($resultado);
    //     $this->assertIsBool($resultado);
    // }

}

?>