<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/bitacora_modelo.php";

class BitacoraTest extends TestCase
{
    private $bitacora;

    public function setUp(): void{
        $this->bitacora = new Bitacora();
    }

    public function tearDown(): void{
        unset($this->bitacora);
    }

    //Metodo consultar
    public function testConsultarBitacora(){
        $resultado = $this->bitacora->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('fecha_hora', $resultado[0]);
        $this->assertArrayHasKey('accion', $resultado[0]);
        $this->assertArrayHasKey('registro_alterado', $resultado[0]);
        $this->assertArrayHasKey('nombre_usuario', $resultado[0]);
        $this->assertArrayHasKey('nombre_modulo', $resultado[0]);
        $this->assertArrayHasKey('nombre_rol', $resultado[0]);
    }

}

?>