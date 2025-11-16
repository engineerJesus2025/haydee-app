<form action="?pagina=bancos_controlador.php&accion=guardar" method="POST" id="form_banco" name="form_banco">
    <div class="row m-3">
        <div class="col-lg-6">
            <label for="nombre_banco">Nombre del Banco</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="border border-dark form-control nombre_banco" name="nombre_banco" id="nombre_banco" placeholder="Nombre del Banco" aria-label="nombre_banco" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-6">
            <label for="codigo">Código del Banco</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-123"></i></span>
                <input type="text" class="border border-dark form-control codigo" name="codigo" id="codigo" placeholder="Código" aria-label="codigo" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-6">
            <label for="numero_cuenta">Número de Cuenta</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank-fill"></i></span>
                <input type="text" class="border border-dark form-control numero_cuenta" name="numero_cuenta" id="numero_cuenta" placeholder="Número de Cuenta" aria-label="numero_cuenta" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-lg-6">
            <label for="telefono_afiliado">Teléfono Afiliado</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill"></i></span>
                <input type="text" class="border border-dark form-control telefono_afiliado" name="telefono_afiliado" id="telefono_afiliado" placeholder="Ejem: 04127721822" aria-label="telefono_afiliado" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-lg-6">
            <label for="cedula_afiliada">Cedula Afiliada</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                <input type="text" class="border border-dark form-control cedula_afiliada" name="cedula_afiliada" id="cedula_afiliada" placeholder="Cedula Afiliada" aria-label="cedula_afiliada" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Guardar</button>
        </div>
    </div>
</form>