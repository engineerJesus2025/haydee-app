$(document).ready(function () {
    $("#monto_estimado").on("keypress", function (e) {
        validarKeyPress(/^[0-9.,]$/, e);
    });

    $("#monto_estimado").on("keyup", function () {
        validarKeyUp(/^\d+([.,]\d{1,2})?$/, this, this.nextElementSibling, "Debe ingresar el monto de la solicitud del gasto");
    });

    $("#descripcion").on("keyup", function () {
        validarKeyUp(/^[A-Za-z0-9 \b]{3,60}$/, this, this.nextElementSibling, "Debe ingresar la descripción de la solicitud del gasto");
    });

    $("#nombre").on("keypress", function (e) {
        validarKeyPress(/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]*$/, e);
    });

    $("#nombre").on("keyup", function () {
        validarKeyUp(/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,40}$/, this, this.nextElementSibling, "Debe ingresar la descripción de la solicitud del gasto");
    });

    document.getElementById('fecha').addEventListener("change",e=>{
        e.target.classList.add('is-valid');
        e.target.classList.remove('is-invalid');
        e.target.nextElementSibling.textContent = "";
    });

    document.getElementById('prioridad').addEventListener("change",e=>{
        let valido = validarKeyUp(/^[0-9]{1}$/,
        e.target,e.target.nextElementSibling,"El valor de la prioridad no es válido");
        console.log(e.target,e.target.value)
        if (!valido) return;

        e.target.classList.add('is-valid');
        e.target.classList.remove('is-invalid');
        e.target.nextElementSibling.textContent = "";
    });

    document.getElementById('selector_mes').addEventListener("change",async e=>{
        let valido = validarKeyUp(/^[0-9]{1,2}$/,
        e.target,e.target.nextElementSibling,"El valor del mes seleccionado no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_mes');
        datos.append('fecha',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
            buscarPresupuesto()
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "El mes seleccionado no dispone de presupuesto";
        }
    });

    document.getElementById('selector_anio').addEventListener("change",async e=>{
        let valido = validarKeyUp(/^[0-9]{1,4}$/,
        e.target,e.target.nextElementSibling,"El valor del año seleccionado no es válido");

        if (!valido) return;

        let datos = new FormData();
        datos.append('validar','validar_anio');
        datos.append('fecha',e.target.value);

        valido = await verificar_clave_foranea(datos);
        
        if (valido) {
            e.target.classList.add('is-valid');
            e.target.classList.remove('is-invalid');
            e.target.nextElementSibling.textContent = "";
            buscarPresupuesto()
        }
        else{
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
            e.target.nextElementSibling.textContent = "El año seleccionado no dispone de presupuesto";
        }
    });

    $("#boton_formulario").on("click", async function (e) {
        let accion = (e.target.getAttribute("modificar")) ? "Editar" : "Registrar";
        e.preventDefault();
        if (await validarEnvio(accion)) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Está seguro que desea ${accion} esta solicitud de gasto?`,
                showCancelButton: true,
				confirmButtonText: "Si, " + accion,
                confirmButtonColor: "#1b8a40",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    envio(accion);
                }
            });
        }
    });
});

function mensajes(icono, tiempo, titulo, mensaje) {
    Swal.fire({
        icon: icono,
        timer: tiempo,
        title: titulo,
        text: mensaje,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: "#e01d22",
    });
}

async function validarEnvio(accion = "Registrar") {
    const descripcion = document.getElementById('descripcion');
    const montoInput = document.getElementById('monto_estimado');
    const fechaInput = document.getElementById('fecha');
    const nombreInput = document.getElementById('nombre');
    const prioridadInput = document.getElementById('prioridad');
    const mesInput = document.getElementById('selector_mes');
    const anioInput = document.getElementById('selector_anio');

    const presupuestoDisponible = parseFloat(document.querySelector("#presupuesto_disponible").textContent.replace(",", "."));
    const montoEstimado = parseFloat(montoInput.value.replace(",", "."));

    const boton = document.querySelector("#boton_formulario");
    const esModificacion = boton.hasAttribute("modificar");
    const monto_original = parseFloat(document.querySelector("#monto_estimado").getAttribute("data-original") || 0);
    const disponible_real = esModificacion ? presupuestoDisponible + monto_original : presupuestoDisponible;

    // Validar mes
    if (mesInput.value === "") {
        mesInput.classList.add('is-invalid');
        mesInput.classList.remove('is-valid');
        mesInput.nextElementSibling.textContent = 'Debe seleccionar el mes del presupuesto';
        mensajes('error', 3000, 'Mes no seleccionado', 'Debe seleccionar el mes del presupuesto.');
        return false;
    }

    let valido = validarKeyUp(/^[0-9]{1,2}$/,
    mesInput,mesInput.nextElementSibling,"El valor del mes seleccionado no es válido");

    if (!valido) {
        mensajes('error', 3000, 'Atención', 'El valor del mes seleccionado no es válido');
        return false;
    }

    let datos = new FormData();
    datos.append('validar','validar_mes');
    datos.append('fecha',mesInput.value);

    valido = await verificar_clave_foranea(datos);
    
    if (valido) {
        mesInput.classList.add('is-valid');
        mesInput.classList.remove('is-invalid');
        mesInput.nextElementSibling.textContent = "";
        buscarPresupuesto()
    }
    else{
        mesInput.classList.remove('is-valid');
        mesInput.classList.add('is-invalid');
        mesInput.nextElementSibling.textContent = "El mes seleccionado no dispone de presupuesto";
        mensajes('error', 3000, 'Atención', 'El mes seleccionado no dispone de presupuesto');
        return false;
    }
       

    // Validar año
    if (anioInput.value === "") {
        anioInput.classList.add('is-invalid');
        anioInput.classList.remove('is-valid');
        anioInput.nextElementSibling.textContent = 'Debe seleccionar el año del presupuesto';
        mensajes('error', 3000, 'Año no seleccionado', 'Debe seleccionar el año del presupuesto.');
        return false;
    }

    valido = validarKeyUp(/^[0-9]{1,4}$/,
    anioInput,anioInput.nextElementSibling,"El valor del año seleccionado no es válido");

    if (!valido) {
        mensajes('error', 3000, 'Atención', 'El valor del año seleccionado no es válido');
        return false;
    }

    datos = new FormData();
    datos.append('validar','validar_anio');
    datos.append('fecha',anioInput.value);

    valido = await verificar_clave_foranea(datos);    
    
    if (valido) {
        anioInput.classList.add('is-valid');
        anioInput.classList.remove('is-invalid');
        anioInput.nextElementSibling.textContent = "";
        buscarPresupuesto()
    }
    else{
        anioInput.classList.remove('is-valid');
        anioInput.classList.add('is-invalid');
        anioInput.nextElementSibling.textContent = "El año seleccionado no dispone de presupuesto";
        mensajes('error', 3000, 'Atención', 'El año seleccionado no dispone de presupuesto');
        return false;
    }

    // Validar fecha
    if (!fechaInput.value) {
        fechaInput.classList.add('is-invalid');
        fechaInput.classList.remove('is-valid');
        fechaInput.nextElementSibling.textContent = 'Debe seleccionar una fecha de solicitud';
        mensajes('error', 3000, 'Fecha inválida', 'Debe seleccionar una fecha de solicitud.');
        return false;
    }

    // Validar nombre
    if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,40}$/.test(nombreInput.value)) {
        nombreInput.classList.add('is-invalid');
        nombreInput.classList.remove('is-valid');
        nombreInput.nextElementSibling.textContent = 'Debe ingresar un nombre válido entre 3 y 40 letras.';
        mensajes('error', 3000, 'Nombre inválido', 'Debe ingresar un nombre válido entre 3 y 40 letras.');
        return false;
    }

    // Validar descripción
    if (validarKeyUp(
        /^[A-Za-z0-9 \b]{3,60}$/,
        descripcion,
        descripcion.nextElementSibling,
        'Debe ingresar la descripción de la solicitud del gasto'
    ) === 0) {
        mensajes('error', 4000, 'Descripción inválida', 'El formato debe ser solo en letras y números.');
        return false;
    }

    // Validar monto
    if (validarKeyUp(
        /^\d+([.,]\d{1,2})?$/,
        montoInput,
        montoInput.nextElementSibling,
        'Debe ingresar el monto de la solicitud del gasto'
    ) === 0) {
        mensajes('error', 4000, 'Monto inválido', 'Debe ingresar un valor numérico válido.');
        return false;
    }

    // Validar prioridad
    if (!prioridadInput.value) {
        prioridadInput.classList.add('is-invalid');
        prioridadInput.classList.remove('is-valid');
        prioridadInput.nextElementSibling.textContent = 'Debe seleccionar una prioridad.';
        mensajes('error', 3000, 'Prioridad no seleccionada', 'Debe seleccionar una prioridad.');
        return false;
    }
    valido = validarKeyUp(/^[0-9]{1}$/,
    prioridadInput,prioridadInput.nextElementSibling,"El valor de la prioridad no es válido");

    if (!valido) {
        mensajes('error', 3000, 'Atención', 'El valor de la prioridad seleccionada no es válido.');
        return false;
    }

    prioridadInput.classList.add('is-valid');
    prioridadInput.classList.remove('is-invalid');
    prioridadInput.nextElementSibling.textContent = "";

    // Validar presupuesto
    if (isNaN(disponible_real) || isNaN(montoEstimado)) {
        mensajes('error', 4000, 'Error de presupuesto', 'No se pudo obtener el monto disponible del presupuesto.');
        return false;
    }

    if (montoEstimado > disponible_real) {
        mensajes('error', 4000, 'Monto excedido', `El monto estimado ($ ${montoEstimado.toFixed(2)}) supera el disponible ($ ${disponible_real.toFixed(2)}).`);
        return false;
    }

    return true;
}

function validarKeyPress(er, e) {
    key = e.keyCode;
    tecla = String.fromCharCode(key);
    a = er.test(tecla);
    if (!a) {
        e.preventDefault();
    }
}

function validarKeyUp(er,etiqueta,etiquetamensaje,
mensaje){
    a = er.test(etiqueta.value);
    
    if(a){
        etiqueta.classList.add('is-valid');
        etiqueta.classList.remove('is-invalid');
        etiquetamensaje.textContent = "";
        return 1;
    }
    else{
        etiqueta.classList.add('is-invalid')
        etiqueta.classList.remove('is-valid');
        etiquetamensaje.textContent = mensaje;
        return 0;
    }
}

async function verificar_clave_foranea(datos){  
    let data = await fetch("",{method:"POST", body:datos}).then(res=>{      
        let result = res.json()
        return result;
    });

    return data     
}