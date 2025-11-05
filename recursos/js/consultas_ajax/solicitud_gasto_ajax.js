consultar()
let data_table, id_eliminado, id_registrado, id_modificar;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_solicitud_gasto");
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.querySelector("#modal_solicitud_gasto"));
let formulario_usar = document.querySelector("#form_solicitud_gasto");

// En caso de que se envie un formulario
function envio(operacion) {
    if (operacion == "Editar") {
        id_modificar = boton_formulario.getAttribute("id_modificar");
        modificar(id_modificar);
    }
    else if (operacion == "Registrar") {
        registrar();
    } else {
        // esto es imposible que pase pero aja
        mensajes('error', 4000, 'Atencion',
            'Ha ocurrido un error durante la operacion, intentelo nuevamente')
    }
}


// Esto es en caso de que uno quite el formulario, le devuelve los valores que tenia
document.querySelector("#modal_solicitud_gasto").addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Solicitud de Gasto";

    // Limpiar mensajes u otros elementos con clase "w-100"
    formulario_usar.querySelectorAll(".w-100").forEach(el => el.textContent = "");

    // Ocultar info presupuesto y campos del formulario
    document.getElementById("info_presupuesto").style.display = "none";
    document.getElementById("campos_formulario_completo").style.display = "none";

    // Resetear selects de mes y año
    document.getElementById("selector_mes").value = "";
    document.getElementById("selector_anio").value = "";

    // Limpiar input oculto del presupuesto
    document.getElementById("presupuesto_id").value = "";

    document.querySelectorAll('.is-valid').forEach(input=>input.classList.remove('is-valid'));
    document.querySelectorAll('.is-invalid').forEach(input=>input.classList.remove('is-invalid'));
});
document.querySelector("#modal_solicitud_gasto").addEventListener("show.bs.modal", async () => {
    await cargarMesesYAniosConPresupuesto();
    document.getElementById("info_presupuesto").style.display = "none";
    document.getElementById("campos_formulario_completo").style.display = "none";
    document.getElementById("presupuesto_id").value = "";
    document.getElementById("selector_mes").value = "";
    document.getElementById("selector_anio").value = "";
});

// Si queremos registrar:

async function registrar() {
    let datos_consulta = new FormData();

    let fecha = document.querySelector("#fecha").value,
        descripcion = document.querySelector("#descripcion").value,
        nombre_solicitante = document.querySelector("#nombre").value,
        monto_estimado = parseFloat(document.querySelector("#monto_estimado").value),
        presupuesto_mensual = document.querySelector("#presupuesto_id").value,
        prioridad = document.querySelector("#prioridad").value;

    let disponible = await consultarPresupuestoDisponible(presupuesto_mensual);
    if (disponible === null) return;

    if (monto_estimado > disponible) {
        mensajes("error", 4000, "Presupuesto insuficiente", `Solo hay Bs. ${disponible.toFixed(2)} disponibles para este mes.`);
        return;
    }

    const estado = "Pendiente";

    datos_consulta.append("fecha", fecha);
    datos_consulta.append("descripcion", descripcion);
    datos_consulta.append("nombre", nombre_solicitante);
    datos_consulta.append("monto_estimado", monto_estimado);
    datos_consulta.append("estado", estado);
    datos_consulta.append("presupuesto_id", presupuesto_mensual);
    datos_consulta.append("prioridad", prioridad);
    datos_consulta.append("operacion", "registrar");

    let respuesta = await query(datos_consulta,'text-secondary');

    if (respuesta && respuesta.estatus) {
        modal.hide();
        formulario_usar.reset();
        formulario_usar.querySelectorAll(".w-100").forEach(el => el.textContent = "");

        id_registrado = await last_id();
        consulta_completada();

        await buscarPresupuesto();
        // Esto es para actualizar el presupuesto mostrado
        await consultar();         

    } else {
        mensajes("error", 4000, "Error", data.mensaje || "No se pudo registrar la solicitud.");
    }
}

//Si queremos consultar
async function consultar() {
    let datos_consulta = new FormData();
    datos_consulta.append('operacion', 'consulta');

    let data = await query(datos_consulta);
    console.log("Datos recibidos:", data);

    if (!(data.estatus == undefined)) {
        mensajes('error', 4000, 'Atención', data.mensaje || 'Error en la consulta');
        return;
    }

    if (data_table) data_table.destroy(); // mata la tabla vieja

    data_table = new DataTable("#tabla_solicitud_gasto", {
        data: data,
        destroy: true,
        responsive: true,
        scrollX: true,
        columns: [
            { data: "fecha_reporte", render: formatearFecha },
            { data: "descripcion_necesidad" },
            { data: "nombre_solicitante" },
            { data: "monto_estimado", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
            { data: "estado" },
            { data: "prioridad", render: obtenerPrioridadTexto },
            {
                data: "id_solicitud",
                render: function (data, type, row) {
                    return `
        <div class="d-flex justify-content-center gap-3">
            ${permiso_editar ? `
                <button type="button"
                        class="btn btn-success col-5 editar"
                        data-bs-toggle="modal"
                        data-bs-target="#modal_solicitud_gasto"
                        value="${data}"
                        title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>` : ''}
            ${permiso_eliminar ? `
                <button type="button"
                        class="btn btn-danger col-5 eliminar"
                        value="${data}"
                        title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>` : ''}
        </div>`;
                },
                className: "text-center",
                orderable: false,
                searchable: false
            }
        ],
        language: {
            processing: "Procesando...",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "Ningún dato disponible en esta tabla",
            info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            infoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
            infoFiltered: "(filtrado de _MAX_ registros)",
            search: "Buscar:",
            loadingRecords: "Cargando...",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "<i class='bi bi-caret-right'></i>",
                previous: "<i class='bi bi-caret-left'></i>"
            },
        }
    });

    // reasignar eventos porque los botones están dentro del render HTML
    setTimeout(() => {
        reasignarEventos();
    }, 100);
    setTimeout(() => {
        data_table.columns.adjust().draw();
    }, 300);
}
function vaciar_tabla() {
    let cuerpo_tabla = document.querySelector('#tabla_solicitud_gasto tbody');
    cuerpo_tabla.textContent = null;
}

function llenarTabla(fila) {
    let cuerpo_tabla = document.querySelector('#tabla_solicitud_gasto tbody');
    let fila_tabla = document.createElement("tr");

    const id_campo = fila["id_solicitud"];

    const fecha = document.createElement("td");
    const descripcion = document.createElement("td");
    const nombre = document.createElement("td");
    const monto = document.createElement("td");
    const estado = document.createElement("td");
    const presupuesto = document.createElement("td");
    const prioridad = document.createElement("td");

    fecha.textContent = formatearFecha(fila["fecha_reporte"]);
    descripcion.textContent = fila["descripcion_necesidad"];
    nombre.textContent = fila["nombre_solicitante"];
    monto.textContent = (fila["monto_estimado"]);
    estado.textContent = fila["estado"];
    presupuesto.textContent = (fila["presupuesto_id"]);
    prioridad.innerHTML = obtenerPrioridadTexto(fila["prioridad"]);


    const acciones = crearBotones(id_campo);


    fila_tabla.appendChild(fecha);
    fila_tabla.appendChild(descripcion);
    fila_tabla.appendChild(nombre);
    fila_tabla.appendChild(monto);
    fila_tabla.appendChild(estado);
    fila_tabla.appendChild(presupuesto);
    fila_tabla.appendChild(prioridad);
    fila_tabla.appendChild(acciones);

    fila_tabla.setAttribute("id", `fila-${id_campo}`);
    fila_tabla.setAttribute("id_solicitud", id_campo);

    cuerpo_tabla.appendChild(fila_tabla);
}

function crearBotones(id) {
    // Creamos los botones de las acciones
    let td = document.createElement("td");
    let acciones = document.createElement("div");
    acciones.setAttribute("class", "row justify-content-evenly");
    // le damos la clases de boostrap para que se vea tu sabe'

    // Lo mismo que arriba, pero con modificar
    let boton_editar = document.createElement("button");

    let icono_editar = document.createElement("i");
    icono_editar.setAttribute("class", "bi bi-pencil-square")
    boton_editar.appendChild(icono_editar);

    boton_editar.setAttribute("type", "button");
    boton_editar.setAttribute("class", "btn btn-success col-lg-2 col-sm-3 col-4");
    boton_editar.setAttribute("tabindex", "-1");
    boton_editar.setAttribute("role", "button");
    boton_editar.setAttribute("aria-disabled", "true");
    boton_editar.setAttribute("data-bs-toggle", "modal");
    boton_editar.setAttribute("data-bs-target", "#modal_usuario");

    boton_editar.setAttribute("title", "Editar");
    boton_editar.setAttribute("value", id);
    boton_editar.addEventListener("click", modificar_formulario)//Esa funcion esta mas abajo

    //Le ponemos los botones al <td><td> de las acciones
    acciones.appendChild(boton_editar);

    if (permiso_eliminar) {
        //creamos el boton de eliminar, le damos valor, y le asignamos la funcion para eliminar
        let boton_eliminar = document.createElement("button");

        let icono_eliminar = document.createElement("i");// le ponemos un icono
        icono_eliminar.setAttribute("class", "bi bi-trash");// y estilos
        boton_eliminar.appendChild(icono_eliminar);

        // le ponemos todos los atributos que lleva este boton
        boton_eliminar.setAttribute("type", "button");
        boton_eliminar.setAttribute("class", "btn btn-danger col-lg-2 col-sm-3 col-4 eliminar");
        boton_eliminar.setAttribute("tabindex", "-1");
        boton_eliminar.setAttribute("role", "button");
        boton_eliminar.setAttribute("aria-disabled", "true");
        // no se para que sirven la mayoria, pero bueno... boostrap

        boton_eliminar.setAttribute("title", "Eliminar");
        boton_eliminar.setAttribute("value", id);// el valor del id para eliminar	

        acciones.appendChild(boton_eliminar);
    }

    td.appendChild(acciones);

    return td;
}

async function eliminar(id) {
    let datos_consulta = new FormData();
    datos_consulta.append("id_solicitud", id);
    datos_consulta.append("operacion", "eliminar");

    let result = await query(datos_consulta);

    if (result && result.estatus) {
        // Usa el API de DataTable correctamente
        let row = data_table.row($(`button[value="${id}"]`).closest("tr"));
        row.remove().draw();

        consulta_completada();
    } else {
        mensajes("error", 4000, "Error", result.mensaje || "No se pudo eliminar la solicitud.");
    }
}

// Esta función prepara el formulario para editar el registro
async function modificar_formulario(e) {


    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_solicitud", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta,'text-secondary');
    const data = respuesta;

    if (!data || !data.id_solicitud) {
        mensajes("error", 3000, "Error", "No se pudo cargar la solicitud.");
        return;
    }

    // Cargar los selects dinámicamente antes de asignar valores
    await cargarMesesYAniosConPresupuesto();

    // Asignar mes y año al selector
    document.querySelector("#selector_mes").value = data.mes;
    document.querySelector("#selector_anio").value = data.anio;

    // Mostrar info presupuesto y campos del formulario
    document.getElementById("info_presupuesto").style.display = "block";
    document.getElementById("campos_formulario_completo").style.display = "block";

    // Asignar valores al formulario
    document.querySelector("#fecha").value = data.fecha_reporte;
    document.querySelector("#descripcion").value = data.descripcion_necesidad;
    document.querySelector("#nombre").value = data.nombre_solicitante;

    const inputMonto = document.querySelector("#monto_estimado");
    inputMonto.value = data.monto_estimado;
    inputMonto.setAttribute("data-original", data.monto_estimado); // ← esta línea es clave

    document.querySelector("#prioridad").value = data.prioridad;
    document.querySelector("#presupuesto_id").value = data.presupuesto_id;

    document.querySelector("#presupuesto_total").textContent = data.monto_presupuesto_total || "-";
    document.querySelector("#presupuesto_disponible").textContent = data.disponible || "-";

    // Configuración del botón
    if (!permiso_editar) {
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    } else {
        boton_formulario.removeAttribute("hidden");
        boton_formulario.removeAttribute("disabled");
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_solicitud);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById("titulo_modal").textContent = "Modificar Solicitud de Gasto";

    id_modificar = id;

    // Mostrar modal al final
    modal.show();
}

async function modificar(id) {
 let monto_input = document.querySelector("#monto_estimado");
    let monto_nuevo = parseFloat(monto_input.value);
    let monto_original = parseFloat(monto_input.getAttribute("data-original") || 0);
    let presupuesto_id = document.querySelector("#presupuesto_id").value; // Corregido el ID

    let disponible = await consultarPresupuestoDisponible(presupuesto_id);
    
    if (disponible === null) return;
    disponible = parseFloat(disponible);
    if (isNaN(disponible)) disponible = 0;
    
    let disponible_real = disponible + monto_original;

    if (isNaN(monto_nuevo) || isNaN(disponible_real)) {
        mensajes("error", 4000, "Error", "Valores inválidos. No se puede validar el presupuesto.");
        return;
    }

    if (monto_nuevo > disponible_real) {
        mensajes("error", 4000, "Presupuesto insuficiente", `Solo hay Bs. ${disponible_real.toFixed(2)} disponibles para este mes.`);
        return;
    }

    // Continúa con el resto del envío
    let datos_consulta = new FormData();

    let fecha = document.querySelector("#fecha").value,
        descripcion = document.querySelector("#descripcion").value,
        nombre_solicitante = document.querySelector("#nombre").value,
        prioridad = document.querySelector("#prioridad").value;
    const estado = "Pendiente";

    datos_consulta.append("id_solicitud", id);
    datos_consulta.append("fecha", fecha);
    datos_consulta.append("descripcion", descripcion);
    datos_consulta.append("nombre", nombre_solicitante);
    datos_consulta.append("monto_estimado", monto_nuevo);
    datos_consulta.append("estado", estado);
    datos_consulta.append("presupuesto_id", presupuesto_id);
    datos_consulta.append("prioridad", prioridad);
    datos_consulta.append("operacion", "modificar");

    let respuesta = await query(datos_consulta,'text-secondary');

    // Resetear el modal
    modal.hide();
    formulario_usar.reset();
    formulario_usar.querySelectorAll(".w-100").forEach(el => el.textContent = "");

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Solicitud de Gasto";

    // Actualizar la tabla
    data_table.row(`#fila-${id}`).remove().draw();

    await consultar();
    consulta_completada();
    reasignarEventos();
}

async function last_id() {
    datos_consulta = new FormData();
    datos_consulta.append("operacion", "ultimo_id");
    let res = await query(datos_consulta);
    return res;
}

async function query(datos,color_carga = 'text-light') {
    document.getElementById('icono_carga').setAttribute("class",`spinner-border ${color_carga}`);
    
    try {
        const res = await fetch("", { method: "POST", body: datos });
        const texto = await res.text();

        // Intenta convertir a JSON
        try {
            return JSON.parse(texto);
        } catch (e) {
            console.error("Respuesta no válida como JSON:", texto);
            mensajes("error", 4000, "Error inesperado", "La respuesta del servidor no es JSON válido.");
            return null;
        }
    } catch (error) {
        console.error("Fallo en la petición:", error);
        mensajes("error", 4000, "Error de red", "No se pudo contactar con el servidor.");
        return null;
    }
}

function consulta_completada() {
    Swal.fire({
        title: "Atencion",
        text: "La operacion se ha realizado correctamente",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#e01d22",
        icon: "success",
    })
}

function init_data_table() {
    return new DataTable("#tabla_solicitud_gasto", {
        destroy: true,
        responsive: true,
        "scrollX": true,
        "pageLength": 10,
        "aaSorting": [],
        language: {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar:",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "<i class='bi bi-caret-right'></i>",
                "previous": "<i class='bi bi-caret-left'></i>"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        }
    })
    // si lees esto tienes que saber que ahora odio estos data table, muerte a jquery...
}

const observer = new MutationObserver(() => {
    reasignarEventos();
});

observer.observe(document.querySelector("#tabla_solicitud_gasto tbody"), {
    childList: true,
    subtree: true
});

function reasignarEventos() {
    document.querySelectorAll(".eliminar").forEach(btn => {
        btn.onclick = (e) => {
            let id = e.target.closest("button").value;
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¿Deseas eliminar esta solicitud?",
                showCancelButton: true,
                confirmButtonText: "Eliminar",
                confirmButtonColor: "#e01d22",
                cancelButtonText: "Cancelar",
                icon: "warning"
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    eliminar(id);
                }
            });
        };
    });

    document.querySelectorAll(".editar").forEach(btn => {
        btn.onclick = modificar_formulario;
    });
}

// ----------- FUNCIONES Y COSAS NUEVAS PARA EL MODULO DE SOLICITUD GASTOS-------------------

document.addEventListener("DOMContentLoaded", () => {
    const selectorMes = document.querySelector("#selector_mes");
    const selectorAnio = document.querySelector("#selector_anio");

    if (selectorMes && selectorAnio) {
        selectorMes.addEventListener("change", buscarPresupuesto);
        selectorAnio.addEventListener("change", buscarPresupuesto);
    }
});

async function buscarPresupuesto() {
    const mes = document.querySelector("#selector_mes").value;
    const anio = document.querySelector("#selector_anio").value;

    const infoPresupuesto = document.querySelector("#info_presupuesto");
    const camposFormulario = document.querySelector("#campos_formulario_completo");
    const inputPresupuestoId = document.querySelector("#presupuesto_id"); // Corregido el ID
    const spanTotal = document.querySelector("#presupuesto_total");
    const spanDisponible = document.querySelector("#presupuesto_disponible");

    if (!mes || !anio) return;

    const datos = new FormData();
    datos.append("operacion", "buscar_presupuesto_por_mes_anio");
    datos.append("mes", mes);
    datos.append("anio", anio);

    const respuesta = await query(datos);

    if (respuesta && respuesta.estatus) {
        // Se convierte a número y si el resultado es NaN, se usa 0.
        let total = parseFloat(respuesta.monto_presupuesto_total);
        let disponible = parseFloat(respuesta.disponible);

        if (isNaN(total)) total = 0;
        if (isNaN(disponible)) disponible = 0;

        // Se muestra el número formateado a 2 decimales.
        spanTotal.textContent = total.toFixed(2);
        spanDisponible.textContent = disponible.toFixed(2);

        inputPresupuestoId.value = respuesta.id_presupuesto;

        infoPresupuesto.style.display = "flex";
        camposFormulario.style.display = "block";
    } else {
        mensajes("error", 4000, "Presupuesto no encontrado", data.mensaje || "No hay presupuesto para esa fecha.");
        infoPresupuesto.style.display = "none";
        camposFormulario.style.display = "none";
        inputPresupuestoId.value = "";
    }
}



// Al cambiar el select
async function cargarDatosPorMes(e) {
    const fecha = e.target.value;
    if (!fecha) return;

    await cargarSolicitudesPorMes(fecha);
}

// Mostrar solicitudes del mes seleccionado
async function cargarSolicitudesPorMes(fecha) {
    const datos = new FormData();
    datos.append("operacion", "filtrar_solicitudes_mes");
    datos.append("fecha", fecha);

    const respuesta = await query(datos);

    vaciar_tabla(); // función existente
    if (respuesta && Array.isArray(respuesta)) {
        if (data_table) data_table.destroy();

        vaciar_tabla();

        respuesta.forEach(solicitud => llenarTabla(solicitud));

        data_table = init_data_table();
    }
}

function obtenerNombreMes(numeroMes) {
    const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    const indice = parseInt(numeroMes, 10) - 1;
    return meses[indice] || "Mes inválido";
}

// Consultar presupuesto disponible
async function consultarPresupuestoDisponible(presupuesto_id) {
    const datos = new FormData();
    datos.append("operacion", "consultar_presupuesto");
    datos.append("presupuesto_id", presupuesto_id);

    const respuesta = await query(datos);

    if (respuesta && respuesta.estatus) {
        return parseFloat(respuesta.disponible);
    } else {
        mensajes("error", 4000, "Error", data.mensaje || "No se pudo consultar el presupuesto.");
        return null; // Importante para saber si falló
    }
}

function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`; // DD-MM-AAAA
    }
    return fechaStr; // En caso de error, retorna original
}

async function cargarMesesYAniosConPresupuesto() {
    const datos = new FormData();
    datos.append("operacion", "meses_anios_con_presupuesto");

    const respuesta = await query(datos);

    // Se comprueba que la respuesta y los datos sean válidos
    if (respuesta && respuesta.estatus && Array.isArray(respuesta.data)) {
        const selectorMes = document.getElementById("selector_mes");
        const selectorAnio = document.getElementById("selector_anio");

        // Limpiar actuales
        selectorMes.innerHTML = `<option value="">Seleccione mes</option>`;
        selectorAnio.innerHTML = `<option value="">Seleccione año</option>`;

        const mesesUnicos = new Set();
        const aniosUnicos = new Set();

        respuesta.data.forEach(p => {
            mesesUnicos.add(parseInt(p.mes));
            aniosUnicos.add(parseInt(p.anio));
        });

        const nombresMeses = [
            "", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        
        // Se crean los elementos de forma más segura
        [...mesesUnicos].sort((a, b) => a - b).forEach(mes => {
            const opcion = document.createElement('option');
            opcion.value = mes;
            opcion.textContent = nombresMeses[mes];
            selectorMes.appendChild(opcion);
        });

        [...aniosUnicos].sort((a, b) => b - a).forEach(anio => {
            const opcion = document.createElement('option');
            opcion.value = anio;
            opcion.textContent = anio;
            selectorAnio.appendChild(opcion);
        });
    } else {
        mensajes("error", 4000, "Error", respuesta?.mensaje || "No se pudo cargar la lista de presupuestos.");
    }
}
function obtenerPrioridadTexto(prioridad) {
    let texto = "";
    let color = "";

    switch (prioridad) {
        case "1":
        case 1:
            texto = "Alta";
            color = "success";
            break;
        case "2":
        case 2:
            texto = "Media";
            color = "warning";
            break;
        case "3":
        case 3:
            texto = "Baja";
            color = "danger";
            break;
        default:
            texto = "Desconocida";
            color = "secondary";
    }

    return `<span class="badge bg-${color}">${texto}</span>`;
}







