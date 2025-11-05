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
                        <label for="anio_reporte" class="form-label">Año:</label>
                        <select class="form-select" id="anio_reporte" name="anio" required>
                            <option value="">Seleccione un año...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mes_reporte" class="form-label">Mes:</label>
                        <select class="form-select" id="mes_reporte" name="mes" required disabled>
                            <option value="">Seleccione un mes...</option>
                        </select>
                    </div>
                        <input type="hidden" id="tasa_dolar_reporte" name="tasa_dolar">

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" form="form_gastos_mensual" id="btn_generar_reporte_gastos" disabled>
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>