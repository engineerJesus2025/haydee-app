<?php
namespace haydee\ayuda;

use haydee\enums\MetodoPago;
use haydee\ayuda\GestorImagenes;
    
class ConstructorDetalles
{
    private const IMAGEN_POR_DEFECTO = 'default.png';
    private const FORMATO_REGEX_IMAGEN = '/^imagen_(\d+)$/';

    /**
     * Construye un array de detalles a partir de los datos POST y FILES.
    */
    public static function construirDetalles($post, $files, $config = [], $esEdicion = false)
    {
        $config = array_merge([
            'campos' => ['fecha', 'monto', 'metodo_pago', 'descripcion_detalle'],
            'bancarios' => ['banco_id', 'referencia'],
            'imagenes' => 'imagen',
            'metodo_pago_campo' => 'metodo_pago',
            'metodos_con_archivo' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value],
            'imagen_default' => self::IMAGEN_POR_DEFECTO,
            'indice_archivo_formato' => self::FORMATO_REGEX_IMAGEN,
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

            // Extraer campos escalares solicitados
            foreach ($config['campos'] as $campo) {
                $detalle[$campo] = $post[$campo][$i] ?? null;
            }

            // Solo procesar lógica bancaria/imágenes si el método de pago es parte de los campos
            if (in_array($config['metodo_pago_campo'], $config['campos'])) {
                $metodoPago = $detalle[$config['metodo_pago_campo']] ?? '';
                $metodoNormalizado = strtoupper(trim($metodoPago)); 
                $esBancario = in_array($metodoNormalizado, $config['metodos_con_archivo']);

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
                    foreach ($config['bancarios'] as $campoBan) {
                        $detalle[$campoBan] = null;
                    }
                    $detalle[$config['imagenes']] = null;
                }
            }

            $detalles[] = $detalle;
        }

        return $detalles;
    }

    public static function ConstruirConceptosGastos($post)
    {
        $config = [
            'campos' => ['id_concepto', 'nombre_concepto']
        ];
        return self::construirDetalles($post, [], $config);
    }

    public static function ConstruirDetallesGastos($post, $files, $esEdicion = false)
    {
        $config = [
            'campos' => ['fecha_detalle', 'monto', 'metodo_pago'],
            'bancarios' => ['cuenta_id', 'referencia'],
            'imagenes' => 'imagen',
            'metodo_pago_campo' => 'metodo_pago',
            'metodos_con_archivo' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value],
            'carpeta_imagenes' => 'gastos',
            'campo_existente' => 'imagen_existente',
            'indice_archivo_formato' => self::FORMATO_REGEX_IMAGEN
        ];
        return self::construirDetalles($post, $files, $config, $esEdicion);
    }

    public static function ConstruirDetallesPagos($post, $files, $esEdicion = false)
    {
        $config = [
            'campos' => ['fecha_pago', 'monto', 'tipo_pago'], 
            'bancarios' => ['banco_id', 'referencia', 'cuenta_id'],
            'imagenes' => 'imagen',
            'metodo_pago_campo' => 'tipo_pago',
            'metodos_con_archivo' => [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value],
            'carpeta_imagenes' => 'pagos',
            'campo_existente' => 'imagen_existente',
            'indice_archivo_formato' => self::FORMATO_REGEX_IMAGEN
        ];
        return self::construirDetalles($post, $files, $config, $esEdicion);
    }
}