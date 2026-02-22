/**
 * reporte_ingreso_egreso.js
 * Reporte de Ingresos y Egresos con gráficos
 * Dependencias: utilidades.js, validaciones.js, formatoFechas.js, chart.js
 */

let graficaChart;
let modal = new bootstrap.Modal(document.getElementById("modal_reporte"));
const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
let fechas_asignadas_ingresos, fechas_asignadas_egresos;
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);

document.addEventListener('DOMContentLoaded', () => {
    // Mostrar/ocultar campos de fecha personalizada
    document.getElementById("filtro").addEventListener("change", e => {
        if (e.target.value === "Otro") {
            document.getElementById("label_fechas").removeAttribute("hidden");
            document.getElementById("div_fecha_inicio").removeAttribute("hidden");
            document.getElementById("div_fecha_cierre").removeAttribute("hidden");
        } else {
            document.getElementById('fecha_inicio').value = '';
            document.getElementById('fecha_fin').value = '';
            document.getElementById("label_fechas").setAttribute("hidden", "");
            document.getElementById("div_fecha_inicio").setAttribute("hidden", "");
            document.getElementById("div_fecha_cierre").setAttribute("hidden", "");
        }
    });

    // Vista previa del gráfico
    document.getElementById("boton_vista_previa").addEventListener("click", async e => {
        let fecha_inicio = document.getElementById('fecha_inicio').value;
        let fecha_fin = document.getElementById('fecha_fin').value;
        let filtro = document.getElementById('filtro').value;

        if (filtro === "") {
            Utilidades.mensaje('error', 'Atención', "Debe seleccionar una opción para buscar");
            return;
        }

        if (filtro === "Otro" && (fecha_inicio === "" || fecha_fin === "")) {
            Utilidades.mensaje('error', 'Atención', "Debe escoger la fecha inicio y final para la consulta");
            return;
        }

        // Calcular fechas según el filtro
        if (filtro === "mes") {
            fecha_inicio = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
            fecha_fin = new Date().toISOString().split('T')[0];
        } else if (filtro === "trimestre") {
            let fecha = new Date();
            fecha.setMonth(fecha.getMonth() - 3);
            fecha_inicio = fecha.toISOString().split('T')[0];
            fecha_fin = new Date().toISOString().split('T')[0];
        } else if (filtro === "semestre") {
            let fecha = new Date();
            fecha.setMonth(fecha.getMonth() - 6);
            fecha_inicio = fecha.toISOString().split('T')[0];
            fecha_fin = new Date().toISOString().split('T')[0];
        } else if (filtro === "año") {
            fecha_inicio = new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0];
            fecha_fin = new Date().toISOString().split('T')[0];
        }

        // Mostrar descripción del período
        document.getElementById("fecha_grafico").textContent = `Mostrando resultados desde el ${fecha_inicio.split("-")[2]} de ${meses[parseInt(fecha_inicio.split("-")[1]) - 1]} del ${fecha_inicio.split("-")[0]} hasta ${fecha_fin.split("-")[2]} de ${meses[parseInt(fecha_fin.split("-")[1]) - 1]} del ${fecha_fin.split("-")[0]}`;
        document.getElementById("fecha_grafico_input").value = document.getElementById("fecha_grafico").textContent;

        let balance = document.getElementById('select_balance').value;
        let metodo_pago = document.getElementById('select_metodo_pago').value;
        let tipo_gasto = document.getElementById('select_tipo_gasto').value;

        // Consultar datos para el gráfico
        let formData = new FormData();
        formData.append("balance", balance);
        formData.append("metodo_pago", metodo_pago);
        formData.append("tipo_gasto", tipo_gasto);
        formData.append("filtro", filtro);
        formData.append("fecha_inicio", fecha_inicio);
        formData.append("fecha_fin", fecha_fin);
        formData.append("operacion", "consultar_ingresos_egresos");

        let resultado = await Utilidades.query(formData);
        if (!resultado.estatus) {
            Utilidades.mensaje('error', 'Atención', resultado.mensaje);
            return;
        }
        if (resultado.datos.length === 0) {
            Utilidades.mensaje('warning', 'Atención', "No hay resultados para esos filtros de búsqueda");
            return;
        }

        // Consultar estadísticas
        formData = new FormData();
        formData.append("balance", balance);
        formData.append("metodo_pago", metodo_pago);
        formData.append("tipo_gasto", tipo_gasto);
        formData.append("filtro", filtro);
        formData.append("fecha_inicio", fecha_inicio);
        formData.append("fecha_fin", fecha_fin);
        formData.append("operacion", "consultar_estadisticas_ingresos_egresos");

        let resultado_estadisticas = await Utilidades.query(formData);
        if (!resultado_estadisticas.estatus) {
            Utilidades.mensaje('error', 'Atención', resultado_estadisticas.mensaje);
            return;
        }

        modal.show();

        // Procesar datos para el gráfico
        procesarDatosGrafico(resultado.datos, filtro);
        actualizarEstadisticas(resultado_estadisticas.datos);

        let modo = document.getElementById('select_mostrar_datos').value;
        if (modo === "solo_texto") {
            if (graficaChart) {
                graficaChart.destroy();
            }
            document.getElementById('canva').setAttribute("hidden", "");
            document.getElementById('titulo_grafico').setAttribute("hidden", "");
            document.getElementById('contenedor_estadistica').removeAttribute("hidden");
        } else if (modo === "grafico_texto") {
            document.getElementById('contenedor_estadistica').removeAttribute("hidden");
            document.getElementById('canva').removeAttribute("hidden");
            document.getElementById('titulo_grafico').removeAttribute("hidden");
        } else {
            document.getElementById('contenedor_estadistica').setAttribute("hidden", "");
            document.getElementById('canva').removeAttribute("hidden");
            document.getElementById('titulo_grafico').removeAttribute("hidden");
        }
    });

    // Eventos para los checkboxes que habilitan/deshabilitan selects
    document.getElementById("balance").addEventListener("change", e => {
        let select = document.getElementById("select_balance");
        if (!select.disabled) {
            if (select.value === "Ingresos") {
                document.getElementById("select_tipo_gasto").parentElement.removeAttribute("hidden");
                document.getElementById("tipo_gasto").parentElement.removeAttribute("hidden");
                document.getElementById("select_tipo_gasto").value = "Todos";
                document.getElementById("select_tipo_gasto").disabled = true;
            }
            select.value = "Todos";
            select.disabled = true;
        } else {
            select.disabled = false;
        }
    });

    document.getElementById("select_balance").addEventListener("change", e => {
        if (e.target.value === "Ingresos") {
            document.getElementById("select_tipo_gasto").parentElement.setAttribute("hidden", "");
            document.getElementById("select_tipo_gasto").value = "Todos";
            document.getElementById("tipo_gasto").parentElement.setAttribute("hidden", "");
            document.getElementById("tipo_gasto").checked = false;
        } else {
            document.getElementById("select_tipo_gasto").parentElement.removeAttribute("hidden");
            document.getElementById("tipo_gasto").parentElement.removeAttribute("hidden");
        }
    });

    document.getElementById("metodo_pago").addEventListener("change", e => {
        let select = document.getElementById("select_metodo_pago");
        if (!select.disabled) {
            document.getElementById("select_metodo_pago").value = "Todos";
            select.disabled = true;
        } else {
            select.disabled = false;
        }
    });

    document.getElementById("tipo_gasto").addEventListener("change", e => {
        let select = document.getElementById("select_tipo_gasto");
        if (!select.disabled) {
            select.value = "Todos";
            select.disabled = true;
        } else {
            select.disabled = false;
        }
    });

    // Botón generar PDF
    document.getElementById("boton_generar").addEventListener("click", e => {
        e.preventDefault();
        let img_barra = document.getElementById("canva").toDataURL("image/png");
        document.getElementById("barra").value = img_barra;

        // Extraemos solo texto de los totales
        document.getElementById('total_pagos_input').value = document.getElementById('total_pagos').textContent;
        document.getElementById('total_gastos_input').value = document.getElementById('total_gastos').textContent;
        document.getElementById('gastos_efectivo_input').value = document.getElementById('gastos_efectivo').textContent;
        document.getElementById('gastos_transferencia_input').value = document.getElementById('gastos_transferencia').textContent;
        document.getElementById('gastos_pago_movil_input').value = document.getElementById('gastos_pago_movil').textContent;
        document.getElementById('pagos_efectivo_input').value = document.getElementById('pagos_efectivo').textContent;
        document.getElementById('pagos_transferencia_input').value = document.getElementById('pagos_transferencia').textContent;
        document.getElementById('pagos_pago_movil_input').value = document.getElementById('pagos_pago_movil').textContent;
        
        // CORRECCIÓN: Usar innerHTML para conservar las etiquetas <p> de las listas de fechas
        document.getElementById('fecha_pagos_input').value = document.getElementById('fecha_pagos').innerHTML;
        document.getElementById('fecha_gastos_input').value = document.getElementById('fecha_gastos').innerHTML;
        
        document.getElementById('mostrar_datos_input').value = document.getElementById('select_mostrar_datos').value;

        modal.hide();
        e.target.closest("form").submit();
    });
});

// =========================================================================
// Funciones auxiliares
// =========================================================================

function procesarDatosGrafico(datos, filtro) {
    let obj_labels = [];
    let meses_anios = [];

    let obj_egreso = {
        label: 'Egreso (BS.)',
        data: [],
        backgroundColor: ['#e01d22'],
        fechas_asignadas: {}
    };
    let obj_ingreso = {
        label: 'Ingreso (BS.)',
        data: [],
        backgroundColor: ['#0079C2'],
        fechas_asignadas: {}
    };

    datos.forEach(registro => {
        let fecha_seleccionada;
        let [anio, mes, dia] = registro.fecha.split("-");
        let fechaObj = new Date(anio, mes - 1, dia);
        let mesNombre = meses[mes - 1];

        if (registro.balance === "Ingreso") {
            if (filtro === "año" || filtro === "semestre") {
                fecha_seleccionada = `${mesNombre} del ${anio}`;
            } else if (filtro === "mes" || filtro === "trimestre") {
                let semanaDelMes = Math.ceil(dia / 7);
                let sufijo = (semanaDelMes === 1 || semanaDelMes === 3) ? "ra" : (semanaDelMes === 2 ? "da" : "ta");
                fecha_seleccionada = `${semanaDelMes}${sufijo} Semana, ${mesNombre}`;
            } else if (filtro === "Otro") {
                fecha_seleccionada = `${mesNombre} ${dia}`;
            }

            if (!meses_anios.includes(fecha_seleccionada)) meses_anios.push(fecha_seleccionada);

            if (!obj_ingreso.fechas_asignadas[fecha_seleccionada]) {
                obj_ingreso.fechas_asignadas[fecha_seleccionada] = parseFloat(registro.monto);
            } else {
                obj_ingreso.fechas_asignadas[fecha_seleccionada] += parseFloat(registro.monto);
            }
        } else {
            if (filtro === "año" || filtro === "semestre") {
                fecha_seleccionada = `${mesNombre} del ${anio}`;
            } else if (filtro === "mes" || filtro === "trimestre") {
                let semanaDelMes = Math.ceil(dia / 7);
                let sufijo = (semanaDelMes === 1 || semanaDelMes === 3) ? "ra" : (semanaDelMes === 2 ? "da" : "ta");
                fecha_seleccionada = `${semanaDelMes}${sufijo} Semana, ${mesNombre}`;
            } else if (filtro === "Otro") {
                fecha_seleccionada = `${mesNombre} ${dia}`;
            }

            if (!meses_anios.includes(fecha_seleccionada)) meses_anios.push(fecha_seleccionada);

            if (!obj_egreso.fechas_asignadas[fecha_seleccionada]) {
                obj_egreso.fechas_asignadas[fecha_seleccionada] = parseFloat(registro.monto);
            } else {
                obj_egreso.fechas_asignadas[fecha_seleccionada] += parseFloat(registro.monto);
            }
        }
    });

    // Asegurar que ambas series tengan todas las fechas
    meses_anios.forEach(fecha => {
        if (obj_ingreso.fechas_asignadas[fecha] === undefined) obj_ingreso.fechas_asignadas[fecha] = 0;
        if (obj_egreso.fechas_asignadas[fecha] === undefined) obj_egreso.fechas_asignadas[fecha] = 0;
    });

    // Ordenar fechas según el filtro
    let fechasOrdenadas = ordenarFechas(Object.keys(obj_ingreso.fechas_asignadas), filtro);

    fechasOrdenadas.forEach(fecha => {
        obj_ingreso.data.push(obj_ingreso.fechas_asignadas[fecha]);
        obj_egreso.data.push(obj_egreso.fechas_asignadas[fecha]);
    });

    fechas_asignadas_ingresos = obj_ingreso.fechas_asignadas;
    fechas_asignadas_egresos = obj_egreso.fechas_asignadas;

    // Crear gráfico
    const ctx = document.getElementById('canva').getContext('2d');
    if (graficaChart) graficaChart.destroy();
    graficaChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: fechasOrdenadas,
            datasets: [
                { label: obj_ingreso.label, data: obj_ingreso.data, backgroundColor: obj_ingreso.backgroundColor },
                { label: obj_egreso.label, data: obj_egreso.data, backgroundColor: obj_egreso.backgroundColor }
            ]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
}

function ordenarFechas(fechas, filtro) {
    if (filtro === "mes" || filtro === "trimestre") {
        // Formato: "X Semana, mes"
        return fechas.sort((a, b) => {
            let aMes = a.split(", ")[1];
            let bMes = b.split(", ")[1];
            let aSemana = parseInt(a.split(" ")[0]);
            let bSemana = parseInt(b.split(" ")[0]);
            if (aMes === bMes) return aSemana - bSemana;
            return meses.indexOf(aMes) - meses.indexOf(bMes);
        });
    } else if (filtro === "año" || filtro === "semestre") {
        // Formato: "mes del año"
        return fechas.sort((a, b) => {
            let aMes = a.split(" ")[0];
            let bMes = b.split(" ")[0];
            let aAnio = parseInt(a.split(" ")[2]);
            let bAnio = parseInt(b.split(" ")[2]);
            if (aAnio === bAnio) return meses.indexOf(aMes) - meses.indexOf(bMes);
            return aAnio - bAnio;
        });
    } else if (filtro === "Otro") {
        // Formato: "mes dia"
        return fechas.sort((a, b) => {
            let aMes = a.split(" ")[0];
            let bMes = b.split(" ")[0];
            let aDia = parseInt(a.split(" ")[1]);
            let bDia = parseInt(b.split(" ")[1]);
            if (aMes === bMes) return aDia - bDia;
            return meses.indexOf(aMes) - meses.indexOf(bMes);
        });
    }
    return fechas;
}

function actualizarEstadisticas(estadisticas) {
    limpiarContainerEstadistica();
    estadisticas.forEach(item => {
        let elemento = document.getElementById(item.indicador);
        if (elemento) {
            let valor = parseFloat(item.valor) || 0;
            elemento.textContent += valor.toFixed(2) + " Bs. / " + (valor / tasa_dolar).toFixed(2) + "$";
            elemento.removeAttribute("no-asignado");
        }
    });

    // Mostrar desglose por fechas
    let fragmentPagos = document.createDocumentFragment();
    for (let tiempo in fechas_asignadas_ingresos) {
        let p = document.createElement("p");
        p.textContent = tiempo + ": " + fechas_asignadas_ingresos[tiempo] + " Bs. / " + (fechas_asignadas_ingresos[tiempo] / tasa_dolar).toFixed(2) + "$";
        fragmentPagos.appendChild(p);
    }
    if (fragmentPagos.children.length === 0) {
        fragmentPagos.appendChild(document.createElement("p")).textContent = "No hay marcas de tiempo";
    }
    document.getElementById('fecha_pagos').appendChild(fragmentPagos);

    let fragmentGastos = document.createDocumentFragment();
    for (let tiempo in fechas_asignadas_egresos) {
        let p = document.createElement("p");
        p.textContent = tiempo + ": " + fechas_asignadas_egresos[tiempo] + " Bs. / " + (fechas_asignadas_egresos[tiempo] / tasa_dolar).toFixed(2) + "$";
        fragmentGastos.appendChild(p);
    }
    if (fragmentGastos.children.length === 0) {
        fragmentGastos.appendChild(document.createElement("p")).textContent = "No hay marcas de tiempo";
    }
    document.getElementById('fecha_gastos').appendChild(fragmentGastos);

    // Completar elementos que no se actualizaron
    document.querySelectorAll("[no-asignado]").forEach(el => {
        el.textContent += "0 Bs.";
    });
}

function limpiarContainerEstadistica() {
    document.getElementById('total_pagos').textContent = "Total de Pagos realizados: ";
    document.getElementById('total_pagos').setAttribute("no-asignado", "");

    document.getElementById('total_gastos').textContent = "Total de Gastos Realizados: ";
    document.getElementById('total_gastos').setAttribute("no-asignado", "");

    document.getElementById('gastos_efectivo').textContent = "Gastos por Efectivo: ";
    document.getElementById('gastos_efectivo').setAttribute("no-asignado", "");

    document.getElementById('gastos_transferencia').textContent = "Gastos por Transferencia: ";
    document.getElementById('gastos_transferencia').setAttribute("no-asignado", "");

    document.getElementById('gastos_pago_movil').textContent = "Gastos por Pago Movil: ";
    document.getElementById('gastos_pago_movil').setAttribute("no-asignado", "");

    document.getElementById('pagos_efectivo').textContent = "Pagos por Efectivo: ";
    document.getElementById('pagos_efectivo').setAttribute("no-asignado", "");

    document.getElementById('pagos_transferencia').textContent = "Pagos por Transferencia: ";
    document.getElementById('pagos_transferencia').setAttribute("no-asignado", "");

    document.getElementById('pagos_pago_movil').textContent = "Pagos por Pago Movil: ";
    document.getElementById('pagos_pago_movil').setAttribute("no-asignado", "");

    document.getElementById('fecha_pagos').textContent = null;
    document.getElementById('fecha_gastos').textContent = null;
}