<form action="?pagina=rol_controlador.php&accion=guardar" method="POST" id="form_rol_registrar">
    <div class="col-md-12">
        <label for="nombre">Nombre del rol</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-gear"></i></span>
            <input type="text" class="form-control nombre_rol" name="nombre" placeholder="Nombre del rol" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
            <span class="w-100"></span>
        </div>
    </div>

    <table id="permiso" class="table" style="width:100%">
        <thead>
            <tr>
                <th>MÓDULOS</th>
                <th>PERMISOS</th>
            </tr>
        </thead>
        <tbody>
            <?php $contador = 1; ?>
            <?php foreach ($registros_modulos as $registro_modulo) : ?>
                <tr>
                    
                    <td>
                        <p>
                            <label>
                                <span> <?php echo $registro_modulo["nombre"] ?></span>
                            </label>
                        </p>
                    </td>
                    <td>
                        <div class="accordion" id="accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-<?php echo $contador; ?>" aria-expanded="true" aria-controls="collapseOne" id="accordionExample-<?php echo $contador; ?>">
                                        PERMISOS
                                    </button>
                                </h2>
                                <div id="collapseOne-<?php echo $contador; ?>" class="accordion-collapse collapse show" data-bs-parent="#accordionExample-<?php echo $contador; ?>">
                                    <div class="accordion-body">

                                        <div class="input-field col s12 m6 left-align">
                                            <?php foreach (Ayuda::filtrarArray($registros_permisos_usuarios, 'modulo_id', $registro_modulo["id_modulo"])  as $permiso) : ?>
                                                <p>
                                                    <label>
                                                        <input class="form-check-input" type="checkbox" id="" name="permisos[]" value=" <?php echo $permiso["id_permiso_usuario"]; ?>" />
                                                        <span> <?php echo $permiso["nombre"] ?></span>
                                                    </label>
                                                </p>
                                            <?php endforeach; ?>
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
    <div class="col-md-12 text-center">
        <button class="btn color-principal" type="submit" id="envio">Registrar</button>
    </div>
</form>