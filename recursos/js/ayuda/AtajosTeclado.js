/**
 * AtajosTeclado.js
 * Propósito: Centralizar la gestión de atajos de teclado globales para CondoHaydee.
 */

const AtajosTeclado = {
    tablaActual: null,

    // Método para recibir la instancia desde Tablas.js
    setTablaActual(instanciaTabulator) {
        this.tablaActual = instanciaTabulator;
    },

    // La clave es la tecla y el valor es la función a ejecutar.
    mapaAcciones: {
        
        // Focar en Búsqueda (b)
        "b": function() {
            const inputBusqueda = document.getElementById("busqueda_global");
            if (inputBusqueda) {
                inputBusqueda.focus();
                inputBusqueda.select(); 
            }
        },

        // Nuevo Registro (n)
        "n": function() {
            const btnNuevo = document.querySelector("#boton_nuevo_registro");
            if (btnNuevo) {
                btnNuevo.click();
            }
        },

        // Refrescar Tabla (r)
        "r": function() {
            // Nota para el futuro. Incluire un botón de refrescar general la tabla
            if (AtajosTeclado.tablaActual) {
                if (typeof Notificaciones !== 'undefined') {
                    Notificaciones.mostrarToast('info', 'Actualizando', 'Recargando datos de la tabla...');
                }

                // Tabulator usa setData() para volver a consultar la URL AJAX
                AtajosTeclado.tablaActual.setData(); 
            }
        },

        // Cerrar Modales y limpiar búsqueda (Escape)
        "escape": function() {
            const modalAbierto = document.querySelector('.modal.show');
            if (modalAbierto) {
                // Si hay un modal, lo cerramos
                bootstrap.Modal.getInstance(modalAbierto)?.hide();
            } else {    
                // Si no hay modal, verificamos el input de búsqueda
                const inputBusqueda = document.getElementById("busqueda_global");
                
                // Solo actuamos si el input existe y tiene algo escrito
                if (inputBusqueda && inputBusqueda.value.trim() !== "") {
                    const btnLimpiar = document.getElementById("btn_limpiar_busqueda");
                    
                    if (btnLimpiar) {
                        btnLimpiar.click();
                    } else {
                        inputBusqueda.value = "";
                        inputBusqueda.dispatchEvent(new Event("input")); // Avisa a Tabulator
                    }
                    
                    // Quitamos el foco del input
                    inputBusqueda.blur(); 
                }
            }
        }
    },

    /**
     * Inicializa el escuchador global de eventos de teclado.
     */
    inicializar() {
        document.addEventListener("keydown", (event) => {
            const elementoEnfocado = document.activeElement;
            const esCampoTexto = elementoEnfocado.tagName === "INPUT" || 
                                elementoEnfocado.tagName === "TEXTAREA" || 
                                elementoEnfocado.isContentEditable;

            if (event.key === "Enter" && esCampoTexto && elementoEnfocado.tagName !== "TEXTAREA") {
                // Prevenimos que el Enter haga cosas raras (como recargar la página si está en un form form)
                event.preventDefault(); 

                // Buscamos si estamos dentro de un modal
                const modal = elementoEnfocado.closest('.modal');
                if (modal) {
                    // Buscamos el botón de guardar. 
                    const btnGuardar = modal.querySelector('#boton_formulario'); 
                    if (btnGuardar) {
                        btnGuardar.click();
                    }
                }
                return; // Salimos para que no evalúe los otros atajos
            }

            // Si es un campo de texto (y no fue Enter), ignoramos los demás atajos (b, n, r)
            if (esCampoTexto) {
                return; 
            }

            // Convertimos la tecla a minúscula (Ej: 'B' se vuelve 'b', 'Escape' se vuelve 'escape')
            const teclaNormalizada = event.key.toLowerCase();

            // Verificar si tenemos una acción definida
            if (this.mapaAcciones[teclaNormalizada]) {
                event.preventDefault(); 
                this.mapaAcciones[teclaNormalizada](); // Ejecutamos la acción
            }
        });

        // Auto-focus global para cualquier modal del sistema
        document.addEventListener('shown.bs.modal', (event) => {
            const modal = event.target;
            // Selecciona el primer input, select o textarea que no esté bloqueado u oculto
            const selector = 'input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]):not([readonly])';
            const primerElemento = modal.querySelector(selector);
            
            if (primerElemento) {
                primerElemento.focus();
            }
        });
    }
};

// Autoinicialización
document.addEventListener("DOMContentLoaded", function() {
    AtajosTeclado.inicializar();
});