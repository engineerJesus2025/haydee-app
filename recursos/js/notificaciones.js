document.addEventListener('DOMContentLoaded', () => {

    async function query(datos) {
        try {            
            const operacion = datos.get('operacion');
            if (!operacion) {                
                throw new Error("La 'operacion' no fue especificada en los datos.");
            }
            
            const url = `?pagina=notificaciones_controlador.php&accion=${operacion}`;

            const res = await fetch(url, { method: "POST", body: datos });
            const data = await res.json();
            return data;
        } catch (error) {
            console.error("Error en la petición:", error);
            return { estatus: false, mensaje: "Ha ocurrido un error durante la consulta", error };
        }
    }
    
    // --- INICIO DE CAMBIOS ---
    // Antes: const notificacionesItems = document.querySelectorAll("#notificaciones-list .notification-item");
    // Ahora: Buscamos los *nuevos botones* de eliminar
    const removeButtons = document.querySelectorAll("#notificaciones-list .notif-remove-btn");

    removeButtons.forEach(button => {
        // Antes: notificacion.addEventListener("click", ...)
        // Ahora: button.addEventListener("click", ...)
        button.addEventListener("click", async e => {
            e.preventDefault(); // Prevenir la acción por defecto del botón
            e.stopPropagation(); // MUY IMPORTANTE: Evita que el clic se propague al enlace <a> padre

            const datos_consulta = new FormData();
            // Antes: datos_consulta.append('id', notificacion.dataset.id);
            // Ahora: Obtenemos el ID desde el 'button'
            datos_consulta.append('id', button.dataset.id);
            datos_consulta.append('operacion', "quitar_notificacion");

            const resultado = await query(datos_consulta);

            if (!resultado.estatus) {
                Swal.fire('Atencion', resultado.mensaje || 'Error al procesar la notificación.', 'error');
                return;
            }

            // --- Lógica para eliminar el elemento (un poco diferente) ---
            
            // 1. Encontrar el <li> contenedor padre para eliminarlo
            // Antes: notificacion.remove();
            // Ahora:
            const itemContainer = button.parentElement;

            if (itemContainer) {
                // 2. Encontrar y eliminar el divisor (que es el *siguiente* <li>)
                const division = itemContainer.nextElementSibling;
                if (division && division.classList.contains("dropdown-divider")) {
                    division.remove();
                }
                
                // 3. Eliminar el <li> de la notificación
                itemContainer.remove();
            }

            // --- El resto de la lógica para actualizar el contador es igual ---
            const countLabelGeneral = document.getElementById('count-label');
            
            // Actualizamos la forma de contar: contamos los botones que quedan
            let count = document.querySelectorAll("#notificaciones-list .notif-remove-btn").length;
            
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
    // --- FIN DE CAMBIOS ---

    
    const botonMarcarTodas = document.getElementById('marcar-todas-leidas');

    if (botonMarcarTodas) {
        // (Esta función de "Marcar todas" no necesita cambios y seguirá funcionando)
        botonMarcarTodas.addEventListener('click', async function(e) {
            e.preventDefault();
            const contador = document.getElementById('count-label');
            if (!contador || contador.classList.contains('d-none') || contador.textContent === '0') {
                return;
            }
            
            const datos = new FormData();
            datos.append('operacion', 'marcar_todas_leidas');

            const resultado = await query(datos);

            if (resultado && resultado.estatus) {
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