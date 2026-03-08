/**
 * bancos_ajax.js
 * Gestión de Bancos - Peticiones AJAX
 * Dependencias: utilidades.js
 */

let id_modificar, numero_cuenta_an;
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;

let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.getElementById("modal_banco"), { focus: false });
let formulario_usar = document.querySelector("#form_banco");
let tabla_bancos;

// Inicializar la tabla al cargar
consultar();

// Resetear modal al cerrarlo
document.querySelector("#modal_banco").addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Banco";
    
    // Limpiar clases de validación
    formulario_usar.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    formulario_usar.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    formulario_usar.querySelectorAll('.w-100').forEach(el => el.textContent = "");
    
    // Deshabilitar RIF hasta que se seleccione tipo de documento
    formulario_usar.querySelector("#rif").setAttribute("disabled", true);
    
    numero_cuenta_an = null;
});


function envio(operacion) {	
    if (operacion === "modificar") {
        modificar(boton_formulario.getAttribute("id_modificar"));
    } else if (operacion === "Registrar") {
        registrar();
    } else {
        Alertas.mostrar('error', 'Atención', 'Ha ocurrido un error durante la operación, inténtelo nuevamente');
    }
}

/**
 * Crea el HTML de los botones de acción (modificar/eliminar) para cada fila
 */
function crearBotones(id) {
    let div = document.createElement("div");
    let html = `<div class="row justify-content-evenly">
                    <button type="button" class="btn btn-success btn-sm col-lg-3 col-4 modificar" data-bs-toggle="modal" data-bs-target="#modal_banco" title="modificar" value="${id}">
                        <i class="bi bi-pencil-square"></i>
                    </button>`;
    if (permiso_eliminar == 1) {
        html += `<button type="button" class="btn btn-danger btn-sm col-lg-3 col-4 eliminar" title="Eliminar" value="${id}">
                    <i class="bi bi-trash"></i>
                </button>`;
    }
    html += `</div>`;
    div.innerHTML = html;
    return div;
}

async function consultar() {
    // 1. Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_banco;
        let html = `<div class="d-flex justify-content-center gap-2">`;
        if (window.permiso_modificar) {
            html += `<button class="btn btn-success btn-sm modificar" value="${id}" title="Modificar"><i class="bi bi-pencil"></i></button>`;
        }
        if (window.permiso_eliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" value="${id}" title="Eliminar"><i class="bi bi-trash"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    // 3. Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "Banco", field: "nombre_banco", minWidth: 100, responsive: 0 },
        { title: "Código", field: "codigo", minWidth: 80 },
        { title: "Nro. Cuenta", field: "numero_cuenta", minWidth: 150 },
        { title: "Teléfono", field: "telefono_afiliado", minWidth: 100 },
        { title: "RIF", field: "rif", minWidth: 100 },
        {
            title: "Acciones",
            formatter: formatoBotones,
            headerSort: false,
            hozAlign: "center",
            vertAlign: "middle",
            minWidth: 100,
            responsive: 0,
            download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;

                // Emulamos el evento para que tus funciones prepararFormulario funcionen sin cambios
                const mockEvent = { currentTarget: btn }; 

                if (btn.classList.contains('modificar')) {
                    prepararFormulario(mockEvent);
                } else if (btn.classList.contains('eliminar')) {
                    const id = btn.value;
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e01d22',
                        confirmButtonText: 'Eliminar'
                    }).then(result => result.isConfirmed && eliminar(id));
                }
            }
        }
    ];
    
    tabla_bancos = Tablas.cargarTabulador(contenedor.id, "", columnas);

    // 5. Buscador Global Dinámico
    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));

            tabla_bancos.setFilter([filtros]);
        });
    }
}

/**
 * Prepara el formulario con los datos del banco a modificar
 */
async function prepararFormulario(e) {
    let datos = new FormData();
    const id = e.currentTarget.value;
    
    datos.append("id_banco", id);
    datos.append('operacion', 'consulta_especifica');

    let respuesta = await Peticiones.enviar(datos);	
    
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    let data = respuesta.datos; // { id_banco, nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif }

    // Llenar formulario
    formulario_usar.querySelector("#nombre_banco").value = data.nombre_banco;
    formulario_usar.querySelector("#codigo").value = data.codigo;
    formulario_usar.querySelector("#numero_cuenta").value = data.numero_cuenta;
    formulario_usar.querySelector("#telefono_afiliado").value = data.telefono_afiliado;
    // Separar tipo de documento y número
    let tipoDoc = data.rif.charAt(0);
    let numeroRif = data.rif.slice(1);
    formulario_usar.querySelector("#tipo_documento").value = tipoDoc;
    formulario_usar.querySelector("#rif").value = numeroRif;
    formulario_usar.querySelector("#rif").removeAttribute("disabled");

    if (permiso_modificar != 1) {
        boton_formulario.setAttribute("hide", true);
        boton_formulario.setAttribute("disabled", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_banco);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById('titulo_modal').textContent = "Modificar Banco";

    id_modificar = id;
    numero_cuenta_an = data.numero_cuenta;

    modal.show();
}

/**
 * Registra un nuevo banco
 */
async function registrar() {
    let datos = new FormData(formulario_usar);
    // Construir RIF completo
    let tipo = datos.get('tipo_documento');
    let rifNum = datos.get('rif');
    datos.set('rif', tipo + rifNum);
    datos.append('operacion', 'registrar');
    
    let respuesta = await Peticiones.enviar(datos);

    modal.hide();
    formulario_usar.reset();

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_bancos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

/**
 * Actualiza un banco existente
 */
async function modificar(id) {	
    let datos = new FormData(formulario_usar);
    let tipo = datos.get('tipo_documento');
    let rifNum = datos.get('rif');
    datos.set('rif', tipo + rifNum);
    datos.append("id_banco", id);
    datos.append('operacion', 'modificar');

    let respuesta = await Peticiones.enviar(datos);

    formulario_usar.reset();
    modal.hide();

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");	
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Banco";

    tabla_bancos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro se ha modificado exitosamente');
}

/**
 * Manejador del clic en botón eliminar (con confirmación)
 */
function eventoEliminar(e) {
    let id = e.target.value || e.target.parentElement.value;

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea eliminar este banco?",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        icon: "warning"
    }).then((resultado) => {
        if (resultado.isConfirmed) eliminar(id);				
    });
}

/**
 * Elimina un banco
 */
async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_banco", id);
    datos.append('operacion', 'eliminar');

    let respuesta = await Peticiones.enviar(datos);
    
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_bancos.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro ha sido eliminado correctamente');
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - BANCOS
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Función de alineación precisa con micro-retraso
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // Configuración Base
    const configBase = {
        showProgress: true,
        animate: true,
        smoothScroll: false, 
        allowKeyboardControl: false,
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        onHighlightStarted: (element) => {
            if (element) {
                element.scrollIntoView({ behavior: 'instant', block: 'center' });
                alinearBurbuja();
            }
        }
    };

    // 1. TOUR VISTA PRINCIPAL
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Cuentas Bancarias', description: 'Aquí gestionas los bancos receptores donde el condominio recibe los pagos de los propietarios.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_banco"]', popover: { title: 'Registrar Banco', description: 'Agrega una nueva cuenta bancaria (nacional o internacional) o billetera digital al sistema.', side: "bottom", align: 'start' } },
        { element: '#tabla_banco_wrapper', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas. Estos datos aparecerán en los reportes y opciones de pago para los usuarios.', side: "top", align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre_banco', popover: { title: 'Entidad Bancaria', description: 'Nombre del banco o plataforma (ej: Banco de Venezuela, Banesco, Binance).', side: 'bottom', align: 'start' } },
        { element: '#codigo', popover: { title: 'Código Bancario', description: 'Los primeros 4 dígitos que identifican al banco (ej: 0102).', side: 'bottom', align: 'start' } },
        { element: '#numero_cuenta', popover: { title: 'Número de Cuenta', description: 'El número completo de la cuenta o la dirección de la billetera/correo (si es Zelle/Paypal).', side: 'top', align: 'start' } },
        { element: '#telefono_afiliado', popover: { title: 'Teléfono Afiliado', description: 'Número de teléfono asociado a la cuenta para validaciones de Pago Móvil.', side: 'top', align: 'start' } },
        { element: '#rif', popover: { title: 'Titular', description: 'Cédula o RIF del titular de la cuenta bancaria.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la cuenta para empezar a recibir operaciones.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_banco');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalHTML && modalHTML.classList.contains('show')) {
                // Si el modal está abierto
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                // Si estamos en la vista principal
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    // Limpieza de seguridad
    if (modalHTML) {
        modalHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});