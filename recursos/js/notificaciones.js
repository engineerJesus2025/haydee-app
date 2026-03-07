document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------
    // Funciones auxiliares
    // ------------------------------------------------------------
    /**
     * Marca una notificación como leída en el servidor.
     * @param {string} id - ID de la notificación
     * @returns {Promise<Object>} - Respuesta del servidor
     */
    async function marcarComoLeida(id) {
        const datos = new FormData();
        datos.append('operacion', 'marcar_como_leido');
        datos.append('id', id);
        const url = '?pagina=notificaciones&accion=marcar_como_leido';
        return await Utilidades.query(datos, false, url);
    }

    /**
     * Elimina visualmente un elemento de notificación y actualiza el contador.
     * @param {HTMLElement} itemContainer - El elemento <li> que contiene la notificación
     */
    function eliminarItemYActualizarContador(itemContainer) {
        if (!itemContainer) return;

        // Eliminar el divisor siguiente (si existe)
        const division = itemContainer.nextElementSibling;
        if (division?.classList.contains('dropdown-divider')) {
            division.remove();
        }

        // Eliminar el contenedor de la notificación
        itemContainer.remove();

        // Actualizar contador general
        const countLabel = document.getElementById('count-label');
        if (!countLabel) return;

        const botonesRestantes = document.querySelectorAll('#lista-notificaciones-items .notif-remove-btn');
        const total = botonesRestantes.length;

        if (total > 0) {
            countLabel.textContent = total > 99 ? '+99' : total;
            countLabel.classList.remove('d-none');
        } else {
            countLabel.textContent = '0';
            countLabel.classList.add('d-none');
            // Mostrar mensaje de "no hay notificaciones"
            const listaItems = document.getElementById('lista-notificaciones-items');
            if (listaItems) {
                listaItems.innerHTML = `
                    <li class="px-3 py-4 text-center text-muted" id="no-hay-notificaciones">
                        <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                        No hay notificaciones nuevas
                    </li>
                `;
            }
        }
    }

    // ------------------------------------------------------------
    // Evento para botones de eliminar (X)
    // ------------------------------------------------------------
    document.querySelectorAll('#lista-notificaciones-items .notif-remove-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const id = button.dataset.id;
            if (!id) return;

            const resultado = await marcarComoLeida(id);
            if (!resultado.estatus) {
                Utilidades.mensaje('error', 'Atención', resultado.mensaje || 'Error al procesar la notificación.');
                return;
            }

            const itemContainer = button.closest('li');
            eliminarItemYActualizarContador(itemContainer);
        });
    });

    // ------------------------------------------------------------
    // Evento para enlaces de notificación (ir al módulo)
    // ------------------------------------------------------------
    document.querySelectorAll('#lista-notificaciones-items .notif-link').forEach(enlace => {
        enlace.addEventListener('click', async (e) => {
            e.preventDefault(); // Detenemos la navegación inmediata
            e.stopPropagation();

            const itemContainer = enlace.closest('li');
            const botonEliminar = itemContainer?.querySelector('.notif-remove-btn');
            const id = botonEliminar?.dataset.id;

            if (!id) {
                // Si no hay ID (caso raro), navegamos directamente
                window.location.href = enlace.href;
                return;
            }

            // Marcar como leída en el servidor
            const resultado = await marcarComoLeida(id);
            if (!resultado.estatus) {
                Utilidades.mensaje('error', 'Atención', resultado.mensaje || 'Error al procesar la notificación.');
                // Aún así navegamos
            }

            // Eliminar visualmente la notificación
            eliminarItemYActualizarContador(itemContainer);

            // Finalmente, navegar a la URL destino
            window.location.href = enlace.href;
        });
    });

    // ------------------------------------------------------------
    // Evento para "Marcar todas como leídas"
    // ------------------------------------------------------------
    const botonMarcarTodas = document.getElementById('marcar-todas-leidas');
    if (botonMarcarTodas) {
        botonMarcarTodas.addEventListener('click', async (e) => {
            e.preventDefault();

            const countLabel = document.getElementById('count-label');
            if (!countLabel || countLabel.classList.contains('d-none') || countLabel.textContent === '0') {
                return; // No hay notificaciones
            }

            const datos = new FormData();
            datos.append('operacion', 'marcar_todas_leidas');
            const url = '?pagina=notificaciones&accion=marcar_todas_leidas';

            const resultado = await Utilidades.query(datos, false, url);

            if (resultado?.estatus) {
                // Vaciar la lista
                const listaItems = document.getElementById('lista-notificaciones-items');
                if (listaItems) {
                    listaItems.innerHTML = `
                        <li class="px-3 py-4 text-center text-muted" id="no-hay-notificaciones">
                            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                            No hay notificaciones nuevas
                        </li>
                    `;
                }
                countLabel.textContent = '0';
                countLabel.classList.add('d-none');
            } else {
                Utilidades.mensaje('error', 'Atención', resultado?.mensaje || 'Hubo un error al marcar las notificaciones.');
            }
        });
    }

    
});