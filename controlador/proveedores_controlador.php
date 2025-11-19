<?php
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PROVEEDORES, CONSULTAR);

use haydee\modelo\Proveedores;

$proveedor = new Proveedores();

if(isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];
    if($operacion == "consulta"){
        $proveedor->registrar_bitacora(CONSULTAR, GESTIONAR_PROVEEDORES, "TODOS LOS PROVEEDORES");
        echo json_encode($proveedor->realizar_consulta("consultar"));
    }

    elseif($operacion == "registrar"){
        $nombre = $_POST["nombre_proveedor"];
        $servicio = $_POST["servicio"];
        $rif = $_POST["rif"];
        $direccion = $_POST["direccion"];

        $proveedor->set_nombre_proveedor($nombre);
        $proveedor->set_servicio($servicio);
        $proveedor->set_rif($rif);
        $proveedor->set_direccion($direccion);
        $resultado = $proveedor->realizar_consulta("registrar");

        if ($resultado["estatus"]) {
            $proveedor->registrar_bitacora(REGISTRAR, GESTIONAR_PROVEEDORES, $nombre . " - " . $rif);
        }
        echo json_encode($resultado);
    }

    elseif($operacion == "consultar_proveedor"){
        $id_proveedor = $_POST["id_proveedor"];
        $proveedor->set_id_proveedor($id_proveedor);
        echo json_encode($proveedor->realizar_consulta("consultar_proveedor"));
    }

    elseif ($operacion == "modificar"){
        $id_proveedor = $_POST["id_proveedor"];
        $nombre = $_POST["nombre_proveedor"];
        $servicio = $_POST["servicio"];
        $rif = $_POST["rif"];
        $direccion = $_POST["direccion"];

        $proveedor->set_id_proveedor($id_proveedor);
        $proveedor->set_nombre_proveedor($nombre);
        $proveedor->set_servicio($servicio);
        $proveedor->set_rif($rif);
        $proveedor->set_direccion($direccion);

        $resultado = $proveedor->realizar_consulta("modificar");

        if ($resultado["estatus"]) {
            $proveedor->registrar_bitacora(MODIFICAR, GESTIONAR_PROVEEDORES, $nombre . " - " . $rif);
        }
    }

    elseif ($operacion == "eliminar"){
        $id_proveedor = $_POST["id_proveedor"];
        $proveedor->set_id_proveedor($id_proveedor);
        $proveedor_alterado = $proveedor->realizar_consulta("consultar_proveedor");
        $resultado = $proveedor->realizar_consulta("eliminar");

        if ($resultado["estatus"]) {
            $proveedor->registrar_bitacora(ELIMINAR, GESTIONAR_PROVEEDORES, $proveedor_alterado["nombre_proveedor"] . " - " . $proveedor_alterado["rif"]);
        }
        echo json_encode($resultado);
    }

    elseif ($operacion == "lastId"){
        echo json_encode($proveedor->realizar_consulta("lastId"));
    }
    exit;
}
require_once "vista/proveedores/proveedores_vista.php";
