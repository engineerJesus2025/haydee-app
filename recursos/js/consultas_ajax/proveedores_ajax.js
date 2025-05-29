consultar();
let data_table, id_eliminado, id_registrado, id_modificar;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_proveedores");
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.querySelector("#modal_proveedores"));
let formulario_usar = document.querySelector("#form_proveedores");


// En caso de que se envie un formulario
function envio(operacion) {
    if (operacion == "Editar") {
        id_modificar = boton_formulario.getAttribute("id_modificar");
        modificar(id_modificar);

    }
    else if (operacion == "Registrar") {
        registrar();
    }
}

document.querySelector('#modal_proveedores').addEventListener('hidden.bs.modal', () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Proveedor";
    formulario_usar.querySelectorAll("[class='w-100").forEach(el => el.textContent = "");
});

// Si queremos registrar:

async function registrar() {
    datos_consulta = new FormData();

    let nombre = formulario_usar.querySelector("#nombre_proveedor").value,
        servicio = formulario_usar.querySelector("#servicio").value,
        rif = formulario_usar.querySelector("#rif").value,
        direccion = formulario_usar.querySelector("#direccion").value;

    datos_consulta.append("nombre_proveedor", nombre);
    datos_consulta.append("servicio", servicio);
    datos_consulta.append("rif", rif);
    datos_consulta.append("direccion", direccion);
    datos_consulta.append("operacion", "registrar");

    let respuesta = await query(datos_consulta);
    console.log("Respuesta al registrar:", respuesta);


    if (respuesta && respuesta.estatus) {
        modal.hide();
        formulario_usar.reset();

        id_registrado = await last_id();

        console.log("ID registrado:", id_registrado);
        let acciones = crearBotones(id_registrado.mensaje);

        let res_data_table = await data_table.row.add([
            nombre,
            servicio,
            rif,
            direccion,
            acciones.outerHTML
        ]).draw(false); // Evita el reinicio de la paginación
        reasignarEventos();

        consulta_completada();
    } else {
        Swal.fire({
            title: "Error",
            text: "No se pudo registrar el proveedor. RIF o Cedula ya existe",
            icon: "error",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#e01d22",
        });
    }
}

// Si queremos consultar
async function consultar() {
    datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta");

    data = await query(datos_consulta);
    vaciar_tabla();

    await data.map(fila => {
        llenarTabla(fila);
    })
    data_table = init_data_table();
}

function vaciar_tabla() {
    let cuerpo_tabla = document.querySelector('#tabla_proveedores tbody');
    cuerpo_tabla.textContent = null;
}

function llenarTabla(fila) {
    let cuerpo_tabla = document.querySelector('#tabla_proveedores tbody');

    let fila_tabla = document.createElement("tr");

    let id_campo = fila["id_proveedor"];

    let nombre_td = document.createElement("td");
    let servicio_td = document.createElement("td");
    let rif_td = document.createElement("td");
    let direccion_td = document.createElement("td");

    nombre_td.textContent = fila["nombre_proveedor"];
    servicio_td.textContent = fila["servicio"];
    rif_td.textContent = fila["rif"];
    direccion_td.textContent = fila["direccion"];

    let acciones = crearBotones(id_campo);

    fila_tabla.appendChild(nombre_td);
    fila_tabla.appendChild(servicio_td);
    fila_tabla.appendChild(rif_td);
    fila_tabla.appendChild(direccion_td);
    fila_tabla.appendChild(acciones);

    fila_tabla.setAttribute("id", `fila-${id_campo}`);

    cuerpo_tabla.appendChild(fila_tabla);
}

function crearBotones(id) {
    let td = document.createElement("td");
    let acciones = document.createElement("div");
    acciones.setAttribute("class", "row justify-content-evenly");

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
    boton_editar.setAttribute("data-bs-target", "#modal_proveedores");

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
    datos_consulta.append("id_proveedor", id);
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
    datos_consulta.append("id_proveedor", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta[0];

    let nombre = formulario_usar.querySelector("#nombre_proveedor"),
        servicio = formulario_usar.querySelector("#servicio"),
        rif = formulario_usar.querySelector("#rif"),
        direccion = formulario_usar.querySelector("#direccion");

    nombre.value = data.nombre_proveedor;
    servicio.value = data.servicio;
    rif.value = data.rif;
    direccion.value = data.direccion;

    if (!permiso_editar) {
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_proveedor);
    boton_formulario.textContent = "Modificar";
    document.getElementById("titulo_modal").textContent = "Modificar Propietario";
    id_modificar = id;
}

async function modificar(id) {
    let datos_consulta = new FormData();
    let nombre = formulario_usar.querySelector("#nombre_proveedor").value,
        servicio = formulario_usar.querySelector("#servicio").value,
        rif = formulario_usar.querySelector("#rif").value,
        direccion = formulario_usar.querySelector("#direccion").value;

    datos_consulta.append("id_proveedor", id);
    datos_consulta.append("nombre_proveedor", nombre);
    datos_consulta.append("servicio", servicio);
    datos_consulta.append("rif", rif);
    datos_consulta.append("direccion", direccion);
    datos_consulta.append("operacion", "modificar");

    await query(datos_consulta);
    formulario_usar.reset();
    modal.hide();

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Proveedor";
    consulta_completada();

    let acciones = crearBotones(id);

    data_table.row(`#fila-${id}`).data([`${nombre}`,
        `${servicio}`,
        `${rif}`,
        `${direccion}`,
        `${acciones.outerHTML}`])
    data_table.draw();

    let fila = document.querySelector(`#fila-${id}`);
    if (fila) {
        fila.querySelector(`[value="${id}"]`).addEventListener("click", modificar_formulario);
    }
}

async function last_id() {
    datos_consulta = new FormData();
    datos_consulta.append("operacion", "ultimo_id");
    let res = await query(datos_consulta);
    return res;
}

async function query(datos) {
    let res = await fetch("", {
        method: "POST",
        body: datos
    });

    let result = await res.json();
    return result;
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
    return new DataTable("#tabla_proveedores", {
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

observer.observe(document.querySelector("#tabla_proveedores tbody"), {
    childList: true,
    subtree: true
});


function reasignarEventos() {
    if (id_eliminado) {
        let existe_fila = tabla.querySelector(`#fila-${id_eliminado}`);
        if (existe_fila) {
            data_table.row(`#fila-${id_eliminado}`).remove().draw();
            id_eliminado = null;
        }
    }

    $(".eliminar").on("click", function(e) {
        // Asegúrate de que el valor se obtenga correctamente
        const id = e.target.closest("button").getAttribute("value");
        if (!id) {
            console.error("No se encontró el ID para eliminar.");
            return;
        }

        Swal.fire({
            title: "Atención",
            text: "¿Está seguro de eliminar este proveedores?",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
            confirmButtonColor: "#e01d22",
            cancelButtonText: "Cancelar",
            icon: "warning"
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                console.log("ID recibido para eliminar:", id);
                eliminar(id);
            }
        });
    });

    if (id_registrado && tabla) {
        let boton_modificar = tabla.querySelector(`[value="${id_registrado.mensaje}"]`);
        if (boton_modificar){
            boton_modificar.addEventListener("click", modificar_formulario);
            boton_modificar.closest("tr").setAttribute("id", `fila-${id_registrado.mensaje}`);
            id_registrado = null;
        }
    }

}