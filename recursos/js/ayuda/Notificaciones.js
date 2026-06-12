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

        // Mapeo de configuración usando Bootstrap Icons (bi) y colores de Bootstrap
        const config = {
            'success': { icon: 'bi-check-circle-fill', color: 'success' },
            'error':   { icon: 'bi-exclamation-octagon-fill', color: 'danger' },
            'warning': { icon: 'bi-exclamation-triangle-fill', color: 'warning' },
            'info':    { icon: 'bi-info-circle-fill', color: 'primary' }
        };
        // Si mandan un tipo raro, por defecto usamos info
        const conf = config[tipo] || config['info'];

        let toastEl = document.createElement('div');
        
        // Estilo base del Toast: Fondo blanco, sombra suave y borde lateral de color
        toastEl.className = `toast bg-white border-0 border-start border-4 border-${conf.color} shadow-sm`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        // Estructura interna usando Flexbox para separar icono y texto (¡adiós texto desalineado!)
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
        // Escuchamos renderComplete para asegurarnos de que el HTML ya exista en pantalla
        tabla.on("renderComplete", function() {
            let idBuscar = Notificaciones.obtenerIdBusqueda();
            
            // Si la URL ya fue limpiada o no hay ID, abortamos pacíficamente
            if (!idBuscar) return;

            // Obtenemos todas las filas activas (las que pasaron los filtros actuales)
            let filasActivas = tabla.getRows("active");
            
            // Buscamos el índice de la fila deseada en todo el universo de datos locales
            let indiceFila = filasActivas.findIndex(fila => {
                let data = fila.getData();
                if (campoBusqueda) {
                    let valor = data[campoBusqueda];
                    if (typeof valor === 'string' && valor.includes(',')) {
                        return valor.split(',').includes(String(idBuscar));
                    }
                    return String(valor) === String(idBuscar);
                } else {
                    let clavePrimaria = Object.keys(data).find(key => key.startsWith('id_'));
                    return clavePrimaria ? String(data[clavePrimaria]) === String(idBuscar) : false;
                }
            });

            // Si el registro existe en la tabla
            if (indiceFila !== -1) {
                let filaEncontrada = filasActivas[indiceFila];
                let pageSize = tabla.getPageSize() || 10;
                
                // Cálculo matemático de la página destino (1-based)
                let paginaDestino = Math.floor(indiceFila / pageSize) + 1;
                let paginaActual = tabla.getPage();

                // CONTROL DE FLUJO: Si no estamos en la página correcta, cambiamos de página y salimos
                if (paginaActual !== paginaDestino) {
                    tabla.setPage(paginaDestino);
                    return; // El cambio de página disparará un nuevo 'renderComplete' automáticamente
                }

                // Si ya estamos en la página correcta, procedemos con el enfoque visual
                let elementoDOM = filaEncontrada.getElement();
                if (elementoDOM) {
                    // Aplicar clases de animación
                    elementoDOM.classList.add('table-primary', 'border-primary', 'resaltar-pulso-azul');
                    
                    setTimeout(() => {
                        // Desplazamiento nativo de la ventana del navegador (100% infalible)
                        elementoDOM.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);

                    // Limpiar el color tras 5 segundos
                    setTimeout(() => {
                        elementoDOM.classList.remove('table-primary', 'border-primary', 'resaltar-pulso-azul');
                    }, 5000);
                }

                // ¡CRÍTICO! Limpiamos la URL para evitar bucles en futuros renders
                Notificaciones.limpiarUrl();

            } else {
                // Si la tabla terminó de procesar y tiene datos, pero el ID no está
                if (filasActivas.length > 0) {
                    Notificaciones.mostrarToast('error', 'No encontrado', 'El registro notificado ya no se encuentra en el sistema.');
                    Notificaciones.limpiarUrl();
                }
            }
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