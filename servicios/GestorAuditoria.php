<?php
namespace haydee\servicios;

use haydee\modelo\Bitacora;

class GestorAuditoria
{
    private $modelo;
    private $moduloId;
    private $datosAnteriores = null;

    public function __construct($modelo, $moduloId)
    {
        $this->modelo = $modelo;
        $this->moduloId = $moduloId;
    }

    /**
     * Prepara la sesión para auditar la próxima vez que se consulte la tabla.
     * Ideal para llamarse cuando se carga la página web (GET).
     */
    public static function inicializarBanderaConsulta($moduloId)
    {
        $_SESSION['auditar_' . $moduloId] = true;
    }

    public function capturarDatosAnteriores($metodoConsultaPrevia)
    {
        $respuestaPrevia = $this->modelo->realizar_consulta($metodoConsultaPrevia);
        if ($respuestaPrevia['estatus'] && !empty($respuestaPrevia['datos'])) {
            $this->datosAnteriores = $this->limpiarArreglo($respuestaPrevia['datos']);
        }
    }

    public function registrarAuditoria($operacion)
    {
        // Si la operación es consultar, verificamos si debemos registrarla
        if ($operacion === 'consultar') {
            $llaveSesion = 'auditar_' . $this->moduloId;
            $debeAuditar = $_SESSION[$llaveSesion] ?? true;

            // Si la bandera está apagada, salimos sin hacer nada
            if (!$debeAuditar) {
                return; 
            }
            
            // Apagamos la bandera para que las recargas automáticas de AJAX no hagan spam
            $_SESSION[$llaveSesion] = false;
        }
        // -------------------------------

        $nuevo = null;
        $anterior = $this->datosAnteriores;

        if (in_array($operacion, ['registrar', 'modificar'])) {
            $nuevo = $this->extraerDatosDelModelo();
        }

        if ($operacion === 'modificar' && is_array($anterior) && is_array($nuevo)) {
            $this->calcularDiferencias($anterior, $nuevo);
        }

        $partesOperacion = explode('_', $operacion);
        $accionBitacora = strtoupper($partesOperacion[0]); 

        if ($operacion !== 'modificar' || ($anterior !== null || $nuevo !== null)) {
            Bitacora::registrar($accionBitacora, $this->moduloId, null, $anterior, $nuevo);
        }
    }

    private function calcularDiferencias(&$anterior, &$nuevo)
    {
        $soloAnteriores = [];
        $soloNuevos = [];

        foreach ($nuevo as $clave => $valorNuevo) {
            if (array_key_exists($clave, $anterior)) {
                $valorAnterior = $anterior[$clave];
                
                if ((string)$valorNuevo !== (string)$valorAnterior) {
                    $soloAnteriores[$clave] = $valorAnterior;
                    $soloNuevos[$clave] = $valorNuevo;
                }
            } else {
                $soloNuevos[$clave] = $valorNuevo;
            }
        }

        $anterior = empty($soloAnteriores) ? null : $soloAnteriores;
        $nuevo = empty($soloNuevos) ? null : $soloNuevos;
    }

    private function extraerDatosDelModelo()
    {
        $datos = [];
        $metodos = get_class_methods($this->modelo);

        foreach ($metodos as $metodo) {
            if (strpos($metodo, 'get_') === 0) {
                $propiedad = substr($metodo, 4);

                if ($propiedad === 'activo' || strpos($propiedad, 'id_') === 0 || substr($propiedad, -3) === '_id') {
                    continue; 
                }

                $valor = $this->modelo->$metodo();

                if ($valor !== null) {
                    $datos[$propiedad] = $valor;
                }
            }
        }
        return $datos;
    }

    private function limpiarArreglo($datosBd)
    {
        $limpios = [];
        foreach ($datosBd as $clave => $valor) {
            if ($clave === 'activo' || strpos($clave, 'id_') === 0 || substr($clave, -3) === '_id') {
                continue;
            }
            $limpios[$clave] = $valor;
        }
        return $limpios;
    }


}