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
    }
};