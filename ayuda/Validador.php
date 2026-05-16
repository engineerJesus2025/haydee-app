<?php
namespace haydee\ayuda;

use DateTime;
use haydee\ayuda\ValidadorBD;

/**
 * Clase Validador
 * Centraliza la validación de datos del Backend leyendo las reglas de los modelos.
 */
class Validador {
    private const FORMATO_FECHA_HORA = 'Y-m-d H:i:s';
    
    private $errores = [];
    private $error_404 = false;
    private $validadorBD = null;

    public function tieneError404() {
        return $this->error_404;
    }

    /**
     * PATRÓN LAZY INITIALIZATION (Carga Perezosa)
     * Solo instancia ValidadorBD (y conecta a MySQL) cuando realmente se necesita.
     */
    private function obtenerValidadorBD() {
        if ($this->validadorBD === null) {
            $this->validadorBD = new ValidadorBD();
        }
        return $this->validadorBD;
    }

    public function tieneErrores() {
        return count($this->errores) > 0;
    }

    public function obtenerErrores() {
        return $this->errores;
    }

    private function agregarError($campo, $mensaje) {
        $this->errores[$campo][] = $mensaje;
    }

    /**
     * Procesa los datos contra el arreglo de reglas del modelo.
     */
    public function validarConjunto($datos, $reglas, $contexto = []) {
        foreach ($reglas as $campo => $regla) {
            $valor = $datos[$campo] ?? null;
            $tieneErrorDeFormato = false;

            // 1. Evaluar si es requerido
            $esRequerido = $this->evaluarSiEsRequerido($regla, $datos);

            if ($esRequerido) {
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    $this->agregarError($campo, "El campo '$campo' es obligatorio.");
                    continue;
                }
            } else {
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue; // Es opcional y está vacío
                }
            }

            // 2. Validación Regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                $this->agregarError($campo, "El formato del campo '$campo' es inválido.");
                $tieneErrorDeFormato = true;
            }

            // 3. Validación DateTime
            if (isset($regla['type']) && $regla['type'] === 'datetime') {
                $d = DateTime::createFromFormat(self::FORMATO_FECHA_HORA, $valor);
                if (!($d && $d->format(self::FORMATO_FECHA_HORA) === $valor)) {
                    $this->agregarError($campo, "El campo '$campo' debe ser una fecha/hora válida.");
                    $tieneErrorDeFormato = true;
                }
            }

            // 4. Validación Min / Max
            if (isset($regla['min']) && is_numeric($valor) && $valor < $regla['min']) {
                $this->agregarError($campo, "El campo '$campo' debe ser mayor o igual a {$regla['min']}.");
                $tieneErrorDeFormato = true;
            }
            if (isset($regla['max']) && is_numeric($valor) && $valor > $regla['max']) {
                $this->agregarError($campo, "El campo '$campo' debe ser menor o igual a {$regla['max']}.");
                $tieneErrorDeFormato = true;
            }

            // Validación de Rango de Fechas (Asegurar que una fecha sea mayor a otra)
            if (isset($regla['fecha_posterior_a'])) {
                $campoAnterior = $regla['fecha_posterior_a'];
                $valorAnterior = $datos[$campoAnterior] ?? null;

                // Solo comparamos si ambos campos tienen valor y tienen formato correcto
                if (!empty($valor) && !empty($valorAnterior) && !$tieneErrorDeFormato) {
                    $fecha1 = strtotime($valorAnterior);
                    $fecha2 = strtotime($valor);

                    if ($fecha1 && $fecha2 && $fecha2 <= $fecha1) {
                        $this->agregarError($campo, "La fecha en '$campo' debe ser posterior a la fecha de '$campoAnterior'.");
                        $tieneErrorDeFormato = true;
                    }
                }
            }

            // Si hay error de formato, abortamos ir a la base de datos para ahorrar recursos
            if ($tieneErrorDeFormato) {
                continue; 
            }

            // 5. Validación EXISTS
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoBd = $regla['exists']['campo'] ?? $campo;
                
                if (!$this->obtenerValidadorBD()->existe($tabla, $campoBd, $valor)) {
                    $this->agregarError($campo, "El valor indicado en '$campo' no existe en el sistema.");
                    
                    if (strpos($campo, 'id_') === 0) {
                        $this->error_404 = true;
                    }
                }
            }

            // 6. Validación UNIQUE
            if (isset($regla['unique']) && !isset($contexto['skip_unique'])) {
                $tabla = $regla['unique']['tabla'];
                $campoBd = $regla['unique']['campo'] ?? $campo;
                $excludeField = $regla['unique']['exclude_field'] ?? null;
                $excludeValue = $contexto['exclude_id'] ?? null;
                
                if (!$this->obtenerValidadorBD()->esUnico($tabla, $campoBd, $valor, $excludeField, $excludeValue)) {
                    $this->agregarError($campo, "El '$campo' ya se encuentra registrado.");
                }
            }
        }
    }

    /**
     * Evalúa si el campo es obligatorio tomando en cuenta dependencias ("requerido_si")
     */
    private function evaluarSiEsRequerido($regla, $datos) {
        $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);

        if (isset($regla['requerido_si'])) {
            foreach ($regla['requerido_si'] as $campoCondicion => $valoresCondicion){
                $valorActualCondicion = $datos[$campoCondicion] ?? null;
                if (in_array($valorActualCondicion, $valoresCondicion)) {
                    $requerido = true;
                    break;
                }
            }
        }
        return $requerido;
    }
}
