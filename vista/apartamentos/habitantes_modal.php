<form action="?pagina=habitantes_controlador.php&accion=guardar" method="POST" id="form_habitantes" name="form_habitantes">
    <div class="row m-3">
        <div class="col-md-6">
            <label for="cedula">Cedula</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                <select class="form-select border border-dark" id="tipo_cedula" aria-label="Default select example" for="tipo_cedula" name="tipo_cedula">
                    <option selected="" hidden="" value="">N/A</option>
                    <option value="V">V</option>
                    <option value="E">E</option>
                </select>
                <input type="text" class="border border-dark form-control cedula" name="cedula" id="cedula" placeholder="Ejem: 30475465" aria-label="cedula" aria-describedby="basic-addon1" minlength="7" maxlength="8" style="flex-grow: 7" value="Selección de documento" disabled="">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="nombre">Nombre</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="border border-dark form-control nombre" name="nombre" id="nombre" placeholder="Ejem: Carlos" aria-label="nombre" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="apellido">Apellido</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-type"></i></span>
                <input type="text" class="border border-dark form-control apellido" name="apellido" id="apellido" placeholder="Ejem: Rodriguez" aria-label="apellido" aria-describedby="basic-addon1" minlength="3" maxlength="30">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-cake2"></i></span>
                <input type="date" class="border border-dark form-control fecha_nacimiento" name="fecha_nacimiento" id="fecha_nacimiento" placeholder="Fecha de nacimiento" aria-label="fecha_nacimiento" aria-describedby="basic-addon1">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="telefono">Telefono</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill"></i></span>
                <input type="text" class="border border-dark form-control telefono" name="telefono" id="telefono" placeholder="Ejem: 04167121830" aria-label="telefono" aria-describedby="basic-addon1" maxlength="11">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="sexo">Sexo</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-gender-male"></i></span>
                <select class="border border-dark form-select sexo" aria-label="Default select example" for="sexo" name="sexo" id="sexo">
                    <option selected hidden value="">Seleccione un Sexo</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        
    </div> 
    <div class="row m-3">
        <div class="col-md-12">
            <label for="correo">Correo electrónico</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                <input type="text" class="border border-dark form-control correo" name="correo" id="correo" placeholder="Ejem: usuario@gmail.com" aria-label="correo" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        
    </div>
    <div class="row m-3">
        <div class="col-md-6">
            <label for="apartamento">Apartamento</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-building"></i></span>
                <select class="border border-dark form-select apartamento_id" aria-label="Default select example" id="apartamento_id" name="apartamento_id" disabled>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="tipo_vinculo">Vinculo</label>
            <div class="input-group mb-3">
                <span class="border border-primary input-group-text" id="basic-addon1"><i class="bi bi-building"></i></span>
                <select class="border border-dark form-select tipo_vinculo" aria-label="Default select example" for="tipo_vinculo" name="tipo_vinculo" id="tipo_vinculo">
                    <option selected hidden value="">Seleccione un Vinculo</option>
                    <option value="Propietario">Propietario</option>
                    <option value="Habitante">Habitante</option>
                </select>
                <span class="w-100 invalid-feedback"></span>
            </div>
        </div>
    </div>
    <div class="row m-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" type="submit" id="boton_formulario_habitantes">Registrar</button>
        </div>
    </div>
</form>