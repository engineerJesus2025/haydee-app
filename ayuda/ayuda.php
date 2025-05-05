<?php


use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class  Ayuda{

    public static function crearCabeceraExcel($registros,$hojaActiva, $letraDeFinExcel,$titulo){

        /*Uniendo celdas*/
        $hojaActiva->mergeCells('A1:C1');

        /*Agregando imagenes*/
        $drawing = new Drawing();
        $drawing->setPath('./recursos/img/mercal_logo.png');
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($hojaActiva);
        $drawing->setWidth(900);
        $drawing->setHeight(65);

        foreach (range('A', $letraDeFinExcel) as $columnID) {
            $hojaActiva->getColumnDimension($columnID)->setAutoSize(true);
            $hojaActiva->getStyle($columnID)->getAlignment()->setHorizontal('center');
        }

        $hojaActiva->mergeCells('A2:'.$letraDeFinExcel.'3')->setCellValue('A2',$titulo);
        $hojaActiva->getStyle('A2')->getAlignment()->setHorizontal('center');
        $hojaActiva->getStyle('A2')->getFont()->setBold(true)->setSize(20);
        $hojaActiva->getStyle('A1:'.$letraDeFinExcel.'3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E01d22');
        $hojaActiva->getStyle('A2')->getFont()->getColor()->setRGB('FFFFFF');
        $hojaActiva->getStyle('A4:'.$letraDeFinExcel.'4')->getFont()->setBold(true)->setSize(12);

        $numero_de_filas = $letraDeFinExcel.(count($registros)+4);
        $hojaActiva->getStyle('A4:'.$numero_de_filas)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hojaActiva->getStyle('A4:'.$numero_de_filas)->getBorders()->getAllBorders()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK ));

    }

    public static function dateDiffInDays($date1, $date2) { 
    
        // Calculating the difference in timestamps 
        $diff = strtotime($date2) - strtotime($date1); 
      
        // 1 day = 24 hours 
        // 24 * 60 * 60 = 86400 seconds 
        return abs(round($diff / 86400)); 
    }


    public static  function checkArray($array, $key, $value) {
        foreach ($array as $item) {
            if (array_key_exists($key, $item) && $item[$key] == $value) {
                return true;
            }
        }
        return false;
    }

    public static function filtrarArray($array, $key, $value) {
        $filtered_array = [];
        foreach ($array as $item) {
            if (array_key_exists($key, $item) && $item[$key] == $value) {
                $filtered_array[] = $item;
            }
        }
        return $filtered_array;
    }

    public static function tiene_permiso($modulo,$accion) {

        $band=false;

        foreach($_SESSION["permisos"] AS $permisos){

            if($permisos["id_modulo"] == $modulo && $permisos["nombre_permiso"] == $accion){
                $band = true;
                break;
            }
        }

        


        // if(isset($_SESSION['permisos'])){
        //     for($i=0;$i<count($_SESSION['permisos']);$i++){
        //            if($_SESSION['permisos'][$i]->id_modulo==$modulo){
        //                $band=true;
        //                break;
        //            }

        //            if($_SESSION['permisos'][$i]->id_modulo==$modulo&&$_SESSION['permisos'][$i]->id_permiso==$permission){
        //                $band=true;
        //                break;
        //            }
               

        //     }

            // if(!is_null($route)&& !$band){
            //     header('Location:'.BASE_URL.$nameModule."/index");
            // }
        // }

        return $band;
    }
}
