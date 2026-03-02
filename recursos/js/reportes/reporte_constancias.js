/**
 * reporte_constancias.js
 * Generador unificado para Constancias de Residencia y Solvencias
 * Dependencias: utilidades.js, validaciones.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // Configuración para cada tipo de reporte
    const configBotones = {
        'boton_residencia': {
            titulo: 'Generar Constancia de Residencia',
            accionControlador: 'residencia',
            operacionConsulta: 'consultar_personas_residencia',
            icono_carga: 'bi-house-fill'
        },
        'boton_solvencia': {
            titulo: 'Generar Constancia de Solvencia',
            accionControlador: 'solvencia',
            operacionConsulta: 'consultar_personas_solvencia',
            icono_carga: 'bi-house-check-fill'
        }
    };

    // Función para precargar datos según el botón presionado
    async function prepararModalConstancia(idBoton) {
        const config = configBotones[idBoton];
        const select = document.getElementById('select_reporte');
        const botonGenerar = document.getElementById('boton_generar');
        
        document.getElementById("titulo_modal_persona").textContent = config.titulo;
        document.getElementById('label_reporte').textContent = "Seleccione el Residente:";
        botonGenerar.setAttribute("reporte", config.accionControlador);

        // Deshabilitar temporalmente mientras carga
        select.innerHTML = '<option selected hidden value="">Cargando residentes...</option>';
        select.disabled = true;

        let datos = new FormData();
        datos.append('operacion', config.operacionConsulta);

        try {
            let respuesta = await Utilidades.query(datos);
            if (respuesta.estatus && respuesta.datos.length > 0) {
                select.innerHTML = '<option selected hidden value="">Seleccione el Residente</option>';
                let fragment = document.createDocumentFragment();
                
                respuesta.datos.forEach(prop => {
                    let option = document.createElement("option");
                    option.textContent = `Apto Nº ${prop.nro_apartamento} | ${prop.nombre} ${prop.apellido}`;
                    option.value = prop.id_habitante;
                    fragment.appendChild(option);
                });
                
                select.appendChild(fragment);
                select.disabled = false;
            } else {
                select.innerHTML = '<option selected hidden value="">No hay residentes disponibles</option>';
            }
        } catch (error) {
            console.error(`Error al cargar datos para ${config.titulo}:`, error);
            Utilidades.mensaje('error', 'Error', 'No se pudieron cargar los datos.');
        }
    }

    // Asignar eventos de apertura a los botones del menú principal
    ['boton_residencia', 'boton_solvencia'].forEach(idBoton => {
        const btn = document.getElementById(idBoton);
        if (btn) {
            // Quitar el estado de "Cargando" inicial
            btn.removeAttribute('disabled');
            let spinnerContainer = btn.querySelector(".spinner-grow")?.parentElement;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi ${configBotones[idBoton].icono_carga}" style="font-size: 5rem !important;"></i>`;
            }

            btn.addEventListener('click', () => prepararModalConstancia(idBoton));
        }
    });

    // Validación del Select
    $('#select_reporte').on('change', async function() {
        if (!Validaciones.select(this.id)) return;
        
        let respuesta = await Utilidades.validar('validar_clave_foranea', {
            tabla: 'habitantes',
            nombre_clave: 'id_habitante',
            valor: this.value
        });

        if (respuesta.estatus) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            this.nextElementSibling.textContent = '';
        } else {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            this.nextElementSibling.textContent = 'El habitante no existe en la base de datos';
        }
    });

    // Envío del formulario
    document.getElementById('boton_generar')?.addEventListener('click', function(e) {
        e.preventDefault();
        const select = document.getElementById('select_reporte');
        
        if (!Validaciones.select(select.id) || select.value === "") {
            Utilidades.mensaje('error', 'Atención', 'Debe seleccionar un residente válido');
            return;
        }

        const reporte = this.getAttribute("reporte");
        const form = document.getElementById('form_reporte');
        form.setAttribute('action', `?pagina=reportes&accion=${reporte}`);
        form.submit();
        
        // Cerrar modal tras enviar
        bootstrap.Modal.getInstance(document.getElementById('modal_reporte_persona')).hide();
    });
});