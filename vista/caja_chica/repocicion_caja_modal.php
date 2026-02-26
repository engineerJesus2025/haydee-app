<form class="row m-3" id="form_registro_gasto">
    <div class="col-lg-5">
        <label for="monto_reponer">Monto del gasto:</label>
        <div class="input-group mb-3">
            <input type="number" name="monto_reponer" id="monto_reponer" class="form-control" maxlength="15" minlength="0" title="Monto del gasto en bolivares" monto="bs" placeholder="Monto">
            <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota">Bs.</span>
            <span class="w-100 invalid-feedback"></span>
        </div>
    </div>
    <div class="col-2 d-flex justify-content-center align-items-center mt-2">
        <button class="btn btn-outline-info boton_intercambio_monto" id="boton_intercambio_monto_reponer" title="Presione para cambiar el tipo de moneda">
            <span><i class="bi bi-arrow-left-right"></i></span>
        </button>            
    </div>
    <div class="col-lg-5 col-10 text-center">
        <label for="monto_cambio_reponer"></label>
        <div class="input-group mb-3">
            <input type="text" name="monto_cambio_reponer" id="monto_cambio_reponer" minlength="0" value="0" class="form-control" maxlength="15" disabled="" title="Monto del gasto en dolares">
            <span class="input-group-text icono_moneda" id="spam_icono_moneda_cuota_cambio">$</span>
        </div>
    </div>
    <div class="col-12">
       <p class="text-muted">Fondos en caja actualmente: <b id="fondos_caja_restantes"></b></p>    
    </div>
    <div class="col-12">
       <p class="text-muted">Total del Fondos Gastados: <b id="fondos_gastados"></b></p>    
    </div>    
</div>
<div class="row m-3">
    <div class="col-md-12 text-center">
        <button class="btn btn-primary" type="submit" id="boton_guardar_reposicion">Guardar</button>
    </div>
</form>
