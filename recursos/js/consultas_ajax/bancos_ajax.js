/**
 * bancos_ajax.js
 * Gestión de Bancos - Peticiones AJAX
 * Dependencias: utilidades.js
 */

let id_modificar, numero_cuenta_an;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

let boton_formulario = document.querySelector("#boton_formulario");
let formulario_usar = document.querySelector("#form_banco");
let tabla_bancos;

let modal = new bootstrap.Modal(document.getElementById("modal_banco"), { focus: false });
let modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

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

async function consultar() {
    // 1. Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_banco;
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa" value="${id}" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificar) {
            html += `<button class="btn btn-success btn-sm modificar" value="${id}" data-tooltip="true" title="Modificar los detalles de este registro">
                    	<i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminar) {
            html += `<button class="btn btn-danger btn-sm eliminar" value="${id}" data-tooltip="true" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    // 3. Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Banco", field: "nombre_banco", minWidth: 120, responsive: 0 },
        { title: "Código", field: "codigo", minWidth: 120 },
        { title: "Tipo de Cuenta", field: "tipo_cuenta", minWidth: 160 },
        {
            title: "Acciones",
            formatter: formatoBotones,
            headerSort: false,
            hozAlign: "center",
            vertAlign: "middle",
            minWidth: 130,
            widthGrow: 2,
            responsive: 0,
            download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;

                // Emulamos el evento para que tus funciones prepararFormulario funcionen sin cambios
                const mockEvent = { currentTarget: btn }; 

                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }

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
    Tablas.inicializarBuscadorGlobal(tabla_bancos, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // Rellenamos los campos del modal
    document.getElementById("vp_nombre_banco").textContent = data.nombre_banco || 'N/A';
    document.getElementById("vp_tipo_cuenta").textContent = data.tipo_cuenta || 'N/A';
    document.getElementById("vp_nro_cuenta").textContent = data.numero_cuenta || 'N/A';
    document.getElementById("vp_documento").textContent = data.rif || 'N/A';
    document.getElementById("vp_telefono").textContent = data.telefono_afiliado || 'N/A';

    // Mostramos el modal
    modalDetalles.show();
}

/**
 * Prepara el formulario con los datos del banco a modificar
 */
async function prepararFormulario(e) {
    let datos = new FormData();
    const id = e.currentTarget.value;
    
    datos.append("id_banco", id);
    datos.append('operacion', 'consultar_banco');

    let respuesta = await Peticiones.enviar(datos);	

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos; // { id_banco, nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif }

        // Llenar formulario
        formulario_usar.querySelector("#nombre_banco").value = data.nombre_banco;
        formulario_usar.querySelector("#codigo").value = data.codigo;
        formulario_usar.querySelector("#tipo_cuenta").value = data.tipo_cuenta;
        formulario_usar.querySelector("#numero_cuenta").value = data.numero_cuenta;
        formulario_usar.querySelector("#telefono_afiliado").value = data.telefono_afiliado;
        // Separar tipo de documento y número
        let tipoDoc = data.rif.charAt(0);
        let numeroRif = data.rif.slice(1);
        formulario_usar.querySelector("#tipo_documento").value = tipoDoc;
        formulario_usar.querySelector("#rif").value = numeroRif;
        formulario_usar.querySelector("#rif").removeAttribute("disabled");

        if (permisoModificar != 1) {
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
    });
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
    datos.append('operacion', 'registrar_banco');
    
    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_bancos.replaceData();
    });
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
    datos.append('operacion', 'modificar_banco');

    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_bancos.replaceData();
    });
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
    datos.append('operacion', 'eliminar_banco');

    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        tabla_bancos.replaceData();
    });
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
        { element: '#tabla_banco', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas. Estos datos aparecerán en los reportes y opciones de pago para los usuarios.', side: "top", align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre_banco', popover: { title: 'Entidad Bancaria', description: 'Nombre del banco o plataforma (ej: Banco de Venezuela, Banesco, Binance).', side: 'bottom', align: 'start' } },
        { element: '#codigo', popover: { title: 'Código Bancario', description: 'Los primeros 4 dígitos que identifican al banco (ej: 0102).', side: 'bottom', align: 'start' } },
        { element: '#numero_cuenta', popover: { title: 'Número de Cuenta', description: 'El número completo de la cuenta o la dirección de la billetera/correo (si es Zelle/Paypal).', side: 'top', align: 'start' } },
        { element: '#tipo_cuenta', popover: { title: 'Tipo de Cuenta', description: 'El tipo de cuenta utilizado (si es Ahorro o Corriente).', side: 'top', align: 'start' } },
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