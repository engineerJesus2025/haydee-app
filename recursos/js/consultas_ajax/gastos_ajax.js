let data_table, id_eliminado, id_registrado, id_modificar;
let id_detalle_gasto = null, id_banco_transaccion; // Variable para almacenar el ID del detalle de gasto

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_gastos");
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.querySelector("#modal_gastos"));
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa"));
let formulario_usar = document.querySelector("#form_gastos");

let modal_carga = new bootstrap.Modal("#modal_carga");
let peticionesActivas = 0;
let ultimaPeticion = 0;
let tiempoCarga;
let tiempoInicio;

consultar();

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

document.querySelector('#modal_gastos').addEventListener('hidden.bs.modal', () => {
    // 1. Restaura el botón y el título del modal a su estado de "Registrar"
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Gasto";

    // 2. Elimina los bloques de detalle adicionales, dejando solo el primero.
    const container = formulario_usar.querySelector("#detalles-container");
    const bloques = container.querySelectorAll(".detalle-gasto");
    bloques.forEach((bloque, index) => {
        if (index > 0) { // Si es un bloque adicional (no el primero)
            bloque.remove();
        }
    });

    // 3. Resetea los valores de todos los inputs del formulario a su valor por defecto
    formulario_usar.reset();

    // 4. Limpia y reconfigura el primer (y ahora único) bloque de detalle.
    const primerBloque = container.querySelector(".detalle-gasto");
    if (primerBloque) {
        // Limpia el texto del comprobante cargado
        const nombreImagen = primerBloque.querySelector(".nombre_imagen_cargada");
        if (nombreImagen) nombreImagen.textContent = '';

        // Oculta el botón de eliminar imagen (si existe)
        const btnEliminarImagen = primerBloque.querySelector(".boton_eliminar_imagen");
        if (btnEliminarImagen) {
            btnEliminarImagen.classList.add('d-none');
            btnEliminarImagen.removeAttribute("data-nombre");
        }

        // Elimina el botón "Eliminar este Detalle" que pudo ser añadido en modo edición
        const btnEliminarDetalle = primerBloque.querySelector('.btn-outline-danger');
        if (btnEliminarDetalle) btnEliminarDetalle.remove();

        // Restaura la visibilidad de los campos según el método de pago por defecto
        const metodoPagoSelect = primerBloque.querySelector('.metodo_pago');
        if (metodoPagoSelect) {
            actualizarVisibilidadCampos(metodoPagoSelect);
        }
    }

    // 5. Elimina cualquier input oculto que se haya añadido (como 'eliminar_imagen')
    const inputOculto = formulario_usar.querySelector("input[name='eliminar_imagen']");
    if (inputOculto) inputOculto.remove();
});

// Si queremos registrar:

async function registrar() {
    let datos_consulta = new FormData();

    // Campos simples
    let tipo = document.querySelector("#tipo").value,
        tipo_gasto = document.querySelector("#tipo_gasto").value,
        solicitud = document.querySelector("#solicitud").value,
        proveedor = formulario_usar.querySelector("#proveedor").value,
        descripcion_gasto = formulario_usar.querySelector("#descripcion_gasto").value;

    datos_consulta.append("tipo", tipo);
    datos_consulta.append("tipo_gasto", tipo_gasto);
    datos_consulta.append("solicitud", solicitud);
    datos_consulta.append("proveedor", proveedor);
    datos_consulta.append("descripcion_gasto", descripcion_gasto);

    // Bloques de detalle
    let bloques_detalle = formulario_usar.querySelectorAll(".detalle-gasto");
    let total = bloques_detalle.length;

    if (total === 0) {
        mensajes("error", 4000, "Error", "Debes agregar al menos un detalle de gasto.");
        return;
    }

    let monto_total = 0;
    let ultima_fecha = "";

    for (let i = 0; i < total; i++) {
        const bloque = bloques_detalle[i];

        let metodo_pago = bloque.querySelector(".metodo_pago"),
            fecha = bloque.querySelector(".fecha_detalle"),
            referencia = bloque.querySelector(".referencia"),
            descripcion = bloque.querySelector(".descripcion_detalle"),
            banco = bloque.querySelector(".banco"),
            imagen = bloque.querySelector(".imagen"),
            monto = bloque.querySelector(".monto");

        if (
            !metodo_pago || !fecha || !referencia || !descripcion ||
            !banco || !monto
        ) {
            mensajes("error", 4000, "Error", "Faltan campos en uno de los detalles.");
            return;
        }

        datos_consulta.append("metodo_pago[]", metodo_pago.value);
        datos_consulta.append("fecha_detalle[]", fecha.value);
        datos_consulta.append("referencia[]", referencia.value);
        datos_consulta.append("descripcion_detalle[]", descripcion.value);
        datos_consulta.append("banco[]", banco.value);
        datos_consulta.append("monto[]", monto.value);

        monto_total += parseFloat(monto.value) || 0;
        if (fecha.value) {
            ultima_fecha = fecha.value;
        }

        if (imagen && imagen.files[0]) {
            datos_consulta.append("imagen[]", imagen.files[0]);
        } else {
            datos_consulta.append("imagen[]", ""); // Para mantener alineados los índices
        }
    }

    datos_consulta.append("operacion", "registrar");

    let respuesta = await query(datos_consulta);
    if (respuesta && !respuesta.estatus) {
        mensajes("error", 4000, "Atención", respuesta.mensaje);
        return;
    }

    let fila = {
        id_gasto: respuesta.gasto.id_gasto,
        tipo: respuesta.gasto.tipo,
        tipo_gasto: respuesta.gasto.tipo_gasto,
        proveedor: respuesta.gasto.proveedor,
        descripcion_gasto: respuesta.gasto.descripcion_gasto,
        monto_total: respuesta.gasto.monto_total,
        ultima_fecha: respuesta.gasto.ultima_fecha
    };

    modal.hide();


    formulario_usar.reset();
    mensajes("success", 4000, "Éxito", "El registro se ha realizado exitosamenteeeeeeeee");

    // Elimina todos los bloques excepto el primero
    const bloques = formulario_usar.querySelectorAll(".detalle-gasto");
    bloques.forEach((bloque, i) => i > 0 && bloque.remove());

    // Crear acciones y añadir fila a la tabla
    let acciones = crearBotones(fila.id_gasto);

    const filaDatos = [
        formatearFecha(respuesta.gasto.ultima_fecha) || "N/A",
        formatearMontoConMoneda(respuesta.gasto.monto_total, respuesta.gasto.metodo_pago_predominante),
        mayuscula(respuesta.gasto.tipo) || "N/A",
        mayuscula(respuesta.gasto.nombre_tipo_gasto) || "N/A", // Corregido
        respuesta.gasto.nombre_proveedor || "N/A",            // Corregido
        respuesta.gasto.descripcion_gasto || "N/A",
        acciones.outerHTML || ""
    ];

    let nuevaFila = data_table.row.add(filaDatos).draw(false).node();
    data_table.row(nuevaFila).data(filaDatos).draw(false);
    data_table.columns.adjust().draw(false);

    id_registrado = { mensaje: fila.id_gasto };

    setTimeout(() => {
        reasignarEventos();
    }, 100); // Esperamos 100 milisegundos antes de reasignar los eventos

    mensajes("success", 4000, "Éxito", "El registro se ha realizado exitosamente");
}


// Si queremos consultar:
async function consultar() {

    datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta");
    data = await query(datos_consulta);
    vaciar_tabla();


    if (!(data.estatus == undefined)) {
        mensajes('error', 4000, 'Atencion', respuesta.mensaje);
        return;// en caso de error mandamos un mensaje con el error y nos vamos
    }


    await data.map(fila => {
        llenarTabla(fila);
    })

    data_table = init_data_table();
}

function vaciar_tabla() {
    let cuerpo_tabla = document.querySelector('#tabla_gastos tbody');
    cuerpo_tabla.textContent = null;
}

function formatearMonto(monto) {
    const numero = parseFloat(monto);
    if (isNaN(numero)) return "0,00";

    return numero.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    if (partes.length === 3) {
        return `${partes[2]}-${partes[1]}-${partes[0]}`; // DD-MM-AAAA
    }
    return fechaStr; // En caso de error, retorna original
}


function llenarTabla(fila) {
    let cuerpo_tabla = document.querySelector('#tabla_gastos tbody');
    let fila_tabla = document.createElement("tr");

    const id_campo = fila["id_gasto"];

    const fecha = document.createElement("td");
    const monto = document.createElement("td");
    const tipo = document.createElement("td");
    const tipo_gasto = document.createElement("td");
    const proveedor = document.createElement("td");
    const descripcion_gasto = document.createElement("td");

    fecha.textContent = formatearFecha(fila["ultima_fecha"]);
    monto.textContent = formatearMontoConMoneda(fila["monto_total"], fila["metodo_pago_predominante"]);


    tipo.textContent = mayuscula(fila["tipo"]);
    tipo_gasto.textContent = mayuscula(fila["nombre_tipo_gasto"]);
    proveedor.textContent = fila["nombre_proveedor"] || "N/A";
    descripcion_gasto.textContent = fila["descripcion_gasto"];

    const acciones = crearBotones(id_campo);

    fila_tabla.appendChild(fecha);
    fila_tabla.appendChild(monto);
    fila_tabla.appendChild(tipo);
    fila_tabla.appendChild(tipo_gasto);
    fila_tabla.appendChild(proveedor);
    fila_tabla.appendChild(descripcion_gasto);
    fila_tabla.appendChild(acciones);

    fila_tabla.setAttribute("id", `fila-${id_campo}`);
    fila_tabla.setAttribute("id_gasto", id_campo);

    cuerpo_tabla.appendChild(fila_tabla);
}


function crearBotones(id) {
    let td = document.createElement("td");
    let acciones = document.createElement("div");
    acciones.setAttribute("class", "row justify-content-evenly");

    // BOTON DE VISTA PREVIA CON EL OJITO
    let boton_vista_previa = document.createElement("button");
    let icono_ver = document.createElement("i");
    icono_ver.setAttribute("class", "bi bi-eye-fill");
    boton_vista_previa.appendChild(icono_ver);
    boton_vista_previa.setAttribute("type", "button");
    boton_vista_previa.setAttribute("class", "btn btn-primary btn-sm col-3");
    boton_vista_previa.setAttribute("title", "Vista previa");
    boton_vista_previa.setAttribute("value", id);
    boton_vista_previa.addEventListener("click", mostrarVistaPrevia);
    acciones.appendChild(boton_vista_previa);

    let boton_editar = document.createElement("button");
    let icono_editar = document.createElement("i");
    icono_editar.setAttribute("class", "bi bi-pencil-square");
    boton_editar.appendChild(icono_editar);
    boton_editar.setAttribute("type", "button");
    boton_editar.setAttribute("class", "btn btn-success btn-sm col-3");
    boton_editar.setAttribute("tabindex", "-1");
    boton_editar.setAttribute("role", "button");
    boton_editar.setAttribute("aria-disabled", "true");
    boton_editar.setAttribute("data-bs-toggle", "modal");
    boton_editar.setAttribute("data-bs-target", "#modal_gastos");
    boton_editar.setAttribute("title", "Editar");
    boton_editar.setAttribute("value", id);
    boton_editar.addEventListener("click", modificar_formulario)
    acciones.appendChild(boton_editar);

    if (permiso_eliminar) {
        let boton_eliminar = document.createElement("button");
        let icono_eliminar = document.createElement("i");
        icono_eliminar.setAttribute("class", "bi bi-trash3-fill");
        boton_eliminar.appendChild(icono_eliminar);

        boton_eliminar.setAttribute("type", "button");
        boton_eliminar.setAttribute("class", "btn btn-danger btn-sm eliminar col-3");
        boton_eliminar.setAttribute("tabindex", "-1");
        boton_eliminar.setAttribute("role", "button");
        boton_eliminar.setAttribute("aria-disabled", "true");

        boton_eliminar.setAttribute("title", "Eliminar");
        boton_eliminar.setAttribute("value", id);

        acciones.appendChild(boton_eliminar);
    }
    td.appendChild(acciones);
    return td;
}

async function eliminar(id) {
    datos_consulta = new FormData();
    datos_consulta.append("id_gasto", id);
    datos_consulta.append("operacion", "eliminar");
    let result = await query(datos_consulta);
    id_eliminado = id;

    data_table.row(`#fila-${id}`).remove().draw();

    consulta_completada();
}

// Esta funcion prepara el formulario para editar el registro
async function modificar_formulario(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_gasto", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);

    if (!respuesta || !respuesta.gasto) {
        mensajes("error", 4000, "Error", "No se pudieron cargar los datos para modificar.");
        return;
    }

    const dataGasto = respuesta.gasto;
    const detalles = respuesta.detalles;

    // Llenar campos principales
    formulario_usar.querySelector("#tipo").value = dataGasto.tipo;
    formulario_usar.querySelector("#tipo_gasto").value = dataGasto.tipo_gasto_id;
    formulario_usar.querySelector("#solicitud").value = dataGasto.solicitud_id;
    formulario_usar.querySelector("#descripcion_gasto").value = dataGasto.descripcion_gasto;
    formulario_usar.querySelector("#proveedor").value = dataGasto.proveedor_id;

    // Reconstruir bloques de detalles
    const detallesContainer = formulario_usar.querySelector("#detalles-container");
    detallesContainer.innerHTML = ''; 
    const plantilla = document.getElementById('plantilla-detalle-gasto');

    if (detalles && detalles.length > 0) {
        detalles.forEach((detalle, index) => {
            const nuevoBloque = plantilla.content.firstElementChild.cloneNode(true);

            // Añadir botón de eliminar si no es el primer detalle
            if (index > 0) {
                const eliminarBtn = document.createElement('button');
                eliminarBtn.className = 'btn btn-sm btn-outline-danger mb-3';
                eliminarBtn.type = 'button';
                eliminarBtn.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar este Detalle';
                eliminarBtn.onclick = () => nuevoBloque.remove();
                nuevoBloque.querySelector('.card-body').prepend(eliminarBtn);
            }

            // Llenar campos del detalle
            nuevoBloque.querySelector(".fecha_detalle").value = detalle.fecha;
            nuevoBloque.querySelector(".metodo_pago").value = detalle.metodo_pago;
            nuevoBloque.querySelector(".monto").value = detalle.monto;
            nuevoBloque.querySelector(".descripcion_detalle").value = detalle.descripcion_detalle_gasto;

            // Mostrar campos condicionales
            const metodo = detalle.metodo_pago.toLowerCase();
            if (metodo === 'transferencia' || metodo === 'pago movil') {
                nuevoBloque.querySelector(".grupo_referencia").classList.remove('d-none');
                nuevoBloque.querySelector(".grupo_banco").classList.remove('d-none');
                nuevoBloque.querySelector(".grupo_imagen").classList.remove('d-none');
                nuevoBloque.querySelector(".referencia").value = detalle.referencia || '';
                nuevoBloque.querySelector(".banco").value = detalle.id_banco || '';
                if (detalle.imagen) {
                    nuevoBloque.querySelector(".nombre_imagen_cargada").textContent = `Comprobante cargado: ${detalle.imagen}`;
                }
            }

            // Si el detalle tiene imagen, crea un input oculto para conservar el nombre
            if (detalle.imagen && detalle.imagen.trim() !== "") {
                const inputImagenExistente = document.createElement('input');
                inputImagenExistente.type = 'hidden';
                inputImagenExistente.name = 'imagen_existente[]';
                inputImagenExistente.value = detalle.imagen;
                nuevoBloque.appendChild(inputImagenExistente);
            }

            agregarEventosMetodoPago(nuevoBloque);
            detallesContainer.appendChild(nuevoBloque);
        });
    }

    // Configurar botón del modal
    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", dataGasto.id_gasto);
    boton_formulario.textContent = "Modificar";
    document.getElementById("titulo_modal").textContent = "Modificar Gasto";
    id_modificar = id;
}


//FUNCIONALIDAD DE LA VISTA PREVIA
async function mostrarVistaPrevia(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_gasto", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta;

    document.getElementById("vista_fecha").textContent = formatearFecha(data.gasto.ultima_fecha);

    await consultar_detalles(data.gasto.id_gasto);
    // Mostrar el modal como los otros
    modalVistaPrevia.show();

}


function abrirModalRegistrar() {
    const modalVistaPreviaEl = document.getElementById('modal_vista_previa');

    function abrirDetalles() {
        modal_detalles.show();
        modalVistaPreviaEl.removeEventListener('hidden.bs.modal', abrirDetalles);
    }

    // Primero elimina para evitar duplicados
    modalVistaPreviaEl.removeEventListener('hidden.bs.modal', abrirDetalles);
    // Luego añade el listener
    modalVistaPreviaEl.addEventListener('hidden.bs.modal', abrirDetalles);

    // Finalmente cierra la vista previa
    modalVistaPrevia.hide();
}
document.getElementById('modal_detalles_gastos').addEventListener('hidden.bs.modal', () => {
    modalVistaPrevia.show();
});




function actualizarVisibilidadCamposVistaPrevia(metodo_pago) {
    const grupoReferencia = document.getElementById("grupo_referencia");
    const grupoBanco = document.getElementById("grupo_banco");
    const grupoImagen = document.getElementById("grupo_imagen");

    const valor = metodo_pago.toLowerCase().trim(); // ← IMPORTANTE

    const mostrar = (valor === "transferencia" || valor === "pago movil" || valor === "pago_movil");

    grupoReferencia.style.display = mostrar ? "block" : "none";
    grupoBanco.style.display = mostrar ? "block" : "none";
    grupoImagen.style.display = mostrar ? "block" : "none";
}


async function modificar(id) {
    // ✅ CAMBIO CLAVE: Se crea el FormData a partir del formulario HTML.
    // Esto captura AUTOMÁTICAMENTE todos los campos: textos, selects, archivos 
    // y, lo más importante, el input oculto 'imagen_existente[]'.
    let datos_consulta = new FormData(formulario_usar);

    // Ahora, solo añadimos los datos que NO están en el formulario.
    datos_consulta.append("id_gasto", id);
    datos_consulta.append("operacion", "modificar");

    // El resto de la función para enviar y procesar la respuesta es igual.
    let respuesta = await query(datos_consulta);

    if (respuesta && !respuesta.estatus) {
        mensajes("error", 4000, "Atención", respuesta.mensaje);
        return;
    }

    modal.hide();

    // Actualizamos la tabla de DataTables: eliminamos la fila vieja y añadimos la nueva.
    data_table.row(`#fila-${id}`).remove().draw(false);

    let fila = respuesta.gasto;
    let acciones = crearBotones(fila.id_gasto);

    const filaDatos = [
        formatearFecha(fila.ultima_fecha) || "N/A",
        formatearMontoConMoneda(fila.monto_total, fila.metodo_pago_predominante),
        mayuscula(fila.tipo) || "N/A",
        mayuscula(fila.nombre_tipo_gasto) || "N/A",
        fila.nombre_proveedor || "N/A",
        fila.descripcion_gasto || "N/A",
        acciones.outerHTML || ""
    ];

    let nuevaFila = data_table.row.add(filaDatos).draw(false).node();
    nuevaFila.id = `fila-${fila.id_gasto}`; 

    mensajes("success", 4000, "Éxito", "El gasto se ha modificado exitosamente");
}


// Cargar los meses y años al cargar la página


async function last_id() {
    datos_consulta = new FormData();
    datos_consulta.append("operacion", "ultimo_id");
    let res = await query(datos_consulta);
    return res;
}

async function query(datos) {
    peticionesActivas++;

    const tiempoInicio = performance.now();

    ultimaPeticion = tiempoInicio;

    if (peticionesActivas === 1) {
        tiempoCarga = setTimeout(()=>{
            modal_carga.show();
        }, 200);
    }

    try{
        let data = await fetch("",{method:"POST", body:datos}).then(res=>{      
        let result = res.json()
            return result;
        });
        return data;
    }
    catch(error){
        console.log(error);
        return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
    }
    finally{
        peticionesActivas--;

        if (peticionesActivas === 0) {
            const espera = 50;
            setTimeout(()=>{
                if (peticionesActivas === 0) {
                    clearTimeout(tiempoCarga);

                    const tiempoTranscurido = performance.now() - tiempoInicio;
                    const tiempoEsperaMin = 300;

                    if (tiempoTranscurido < tiempoEsperaMin) {
                        const restante = tiempoEsperaMin - tiempoTranscurido;
                        setTimeout(()=>{
                            if (performance.now() - ultimaPeticion >= restante) {
                                modal_carga.hide();
                            }
                        },restante);
                    }
                    else{
                        modal_carga.hide();
                    }
                }
            }, espera);
        }
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
    return new DataTable("#tabla_gastos", {
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

observer.observe(document.querySelector("#tabla_gastos tbody"), {
    childList: true,
    subtree: true
});

function reasignarEventos() {
    if (id_eliminado) { //Si hay un eliminado que no se ha quitado de la tabla
        let existe_fila = tabla.querySelector(`#fila-${id_eliminado}`)
        if (existe_fila) {
            data_table.row(`#fila-${id_eliminado}`).remove().draw();
            id_eliminado = null;
        }
    }

    // Se asigna el evento eliminar para los botones
    $(".eliminar").on("click", function (e) {
        let id = e.target.value;
        if (id == undefined) {
            id = e.target.parentElement.value;
        }
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Está seguro que desea eliminar este gasto?",
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
    });

    // Bucle para VISTA PREVIA
    document.querySelectorAll("button[title='Vista previa']").forEach(btn => {
        btn.removeEventListener("click", mostrarVistaPrevia);
        btn.addEventListener("click", mostrarVistaPrevia);
    });

    document.querySelectorAll("button[title='Editar']").forEach(btn => {
        btn.removeEventListener("click", modificar_formulario); // Evita duplicados
        btn.addEventListener("click", modificar_formulario); // Asigna el evento
    });

    if (id_registrado) {
        let fila_nueva = tabla.querySelector(`button[value='${id_registrado.mensaje}']`).closest("tr");
        if (fila_nueva && !fila_nueva.id) {
            fila_nueva.setAttribute("id", `fila-${id_registrado.mensaje}`);
        }
        id_registrado = null;
    }
}


// ----------- FUNCIONES Y COSAS NUEVAS PARA EL MODULO DE GASTOS-------------------

function actualizarVisibilidadCampos(select) {
    const bloque = select.closest('.detalle-gasto');
    const grupoReferencia = bloque.querySelector(".grupo_referencia");
    const grupoBanco = bloque.querySelector(".grupo_banco");
    const grupoImagen = bloque.querySelector(".grupo_imagen");

    const valor = select.value.toLowerCase();

    // Determina si los campos deben mostrarse
    const mostrarCampos = (valor === "pago movil" || valor === "transferencia");

    // Usa classList para añadir o quitar la clase d-none
    grupoReferencia.classList.toggle('d-none', !mostrarCampos);
    grupoBanco.classList.toggle('d-none', !mostrarCampos);
    grupoImagen.classList.toggle('d-none', !mostrarCampos);
}

function agregarEventosMetodoPago(bloque) {
    const metodoPagoSelect = bloque.querySelector('.metodo_pago');
    metodoPagoSelect.addEventListener('change', function () {
        actualizarVisibilidadCampos(this);
    });

    // Aplicar visibilidad inicial
    actualizarVisibilidadCampos(metodoPagoSelect);
}

document.addEventListener('DOMContentLoaded', () => {
    // Inicializa visibilidad en el bloque original
    document.querySelectorAll('.detalle-gasto').forEach(b => agregarEventosMetodoPago(b));

    // Botón de agregar nuevo detalle
   document.getElementById('agregar_detalle').addEventListener('click', () => {
    const container = document.getElementById('detalles-container');
    
    // Clonar siempre desde la plantilla limpia para evitar heredar estados
    const plantilla = document.getElementById('plantilla-detalle-gasto');
    const nuevoDetalle = plantilla.content.firstElementChild.cloneNode(true);

    // Limpia los valores de los inputs
    nuevoDetalle.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.type !== 'hidden') el.value = '';
    });
    const nombreImagen = nuevoDetalle.querySelector('.nombre_imagen_cargada');
    if (nombreImagen) nombreImagen.textContent = '';
    
    // Añade el botón para eliminar este nuevo bloque
    const eliminarBtn = document.createElement('button');
    eliminarBtn.className = 'btn btn-sm btn-outline-danger mb-3';
    eliminarBtn.type = 'button';
    eliminarBtn.innerHTML = '<i class="bi bi-x-circle"></i> Eliminar este Detalle';
    eliminarBtn.onclick = () => nuevoDetalle.remove();
    
    const cardBody = nuevoDetalle.querySelector('.card-body');
    cardBody.prepend(eliminarBtn);

    // Aplica la lógica de visibilidad al nuevo bloque
    agregarEventosMetodoPago(nuevoDetalle);

    // Añade el nuevo bloque al formulario
    container.appendChild(nuevoDetalle);
});
});


// Mostrar los totales por método de pago
async function cargarTotalesMetodoPago(fecha) {
    const datos = new FormData();
    datos.append("operacion", "totales_metodo_pago");
    datos.append("fecha", fecha);

    const respuesta = await query(datos);
    const cuerpo_tabla = document.querySelector("#tabla_totales tbody");
    cuerpo_tabla.textContent = "";

    let totalBs = 0;
    let totalDolares = 0;

    if (respuesta && Array.isArray(respuesta) && respuesta.length > 0) {
        respuesta.forEach(item => {
            const fila = document.createElement("tr");

            const metodo = document.createElement("td");
            metodo.textContent = formatearMetodo(item.metodo_pago);

            const total = document.createElement("td");
            const monto = parseFloat(item.total);

            if (item.metodo_pago === "efectivo") {
                total.textContent = `${formatearMonto(monto)} $`;
                totalDolares += monto;
            } else {
                total.textContent = `${formatearMonto(monto)} Bs`;
                totalBs += monto;
            }

            fila.appendChild(metodo);
            fila.appendChild(total);
            cuerpo_tabla.appendChild(fila);
        });

        // Fila de total Bs
        const filaTotalBs = document.createElement("tr");
        const celdaLabelBs = document.createElement("td");
        celdaLabelBs.innerHTML = "<strong>Total Bs</strong>";
        const celdaTotalBs = document.createElement("td");
        celdaTotalBs.innerHTML = `<strong>${formatearMonto(totalBs)} Bs</strong>`;
        filaTotalBs.appendChild(celdaLabelBs);
        filaTotalBs.appendChild(celdaTotalBs);
        cuerpo_tabla.appendChild(filaTotalBs);

        // Fila de total $
        const filaTotalUsd = document.createElement("tr");
        const celdaLabelUsd = document.createElement("td");
        celdaLabelUsd.innerHTML = "<strong>Total $</strong>";
        const celdaTotalUsd = document.createElement("td");
        celdaTotalUsd.innerHTML = `<strong>${formatearMonto(totalDolares)} $</strong>`;
        filaTotalUsd.appendChild(celdaLabelUsd);
        filaTotalUsd.appendChild(celdaTotalUsd);
        cuerpo_tabla.appendChild(filaTotalUsd);
    } else {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = 2;
        celda.textContent = "No hay datos disponibles.";
        fila.appendChild(celda);
        cuerpo_tabla.appendChild(fila);
    }
}
function formatearMetodo(metodo) {
    switch (metodo) {
        case "pago_movil": return "Pago Móvil";
        case "transferencia": return "Transferencia";
        case "efectivo": return "Efectivo";
        case "variable": return "Variable";
        case "fijo": return "Fijo";
        default: return metodo;
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

function mayuscula(texto) {
    if (!texto) return "";
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
}

function formatearMontoConMoneda(monto, metodo_pago) {
    const numero = parseFloat(monto);
    if (isNaN(numero)) return "0,00 Bs"; // Valor por defecto

    const montoFormateado = numero.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    if (metodo_pago && metodo_pago.toLowerCase().includes('efectivo')) {
        return `$ ${montoFormateado}`;
    } else {
        return `${montoFormateado} Bs`;
    }
}




// -------DETALLES GASTOS-------------------
let data_table_detalles, id_eliminado_detalles, id_registrado_detalles, id_modificar_detalles, referencia_an_detalles;
let tabla_detalles = document.querySelector("#tabla_detalles_gastos");
let boton_formulario_detalles = document.querySelector("#boton_formulario_detalles");
let modal_detalles = new bootstrap.Modal("#modal_detalles_gastos");
let formulario_usar_detalles = document.querySelector(`#form_detalles_gastos`);
let modalVistaPrevia_detalles = new bootstrap.Modal(document.querySelector("#modal_vista_previa_detalles"));

function envio_detalles(operacion) {
    if (operacion == "Editar") {
        /*
            - getAttribute: es un método que permite obtener el valor de un atributo 
            específico de un elemento HTML.

            y "envio", "modificar", "registrar", "mensajes" son funciones definidas 
            por nosotroso mismos para que no te confundas.
        */
        id_modificar_detalles = boton_formulario_detalles.getAttribute("id_modificar");//obtenemos el id del registro
        modificar_detalles(id_modificar_detalles);
    }
    else if (operacion == "Registrar") {
        //sino a registrar
        registrar_detalles();
    } else {
        // esto es imposible que pase pero aja
        mensajes('error', 4000, 'Atencion',
            'Ha ocurrido un error durante la operacion, intentelo nuevamente')
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const modal_detalles_element = document.querySelector(`#modal_detalles_gastos`);
    if (!modal_detalles_element) return console.error("❌ No se encontró el modal de detalles en el DOM.");

    modal_detalles_element.addEventListener("hide.bs.modal", () => {
        formulario_usar_detalles.reset();
        boton_formulario_detalles.removeAttribute("modificar");
        boton_formulario_detalles.removeAttribute("id_modificar");
        boton_formulario_detalles.textContent = "Registrar";

        const tituloModal = document.getElementById('titulo_modal_detalles');
        if (tituloModal) {
            tituloModal.textContent = "Registrar Detalle Gasto";
        }

        formulario_usar_detalles.querySelectorAll("[class='w-100']").forEach(el => el.textContent = "");

        const nombreImagen = document.querySelector("#nombre_imagen_cargada_detalles");
        if (nombreImagen) nombreImagen.textContent = "";

        const btnEliminarImg = document.querySelector("#boton_eliminar_imagen_detalles");
        if (btnEliminarImg) {
            btnEliminarImg.classList.add("d-none");
            btnEliminarImg.removeAttribute("data-nombre");
        }

        const inputOculto = formulario_usar_detalles.querySelector("input[name='eliminar_imagen']");
        if (inputOculto) inputOculto.remove();
    });
});


async function consultar_detalles(id_gasto) {
    if ($.fn.DataTable.isDataTable("#tabla_detalles_gastos")) {
        $('#tabla_detalles_gastos').DataTable().clear().destroy();
    }

    //Creamos el formData
    datos_consulta = new FormData();

    //Aqui decimos que vamos a hacer
    datos_consulta.append('operacion', 'consultar_detalles');
    datos_consulta.append('id_gasto', id_gasto);

    //Llamamos a la funcion para hacer la consulta
    data = await query(datos_consulta)
    vaciar_tabla_detalles(); //Vaciamos la tabla de lo que tenia antes

    // Resvisamos el resultado
    if (!(data.estatus == undefined)) {
        mensajes('error', 4000, 'Atencion', data.mensaje);
        return;// en caso de error mandamos un mensaje con el error y nos vamos
    }

    //recorremos los datos y en cada vuelta llamamos una funcion para llenar la tabla
    await data.map(fila => {
        llenarTabla_detalles(fila);
    })

    data_table_detalles = init_data_table_detalles(); //iniciamos el dataTable de jquery
}
function vaciar_tabla_detalles() {
    let cuerpo_tabla = document.querySelector(`#tabla_detalles_gastos tbody`);
    cuerpo_tabla.textContent = null;
}

function llenarTabla_detalles(fila) {
    console.log("Se llena tabla secundaria:", fila);
    // seleccionamos el cuerpo de la tabla que vamos a llenar
    let cuerpo_tabla = document.querySelector(`#tabla_detalles_gastos tbody`);

    // Creamos etiquetas
    let fila_tabla = document.createElement("tr");//creamos la fila <tr></tr>

    let id_campo = fila["id_detalle_gasto"]; // guardamos el id que nos interese

    // creamos un td por cada columna que vamos a llenar de la tabla <td></td>
    let fecha_td = document.createElement("td"),
        monto_td = document.createElement("td"),
        metodo_pago_td = document.createElement("td"),
        descripcion_detalle_td = document.createElement("td");

    // le damos el contenido de la consulta
    let datos = fila;

    fecha_td.textContent = formatearFecha(datos.fecha);
    monto_td.textContent = formatearMontoConMoneda(datos.monto, datos.metodo_pago);
    metodo_pago_td.textContent = datos.metodo_pago;
    descripcion_detalle_td.textContent = datos.descripcion_detalle_gasto;

    let acciones = crearBotones_detalles(id_campo);
    // creamos los botones de eliminar y modificar

    // le ponemos los td a la fila (tr)
    fila_tabla.appendChild(fecha_td);
    fila_tabla.appendChild(monto_td);
    fila_tabla.appendChild(metodo_pago_td);
    fila_tabla.appendChild(descripcion_detalle_td);
    fila_tabla.appendChild(acciones);

    fila_tabla.setAttribute("id", `fila-${id_campo}`);
    // le ponemos un id a las fila para cuando las eliminemos

    // y por ultimo, llenamos la tabla con la fila
    cuerpo_tabla.appendChild(fila_tabla);
}
function crearBotones_detalles(id) {
    // Creamos los botones de las acciones
    let td = document.createElement("td");
    let acciones = document.createElement("div");
    acciones.setAttribute("class", "row justify-content-evenly");
    // le damos la clases de boostrap para que se vea tu sabe'

    // BOTON DE VISTA PREVIA CON EL OJITO
    let boton_vista_previa = document.createElement("button");
    let icono_ver = document.createElement("i");
    icono_ver.setAttribute("class", "bi bi-eye-fill");
    boton_vista_previa.appendChild(icono_ver);
    boton_vista_previa.setAttribute("type", "button");
    boton_vista_previa.setAttribute("class", "btn btn-primary btn-sm col-3");
    boton_vista_previa.setAttribute("title", "Informacion");
    boton_vista_previa.setAttribute("value", id);
    boton_vista_previa.addEventListener("click", mostrarVistaPrevia_detalles);
    acciones.appendChild(boton_vista_previa);

    td.appendChild(acciones);

    return td;
}

async function mostrarVistaPrevia_detalles(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_detalle_gasto", id);
    datos_consulta.append("operacion", "consulta_especifica_detalles");

    const respuesta = await query(datos_consulta);
    const data = respuesta;

    document.getElementById("vista_fecha_detalles").textContent = formatearFecha(data.fecha);
    document.getElementById("vista_monto_detalles").textContent = formatearMontoConMoneda(data.monto, data.metodo_pago);

    //document.getElementById("vista_prioridad").innerHTML = obtenerPrioridadTexto(data.prioridad);
    document.getElementById("vista_metodo_pago_detalles").textContent = data.metodo_pago;

    document.getElementById("vista_nombre_banco_detalles").innerHTML = data.nombre_banco && data.nombre_banco.trim() !== ""
        ? data.nombre_banco
        : "No hay banco registrado";

    document.getElementById("vista_referencia_detalles").innerHTML = data.referencia && data.referencia.trim() !== ""
        ? data.referencia
        : "No hay referencia registrada";

    document.getElementById("vista_descripcion_detalles").textContent = data.descripcion_detalle_gasto;

    // Resetear mensaje de error por si estaba visible
    document.getElementById("vista_imagen_detalles").style.display = "block";
    document.getElementById("mensaje_error_imagen_detalles").classList.add("d-none");

    const imagen = (data.imagen && data.imagen !== "")
        ? `recursos/img/gastos/${data.imagen}`
        : "";

    document.getElementById("vista_imagen_detalles").setAttribute("src", imagen);

    // Mostrar el modal como los otros
    modalVistaPrevia_detalles.show();
}


function init_data_table_detalles() {
    return new DataTable("#tabla_detalles_gastos", {
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

const resizeObserver = new ResizeObserver(entries => {
    if (data_table_detalles) {
        data_table_detalles.draw();
    }
});

resizeObserver.observe(tabla_detalles);