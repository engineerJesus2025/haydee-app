//consultar();
let data_table, id_eliminado, id_registrado, id_modificar;

let permiso_eliminar = document.querySelector("#permiso_eliminar").value;
let permiso_editar = document.querySelector("#permiso_editar").value;

let tabla = document.querySelector("#tabla_gastos");
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.querySelector("#modal_gastos"));
let modalVistaPrevia = new bootstrap.Modal(document.querySelector("#modal_vista_previa"));
let formulario_usar = document.querySelector("#form_gastos");

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
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Gasto";
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

    let fecha = document.querySelector("#fecha").value,
        monto = document.querySelector("#monto").value,
        tipo_gasto = document.querySelector("#tipo_gasto").value,
        metodo_pago = document.querySelector("#metodo_pago").value,
        referencia = document.querySelector("#referencia").value,
        descripcion_gasto = document.querySelector("#descripcion").value,
        banco = document.querySelector("#banco").value,
        proveedor = document.querySelector("#proveedor").value,
        imagen = document.querySelector("#imagen").files[0];

    datos_consulta.append("fecha", fecha);
    datos_consulta.append("monto", monto);
    datos_consulta.append("tipo_gasto", tipo_gasto);
    datos_consulta.append("metodo_pago", metodo_pago);
    datos_consulta.append("referencia", referencia);
    datos_consulta.append("descripcion_gasto", descripcion_gasto);
    datos_consulta.append("banco", banco);
    datos_consulta.append("proveedor", proveedor);
    datos_consulta.append("imagen", imagen);
    datos_consulta.append("operacion", "registrar");

    let respuesta = await query(datos_consulta);

    if (respuesta && respuesta.estatus) {
        modal.hide();
        formulario_usar.reset();

        id_registrado = await last_id();

        // Crear fila nueva con los datos
        let fila_nueva = {
            id_gasto: id_registrado.mensaje,
            fecha: fecha,
            monto: monto,
            tipo_gasto: tipo_gasto,
            metodo_pago: metodo_pago,
            referencia: referencia,
            descripcion_gasto: descripcion_gasto,
            banco: banco,
            proveedor: proveedor,
            imagen: imagen
        };

        // Insertar la nueva fila visualmente
await cargarGastosPorMes(document.querySelector("#selector_mes_anio").value);
await cargarTotalesMetodoPago(document.querySelector("#selector_mes_anio").value);
consulta_completada();
        consulta_completada();
    } else {
        Swal.fire({
            title: "Error",
            text: "No se ha podido registrar el gasto",
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
    const tipo_gasto = document.createElement("td");
    const metodo_pago = document.createElement("td");
    const banco = document.createElement("td");
    const referencia = document.createElement("td");
    const imagen = document.createElement("td");
    const descripcion_gasto = document.createElement("td");

    fecha.textContent = formatearFecha(fila["fecha"]);

    const metodo = fila["metodo_pago"];
    const esBs = ["pago_movil", "transferencia"].includes(metodo);
    monto.textContent = `${formatearMonto(fila["monto"])} ${esBs ? "Bs" : "$"}`;

    tipo_gasto.textContent = mayuscula(fila["tipo_gasto"]);
    metodo_pago.textContent = formatearMetodo(metodo);
    banco.textContent = fila["banco"] || "—";
    referencia.textContent = fila["referencia"] || "—";
    imagen.textContent = fila["imagen"] ? "🖼️" : "—";
    descripcion_gasto.textContent = fila["descripcion_gasto"];

    const acciones = crearBotones(id_campo);

    fila_tabla.appendChild(fecha);
    fila_tabla.appendChild(monto);
    fila_tabla.appendChild(tipo_gasto);
    fila_tabla.appendChild(metodo_pago);
    fila_tabla.appendChild(banco);
    fila_tabla.appendChild(referencia);
    fila_tabla.appendChild(imagen);
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
    const data = respuesta;

    let fecha = formulario_usar.querySelector("#fecha");
    let monto = formulario_usar.querySelector("#monto");
    let tipo_gasto = formulario_usar.querySelector("#tipo_gasto");
    let metodo_pago = formulario_usar.querySelector("#metodo_pago");
    let referencia = formulario_usar.querySelector("#referencia");
    let descripcion = formulario_usar.querySelector("#descripcion");
    let banco = formulario_usar.querySelector("#banco");
    let proveedor = formulario_usar.querySelector("#proveedor");


    fecha.value = data.fecha;
    monto.value = data.monto;
    tipo_gasto.value = data.tipo_gasto;
    metodo_pago.value = data.metodo_pago;
    actualizarVisibilidadCampos();
    referencia.value = data.referencia;
    descripcion.value = data.descripcion_gasto;
    banco.value = data.id_banco;
    proveedor.value = data.id_proveedor;

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
    boton_formulario.setAttribute("id_modificar", data.id_gasto);
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
    const data = respuesta[0];

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
    let fecha = document.querySelector("#fecha").value,
        monto = document.querySelector("#monto").value,
        tipo_gasto = document.querySelector("#tipo_gasto").value,
        metodo_pago = document.querySelector("#metodo_pago").value,
        referencia = document.querySelector("#referencia").value,
        descripcion = document.querySelector("#descripcion").value,
        banco = document.querySelector("#banco").value,
        proveedor = document.querySelector("#proveedor").value,
        imagen = document.querySelector("#imagen").files[0];

    datos_consulta.append("id_gasto", id);
    datos_consulta.append("fecha", fecha);
    datos_consulta.append("monto", monto);
    datos_consulta.append("tipo_gasto", tipo_gasto);
    datos_consulta.append("metodo_pago", metodo_pago);
    datos_consulta.append("referencia", referencia);
    datos_consulta.append("descripcion_gasto", descripcion);
    datos_consulta.append("banco", banco);
    datos_consulta.append("proveedor", proveedor);
    datos_consulta.append("operacion", "modificar");

    if (imagen !== undefined) {
        datos_consulta.append("imagen", imagen);
    }

    let respuesta = await query(datos_consulta);
    formulario_usar.reset();
    modal.hide();

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Registrar";
    document.getElementById("titulo_modal").textContent = "Registrar Gasto";

    // Actualizar la tabla
    const filaExistente = document.querySelector(`#fila-${id}`);
    if (filaExistente) {
        filaExistente.remove(); // Elimina la fila vieja
    }

    const filaModificada = {
        id_gasto: id,
        fecha: fecha,
        monto: monto,
        tipo_gasto: tipo_gasto,
        metodo_pago: metodo_pago,
        referencia: referencia,
        descripcion_gasto: descripcion,
        banco: banco,
        proveedor: proveedor,
        imagen: respuesta.imagen || ""
    };

    llenarTabla(filaModificada);
    consulta_completada();
    reasignarEventos();

    // 🔄 Recargar totales
    const idMesActual = document.querySelector("#selector_mes_anio").value;
    await cargarTotalesMetodoPago(idMesActual);
}


// BOTON PARA ELIMINAR LA IMAGEN EN EL FORMULARIO DE EDITAR
document.querySelector("#boton_eliminar_imagen").addEventListener("click", function () {
    Swal.fire({
        title: "¿Eliminar imagen?",
        text: "La imagen cargada será eliminada de este gasto.",
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

// Cargar los meses y años al cargar la página


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


// ----------- FUNCIONES Y COSAS NUEVAS PARA EL MODULO DE GASTOS-------------------
document.addEventListener("DOMContentLoaded", async () => {
  await cargarSelectorMesAnio();

  const selector = document.querySelector("#selector_mes_anio");

  // Obtener mes y año actuales
  const fecha = new Date();
  const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // meses de 0-11
  const anio = fecha.getFullYear();
  const valorActual = `${mes}-${anio}`;

  // Buscar si ese valor existe en las opciones
  const opcionActual = Array.from(selector.options).find(opt => opt.text === valorActual);
  if (opcionActual) {
    selector.value = opcionActual.value;

    // Cargar datos del mes actual automáticamente
    await cargarGastosPorMes(opcionActual.value);
    await cargarTotalesMetodoPago(opcionActual.value);
  }

  // Evento cuando cambia el select
  selector.addEventListener("change", cargarDatosPorMes);
});

// Llenar el select de meses y años
async function cargarSelectorMesAnio() {
  const datos = new FormData();
  datos.append("operacion", "listar_gastos_mes");

  const respuesta = await query(datos);

  if (respuesta && Array.isArray(respuesta)) {
    const selector = document.querySelector("#selector_mes_anio");
    selector.innerHTML = '<option value="">Seleccione un mes/año</option>';

    // Calcular mes y año actual
    const fechaActual = new Date();
    const mesActual = String(fechaActual.getMonth() + 1).padStart(2, "0");
    const anioActual = fechaActual.getFullYear();

    let idSeleccionado = null;

    respuesta.forEach(item => {
      if (item.mes && item.anio) {
        const option = document.createElement("option");
        const nombreMes = obtenerNombreMes(item.mes);
        option.value = item.id_gasto_mes;
        option.text = `${nombreMes}-${item.anio}`;
        selector.appendChild(option);

        // Comparar con el mes/año actual
        if (parseInt(item.mes) == parseInt(mesActual) && parseInt(item.anio) == anioActual) {
          idSeleccionado = item.id_gasto_mes;
        }
      }
    });

    // Si se encontró el mes actual en la lista, seleccionarlo y cargar sus datos
    if (idSeleccionado) {
      selector.value = idSeleccionado;
      await cargarGastosPorMes(idSeleccionado);
      await cargarTotalesMetodoPago(idSeleccionado);
    }
  }
}


// Al cambiar el select
async function cargarDatosPorMes(e) {
  const id_mes = e.target.value;
  if (!id_mes) return;

  await cargarGastosPorMes(id_mes);
  await cargarTotalesMetodoPago(id_mes);
}

// Mostrar gastos del mes seleccionado
async function cargarGastosPorMes(id_mes) {
  const datos = new FormData();
  datos.append("operacion", "filtrar_gastos_mes");
  datos.append("gasto_mes_id", id_mes);

  const respuesta = await query(datos);

  vaciar_tabla(); // función existente
  if (respuesta && Array.isArray(respuesta)) {
    if (data_table) data_table.destroy(); 

vaciar_tabla(); 

respuesta.forEach(gasto => llenarTabla(gasto));

data_table = init_data_table();
  }
}

function actualizarVisibilidadCampos() {
    const metodoPago = document.querySelector("#metodo_pago");
    const grupoReferencia = document.querySelector("#grupo_referencia");
    const grupoBanco = document.querySelector("#grupo_banco");
    const grupoImagen = document.querySelector("#grupo_imagen");

    const valor = metodoPago.value;

    if (valor === "efectivo" || valor === "Efectivo ($)") {
        grupoReferencia.style.display = "none";
        grupoBanco.style.display = "none";
        grupoImagen.style.display = "none";
    } else if (valor === "pago_movil" || valor === "transferencia" || valor === "Pago Móvil" || valor === "Transferencia") {
        grupoReferencia.style.display = "";
        grupoBanco.style.display = "";
        grupoImagen.style.display = "";
    } else {
        grupoReferencia.style.display = "none";
        grupoBanco.style.display = "none";
        grupoImagen.style.display = "none";
    }
}

// Ejecutar en eventos globales
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("#metodo_pago").addEventListener("change", actualizarVisibilidadCampos);
    actualizarVisibilidadCampos();
});

// Mostrar los totales por método de pago
async function cargarTotalesMetodoPago(id_mes) {
    const datos = new FormData();
    datos.append("operacion", "totales_metodo_pago");
    datos.append("gasto_mes_id", id_mes);

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

document.addEventListener("DOMContentLoaded", function () {
    const metodoPago = document.querySelector("#metodo_pago");
    const unidadMonto = document.querySelector("#unidad_monto");

    function actualizarUnidadMoneda() {
        const valor = metodoPago.value;

        if (valor === "pago_movil" || valor === "transferencia") {
            unidadMonto.textContent = "Bs";
            unidadMonto.style.display = "";
        } else if (valor === "efectivo") {
            unidadMonto.textContent = "$";
            unidadMonto.style.display = "";
        } else {
            unidadMonto.style.display = "none";
        }
    }

    metodoPago.addEventListener("change", actualizarUnidadMoneda);
    actualizarUnidadMoneda(); // Ejecutar por si hay valor cargado
});

function mayuscula(texto) {
    if (!texto) return "";
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
}