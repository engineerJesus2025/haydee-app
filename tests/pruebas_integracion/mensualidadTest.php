<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/mensualidad_modelo.php";
// vendor\bin\phpunit tests
class MensualidadTest extends TestCase
{
    private $mensualidad;

    private $apartamento_id = 11;
    private $id_presupuestos = [];

    public function setUp(): void{
        $this->mensualidad = new Mensualidad();
    }

    public function tearDown(): void{
        unset($this->mensualidad);
    }

    public function testRegistrarMensualidad(){
        $presupuesto_mensualidad_obj = new Presupuesto_mensualidad();

        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_monto_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id($this->apartamento_id);

        $resultado_mensualidad = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado_mensualidad);
        $this->assertNotEmpty($resultado_mensualidad);
        $this->assertCount(3, $resultado_mensualidad);
        
        $this->assertTrue($resultado_mensualidad["estatus"]);
        $this->assertStringContainsString('OK', $resultado_mensualidad["mensaje"]);
        $this->assertIsInt(intval($resultado_mensualidad["lastId"]));

        $presupuesto_mensualidad_obj->set_mensualidad_id($resultado_mensualidad["lastId"]);

        foreach ($this->id_presupuestos as $presupuesto) {
            $presupuesto_mensualidad_obj->set_presupuesto_id($presupuesto);            
            $resultado_presupuesto = $presupuesto_mensualidad_obj->realizar_consulta('registrar_presupuesto_mensualidad');

            $this->assertIsArray($resultado_presupuesto);
            $this->assertNotEmpty($resultado_presupuesto);
            $this->assertCount(2, $resultado_presupuesto);
            
            $this->assertTrue($resultado_presupuesto["estatus"]);
            $this->assertStringContainsString('OK', $resultado_presupuesto["mensaje"]);
        }
    }

    //Metodo registrar
    public function testRegistrarMensualidadDatosCorrectos(){
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_monto_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);

        $resultado = $this->mensualidad->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertIsInt(intval($resultado["lastId"]));
    }

    public function testRegistrarMensualidadDatosIncorrecto(){
        $this->mensualidad->set_monto("monto incorrecto");
        $this->mensualidad->set_monto_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);

        $resultado = $this->mensualidad->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los 'montos' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarMensualidadIDApartamentoIncorrecto(){
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_monto_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(1231);

        $resultado = $this->mensualidad->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los Apartamentos seleccionados no existe", $resultado["mensaje"]);
    }

    public function testRegistrarMensualidadUnicoDatosVacios(){
        $this->mensualidad->set_monto("");
        $this->mensualidad->set_monto_dolar("");
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");
        $this->mensualidad->set_apartamento_id('');

        $resultado = $this->mensualidad->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarMensualidadDatosCorrectos(){
        $this->mensualidad->set_id_mensualidad(238); // Id existente
        $this->mensualidad->set_monto("99.11");
        $this->mensualidad->set_monto_dolar("11.99");
        $this->mensualidad->set_mes("2");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);

        $resultado = $this->mensualidad->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarMensualidadDatosIncorrecto(){
        $this->mensualidad->set_id_mensualidad(238); // Id existente
        $this->mensualidad->set_monto("11.11");
        $this->mensualidad->set_monto_dolar("monto dolar incorrecto");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);

        $resultado = $this->mensualidad->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los 'montos en dolar' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarMensualidadIDIncorrecto(){
        $this->mensualidad->set_id_mensualidad(12312312); // Id inexistente
        $this->mensualidad->set_monto("11.11");
        $this->mensualidad->set_monto_dolar("99.99");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);

        $resultado = $this->mensualidad->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La mensualidad seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarMensualidadIDApartamentoIncorrecto(){
        $this->mensualidad->set_id_mensualidad(238); // Id existente
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_monto_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(1231);

        $resultado = $this->mensualidad->realizar_consulta('registrar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los Apartamentos seleccionados no existe", $resultado["mensaje"]);
    }

    public function testEditarMensualidadUnicoDatosVacios(){
        $this->mensualidad->set_id_mensualidad(238);
        $this->mensualidad->set_monto("");
        $this->mensualidad->set_monto_dolar("");
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");
        $this->mensualidad->set_apartamento_id('');

        $resultado = $this->mensualidad->realizar_consulta('editar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarMensualidadDatosCorrectos(){
        $this->mensualidad->set_mes("8");
        $this->mensualidad->set_anio("2008");

        $resultado = $this->mensualidad->realizar_consulta('eliminar',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarMensualidadMesIncorrecto(){
        $this->mensualidad->set_mes("212312");
        $this->mensualidad->set_anio("2025");

        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El mes para la consulta no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEliminarMensualidadAnioIncorrecto(){
        $this->mensualidad->set_mes("2");
        $this->mensualidad->set_anio("2231025");

        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El año para la consulta no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEliminarMensualidadDatosVacios(){
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");

        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El mes o año se envio vacios", $resultado["mensaje"]);
    }

    //Metodo consultar_estadisticas_inicio
    public function testConsultarEstadisticasDelInicio(){
        $resultado = $this->mensualidad->realizar_consulta('consultar_estadisticas_inicio');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('accion', $resultado[0]);
        $this->assertArrayHasKey('valor', $resultado[0]);        
    }

    //Metodo consultar_meses_mensualidad
    public function testConsultarMesesMensualidad(){
        $resultado = $this->mensualidad->realizar_consulta('consultar_meses_mensualidad');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('mes', $resultado[0]);
        $this->assertArrayHasKey('anio', $resultado[0]);        
    }

    //Metodo consultar_monto_dolar_mensualidades
    public function testConsultarMontoEnDolares(){
        $resultado = $this->mensualidad->realizar_consulta('consultar_monto_dolar_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(4,$resultado);
        
        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('mes', $resultado);
        $this->assertArrayHasKey('anio', $resultado);
        $this->assertArrayHasKey('monto_dolar', $resultado);
        $this->assertArrayHasKey('tasa_dolar', $resultado);
    }

    //Metodo consultar_mensualidades_pendientes
    public function testConsultarMensualidadesPendientes(){
        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidades_pendientes');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('nro_apartamento', $resultado[0]);
        $this->assertArrayHasKey('anio', $resultado[0]);
        $this->assertArrayHasKey('mes', $resultado[0]);
        $this->assertArrayHasKey('cambio_neto_mes', $resultado[0]);
        $this->assertArrayHasKey('deuda_acumulada', $resultado[0]);        
    }   
}

?>