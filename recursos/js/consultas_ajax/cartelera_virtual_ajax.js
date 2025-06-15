consultar();
let data_table, id_eliminado, id_registrado, id_modificar;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_cartelera_virtual");
let nombre_usuario = document.querySelector("#nombre_usuario")?.value || "Desconocido";
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.querySelector("#modal_cartelera"));
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa"));
let formulario_usar = document.querySelector("#form_cartelera");

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

document.querySelector('#modal_cartelera').addEventListener('hidden.bs.modal', () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Publicación";
    formulario_usar.querySelectorAll("[class='w-100").forEach(el => el.textContent = "");
    document.querySelector("#nombre_imagen_cargada").textContent = "";
    document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
    document.querySelector("#boton_eliminar_imagen").removeAttribute("data-nombre");

    const inputOculto = formulario_usar.querySelector("input[name='eliminar_imagen']");
    if (inputOculto) inputOculto.remove();
});

// Si queremos registrar:

async function registrar() {
    let datos_consulta = new FormData();

    let titulo = document.querySelector("#titulo").value,
        descripcion = document.querySelector("#descripcion").value,
        fecha = document.querySelector("#fecha").value,
        imagen = document.querySelector("#imagen").files[0],
        prioridad = document.querySelector("#prioridad").value;

    datos_consulta.append("titulo", titulo);
    datos_consulta.append("descripcion", descripcion);
    datos_consulta.append("fecha", fecha);
    datos_consulta.append("imagen", imagen);
    datos_consulta.append("prioridad", prioridad);
    datos_consulta.append("operacion", "registrar");

    let respuesta = await query(datos_consulta);

    if (respuesta && respuesta.estatus) {
        modal.hide();
        formulario_usar.reset();

        id_registrado = await last_id();

        // Obtener el nombre del autor desde el input del modal
        nombre_usuario = document.querySelector("#nombre_usuario")?.value || "Desconocido";

        // Crear fila nueva con los datos
        let fila_nueva = {
            id_cartelera: id_registrado.mensaje,
            fecha: fecha,
            titulo: titulo,
            prioridad: prioridad,
            nombre_usuario: nombre_usuario
        };

        // Insertar la nueva fila visualmente
        llenarTabla(fila_nueva);
        data_table.row.add(document.querySelector(`#fila-${id_registrado.mensaje}`)).draw(false);
        reasignarEventos();
        consulta_completada();
    } else {
        Swal.fire({
            title: "Error",
            text: "No se ha podido registrar la publicación",
            icon: "error",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#e01d22",
        });
    }
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
    let cuerpo_tabla = document.querySelector('#tabla_cartelera_virtual tbody');
    cuerpo_tabla.textContent = null;
}

// PARA EL COLOR DE LA PRIORIDAD, COMO SOLO SON 3 OPCIONES HICE MEJOR UN SWITCH CASE
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


function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    if (partes.length === 3) {
        return `${partes[2]}-${partes[1]}-${partes[0]}`; // DD-MM-AAAA
    }
    return fechaStr; // En caso de error, retorna original
}

function llenarTabla(fila) {
    let cuerpo_tabla = document.querySelector('#tabla_cartelera_virtual tbody');
    let fila_tabla = document.createElement("tr");
    let id_campo = fila["id_cartelera"];

    let fecha = document.createElement("td"),
        titulo = document.createElement("td"),
        autor = document.createElement("td");

    prioridad = document.createElement("td");

    fecha.textContent = formatearFecha(fila["fecha"]);
    titulo.textContent = fila["titulo"];
    autor.textContent = fila["nombre_usuario"];

    prioridad.innerHTML = obtenerPrioridadTexto(fila["prioridad"]);

    let acciones = crearBotones(id_campo);

    fila_tabla.appendChild(fecha);
    fila_tabla.appendChild(titulo);
    fila_tabla.appendChild(autor);
    fila_tabla.appendChild(prioridad);
    fila_tabla.appendChild(acciones);

    fila_tabla.setAttribute("id", `fila-${id_campo}`);
    fila_tabla.setAttribute("id_cartelera", id_campo);
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
    boton_editar.setAttribute("data-bs-target", "#modal_cartelera");
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
    datos_consulta.append("id_cartelera", id);
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
    datos_consulta.append("id_cartelera", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta[0];

    let titulo = formulario_usar.querySelector("#titulo");
    let descripcion = formulario_usar.querySelector("#descripcion");
    let fecha = formulario_usar.querySelector("#fecha");
    let prioridad = formulario_usar.querySelector("#prioridad");

    titulo.value = data.titulo;
    descripcion.value = data.descripcion;
    fecha.value = data.fecha;
    prioridad.value = data.prioridad;

    // Mostrar nombre de imagen, esos id estan en el formulario
    const nombreImagen = document.querySelector("#nombre_imagen_cargada");
    const botonEliminarImagen = document.querySelector("#boton_eliminar_imagen");

    if (data.imagen && data.imagen !== "") {
        let nombre_archivo = data.imagen.split("/").pop();
        nombreImagen.textContent = `Imagen cargada: ${nombre_archivo}`;
        botonEliminarImagen.classList.remove("d-none");
        botonEliminarImagen.setAttribute("data-nombre", nombre_archivo);
    } else {
        nombreImagen.textContent = "No hay imagen cargada.";
        botonEliminarImagen.classList.add("d-none");
        botonEliminarImagen.removeAttribute("data-nombre");
    }

    if (!permiso_editar) {
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_cartelera);
    boton_formulario.textContent = "Modificar";
    document.getElementById("titulo_modal").textContent = "Modificar Publicación";
    id_modificar = id;
}

//FUNCIONALIDAD DE LA VISTA PREVIA
async function mostrarVistaPrevia(e) {
    const boton = e.target.closest("button");
    const id = boton.getAttribute("value");

    const datos_consulta = new FormData();
    datos_consulta.append("id_cartelera", id);
    datos_consulta.append("operacion", "consulta_especifica");

    const respuesta = await query(datos_consulta);
    const data = respuesta[0];

    document.getElementById("vista_titulo").textContent = data.titulo;
    document.getElementById("vista_descripcion").textContent = data.descripcion;
    document.getElementById("vista_fecha").textContent = formatearFecha(data.fecha);
    document.getElementById("vista_prioridad").innerHTML = obtenerPrioridadTexto(data.prioridad);
    document.getElementById("vista_autor").textContent = data.nombre_usuario;

    // Resetear mensaje de error por si estaba visible
    document.getElementById("vista_imagen").style.display = "block";
    document.getElementById("mensaje_error_imagen").classList.add("d-none");

    const imagen = (data.imagen && data.imagen !== "")
        ? `recursos/img/${data.imagen}`
        : "";

    document.getElementById("vista_imagen").setAttribute("src", imagen);

    // Mostrar el modal como los otros
    modalVistaPrevia.show();
}


async function modificar(id) {
    let datos_consulta = new FormData();
    let titulo = document.querySelector("#titulo").value,
        descripcion = document.querySelector("#descripcion").value,
        fecha = document.querySelector("#fecha").value,
        imagen = document.querySelector("#imagen").files[0],
        prioridad = document.querySelector("#prioridad").value;

    datos_consulta.append("id_cartelera", id);
    datos_consulta.append("titulo", titulo);
    datos_consulta.append("descripcion", descripcion);
    datos_consulta.append("fecha", fecha);
    datos_consulta.append("prioridad", prioridad);
    datos_consulta.append("operacion", "modificar");

    // Solo si hay una nueva imagen seleccionada
    if (imagen !== undefined) {
        datos_consulta.append("imagen", imagen);
    }

    let respuesta = await query(datos_consulta);
    formulario_usar.reset();
    modal.hide();

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Publicación";
    consulta_completada();

    // Obtener ruta de imagen actualizada o previa
    const rutaImagen = respuesta.imagen_url || "recursos/img/default.jpg";
    const prioridadHTML = obtenerPrioridadTexto(prioridad);

    const acciones = crearBotones(id);

    data_table.row(`#fila-${id}`).data([
        formatearFecha(fecha),
        titulo,
        nombre_usuario,
        prioridadHTML,
        acciones.outerHTML
    ]).draw();

    const fila = document.querySelector(`#fila-${id}`);
    if (fila) {
        fila.querySelector(`[value="${id}"]`).addEventListener("click", modificar_formulario);
    }
    reasignarEventos();
}


// BOTON PARA ELIMINAR LA IMAGEN EN EL FORMULARIO DE EDITAR
document.querySelector("#boton_eliminar_imagen").addEventListener("click", function () {
    Swal.fire({
        title: "¿Eliminar imagen?",
        text: "La imagen cargada será eliminada de esta publicación.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar"
    }).then((result) => {
        if (result.isConfirmed) {
            const hiddenEliminar = document.createElement("input");
            hiddenEliminar.type = "hidden";
            hiddenEliminar.name = "eliminar_imagen";
            hiddenEliminar.value = "1";
            formulario_usar.appendChild(hiddenEliminar);

            // llAMA A LOS INPUTS QUE ESTAN EN EL MODAL
            document.querySelector("#nombre_imagen_cargada").textContent = "Imagen eliminada.";
            document.querySelector("#boton_eliminar_imagen").classList.add("d-none");
        }
    });
});

async function last_id() {
    datos_consulta = new FormData();
    datos_consulta.append("operacion", "ultimo_id");
    let res = await query(datos_consulta);
    return res;
}

async function query(datos) {
    let data = await fetch("", { method: "POST", body: datos }).then(res => {
        let result = res.json();
        return result;//Convertimos el resultado de json a js y lo mandamos
    })
    // console.log(data);
    return data;
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
    return new DataTable("#tabla_cartelera_virtual", {
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

observer.observe(document.querySelector("#tabla_cartelera_virtual tbody"), {
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
        //Esto es porque si la tabla esta paginada, como que no encuentra cual borrar hasta que esta en la pagina que la contiene
    }

    // Se asigna el evento eliminar para los botones, esta aqui porque pasa algo parecido a lo de arriba
    $(".eliminar").on("click", function (e) {
        id = e.target.value;
        if (id == undefined) {
            id = e.target.parentElement.value;
        }
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Está seguro que desea eliminar esta publicación?",
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
     document.querySelectorAll("button[title='Vista previa']").forEach(btn => {
        btn.removeEventListener("click", mostrarVistaPrevia);
        btn.addEventListener("click", mostrarVistaPrevia);
    });
    if (id_registrado) { // en caso de que se haya registrado y no se haya añadido a la tabla
        let boton_modificar = tabla.querySelector(`[value='${id_registrado.mensaje}']`);
        // captura el boton de editar, sino lo encuentra es que no esta en su pagina, y no tiene caso ponerle evento
        if (boton_modificar) {
            // si lo encuentra le pone el evento de modificar
            boton_modificar.addEventListener("click", modificar_formulario);
            boton_modificar.parentElement.parentElement.parentElement.setAttribute("id", `fila-${id_registrado.mensaje}`);
            id_registrado = null;
        }
    }
}