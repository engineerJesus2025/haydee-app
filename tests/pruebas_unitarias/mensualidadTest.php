<?php 
use PHPUnit\Framework\TestCase;
require_once "modelo/mensualidad_modelo.php";
// vendor\bin\phpunit tests
class MensualidadTest extends TestCase
{
    private $mensualidad;

    private $id_mensualidad = 318;
    private $id_apartamento = 11;
    private $mes_borrar = "2";
    private $anio_borrar = "2025";

    public function setUp(): void{
        $this->mensualidad = new Mensualidad();
    }

    public function tearDown(): void{
        unset($this->mensualidad);
    }

    //Metodo verificarMeses
    public function testConsultarMesesSinMensualidad(){
        $resultado = $this->mensualidad->realizar_consulta('verificarMeses');
        //deben haber meses sin mensualidad
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);        

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('mes_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('anio_presupuesto', $resultado[0]);        
    }

    //Metodo consultarPorMeses
    public function testConsultarMensualidadesPorMeses(){
        $resultado = $this->mensualidad->realizar_consulta('consultarPorMeses',true);
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); 

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('ids', $resultado[0]);
        $this->assertArrayHasKey('ids_apartamentos', $resultado[0]);
        $this->assertArrayHasKey('monto', $resultado[0]);
        $this->assertArrayHasKey('tasa_dolar', $resultado[0]);
        $this->assertArrayHasKey('mes', $resultado[0]);
        $this->assertArrayHasKey('anio', $resultado[0]);
        $this->assertArrayHasKey('pagado', $resultado[0]);
        $this->assertArrayHasKey('porcentaje_interes', $resultado[0]);
        $this->assertArrayHasKey('limite_mensualidad', $resultado[0]);
    }

    //Metodo consultar_mensualidad_apartamentos
    public function testConsultarMensualidadApartamentosDatosCorrecto(){
        $this->mensualidad->set_mes("3");
        $this->mensualidad->set_anio("2025");

        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('id_mensualidad', $resultado[0]);
        $this->assertArrayHasKey('id_apartamento', $resultado[0]);
        $this->assertArrayHasKey('mes', $resultado[0]);
        $this->assertArrayHasKey('anio', $resultado[0]);
        $this->assertArrayHasKey('nro_apartamento', $resultado[0]);
        $this->assertArrayHasKey('nombre', $resultado[0]);
        $this->assertArrayHasKey('apellido', $resultado[0]);
        $this->assertArrayHasKey('monto', $resultado[0]);
        $this->assertArrayHasKey('tasa_dolar', $resultado[0]);
        $this->assertArrayHasKey('pagado', $resultado[0]);
        $this->assertArrayHasKey('pagado_dolar', $resultado[0]);        
    }

    public function testConsultarMensualidadApartamentosMesIncorrecto(){
        $this->mensualidad->set_mes("212312");
        $this->mensualidad->set_anio("2025");

        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El mes para la consulta no posee un valor valido", $resultado["mensaje"]);
    }

    public function testConsultarMensualidadApartamentosAnioIncorrecto(){
        $this->mensualidad->set_mes("3");
        $this->mensualidad->set_anio("2231025");

        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El año para la consulta no posee un valor valido", $resultado["mensaje"]);
    }

    public function testConsultarMensualidadApartamentosDatosVacios(){
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");

        $resultado = $this->mensualidad->realizar_consulta('consultar_mensualidad_apartamentos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El mes o año se envio vacios", $resultado["mensaje"]);
    }

    //Metodo registrar
    public function testRegistrarMensualidadDatosCorrectos(){
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_tasa_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertIsInt(intval($resultado["lastId"]));
    }

    public function testRegistrarMensualidadDatosIncorrecto(){
        $this->mensualidad->set_monto("monto incorrecto");
        $this->mensualidad->set_tasa_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(11);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los 'montos' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarMensualidadIDApartamentoIncorrecto(){
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_tasa_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(1231);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los Apartamentos seleccionados no existe", $resultado["mensaje"]);
    }

    public function testRegistrarMensualidadUnicoDatosVacios(){
        $this->mensualidad->set_monto("");
        $this->mensualidad->set_tasa_dolar("");
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");
        $this->mensualidad->set_apartamento_id('');
        $this->mensualidad->set_porcentaje_interes('');
        $this->mensualidad->set_limite_mensualidad('');

        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarMensualidadDatosCorrectos(){
        $this->mensualidad->set_id_mensualidad($this->id_mensualidad); // Id existente
        $this->mensualidad->set_monto("99.11");
        $this->mensualidad->set_tasa_dolar("11.99");
        $this->mensualidad->set_mes("2");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id($this->id_apartamento);
        $this->mensualidad->set_porcentaje_interes(9);
        $this->mensualidad->set_limite_mensualidad(9);

        $resultado = $this->mensualidad->realizar_consulta('editar');   

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);   
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarMensualidadDatosIncorrecto(){
        $this->mensualidad->set_id_mensualidad($this->id_mensualidad); // Id existente
        $this->mensualidad->set_monto("11.11");
        $this->mensualidad->set_tasa_dolar("monto dolar incorrecto");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id($this->id_apartamento);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('editar');  

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);      
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los 'montos en dolar' no posee un valor valido", $resultado["mensaje"]);
    }
    public function testEditarMensualidadIDIncorrecto(){
        $this->mensualidad->set_id_mensualidad(12312312); // Id inexistente
        $this->mensualidad->set_monto("11.11");
        $this->mensualidad->set_tasa_dolar("99.99");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id($this->id_apartamento);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('editar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);     
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("La mensualidad seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarMensualidadIDApartamentoIncorrecto(){
        $this->mensualidad->set_id_mensualidad($this->id_mensualidad); // Id existente
        $this->mensualidad->set_monto("20.12");
        $this->mensualidad->set_tasa_dolar("1.00");
        $this->mensualidad->set_mes("4");
        $this->mensualidad->set_anio("2025");
        $this->mensualidad->set_apartamento_id(1231);
        $this->mensualidad->set_porcentaje_interes(10);
        $this->mensualidad->set_limite_mensualidad(15);

        $resultado = $this->mensualidad->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno de los Apartamentos seleccionados no existe", $resultado["mensaje"]);
    }

    public function testEditarMensualidadUnicoDatosVacios(){
        $this->mensualidad->set_id_mensualidad($this->id_mensualidad);
        $this->mensualidad->set_monto("");
        $this->mensualidad->set_tasa_dolar("");
        $this->mensualidad->set_mes("");
        $this->mensualidad->set_anio("");
        $this->mensualidad->set_apartamento_id('');
        $this->mensualidad->set_porcentaje_interes('');
        $this->mensualidad->set_limite_mensualidad('');

        $resultado = $this->mensualidad->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarMensualidadDatosCorrectos(){
        $this->mensualidad->set_mes($this->mes_borrar);
        $this->mensualidad->set_anio($this->anio_borrar);

        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarMensualidadMesIncorrecto(){
        $this->mensualidad->set_mes("212312");
        $this->mensualidad->set_anio($this->anio_borrar);

        $resultado = $this->mensualidad->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El mes para la consulta no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEliminarMensualidadAnioIncorrecto(){
        $this->mensualidad->set_mes($this->mes_borrar);
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
        $resultado = $this->mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3,$resultado);
        
        // Revisamos la estructura de un elemento
        $this->assertArrayHasKey('mes', $resultado);
        $this->assertArrayHasKey('anio', $resultado);  
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