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
        if (tipo === 'warning') config.customClass.confirmButton = 'btn btn-warning mx-2'; // Bootstrap nativo para warning

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
    }
};