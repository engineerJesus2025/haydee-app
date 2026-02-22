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
});

// Mostrar/ocultar contraseñas
document.querySelectorAll('.contra').forEach(boton => {
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
document.getElementById('boton_editar')?.addEventListener('click', () => {
    document.getElementById('nombre').value = document.getElementById('p_nombre').textContent;
    document.getElementById('apellido').value = document.getElementById('p_apellido').textContent;
    document.getElementById('correo').value = document.getElementById('p_correo').textContent;

    document.getElementById('body_perfil').setAttribute('hidden', '');
    document.getElementById('form_perfil').removeAttribute('hidden');
    document.getElementById('boton_editar').setAttribute('disabled', '');
});

document.getElementById('boton_cancelar')?.addEventListener('click', () => {
    document.querySelectorAll('.is-valid, .is-invalid').forEach(input => input.classList.remove('is-valid', 'is-invalid'));
    document.getElementById('form_perfil').setAttribute('hidden', '');
    document.getElementById('body_perfil').removeAttribute('hidden');
    document.getElementById('boton_editar').removeAttribute('disabled');
});

// Ajustar DataTable cuando se abre el modal de notificaciones
document.getElementById('modal_notificaciones')?.addEventListener('shown.bs.modal', () => {
    if (tabla_notificaciones) {
        tabla_notificaciones.columns.adjust().draw();
    }
});

// ============================================
// FUNCIONES PRINCIPALES
// ============================================

async function llenarCardUsuario() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_perfil_usuario');

    const respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje || 'Error al cargar perfil');
        return;
    }

    const usuario = respuesta.datos;
    const [claseBadge, claseIcono] = definirColorBadge(usuario.nombre_rol);

    document.getElementById('titulo_nombre').textContent = `${usuario.nombre_usuario} ${usuario.apellido}`;
    document.getElementById('titulo_rol').textContent = usuario.nombre_rol;
    document.getElementById('p_nombre').textContent = usuario.nombre_usuario;
    document.getElementById('p_apellido').textContent = usuario.apellido;
    document.getElementById('p_correo').textContent = usuario.correo;
    document.getElementById('spam_rol').textContent = usuario.nombre_rol;
    // document.getElementById('ultimo_acceso').textContent = formatearUltimoAcceso(usuario.ultima_vez);
    document.getElementById('ultimo_acceso').textContent = FormatoFechas.formatoUltimoAcceso(usuario.ultima_vez);

    correo_an = usuario.correo;

    // Icono de rol
    const iconoRol = document.createElement('i');
    iconoRol.className = claseIcono;
    iconoRol.style.fontSize = '4rem';
    const tituloIcono = document.getElementById('titulo_icono');
    tituloIcono.textContent = '';
    tituloIcono.appendChild(iconoRol);

    const spamRol = document.getElementById('spam_rol');
    spamRol.className = claseBadge;

    document.getElementById('boton_editar').removeAttribute('disabled');
    document.getElementById('boton_editar').innerHTML = '<i class="bi bi-pencil me-1"></i>Editar';
}

function llenarTablaNotificaciones() {
    const parametrosConsulta = (data) => {
        data.operacion = 'consultar_mis_notificaciones';
    };

    const estructura = [
        { data: 'titulo' },
        { data: 'descripcion' },
        {
            data: 'fecha',
            // render: (fecha) => formatearFechaHora(fecha)
            render: (fecha) => FormatoFechas.formatoFechaHora(fecha)
        },
        {
            data: null,
            render: (row) => {
                const span = document.createElement('span');
                span.className = (row.leido == 1) ? 'badge bg-primary' : 'badge bg-warning text-dark';
                span.textContent = (row.leido == 1) ? 'Sí' : 'No';
                return span.outerHTML;
            }
        },
        {
            data: null,
            render: (row) => {
                const boton = document.createElement('button');
                boton.className = 'btn btn-sm btn-primary';
                boton.title = 'Ver Notificación';
                boton.type = 'button';
                boton.dataset.tabla_origen = row.tabla_origen;
                boton.dataset.id_registro_origen = row.id_registro_origen;
                boton.innerHTML = '<i class="bi bi-eye"></i>';
                return boton.outerHTML;
            }
        }
    ];

    const configuracionPost = (row, data) => {
        Array.from(row.children).forEach(td => td.classList.add('align-middle'));
        const ultimaCelda = row.children[row.children.length - 1];
        ultimaCelda.classList.add('text-center');
        const boton = ultimaCelda.firstElementChild;
        if (boton) {
            boton.addEventListener('click', () => {
                const url = `?pagina=${boton.dataset.tabla_origen}_controlador.php&accion=inicio&buscar=${boton.dataset.id_registro_origen}`;
                window.location.href = url;
            });
        }
    };

    tabla_notificaciones = Utilidades.crearDataTable(
        'tabla_notificaciones',
        estructura,
        parametrosConsulta,
        configuracionPost
    );

    document.getElementById('notificaciones')?.removeAttribute('disabled');
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

function formatearUltimoAcceso(fecha) {
    const ahora = new Date();
    if (!fecha) return ahora.toLocaleDateString('es-ES');
    const fechaAcceso = new Date(fecha.replace(' ', 'T'));
    const diffMs = ahora - fechaAcceso;
    const diffDias = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    let hora = fechaAcceso.getHours();
    const minutos = fechaAcceso.getMinutes().toString().padStart(2, '0');
    const amPm = hora >= 12 ? 'p.m.' : 'a.m.';
    hora = hora % 12 || 12;
    const horaFormateada = `${hora}:${minutos} ${amPm}`;

    if (diffDias === 0) return `Hoy a las ${horaFormateada}`;
    if (diffDias === 1) return `Ayer a las ${horaFormateada}`;
    if (diffDias <= 7) {
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return `El ${diasSemana[fechaAcceso.getDay()]} a las ${horaFormateada}`;
    }
    return fechaAcceso.toLocaleDateString('es-ES') + ` a las ${horaFormateada}`;
}

function formatearFechaHora(fechaHoraStr) {
    const fecha = new Date(fechaHoraStr);
    const dia = String(fecha.getUTCDate()).padStart(2, '0');
    const mes = String(fecha.getUTCMonth() + 1).padStart(2, '0');
    const anio = fecha.getUTCFullYear();
    return `${dia}-${mes}-${anio}`;
}

// ============================================
// FUNCIONES DE MODIFICACIÓN (llamadas desde validaciones)
// ============================================

async function modificar() {
    const formData = new FormData();
    formData.append('nombre', document.getElementById('nombre').value);
    formData.append('apellido', document.getElementById('apellido').value);
    formData.append('correo', document.getElementById('correo').value);
    formData.append('operacion', 'editar_perfil');

    const respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    // Actualizar nombre en el botón de la barra superior (opcional)
    const botonUsuario = document.getElementById('boton_accion_usuario');
    if (botonUsuario) {
        const nuevoNombre = document.getElementById('nombre').value;
        // botonUsuario.textContent = botonUsuario.textContent.replace(/\s\S+$/, ' ' + nuevoNombre);
        botonUsuario.textContent = botonUsuario.textContent.replace(botonUsuario.textContent.trim().split(" ")[1],nuevoNombre);
    }

    await llenarCardUsuario(); // Recargar datos
    Utilidades.mensaje('success', 'Éxito', 'Datos actualizados correctamente');

    document.getElementById('form_perfil').setAttribute('hidden', '');
    document.getElementById('body_perfil').removeAttribute('hidden');
    document.getElementById('boton_editar').removeAttribute('disabled');
}

async function modificarContra() {
    const formData = new FormData();
    formData.append('contra', document.getElementById('contra').value);
    formData.append('correo', document.getElementById('p_correo').textContent);
    formData.append('operacion', 'cambiar_contrasenia');

    const respuesta = await Utilidades.query(formData);
    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 'Atención', respuesta.mensaje);
        return;
    }

    // Limpiar campos
    document.querySelectorAll('input').forEach(input => input.value = '');
    Utilidades.mensaje('success', 'Éxito', 'Contraseña actualizada correctamente');

    const modal = bootstrap.Modal.getInstance(document.getElementById('modal_contra'));
    modal?.hide();
}

// Exponer funciones para que las use el validador
window.modificar = modificar;
window.modificarContra = modificarContra;