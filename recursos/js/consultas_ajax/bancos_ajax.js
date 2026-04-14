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
    // Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBanco = (cell) => {
        let nombre = cell.getValue() || "";
        // Forzamos primera letra mayúscula y el resto minúscula para estandarizar
        nombre = nombre.charAt(0).toUpperCase() + nombre.slice(1).toLowerCase();
        return `<div class="d-flex align-items-center fw-bold text-dark">
                    <i class="bi bi-bank2 text-primary me-2 fs-5"></i> ${nombre}
                </div>`;
    };

    // Formato para el Código Bancario
    const formatoCodigo = (cell) => {
        let codigo = cell.getValue() || "---";
        return `<span class="text-muted fw-semibold">
                    <i class="bi bi-upc-scan me-1 opacity-50"></i> ${codigo}
                </span>`;
    };

    // Formato para Tipo de Cuenta
    const formatoTipo = (cell) => {
        let tipo = cell.getValue() || "";
        let color = "secondary";
        let icono = "bi-wallet2";

        if (tipo.toLowerCase() === "corriente") {
            color = "info"; // Azul claro
            icono = "bi-briefcase-fill";
        } else if (tipo.toLowerCase() === "ahorro") {
            color = "success"; // Verde
            icono = "bi-safe2-fill";
        }

        return `<span class="badge bg-${color} bg-opacity-10 text-${color} border border-${color} px-3 py-2 shadow-sm" style="font-size: .85rem;">
                    <i class="bi ${icono} me-1"></i> ${tipo}
                </span>`;
    };

    // Formato de Botones
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

    // Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Banco", field: "nombre_banco", formatter: formatoBanco, minWidth: 150, responsive: 0 },
        { title: "Código", field: "codigo", formatter: formatoCodigo, minWidth: 120 },
        { title: "Tipo de Cuenta", field: "tipo_cuenta", formatter: formatoTipo, minWidth: 160 },
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

// Función que lee la memoria de Tabulator
function mostrarVistaPrevia(data) {
    // Formatear Nombre del Banco (Capitalizamos la primera letra)
    let nombreBanco = data.nombre_banco || 'N/A';
    if (nombreBanco !== 'N/A') {
        nombreBanco = nombreBanco.charAt(0).toUpperCase() + nombreBanco.slice(1).toLowerCase();
    }
    document.getElementById("vp_nombre_banco").textContent = nombreBanco;

    // Agregar el Código Bancario
    document.getElementById("vp_codigo").textContent = data.codigo || '---';

    // Formatear Tipo de Cuenta
    let tipo = data.tipo_cuenta || 'N/A';
    let color = "secondary";
    let icono = "bi-wallet2";

    if (tipo.toLowerCase() === "corriente") {
        color = "info";
        icono = "bi-briefcase-fill";
    } else if (tipo.toLowerCase() === "ahorro") {
        color = "success";
        icono = "bi-safe2-fill";
    }

    if (tipo !== 'N/A') {
        // Usamos innerHTML para inyectar la etiqueta
        document.getElementById("vp_tipo_cuenta").innerHTML = `
            <span class="badge bg-${color} bg-opacity-10 text-${color} border border-${color} px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                <i class="bi ${icono} me-1"></i> ${tipo}
            </span>`;
    } else {
        document.getElementById("vp_tipo_cuenta").textContent = tipo;
    }

    // Rellenar el resto de campos normalmente
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
// MÓDULO DE AYUDA - BANCOS
// ============================================================
// document.addEventListener('DOMContentLoaded', () => {
//     // Usamos el Helper tal como usamos Tablas.cargarTabulador()
//     AyudaInteractiva.inicializar({
//         idModal: 'modal_banco',
//         pasosPrincipal: [
//             { element: '.page-header', popover: { title: 'Cuentas Bancarias', description: 'Aquí gestionas los bancos receptores donde el condominio recibe los pagos.', side: "bottom", align: 'center' } },
//             { element: 'button[data-bs-target="#modal_banco"]', popover: { title: 'Registrar Banco', description: 'Agrega una nueva cuenta bancaria o billetera digital.', side: "bottom", align: 'start' } },
//             { element: '#tabla_banco', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas.', side: "top", align: 'center' } }
//         ],
//         pasosModal: [
//         { element: '#nombre_banco', popover: { title: 'Entidad Bancaria', description: 'Nombre del banco o plataforma (ej: Banco de Venezuela, Banesco, Binance).', side: 'bottom', align: 'start' } },
//         { element: '#codigo', popover: { title: 'Código Bancario', description: 'Los primeros 4 dígitos que identifican al banco (ej: 0102).', side: 'bottom', align: 'start' } },
//         { element: '#numero_cuenta', popover: { title: 'Número de Cuenta', description: 'El número completo de la cuenta o la dirección de la billetera/correo (si es Zelle/Paypal).', side: 'top', align: 'start' } },
//         { element: '#tipo_cuenta', popover: { title: 'Tipo de Cuenta', description: 'El tipo de cuenta utilizado (si es Ahorro o Corriente).', side: 'top', align: 'start' } },
//         { element: '#telefono_afiliado', popover: { title: 'Teléfono Afiliado', description: 'Número de teléfono asociado a la cuenta para validaciones de Pago Móvil.', side: 'top', align: 'start' } },
//         { element: '#rif', popover: { title: 'Titular', description: 'Cédula o RIF del titular de la cuenta bancaria.', side: 'top', align: 'start' } },
//         { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la cuenta para empezar a recibir operaciones.', side: 'top', align: 'center' } }
//         ]
//     });
// });


// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Cuentas Bancarias', description: 'Aquí gestionas los bancos receptores donde el condominio recibe los pagos.', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_banco"]', popover: { title: 'Registrar Banco', description: 'Agrega una nueva cuenta bancaria o billetera digital.', side: "bottom", align: 'start' } },
            { element: '#tabla_banco', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#nombre_banco', popover: { title: 'Entidad Bancaria', description: 'Nombre del banco o plataforma (ej: Banco de Venezuela, Banesco, Binance).', side: 'bottom', align: 'start' } },
            { element: '#codigo', popover: { title: 'Código Bancario', description: 'Los primeros 4 dígitos que identifican al banco (ej: 0102).', side: 'bottom', align: 'start' } },
            { element: '#numero_cuenta', popover: { title: 'Número de Cuenta', description: 'El número completo de la cuenta o la dirección de la billetera/correo (si es Zelle/Paypal).', side: 'top', align: 'start' } },
            { element: '#tipo_cuenta', popover: { title: 'Tipo de Cuenta', description: 'El tipo de cuenta utilizado (si es Ahorro o Corriente).', side: 'top', align: 'start' } },
            { element: '#telefono_afiliado', popover: { title: 'Teléfono Afiliado', description: 'Número de teléfono asociado a la cuenta para validaciones de Pago Móvil.', side: 'top', align: 'start' } },
            { element: '#rif', popover: { title: 'Titular', description: 'Cédula o RIF del titular de la cuenta bancaria.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la cuenta para empezar a recibir operaciones.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_banco',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
