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

// Botones de edición/cancelación con transición suave
document.getElementById('boton_modificar')?.addEventListener('click', () => {
    // Pasar datos al formulario (usamos el ID del correo de la columna derecha)
    document.getElementById('nombre').value = document.getElementById('p_nombre').textContent;
    document.getElementById('apellido').value = document.getElementById('p_apellido').textContent;
    document.getElementById('correo').value = document.getElementById('p_correo').textContent;

    const bodyPerfil = document.getElementById('body_perfil');
    const formPerfil = document.getElementById('form_perfil');

    // Intercambiar visibilidad
    bodyPerfil.setAttribute('hidden', '');
    formPerfil.removeAttribute('hidden');
    
    // Aplicar animación al formulario
    formPerfil.classList.remove('animacion-aparecer');
    void formPerfil.offsetWidth; // Truco para reiniciar la animación
    formPerfil.classList.add('animacion-aparecer');

    document.getElementById('boton_modificar').setAttribute('disabled', '');
});

document.getElementById('boton_cancelar')?.addEventListener('click', () => {
    // Limpiar clases de validación
    document.querySelectorAll('.is-valid, .is-invalid').forEach(input => input.classList.remove('is-valid', 'is-invalid'));
    
    const bodyPerfil = document.getElementById('body_perfil');
    const formPerfil = document.getElementById('form_perfil');

    // Intercambiar visibilidad
    formPerfil.setAttribute('hidden', '');
    bodyPerfil.removeAttribute('hidden');
    
    // Aplicar animación a la vista de perfil
    bodyPerfil.classList.remove('animacion-aparecer');
    void bodyPerfil.offsetWidth; // Truco para reiniciar la animación
    bodyPerfil.classList.add('animacion-aparecer');

    document.getElementById('boton_modificar').removeAttribute('disabled');
});

// ============================================
// FUNCIONES PRINCIPALES
// ============================================

async function llenarCardUsuario() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_perfil_usuario');

    // Mantenemos el 'false' para que sea una petición en silencio (sin modal de carga global)
    const respuesta = await Peticiones.enviar(formData, '', false);
    Validador.procesarRespuesta(respuesta, (respuestaServidor) => {
        const usuario = respuestaServidor.datos;
        const [claseBadge, claseIcono] = definirColorBadge(usuario.nombre_rol);

        // --- LÓGICA DE AVATAR VISUAL (INICIALES) ---
        const inicialNombre = usuario.nombre_usuario.charAt(0).toUpperCase();
        const inicialApellido = usuario.apellido.charAt(0).toUpperCase();
        const iniciales = `${inicialNombre}${inicialApellido}`;
        
        const contenedorAvatar = document.getElementById('contenedor_avatar');
        contenedorAvatar.classList.remove('skeleton'); // Quitamos la animación al avatar
        contenedorAvatar.innerHTML = iniciales;
        
        const claseColorFondo = claseBadge.split(' ')[1]; 
        contenedorAvatar.classList.remove('bg-primary'); 
        contenedorAvatar.classList.add(claseColorFondo);
        if(claseColorFondo === 'bg-warning' || claseColorFondo === 'bg-info') {
            contenedorAvatar.classList.replace('text-white', 'text-dark');
        }
        // Inyectamos los datos y borramos los esqueletos
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

        // Habilitar botón y cambiar texto
        document.getElementById('boton_modificar').removeAttribute('disabled');
        document.getElementById('boton_modificar').innerHTML = '<i class="bi bi-pencil me-1"></i> Modificar';

        // --- ANIMACIÓN DE ENTRADA (FADE IN) ---
        const elementosAAnimar = [
            'contenedor_avatar', 
            'titulo_nombre', 
            'spam_rol', 
            'ultimo_acceso', 
            'p_nombre', 
            'p_apellido', 
            'p_correo',
        ];
        
        elementosAAnimar.forEach(id => {
            let el = document.getElementById(id);
            if(el) {
                el.classList.remove('animacion-aparecer');
                void el.offsetWidth; // Truco de JS para forzar reinicio de la animación
                el.classList.add('animacion-aparecer');
            }
        });
    });
}

function llenarTablaNotificaciones() {
    // Encontrar el contenedor de la tabla
    const contenedor = document.querySelector(".tabla-sistema-haydee");
    if (!contenedor) return;

    // Formateadores Visuales
    const formatoLeido = (cell) => {
        const leido = cell.getValue();
        return `<span class="${leido == 1 ? 'badge bg-primary' : 'badge bg-warning text-dark'}">${leido == 1 ? 'Sí' : 'No'}</span>`;
    };

    const formatoBotones = (cell) => {
        return `<button data-tooltip="true" class="btn btn-sm btn-primary ver-notificacion" title="Ver Notificación" type="button">
                    <i class="bi bi-eye"></i>
                </button>`;
    };

    // Definición de Columnas
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
                    const data = cell.getData();
                    const url = `?pagina=${data.tabla_origen}&accion=inicio&buscar=${data.id_registro_origen}`;
                    window.location.href = url;
                }
            }
        }
    ];

    // Parámetros a enviar a PHP
    const opcionesExtra = {
        parametrosExtra: { operacion: 'consultar_mis_notificaciones' }
    };

    let mostrarCarga = false;

    // Inicialización de Tabulator
    tabla_notificaciones = Tablas.cargarTabulador(contenedor.id, "", columnas, opcionesExtra, mostrarCarga);

    // Activamos el elemento visual si existe
    document.getElementById('notificaciones')?.removeAttribute('disabled');

    // Buscador global dinámico
    Tablas.inicializarBuscadorGlobal(tabla_notificaciones, "busqueda_global", columnas);
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
    Validador.procesarRespuesta(respuesta, () => {
        llenarCardUsuario(); // Recargar datos
        // Actualizar nombre en el botón de la barra superior
        const nombreUsuario = document.getElementById('nombre_usuario_sesion');
        if (nombreUsuario) {
            const nuevoNombre = document.getElementById('nombre').value + " " + document.getElementById('apellido').value;
            // botonUsuario.textContent = botonUsuario.textContent.replace(/\s\S+$/, ' ' + nuevoNombre);
            
            nombreUsuario.textContent = nuevoNombre;
        }

        document.getElementById('form_perfil').setAttribute('hidden', '');

        const bodyPerfil = document.getElementById('body_perfil');
        bodyPerfil.removeAttribute('hidden');

        // Disparamos la animación
        bodyPerfil.classList.remove('animacion-aparecer');
        void bodyPerfil.offsetWidth;
        bodyPerfil.classList.add('animacion-aparecer');

        document.getElementById('boton_modificar').removeAttribute('disabled');
    });
}

async function modificarContra() {
    const formData = new FormData();
    formData.append('contra', document.getElementById('contra').value);
    formData.append('correo', document.getElementById('p_correo').textContent);
    formData.append('operacion', 'cambiar_contrasenia');

    const respuesta = await Peticiones.enviar(formData);
    Validador.procesarRespuesta(respuesta, () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modal_contra'));
        modal?.hide();
    });
}

// Exponer funciones para que las use el validador
window.modificar = modificar;
window.modificarContra = modificarContra;