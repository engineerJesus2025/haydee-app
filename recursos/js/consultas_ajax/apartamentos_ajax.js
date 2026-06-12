let tabla_apartamentos;
let data_table_habitantes;
let id_apartamento_seleccionado;

const FormatosVisuales = {
    nro: (valor) => `<div class="d-flex align-items-center fw-bold">
                        <i class="bi bi-door-closed text-primary me-2 fs-5"></i> ${valor}
                     </div>`,

    porcentaje: (valor) => `<span class="text-muted fw-semibold">
                                <i class="bi bi-pie-chart-fill me-1 opacity-50"></i> ${valor}%
                            </span>`,

    // Servicios (Gas, Agua, etc.)
    tiene: (valor) => {
        if (valor == 1) {
            return ComponentesUI.crearSoftBadge('success', 'bi-check-circle', 'TIENE');
        } else {
            return ComponentesUI.crearSoftBadge('danger', 'bi-x-circle', 'NO TIENE');
        }
    },

    // Estado de ocupación (Alquilado/Propietario)
    siNo: (valor) => {
        if (valor == 1) {
            // Alquilado: Azul suave (Primary)
            return ComponentesUI.crearSoftBadge('primary', 'bi-key-fill', 'Alquilado');
        } else {
            // Propietario: Gris suave (Secondary)
            return ComponentesUI.crearSoftBadge('secondary', 'bi-house-door-fill', 'Propietario');
        }
    }
};

const modalApartamento = new bootstrap.Modal(document.getElementById("modal_apartamentos"), { focus: false });
const modalVistaPrevia = new bootstrap.Modal(document.getElementById("modal_vista_previa"), { focus: false });
const modalHabitante = new bootstrap.Modal(document.getElementById("modal_habitantes"), { focus: false });
const modalVistaPreviaHabitantes = new bootstrap.Modal("#modal_vista_previa_habitantes");

const formApartamento = document.querySelector("#form_apartamentos");
const formHabitantes = document.querySelector("#form_habitantes");
const btnFormulario = document.querySelector("#boton_formulario");
const btnFormularioHabitante = document.querySelector("#boton_formulario_habitantes");

// Permisos
const permisoModificar = window.PermisosModulo?.apartamentos?.modificar || false;
const permisoEliminar = window.PermisosModulo?.apartamentos?.eliminar || false;
const permisoModificarHabitantes = window.PermisosModulo?.habitantes?.modificar || false;
const permisoEliminarHabitantes = window.PermisosModulo?.habitantes?.eliminar || false;

// APARTAMENTOS
document.addEventListener('DOMContentLoaded', () => {
    consultarApartamentos();
});

async function consultarApartamentos() {
    // 1. FORMATOS VISUALES (Consumiendo objeto global)
    const formatoNro = (cell) => FormatosVisuales.nro(cell.getValue());
    const formatoPorcentaje = (cell) => FormatosVisuales.porcentaje(cell.getValue());
    const formatoTiene = (cell) => FormatosVisuales.tiene(cell.getValue());
    const formatoSiNo = (cell) => FormatosVisuales.siNo(cell.getValue());

    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
                        <button data-tooltip="true" type="button" class="btn btn-primary btn-sm vista-previa" title="Ver Habitantes del Apartamento">
                            <i class="bi bi-people-fill"></i>
                            <span class="d-none d-lg-inline ms-2">Habitantes</span>
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

    // 2. COLUMNAS
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Nro.", field: "nro_apartamento", formatter: formatoNro, minWidth: 100, responsive: 0 },
        { title: "Participación", field: "porcentaje_participacion", formatter: formatoPorcentaje, minWidth: 150 },
        { title: "Alquilado", field: "alquilado", formatter: formatoSiNo, minWidth: 150 },
        { 
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 150, 
            responsive: 0, download: false, headerHozAlign: "center",
            widthGrow: 2,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_apartamento;

                if (btn.classList.contains('vista-previa')) mostrarVistaPrevia(id);
                if (btn.classList.contains('modificar')) prepararFormularioApartamento(id);
                if (btn.classList.contains('eliminar')) confirmarEliminarApartamento(id);
            }
        }
    ];

    // 3. OPCIONES EXTRA Y LLENADO DEL SELECT
    const opcionesExtra = {
        parametrosExtra: { operacion: 'consulta' },
        
        // LA MAGIA: Tabulator ejecuta esto al recibir la data. Ideal para llenar tu select.
        dataLoaded: function(data) {
            const selectApto = document.getElementById("apartamento_id");
            if (selectApto) {
                // Limpiamos primero para evitar duplicados al recargar la tabla
                selectApto.innerHTML = '<option value="" selected hidden>Seleccione un apartamento</option>';
                data.forEach(item => {
                    const option = new Option(item.nro_apartamento, item.id_apartamento);
                    selectApto.add(option);
                });
            }
        }
    };

    tabla_apartamentos = Tablas.cargarTabulador("tabla_apartamentos", "", columnas, opcionesExtra);

    // 4. BUSCADOR GLOBAL
    Tablas.inicializarBuscadorGlobal(tabla_apartamentos, "busqueda_global", columnas);
}

async function registrarApartamento() {
    const datos = new FormData(formApartamento);
    datos.set('operacion', 'registrar_apartamento');

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modalApartamento.hide();
        tabla_apartamentos.replaceData();
    });
}

async function prepararFormularioApartamento(id) {
    const datos = new FormData();
    datos.append('id_apartamento', id);
    datos.append('operacion', 'consulta_especifica');

    const respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const apto = respuestaServidor.apartamento;
        formApartamento.querySelector('#nro_apartamento').value = apto.nro_apartamento;
        formApartamento.querySelector('#porcentaje_participacion').value = apto.porcentaje_participacion;
        formApartamento.querySelector('#gas').value = apto.gas;
        formApartamento.querySelector('#agua').value = apto.agua;
        formApartamento.querySelector('#alquilado').value = apto.alquilado;

        nro_apartamento_an = apto.nro_apartamento;

        document.getElementById('titulo_modal').textContent = 'Modificar Apartamento';
        document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-building-gear");
        // btnFormulario.textContent = 'Guardar Cambios';
        document.getElementById('texto_boton_formulario').textContent = 'Guardar Cambios';
        btnFormulario.dataset.id = id;

        modalApartamento.show();
    });
}

async function modificarApartamento() {
    const id = btnFormulario.dataset.id;
    const datos = new FormData(formApartamento);
    datos.set('id_apartamento', id);
    datos.set('operacion', 'modificar_apartamento');

    const respuesta = await Peticiones.enviar(datos, "", true);
    Validador.procesarRespuesta(respuesta, () => {
        modalApartamento.hide();
        tabla_apartamentos.replaceData();
    });
}

function confirmarEliminarApartamento(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Apartamento?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminarApartamento(id); }
    );
}

async function eliminarApartamento(id) {
    const datos = new FormData();
    datos.append('id_apartamento', id);
    datos.append('operacion', 'eliminar');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_apartamentos.replaceData();
    });
}

document.getElementById('modal_apartamentos').addEventListener('hide.bs.modal', () => {
    formApartamento.reset();
    delete btnFormulario.dataset.id;
    document.getElementById('titulo_modal').textContent = 'Registrar Apartamento';
    document.getElementById("icono_titulo_modal").setAttribute("class","bi bi-building-add");
    document.getElementById('texto_boton_formulario').textContent = 'Guardar Apartamento';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));
});

// VISTA PREVIA DE APARTAMENTO Y HABITANTES
async function mostrarVistaPrevia(id) {
    id_apartamento_seleccionado = id;

    // Cargar datos del apartamento actual
    const datosApto = new FormData();
    datosApto.append('id_apartamento', id);
    datosApto.append('operacion', 'consulta_especifica');
    const respApto = await Peticiones.enviar(datosApto);

    Validador.procesarRespuesta(respApto, (respuestaServidor) => {
        const apto = respuestaServidor.apartamento;
        document.getElementById('apt_nro').textContent = apto.nro_apartamento || 'N/A';
        document.getElementById('apartamento_nro_visual').value = apto.nro_apartamento || 'N/A';
        document.getElementById('apt_porcentaje').textContent = apto.porcentaje_participacion || '0';
        document.getElementById('apt_gas').innerHTML = FormatosVisuales.tiene(apto.gas);
        document.getElementById('apt_agua').innerHTML = FormatosVisuales.tiene(apto.agua);
        document.getElementById('apt_alquilado').innerHTML = FormatosVisuales.siNo(apto.alquilado);
        // Inicializar o recargar tabla de habitantes
        if (!data_table_habitantes) {
            initTablaHabitantes();
        } else {
            const parametrosExtra = { 
                operacion: 'consultar_habitantes',
                id_apartamento: id_apartamento_seleccionado
            }
            data_table_habitantes.setData("",parametrosExtra);
        }
        document.getElementById("apartamento_id").value = id_apartamento_seleccionado;
        modalVistaPrevia.show();   
    });
}

function initTablaHabitantes() {
    // FORMATOS VISUALES
    const formatoNro = (cell) => FormatosVisuales.nro(cell.getValue());

    const formatoBotones = (cell) => {
        let html = `<div class="d-flex justify-content-center flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm vista-previa-habitante" data-tooltip="true" title="Ver Mas">
                <i class="bi bi-eye"></i>
                <span class="d-none d-lg-inline ms-2">Ver</span>
            </button>`;
        if (permisoModificarHabitantes) {
            html += `<button data-tooltip="true" type="button" class="btn btn-success btn-sm modificar-habitante" title="Modificar los detalles de este registro" data-bs-toggle="modal" data-bs-target="#modal_habitantes">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-lg-inline ms-2">Editar</span>
                    </button>`;
        }
        if (permisoEliminarHabitantes) {
            html += `<button data-tooltip="true" type="button" class="btn btn-danger btn-sm eliminar-habitante" title="Quitar este elemento del sistema">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-lg-inline ms-2">Borrar</span>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    // COLUMNAS
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Nombre", field: "nombre", minWidth: 130, responsive: 0 },
        { title: "Apellido", field: "apellido", minWidth: 130 },
        { title: "Vínculo", field: "tipo_vinculo", minWidth: 130 },
        { 
            title: "Acciones", formatter: formatoBotones, headerSort: false, 
            hozAlign: "center", vertAlign: "middle", minWidth: 140, responsive: 0, 
            download: false, headerHozAlign: "center", widthGrow: 2,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const id = cell.getData().id_habitante;
                
                if (btn.classList.contains('vista-previa-habitante')) mostrarVistaPreviaHabitante(id);
                if (btn.classList.contains('modificar-habitante')) prepararFormularioHabitantes(id);
                if (btn.classList.contains('eliminar')) confirmarEliminarHabitante(id);
            }
        }
    ];

    // 3. ENVIAR VARIABLES DINÁMICAS A PHP
    const opcionesExtra = {
        parametrosExtra: { 
            operacion: 'consultar_habitantes',
            id_apartamento: id_apartamento_seleccionado
        },
        cssClass: "tabla-vista-previa",
        paginaSize: 5
    };

    data_table_habitantes = Tablas.cargarTabulador("tabla_habitantes", "", columnas, opcionesExtra);
}

// ============================================
// HABITANTES (CRUD)
// ============================================
async function registrarHabitante() {
    const datos = new FormData(formHabitantes);
    datos.set('operacion', 'registrar_habitantes');
    datos.set('apartamento_id', id_apartamento_seleccionado);

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modalHabitante.hide();
        data_table_habitantes.replaceData();
    });
}

async function prepararFormularioHabitantes(id) {
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'consulta_especifica_habitante');

    const respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;
        formHabitantes.querySelector('#nombre').value = data.nombre;
        formHabitantes.querySelector('#apellido').value = data.apellido;
        formHabitantes.querySelector('#tipo_cedula').value = data.cedula.charAt(0);
        formHabitantes.querySelector('#cedula').value = data.cedula.slice(1);
        formHabitantes.querySelector('#telefono').value = data.telefono;
        formHabitantes.querySelector('#correo').value = data.correo;
        formHabitantes.querySelector('#fecha_nacimiento').value = data.fecha_nacimiento;
        formHabitantes.querySelector('#sexo').value = data.sexo;
        formHabitantes.querySelector('#apartamento_id').value = data.apartamento_id;
        formHabitantes.querySelector('#apartamento_nro_visual').value = data.apartamento;
        formHabitantes.querySelector('#tipo_vinculo').value = data.tipo_vinculo;

        // Guardar valores originales para comparar en validaciones
        cedula_an = data.cedula;
        correo_an = data.correo;
        tipo_vinculo_an = data.tipo_vinculo;

        document.getElementById('titulo_modal_habitantes').textContent = 'Modificar Habitante';
        document.getElementById("icono_titulo_modal_habitantes").setAttribute("class","bi bi-person-gear");
        document.getElementById("texto_boton_formulario_habitantes").textContent = 'Guardar Cambios';
        btnFormularioHabitante.dataset.id = id;
        formHabitantes.querySelector('#cedula').removeAttribute('disabled');
    });
}

async function modificarHabitante() {
    const id = btnFormularioHabitante.dataset.id;
    const datos = new FormData(formHabitantes);
    datos.set('id_habitante', id);
    datos.set('operacion', 'modificar_habitantes');
    datos.set('apartamento_id', id_apartamento_seleccionado);

    const respuesta = await Peticiones.enviar(datos, "", true);

    Validador.procesarRespuesta(respuesta, () => {
        modalHabitante.hide();
        data_table_habitantes.replaceData();
    });
}

function confirmarEliminarHabitante(id) {
    Alertas.confirmarAccion(
        "¿Eliminar Habitante?",
        "Esta acción no se puede deshacer.",
        "error",
        () => { eliminarHabitante(id); }
    );
}

async function eliminarHabitante(id) {
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'eliminar_habitantes');

    const respuesta = await Peticiones.enviar(datos);

    Validador.procesarRespuesta(respuesta, () => {
        data_table_habitantes.replaceData();
    });
}

async function mostrarVistaPreviaHabitante(id) {
    const datos = new FormData();
    datos.append('id_habitante', id);
    datos.append('operacion', 'consulta_especifica_habitante');

    const respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const data = respuestaServidor.datos;
        // Datos personales
        document.getElementById('vista_nombre').textContent = data.nombre || '';
        document.getElementById('vista_apellido').textContent = data.apellido || '';
        document.getElementById('vista_cedula').textContent = data.cedula || '';
        document.getElementById('vista_telefono').textContent = data.telefono || '';
        document.getElementById('vista_correo').textContent = data.correo || '';
        document.getElementById('vista_fecha_nacimiento').textContent = FormatoFechas.formatoUsuario(data.fecha_nacimiento);
        document.getElementById('vista_sexo').textContent = data.sexo || '';
        // Datos del apartamento
        document.getElementById('vista_apartamento_nro').textContent = data.nro_apartamento ? `Nro ${data.nro_apartamento}` : 'No asignado';
        document.getElementById('vista_apartamento_porcentaje').textContent = data.porcentaje_participacion || '0';
        document.getElementById('vista_apartamento_gas').innerHTML = FormatosVisuales.tiene(data.gas);
        document.getElementById('vista_apartamento_agua').innerHTML = FormatosVisuales.tiene(data.agua);
        document.getElementById('vista_apartamento_alquilado').innerHTML = FormatosVisuales.siNo(data.alquilado);

        modalVistaPreviaHabitantes.show();
    });
}

// Eventos del modal de habitantes
document.getElementById('modal_habitantes').addEventListener('hide.bs.modal', () => {
    formHabitantes.reset();
    formHabitantes.querySelector('#cedula').setAttribute('disabled', '');
    delete btnFormularioHabitante.dataset.id;
    document.getElementById('titulo_modal_habitantes').textContent = 'Registrar Habitante';
    document.getElementById("icono_titulo_modal_habitantes").setAttribute("class","bi bi-person-plus");
    document.getElementById("texto_boton_formulario_habitantes").textContent = 'Guardar Habitante';
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    // Resetear variables globales
    cedula_an = null;
    correo_an = null;
    tipo_vinculo_an = null;
});

// Exponer funciones para el validador (si existe, ya no me acuerdo)
window.registrarApartamento = registrarApartamento;
window.modificarApartamento = modificarApartamento;
window.eliminarApartamento = eliminarApartamento;
window.prepararFormularioApartamento = prepararFormularioApartamento;
window.registrarHabitante = registrarHabitante;
window.modificarHabitante = modificarHabitante;
window.eliminarHabitante = eliminarHabitante;
window.prepararFormularioHabitantes = prepararFormularioHabitantes;

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión Inmobiliaria', description: 'Aquí administras la estructura del condominio (Apartamentos) y quiénes viven en ellos (Habitantes).', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_apartamentos"]', popover: { title: 'Registrar Propiedad', description: 'Usa este botón para dar de alta un nuevo apartamento en el sistema.', side: "bottom", align: 'start' } },
        { element: '#tabla_apartamentos', popover: { title: 'Directorio', description: 'Lista maestra de apartamentos. El botón azul "Personas" te permite gestionar a los habitantes de ese apartamento.', side: "top", align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nro_apartamento', popover: { title: 'Identificación', description: 'Número o código del apartamento (Ej: 1-A, PH-1).', side: 'bottom', align: 'start' } },
        { element: '#porcentaje_participacion', popover: { title: 'Alícuota', description: 'Porcentaje de participación del apartamento para el cálculo de gastos comunes.', side: 'top', align: 'start' } },
        { element: '#gas', popover: { title: 'Servicios', description: 'Indica si este apartamento posee conexión a servicios específicos como Gas o Agua.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el inmueble en la base de datos.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_apartamentos',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
