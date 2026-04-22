/**
 * Notificaciones.js
 * Utilidad centralizada para manejar notificaciones.
 */

const Notificaciones = {
    mostrarToast: function(tipo, titulo, mensaje) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1100';
            document.body.appendChild(toastContainer);
        }

        // 1. Mapeo de configuración usando Bootstrap Icons (bi) y colores de Bootstrap
        const config = {
            'success': { icon: 'bi-check-circle-fill', color: 'success' },
            'error':   { icon: 'bi-exclamation-octagon-fill', color: 'danger' },
            'warning': { icon: 'bi-exclamation-triangle-fill', color: 'warning' },
            'info':    { icon: 'bi-info-circle-fill', color: 'primary' }
        };
        // Si mandan un tipo raro, por defecto usamos info
        const conf = config[tipo] || config['info'];

        let toastEl = document.createElement('div');
        
        // 2. Estilo base del Toast: Fondo blanco, sombra suave y borde lateral de color
        toastEl.className = `toast bg-white border-0 border-start border-4 border-${conf.color} shadow-sm`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        // 3. Estructura interna usando Flexbox para separar icono y texto (¡adiós texto desalineado!)
        toastEl.innerHTML = `
            <div class="toast-body d-flex align-items-start p-3">
                <i class="bi ${conf.icon} text-${conf.color} fs-4 me-3" style="line-height: 1.2;"></i>
                
                <div class="flex-grow-1">
                    <strong class="d-block text-dark mb-1" style="font-size: 1.05rem;">${titulo}</strong>
                    <span class="text-secondary">${mensaje}</span>
                </div>
                
                <button type="button" class="btn-close ms-2 mt-1" data-bs-dismiss="toast" aria-label="Cerrar"></button>
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

    resaltarEnTabulator: function(tabla, campoBusqueda = null) {
        // Quitamos la lectura de la URL aquí afuera.
        // Dejaremos que la tabla la lea solo cuando termine de cargar.

        tabla.on("dataLoaded", function() {
            setTimeout(() => {
                // Volvemos a consultar la URL en este exacto momento.
                let idBuscar = Notificaciones.obtenerIdBusqueda();
                
                // Si la URL está limpia (porque el Select ya la consumió), abortamos en paz sin dar error
                if (!idBuscar) return;

                let filas = tabla.getRows();
                let filaEncontrada = filas.find(fila => {
                    let data = fila.getData();
                    
                    // 1. SI HAY COLUMNA EXPLÍCITA:
                    if (campoBusqueda) {
                        let valor = data[campoBusqueda];
                        if (typeof valor === 'string' && valor.includes(',')) {
                            return valor.split(',').includes(String(idBuscar));
                        }
                        return String(valor) === String(idBuscar);
                    } 
                    // 2. AUTO-DETECCIÓN INTELIGENTE:
                    else {
                        let clavePrimaria = Object.keys(data).find(key => key.startsWith('id_'));
                        if (clavePrimaria) {
                            return String(data[clavePrimaria]) === String(idBuscar);
                        }
                        return false;
                    }
                });

                if (filaEncontrada) {
                    let elementoDOM = filaEncontrada.getElement();
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

        // this.inyectarEstilos();

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