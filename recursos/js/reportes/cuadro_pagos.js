let botonCuadroPagos = document.getElementById('boton_cuadro_pagos');
let arrayMeses = [];

document.addEventListener('DOMContentLoaded', () => {
    //  Cargamos los datos al iniciar la pantalla
    consultarMeses();
});

// Al hacer clic (si está habilitado), poblamos el modal
botonCuadroPagos.addEventListener("click", () => {
    document.getElementById("titulo_modal_persona").textContent = 'Generar Cuadro de Pagos';
    document.getElementById('label_reporte').textContent = "Seleccione el mes para generar el cuadro ";

    let asterisco = document.createElement("span");
    asterisco.classList.add("text-danger");
    asterisco.textContent = "*";
    document.getElementById('label_reporte').appendChild(asterisco);

    let botonGenerar = document.getElementById('boton_generar');
    botonGenerar.setAttribute("reporte", "cuadro_pagos");

    let select = document.getElementById('select_reporte');
    select.innerHTML = '<option selected hidden value="">Seleccione el Mes</option>';

    let fragment = document.createDocumentFragment();
    arrayMeses.forEach(mes => {
        let nombreMes = FormatoFechas.nombreMes(mes.mes);
        let option = document.createElement("option");
        option.textContent = `${nombreMes} del ${mes.anio}`;
        option.value = FormatoFechas.formatear(`1-${mes.mes}-${mes.anio}`,"MM-YYYY");
        fragment.appendChild(option);
    });

    select.appendChild(fragment);

    botonGenerar.onclick = function(e) {
        if (select.dataset.valor != "mensualidad") return;
        e.preventDefault();
        
        if (!Validador.evaluarInput(select, Patrones.mesAnio, 'seleccione un mes válido')) {
            Alertas.mostrar('error', 'Atención', 'Debe seleccionar un mes válido');
            return;
        }
        let reporte = this.getAttribute("reporte");
        document.getElementById('form_reporte').setAttribute('action', `?pagina=reportes&accion=${reporte}`);
        document.getElementById('form_reporte').submit();
    };

    select.dataset.valor = "mensualidad";
});

function consultarMeses() {
    let datos = new FormData();
    datos.append('operacion', "consultar_meses_mensualidad");

    Peticiones.enviar(datos, "", false).then(respuesta => {
        let spinnerContainer = botonCuadroPagos.querySelector(".spinner-grow")?.parentElement;

        const contenedorTarjeta = botonCuadroPagos.parentElement;
        
        if (respuesta.estatus && respuesta.datos.length > 0) {
            Tooltips.actualizarDinamicamente(contenedorTarjeta, 'Generar cuadro comparativo de solvencia mensual');
            botonCuadroPagos.removeAttribute('disabled');
            arrayMeses = respuesta.datos;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi bi-ui-checks" style="font-size: 5rem !important;"></i>`;
            }
        } else {
            Tooltips.actualizarDinamicamente(contenedorTarjeta, 'No existen mensualidades procesadas para este cuadro.');
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi bi-inbox text-secondary" style="font-size: 5rem !important;"></i>`;
            }
        }
    }).catch(error => {
        console.error("Error al cargar meses:", error);
    });
}