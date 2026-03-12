/**
 * Script AJAX para el perfil de usuario
 * Dependencias: utilidades.js (objeto Utilidades), validaciones.js (objeto Validaciones)
 */

let correo_an; // Para comparar en edición
let tabla_notificaciones;

document.addEventListener('DOMContentLoaded', () => {
    llenarCardUsuario();
    llenarTablaNotificaciones();
});

// Resetear modal de cambio de contraseña al cerrar
document.getElementById('modal_contra')?.addEventListener('hide.bs.modal', () => {
    document.querySelectorAll('.is-valid, .is-invalid').forEach(input => input.classList.remove('is-valid', 'is-invalid'));
    document.querySelectorAll('input').forEach(input => {
        if (input.id.includes('contra')) {
            const iconParent = input.nextElementSibling;
            if (iconParent) {
                iconParent.classList.remove('border-danger', 'text-danger', 'border-success', 'text-success');
                const icon = iconParent.querySelector('i');
                if (icon) icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
        input.value = '';
    });
    correo_an = null;

    document.getElementById("barra_seguridad").setAttribute("style","width: 0%; transition: width 0.4s ease;");
    document.getElementById("barra_seguridad").setAttribute("class","progress-bar bg-danger transition-all");
    document.getElementById("texto_seguridad").setAttribute("class","fw-medium text-danger d-block mb-3 w-100 invalid-feedback");
    document.getElementById("texto_seguridad").textContent = "Nivel de seguridad: Vacío";
});

// Mostrar/ocultar contraseñas
document.querySelectorAll('.contra-btn').forEach(boton => {
    boton.addEventListener('click', e => {
        e.preventDefault();
        const icon = e.target.tagName === 'I' ? e.target : e.target.querySelector('i');
        if (!icon) return;
        const input = icon.parentElement.previousElementSibling;
        if (icon.classList.contains('bi-eye')) {
            input.setAttribute('type', 'text');
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.setAttribute('type', 'password');
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});

// Botones de edición/cancelación
document.getElementById('boton_modificar')?.addEventListener('click', () => {
    document.getElementById('nombre').value = document.getElementById('p_nombre').textContent;
    document.getElementById('apellido').value = document.getElementById('p_apellido').textContent;
    document.getElementById('correo').value = document.getElementById('p_correo').textContent;

    document.getElementById('body_perfil').setAttribute('hidden', '');
    document.getElementById('form_perfil').removeAttribute('hidden');
    document.getElementById('boton_modificar').setAttribute('disabled', '');
});

document.getElementById('boton_cancelar')?.addEventListener('click', () => {
    document.querySelectorAll('.is-valid, .is-invalid').forEach(input => input.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('form_perfil').setAttribute('hidden', '');
    document.getElementById('body_perfil').removeAttribute('hidden');
    document.getElementById('boton_modificar').removeAttribute('disabled');
});

// ============================================
// FUNCIONES PRINCIPALES
// ============================================

async function llenarCardUsuario() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_perfil_usuario');

    const respuesta = await Peticiones.enviar(formData, '', false);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje || 'Error al cargar perfil');
        return;
    }

    const usuario = respuesta.datos;
    const [claseBadge, claseIcono] = definirColorBadge(usuario.nombre_rol);

    // --- NUEVA LÓGICA DE AVATAR VISUAL (INICIALES) ---
    // Tomamos la primera letra del nombre y la primera del apellido
    const inicialNombre = usuario.nombre_usuario.charAt(0).toUpperCase();
    const inicialApellido = usuario.apellido.charAt(0).toUpperCase();
    const iniciales = `${inicialNombre}${inicialApellido}`;
    
    // Inyectamos las iniciales eliminando el placeholder
    const contenedorAvatar = document.getElementById('contenedor_avatar');
    contenedorAvatar.innerHTML = iniciales;
    
    // Extraemos el color de fondo (bg-primary, bg-warning, etc.) del array claseBadge para pintar el avatar
    const claseColorFondo = claseBadge.split(' ')[1]; // toma 'bg-primary' de 'badge bg-primary'
    contenedorAvatar.classList.remove('bg-primary'); // quitamos el azul por defecto
    contenedorAvatar.classList.add(claseColorFondo);
    if(claseColorFondo === 'bg-warning' || claseColorFondo === 'bg-info') {
        contenedorAvatar.classList.replace('text-white', 'text-dark');
    }
    // ------------------------------------------------

    document.getElementById('titulo_nombre').textContent = `${usuario.nombre_usuario} ${usuario.apellido}`;
    document.getElementById('p_nombre').textContent = usuario.nombre_usuario;
    document.getElementById('p_apellido').textContent = usuario.apellido;
    document.getElementById('p_correo').textContent = usuario.correo;
    document.getElementById('spam_rol').textContent = usuario.nombre_rol;
    document.getElementById('ultimo_acceso').textContent = FormatoFechas.formatoUltimoAcceso(usuario.ultima_vez);

    correo_an = usuario.correo;

    const spamRol = document.getElementById('spam_rol');
    spamRol.className = claseBadge;

    document.getElementById('boton_modificar').removeAttribute('disabled');
    document.getElementById('boton_modificar').innerHTML = '<i class="bi bi-pencil me-1"></i>modificar';
}

function llenarTablaNotificaciones() {
    // 1. Encontrar el contenedor de la tabla
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // 2. Formateadores Visuales
    const formatoLeido = (cell) => {
        const leido = cell.getValue();
        return `<span class="${leido == 1 ? 'badge bg-primary' : 'badge bg-warning text-dark'}">${leido == 1 ? 'Sí' : 'No'}</span>`;
    };

    const formatoBotones = (cell) => {
        // Un botón HTML súper limpio, sin necesidad de inyectarle data-atributos
        return `<button class="btn btn-sm btn-primary ver-notificacion" title="Ver Notificación" type="button">
                    <i class="bi bi-eye"></i>
                </button>`;
    };

    // 3. Definición de Columnas
    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false, headerHozAlign: "center" },
        { title: "Título", field: "titulo", minWidth: 150, responsive: 0 },
        { title: "Descripción", field: "descripcion", minWidth: 300 },
        { title: "Fecha", field: "fecha", formatter: (cell) => FormatoFechas.formatoUsuario(cell.getValue()), minWidth: 50 },
        { title: "Leído", field: "leido", formatter: formatoLeido, minWidth: 50, hozAlign: "center", headerHozAlign: "center" },
        {
            title: "Acciones", 
            formatter: formatoBotones, 
            headerSort: false, hozAlign: "center", vertAlign: "middle", 
            minWidth: 100, responsive: 0, download: false,
            headerHozAlign: "center",
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;

                if (btn.classList.contains('ver-notificacion')) {
                    // Extraemos los datos necesarios directamente de la fila en memoria
                    const data = cell.getData();
                    const url = `?pagina=${data.tabla_origen}&accion=inicio&buscar=${data.id_registro_origen}`;
                    window.location.href = url;
                }
            }
        }
    ];

    // 4. Parámetros a enviar a PHP
    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar_mis_notificaciones' }
    };

    let mostrarCarga = false;

    // 5. Inicialización de Tabulator
    tabla_notificaciones = Tablas.cargarTabulador(contenedor.id, "", columnas, opcionesExtra, mostrarCarga);

    // Activamos el elemento visual si existe (esto lo tenías en tu código original)
    document.getElementById('notificaciones')?.removeAttribute('disabled');

    // 6. Buscador global dinámico
    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas.filter(col => col.field).map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_notificaciones.setFilter([filtros]);
        });
    }
}

function definirColorBadge(nombreRol) {
    const map = {
        'Administrador Global': ['badge bg-warning text-dark', 'bi bi-globe me-3'],
        'Administrador': ['badge bg-primary', 'bi bi-person-fill-gear me-3'],
        'Propietario': ['badge bg-success', 'bi bi-key-fill me-3'],
        'Contador': ['badge bg-danger', 'bi bi-calculator-fill me-3'],
        'Presidente': ['badge bg-info text-dark', 'bi bi-award-fill me-3']
    };
    return map[nombreRol] || ['badge bg-secondary', 'bi bi-person-circle me-3'];
}

// ============================================
// FUNCIONES DE MODIFICACIÓN (llamadas desde validaciones)
// ============================================

async function modificar() {
    const formData = new FormData();
    formData.append('nombre', document.getElementById('nombre').value);
    formData.append('apellido', document.getElementById('apellido').value);
    formData.append('correo', document.getElementById('correo').value);
    formData.append('operacion', 'modificar_perfil');

    const respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    // Actualizar nombre en el botón de la barra superior (opcional)
    const nombreUsuario = document.getElementById('nombre_usuario_sesion');
    if (nombreUsuario) {
        const nuevoNombre = document.getElementById('nombre').value + " " + document.getElementById('apellido').value;
        // botonUsuario.textContent = botonUsuario.textContent.replace(/\s\S+$/, ' ' + nuevoNombre);
        
        nombreUsuario.textContent = nuevoNombre;
    }

    await llenarCardUsuario(); // Recargar datos
    Alertas.mostrar('success', 'Éxito', 'Datos actualizados correctamente');

    document.getElementById('form_perfil').setAttribute('hidden', '');
    document.getElementById('body_perfil').removeAttribute('hidden');
    document.getElementById('boton_modificar').removeAttribute('disabled');
}

async function modificarContra() {
    const formData = new FormData();
    formData.append('contra', document.getElementById('contra').value);
    formData.append('correo', document.getElementById('p_correo').textContent);
    formData.append('operacion', 'cambiar_contrasenia');

    const respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Atención', respuesta.mensaje);
        return;
    }

    // Limpiar campos
    document.querySelectorAll('input').forEach(input => input.value = '');
    Alertas.mostrar('success', 'Éxito', 'Contraseña actualizada correctamente');

    const modal = bootstrap.Modal.getInstance(document.getElementById('modal_contra'));
    modal?.hide();
}

// Exponer funciones para que las use el validador
window.modificar = modificar;
window.modificarContra = modificarContra;