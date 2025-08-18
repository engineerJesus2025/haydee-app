<div class="modal fade" id="modal_gastos_mensual" tabindex="-1" aria-labelledby="titulo_modal_gastos" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="titulo_modal_gastos">Generar Relación de Gastos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione el período para el reporte.</p>
                <form method="POST" action="?pagina=reportes_controlador.php&accion=generar_reporte_gastos_mensual" target="_blank" id="form_gastos_mensual">
                    <div class="mb-3">
                        <label for="mes" class="form-label">Mes:</label>
                        <select class="form-select" id="mes" name="mes" required>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="anio" class="form-label">Año:</label>
                        <input type="number" class="form-control" id="anio" name="anio" value="<?php echo date('Y'); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" form="form_gastos_mensual">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>