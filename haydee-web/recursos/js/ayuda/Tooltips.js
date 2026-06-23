/**
 * Tooltips.js
 */
const Tooltips = {
    inicializarTodos: function (contenedor = document) {
        if (typeof bootstrap === 'undefined') return;
        
        const selectores = '[data-bs-toggle="tooltip"], [data-tooltip="true"]';
        const tooltipTriggerList = contenedor.querySelectorAll(selectores);
        
        // --- Configuración del temporizador (Delay) ---
        const configuracion = {
            trigger: 'hover',
            container: 'body', 
            delay: { "show": 600, "hide": 100 }
        };

        tooltipTriggerList.forEach(elemento => {
            if (!bootstrap.Tooltip.getInstance(elemento)) {
                const instancia = new bootstrap.Tooltip(elemento, configuracion);

                elemento.addEventListener('show.bs.tooltip', () => {
                    // Buscamos TODOS los elementos que tengan tooltips en la página
                    const todos = document.querySelectorAll('[data-bs-toggle="tooltip"], [data-tooltip="true"]');
                    todos.forEach(otro => {
                        if (otro !== elemento) {
                            const instOtro = bootstrap.Tooltip.getInstance(otro);
                            if (instOtro) instOtro.hide(); // Escondemos cualquier otro abierto
                        }
                    });
                });

                // Mantener la limpieza al hacer clic
                elemento.addEventListener('click', function() {
                    instancia.hide();
                    Tooltips.limpiarHuerfanos();
                });
            }
        });
    },

    actualizarDinamicamente: function (elemento, nuevoMensaje, posicion = 'top') {
        if (!elemento || typeof bootstrap === 'undefined') return;
        const tooltipPrevio = bootstrap.Tooltip.getInstance(elemento);
        if (tooltipPrevio) tooltipPrevio.dispose();

        elemento.setAttribute('title', nuevoMensaje);
        new bootstrap.Tooltip(elemento, { 
            placement: posicion, 
            trigger: 'hover',
            delay: { "show": 600, "hide": 100 }
        });
    },

    // --- Función para destruir tooltips pegados en pantalla ---
    limpiarHuerfanos: function() {
        // Busca cualquier elemento HTML de tooltip que haya quedado visible en el body
        const tooltipsPegados = document.querySelectorAll('.tooltip.show');
        tooltipsPegados.forEach(tooltip => tooltip.remove());
    }
};

document.addEventListener("DOMContentLoaded",()=> Tooltips.inicializarTodos());

// Si el usuario hace clic en CUALQUIER lugar de la página, limpia los tooltips pegados
document.addEventListener('click', () => {
    Tooltips.limpiarHuerfanos();
});

document.addEventListener('scroll', () => {
    Tooltips.limpiarHuerfanos();
}, true);