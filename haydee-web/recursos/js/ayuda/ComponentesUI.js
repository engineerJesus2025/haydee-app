/**
 * ComponentesUI.js
 * Propósito: Centralizar la generación de código HTML repetitivo para elementos de interfaz.
 */
const ComponentesUI = {
    /**
     * Genera el HTML de un "Soft Badge" adaptable al Modo Oscuro/Claro.
     */
    crearSoftBadge(color, icono, texto) {
        const htmlIcono = icono ? `<i class="${icono} me-1"></i> ` : '';
        return `<span class="badge badge-soft-${color} py-2 shadow-sm text-nowrap" style="font-size: .85rem;">
                    ${htmlIcono}${texto}
                </span>`;
    },

    /**
     * Conecta un input de contraseña con una barra de progreso y un texto
     * para mostrar en tiempo real el nivel de seguridad.
     */
    inicializarMedidorContrasena(input, barra, texto) {
        if (!input || !barra || !texto) return;

        input.addEventListener('input', function () {
            const pass = this.value;
            let fortaleza = 0;

            if (pass.length >= 8)            fortaleza += 20; // Longitud mínima
            if (/[a-z]/.test(pass))          fortaleza += 20; // Minúscula
            if (/[A-Z]/.test(pass))          fortaleza += 20; // Mayúscula
            if (/\d/.test(pass))             fortaleza += 20; // Número
            if (/[_.+*$#%&@-]/.test(pass))   fortaleza += 20; // Carácter especial

            barra.style.width = fortaleza + '%';

            if (pass.length === 0) {
                barra.className = 'progress-bar bg-danger';
                texto.textContent = 'Nivel de seguridad: Vacío';
                texto.className = 'fw-medium text-danger d-block mb-3';
            } else if (fortaleza <= 40) {
                barra.className = 'progress-bar bg-danger';
                texto.textContent = 'Nivel de seguridad: Muy Débil';
                texto.className = 'fw-medium text-danger d-block mb-3';
            } else if (fortaleza === 60) {
                barra.className = 'progress-bar bg-warning';
                texto.textContent = 'Nivel de seguridad: Débil';
                texto.className = 'fw-medium text-warning d-block mb-3';
            } else if (fortaleza === 80) {
                barra.className = 'progress-bar bg-info';
                texto.textContent = 'Nivel de seguridad: Buena';
                texto.className = 'fw-medium text-info d-block mb-3';
            } else {
                barra.className = 'progress-bar bg-success';
                texto.textContent = 'Nivel de seguridad: Muy Fuerte';
                texto.className = 'fw-medium text-success d-block mb-3';
            }
        });
    }

};