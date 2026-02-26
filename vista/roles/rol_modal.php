<form id="form_rol">
    <div class="col-md-12">
        <label for="nombre">Nombre del rol</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-gear"></i></span>
            <input type="text" class="form-control nombre_rol" name="nombre" id="nombre" placeholder="Ejem: Contador" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
            <span class="w-100 invalid-feedback"></span>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tabla_permisos" class="table">
            <thead>
                <tr>
                    <th>MÓDULOS</th>
                    <th>PERMISOS</th>
                </tr>
            </thead>
            <tbody>
                <?php $contador = 1; ?>
                <?php foreach ($registros_modulos as $registro_modulo) : ?>
                    <tr data-modulo="<?php echo $registro_modulo['id_modulo'] ?>">
                        <td>
                            <span> <?php echo str_replace("_", " ", $registro_modulo["nombre"]); ?></span>
                            <div class="form-check mt-3 text-muted">
                                <input type="checkbox" class="seleccionar_todo form-check-input" id="checkbox-<?php echo $contador ?>">
                                <label class="form-check-label" for="checkbox-<?php echo $contador ?>">Seleccionar Todo</label>
                            </div>
                        </td>
                        <td>
                            <div class="accordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-<?php echo $contador; ?>" aria-expanded="true" aria-controls="collapseOne" id="accordionExample-<?php echo $contador; ?>">
                                            PERMISOS
                                        </button>
                                    </h2>
                                    <div id="collapseOne-<?php echo $contador; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample-<?php echo $contador; ?>">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <?php 
                                                foreach ($registros_permisos_usuarios as $registro_permiso) { ?>
                                                        <div class="col">
                                                            <label>
                                                                <input class="form-check-input" type="checkbox" name="permisos[]" value="<?php echo $registro_permiso["id_permiso"]; ?>" error='0'/>
                                                                <span> <?php echo $registro_permiso["accion"] ?></span>
                                                            </label>
                                                        </div>
                                                    <?php } ?>
                                                <span style="width: 100%; margin-top: .25rem;font-size: .875em; color: var(--bs-form-invalid-color);"></span>
                                            </div>
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
    <div class="col-md-12 text-center">
        <button class="btn btn-primary" type="submit" id="boton_formulario">Guardar</button>
    </div>
</form>