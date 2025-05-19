<form action="?pagina=bancos_controlador.php&accion=guardar" method="POST" id="form_banco" name="form_banco">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="nombre_banco">Nombre del Banco</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control nombre_banco" name="nombre_banco" id="nombre_banco" placeholder="Nombre del Banco" aria-label="nombre_banco" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="codigo">Codigo del Banco</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="form-control codigo" name="codigo" id="codigo" placeholder="Codigo" aria-label="codigo" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="numero_cuenta">Numero de Cuenta</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control numero_cuenta" name="numero_cuenta" id="numero_cuenta" placeholder="Número de Cuenta" aria-label="numero_cuenta" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="telefono_afiliado">Telefono Afiliado</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control telefono_afiliado" name="telefono_afiliado" id="telefono_afiliado" placeholder="Telefono Afiliado" aria-label="telefono_afiliado" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="cedula_afiliada">Cedula Afiliada</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="form-control cedula_afiliada" name="cedula_afiliada" id="cedula_afiliada" placeholder="Cedula Afiliada" aria-label="cedula_afiliada" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
        </div>
    </div>
</form>