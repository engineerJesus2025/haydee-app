let id_modificar, correo_an;

const permisoModificar = window.PermisosModulo?.modificar || false;
const permisoEliminar = window.PermisosModulo?.eliminar || false;

const boton_formulario = document.querySelector("#boton_formulario"); 

const modal = new bootstrap.Modal(document.getElementById("modal_usuario"), { focus: false });
const modalDetalles = new bootstrap.Modal(document.getElementById("modal_detalles"), { focus: false });

const formulario_usar = document.querySelector(`#form_usuario`); 
let tabla_usuarios;

consultar();

// Resetear modal al cerrarlo
document.querySelector(`#modal_usuario`).addEventListener("hide.bs.modal", () => {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Usuario";      
    
    formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Confirmar Contraseña";
    formulario_usar.querySelector("#confir_contra").placeholder = "Confirmar Contraseña";
    formulario_usar.querySelector("#contra").placeholder = "Contraseña";

    correo_an = null;

    formulario_usar.querySelectorAll('input, select').forEach(input => {
        EstadoInputs.limpiar(input);
        
        // Resetear el icono del ojo y el tipo de input si es contraseña
        if (input.id.includes('contra')) {
            input.setAttribute('type', 'password');
            const icono = input.parentElement.querySelector('.contra-btn i');
            if (icono && icono.classList.contains('bi-eye-slash')) {
                icono.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    });
    document.getElementById("barra_seguridad").setAttribute("style","width: 0%; transition: width 0.4s ease;");
    document.getElementById("barra_seguridad").setAttribute("class","progress-bar bg-danger transition-all");
    document.getElementById("texto_seguridad").setAttribute("class","fw-medium text-danger d-block mb-3 w-100 invalid-feedback");
    document.getElementById("texto_seguridad").textContent = "Nivel de seguridad: Vacío";
});

// Mostrar/Ocultar contraseñas
document.querySelectorAll('.contra-btn').forEach(boton => {
    boton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // 'this' siempre será el botón <button>.
        // Buscamos el ícono dentro de este botón y el input que está justo antes.
        let icono = this.querySelector('i');
        let inputTarget = this.previousElementSibling; 

        if (icono.classList.contains('bi-eye')) {
            inputTarget.setAttribute('type', 'text');
            icono.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            inputTarget.setAttribute('type', 'password');
            icono.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});

function envio(operacion) { 
    if (operacion === "modificar") {
        modificar(boton_formulario.getAttribute("id_modificar"));
    } else if(operacion === "Registrar"){
        registrar();
    } else {
        Alertas.mostrar('error', 'Atención', 'Ha ocurrido un error durante la operación, inténtelo nuevamente');
    }
}

async function consultar() {
    const contenedorTabla = document.querySelector(".tabla-sistema-haydee");
    if (!contenedorTabla) return;

    const formatoNombre = (cell) => {
        let nombre = cell.getValue() || "";
        nombre = nombre.charAt(0).toUpperCase() + nombre.slice(1).toLowerCase();
        return `<div class="d-flex align-items-center fw-bold text-dark">
                    <i class="bi bi-person-circle text-primary me-2 fs-5"></i> ${nombre}
                </div>`;
    };

    // Formato Apellido (Solo capitalizado)
    const formatoApellido = (cell) => {
        let apellido = cell.getValue() || "";
        return apellido.charAt(0).toUpperCase() + apellido.slice(1).toLowerCase();
    };

    // Formato Rol (Soft Badges + Íconos)
    const formatoRol = (cell) => {
        const rol = cell.getValue() || "Desconocido";

        const [icono,color,claseTextoBorder,claseIcono] = obtenerIconoRol(rol);

        return `<span class="badge bg-${color} bg-opacity-10 ${claseTextoBorder} px-3 py-2 shadow-sm" style="font-size: .85rem;">
                    <i class="bi bi-${icono} ${claseIcono} me-1"></i> ${rol}
                </span>`;
    };
    
    const formatoBotones = (cell) => {
        const id = cell.getData().id_usuario;
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

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Nombre", field: "nombre", formatter: formatoNombre, minWidth: 150, responsive: 0 },
        { title: "Apellido", field: "apellido", formatter: formatoApellido, minWidth: 130 },
        { title: "Rol", field: "nombre_rol", formatter: formatoRol, vertAlign: "middle", minWidth: 200 },      
        { 
            title: "Acciones", 
            formatter: formatoBotones, 
            headerSort: false, 
            hozAlign: "center",
            headerHozAlign: "center",
            vertAlign: "middle",
            minWidth: 130,
            widthGrow: 2,
            responsive: 0,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                if (btn.classList.contains('vista-previa')) {
                    mostrarVistaPrevia(cell.getData());
                }
                if (btn.classList.contains('modificar')) preparar_formulario({ target: btn });
                if (btn.classList.contains('eliminar')) eventoEliminar({ target: btn });
            }
        }       
    ];

    tabla_usuarios = Tablas.cargarTabulador(contenedorTabla.id, "", columnas);

    Tablas.inicializarBuscadorGlobal(tabla_usuarios, "busqueda_global", columnas);
}

// Función que lee la memoria de Tabulator (Sin AJAX extra)
function mostrarVistaPrevia(data) {
    // Nombre Completo (Capitalizado)
    let nombre = data.nombre ? data.nombre.charAt(0).toUpperCase() + data.nombre.slice(1).toLowerCase() : "";
    let apellido = data.apellido ? data.apellido.charAt(0).toUpperCase() + data.apellido.slice(1).toLowerCase() : "";
    
    document.getElementById("vp_nombre_completo").textContent = `${nombre} ${apellido}`;

    // 2. Avatar (Extraemos la primera letra del nombre ya capitalizada)
    document.getElementById("vp_avatar_inicial").textContent = nombre ? nombre.charAt(0) : 'U';

    // Correo
    document.getElementById("vp_correo").textContent = data.correo || 'No registrado';

    // Rol (Inyectamos el mismo Soft Badge de la tabla)
    const rolEl = document.getElementById("vp_rol_badge");
    const rol = data.nombre_rol || "Desconocido";
    
    let color = "secondary";
    let icono = "bi-person-badge";

    if (rol === 'Administrador Global') {
        color = "warning text-dark border-warning"; 
        icono = "bi-shield-lock-fill text-warning";
    } else if (rol === 'Administrador') {
        color = "primary"; icono = "bi-shield-check";
    } else if (rol === 'Propietario') {
        color = "success"; icono = "bi-house-door-fill";
    } else if (rol === 'Contador') {
        color = "info"; icono = "bi-calculator-fill";
    }

    let claseTextoBorder = rol === 'Administrador Global' ? "text-dark border border-warning" : `text-${color} border border-${color}`;
    let claseIcono = rol === 'Administrador Global' ? "text-warning" : "";

    // Como estamos inyectando HTML, le quitamos cualquier clase previa que tuviera el <span> en tu PHP
    rolEl.className = ""; 
    rolEl.innerHTML = `
        <span class="badge bg-${color} bg-opacity-10 ${claseTextoBorder} px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
            <i class="bi ${icono} ${claseIcono} me-1"></i> ${rol}
        </span>`;

    // Mostramos el modal
    modalDetalles.show();
}

async function registrar() {
    let datos = new FormData(formulario_usar);
    datos.append('operacion', 'registrar_usuario');
    
    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        modal.hide();
        tabla_usuarios.replaceData();
    });
}

async function preparar_formulario(e) {
    let datos = new FormData();
    let id = e.target.value || e.target.parentElement.value; 
    
    datos.append("id_usuario", id);
    datos.append('operacion', 'consultar_usuario');

    let respuesta = await Peticiones.enviar(datos);    
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        let data = respuestaServidor.datos; 

        formulario_usar.querySelector("#nombre").value = data.nombre;
        formulario_usar.querySelector("#apellido").value = data.apellido;    
        formulario_usar.querySelector("#correo").value = data.correo;
        formulario_usar.querySelector("#rol_id").value = data.rol_id;  
        
        boton_formulario.setAttribute("modificar", true);
        boton_formulario.setAttribute("id_modificar", data.id_usuario);
        boton_formulario.textContent = "Guardar Cambios";
        document.getElementById('titulo_modal').textContent = "Modificar Usuario";
        
        formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Nueva Contraseña" ;
        formulario_usar.querySelector("#confir_contra").placeholder = "Escriba su Nueva Contraseña" ;
        formulario_usar.querySelector("#contra").placeholder = "Escriba su Contraseña";

        id_modificar = id;
        correo_an = data.correo;

        modal.show();
    });   
}

async function modificar(id) {  
    let datos = new FormData(formulario_usar);
    let nueva_contra = formulario_usar.querySelector("#confir_contra").value || formulario_usar.querySelector("#contra").value;
    let rol_nombre = formulario_usar.querySelector("#rol_id").selectedOptions[0].textContent;

    datos.append("id_usuario", id);
    datos.append("contra", nueva_contra);
    datos.append("rol_nombre", rol_nombre);
    datos.append('operacion', 'modificar_usuario');

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        if (respuestaServidor.esMismoUsuario){

           const nombreUsuario = document.getElementById('nombre_usuario_sesion');
            if (nombreUsuario) {
                const nuevoNombre = document.getElementById('nombre').value + " " + document.getElementById('apellido').value;
                nombreUsuario.textContent = nuevoNombre;
            }
        }
        modal.hide();
        tabla_usuarios.replaceData();
    });
}

function eventoEliminar(e){
    let id = e.target.value || e.target.parentElement.value;

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Está seguro que desea eliminar este Usuario?",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        confirmButtonColor: "#e01d22",
        cancelButtonText: "Cancelar",
        icon: "warning"
    }).then((resultado) => {
        if (resultado.isConfirmed) eliminar(id);                
    });
}

async function eliminar(id) {
    let datos = new FormData();
    datos.append("id_usuario", id);
    datos.append('operacion', 'eliminar');

    let respuesta = await Peticiones.enviar(datos);
    Validador.procesarRespuesta(respuesta, () => {
        tabla_usuarios.replaceData();
    });
}

function obtenerIconoRol(nombre){
    let icono = "person-badge";
    let colorIcono = "secondary";

    switch (nombre.toUpperCase()) {
        case 'ADMINISTRADOR GLOBAL': 
            icono = 'shield-lock-fill'; 
            colorIcono = "warning text-dark border-warning";
            break;
        case 'ADMINISTRADOR': 
            icono = 'shield-check';
            colorIcono = "primary";
            break;
        case 'PROPIETARIO': 
            icono = 'house-door-fill';
            colorIcono = "success";
            break;
        case 'CONTADOR':  
            icono = 'calculator-fill';
            colorIcono = "danger";
            break;
        case 'PRESIDENTE':  
            icono = 'person-workspace';
            colorIcono = "info text-dark";
            break;
    }

    // Si es el warning (Global) necesitamos ajustar el color del texto para que no sea amarillo sobre blanco
    let claseTextoBorder = nombre.toUpperCase() === 'ADMINISTRADOR GLOBAL' ? "text-dark border border-warning" : `text-${colorIcono} border border-${colorIcono}`;
    let claseIcono = nombre.toUpperCase() === 'ADMINISTRADOR GLOBAL' ? "text-warning" : "";

    return [icono,colorIcono,claseTextoBorder,claseIcono];

        if (rol === 'Administrador Global') {
            color = "warning text-dark border-warning"; 
            icono = "bi-shield-lock-fill text-warning";
        } else if (rol === 'Administrador') {
            color = "primary"; 
            icono = "bi-shield-check";
        } else if (rol === 'Propietario') {
            color = "success"; 
            icono = "bi-house-door-fill";
        } else if (rol === 'Contador') {
            color = "info"; 
            icono = "bi-calculator-fill";
        }         
}

// ============================================================
// MÓDULO DE AYUDA INTERACTIVA
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión de Usuarios', description: 'Aquí administras quién tiene acceso al sistema y qué nivel de permisos posee.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_usuario"]', popover: { title: 'Nuevo Usuario', description: 'Registra un nuevo operador, administrador o propietario para que pueda iniciar sesión.', side: "bottom", align: 'start' } },
        { element: '#tabla_usuarios', popover: { title: 'Directorio', description: 'Lista de usuarios registrados. Puedes editar sus datos (como resetear contraseñas) o eliminarlos.', side: 'top', align: 'center' } }
    ];

    const stepsModal = [
        { element: '#nombre', popover: { title: 'Datos Personales', description: 'Ingresa el Nombre y Apellido del usuario.', side: 'bottom', align: 'start' } },
        { element: '#correo', popover: { title: 'Correo Electrónico', description: 'Email que servirá como usuario para el inicio de sesión.', side: 'top', align: 'start' } },
        { element: '#rol_id', popover: { title: 'Rol y Permisos', description: 'Define qué puede hacer este usuario en el sistema (Administrador, Super Usuario, etc.).', side: 'top', align: 'start' } },
        { element: '#contra', popover: { title: 'Seguridad', description: 'Establece una contraseña. Puedes usar el botón del "ojo" a la derecha para verificar lo que escribes.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Crea el usuario y otorga el acceso inmediato.', side: 'top', align: 'center' } }
    ];

    AyudaInteractiva.inicializar({
        idModal: 'modal_usuario',
        pasosPrincipal: stepsPrincipal,
        pasosModal: stepsModal
    });
});
