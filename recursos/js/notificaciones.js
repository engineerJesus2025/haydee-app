document.addEventListener('DOMContentLoaded', () => {
    
    // Función para comunicarse con el servidor
    async function marcarComoLeida(id) {
        const datos = new FormData();
        datos.append('operacion', 'marcar_como_leido');
        datos.append('id', id);
        const url = '?pagina=notificaciones&accion=marcar_como_leido';
        return await Peticiones.enviar(datos, url, false);
    }

    // Función para quitar el elemento visualmente
    function eliminarItemYActualizarContador(itemContainer) {
        if (!itemContainer) return;
        itemContainer.remove();

        const countLabel = document.getElementById('count-label');
        const btnMarcarTodas = document.getElementById('marcar-todas-leidas'); // Seleccionamos el enlace
        
        if (!countLabel) return;

        // Contamos cuántas notificaciones quedan visualmente
        const notificacionesRestantes = document.querySelectorAll('.notif-item');
        const count = notificacionesRestantes.length;

        if (count > 0) {
            countLabel.textContent = count > 9 ? '9+' : count;
        } else {
            countLabel.textContent = '0';
            countLabel.classList.add('d-none');
            
            // Ocultamos el enlace "Marcar todas como leídas"
            if (btnMarcarTodas) {
                btnMarcarTodas.classList.add('d-none');
            }
            
            const listaItems = document.getElementById('lista-notificaciones-items');
            if (listaItems) {
                // Inyectamos el diseño vacío mejorado
                listaItems.innerHTML = `
                    <div class="notif-empty py-5 text-center" id="no-hay-notificaciones">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-bell-slash fs-3 text-secondary"></i>
                        </div>
                        <span class="d-block text-secondary fw-medium">No hay notificaciones nuevas</span>
                    </div>
                `;
            }
        }
    }

    // DELEGACIÓN DE EVENTOS EN LA LISTA
    const listaNotificaciones = document.getElementById('lista-notificaciones-items');
    if (listaNotificaciones) {
        listaNotificaciones.addEventListener('click', async (e) => {
            
            // CASO 1: Clic en la "X" para eliminar la notificación
            const botonMarcar = e.target.closest('.notif-remove-btn');
            if (botonMarcar) {
                e.preventDefault();
                e.stopPropagation();

                const idNotificacion = botonMarcar.getAttribute('data-id');
                if (!idNotificacion) return;

                botonMarcar.disabled = true;
                const resultado = await marcarComoLeida(idNotificacion);

                if (resultado?.estatus) {
                    eliminarItemYActualizarContador(botonMarcar.closest('.notif-item'));
                } else {
                    botonMarcar.disabled = false;
                    Alertas.mostrar('error', 'Atención', resultado?.mensaje || 'Error al procesar la solicitud.');
                }
                return; // Terminamos aquí para que no ejecute el caso 2
            }

            // CASO 2: Clic en la notificación (enlace) para leer y redirigir
            const linkNotificacion = e.target.closest('.notif-link');
            if (linkNotificacion) {
                e.preventDefault(); // Evitamos que redirija instantáneamente
                
                // Buscamos el ID en el botón "X" hermano
                const itemContainer = linkNotificacion.closest('.notif-item');
                const idNotificacion = itemContainer.querySelector('.notif-remove-btn').getAttribute('data-id');

                // Opcional: Le bajamos un poco la opacidad para que el usuario sepa que hizo clic
                linkNotificacion.style.opacity = '0.5';

                // AGREGAMOS AWAIT AQUÍ: Esperamos a que el servidor confirme que se marcó como leída
                if (idNotificacion) {
                    await marcarComoLeida(idNotificacion);
                }
                
                // Ahora sí, con la petición terminada, redirigimos a la URL objetivo
                window.location.href = linkNotificacion.href;
            }
        });

        // Cambiar fecha a tiempo relativo
        document.querySelectorAll('.notif-date').forEach(div_fecha => {
            const fecha = div_fecha.dataset.fecha;
            if (fecha) {
                const tiempor_relativo = FormatoFechas.tiempoRelativo(fecha);
                div_fecha.querySelector('.fecha').innerText = tiempor_relativo;
            }
        });
    }

    // MARCAR TODAS COMO LEÍDAS
    const botonMarcarTodas = document.getElementById('marcar-todas-leidas');
    if (botonMarcarTodas) {
        botonMarcarTodas.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const countLabel = document.getElementById('count-label');
            if (!countLabel || countLabel.classList.contains('d-none') || countLabel.textContent === '0') {
                return;
            }

            const datos = new FormData();
            datos.append('operacion', 'marcar_todas_leidas');
            const url = '?pagina=notificaciones&accion=marcar_todas_leidas';

            const resultado = await Peticiones.enviar(datos, url, false);

            if (resultado?.estatus) {
                const listaItems = document.getElementById('lista-notificaciones-items');
                if (listaItems) {
                    listaItems.innerHTML = `
                        <li class="notif-empty" id="no-hay-notificaciones">
                            <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                            <span class="d-block">No hay notificaciones nuevas</span>
                        </li>
                    `;
                }
                countLabel.textContent = '0';
                countLabel.classList.add('d-none');
            } else {
                Alertas.mostrar('error', 'Atención', resultado?.mensaje || 'Hubo un error al marcar las notificaciones.');
            }
        });
    }
});