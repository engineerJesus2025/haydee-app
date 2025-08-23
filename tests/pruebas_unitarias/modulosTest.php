<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/modulos_modelo.php";

class ModulosTest extends TestCase
{
    private $modulos;

    public function setUp(): void{
        $this->modulos = new Modulos();
    }

    public function tearDown(): void{
        unset($this->modulos);
    }

    //Metodo consultar
    public function testConsultarModulos(){
        $resultado = $this->modulos->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);
    }
}

?>