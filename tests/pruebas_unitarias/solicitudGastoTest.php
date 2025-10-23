<?php
use PHPUnit\Framework\TestCase;
require_once "modelo/solicitud_gasto_modelo.php";

class SolicitudGastoTest extends TestCase{
    private $solicitud_gasto;

    public function setUp(): void{
        $this->solicitud_gasto = new Solicitud_gasto();

        $this->solicitud_gasto->get_conex()->beginTransaction();
    }

    public function tearDown(): void{
        $this->solicitud_gasto->get_conex()->rollBack();
        unset($this->solicitud_gasto);
    }

    // -------------- CONSULTAR --------------//

    public function testConsultarSolicitudesGasto(){
        $resultado = $this->solicitud_gasto->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertArrayHasKey('id_solicitud', $resultado[0]);
        $this->assertArrayHasKey('fecha_reporte', $resultado[0]);
        $this->assertArrayHasKey('descripcion_necesidad', $resultado[0]);
        $this->assertArrayHasKey('nombre_solicitante', $resultado[0]);
        $this->assertArrayHasKey('estado', $resultado[0]);
        $this->assertArrayHasKey('presupuesto_id', $resultado[0]);
        $this->assertArrayHasKey('monto_estimado', $resultado[0]);
        $this->assertArrayHasKey('prioridad', $resultado[0]);
    }

    public function testConsultarSolicitudGastoUnicoIdCorrecto(){
        $this->solicitud_gasto->set_id_solicitud(8);

        $resultado = $this->solicitud_gasto->realizar_consulta('consultar_solicitud_id');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_solicitud', $resultado);
        $this->assertArrayHasKey('monto_estimado', $resultado);
        $this->assertArrayHasKey('descripcion_necesidad', $resultado);
        $this->assertArrayHasKey('nombre_solicitante', $resultado);
        $this->assertArrayHasKey('estado', $resultado);
        $this->assertArrayHasKey('presupuesto_id', $resultado);
        $this->assertArrayHasKey('prioridad', $resultado);
    }
    public function testConsultarSolicitudGastoUnicoIdIncorrecto(){
        $this->solicitud_gasto->set_id_solicitud(9999);

        $resultado = $this->solicitud_gasto->realizar_consulta('consultar_solicitud_id');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }
    public function testConsultarSolicitudGastoUnicoDatosVacios(){
        $this->solicitud_gasto->set_id_solicitud('');

        $resultado = $this->solicitud_gasto->realizar_consulta('consultar_solicitud_id');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // -------------- REGISTRAR --------------//
    public function testRegistrarSolicitudDatosCorrectos()
    {
        $this->solicitud_gasto->set_fecha_reporte('2025-10-21');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba de registro exitoso');
        $this->solicitud_gasto->set_nombre_solicitante('Tester');
        $this->solicitud_gasto->set_monto_estimado(5.00);
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(56); 
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "El registro falló: " . ($resultado['mensaje'] ?? 'Error desconocido'));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }
    public function testRegistrarSolicitudDatosInvalidos()
    {
        $this->solicitud_gasto->set_fecha_reporte('2025-10-21');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba monto inválido');
        $this->solicitud_gasto->set_nombre_solicitante('Tester');
        $this->solicitud_gasto->set_monto_estimado(0); // <-- Dato inválido
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(999999);
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('monto estimado debe ser un número mayor a cero', $resultado['mensaje']);
    }
    public function testRegistrarSolicitudDatosVacios()
    {
        $this->solicitud_gasto->set_fecha_reporte('');
        $this->solicitud_gasto->set_descripcion_necesidad('');
        $this->solicitud_gasto->set_nombre_solicitante('');
        $this->solicitud_gasto->set_monto_estimado('');
        $this->solicitud_gasto->set_estado('');
        $this->solicitud_gasto->set_presupuesto_id('');
        $this->solicitud_gasto->set_prioridad('');

        $resultado = $this->solicitud_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La descripción de la necesidad no puede estar vacía.', $resultado['mensaje']);
    }



// -------------- MODIFICAR --------------//
public function testModificarSolicitudDatosCorrectos()
    {
        $this->solicitud_gasto->set_id_solicitud(8);
        
        $this->solicitud_gasto->set_fecha_reporte('2025-10-25');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba de modificación exitosa');
        $this->solicitud_gasto->set_nombre_solicitante('Tester Modificado');
        $this->solicitud_gasto->set_monto_estimado(10.00); // Un monto bajo que quepa
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(56); // Presupuesto válido
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "La modificación falló: " . ($resultado['mensaje'] ?? 'Error desconocido'));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testModificarSolicitudIdVacio()
    {
        $this->solicitud_gasto->set_id_solicitud(''); // <-- ID Inválido
        
        $this->solicitud_gasto->set_fecha_reporte('2025-10-25');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba ID vacío');
        $this->solicitud_gasto->set_nombre_solicitante('Tester Modificado');
        $this->solicitud_gasto->set_monto_estimado(10.00);
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(56);
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('id de la solicitud requerida esta vacio', $resultado['mensaje']);
    }
    public function testModificarSolicitudIdInexistente()
    {
        $this->solicitud_gasto->set_id_solicitud(99999); // <-- ID Inválido
        
        // Seteamos el resto de datos como válidos
        $this->solicitud_gasto->set_fecha_reporte('2025-10-25');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba ID inexistente');
        $this->solicitud_gasto->set_nombre_solicitante('Tester Modificado');
        $this->solicitud_gasto->set_monto_estimado(10.00);
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(56);
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La solicitud seleccionada no existe', $resultado['mensaje']);
    }

    public function testModificarSolicitudDatosVacios()
    {
        $this->solicitud_gasto->set_id_solicitud(8);
        $this->solicitud_gasto->set_descripcion_necesidad(''); // <-- Dato Inválido

        $this->solicitud_gasto->set_fecha_reporte('');
        $this->solicitud_gasto->set_nombre_solicitante('');
        $this->solicitud_gasto->set_monto_estimado('');
        $this->solicitud_gasto->set_estado('');
        $this->solicitud_gasto->set_presupuesto_id('');
        $this->solicitud_gasto->set_prioridad('');

        $resultado = $this->solicitud_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La descripción de la necesidad no puede estar vacía', $resultado['mensaje']);
    }
    
    public function testModificarSolicitudDatosInvalidos()
    {
        $this->solicitud_gasto->set_id_solicitud(8);
        $this->solicitud_gasto->set_monto_estimado(999999999.00); // Monto Inválido (excedido)

        $this->solicitud_gasto->set_fecha_reporte('2025-10-25');
        $this->solicitud_gasto->set_descripcion_necesidad('Prueba monto excedido');
        $this->solicitud_gasto->set_nombre_solicitante('Tester Modificado');
        $this->solicitud_gasto->set_estado('Pendiente');
        $this->solicitud_gasto->set_presupuesto_id(5542316);
        $this->solicitud_gasto->set_prioridad(1);

        $resultado = $this->solicitud_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El presupuesto seleccionado no es válido', $resultado['mensaje']);
    }


    // -------------- ELIMINAR --------------//
    public function testEliminarSolicitudDatosCorrectos()
    {
        $this->solicitud_gasto->set_id_solicitud(8); 

        $resultado = $this->solicitud_gasto->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "La eliminación falló: " . ($resultado['mensaje'] ?? 'Error desconocido'));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testEliminarSolicitudIdInexistente()
    {
        $this->solicitud_gasto->set_id_solicitud(99999); 

        $resultado = $this->solicitud_gasto->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La solicitud seleccionada no existe', $resultado['mensaje']);
    }

    public function testEliminarSolicitudIdVacio()
    {
        $this->solicitud_gasto->set_id_solicitud(''); 

        $resultado = $this->solicitud_gasto->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('id de la solicitud requerida esta vacio', $resultado['mensaje']);
    }


    // -------------- OTROS METODOS --------------//
    public function testConsultarPresupuestoConFechaExistente()
    {
        $fecha_existente = '2025-05-01';
        
        $resultado = $this->solicitud_gasto->consultar_presupuesto($fecha_existente);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('id_presupuesto', $resultado);
        $this->assertArrayHasKey('disponible', $resultado);
    }

    public function testConsultarPresupuestoConFechaInexistente()
    {
        $fecha_inexistente = '1990-01-01'; 

        $resultado = $this->solicitud_gasto->consultar_presupuesto($fecha_inexistente);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('No hay presupuesto registrado', $resultado['mensaje']);
    }

    public function testConsultarPresupuestoDisponibleConIdExistente()
    {
        $id_presupuesto_existente = 56; 
        $this->solicitud_gasto->set_presupuesto_id($id_presupuesto_existente);
        
        $resultado = $this->solicitud_gasto->consultar_presupuesto_disponible();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        
        // Verifica que me devuelva los campos de cálculo
        $this->assertArrayHasKey('monto_presupuesto_total', $resultado);
        $this->assertArrayHasKey('total_usado', $resultado);
        $this->assertArrayHasKey('disponible', $resultado);
        $this->assertIsNumeric($resultado['disponible']); 
    }

    public function testConsultarPresupuestoDisponibleConIdInexistente()
    {
        $id_inexistente = 99999;
        $this->solicitud_gasto->set_presupuesto_id($id_inexistente);
        
        $resultado = $this->solicitud_gasto->consultar_presupuesto_disponible();

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('No se encontró el presupuesto', $resultado['mensaje']);
    }

public function testConsultarSolicitudesPorMesConDatos()
    {
        $anio = 2025;
        $mes = 9;
        
        $resultado = $this->solicitud_gasto->consultar_solicitudes_por_mes($mes, $anio);

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado); // Debe encontrar al menos una
        $this->assertArrayHasKey('id_solicitud', $resultado[0]); // Verifica la estructura
    }

    public function testConsultarSolicitudesPorMesSinDatos()
    {
        $anio = 1990;
        $mes = 1;
        
        $resultado = $this->solicitud_gasto->consultar_solicitudes_por_mes($mes, $anio);

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    }