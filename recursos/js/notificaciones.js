document.addEventListener('DOMContentLoaded', () => {

    // -----------------------------------------------------------------
    // FUNCIÓN AJAX CORREGIDA Y SIMPLIFICADA
    // -----------------------------------------------------------------
    async function query(datos) {
        try {
            // Obtenemos la 'operacion' desde el objeto FormData que recibimos
            const operacion = datos.get('operacion');
            if (!operacion) {
                // Medida de seguridad por si la operación no viene en los datos
                throw new Error("La 'operacion' no fue especificada en los datos.");
            }
            
            // Construimos la URL correcta
            const url = `?pagina=notificaciones_controlador.php&accion=${operacion}`;

            const res = await fetch(url, { method: "POST", body: datos });
            const data = await res.json();
            return data;
        } catch (error) {
            console.error("Error en la petición:", error);
            return { estatus: false, mensaje: "Ha ocurrido un error durante la consulta", error };
        }
    }

    // -----------------------------------------------------------------
    // LÓGICA PARA MARCAR UNA NOTIFICACIÓN (Ahora funciona con la nueva query)
    // -----------------------------------------------------------------
    const notificacionesItems = document.querySelectorAll("#notificaciones-list .notification-item");

    notificacionesItems.forEach(notificacion => {
        notificacion.addEventListener("click", async e => {
            e.preventDefault();
            const datos_consulta = new FormData();
            datos_consulta.append('id', notificacion.dataset.id);
            datos_consulta.append('operacion', "quitar_notificacion");

            // La llamada (con un solo parámetro) ahora es correcta
            const resultado = await query(datos_consulta);

            if (!resultado.estatus) {
                Swal.fire('Atencion', resultado.mensaje || 'Error al procesar la notificación.', 'error');
                return;
            }

            // ... (el resto del código para actualizar la interfaz sigue igual)
            const division = notificacion.nextElementSibling;
            if (division && division.classList.contains("dropdown-divider")) {
                division.remove();
            }
            notificacion.remove();
            const countLabelGeneral = document.getElementById('count-label');
            let count = document.querySelectorAll("#notificaciones-list .notification-item").length;
            if (count > 0) {
                countLabelGeneral.textContent = count > 99 ? '+99' : count;
            } else {
                countLabelGeneral.textContent = '0';
                countLabelGeneral.classList.add('d-none');
                const listaItems = document.getElementById('lista-notificaciones-items');
                if(listaItems){
                    listaItems.innerHTML = `
                        <li class="px-3 py-4 text-center text-muted" id="no-hay-notificaciones">
                            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                            No hay notificaciones nuevas
                        </li>
                    `;
                }
            }
        });
    });

    // -----------------------------------------------------------------
    // LÓGICA PARA MARCAR TODAS COMO LEÍDAS (Ahora funciona con la nueva query)
    // -----------------------------------------------------------------
    const botonMarcarTodas = document.getElementById('marcar-todas-leidas');

    if (botonMarcarTodas) {
        botonMarcarTodas.addEventListener('click', async function(e) {
            e.preventDefault();
            const contador = document.getElementById('count-label');
            if (!contador || contador.classList.contains('d-none') || contador.textContent === '0') {
                return;
            }
            
            const datos = new FormData();
            datos.append('operacion', 'marcar_todas_leidas');

            // La llamada (con un solo parámetro) ahora es correcta
            const resultado = await query(datos);

            if (resultado && resultado.estatus) {
                // ... (el resto del código para actualizar la interfaz sigue igual)
                const listaItems = document.getElementById('lista-notificaciones-items');
                contador.textContent = '0';
                contador.classList.add('d-none');
                if (listaItems) {
                    listaItems.innerHTML = `
                        <li class="px-3 py-4 text-center text-muted" id="no-hay-notificaciones">
                            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                            No hay notificaciones nuevas
                        </li>
                    `;
                }
            } else {
                alert('Hubo un error al marcar las notificaciones.');
            }
        });
    }
});