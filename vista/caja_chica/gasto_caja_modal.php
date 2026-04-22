<div class="modal fade" id="modal_registro_gastos" tabindex="-1" aria-labelledby="titulo_modal_registro_gasto" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="titulo_modal_registro_gasto">Registrar Gasto de Caja</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row m-3" id="form_registro_gasto">
                    <div class="col-lg-8">
                       <label for="fecha">Fecha del gasto: <spam class="text-danger">*</spam></label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-calendar"></i></span>
                            <input type="date" class="border border-dark rounded-end form-control" name="fecha" id="fecha"  aria-label="fecha" aria-describedby="basic-addon1" autofocus>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>    
                    <div class="col-lg-5">
                        <label for="monto">Monto del gasto: <spam class="text-danger">*</spam></label>
                        <div class="input-group mb-3">
                            <input type="number" name="monto" id="monto" class="border border-dark form-control" maxlength="15" minlength="0" title="Monto del gasto en bolivares" monto="bs" placeholder="Monto">
                            <span class="border border-primary rounded-end input-group-text icono_moneda" id="spam_icono_moneda_cuota">Bs.</span>
                            <span class="w-100 invalid-feedback"></span>
                        </div>
                    </div>
                    <div class="col-2 d-flex justify-content-center align-items-center mt-2">
                        <button class="btn btn-outline-info boton_intercambio_monto" id="boton_intercambio_monto" title="Presione para cambiar el tipo de moneda">
                            <span><i class="bi bi-arrow-left-right"></i></span>
                        </button>            
                    </div>
                    <div class="col-lg-5 col-10 text-center">
                        <label for="monto_cambio"></label>
                        <div class="input-group mb-3">
                            <input type="text" name="monto_cambio" id="monto_cambio" minlength="0" value="0" class="form-control" maxlength="15" disabled title="Monto del gasto en dolares">
                            <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota_cambio">$</span>
                        </div>
                    </div>
                    <div class="col-12">
                       <p class="text-muted">Fondos en caja: <b id="fondos_caja"></b></p>    
                    </div>
                    <div class="col-12">
                       <p class="text-muted">Fondos restantes: <b id="fondos_restante"></b></p>    
                    </div>
                    <div class="col-12">        
                        <label for="concepto">Concepto: <spam class="text-danger">*</spam></label>
                        <div class="input-group mb-3">
                            <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                            <input type="text" class="border border-dark rounded-end form-control" name="concepto" id="concepto" placeholder="Ingrese una descripción del gasto" aria-label="concepto" aria-describedby="basic-addon1" minlength="3">
                            <span class="w-100 invalid-feedback"></span>
                        </div>        
                    </div>
                </div>
                <div class="row m-3">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary" type="submit" id="boton_gasto_caja">Guardar</button>
                    </div>
                </form >
            </div>
        </div>
    </div>
</div>