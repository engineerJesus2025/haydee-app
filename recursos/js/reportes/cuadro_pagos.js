/**
 * cuadro_pagos.js
 * Generación de cuadro de pagos
 * Dependencias: utilidades.js, validaciones.js, formatoFechas.js
 */

let botonCuadroPagos = document.getElementById('boton_cuadro_pagos');
let arrayMeses = [];

botonCuadroPagos.addEventListener("click", () => {
    document.getElementById("titulo_modal_persona").textContent = 'Generar Cuadro de Pagos';
    document.getElementById('label_reporte').textContent = "Seleccione el mes para generar el cuadro";

    let botonGenerar = document.getElementById('boton_generar');
    botonGenerar.setAttribute("reporte", "cuadro_pagos");

    let select = document.getElementById('select_reporte');
    select.innerHTML = '<option selected hidden value="">Seleccione el Mes</option>';

    let fragment = document.createDocumentFragment();
    arrayMeses.forEach(mes => {
        let fecha = new Date(mes.anio, mes.mes - 1, 1);
        let nombreMes = fecha.toLocaleString("es-ES", { month: 'long' });
        let option = document.createElement("option");
        option.textContent = `${nombreMes} del ${mes.anio}`;
        option.value = `${mes.mes}-${mes.anio}`;
        fragment.appendChild(option);
    });
    select.appendChild(fragment);

    // Validación simple del formato
    $(select).off('change').on('change', function() {
        let valido = /^\d{1,2}-\d{4}$/.test(this.value);
        if (valido) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            this.nextElementSibling.textContent = '';
        } else {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            this.nextElementSibling.textContent = 'La fecha seleccionada no es válida';
        }
    });

    $('#boton_generar').off('click').on('click', function(e) {
        e.preventDefault();
        let select = document.getElementById('select_reporte');
        if (!/^\d{1,2}-\d{4}$/.test(select.value)) {
            Utilidades.mensaje('error', 'Atención', 'Debe seleccionar un mes válido');
            return;
        }
        let reporte = this.getAttribute("reporte");
        document.getElementById('form_reporte').setAttribute('action', `?pagina=reportes_controlador.php&accion=${reporte}`);
        document.getElementById('form_reporte').submit();
    });
});

function consultarMeses() {
    let datos = new FormData();
    datos.append('operacion', "consultar_meses_mensualidad");

    Utilidades.query(datos).then(respuesta => {
        if (respuesta.estatus && respuesta.datos.length > 0) {
            botonCuadroPagos.removeAttribute('disabled');
            arrayMeses = respuesta.datos;
        } else {
            botonCuadroPagos.parentElement.setAttribute('title', 'No hay mensualidades registradas para generar el cuadro');
        }
        let spinnerContainer = botonCuadroPagos.querySelector(".spinner-grow")?.parentElement;
        if (spinnerContainer) {
            spinnerContainer.innerHTML = `<i class="bi bi-ui-checks" style="font-size: 5rem !important;"></i>`;
        }
    }).catch(error => {
        console.error("Error al cargar meses:", error);
        Utilidades.mensaje('error', 'Error', 'No se pudieron cargar los meses');
    });
}

consultarMeses();