<div class="modal fade" id="modal_gastos" tabindex="-1" aria-labelledby="titulo-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content card shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5">
                    <i class="bi bi-cart-check" id="icono_titulo_modal"></i>
                    <span class="ms-2" id="titulo_modal">Registrar Gasto</span>
                </h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-link text-white p-1 me-3 lh-1 d-md-none" id="btn_ayuda_modal" title="Ayuda de este formulario" data-tooltip="true">
                        <i class="bi bi-question-circle-fill fs-4"></i>
                    </button>
                    
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body rounded-bottom">
                <form id="form_gastos" name="form_cartelera" enctype="multipart/form-data">
                    <div class="container mt-4">
                        <div class="row mb-3">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="clasificacion" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="form-select" name="clasificacion" id="clasificacion" required>
                                        <option value="" selected hidden>Seleccione un tipo</option>
                                        <option value="FIJO">Fijo</option>
                                        <option value="VARIABLE">Variable</option>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label class="form-label fw-semibold" for="concepto_id">Concepto del Gasto <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="form-select" name="concepto_id" id="concepto_id" required>
                                        <option value="" selected hidden>Seleccione el concepto</option>
                                        <?php 
                                        $tipo_actual = "";
                                        foreach ($conceptos_gasto['datos'] as $concepto): 
                                            // Si el tipo de gasto cambia respecto al anterior, abrimos un nuevo grupo
                                            if ($tipo_actual != $concepto["nombre_tipo_gasto"]) {
                                                // Si no es la primera iteración, cerramos el grupo anterior
                                                if ($tipo_actual != "") {
                                                    echo "</optgroup>";
                                                }
                                                $tipo_actual = $concepto["nombre_tipo_gasto"];
                                                echo "<optgroup label='" . htmlspecialchars($tipo_actual) . "'>";
                                            }
                                            
                                            // por si hay un tipo de gasto vacío
                                            if (!empty($concepto["id_concepto"])):
                                        ?>
                                                <option value="<?php echo $concepto["id_concepto"]; ?>">
                                                    <?php echo $concepto["nombre_concepto"]; ?>
                                                </option>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        if ($tipo_actual != "") echo "</optgroup>"; 
                                        ?>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 3: Descripción general del gasto -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="descripcion_gasto" class="form-label fw-semibold">Descripción del Gasto <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="descripcion_gasto" id="descripcion_gasto" class="form-control" rows="3"
                                        placeholder="Describa el detalle del gasto realizado..." minlength="10" required></textarea>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 4: Proveedor + Solicitud -->
                        <div class="row mb-3">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="proveedor_id" class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="form-select" name="proveedor_id" id="proveedor_id" required>
                                        <option value="" selected hidden>Seleccione un proveedor</option>
                                        <?php foreach ($proveedores['datos'] as $proveedor): ?>
                                            <option value="<?php echo $proveedor["id_proveedor"] ?>">
                                                <?php echo $proveedor["nombre_proveedor"] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="presupuesto_id" class="form-label fw-semibold">Presupuesto Base <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                    <select class="form-select" name="presupuesto_id" id="presupuesto_id" required>
                                        <option value="" selected hidden>Seleccione un presupuesto</option>
                                        <?php foreach ($presupuestos['datos'] as $presupuesto): ?>
                                            <option value="<?php echo $presupuesto["id_presupuesto"] ?>">
                                                <?php echo "Presupuesto del " . date("d/m/Y", strtotime($presupuesto["fecha"])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="w-100 invalid-feedback"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del Gasto -->
                        <div id="detalles-container">
                            <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
                                <h5 class="card-title text-primary fw-bold mb-3">
                                    <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
                                </h5>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-5 col-md-6 mb-3">
                                            <label for="metodo_pago" class="form-label fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                                <select class="form-select metodo_pago" name="metodo_pago[]" required>
                                                    <option value="" selected hidden>Seleccione un método</option>
                                                    <option value="PAGO MOVIL">Pago Móvil</option>
                                                    <option value="TRANSFERENCIA">Transferencia</option>
                                                    <option value="EFECTIVO">Efectivo ($)</option>
                                                </select>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-7 col-md-6 mb-3 grupo_imagen d-none">
                                            <label class="form-label text-primary fw-bold"><i class="bi bi-magic me-1"></i> Subir Comprobante (Autocompletar) <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text border-primary text-primary"><i class="bi bi-file-earmark-image"></i></span>
                                                <input type="file" class="form-control imagen border-primary" name="imagen[]" accept="image/*">
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <small class="text-muted fst-italic d-block mt-1 nombre_imagen_cargada"></small>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label for="fecha_detalle" class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                                <input type="date" class="form-control fecha_detalle" name="fecha_detalle[]" required>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label for="monto" class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$" class="form-control monto" name="monto[]" placeholder="Ej: 250.00" required>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <div class="invalid-feedback" id="mensaje_monto"></div>
                                        </div>
                                    </div>

                                    <div class="row g-3 grupo_bancario d-none mt-1">
                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Cuenta Origen <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                                <select class="form-select cuenta" name="cuenta_id[]">
                                                    <option value="" selected>Seleccione una cuenta</option>
                                                    <?php foreach ($cuentas['datos'] as $cta): 
                                                        $codigoBanco = isset($cta["codigo"]) ? $cta["codigo"] : substr($cta["numero_cuenta"], 0, 4);
                                                    ?>
                                                        <option value="<?php echo $cta["id_cuenta"] ?>" data-codigo="<?php echo $codigoBanco; ?>">
                                                            <?php echo $cta["nombre_banco"] . ' - ' . $cta["numero_cuenta"] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label for="referencia" class="form-label fw-semibold">Referencia/N° <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                                                <input type="text" class="form-control referencia" minlength="4" maxlength="20" name="referencia[]" placeholder="Nro Operación">
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para agregar detalle -->
                        <div class="row mb-4">
                            <div class="col-lg-5">
                                <button type="button" class="btn btn-soft-success w-100" id="agregar_detalle">
                                    <i class="bi bi-plus-circle"></i> Agregar Detalle de Gasto
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-soft-secondary me-3 mb-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </button>
                    <button class="btn btn-primary px-3 shadow-sm mb-2" type="submit" id="boton_formulario">
                        <i class="bi bi-check2-circle me-2"></i>
                        <span id="texto_boton_formulario">Guardar Gasto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="plantilla-detalle-gasto">
    <div class="detalle-gasto card border-0 shadow-sm p-4 mb-4">
        <h5 class="card-title text-primary fw-bold mb-3">
            <i class="bi bi-receipt-cutoff me-2"></i> Detalles del Gasto
        </h5>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="fecha_detalle" class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control fecha_detalle" name="fecha_detalle[]" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="metodo_pago" class="form-label fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                        <select class="form-select metodo_pago" name="metodo_pago[]" required>
                            <option value="" selected hidden>Seleccione un método</option>
                            <option value="PAGO MOVIL">Pago Móvil</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                            <option value="EFECTIVO">Efectivo ($)</option>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 mb-3 grupo_imagen d-none">
                    <label class="form-label text-primary fw-bold"><i class="bi bi-magic me-1"></i> Subir Comprobante <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text border-primary text-primary"><i class="bi bi-file-earmark-image"></i></span>
                        <input type="file" class="form-control imagen border-primary" name="imagen[]" accept="image/*">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                    <small class="text-muted fst-italic d-block mt-1 nombre_imagen_cargada"></small>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-lg-4 col-md-4 mb-3">
                    <label for="monto" class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$" class="form-control monto" name="monto[]" placeholder="Ej: 250.00" required>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                    <div class="invalid-feedback" id="mensaje_monto"></div>
                </div>

                <div class="col-lg-4 col-md-4 mb-3 grupo_bancario d-none">
                    <label class="form-label fw-semibold">Cuenta Origen <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-bank"></i></span>
                        <select class="form-select cuenta" name="cuenta_id[]">
                            <option value="" selected>Seleccione una cuenta</option>
                            <?php foreach ($cuentas['datos'] as $cta): ?>
                                <option value="<?php echo $cta["id_cuenta"] ?>">
                                    <?php echo $cta["nombre_banco"] . ' - ' . $cta["numero_cuenta"] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 mb-3 grupo_bancario d-none">
                    <label for="referencia" class="form-label fw-semibold">Referencia/N° <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                        <input type="text" class="form-control referencia" minlength="4" maxlength="20" name="referencia[]" placeholder="Nro Operación">
                        <span class="w-100 invalid-feedback"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>