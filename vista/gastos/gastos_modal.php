<form action="?pagina=gastos_controlador.php&accion=guardar" method="POST" id="form_gastos" name="form_cartelera"
    enctype="multipart/form-data">
    <div class="container mt-4">

        <!-- Fila 1: Fecha + Monto -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fecha">Fecha del Gasto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" class="form-control" name="fecha" id="fecha" required>
                </div>
            </div>
            <div class="col-md-6">
                <label for="metodo_pago">Método de Pago</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                    <select class="form-select" name="metodo_pago" id="metodo_pago" required>
                        <option value="" disabled selected>Seleccione un método</option>
                        <option value="pago_movil">Pago Movil</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="efectivo">Efectivo ($)</option>
                    </select>
                </div>
            </div>
        </div>



        <!-- Fila 2: Tipo de Gasto + Método de Pago -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="tipo_gasto">Tipo de Gasto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                    <select class="form-select" name="tipo_gasto" id="tipo_gasto" required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                        <option value="fijo">Fijo</option>
                        <option value="variable">Variable</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label for="monto">Monto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                    <input type="text" inputmode="decimal" pattern="^[0-9]+([.,][0-9]{1,2})?$" class="form-control"
                        name="monto" id="monto" required>
                </div>
                <div class="invalid-feedback" id="mensaje_monto"></div>
            </div>
        </div>

        <!-- Fila 3: Referencia + Banco (solo si NO es efectivo) -->
        <div class="row mb-3">
            <div class="col-md-6" id="grupo_referencia">
                <label for="referencia">Referencia/N° Comprobante</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                    <input type="text" class="form-control" name="referencia" id="referencia">
                </div>
            </div>
            <div class="col-md-6" id="grupo_banco">
                <label for="banco">Banco</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                    <select class="form-select" name="banco" id="banco">
                        <option value="" disabled selected>Seleccione un banco</option>
                        <?php foreach ($bancos as $banco): ?>
                            <option value="<?php echo $banco["id_banco"] ?>"><?php echo $banco["nombre_banco"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Fila 4: Proveedor -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="proveedor">Proveedor</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <select class="form-select" name="proveedor" id="proveedor" required>
                        <option value="" disabled selected>Seleccione un proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?php echo $proveedor["id_proveedor"] ?>">
                                <?php echo $proveedor["nombre_proveedor"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Fila 5: Descripción -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="descripcion">Descripción del Gasto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3"
                        placeholder="Describa el gasto realizado..." required></textarea>
                </div>
            </div>
        </div>

        <!-- Fila 6: Imagen (solo si NO es efectivo) -->
        <div class="row mb-3" id="grupo_imagen">
            <div class="col-md-12">
                <label for="imagen">Comprobante (Imagen)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-file-earmark-image"></i></span>
                    <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*">
                </div>
                <small id="nombre_imagen_cargada" class="text-muted fst-italic d-block mt-1"></small>
                <button type="button" id="boton_eliminar_imagen" class="btn btn-sm btn-outline-danger mt-2 d-none">
                    <i class="bi bi-trash3"></i> Eliminar imagen cargada
                </button>
            </div>
        </div>

        <!-- Botón -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
            </div>
        </div>
    </div>
</form>