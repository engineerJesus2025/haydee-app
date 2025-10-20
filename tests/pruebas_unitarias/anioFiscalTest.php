<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/anio_fiscal_modelo.php";
// vendor\bin\phpunit tests
class AnioFiscalTest extends TestCase
{
    private $anio_fiscal;

    public function setUp(): void{
        $this->anio_fiscal = new Anio_fiscal();
    }

    public function tearDown(): void{
        unset($this->anio_fiscal);
    }

    //Metodo consultar
    public function testConsultarAniosFiscales(){
        $resultado = $this->anio_fiscal->realizar_consulta('consultar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_anio_fiscal', $resultado[0]);
        $this->assertArrayHasKey('fecha_inicio', $resultado[0]);
        $this->assertArrayHasKey('fecha_cierre', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('descripcion', $resultado[0]);
    }

    // Metodo consultar_anio_fiscal
    public function testConsultarAnioFiscalUnicoIdCorrecto(){
        $this->anio_fiscal->set_id_anio_fiscal(1);

        $resultado = $this->anio_fiscal->realizar_consulta('consultar_anio_fiscal');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_anio_fiscal', $resultado);
        $this->assertArrayHasKey('fecha_inicio', $resultado);
        $this->assertArrayHasKey('fecha_cierre', $resultado);
        $this->assertArrayHasKey('estado', $resultado);
        $this->assertArrayHasKey('descripcion', $resultado);
    }

    public function testConsultarAnioFiscalUnicoIdIncorrecto(){
        $this->anio_fiscal->set_id_anio_fiscal(123122);

        $resultado = $this->anio_fiscal->realizar_consulta('consultar_anio_fiscal');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarAnioFiscalUnicoDatosVacios(){
        $this->anio_fiscal->set_id_anio_fiscal('');

        $resultado = $this->anio_fiscal->realizar_consulta('consultar_anio_fiscal');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    //Metodo registrar
    public function testRegistrarAnioFiscalDatosCorrectos(){
        $this->anio_fiscal->set_fecha_inicio("2020-01-01");
        $this->anio_fiscal->set_fecha_cierre("2021-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Prueba unitaria");

        $resultado = $this->anio_fiscal->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testRegistrarAnioFiscalDatosIncorrecto(){
        $this->anio_fiscal->set_fecha_inicio("fecha_icorrecto");
        $this->anio_fiscal->set_fecha_cierre("2021-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Prueba unitaria");

        $resultado = $this->anio_fiscal->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha de inicio' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarAnioFiscalUnicoDatosVacios(){
        $this->anio_fiscal->set_fecha_inicio("");
        $this->anio_fiscal->set_fecha_cierre("");
        $this->anio_fiscal->set_estado("");
        $this->anio_fiscal->set_descripcion("");

        $resultado = $this->anio_fiscal->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    // //Metodo editar
    public function testEditarAnioFiscalDatosCorrectos(){
        $this->anio_fiscal->set_id_anio_fiscal(1); // Id existente
        $this->anio_fiscal->set_fecha_inicio("2025-01-01");
        $this->anio_fiscal->set_fecha_cierre("2026-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Ejecutada prueba de edicion");

        $resultado = $this->anio_fiscal->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarAnioFiscalDatosIncorrecto(){
        $this->anio_fiscal->set_id_anio_fiscal(1); // Id existente
        $this->anio_fiscal->set_fecha_inicio("2021-01-01");
        $this->anio_fiscal->set_fecha_cierre("fecha_icorrecto");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Prueba erronea");

        $resultado = $this->anio_fiscal->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha de cierre' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarAnioFiscalIDIncorrecto(){
        $this->anio_fiscal->set_id_anio_fiscal(12312312); // Id inexistente
        $this->anio_fiscal->set_fecha_inicio("2021-01-01");
        $this->anio_fiscal->set_fecha_cierre("2022-01-01");
        $this->anio_fiscal->set_estado("Abierto");
        $this->anio_fiscal->set_descripcion("Prueba erronea");

        $resultado = $this->anio_fiscal->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Año Fiscal seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEditarAnioFiscalUnicoDatosVacios(){
        $this->anio_fiscal->set_id_anio_fiscal(1);
        $this->anio_fiscal->set_fecha_inicio("");
        $this->anio_fiscal->set_fecha_cierre("");
        $this->anio_fiscal->set_estado("");
        $this->anio_fiscal->set_descripcion("");

        $resultado = $this->anio_fiscal->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarAnioFiscalDatosCorrectos(){
        $this->anio_fiscal->set_id_anio_fiscal(4); // Id existente

        $resultado = $this->anio_fiscal->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarAnioFiscalIDIncorrecto(){
        $this->anio_fiscal->set_id_anio_fiscal(12312312); // Id inexistente

        $resultado = $this->anio_fiscal->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El Año Fiscal seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarAnioFiscalUnicoDatosVacios(){
        $this->anio_fiscal->set_id_anio_fiscal('');

        $resultado = $this->anio_fiscal->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del Año Fiscal requerido esta vacio", $resultado["mensaje"]);
    }

    //Metodo verificar_anio_fiscal
    public function testAniosFiscales(){
        $resultado = $this->anio_fiscal->realizar_consulta('verificar_anio_fiscal');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }
}

?>