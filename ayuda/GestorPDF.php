<?php
namespace haydee\ayuda;

use Dompdf\Dompdf;

class GestorPDF
{
    /**
     * Genera y descarga un PDF a partir de una vista y un arreglo de datos.
     * * @param string $ruta_vista La ruta al archivo PHP que contiene el HTML.
     * @param array $datos Arreglo asociativo con las variables que usará la vista.
     * @param string $nombre_archivo El nombre del archivo a descargar (sin .pdf).
     * @param string $orientacion 'portrait' (vertical) o 'landscape' (horizontal).
     */
    public static function generar($ruta_vista, $datos, $nombre_archivo, $orientacion = 'portrait')
    {
        // extract() convierte las llaves del arreglo en variables.
        // Ejemplo: ['mes' => 'Enero'] se convierte en la variable $mes disponible para la vista.
        extract($datos);

        // Capturamos el HTML de la vista
        ob_start();
        require_once $ruta_vista;
        $html = ob_get_clean();

        // Configuramos y renderizamos Dompdf
        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientacion);
        $dompdf->render();

        // Forzamos la descarga
        $dompdf->stream($nombre_archivo . ".pdf");
        exit;
    }
}
?>