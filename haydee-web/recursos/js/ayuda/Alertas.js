const Alertas = {
    /**
     * CENTRALIZACIÓN DEL FALLBACK
     */
    _ejecutarFallback(titulo, mensaje, accion = null) {
        console.warn("SweetAlert2 no está cargado. Usando métodos nativos.");
        const resultado = confirm(`${titulo}\n\n${mensaje}`);
        if (resultado && typeof accion === 'function') accion();
        return Promise.resolve({ isConfirmed: resultado });
    },

    /**
     * CONFIGURACIÓN BASE ESTÁNDAR
     */
    _obtenerConfiguracionBase() {
        return {
            buttonsStyling: false,
            background: 'var(--ch-card-bg)', 
            color: 'var(--ch-color-titulos)',
            scrollbarPadding: false,
            heightAuto: false,
            customClass: {
                popup: 'vp-border-color shadow-lg rounded-4 border', 
                title: 'fw-bold',
                htmlContainer: 'text-muted',
                confirmButton: 'btn btn-soft-success mx-2', 
                cancelButton: 'btn btn-soft-secondary mx-2',
                denyButton: 'btn text-white mx-2' 
            }
        };
    },

    /**
     * Muestra una alerta estándar en pantalla.
     * tipo - 'success', 'error', 'warning', 'info'
     */
    mostrar(tipo, titulo, mensaje, tiempo = 4000) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, mensaje);

        const config = {
            ...this._obtenerConfiguracionBase(),
            icon: tipo,
            title: titulo,
            text: mensaje,
            timer: tiempo,
            showConfirmButton: true,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Aceptar'
        };

        // Asignación de botones soft según el tipo
        if (tipo === 'error') config.customClass.confirmButton = 'btn btn-soft-danger mx-2';
        if (tipo === 'info') config.customClass.confirmButton = 'btn btn-soft-info mx-2';
        if (tipo === 'warning') config.customClass.confirmButton = 'btn btn-soft-warning mx-2'; // Bootstrap nativo para warning

        Swal.fire(config);
    },

    /**
     * Muestra una alerta bloqueante y ejecuta una acción al confirmarla.
     */
    mostrarConAccion(tipo, titulo, mensaje, textoBoton, funcionAccion) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, mensaje, funcionAccion);

        const config = {
            ...this._obtenerConfiguracionBase(),
            icon: tipo,
            title: titulo,
            text: mensaje,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: `<i class="bi bi-arrow-right-circle me-1"></i> ${textoBoton}`,
        };

        if (tipo === 'error') config.customClass.confirmButton = 'btn btn-soft-danger mx-2';
        else if (tipo === 'warning') config.customClass.confirmButton = 'btn btn-primary mx-2';

        Swal.fire(config).then((result) => {
            if (result.isConfirmed && typeof funcionAccion === 'function') {
                funcionAccion();
            }
        });
    },

    /**
     * Muestra un modal de confirmación con soporte para HTML.
     */
    pedirConfirmacion(titulo, htmlContenido, icono = 'question', textoConfirmar = 'Sí, continuar', textoCancelar = 'Cancelar') {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, "Vista previa de confirmación");

        return Swal.fire({
            ...this._obtenerConfiguracionBase(),
            title: titulo,
            html: htmlContenido,
            icon: icono, // Usa 'question' por defecto ahora
            showCancelButton: true,
            confirmButtonText: `<i class="bi bi-check-lg me-1"></i> ${textoConfirmar}`,
            cancelButtonText: `<i class="bi bi-x-lg me-1"></i> ${textoCancelar}`
        });
    },

    /**
     * Muestra un modal de confirmación simple.
     */
    confirmarAccion(titulo, mensaje, tipo, funcionConfirmar) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, mensaje, funcionConfirmar);

        const config = {
            ...this._obtenerConfiguracionBase(),
            title: titulo,
            text: mensaje,
            icon: tipo === 'warning' ? 'question' : tipo, 
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Sí',
            cancelButtonText: '<i class="bi bi-x-lg me-1"></i> No'
        };

        // usamos rojo
        if (tipo === 'error' || titulo.toLowerCase().includes('eliminar') || titulo.toLowerCase().includes('anular')) {
            config.icon = 'warning';
            config.customClass.confirmButton = 'btn btn-soft-danger mx-2';
            config.confirmButtonText = '<i class="bi bi-trash3 me-1"></i> Sí, eliminar';
        }

        Swal.fire(config).then((result) => {
            if (result.isConfirmed && typeof funcionConfirmar === 'function') {
                funcionConfirmar();
            }
        });
    },

    /**
     * Muestra el desglose de vista previa estilo bancario antes de guardar. 
     */
    confirmarConVistaPrevia(titulo, htmlContenido, funcionConfirmar) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, 'Vista previa de pago', funcionConfirmar);

        Swal.fire({
            ...this._obtenerConfiguracionBase(),
            title: titulo,
            html: htmlContenido,
            icon: 'question', 
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-floppy me-1"></i> Sí, guardar',
            cancelButtonText: '<i class="bi bi-pencil-square me-1"></i> No',
            customClass: {
                ...this._obtenerConfiguracionBase().customClass,
                popup: 'vp-border-color shadow-lg rounded-4 border modal-lg', 
            }
        }).then((result) => {
            if (result.isConfirmed && typeof funcionConfirmar === 'function') {
                funcionConfirmar();
            }
        });
    },

    /**
     * Nueva alerta para gestionar reportes o consultas vacías.
     */
    mostrarSinDatos(titulo = "Sin resultados", mensaje = "No se encontraron datos con los filtros actuales.") {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, mensaje);

        Swal.fire({
            ...this._obtenerConfiguracionBase(),
            icon: 'info',
            title: titulo,
            text: mensaje,
            confirmButtonText: '<i class="bi bi-hand-thumbs-up me-1"></i> Entendido',
            customClass: {
                ...this._obtenerConfiguracionBase().customClass,
                confirmButton: 'btn btn-soft-info mx-2'
            }
        });
    },

    /**
     * Alerta de éxito que ofrece de manera explícita y opcional la generación de un comprobante.
     */
    mostrarExitoConReciboOpcional(titulo, mensaje, funcionRecibo) {
        if (typeof Swal === 'undefined') {
            alert(`${titulo}: ${mensaje}`);
            return;
        }

        const config = {
            ...this._obtenerConfiguracionBase(),
            icon: 'success',
            title: titulo,
            text: mensaje,
            showDenyButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Cerrar',
            denyButtonText: '<i class="bi bi-file-earmark-pdf me-1"></i> Generar Recibo',
            customClass: {
                ...this._obtenerConfiguracionBase().customClass,
                confirmButton: 'btn btn-soft-secondary mx-2',
                denyButton: 'btn text-white mx-2'
            },
            didOpen: (modal) => {
                const btnDeny = modal.querySelector('.swal2-deny');
                if (btnDeny) btnDeny.style.backgroundColor = '#3939a9';
            }
        };

        Swal.fire(config).then((result) => {
            // Si el usuario presiona "Generar Recibo" (Deny)
            if (result.isDenied && typeof funcionRecibo === 'function') {
                funcionRecibo();
            }
        });
    },

    /**
     * Muestra un asistente interactivo con inyección de HTML personalizado.
     */
    mostrarAsistenteInteractivo(titulo, subtitulo, htmlContenido, funcionConfirmar) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(titulo, subtitulo, funcionConfirmar);

        Swal.fire({
            ...this._obtenerConfiguracionBase(),
            title: `<div class="fs-4 mb-2"><i class="bi bi-magic text-warning me-2"></i>${titulo}</div>`,
            html: `
                <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.4;">${subtitulo}</p>
                <div class="text-start">
                    ${htmlContenido}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-calculator me-2"></i> Aplicar Ajuste',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancelar', // <-- Ícono añadido
            customClass: {
                ...this._obtenerConfiguracionBase().customClass,
                popup: 'vp-border-color shadow-lg rounded-4 border',
                confirmButton: 'btn btn-soft-warning mx-2 px-4 fw-bold shadow-sm', 
                cancelButton: 'btn btn-soft-secondary mx-2 px-4'
            }
        }).then((result) => {
            if (result.isConfirmed && typeof funcionConfirmar === 'function') {
                funcionConfirmar();
            }
        });
    },

    /**
     * Muestra una alerta basada en el JSON del servidor cuando hay errores
     */
    mostrarErrorEnriquecido(datos) {
        if (typeof Swal === 'undefined') return this._ejecutarFallback(datos.titulo || 'Error', datos.mensaje);

        const mapaIconos = {
            'server-crash': 'bi-hdd-network',
            'alert-triangle': 'bi-exclamation-triangle',
            'info-circle': 'bi-info-circle',
            'shield-off': 'bi-shield-slash',
            'database-fail': 'bi-database-fill-x',
            'alert-octagon': 'bi-x-octagon-fill'
        };

        const mapaBotones = {
            'validacion': 'btn-soft-warning',
            'negocio': 'btn-soft-info',
            'seguridad': 'btn-soft-danger',
            'bd': 'btn-soft-danger',
            'critico': 'btn-dark',
            'sistema': 'btn-soft-secondary'
        };

        const iconoBi = mapaIconos[datos.icono] || 'bi-bug';
        const colorBase = datos.color || '#6c757d'; 
        const claseBoton = mapaBotones[datos.tipo] || 'btn-soft-secondary';
        
        let textoHtml = datos.mensaje || 'Ocurrió un error inesperado al procesar la solicitud.';
        if (datos.ref) {
            textoHtml += `
                <div class="text-center mt-3" style="font-size: 0.85em; color: #6c757d;">
                    Ref: <b class="font-monospace text-reset">${datos.ref}</b>
                </div>
            `;
        }

        Swal.fire({
            ...this._obtenerConfiguracionBase(),
            iconHtml: `<i class="bi ${iconoBi}" style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 0.85em;"></i>`,
            title: datos.titulo || 'Error del Sistema',
            html: textoHtml,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Aceptar',
            customClass: {
                ...this._obtenerConfiguracionBase().customClass,
                confirmButton: `btn ${claseBoton} mx-2 shadow-sm`
            },
            didOpen: (modal) => {
                const iconContainer = modal.querySelector('.swal2-icon');
                if (iconContainer) {
                    iconContainer.style.color = colorBase;
                    iconContainer.style.borderColor = colorBase; 
                }
            }
        });
    },
};