/**
 * Script AJAX para la gestión de Usuarios
 * Dependencias: Peticiones.js, Alertas.js, Tablas.js, EstadoInputs.js
 */

let id_modificar, correo_an;

const permiso_eliminar = document.querySelector("#permiso_eliminar").value;
const permiso_modificar = document.querySelector("#permiso_modificar").value;

const boton_formulario = document.querySelector("#boton_formulario"); 
const modal = new bootstrap.Modal(document.getElementById("modal_usuario"), { focus: false });
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

    const formatoRol = (cell) => {
        const rol = cell.getValue();
        const colores = {
            'Administrador Global': "badge bg-warning text-dark",
            'Administrador': "badge bg-primary",
            'Propietario': "badge bg-success",
            'Contador': "badge bg-danger",
            'Presidente': "badge bg-info text-dark"
        };
        const claseBadge = colores[rol] || "badge bg-secondary";
        return `<span class="${claseBadge}">${rol}</span>`;
    };
    
    const formatoBotones = (cell) => {
        const id = cell.getData().id_usuario;
        let html = `<div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-success btn-sm modificar" data-bs-toggle="modal" data-bs-target="#modal_usuario" title="Modificar" value="${id}">
                            <i class="bi bi-pencil-square"></i>
                        </button>`;
        if (permiso_eliminar) {
            html += `<button type="button" class="btn btn-danger btn-sm eliminar" title="Eliminar" value="${id}">
                        <i class="bi bi-trash"></i>
                    </button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center", },
        { title: "Nombre", field: "nombre", minWidth: 100, responsive: 0 },
        { title: "Apellido", field: "apellido", minWidth: 100 },
        { title: "Correo", field: "correo", minWidth: 180 }, 
        { title: "Rol", field: "nombre_rol", formatter: formatoRol, vertAlign: "middle", minWidth: 150 },      
        { 
            title: "Acciones", 
            formatter: formatoBotones, 
            headerSort: false, 
            hozAlign: "center",
            headerHozAlign: "center",
            vertAlign: "middle",
            minWidth: 100,
            responsive: 0,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                if (btn.classList.contains('modificar')) preparar_formulario({ target: btn });
                if (btn.classList.contains('eliminar')) eventoEliminar({ target: btn });
            }
        }       
    ];

    tabla_usuarios = Tablas.cargarTabulador(contenedorTabla.id, "", columnas);

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas
                .filter(col => col.field) 
                .map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_usuarios.setFilter([filtros]);
        });
    }
}

async function registrar() {
    let datos = new FormData(formulario_usar);
    datos.append('operacion', 'registrar');
    
    let respuesta = await Peticiones.enviar(datos);

    modal.hide();
    formulario_usar.reset();

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_usuarios.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro se ha realizado exitosamente');
}

async function preparar_formulario(e) {
    let datos = new FormData();
    let id = e.target.value || e.target.parentElement.value; 
    
    datos.append("id_usuario", id);
    datos.append('operacion', 'consulta_especifica');

    let respuesta = await Peticiones.enviar(datos);    
    
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    let data = respuesta.datos; 

    formulario_usar.querySelector("#nombre").value = data.nombre;
    formulario_usar.querySelector("#apellido").value = data.apellido;    
    formulario_usar.querySelector("#correo").value = data.correo;
    formulario_usar.querySelector("#rol").value = data.rol_id;  

    if(!permiso_modificar){
        boton_formulario.setAttribute("hidden", true);
        boton_formulario.setAttribute("disabled", true);
    }
    
    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", data.id_usuario);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById('titulo_modal').textContent = "Modificar Usuario";
    
    formulario_usar.querySelector("#confir_contra").parentElement.previousElementSibling.textContent = "Nueva Contraseña" ;
    formulario_usar.querySelector("#confir_contra").placeholder = "Escriba su Nueva Contraseña" ;
    formulario_usar.querySelector("#contra").placeholder = "Escriba su Contraseña";

    id_modificar = id;
    correo_an = data.correo;
}

async function modificar(id) {  
    let datos = new FormData(formulario_usar);
    let nueva_contra = formulario_usar.querySelector("#confir_contra").value || formulario_usar.querySelector("#contra").value;
    let rol_nombre = formulario_usar.querySelector("#rol").selectedOptions[0].textContent;

    datos.append("id_usuario", id);
    datos.append("contra", nueva_contra);
    datos.append("rol_nombre", rol_nombre);
    datos.append('operacion', 'modificar_usuario');

    let respuesta = await Peticiones.enviar(datos);

    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    if (respuesta.esMismoUsuario){

       const nombreUsuario = document.getElementById('nombre_usuario_sesion');
        if (nombreUsuario) {
            const nuevoNombre = document.getElementById('nombre').value + " " + document.getElementById('apellido').value;
            nombreUsuario.textContent = nuevoNombre;
        }
    }

    modal.hide();
    formulario_usar.reset();

    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");   
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar Usuario";

    tabla_usuarios.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro se ha modificado exitosamente');
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
    
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    tabla_usuarios.replaceData();
    Alertas.mostrar('success', 'Éxito', 'El registro ha sido eliminado correctamente');
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - USUARIOS
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar que la burbuja se ancle bien
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // Configuración base
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
        { element: '.page-header', popover: { title: 'Gestión de Usuarios', description: 'Aquí administras quién tiene acceso al sistema y qué nivel de permisos posee.', side: "bottom", align: 'center' } },
        { element: 'button[data-bs-target="#modal_usuario"]', popover: { title: 'Nuevo Usuario', description: 'Registra un nuevo operador, administrador o propietario para que pueda iniciar sesión.', side: "bottom", align: 'start' } },
        { element: '#tabla_usuarios_wrapper', popover: { title: 'Directorio', description: 'Lista de usuarios registrados. Puedes editar sus datos (como resetear contraseñas) o eliminarlos.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#nombre', popover: { title: 'Datos Personales', description: 'Ingresa el Nombre y Apellido del usuario.', side: 'bottom', align: 'start' } },
        { element: '#correo', popover: { title: 'Correo Electrónico', description: 'Email que servirá como usuario para el inicio de sesión.', side: 'top', align: 'start' } },
        { element: '#rol', popover: { title: 'Rol y Permisos', description: 'Define qué puede hacer este usuario en el sistema (Administrador, Super Usuario, etc.).', side: 'top', align: 'start' } },
        { element: '#contra', popover: { title: 'Seguridad', description: 'Establece una contraseña. Puedes usar el botón del "ojo" a la derecha para verificar lo que escribes.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Crea el usuario y otorga el acceso inmediato.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalHTML = document.getElementById('modal_usuario');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalHTML && modalHTML.classList.contains('show')) {
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    if (modalHTML) {
        modalHTML.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});