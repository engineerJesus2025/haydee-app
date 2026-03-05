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
    await Promise.all([
        cargarTarjetasInicio(),
        cargarApartamentos(),
        cargarActividadReciente(),
        cargarWidgetPublicaciones()
    ]);

    await consultarPublicaciones();

    if (document.getElementById('div_graficos')) {
        await cargarGraficos();
    }
}

// Funciones para llenar el dashboard
async function cargarGraficos() {
    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_inicio_grafico");

    // USAMOS EL HELPER AQUI
    let respuesta = await Utilidades.query(datos_consulta,false);

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
        document.getElementById("canva_1").parentElement.removeAttribute("style");
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
        document.getElementById("canva_2").parentElement.removeAttribute("style");
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
        let respuesta = await Utilidades.query(datos_consulta,false);
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
    card.querySelector('small').textContent = `Publicado el ${FormatoFechas.formatoUsuario(publicacion.fecha)}`;
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

// --- FUNCIÓN PARA LAS TARJETAS SUPERIORES ---
async function cargarTarjetasInicio() {
    let datos = new FormData();
    datos.append("operacion", "consultar_tarjetas_resumen");

    try {
        let respuesta = await Utilidades.query(datos, false); // false = sin spinner
        if (respuesta.estatus && respuesta.datos) {
            const kpis = respuesta.datos;
            
            document.getElementById('kpi-aptos').textContent = `${kpis.apartamentos_ocupados}/${kpis.total_apartamentos}`;
            document.getElementById('kpi-residentes').textContent = kpis.residentes_activos;
            document.getElementById('kpi-recaudado').textContent = `${parseFloat(kpis.recaudado_mes).toFixed(2)} Bs.`;
            document.getElementById('kpi-pendientes').textContent = kpis.recibos_pendientes;
        }
    } catch (error) {
        console.error("Error al cargar KPIs:", error);
    }
}

// --- FUNCIÓN PARA EL WIDGET DE PUBLICACIONES ---
async function cargarWidgetPublicaciones() {
    let datos = new FormData();
    datos.append("operacion", "consulta_widget_publicaciones");

    try {
        let respuesta = await Utilidades.query(datos, false); // false = sin spinner
        const contenedor = document.getElementById('contenedor-widget-publicaciones');
        if (!contenedor) return;

        contenedor.innerHTML = ''; // Borramos los esqueletos

        if (!respuesta.estatus || respuesta.datos.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-muted my-3">No hay publicaciones recientes.</p>';
            return;
        }

        let fragment = document.createDocumentFragment();

        respuesta.datos.forEach(pub => {
            // Usamos tu helper de fechas
            let fechaFormateada = FormatoFechas.formatoUsuario(pub.fecha) || pub.fecha;

            let divItem = document.createElement('div');
            divItem.className = 'card publi-item bg-light border-0 p-3 rounded-4';
            divItem.innerHTML = `
                <div class="d-flex align-items-start">
                    <div class="activity-icon icon-box-blue me-3" style="width: 32px; height: 32px; font-size: 0.8rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">${pub.titulo}</h6>
                        <div class="text-muted-custom">${pub.nombre_usuario} - ${fechaFormateada}</div>
                    </div>
                </div>
            `;
            fragment.appendChild(divItem);
        });

        contenedor.appendChild(fragment);

    } catch (error) {
        console.error("Error al cargar widget de publicaciones:", error);
    }
}

async function cargarApartamentos() {
    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_apartamentos");

    try {
        let respuesta = await Utilidades.query(datos_consulta,false);

        if (!respuesta.estatus) {
            console.error("Error al cargar apartamentos:", respuesta.mensaje);
            return;
        }

        const apartamentos = respuesta.datos;
        const contenedorGrid = document.querySelector('.apartamentos-grid');
        
        if (!contenedorGrid) return;

        // 1. Limpiamos el contenido HTML estático
        contenedorGrid.innerHTML = '';

        // 2. Creamos un fragmento de documento para optimizar el renderizado en el navegador (Mejor rendimiento)
        let fragment = document.createDocumentFragment();

        apartamentos.forEach(apt => {
            // Evaluamos la clase de CSS a usar basándonos en el estado
            let claseEstado = apt.estado === 'Ocupado' ? 'apt-ocupado' : 'apt-libre';

            // Creamos el div principal del badge
            let divBadge = document.createElement('div');
            divBadge.className = `apt-badge ${claseEstado}`;
            
            // Creamos el span para el número de apartamento
            let spanNro = document.createElement('span');
            spanNro.textContent = apt.nro_apartamento;
            
            // Creamos el span para el texto inferior
            let spanTexto = document.createElement('span');
            spanTexto.className = 'apt-text-small';
            spanTexto.textContent = apt.estado.toUpperCase();
            
            // Unimos todo
            divBadge.appendChild(spanNro);
            divBadge.appendChild(spanTexto);
            fragment.appendChild(divBadge);
        });

        // 3. Inyectamos todo el grid de una sola vez
        contenedorGrid.appendChild(fragment);

    } catch (error) {
        console.error("Error en consulta de la cuadrícula de apartamentos:", error);
    }
}

async function cargarActividadReciente() {
    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_actividad");

    try {
        // Usamos tu helper Utilidades
        let respuesta = await Utilidades.query(datos_consulta,false);
        const contenedor = document.getElementById('contenedor-actividad');
        
        if (!contenedor) return;

        if (!respuesta.estatus) {
            contenedor.innerHTML = `<p class="text-center text-muted">${respuesta.mensaje}</p>`;
            return;
        }

        const actividades = respuesta.datos;
        contenedor.innerHTML = ''; // Limpiar el esqueleto estático

        if (actividades.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-muted">No hay actividad reciente.</p>';
            return;
        }

        let fragment = document.createDocumentFragment();

        actividades.forEach((item, index) => {
            console.log(item.accion)
            let config = obtenerConfiguracionIcono(item.accion);

            // Quitar el borde inferior al último elemento para estética
            let borderClass = index === actividades.length - 1 ? '' : 'mb-3 pb-3 border-bottom';

            // Usamos tu helper de fechas (Ajusta el nombre del método según tu formatoFechas.js)
            let fechaFormateada = FormatoFechas.formatoUsuario(item.fecha_evento); 

            // Lógica inteligente para mostrar el texto
            let textoActividad = '';
            if (item.accion === 'Inició sesión' || item.accion === 'Cerró sesión') {
                // Para login/logout mostramos un mensaje directo
                textoActividad = `<span class="text-muted">${item.accion.toLowerCase()} en el sistema.</span>`;
            } else {
                // Para el resto, mostramos el módulo y la descripción
                textoActividad = `<span class="text-muted">${item.accion.toLowerCase()} en ${item.nombre_modulo}:</span>
                                  <span class="text-dark"> ${item.descripcion}</span>`;
            }

            let divItem = document.createElement('div');
            divItem.className = `d-flex align-items-start ${borderClass}`;
            
            divItem.innerHTML = `
                <div class="activity-icon ${config.color} me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    ${config.icono}
                </div>
                <div>
                    <div class="mb-1">
                        <span class="fw-bold text-dark">${item.nombre_usuario}</span> 
                        ${textoActividad}
                    </div>
                    <div class="text-muted-custom" style="font-size: 0.8rem;">${fechaFormateada}</div>
                </div>
            `;
            
            fragment.appendChild(divItem);
        });

        contenedor.appendChild(fragment);

    } catch (error) {
        console.error("Error en consulta de actividad de bitácora:", error);
    }
}

/**
 * Retorna un color e ícono SVG basado en la acción formateada
 */
function obtenerConfiguracionIcono(accion) {
    let accionUpper = accion.toUpperCase();
    
    if (accionUpper.includes('REGISTRÓ') || accionUpper.includes('CREÓ')) {
        return {
            color: 'icon-box-green',
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>'
        };
    } else if (accionUpper.includes('ELIMINÓ') || accionUpper.includes('ANULÓ')) {
        return {
            color: 'icon-box-red',
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>'
        };
    } else if (accionUpper.includes('MODIFICÓ') || accionUpper.includes('ACTUALIZÓ')) {
        return {
            color: 'icon-box-yellow',
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>'
        };
    } else if (accionUpper.includes('INICIÓ')) {
        return {
            color: 'icon-box-blue',
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>'
        };
    } else if (accionUpper.includes('CERRÓ')) {
        return {
            color: 'icon-box-red', 
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>'
        };
    } else { // Consultó y otros
        return {
            color: 'icon-box-blue',
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
    }
}

// Función auxiliar para calcular "Hace X días"
function calcularTiempoHace(fechaStr) {
    const fechaEvento = new Date(fechaStr + 'T00:00:00'); 
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const diffTiempo = hoy.getTime() - fechaEvento.getTime();
    const diffDias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24)); 
    
    if (diffDias === 0) return "Hoy";
    if (diffDias === 1) return "Ayer";
    if (diffDias < 0) return "En el futuro"; // Por si hay fechas adelantadas
    return `Hace ${diffDias} días`;
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