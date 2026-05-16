<?php
namespace haydee\ayuda;

use Dompdf\Dompdf;

class GestorPDF
{
    private const FORMATO_PAPEL = 'A4';
    private const EXTENSION_ARCHIVO = '.pdf';

    /**
     * Genera y descarga un PDF a partir de una vista y un arreglo de datos.
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
        $dompdf->setPaper(self::FORMATO_PAPEL, $orientacion);
        $dompdf->render();

        // Forzamos la descarga
        $dompdf->stream($nombre_archivo . self::EXTENSION_ARCHIVO);
        exit;
    }
}
