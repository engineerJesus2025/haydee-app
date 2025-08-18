<?php
require_once "modelo/proveedores_modelo.php";
$proveedor = new Proveedores();

if(isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];
    if($operacion == "consulta"){
        echo json_encode($proveedor->consultar());
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
        echo json_encode($proveedor->registrar());
    }

    elseif($operacion == "consulta_especifica"){
        $id_proveedor = $_POST["id_proveedor"];
        $proveedor->set_id_proveedor($id_proveedor);
        echo json_encode($proveedor->consultar_proveedor());
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

        echo json_encode($proveedor->editar_proveedor());
    }

    elseif ($operacion == "eliminar"){
        $id_proveedor = $_POST["id_proveedor"];
        $proveedor->set_id_proveedor($id_proveedor);
        echo json_encode($proveedor->eliminar_proveedor());
    }

    elseif ($operacion == "ultimo_id"){
        echo json_encode($proveedor->lastId());
    }
    exit;
}
require_once "vista/proveedores/proveedores_vista.php";
