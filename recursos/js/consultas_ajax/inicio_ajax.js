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
    // return
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

    try {
        // Pedimos los datos (sin spinner)
        let respuesta = await Peticiones.enviar(datos_consulta, "", false);
        Validador.procesarRespuesta(respuesta, (respuestaServidor) => {    
            const data = respuestaServidor.datos;

            // ==================================================
            // PREPARAR GRÁFICO 1: ESTADO DE DEUDAS
            // ==================================================
            let deudasData = [
                data.grafico_deudas.aptos_solventes || 0,
                data.grafico_deudas.aptos_morosos || 0
            ];

            // Ocultar esqueletos
            document.getElementById('esqueleto_titulo_1').classList.add('d-none');
            document.getElementById('esqueleto_canva_1').classList.add('d-none');
            document.getElementById('canva_1').removeAttribute('hidden');
            document.getElementById('canva_1').parentElement.classList.add('animacion-aparecer');
            document.getElementById('canva_1').parentElement.style.height = '250px';

            // Actualizar datos inferiores
            document.getElementById('esqueleto_dato_1_1').textContent = `${deudasData[0]} Aptos.`;
            document.getElementById('esqueleto_dato_1_1').classList.add('animacion-aparecer');
            document.getElementById('esqueleto_dato_2_1').textContent = `${deudasData[1]} Aptos.`;
            document.getElementById('esqueleto_dato_2_1').classList.add('animacion-aparecer');

            // .className.add('animacion-aparecer');

            if (deudasData[0] === 0 && deudasData[1] === 0) {
                document.getElementById('div_alert_1').removeAttribute('hidden');
                document.getElementById('canva_1').parentElement.style.display = 'none';
            } else {
                const ctx1 = document.getElementById('canva_1').getContext('2d');
                if (graficaChart_1) graficaChart_1.destroy();
                
                graficaChart_1 = new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: ['Solventes', 'Con Deuda'],
                        datasets: [{
                            data: deudasData,
                            backgroundColor: ['#10b981', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '70%',
                        // --- NUEVA ANIMACIÓN NATIVA DE CHART.JS ---
                        animation: {
                            animateScale: true,   // Crece desde el centro
                            animateRotate: true,  // Gira mientras aparece
                            duration: 1500,       // Dura 1.5 segundos (muy fluido)
                            easing: 'easeOutQuart' // Aceleración elegante
                        }
                    }
                });
            }

            // ==================================================
            // PREPARAR GRÁFICO 2: INGRESOS VS GASTOS
            // ==================================================
            let labelsMeses = [];
            let datosIngresos = [];
            let datosGastos = [];
            
            // Sumatorias para la parte inferior
            let ingresoMesActual = 0;
            let gastoMesActual = 0;

            data.grafico_ingresos_gastos.forEach((mes, index) => {
                labelsMeses.push(mes.etiqueta);
                datosIngresos.push(mes.ingresos);
                datosGastos.push(mes.gastos);
                
                if (index === data.grafico_ingresos_gastos.length - 1) {
                    ingresoMesActual = mes.ingresos;
                    gastoMesActual = mes.gastos;
                }
            });

            // Ocultar esqueletos
            document.getElementById('esqueleto_titulo_2').classList.add('d-none');
            document.getElementById('esqueleto_canva_2').classList.add('d-none');
            document.getElementById('canva_2').removeAttribute('hidden');
            document.getElementById('canva_2').parentElement.classList.add('animacion-aparecer');
            document.getElementById('canva_2').parentElement.style.height = '250px';

            // Actualizar datos inferiores
            document.getElementById('esqueleto_dato_1_2').textContent = `${parseFloat(ingresoMesActual).toFixed(2)} Bs.`;
            document.getElementById('esqueleto_dato_1_2').classList.add('animacion-aparecer');
            document.getElementById('esqueleto_dato_2_2').textContent = `${parseFloat(gastoMesActual).toFixed(2)} Bs.`;
            document.getElementById('esqueleto_dato_2_2').classList.add('animacion-aparecer');
            
            const ctx2 = document.getElementById('canva_2').getContext('2d');
            if (graficaChart_2) graficaChart_2.destroy();
            
            graficaChart_2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: labelsMeses,
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: datosIngresos,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        },
                        {
                            label: 'Egresos',
                            data: datosGastos,
                            backgroundColor: '#f43f5e',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    },
                    // --- NUEVA ANIMACIÓN NATIVA DE CHART.JS ---
                    animation: {
                        duration: 1500,
                        easing: 'easeOutBack', // Da un pequeñísimo "rebote" al terminar de subir
                        delay: (context) => {
                            // Crea un efecto de "ola" retrasando cada barra un poquito
                            let delay = 0;
                            if (context.type === 'data' && context.mode === 'default' && !context.dropped) {
                                delay = context.dataIndex * 150 + context.datasetIndex * 100;
                                context.dropped = true;
                            }
                            return delay;
                        }
                    }
                }
            });
        });
    } catch (error) {
        console.error("Error en consulta de gráficos:", error);
    }
}

async function consultarPublicaciones() {
    if (fin) return; // Optimizacion simple

    document.getElementById('carga_publicaciones').removeAttribute('hidden');

    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_inicio");
    datos_consulta.append("limite", limite);

    try {
        let respuesta = await Peticiones.enviar(datos_consulta, "", false);
        document.getElementById('carga_publicaciones').setAttribute('hidden', '');
        Validador.procesarRespuesta(respuesta, (respuestaServidor) => {    
            const publicaciones = respuestaServidor.datos;

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
        });

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
    const card = clone.querySelector('.item-publicacion');
    
    // 1. Llenar los textos usando tus helpers
    card.querySelector('.post-title').textContent = publicacion.titulo;
    card.querySelector('.post-date').textContent = FormatoFechas.formatoUsuario(publicacion.fecha) || publicacion.fecha;
    card.querySelector('.post-description').textContent = publicacion.descripcion;
    card.querySelector('.author-name').textContent = publicacion.nombre_usuario;
    
    // 2. Lógica de la Imagen y su Fallback
    const imgElement = card.querySelector('.post-image');
    // SVG convertido en Base64 para inyectarlo sin hacer peticiones extra
    const svgPorDefecto = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22400%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20400%20200%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22400%22%20height%3D%22200%22%20fill%3D%22%23e9ecef%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%236c757d%22%20font-size%3D%2216%22%20font-family%3D%22Arial%2C%20sans-serif%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3ESin%20Imagen%3C%2Ftext%3E%3C%2Fsvg%3E';
    
    if (publicacion.imagen && publicacion.imagen.trim() !== '') {
        imgElement.src = `recursos/img/cartelera/${publicacion.imagen}`;
    } else {
        imgElement.src = svgPorDefecto;
    }

    // 3. Lógica Semántica para la Etiqueta de Prioridad
    const badge = card.querySelector('.priority-badge');
    const prioridadStr = String(publicacion.prioridad); 
    
    if (prioridadStr === "1") {
        badge.textContent = "Urgente";
        badge.classList.add('bg-danger');
    } else if (prioridadStr === "2") {
        badge.textContent = "Importante";
        badge.classList.add('bg-warning', 'text-dark');
    } else {
        badge.textContent = "Informativo";
        badge.classList.add('bg-success');
    }
    
    return card;
}

// --- FUNCIÓN PARA LAS TARJETAS SUPERIORES ---
async function cargarTarjetasInicio() {
    let datos = new FormData();
    datos.append("operacion", "consultar_tarjetas_resumen");

    try {
        let respuesta = await Peticiones.enviar(datos, "", false);
        Validador.procesarRespuesta(respuesta, (respuestaServidor) => {    
            const kpis = respuestaServidor.datos;
            
            // Textos principales
            document.getElementById('kpi-aptos').textContent = `${kpis.apartamentos_ocupados}/${kpis.total_apartamentos}`;
            document.getElementById('kpi-residentes').textContent = kpis.residentes_activos;
            document.getElementById('kpi-recaudado').textContent = `${parseFloat(kpis.recaudado_mes).toFixed(2)} Bs.`;
            document.getElementById('kpi-pendientes').textContent = kpis.recibos_pendientes;
            
            // --- APLICAR ANIMACIÓN A LAS TARJETAS ---
            ['kpi-aptos', 'kpi-residentes', 'kpi-recaudado', 'kpi-pendientes', 'badge-aptos', 'badge-pendientes'].forEach(id => {
                let elemento = document.getElementById(id);
                if(elemento) elemento.classList.add('animacion-aparecer');
            });

            // Badge 1: Porcentaje de ocupación
            let porcentajeOcupacion = 0;
            if (kpis.total_apartamentos > 0) {
                porcentajeOcupacion = Math.round((kpis.apartamentos_ocupados / kpis.total_apartamentos) * 100);
            }
            document.getElementById('badge-aptos').textContent = `${porcentajeOcupacion}% Ocupado`;
            document.getElementById('badge-aptos').removeAttribute("hidden");

            // Badge 4: Monto total de deuda
            let badgePendientes = document.getElementById('badge-pendientes');
            if(badgePendientes) {
                badgePendientes.textContent = `${parseFloat(kpis.deuda_total).toFixed(2)} Bs.`;
                badgePendientes.removeAttribute("hidden");
            }
        });
    } catch (error) {
        console.error("Error al cargar KPIs:", error);
    }
}

// --- FUNCIÓN PARA EL WIDGET DE PUBLICACIONES ---
async function cargarWidgetPublicaciones() {
    let datos = new FormData();
    datos.append("operacion", "consulta_widget_publicaciones");

    try {
        const contenedor = document.getElementById('contenedor-widget-publicaciones');
        if (!contenedor) return;

        let respuesta = await Peticiones.enviar(datos, "", false); // false = sin spinner
        if (!respuesta.estatus || respuesta.datos.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-muted my-3">No hay publicaciones recientes.</p>';
            return;
        }

        contenedor.innerHTML = ''; // Borramos los esqueletos

        let fragment = document.createDocumentFragment();

        respuesta.datos.forEach(pub => {
            let fechaFormateada = FormatoFechas.formatoUsuario(pub.fecha) || pub.fecha;

            // Configuramos los colores según la prioridad
            let colorIcono, colorBorde;
            
            if (pub.prioridad == 1) { // Alta
                colorIcono = 'icon-box-red';
                colorBorde = 'border-danger';
            } else if (pub.prioridad == 2) { // Media
                colorIcono = 'icon-box-yellow';
                colorBorde = 'border-warning';
            } else { // Baja (3)
                colorIcono = 'icon-box-blue';
                colorBorde = 'border-primary';
            }

            let divItem = document.createElement('div');
            // Le agregamos 'border-start border-4' y la clase dinámica del color
            divItem.className = `card publi-item bg-light border-0 p-3 rounded-4 border-start border-4 ${colorBorde} animacion-aparecer`;
            
            divItem.innerHTML = `
                <div class="d-flex align-items-start">
                    <div class="activity-icon ${colorIcono} me-3" style="width: 32px; height: 32px; font-size: 0.8rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">${pub.titulo}</h6>
                        <div class="text-muted-custom" style="font-size: 0.75rem;">
                            ${pub.nombre_usuario} <br> ${fechaFormateada}
                        </div>
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
        let respuesta = await Peticiones.enviar(datos_consulta, "", false);

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

        apartamentos.forEach((apt,index) => {
            // Evaluamos la clase de CSS a usar basándonos en el estado
            let claseEstado = apt.estado === 'Ocupado' ? 'apt-ocupado' : 'apt-libre';

            // Creamos el div principal del badge
            let divBadge = document.createElement('div');
            divBadge.className = `apt-badge ${claseEstado} animacion-aparecer`;
            divBadge.style.animationDelay = `${index * 0.05}s`;
            
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
        let respuesta = await Peticiones.enviar(datos_consulta, "", false);
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
            divItem.className = `d-flex align-items-start ${borderClass} animacion-aparecer`;
            
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
            icono: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
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
    
    // JS Puro en lugar de Utilidades
    const esqueleto = document.getElementById(idEsqueleto);
    if (esqueleto) esqueleto.replaceWith(contenedor);
}

function mostrarAlertaSinDatos(idAlerta, idsAEliminar) {
    const alerta = document.getElementById(idAlerta);
    if (alerta) alerta.removeAttribute("hidden");
    
    // JS Puro en lugar de Utilidades
    idsAEliminar.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.remove();
    });
}