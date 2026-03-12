<?php
// Diccionario de iconos basado en tu navbar.php
$iconos_modulos = [
    'pagos'             => 'bi-cash-coin',
    'gastos'            => 'bi-cart-plus',
    'caja_chica'        => 'bi-bank2',
    'mensualidad'       => 'bi-piggy-bank-fill',
    'cartelera_virtual' => 'bi-tv',
    'apartamentos'      => 'bi-door-open',
    'solicitud_gasto'   => 'bi-clipboard-check',
    'presupuesto'       => 'bi-calculator',
    'anio_fiscal'       => 'bi-calendar-range',
    'reportes'          => 'bi-card-checklist',
    'configuracion'     => 'bi-gear-wide-connected',
    'proveedores'       => 'bi-truck',
    'bancos'            => 'bi-bank',
    'tipo_gasto'        => 'bi-columns-gap',
    'usuarios'          => 'bi-person-badge-fill',
    'seguridad'         => 'bi-shield-fill-check',
    'rol'               => 'bi-person-gear',
    'roles'             => 'bi-person-gear',
    'bitacora'          => 'bi-journal-text',
    'permisos'          => 'bi-key-fill',
    'modulos'           => 'bi-stack',
    'notificaciones'    => 'bi-bell-fill',
    'mantenimiento'     => 'bi-tools'
];
?>
<form id="form_rol">
    <div class="row mb-4">
        <div class="col-md-12">
            <label for="nombre">Nombre del rol <spam class="text-danger">*</spam></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-gear"></i></span>
                <input type="text" class="form-control nombre_rol" name="nombre" id="nombre" placeholder="Ejem: Contador" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="table-responsive shadow-sm border rounded" style="max-height: 500px; overflow-y: auto;">
        <table id="tabla_permisos" class="table table-hover align-top mb-0">
            <thead class="table-light sticky-top shadow-sm" style="z-index: 2;">
                <tr>
                    <th style="width: 35%;" class="py-3 text-secondary text-uppercase" scope="col">
                        <i class="bi bi-grid-1x2 me-2"></i>Módulo del Sistema
                    </th>
                    <th style="width: 65%;" class="py-3 text-secondary text-uppercase" scope="col">
                        <i class="bi bi-ui-checks me-2"></i>Configuración de Accesos
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php $contador = 1; ?>
                <?php foreach ($registros_modulos as $registro_modulo) : ?>
                    
                    <?php 
                        // Preparar el nombre e icono
                        $nombre_bd = strtolower(str_replace("GESTIONAR_","",$registro_modulo['nombre']))    ;
                        $icono_modulo = isset($iconos_modulos[$nombre_bd]) ? $iconos_modulos[$nombre_bd] : 'bi-folder2-open';
                        $nombre_mostrar = str_replace("_", " ", $registro_modulo["nombre"]);
                    ?>

                    <tr data-modulo="<?php echo $registro_modulo['id_modulo'] ?>">
                        <td>
                            <div class="d-flex flex-column gap-2 py-2 ps-2">
                                <span class="fw-bold text-uppercase text-primary d-flex align-items-center" style="letter-spacing: 0.5px;">
                                    <div class="p-2 text-primary me-2">
                                        <i class="bi <?php echo $icono_modulo; ?> fs-5"></i>
                                    </div>
                                    <?php echo $nombre_mostrar; ?>
                                </span>
                                
                                <div class="form-check form-switch ms-1 mt-1">
                                    <label class="d-flex align-items-center text-nowrap text-muted user-select-none" style="cursor: pointer; font-size: 0.9rem;">
                                        <input type="checkbox" class="seleccionar_todo form-check-input me-2 shadow-none" id="checkbox-<?php echo $contador ?>" style="cursor: pointer;">
                                        <span>Seleccionar todo el módulo</span>
                                    </label>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="accordion accordion-flush" id="accordionParent-<?php echo $contador; ?>">
                                <div class="accordion-item border rounded">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse-<?php echo $contador; ?>" 
                                                aria-expanded="false"
                                                style="box-shadow: none;">
                                            <span class="text-secondary fw-semibold" style="font-size: 1.10rem;">
                                                <i class="bi bi-shield-lock me-2"></i>
                                                Desplegar permisos específicos
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse-<?php echo $contador; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionParent-<?php echo $contador; ?>">
                                        <div class="accordion-body bg-white py-0">
                                            <div class="d-flex flex-wrap gap-4 justify-content-between">
                                                <?php foreach ($registros_permisos_usuarios as $registro_permiso) : ?>
                                                    <div class="form-check m-0">
                                                        <label class="d-flex align-items-center text-nowrap py-1 user-select-none  permiso-item" style="cursor: pointer;">
                                                            <input class="form-check-input me-2 shadow-none border-secondary" type="checkbox" 
                                                                   name="permisos[]" 
                                                                   value="<?php echo $registro_permiso["id_permiso"]; ?>" 
                                                                   error='0'
                                                                   style="margin-top: 0; cursor: pointer;"/>
                                                            <span class="text-capitalize text-dark"><?php echo $registro_permiso["accion"] ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="invalid-feedback d-block mt-2" style="font-size: 0.8rem;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php $contador++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="col-md-12 text-end mt-4 pt-3 border-top">
        <button class="btn btn-primary px-4" type="submit" id="boton_formulario">
            <i class="bi bi-floppy me-1"></i> Guardar Rol
        </button>
    </div>
</form>