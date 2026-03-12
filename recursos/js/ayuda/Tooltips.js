/**
 * Tooltips.js (Actualizado)
 */
const Tooltips = {
    inicializarTodos: function (contenedor = document) {
        if (typeof bootstrap === 'undefined') return;
        
        // 1. Buscamos el data-bs-toggle normal Y nuestro nuevo atributo personalizado
        const selectores = '[data-bs-toggle="tooltip"], [data-tooltip="true"]';
        const tooltipTriggerList = contenedor.querySelectorAll(selectores);
        
        tooltipTriggerList.forEach(elemento => {
            // Evita inicializar dos veces
            if (!bootstrap.Tooltip.getInstance(elemento)) {
                new bootstrap.Tooltip(elemento, { trigger: 'hover' });
                
                // 2. SOLUCIÓN AL TOOLTIP PEGADO: Ocultar al hacer clic
                elemento.addEventListener('click', function() {
                    const tooltipInstance = bootstrap.Tooltip.getInstance(this);
                    if (tooltipInstance) {
                        tooltipInstance.hide(); // Fuerza a que se esconda
                    }
                });
            }
        });
    },

    actualizarDinamicamente: function (elemento, nuevoMensaje, posicion = 'top') {
        if (!elemento || typeof bootstrap === 'undefined') return;
        const tooltipPrevio = bootstrap.Tooltip.getInstance(elemento);
        if (tooltipPrevio) tooltipPrevio.dispose();

        elemento.setAttribute('title', nuevoMensaje);
        new bootstrap.Tooltip(elemento, { placement: posicion, trigger: 'hover' });
    }
};

document.addEventListener("DOMContentLoaded",()=>{
    Tooltips.inicializarTodos();
});