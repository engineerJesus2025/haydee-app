let id_modificar, numero_cuenta_an;
const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

let boton_formulario = document.querySelector("#boton_formulario");
let formulario_usar = document.querySelector("#form_cuenta");
let tabla_cuentas;

let modal = new bootstrap.Modal(document.getElementById("modal_cuenta"), { focus: false });
let modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

// Inicializar la tabla al cargar
consultar();

// Resetear modal al cerrarlo
document.querySelector("#modal_cuenta").addEventListener("hide.bs.modal", () => {
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
    
    // Deshabilitar RIF hasta que se seleccione tipo de documento
    formulario_usar.querySelector("#rif").setAttribute("disabled", true);
    
    numero_cuenta_an = null;
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

    // Formato para Tipo de Cuenta
    const formatoTipo = (cell) => {
        const config = obtenerConfigTipoCuenta(cell.getValue());
        // Llamamos al helper y le pasamos los parámetros
        return ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
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
        { title: "Tipo de Cuenta", field: "tipo_cuenta", formatter: formatoTipo, minWidth: 160 },
        { title: "RIF/Cédula", field: "rif", minWidth: 120 },
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
                const id = cell.getData().id_cuenta;
                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(cell.getData());
                if (btn.classList.contains('modificar')) prepararFormulario(id);
                if (btn.classList.contains('eliminar')) confirmarEliminar(id);
            }
        }
    ];
    
    tabla_cuentas = Tablas.cargarTabulador(contenedor.id, "", columnas);

    // 5. Buscador Global Dinámico
    Tablas.inicializarBuscadorGlobal(tabla_cuentas, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator
function mostrarVistaPrevia(data) {
    // Formatear Nombre del Banco
    let nombreBanco = data.nombre_banco || 'N/A';
    if (nombreBanco !== 'N/A') {
        nombreBanco = nombreBanco.charAt(0).toUpperCase() + nombreBanco.slice(1).toLowerCase();
    }
    document.getElementById("vp_nombre_banco").textContent = nombreBanco;

    // Agregar el Código Bancario
    document.getElementById("vp_codigo").textContent = data.codigo || '---';

    // Formatear Tipo de Cuenta usando el Helper
    const config = obtenerConfigTipoCuenta(data.tipo_cuenta);

    if (config.texto !== 'N/A') {
        // Inyectamos el componente limpio
        document.getElementById("vp_tipo_cuenta").innerHTML = ComponentesUI.crearSoftBadge(config.color, config.icono, config.texto);
    } else {
        document.getElementById("vp_tipo_cuenta").textContent = config.texto;
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
async function prepararFormulario(id) {
    let datos = new FormData();
    
    datos.append("id_cuenta", id);
    datos.append('operacion', 'consultar_cuenta');

    let respuesta = await Peticiones.enviar(datos);	

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos; // { id_cuenta, nombre_banco, codigo, numero_cuenta, telefono_afiliado, rif }
        
        // Llenar formulario
        formulario_usar.querySelector('#banco_id').value = data.banco_id;
        formulario_usar.querySelector("#numero_cuenta").value = data.numero_cuenta;
        formulario_usar.querySelector("#tipo_cuenta").value = data.tipo_cuenta;
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
        boton_formulario.setAttribute("id_modificar", data.id_cuenta);
        // boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        document.getElementById('titulo_modal').textContent = "Modificar Banco";
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-bank2");

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
    datos.append('operacion', 'registrar_cuenta');
    
    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_cuentas.replaceData();
    });
}

/**
 * Actualiza un banco existente
 */
async function modificar() {	
    let datos = new FormData(formulario_usar);
    let tipo = datos.get('tipo_documento');
    let rifNum = datos.get('rif');
    datos.set('rif', tipo + rifNum);
    datos.append("id_cuenta", id_modificar);
    datos.append('operacion', 'modificar_cuenta');

    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_cuentas.replaceData();
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
    datos.append("id_cuenta", id);
    datos.append('operacion', 'eliminar_cuenta');

    let respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        tabla_cuentas.replaceData();
    });
}

function obtenerConfigTipoCuenta(tipo) {
    let texto = tipo || "N/A";
    let color = "secondary";
    let icono = "bi-wallet2";

    const tipoLower = texto.toLowerCase();

    if (tipoLower === "corriente") {
        color = "info";
        icono = "bi-briefcase-fill";
    } else if (tipoLower === "ahorro") {
        color = "success";
        icono = "bi-safe2-fill";
    } else if (tipoLower.includes("libre convertibilidad")) {
        // para cuentas en divisas como Libre Convertibilidad USD/EUR (por si aplica en un futuro loco)
        color = "warning"; 
        icono = "bi-currency-exchange";
    }

    return { color, icono, texto };
}


// MÓDULO DE AYUDA INTERACTIVA
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
            { element: '.page-header', popover: { title: 'Cuentas Bancarias', description: 'Aquí gestionas los bancos receptores donde el condominio recibe los pagos.', side: "bottom", align: 'center' } },
            { element: 'button[data-bs-target="#modal_cuenta"]', popover: { title: 'Registrar Banco', description: 'Agrega una nueva cuenta bancaria o billetera digital.', side: "bottom", align: 'start' } },
            { element: '#tabla_cuenta', popover: { title: 'Cuentas Activas', description: 'Listado de cuentas registradas.', side: "top", align: 'center' } }
        ];

    const stepsModal = [
            { element: '#banco_id', popover: { title: 'Institución Financiera', description: 'Seleccione el banco al cual pertenece esta cuenta.', side: 'bottom', align: 'start' } },
            { element: '#numero_cuenta', popover: { title: 'Número de Cuenta', description: 'El número completo de la cuenta o la dirección de la billetera/correo (si es Zelle/Paypal).', side: 'top', align: 'start' } },
            { element: '#tipo_cuenta', popover: { title: 'Tipo de Cuenta', description: 'El tipo de cuenta utilizado (si es Ahorro o Corriente).', side: 'top', align: 'start' } },
            { element: '#telefono_afiliado', popover: { title: 'Teléfono Afiliado', description: 'Número de teléfono asociado a la cuenta para validaciones de Pago Móvil.', side: 'top', align: 'start' } },
            { element: '#rif', popover: { title: 'Titular', description: 'Cédula o RIF del titular de la cuenta bancaria.', side: 'top', align: 'start' } },
            { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra la cuenta para empezar a recibir operaciones.', side: 'top', align: 'center' } }
        ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_cuenta',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
