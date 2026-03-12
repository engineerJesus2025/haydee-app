/**
 * Notificaciones.js
 * Utilidad centralizada para manejar notificaciones.
 */

const Notificaciones = {
    // Inyecta los estilos de animación de pulso en tono azul (Primary)
    inyectarEstilos: function() {
        if (!document.getElementById('estilos-notificaciones')) {
            const style = document.createElement('style');
            style.id = 'estilos-notificaciones';
            style.innerHTML = `
                @keyframes pulso-notificacion-azul {
                    0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); } /* Azul de Bootstrap */
                    70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
                }
                .resaltar-pulso-azul {
                    animation: pulso-notificacion-azul 2s infinite !important;
                    background-color: #f0f7ff !important; /* Un fondo azul muy sutil para el select */
                }
            `;
            document.head.appendChild(style);
        }
    },

    mostrarToast: function(tipo, titulo, mensaje) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1055';
            document.body.appendChild(toastContainer);
        }

        let bgClass = tipo === 'error' ? 'text-bg-danger' : (tipo === 'success' ? 'text-bg-success' : 'text-bg-primary');
        let icon = tipo === 'error' ? 'bi-exclamation-octagon' : 'bi-info-circle';

        let toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center border-0 ${bgClass}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i> <strong>${titulo}</strong>: ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        `;

        toastContainer.appendChild(toastEl);
        new bootstrap.Toast(toastEl, { delay: 5000 }).show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    },

    obtenerIdBusqueda: function() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('buscar');
    },

    limpiarUrl: function() {
        const url = new URL(window.location);
        url.searchParams.delete('buscar');
        window.history.replaceState({}, document.title, url);
    },

    resaltarEnTabulator: function(tabla, campoBusqueda) {
        let idBuscar = this.obtenerIdBusqueda();
        if (!idBuscar) return;

        this.inyectarEstilos();

        tabla.on("dataLoaded", function() {
            setTimeout(() => {
                let filas = tabla.getRows();
                let filaEncontrada = filas.find(fila => {
                    let valor = fila.getData()[campoBusqueda];
                    if (typeof valor === 'string' && valor.includes(',')) {
                        return valor.split(',').includes(String(idBuscar));
                    }
                    return String(valor) === String(idBuscar);
                });

                if (filaEncontrada) {
                    let elementoDOM = filaEncontrada.getElement();
                    // Cambiamos a table-primary y border-primary
                    elementoDOM.classList.add('table-primary', 'border-primary', 'resaltar-pulso-azul');
                    
                    filaEncontrada.scrollTo().then(() => {
                        elementoDOM.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });

                    setTimeout(() => {
                        elementoDOM.classList.remove('table-primary', 'border-primary', 'resaltar-pulso-azul');
                    }, 5000);

                    Notificaciones.limpiarUrl();
                } else {
                    Notificaciones.mostrarToast('error', 'No encontrado', 'El registro notificado ya no se encuentra en el sistema.');
                    Notificaciones.limpiarUrl();
                }
            }, 300); 
        });
    },

    resaltarEnSelect: function(idSelect) {
        let idBuscar = this.obtenerIdBusqueda();
        if (!idBuscar) return;

        let select = document.getElementById(idSelect);
        if (!select) return;

        this.inyectarEstilos();

        let optionExists = Array.from(select.options).some(opt => opt.value === String(idBuscar));
        
        if (optionExists) {
            select.value = idBuscar;
            select.dispatchEvent(new Event('change'));
            
            // Usamos clases de borde azul en lugar de is-valid
            select.classList.add('border', 'border-2', 'border-primary', 'resaltar-pulso-azul');
            
            select.scrollIntoView({ behavior: 'smooth', block: 'center' });

            setTimeout(() => {
                select.classList.remove('border', 'border-2', 'border-primary', 'resaltar-pulso-azul');
            }, 5000);

            this.limpiarUrl();
        } else {
            this.mostrarToast('error', 'No encontrado', 'La caja notificada no existe o está cerrada.');
            this.limpiarUrl();
        }
    }
};