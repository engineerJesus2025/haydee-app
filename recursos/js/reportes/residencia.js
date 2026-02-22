/**
 * residencia.js
 * Gestión de constancias de residencia
 * Dependencias: utilidades.js, validaciones.js
 */

let botonResidencia = document.getElementById('boton_residencia');
let arrayPropietariosResidentes = [];

botonResidencia.addEventListener("click", () => {
    document.getElementById("titulo_modal_persona").textContent = 'Generar constancia de residencia';
    document.getElementById('label_reporte').textContent = "Seleccione la persona para la Constancia";

    let botonGenerar = document.getElementById('boton_generar');
    botonGenerar.setAttribute("reporte", "residencia");

    let select = document.getElementById('select_reporte');
    // Limpiar opciones previas
    select.innerHTML = '<option selected hidden value="">Seleccione el Residente</option>';

    let fragment = document.createDocumentFragment();
    arrayPropietariosResidentes.forEach(prop => {
        let option = document.createElement("option");
        option.textContent = `Apartamento Nº ${prop.nro_apartamento}, ${prop.nombre} ${prop.apellido}`;
        option.value = prop.id_habitante;
        fragment.appendChild(option);
    });
    select.appendChild(fragment);

    // Configurar validación para este modal
    $(select).off('change').on('change', async function() {
        if (!Validaciones.select(this.id)) return;
        // Verificar existencia del habitante
        let existe = await verificarHabitante(this.value);
        if (existe) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
            this.nextElementSibling.textContent = '';
        } else {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            this.nextElementSibling.textContent = 'El habitante no existe';
        }
    });

    // Configurar evento del botón generar
    $('#boton_generar').off('click').on('click', function(e) {
        e.preventDefault();
        let select = document.getElementById('select_reporte');
        if (!Validaciones.select(select.id)) {
            Utilidades.mensaje('error', 'Atención', 'Debe seleccionar un residente');
            return;
        }
        // Si se requiere verificación adicional, se hace aquí
        let reporte = this.getAttribute("reporte");
        document.getElementById('form_reporte').setAttribute('action', `?pagina=reportes_controlador.php&accion=${reporte}`);
        document.getElementById('form_reporte').submit();
    });
});

async function verificarHabitante(id) {
    let respuesta = await Utilidades.validar('validar_clave_foranea', {
        tabla: 'habitantes',
        nombre_clave: 'id_habitante',
        valor: id
    });
    return respuesta.estatus;
}

function consultarPropietarios() {
    let datos = new FormData();
    datos.append('operacion', "consultar_personas_residencia");

    Utilidades.query(datos).then(respuesta => {
        if (respuesta.estatus && respuesta.datos.length > 0) {
            botonResidencia.removeAttribute('disabled');
            arrayPropietariosResidentes = respuesta.datos;
        } else {
            botonResidencia.parentElement.setAttribute('title', 'No hay habitantes ni propietarios registrados');
        }
        // Reemplazar spinner por ícono
        let spinnerContainer = botonResidencia.querySelector(".spinner-grow")?.parentElement;
        if (spinnerContainer) {
            spinnerContainer.innerHTML = `<i class="bi bi-house-fill" style="font-size: 5rem !important;"></i>`;
        }
    }).catch(error => {
        console.error("Error al cargar propietarios:", error);
        Utilidades.mensaje('error', 'Error', 'No se pudieron cargar los propietarios');
    });
}

consultarPropietarios();