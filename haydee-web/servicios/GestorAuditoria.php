<?php
namespace haydee\servicios;

use haydee\enums\Accion;
use haydee\enums\Modulo; 
use haydee\modelo\Bitacora;

class GestorAuditoria
{
    private const PREFIJO_SESION = 'auditar_';
    private const SUFIJO_ID = 'id_';
    private const SUFIJO_FK = '_id';

    private $modelo;
    private Modulo $modulo; // Tipamos fuertemente la propiedad
    private $datosAnteriores = null;

    public function __construct($modelo, Modulo $modulo)
    {
        $this->modelo = $modelo;
        $this->modulo = $modulo;
    }

    /**
     * Prepara la sesión para auditar la próxima vez que se consulte la tabla.
     */
    public static function inicializarBanderaConsulta(Modulo $modulo)
    {
        $_SESSION[self::PREFIJO_SESION . $modulo->value] = true;
    }

    public function capturarDatosAnteriores($metodoConsultaPrevia)
    {
        $respuestaPrevia = $this->modelo->realizar_consulta($metodoConsultaPrevia);
        if ($respuestaPrevia['estatus'] && !empty($respuestaPrevia['datos'])) {
            $this->datosAnteriores = $this->limpiarArreglo($respuestaPrevia['datos']);
        }
    }

    public function registrarAuditoria(Accion $accion)
    {
        if ($accion === Accion::CONSULTAR) {
            $llaveSesion = self::PREFIJO_SESION . $this->modulo->value;
            $debeAuditar = $_SESSION[$llaveSesion] ?? true;

            if (!$debeAuditar) return; 
            
            $_SESSION[$llaveSesion] = false;
        }

        $nuevo = null;
        $anterior = $this->datosAnteriores;

        if (in_array($accion, [Accion::REGISTRAR, Accion::MODIFICAR], true)) {
            $nuevo = $this->extraerDatosDelModelo();
        }

        if ($accion === Accion::MODIFICAR && is_array($anterior) && is_array($nuevo)) {
            $this->calcularDiferencias($anterior, $nuevo);
        }

        // AHORA: Pasamos las instancias puras
        if ($accion !== Accion::MODIFICAR || ($anterior !== null || $nuevo !== null)) {
            Bitacora::registrar($accion, $this->modulo, null, $anterior, $nuevo);
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

                if ($propiedad === 'activo' || strpos($propiedad, self::SUFIJO_ID) === 0 || substr($propiedad, -strlen(self::SUFIJO_FK)) === self::SUFIJO_FK) {
                    continue; 
                }

                $valor = $this->modelo->$metodo();
                if ($valor !== null) $datos[$propiedad] = $valor;
            }
        }
        return $datos;
    }

    private function limpiarArreglo($datosBd)
    {
        $limpios = [];
        foreach ($datosBd as $clave => $valor) {
            if ($clave === 'activo' || strpos($clave, self::SUFIJO_ID) === 0 || substr($clave, -strlen(self::SUFIJO_FK)) === self::SUFIJO_FK) {
                continue;
            }
            $limpios[$clave] = $valor;
        }
        return $limpios;
    }
}