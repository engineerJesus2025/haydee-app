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
    private Modulo $modulo;
    private $datosAnteriores = null;

    public function __construct($modelo, Modulo $modulo)
    {
        $this->modelo = $modelo;
        $this->modulo = $modulo;
    }

    public static function inicializarBanderaConsulta(Modulo $modulo)
    {
        $_SESSION[self::PREFIJO_SESION . $modulo->value] = true;
    }

    public function capturarDatosAnteriores($metodoConsultaPrevia)
    {
        $respuestaPrevia = $this->modelo->realizar_consulta($metodoConsultaPrevia);
        if ($respuestaPrevia['estatus'] && !empty($respuestaPrevia['datos'])) {
            $datos = $respuestaPrevia['datos'];

            // 1. Si la BD trajo detalles y el modelo sabe resumir, generamos el resumen previo
            if (method_exists($this->modelo, 'resumirDetalles') && isset($datos['detalles'])) {
                $datos['resumen_detalles'] = $this->modelo->resumirDetalles($datos['detalles']);
            }

            // 2. Limpiamos (limpiarArreglo se encargará de eliminar 'detalles' crudo)
            $this->datosAnteriores = $this->limpiarArreglo($datos);
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
                
                // Si el valor es un arreglo (ej: resumen_detalles), comparamos sus JSON
                $strNuevo = is_array($valorNuevo) ? json_encode($valorNuevo) : (string)$valorNuevo;
                $strAnterior = is_array($valorAnterior) ? json_encode($valorAnterior) : (string)$valorAnterior;

                if ($strNuevo !== $strAnterior) {
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

                // Filtros estándar de IDs, llaves foráneas y activos
                if ($propiedad === 'activo' 
                    || $propiedad === 'resumen_detalles'
                    || strpos($propiedad, self::SUFIJO_ID) === 0 
                    || substr($propiedad, -strlen(self::SUFIJO_FK)) === self::SUFIJO_FK) {
                    continue; 
                }

                $valor = $this->modelo->$metodo();

                // Si el valor es nulo o es un array (como datos_apartamentos o detalles crudos), lo ignoramos
                if ($valor !== null && !is_array($valor)) {
                    $datos[$propiedad] = $valor;
                }
            }
        }

        if (method_exists($this->modelo, 'resumirDetalles') && method_exists($this->modelo, 'get_detalles')) {
            $detallesActuales = $this->modelo->get_detalles();
            if (!empty($detallesActuales)) {
                $datos['resumen_detalles'] = $this->modelo->resumirDetalles($detallesActuales);
            }
        }

        return $datos;
    }

    private function limpiarArreglo($datosBd)
    {
        $limpios = [];
        foreach ($datosBd as $clave => $valor) {
            if ($clave === 'activo' 
                || strpos($clave, self::SUFIJO_ID) === 0 
                || substr($clave, -strlen(self::SUFIJO_FK)) === self::SUFIJO_FK) {
                continue;
            }

            if (is_array($valor) && $clave !== 'resumen_detalles') {
                continue;
            }

            $limpios[$clave] = $valor;
        }
        return $limpios;
    }
}