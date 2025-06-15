<?php
	require_once "vista/componentes/sesion.php";
	require_once "modelo/conexion.php";

    if($accion == "inicio"){
  //   	$empleado = new Empleado();
  //   	$total_empleados = $empleado->total_empleados();
  //   	$total_empleados_activos = $empleado->total_empleados_activos();
  //   	$total_empleados_jubilados = $empleado->total_empleados_jubilados();
		// $total_empleados_reposo = $empleado->total_empleados_reposo();


        require_once "vista/inicio/inicio_vista.php";
    }

?>