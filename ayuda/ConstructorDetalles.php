<?php
namespace haydee\ayuda;

use haydee\ayuda\GestorImagenes;

class ConstructorDetalles
{
    /**
     * Construye un array de detalles a partir de los datos POST y FILES.
     * 
     * @param array $post Datos $_POST
     * @param array $files Datos $_FILES
     * @param array $config Configuración de campos y mapeo (opcional)
     *        - 'campos': lista de campos escalares a extraer (ej. ['fecha', 'monto', 'metodo_pago'])
     *        - 'bancarios': campos que solo se incluyen si el método de pago lo requiere (ej. ['banco_id', 'referencia'])
     *        - 'imagenes': nombre del campo de archivo (por defecto 'imagen')
     *        - 'metodo_pago_campo': nombre del campo que indica el método de pago (por defecto 'metodo_pago')
     *        - 'metodos_con_archivo': array de métodos de pago que requieren archivo (por defecto ['Transferencia', 'Pago Movil'])
     *        - 'imagen_default': nombre de imagen por defecto (por defecto 'default.png')
     *        - 'indice_archivo_formato': patrón de los nombres de archivo en $_FILES (por defecto '/^imagen_(\d+)$/')
     * @param bool $esEdicion Si es true, se consideran imágenes existentes (campo 'imagen_existente')
     * @return array Array de detalles, cada uno con los campos configurados
    */
    public static function construirDetalles($post, $files, $config = [], $esEdicion = false)
    {
        $config = array_merge([
            'campos' => ['fecha', 'monto', 'metodo_pago', 'descripcion_detalle'],
            'bancarios' => ['banco_id', 'referencia'],
            'imagenes' => 'imagen',
            'metodo_pago_campo' => 'metodo_pago',
            'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
            'imagen_default' => 'default.png',
            'indice_archivo_formato' => '/^imagen_(\d+)$/',
            'campo_existente' => 'imagen_existente'
        ], $config);

        $detalles = [];
        $numFilas = count($post[$config['campos'][0]] ?? []);

        // Mapear archivos subidos por índice
        $archivosPorIndice = [];
        foreach ($files as $key => $fileArray) {
            if (preg_match($config['indice_archivo_formato'], $key, $matches)) {
                $index = (int)$matches[1];
                if ($fileArray['error'] === UPLOAD_ERR_OK) {
                    $archivosPorIndice[$index] = $fileArray;
                }
            }
        }

        for ($i = 0; $i < $numFilas; $i++) {
            $detalle = [];

            // Extraer campos escalares
            foreach ($config['campos'] as $campo) {
                $detalle[$campo] = $post[$campo][$i] ?? null;
            }

            // Si el método de pago requiere datos bancarios
            $metodoPago = $detalle[$config['metodo_pago_campo']] ?? '';
            $esBancario = in_array($metodoPago, $config['metodos_con_archivo']);

            if ($esBancario) {
                foreach ($config['bancarios'] as $campoBan) {
                    $detalle[$campoBan] = $post[$campoBan][$i] ?? null;
                }

                // Procesar imagen
                if (isset($archivosPorIndice[$i])) {
                    $nombreImagen = GestorImagenes::subir($archivosPorIndice[$i], $config['carpeta_imagenes'] ?? 'gastos');
                    $detalle[$config['imagenes']] = $nombreImagen ?: $config['imagen_default'];
                } else {
                    if ($esEdicion && isset($post[$config['campo_existente']][$i])) {
                        $detalle[$config['imagenes']] = $post[$config['campo_existente']][$i];
                    } else {
                        $detalle[$config['imagenes']] = $config['imagen_default'];
                    }
                }
            } else {
                // Si no es bancario, estos campos se dejan como null o se omiten
                foreach ($config['bancarios'] as $campoBan) {
                    $detalle[$campoBan] = null;
                }
                $detalle[$config['imagenes']] = null;
            }

            $detalles[] = $detalle;
        }

        return $detalles;
    }

    /**
     * Versión simplificada para Gastos con configuración por defecto.
     */
    public static function ConstruirDetallesGastos($post, $files, $esEdicion = false)
    {
        $config = [
            'campos' => ['fecha_detalle', 'monto', 'metodo_pago', 'descripcion_detalle'],
            'bancarios' => ['banco_id', 'referencia'],
            'imagenes' => 'imagen',
            'metodo_pago_campo' => 'metodo_pago',
            'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
            'carpeta_imagenes' => 'gastos',
            'campo_existente' => 'imagen_existente',
            'indice_archivo_formato' => '/^imagen_(\d+)$/'
        ];
        return self::construirDetalles($post, $files, $config, $esEdicion);
    }

    /**
     * Versión simplificada para Pagos (ejemplo).
     */
    public static function ConstruirDetallesPagos($post, $files, $esEdicion = false)
    {
        // Configuración específica para pagos
        $config = [
            'campos' => ['fecha_pago', 'monto_pago', 'metodo_pago', 'referencia_pago'],
            'bancarios' => ['banco_id', 'referencia'],
            'imagenes' => 'comprobante',
            'metodo_pago_campo' => 'metodo_pago',
            'metodos_con_archivo' => ['Transferencia', 'Depósito'],
            'carpeta_imagenes' => 'pagos',
            'campo_existente' => 'comprobante_existente',
            'indice_archivo_formato' => '/^comprobante_(\d+)$/'
        ];
        return self::construirDetalles($post, $files, $config, $esEdicion);
    }

    // Se pueden agregar más métodos específicos para Presupuesto, etc.
}