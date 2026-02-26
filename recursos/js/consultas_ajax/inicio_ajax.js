// Variables globales
let contenido_principal = document.getElementById('contenido');
let limite = 0;
let fin = false; 
let consultando = false; 
let bloqueado = false; 
let graficaChart_1;
let graficaChart_2;

document.addEventListener('DOMContentLoaded', () => {
    cargaInicio();
});

let scrollTimeout;
window.addEventListener("scroll", () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
        if (consultando || bloqueado || fin) return;

        const alturaPagina = document.documentElement.scrollHeight;
        const alturaVentana = window.innerHeight;
        const desplazamientoActual = window.scrollY;

        if (desplazamientoActual + alturaVentana >= alturaPagina - 100) {
            consultando = true;
            bloqueado = true;
            consultarPublicaciones();
        }
    }, 150);
});

async function cargaInicio() {
    await consultarPublicaciones();
    if (document.getElementById('div_graficos')) {
        await cargarGraficos();
    }
}

async function cargarGraficos() {
    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_inicio_grafico");

    // USAMOS EL HELPER AQUI
    let respuesta = await Utilidades.query(datos_consulta);

    if (!respuesta.estatus) {
        Utilidades.mensaje('error', 4000, 'Atención', respuesta.mensaje || 'Error al cargar gráficos');
        return;
    }

    const datos = respuesta.datos;
    const ctx_1 = document.getElementById('canva_1').getContext('2d');
    const ctx_2 = document.getElementById('canva_2').getContext('2d');

    // Preparar títulos
    const titulo_1 = document.createElement("h5");
    titulo_1.classList.add("text-center");
    titulo_1.textContent = "Deudas de apartamentos";
    Utilidades.reemplazarElemento("esqueleto_titulo_1", titulo_1);

    const titulo_2 = document.createElement("h5");
    titulo_2.classList.add("text-center");
    titulo_2.textContent = "Resumen de balance del mes";
    Utilidades.reemplazarElemento("esqueleto_titulo_2", titulo_2);

    // --- GRÁFICO 1 ---
    if (datos && datos[0] && datos[1] && (datos[0].valor != 0 || datos[1].valor != 0)) {
        actualizarInfoGrafico('esqueleto_dato_1_1', 'Total de deuda:', datos[0].valor, 'text-danger');
        actualizarInfoGrafico('esqueleto_dato_2_1', 'Total de ingresos (Apt):', datos[1].valor, 'text-success');

        graficaChart_1 = new Chart(ctx_1, {
            type: 'pie',
            data: {
                labels: [`Apartamentos Morosos: ${datos[0].accion}`, `Apartamentos sin deudas: ${datos[1].accion}`],
                datasets: [{
                    label: "Valor en Bs",
                    data: [parseFloat(datos[0].valor || 0).toFixed(2), parseFloat(datos[1].valor || 0).toFixed(2)],
                    backgroundColor: ['#e01d22', '#0079C2']
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
        
        Utilidades.eliminarElemento('esqueleto_canva_1');
        document.getElementById("canva_1").removeAttribute("hidden");
    } else {
        mostrarAlertaSinDatos('div_alert_1', ['canva_1', 'esqueleto_canva_1', 'esqueleto_dato_1_1', 'esqueleto_dato_2_1']);
    }

    // --- GRÁFICO 2 ---
    if (datos && datos[2] && datos[3]) {
        actualizarInfoGrafico('esqueleto_dato_1_2', 'Total de Gastos:', datos[2].valor, 'text-danger');
        actualizarInfoGrafico('esqueleto_dato_2_2', 'Total de Ingresos:', datos[3].valor, 'text-success');

        graficaChart_2 = new Chart(ctx_2, {
            type: 'bar',
            data: {
                labels: ["Total de Gastos", "Total de Ingresos"],
                datasets: [{
                    label: "Cantidad Bs.",
                    data: [datos[2].valor, datos[3].valor],
                    backgroundColor: ['#e01d22', '#0079C2']
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        Utilidades.eliminarElemento('esqueleto_canva_2');
        document.getElementById("canva_2").removeAttribute("hidden");
    } else {
        mostrarAlertaSinDatos('div_alert_2', ['canva_2', 'esqueleto_canva_2', 'esqueleto_dato_1_2', 'esqueleto_dato_2_2']);
    }
}

async function consultarPublicaciones() {
    if (fin) return; // Optimizacion simple

    document.getElementById('carga_publicaciones').removeAttribute('hidden');

    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_inicio");
    datos_consulta.append("limite", limite);

    try {
        // USAMOS EL HELPER AQUI
        let respuesta = await Utilidades.query(datos_consulta);
        document.getElementById('carga_publicaciones').setAttribute('hidden', '');

        if (!respuesta.estatus) {
            console.error("Error del servidor:", respuesta.mensaje);
            return;
        }

        const publicaciones = respuesta.datos;

        if (!publicaciones || publicaciones.length === 0) {
            fin = true;
            mostrarMensajeFin(limite === 0 ? "No hay publicaciones" : "No hay más resultados");
            return;
        }

        let fragment = document.createDocumentFragment();
        publicaciones.forEach(publicacion => {
            fragment.appendChild(construirHTMLPublicacion(publicacion));
        });

        limite += 2;
        contenido_principal.appendChild(fragment);

    } catch (error) {
        console.error("Error en consulta:", error);
    } finally {
        consultando = false;
        setTimeout(() => { bloqueado = false; }, 600);
    }
}

function construirHTMLPublicacion(publicacion) {
    const template = document.getElementById('template-publicacion');
    const clone = template.content.cloneNode(true);
    const card = clone.querySelector('.card');
    const contentCol = card.querySelector('.col-md-7');
    const imageCol = card.querySelector('.col-md-5');
    
    // Llenar datos de forma segura
    card.querySelector('.post-title').textContent = publicacion.titulo;
    card.querySelector('small').textContent = `Publicado el ${Utilidades.formatearFecha(publicacion.fecha)}`;
    card.querySelector('.author-badge').textContent = `Por ${publicacion.nombre_usuario}`;
    card.querySelector('.post-description').textContent = publicacion.descripcion;
    
    if (publicacion.imagen && publicacion.imagen !== '') {
        card.querySelector('.post-image').src = `recursos/img/cartelera/${publicacion.imagen}`;
    } else {
        imageCol.remove(); // Eliminar la columna de imagen
        contentCol.className = 'col-12'; // Ajustar ancho
        const meta = card.querySelector('.post-meta');
        meta.classList.remove('mb-5'); // Quitar margen extra
    }
    
    return card;
}

// funciones específicas de esta vista
function mostrarMensajeFin(mensajeTexto) {
    if(document.getElementById('msg-fin-posts')) return;
    let div_no_hay = document.createElement("div");
    div_no_hay.id = 'msg-fin-posts';
    div_no_hay.className = "col-10 text-center my-2 text-muted";
    div_no_hay.textContent = mensajeTexto;
    contenido_principal.appendChild(div_no_hay);
}

function actualizarInfoGrafico(idEsqueleto, textoLabel, valor, claseColor) {
    const contenedor = document.createElement("p");
    contenedor.textContent = textoLabel + " ";
    const valorNegrita = document.createElement("b");
    valorNegrita.className = claseColor;
    valorNegrita.textContent = (parseFloat(valor).toFixed(2) || 0) + " Bs.";
    contenedor.appendChild(valorNegrita);
    Utilidades.reemplazarElemento(idEsqueleto, contenedor);
}

function mostrarAlertaSinDatos(idAlerta, idsAEliminar) {
    const alerta = document.getElementById(idAlerta);
    if (alerta) alerta.removeAttribute("hidden");
    idsAEliminar.forEach(id => Utilidades.eliminarElemento(id));
}