/**
 * bancos_ajax.js
 * Gestión de Bancos - Peticiones AJAX
 * Dependencias: utilidades.js
 */

let id_modificar;
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
    // boton_formulario.textContent = "Guardar";
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Banco';
    document.getElementById('titulo_modal').textContent = "Registrar Banco";
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-bank");
    
    // Limpiar clases de validación
    formulario_usar.querySelectorAll('.is-valid').forEach(input => input.classList.remove('is-valid'));
    formulario_usar.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    formulario_usar.querySelectorAll('.w-100').forEach(el => el.textContent = "");
});

async function consultar() {
    // Encontrar el contenedor dinámicamente
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    const formatoBanco = (cell) => {
        let nombre = cell.getValue() || "";
        // Forzamos primera letra mayúscula y el resto minúscula para estandarizar
        nombre = nombre.charAt(0).toUpperCase() + nombre.slice(1).toLowerCase();
        return `<div class="d-flex align-items-center fw-bold">
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

    // Formato de Botones
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

    // Estructura de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Banco", field: "nombre_banco", formatter: formatoBanco, minWidth: 150, responsive: 0 },
        { title: "Código", field: "codigo", formatter: formatoCodigo, minWidth: 120 },
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
                const id = cell.getData().id_banco;
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];
    
    tabla_bancos = Tablas.cargarTabulador(contenedor.id, "", columnas);

    // 5. Buscador Global Dinámico
    Tablas.inicializarBuscadorGlobal(tabla_bancos, "busqueda_global", columnas);
}

function mostrarVistaPrevia(data) {
    // Formatear Nombre del Banco
    let nombreBanco = data.nombre_banco || 'N/A';
    if (nombreBanco !== 'N/A') {
        nombreBanco = nombreBanco.charAt(0).toUpperCase() + nombreBanco.slice(1).toLowerCase();
    }
    document.getElementById("detalle_nombre_banco").textContent = nombreBanco;

    // Agregar el Código Bancario
    document.getElementById("detalle_codigo_banco").textContent = data.codigo || '---';

    // Mostramos el modal
    modalDetalles.show();
}

/**
 * Prepara el formulario con los datos del banco a modificar
 */
async function prepararFormulario(id) {
    let datos = new FormData();
    
    datos.append("id_banco", id);
    datos.append('operacion', 'consultar_banco');

    let respuesta = await Peticiones.enviar(datos);	

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos; // { id_banco, nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif }

        // Llenar formulario
        formulario_usar.querySelector("#nombre_banco").value = data.nombre_banco;
        formulario_usar.querySelector("#codigo").value = data.codigo;

        if (permisoModificar != 1) {
            boton_formulario.setAttribute("hide", true);
            boton_formulario.setAttribute("disabled", true);
        }

        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", data.id_banco);
        // boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = "Modificar Banco";
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-bank2");

        id_modificar = id;

        modal.show();
    });
}

/**
 * Registra un nuevo banco
 */
async function registrar() {
    let datos = new FormData(formulario_usar);
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
async function modificar() {	
    let datos = new FormData(formulario_usar);
    datos.append("id_banco", id_modificar);
    datos.append('operacion', 'modificar_banco');

    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_bancos.replaceData();
    });
}

function confirmarEliminar(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Banco?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminar(id); }
    );
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

// MÓDULO DE AYUDA INTERACTIVA
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Cuentas Bancarias', description: 'Aquí gestionas los bancos receptores donde el condominio recibe los pagos.', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_banco"]', popover: { title: 'Registrar Banco', description: 'Agrega una nueva cuenta bancaria o billetera digital.', side: "bottom", align: 'start' } },
            { element: '#tabla_banco', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#nombre_banco', popover: { title: 'Entidad Bancaria', description: 'Nombre del banco o plataforma (ej: Banco de Venezuela, Banesco, Binance).', side: 'bottom', align: 'start' } },
            { element: '#codigo', popover: { title: 'Código Bancario', description: 'Los primeros 4 dígitos que identifican al banco (ej: 0102).', side: 'bottom', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la cuenta para empezar a recibir operaciones.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_banco',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
