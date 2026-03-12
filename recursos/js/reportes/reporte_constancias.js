/**
 * reporte_constancias.js
 * Dependencias: Validador.js, Patrones.js, Alertas.js, Peticiones.js
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

    // Almacén en memoria de los datos (para no consultar cada vez que se haga click)
    let datosCargados = {
        'boton_residencia': [],
        'boton_solvencia': []
    };

    // 1. Precargar datos al iniciar la vista
    precargarBoton('boton_residencia');
    precargarBoton('boton_solvencia');

    async function precargarBoton(idBoton) {
        const config = configBotones[idBoton];
        let datos = new FormData();
        datos.append('operacion', config.operacionConsulta);
        
        const respuesta = await Peticiones.enviar(datos, "", false);
        const botonDom = document.getElementById(idBoton);
        const contenedorTarjeta = botonDom.parentElement; // El div.card que tiene el title

        // 1. Destruimos cualquier tooltip previo para evitar conflictos
        const tooltipPrevio = bootstrap.Tooltip.getInstance(contenedorTarjeta);
        if (tooltipPrevio) tooltipPrevio.dispose();

        if (respuesta.estatus && respuesta.datos && respuesta.datos.length > 0) {
            datosCargados[idBoton] = respuesta.datos;
            botonDom.removeAttribute('disabled'); 
            
            // Asignamos el mensaje de éxito
            contenedorTarjeta.setAttribute('title', 'Click para ver opciones de este reporte');
            
            let spinnerContainer = botonDom.querySelector(".spinner-grow")?.parentElement;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi ${config.icono_carga}" style="font-size: 5rem !important;"></i>`;
            }
        } else {
            // Asignamos el mensaje de bandeja vacía
            contenedorTarjeta.setAttribute('title', 'No hay residentes disponibles para este reporte');
            let spinnerContainer = botonDom.querySelector(".spinner-grow")?.parentElement;
            if (spinnerContainer) {
                spinnerContainer.innerHTML = `<i class="bi bi-inbox text-secondary" style="font-size: 5rem !important;"></i>`;
            }
        }
        // 2. Creamos el tooltip de Bootstrap con el texto actualizado
        new bootstrap.Tooltip(contenedorTarjeta, {
            placement: 'top',
            trigger: 'hover'
        });
    }

    // 2. Función para preparar el modal de forma instantánea al hacer clic
    function prepararModalConstancia(idBoton) {
        const config = configBotones[idBoton];
        const select = document.getElementById('select_reporte');
        const botonGenerar = document.getElementById('boton_generar');
        
        document.getElementById("titulo_modal_persona").textContent = config.titulo;
        document.getElementById('label_reporte').textContent = "Seleccione el Residente: ";
        botonGenerar.setAttribute("reporte", config.accionControlador);

        let asterisco = document.createElement("span");
        asterisco.classList.add("text-danger");
        asterisco.textContent = "*";
        document.getElementById('label_reporte').appendChild(asterisco);

        select.innerHTML = '<option selected hidden value="">Seleccione el Habitante</option>';
        
        let fragment = document.createDocumentFragment();
        datosCargados[idBoton].forEach(habitante => {
            let option = document.createElement("option");
            option.textContent = `${habitante.cedula} - ${habitante.nombre} ${habitante.apellido}`;
            option.value = habitante.id_habitante;
            fragment.appendChild(option);
        });
        select.appendChild(fragment);

        select.dataset.valor = "habitante";
    }

    // Asignación de clics
    document.getElementById('boton_residencia')?.addEventListener("click", () => prepararModalConstancia('boton_residencia'));
    document.getElementById('boton_solvencia')?.addEventListener("click", () => prepararModalConstancia('boton_solvencia'));

    // Validación asíncrona (si llegase a manipular el select por HTML inspector)
    document.getElementById('select_reporte')?.addEventListener('change', async function() {
        if (this.dataset.valor != "habitante") return;
        if (!Validador.evaluarSelect(this.id)) return;
        await Validador.verificarExistenciaEnServidor(
            'validar_clave_foranea', 
            { tabla: 'habitantes', nombre_clave: 'id_habitante', valor: this.value }, 
            this, 
            'El habitante no existe en la base de datos'
        );
    });

    // Envío del formulario
    document.getElementById('boton_generar')?.addEventListener('click', function(e) {
        e.preventDefault();
        const select = document.getElementById('select_reporte');
        
        if (!Validador.evaluarSelect(select.id)) {
            Alertas.mostrar('error', 'Atención', 'Debe seleccionar un residente válido');
            return;
        }

        const reporte = this.getAttribute("reporte");
        const form = document.getElementById('form_reporte');
        form.setAttribute('action', `?pagina=reportes&accion=${reporte}`);
        form.submit();
        
        bootstrap.Modal.getInstance(document.getElementById('modal_reporte_persona'))?.hide();
    });
});