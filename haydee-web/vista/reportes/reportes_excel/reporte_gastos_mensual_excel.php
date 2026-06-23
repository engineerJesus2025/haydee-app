<?php
// Asegurarnos de que no haya salida de HTML previa que corrompa el archivo Excel
if (ob_get_length()) ob_clean();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 1. Preparación de datos base
$meses = [1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'];
$nombre_mes = $meses[(int)$mes];

// Reintegramos el Gas a los gastos variables para el cuadro si existe
if ($total_gas_bs > 0) {
    $gastos_variables[] = ['descripcion_gasto' => 'GAS LARA', 'monto' => $total_gas_bs];
}

$spreadsheet = new Spreadsheet();

// 2. Definición de Estilos Reutilizables
$estiloTitulo = [
    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D6EFD']] // Azul Bootstrap
];

$estiloCabeceraTabla = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']] // Azul claro
];

$estiloTotales = [
    'font' => ['bold' => true],
    'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']]
];

// 3. Función constructora de Hojas (Para no repetir código)
$generarHoja = function($sheet, $titulo_secundario, $fijos, $variables, $tasa, $tipo_calculo) use ($nombre_mes, $anio, $estiloTitulo, $estiloCabeceraTabla, $estiloTotales) {
    
    // Configurar columnas
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(55);
    $sheet->getColumnDimension('C')->setWidth(20);

    // Títulos
    $sheet->setCellValue('A1', "RELACIÓN DE GASTOS DEL MES DE $nombre_mes $anio");
    $sheet->mergeCells('A1:C1');
    $sheet->getStyle('A1:C1')->applyFromArray($estiloTitulo);
    
    $sheet->setCellValue('A2', $titulo_secundario);
    $sheet->mergeCells('A2:C2');
    $sheet->getStyle('A2:C2')->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Cabeceras de tabla
    $sheet->setCellValue('A4', 'N°');
    $sheet->setCellValue('B4', 'DESCRIPCIÓN');
    $sheet->setCellValue('C4', 'MONTO BS.');
    $sheet->getStyle('A4:C4')->applyFromArray($estiloCabeceraTabla);

    $fila = 5;
    $subtotal = 0;

    // --- GASTOS FIJOS ---
    $sheet->setCellValue('B'.$fila, 'GASTOS FIJOS DEL MES');
    $sheet->getStyle('B'.$fila)->getFont()->setBold(true);
    $fila++;

    $contador = 1;
    foreach ($fijos as $gasto) {
        $sheet->setCellValue('A'.$fila, $contador++);
        $sheet->setCellValue('B'.$fila, strtoupper($gasto['descripcion_gasto']));
        $sheet->setCellValue('C'.$fila, $gasto['monto']);
        $sheet->getStyle('C'.$fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $subtotal += $gasto['monto'];
        $fila++;
    }

    $fila++; // Espacio

    // --- GASTOS VARIABLES ---
    $sheet->setCellValue('B'.$fila, 'GASTOS VARIABLES DEL MES');
    $sheet->getStyle('B'.$fila)->getFont()->setBold(true);
    $fila++;

    $contador = 1;
    foreach ($variables as $gasto) {
        // Regla de Exención: Si es la hoja del Apto 61, nos saltamos la luz/Corpoelec
        if ($tipo_calculo === 'exento') {
            if (stripos($gasto['descripcion_gasto'], 'corpoelec') !== false || stripos($gasto['descripcion_gasto'], 'luz') !== false) {
                continue; // Saltamos este registro
            }
        }

        $sheet->setCellValue('A'.$fila, $contador++);
        $sheet->setCellValue('B'.$fila, strtoupper($gasto['descripcion_gasto']));
        $sheet->setCellValue('C'.$fila, $gasto['monto']);
        $sheet->getStyle('C'.$fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $subtotal += $gasto['monto'];
        $fila++;
    }

    // --- CÁLCULOS FINALES ---
    $fondo_reserva = $subtotal * 0.10; // 10%
    $total_general = $subtotal + $fondo_reserva;

    $fila++;
    $sheet->setCellValue('B'.$fila, 'SUB-TOTAL BS.');
    $sheet->setCellValue('C'.$fila, $subtotal);
    
    $fila++;
    $sheet->setCellValue('B'.$fila, '10% FONDO DE RESERVA BS.');
    $sheet->setCellValue('C'.$fila, $fondo_reserva);

    $fila++;
    $sheet->setCellValue('B'.$fila, 'TOTAL GASTOS BS.');
    $sheet->setCellValue('C'.$fila, $total_general);
    $sheet->getStyle("B$fila:C$fila")->applyFromArray($estiloTotales);

    // Formatear la columna C como moneda en estas filas
    $sheet->getStyle("C".($fila-2).":C$fila")->getNumberFormat()->setFormatCode('#,##0.00');

    // --- CÁLCULO DE CUOTAS SEGÚN LA HOJA ---
    $fila += 2;
    $cuota_bs = 0;
    $divisor_estandar = 28; // Apartamentos estándar

    if ($tipo_calculo === 'ph') {
        $cuota_bs = $total_general * 0.055; // 5.50% de participación
        $txt_cuota = "CUOTA PH 5,50% BS.";
    } elseif ($tipo_calculo === 'exento') {
        $cuota_bs = $total_general / $divisor_estandar;
        $txt_cuota = "CUOTA APTO EXENTO BS.";
    } else {
        $cuota_bs = $total_general / $divisor_estandar;
        $txt_cuota = "CUOTA POR APARTAMENTO BS.";
    }

    $cuota_dolar = $cuota_bs / $tasa;

    // Imprimir Cuotas
    $sheet->setCellValue('B'.$fila, $txt_cuota);
    $sheet->setCellValue('C'.$fila, $cuota_bs);
    $sheet->getStyle("B$fila:C$fila")->getFont()->setBold(true);
    $sheet->getStyle("C$fila")->getNumberFormat()->setFormatCode('#,##0.00');
    
    $fila++;
    $sheet->setCellValue('B'.$fila, str_replace('BS.', '$', $txt_cuota));
    $sheet->setCellValue('C'.$fila, $cuota_dolar);
    $sheet->getStyle("B$fila:C$fila")->getFont()->setBold(true);
    $sheet->getStyle("B$fila:C$fila")->getFont()->getColor()->setARGB('FF008000'); // Verde
    $sheet->getStyle("C$fila")->getNumberFormat()->setFormatCode('#,##0.00');

    $fila += 2;
    $sheet->setCellValue('B'.$fila, "TASA BCV APLICADA: BS. " . number_format($tasa, 2, ',', '.'));
    $sheet->getStyle('B'.$fila)->getFont()->setItalic(true);
};

// 4. Creación de las 3 Hojas

// Hoja 1: Aptos en General
$hoja1 = $spreadsheet->getActiveSheet();
$hoja1->setTitle('APTOS EN GRAL');
$generarHoja($hoja1, "APARTAMENTOS EN GENERAL", $gastos_fijos, $gastos_variables, $tasa_dolar, 'general');

// Hoja 2: Penthouse
$hoja2 = $spreadsheet->createSheet();
$hoja2->setTitle('PH 5,50');
$generarHoja($hoja2, "PENTHOUSE (5,50%)", $gastos_fijos, $gastos_variables, $tasa_dolar, 'ph');

// Hoja 3: Exento Apto 61
$hoja3 = $spreadsheet->createSheet();
$hoja3->setTitle('EXENTO APTO 61');
$generarHoja($hoja3, "APARTAMENTO 61 (EXENTO LUZ)", $gastos_fijos, $gastos_variables, $tasa_dolar, 'exento');

// 5. Configurar Cabeceras HTTP para forzar la descarga del Excel
$nombreDelArchivo = "CUADRO_GASTOS_{$nombre_mes}_{$anio}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreDelArchivo . '"');
header('Cache-Control: max-age=0');

// Generar y enviar el archivo al navegador
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Detener el script para no enviar basura al archivo
exit;