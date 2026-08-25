// VARIABLES GLOBALES
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;
let boton_formulario = document.querySelector("#boton_formulario");

let modal = new bootstrap.Modal(document.getElementById("modal_presupuesto"), { focus: false });
let modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

let formulario_usar = document.querySelector("#form_presupuesto");
let select_mes = document.querySelector("#fecha");
let detalles_presupuestos_base;

let tabla_presupuesto;
let modal_carga = new bootstrap.Modal("#modal_carga");
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);

// INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', () => {
    consultar();
    document.querySelector("#modal_presupuesto")?.addEventListener("hide.bs.modal", resetModal);
});

function resetModal() {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Presupuesto';
    document.getElementById('titulo_modal').textContent = "Registrar presupuesto";
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-journal-plus");

    // Eliminar fecha extra si existe
    let fechaExtra = document.getElementById('fecha_editada');
    if (fechaExtra) fechaExtra.remove();

    // Restaurar contenido original de los detalles
    document.getElementById('contenedor_presupuestos').innerHTML = detalles_presupuestos_base;

    // Restablecer valores de inputs numéricos
    document.querySelectorAll("[type='number']").forEach(input => {
        if (input.id !== "cuota_reserva") input.value = 0;
    });
    document.querySelectorAll("[type='checkbox']").forEach(chk => chk.checked = false);
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);
}

// FUNCIONES AUXILIARES DE UI (agregar/eliminar filas, etc.)
function crearFilaConcepto(id_concepto, nombre_concepto) {
    let div_padre = document.createElement("div");
    div_padre.setAttribute('class', 'accordion-body row align-items-start fila-concepto border-bottom pt-2 mx-0');
    div_padre.setAttribute('data-concepto-id', id_concepto);

    // Columna del Nombre
    let div_nombre = document.createElement("div");
    div_nombre.setAttribute("class", "col-12 col-md-5 px-1");
    
    div_nombre.innerHTML = `
        <div class="input-group pe-none opacity-100">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar"></i></span>
            <input class="form-control bg-light text-muted fw-semibold text-center text-md-start border-start-0" 
                   type="text" value="${nombre_concepto}" readonly tabindex="-1" disabled>
        </div>`;

    // Columna de los Montos
    let div_inputs_montos = document.createElement("div");
    div_inputs_montos.setAttribute("class", "col-12 col-md-7 row m-0 p-0 align-items-start");

    // Input Bs
    let div_monto_bs = document.createElement("div");
    div_monto_bs.setAttribute("class", "col-5 px-1");
    div_monto_bs.innerHTML = `
        <div class="input-group">
            <input class="form-control monto-detalle" type="number" value="0" title="Monto en Bolívares" placeholder="0.00" monto="bs">
            <span class="w-100 invalid-feedback"></span>
            <span class="input-group-text icono_moneda">Bs.</span>
        </div>`;

    // Botón Intercambio
    let div_intercambio = document.createElement("div");
    div_intercambio.setAttribute("class", "col-2 px-0 d-flex justify-content-center");
    div_intercambio.innerHTML = `
        <button tabindex="-1" class="btn btn-soft-info boton_intercambio w-100 px-1" style="max-width: 42px;" title="Alternar moneda">
            <i class="bi bi-arrow-left-right"></i>
        </button>`;

    // Input Dólares
    let div_monto_usd = document.createElement("div");
    div_monto_usd.setAttribute("class", "col-5 px-1");
    div_monto_usd.innerHTML = `
        <div class="input-group">
            <input class="form-control" type="text" disabled value="0" title="Monto en Dólares" convertido="">
            <span class="input-group-text icono_moneda">$</span>
        </div>`;

    div_inputs_montos.appendChild(div_monto_bs);
    div_inputs_montos.appendChild(div_intercambio);
    div_inputs_montos.appendChild(div_monto_usd);

    div_padre.appendChild(div_nombre);
    div_padre.appendChild(div_inputs_montos);

    return div_padre;
}

function empaquetarDatosPresupuesto(formData) {
    let fecha = document.getElementById('fecha').value;
    let observacion = document.getElementById("observacion").value || "Sin observación";

    let input_reserva = document.getElementById("cuota_reserva");
    let cuota_reserva = 0;
    if (input_reserva) {
        cuota_reserva = input_reserva.getAttribute("monto") === "bs" ?
            input_reserva.value :
            input_reserva.closest(".row").querySelector("[convertido]").value;
    }

    formData.append('fecha', fecha);
    formData.append('cuota_reserva', parseFloat(cuota_reserva || 0));
    formData.append('observacion', observacion);

    let cantidadDetalles = 0;
    
    // Recorrer todas las filas generadas dinámicamente
    document.querySelectorAll(".fila-concepto").forEach(fila => {
        let concepto_id = fila.getAttribute("data-concepto-id");
        let input_monto = fila.querySelector("[monto]");
        
        let monto = 0;
        if (input_monto) {
            monto = input_monto.getAttribute("monto") === "bs" ?
                fila.querySelector("input[type='number']").value :
                fila.querySelector("[convertido]").value;
        }
        
        monto = parseFloat(monto);
        
        // Solo enviamos los conceptos a los que se les haya asignado un presupuesto
        if (monto > 0) {
            formData.append('concepto_id[]', concepto_id);
            formData.append('monto[]', monto);
            cantidadDetalles++;
        }
    });

    return cantidadDetalles;
}

async function consultar() {
    const formatoPeriodo = (cell) => {
        const row = cell.getData();
        let [anio, mes] = cell.getValue().split('-');
        let textoFecha = `${FormatoFechas.nombreMes(parseInt(mes).toString().padStart(2,0))} del ${anio}`.toUpperCase();
        
        return `<div class="fw-bold">${textoFecha}</div>`;
    };

    const formatoMonto = (cell) => {
        const data = cell.getData();
        const tasaHistorica = parseFloat(data.tasa_dolar) || 1; // Tasa de la BD
        return `${parseFloat(data.total_estimado).toFixed(2)} Bs. / ${(data.total_estimado / tasaHistorica).toFixed(2)} $`;
    };

    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" data-tooltip="true" title="Modificar los detalles de este registro">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" data-tooltip="true" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Período", field: "fecha", formatter: formatoPeriodo, minWidth: 180, responsive: 0 },
        { title: "Estimado", field: "total_estimado", formatter: formatoMonto, minWidth: 180 },
        {
            title: "Acciones", 
            formatter: formatoBotones, headerSort: false, hozAlign: "center", 
            vertAlign: "middle", minWidth: 130, responsive: 0, download: false,widthGrow: 2,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_presupuesto;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];

    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar' },
    };

    tabla_presupuesto = Tablas.cargarTabulador("tabla_presupuesto", "", columnas, opcionesExtra);
    
    // Llamada vital del módulo
    await consultarInformacionFormulario();

    Tablas.inicializarBuscadorGlobal(tabla_presupuesto, "busqueda_global", columnas);
}

// Función asíncrona para Vista Previa
async function mostrarVistaPrevia(data) {
    let [anio, mes] = data.fecha.split('-');
    document.getElementById("vp_periodo").textContent = `${FormatoFechas.nombreMes(parseInt(mes).toString().padStart(2,0))} ${anio}`.toUpperCase();
    
    // Totales (Usando la variable global tasa_dolar)
    const tasaHistorica = parseFloat(data.tasa_dolar) || 1;
    let totalBs = parseFloat(data.total_estimado) || 0;

    document.getElementById("vp_total_bs").textContent = `${totalBs.toFixed(2)} Bs.`;
    document.getElementById("vp_total_usd").textContent = `Ref: ${(totalBs / tasaHistorica).toFixed(2)} $`;

    document.getElementById("vp_observacion").textContent = data.observacion || 'Sin observaciones.';

    // Preparamos el contenedor y mostramos el modal con loader
    const contenedor = document.getElementById("vp_contenedor_detalles_presupuesto");
    contenedor.innerHTML = '<div class="text-center py-4"><div class="spinner- text-primary" role="status"></div><div class="text-muted mt-2 small">Cargando desglose...</div></div>';
    
    // Limpiamos la reserva hasta que llegue la petición (Tabulator no la trae por defecto en la consulta general)
    document.getElementById("vp_reserva_bs").textContent = "...";
    document.getElementById("vp_reserva_usd").textContent = "...";
    
    modalDetalles.show();

    // Solicitamos los detalles al servidor
    const formData = new FormData();
    formData.append('operacion', 'consultar_presupuesto');
    formData.append('id_presupuesto', data.id_presupuesto);

    const respuesta = await Peticiones.enviar(formData, "", true);

    // Renderizamos la respuesta
    if (respuesta.estatus && respuesta.datos) {
        const pres = respuesta.datos;

        // Actualizamos la cuota de reserva real
        let reservaBs = parseFloat(pres.cuota_reserva) || 0;
        document.getElementById("vp_reserva_bs").textContent = `${reservaBs.toFixed(2)} Bs.`;
        document.getElementById("vp_reserva_usd").textContent = `Ref: ${(reservaBs / tasaHistorica).toFixed(2)} $`;

        // Renderizado de detalles
        if (pres.detalles && pres.detalles.length > 0) {
            
            // Agrupamos los detalles por nombre_tipo_gasto
            const agrupados = pres.detalles.reduce((acc, curr) => {
                const categoria = curr.nombre_tipo_gasto || 'Otros Gastos';
                if (!acc[categoria]) acc[categoria] = [];
                acc[categoria].push(curr);
                return acc;
            }, {});

            // Construimos el HTML
            let html = '';
            for (const [categoria, items] of Object.entries(agrupados)) {
                
                // Sumamos el subtotal de esta categoría
                const subtotal = items.reduce((sum, item) => sum + parseFloat(item.monto), 0);
                const subtotalBadge = ComponentesUI.crearSoftBadge('primary', null, `Subtotal: ${subtotal.toFixed(2)} Bs.`);

                // Creamos las filas de la tabla
                const filas = items.map(item => `
                    <tr>
                        <td class="text-dark align-middle -0 -bottom vp--color">
                            <i class="bi bi-caret-right text-primary me-1" style="font-size: 0.8rem;"></i> 
                            <span style="color: var(--bs-body-color);">${item.nombre_concepto}</span>
                        </td>
                        <td class="text-end fw-semibold text-dark align-middle -0 -bottom vp--color">
                            <span style="color: var(--bs-body-color);">${parseFloat(item.monto).toFixed(2)} Bs.</span>
                        </td>
                    </tr>
                `).join('');
                
                html += `
                    <div class="card -0 shadow-sm mb-2 card-item pb-3">
                        <div class="vp-card-header -bottom d-flex justify-content-between align-items-center p-2 vp--color">
                            <span class="fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px; color: var(--bs-body-color);">
                                <i class="bi bi-folder2-open me-2 text-primary"></i>${categoria}
                            </span>
                            ${subtotalBadge}
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-less mb-0">
                                <tbody>
                                    ${filas}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
            contenedor.innerHTML = html;

        } else {
            contenedor.innerHTML = `
                <div class="alert alert-warning d-flex align-items-center mb-0 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>Este presupuesto no tiene detalles registrados.</div>
                </div>`;
        }
    } else {
        contenedor.innerHTML = '<div class="text-center text-danger py-3">Error al cargar los detalles.</div>';
    }
}

async function consultarInformacionFormulario() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_meses_faltantes');
    const respuesta = await Peticiones.enviar(formData);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const meses = respuestaServidor.datos || [];
            let boton_registrar = document.getElementById('boton_nuevo_registro');
        if (meses.length === 0) {
            if (!boton_registrar) return;
            boton_registrar.nextElementSibling.textContent = "No hay meses para definir presupuesto";
            boton_registrar.setAttribute('style', 'display:none');
            return;
        }
        else if(boton_registrar.getAttribute('style')?.includes("display:none")){
            boton_registrar.removeAttribute('style');
            boton_registrar.nextElementSibling.textContent = "";
        }

        select_mes.innerHTML = '';
        let fragment = document.createDocumentFragment();
        meses.forEach(m => {
            let option = document.createElement("option");
            option.value = `${m.anio_faltante}-${m.mes_faltante.toString().padStart(2,0)}-01`;

            option.textContent = `${FormatoFechas.nombreMes(m.mes_faltante)} del ${m.anio_faltante}`.toUpperCase();

            fragment.appendChild(option);
        });
        select_mes.appendChild(fragment);

        llenarDetallesPresupuestos();
    });
}

async function llenarDetallesPresupuestos() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_tipo_gastos');
    const respuesta = await Peticiones.enviar(formData);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const datos = respuestaServidor.datos || [];
        const contenedor = document.getElementById('contenedor_presupuestos');
        contenedor.innerHTML = '';
        
        // Agrupar los conceptos por Tipo de Gasto
        const tiposAgrupados = datos.reduce((acc, fila) => {
            if (!acc[fila.id_tipo_gasto]) {
                acc[fila.id_tipo_gasto] = {
                    id: fila.id_tipo_gasto,
                    nombre: fila.nombre_tipo_gasto,
                    conceptos: []
                };
            }
            if (fila.id_concepto) {
                acc[fila.id_tipo_gasto].conceptos.push({
                    id: fila.id_concepto,
                    nombre: fila.nombre_concepto
                });
            }
            return acc;
        }, {});

        let fragment = document.createDocumentFragment();

        Object.values(tiposAgrupados).forEach(tipo => {
            // Saltamos la reposición de caja chica si es regla de negocio
            if (tipo.nombre.includes("Reposición de Caja Chica")) return;
            if (tipo.conceptos.length === 0) return; // No renderizar si la categoría está vacía

            let nombreFormat = tipo.nombre.replaceAll(" ", "-").toLowerCase();
            let acordeon = document.createElement("div");
            acordeon.className = "accordion col-12 mb-2";
            acordeon.id = nombreFormat;

            let item = document.createElement("div");
            item.className = "accordion-item shadow-sm";
            
            let header = document.createElement("h2");
            header.className = "accordion-header";
            
            let boton = document.createElement("button");
            boton.className = "accordion-button collapsed fw-semibold text-uppercase";
            boton.type = "button";
            boton.setAttribute("tabindex", '-1');
            boton.setAttribute('data-bs-toggle', 'collapse');
            boton.setAttribute('data-bs-target', `#${nombreFormat}-body`);
            // Se le añade un icono para mejor estética visual
            boton.innerHTML = `<i class="bi bi-folder2-open me-2 text-primary"></i> ${tipo.nombre}`;
            header.appendChild(boton);

            let cuerpo = document.createElement("div");
            cuerpo.className = "align-items-center accordion-collapse collapse";
            cuerpo.setAttribute("style", "background-color: transparent !important;");
            cuerpo.id = `${nombreFormat}-body`;

            // Renderizar un input por cada concepto extraído de la BD
            tipo.conceptos.forEach(concepto => {
                cuerpo.appendChild(crearFilaConcepto(concepto.id, concepto.nombre));
            });

            item.appendChild(header);
            item.appendChild(cuerpo);
            acordeon.appendChild(item);
            fragment.appendChild(acordeon);
        });

        contenedor.appendChild(fragment);
        detalles_presupuestos_base = contenedor.innerHTML; // Guardar base para resetModal
    });
}

/**
 * Registrar nuevo presupuesto
 */
async function registrar() {
    let formData = new FormData();
    formData.append('operacion', 'registrar_presupuesto');
    formData.append('tasa_dolar', tasa_dolar);

    let totalDetalles = empaquetarDatosPresupuesto(formData);

    if (totalDetalles === 0) {
        Alertas.mostrar('warning', 'Atención', 'Debe agregar al menos un detalle con monto mayor a 0');
        return;
    }

    let respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        consultarInformacionFormulario();
        tabla_presupuesto.replaceData();
    });
}

/**
 * Modificar presupuesto existente
 */
async function modificar(id) {
    let formData = new FormData();
    formData.append('operacion', 'modificar_presupuesto');
    formData.append('id_presupuesto', id);
    formData.append('tasa_dolar', tasa_dolar);

    let totalDetalles = empaquetarDatosPresupuesto(formData);

    if (totalDetalles === 0) {
        Alertas.mostrar('warning', 'Atención', 'Debe agregar al menos un detalle con monto mayor a 0');
        return;
    }

    let respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_presupuesto.replaceData();
        modal.hide();
    });
}

/**
 * Prepara el formulario para edición
 */
async function prepararFormulario(id) {
    if (document.getElementById("contenedor_presupuestos").childElementCount === 0) {
        await llenarDetallesPresupuestos();
    }

    let formData = new FormData();
    formData.append("id_presupuesto", id);
    formData.append('operacion', 'consultar_presupuesto');

    let respuesta = await Peticiones.enviar(formData, "", true);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let presupuesto = respuestaServidor.datos;

        tasa_dolar = parseFloat(presupuesto.tasa_dolar) || 1;
        // Mostrar la fecha en el select (puede que no esté en la lista, así que la creamos)
        let [anio, mes] = presupuesto.fecha.split('-');
        mes = parseInt(mes);
        let fechaStr = `${anio}-${mes.toString().padStart(2, '0')}-01`;

        let option = document.createElement("option");
        option.value = fechaStr;
        option.textContent = `${FormatoFechas.nombreMes(mes)} del ${anio}`.toUpperCase();
        option.id = 'fecha_editada';
        option.selected = true;
        select_mes.appendChild(option);
        select_mes.value = fechaStr;


        document.getElementById("observacion").value = presupuesto.observacion || '';
        document.getElementById("cuota_reserva").value = presupuesto.cuota_reserva;

        // Calcular el equivalente en dólares
        let inputConvertir = document.getElementById("cuota_reserva").closest(".row").querySelector("[convertido]");
        inputConvertir.value = (presupuesto.cuota_reserva / tasa_dolar).toFixed(2);

        // Marcar detalles existentes
        if (presupuesto.detalles && presupuesto.detalles.length > 0) {
            presupuesto.detalles.forEach(det => {
                let fila = document.querySelector(`.fila-concepto[data-concepto-id='${det.concepto_id}']`);
                if (fila) {
                    let inputMonto = fila.querySelector("input[type='number']");
                    let inputConvertido = fila.querySelector("[convertido]");
                    
                    inputMonto.value = parseFloat(det.monto).toFixed(2);
                    inputConvertido.value = (parseFloat(det.monto) / tasa_dolar).toFixed(2);
                    
                    // Abrir el acordeón para que el usuario vea dónde hay montos asignados
                    let colapso = fila.closest('.accordion-collapse');
                    if (colapso && !colapso.classList.contains('show')) {
                        new bootstrap.Collapse(colapso, { toggle: true });
                    }
                }
            });
        }

        if (permisoModificar != 1) {
            boton_formulario.setAttribute("hidden", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", id);
        // boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = "Modificar presupuesto";
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-journal-minus");

        modal.show();
    });
}

// ELIMINACIÓN
function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Presupuesto?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id); }
    );
}

async function eliminar(id) {
    let formData = new FormData();
    formData.append("id_presupuesto", id);
    formData.append('operacion', 'eliminar_presupuesto');
    let respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_presupuesto.replaceData();
        consultarInformacionFormulario();
    });
}

// MÓDULO DE AYUDA INTERACTIVA
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión de Presupuestos', description: 'Aquí planificas los gastos del mes siguiente para calcular cuánto deberá pagar cada apartamento.', side: "bottom", align: 'center' } },
        // Usamos una lógica segura para encontrar el botón, incluso si está oculto por validaciones PHP
        { element: document.querySelector('#boton_nuevo_registro') || '.card', popover: { title: 'Nuevo Presupuesto', description: 'Si hay meses pendientes por planificar, usa este botón para iniciar la carga de gastos estimados.', side: "bottom", align: 'start' } },
        { element: '#tabla_presupuesto', popover: { title: 'Historial', description: 'Lista de presupuestos registrados. Puedes ver el monto total esperado y la cuota de reserva asignada.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#fecha', popover: { title: 'Periodo', description: 'Selecciona a qué mes corresponde este presupuesto. Solo aparecerán los meses futuros disponibles.', side: 'bottom', align: 'start' } },
        { element: '#cuota_reserva', popover: { title: 'Fondo de Reserva', description: 'Ingresa el monto destinado al fondo de reserva del condominio.', side: 'top', align: 'start' } },
        { element: '.boton_intercambio_cuota', popover: { title: 'Moneda', description: 'Usa este botón si necesitas ingresar el monto de la reserva en Dólares; el sistema hará la conversión.', side: 'top', align: 'start' } },
        { element: '#contenedor_presupuestos', popover: { title: 'Categorías de Gastos', description: 'Aquí aparecerán listados los tipos de gastos (Gas, Luz, Mantenimiento). Despliega cada acordeón para ingresar los montos estimados.', side: 'top', align: 'center' } },
        { element: '#observacion', popover: { title: 'Observaciones', description: 'Añade cualquier nota importante sobre este presupuesto.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el presupuesto para que luego puedas generar las mensualidades de cobro.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_presupuesto',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
