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
    if (fin) return; 

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
    
    card.querySelector('.post-title').textContent = publicacion.titulo;
    card.querySelector('.post-date').textContent = FormatoFechas.tiempoRelativo(publicacion.fecha) || publicacion.fecha;
    card.querySelector('.post-description').textContent = publicacion.descripcion;
    card.querySelector('.author-name').textContent = publicacion.nombre_usuario;
    
    // Imagen y su Fallback
    const imgElement = card.querySelector('.post-image');
    // SVG convertido en Base64 para inyectarlo sin hacer peticiones extra
    const svgPorDefecto = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22400%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20400%20200%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22400%22%20height%3D%22200%22%20fill%3D%22%23e9ecef%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%236c757d%22%20font-size%3D%2216%22%20font-family%3D%22Arial%2C%20sans-serif%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3ESin%20Imagen%3C%2Ftext%3E%3C%2Fsvg%3E';
    
    if (publicacion.imagen && publicacion.imagen.trim() !== '') {
        imgElement.src = `recursos/img/cartelera_virtual/${publicacion.imagen}`;
    } else {
        imgElement.src = svgPorDefecto;
    }

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

    card.style.cursor = 'pointer'; // Para que el ratón cambie a una manito
    card.addEventListener('click', () => mostrarVistaPreviaPublicacion(publicacion));
    
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
            let fechaFormateada = FormatoFechas.tiempoRelativo(pub.fecha) || pub.fecha;

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

            divItem.addEventListener('click', () => mostrarVistaPreviaPublicacion(pub));

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

        contenedorGrid.innerHTML = '';
        let fragment = document.createDocumentFragment();

        apartamentos.forEach((apt, index) => {
            let claseEstado = apt.estado === 'Ocupado' ? 'apt-ocupado' : 'apt-libre';

            let divBadge = document.createElement('div');
            divBadge.className = `apt-badge ${claseEstado} animacion-aparecer`;
            divBadge.style.animationDelay = `${index * 0.02}s`; // Animación en cascada más rápida
            
            let spanNro = document.createElement('span');
            spanNro.textContent = apt.nro_apartamento;
            
            let spanTexto = document.createElement('span');
            spanTexto.className = 'apt-text-small';
            spanTexto.textContent = apt.estado.toUpperCase();
            
            // --- NUEVO: Evento Click para abrir el modal ---
            divBadge.addEventListener('click', () => {
                mostrarInfoApartamento(apt);
            });
            // -----------------------------------------------

            divBadge.appendChild(spanNro);
            divBadge.appendChild(spanTexto);
            fragment.appendChild(divBadge);
        });

        contenedorGrid.appendChild(fragment);

    } catch (error) {
        console.error("Error en consulta de la cuadrícula de apartamentos:", error);
    }
}

// --- Llena y abre el modal de Apartamentos ---
function mostrarInfoApartamento(apt) {
    document.getElementById('info-apt-nro').textContent = apt.nro_apartamento;
    document.getElementById('info-apt-residente').textContent = apt.residente_principal.toLowerCase(); // Convertimos a minúscula para que el CSS haga el Capitalize
    
    // 1. Configurar colores de cabecera y estado
    const header = document.getElementById('info-apt-header');
    const badgeEstado = document.getElementById('info-apt-estado');
    
    if (apt.estado === 'Ocupado') {
        header.className = 'modal-header border-0 pb-4 pt-4 justify-content-center position-relative bg-primary';
        badgeEstado.textContent = 'Ocupado';
        badgeEstado.className = 'badge bg-primary text-white rounded-pill shadow-sm px-4 py-2 fs-6 text-uppercase border border-2 border-white';
    } else {
        header.className = 'modal-header border-0 pb-4 pt-4 justify-content-center position-relative bg-success';
        badgeEstado.textContent = 'Libre';
        badgeEstado.className = 'badge bg-success text-white rounded-pill shadow-sm px-4 py-2 fs-6 text-uppercase border border-2 border-white';
    }

    // 2. Configurar colores de la caja de deuda
    const cajaDeuda = document.getElementById('info-apt-caja-deuda');
    const h3Deuda = document.getElementById('info-apt-deuda');
    const msgDeuda = document.getElementById('info-apt-mensaje-deuda');
    const iconoDeuda = document.getElementById('info-apt-icono-deuda');
    const montoDeuda = parseFloat(apt.deuda_total);

    h3Deuda.textContent = `${montoDeuda.toFixed(2)} Bs.`;

    if (montoDeuda > 0) {
        // Estado: Con Deuda (Tonos Rojos)
        cajaDeuda.style.backgroundColor = '#fef2f2'; // Fondo rojo muy suave pastel
        cajaDeuda.style.color = '#dc2626'; // Texto rojo oscuro
        h3Deuda.className = 'fw-bolder mb-0 text-danger';
        iconoDeuda.className = 'fas fa-exclamation-triangle me-1 text-danger';
        msgDeuda.textContent = 'Posee deuda pendiente';
        msgDeuda.className = 'fw-bold text-danger';
    } else {
        // Estado: Solvente (Tonos Verdes)
        cajaDeuda.style.backgroundColor = '#f0fdf4'; // Fondo verde muy suave pastel
        cajaDeuda.style.color = '#16a34a'; // Texto verde oscuro
        h3Deuda.className = 'fw-bolder mb-0 text-success';
        iconoDeuda.className = 'fas fa-check-circle me-1 text-success';
        msgDeuda.textContent = 'Solvente';
        msgDeuda.className = 'fw-bold text-success';
    }

    // 3. Mostrar Modal
    const modalElement = document.getElementById('modalInfoApartamento');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

async function cargarActividadReciente() {
    let datos_consulta = new FormData();
    datos_consulta.append("operacion", "consulta_actividad");

    try {
        let respuesta = await Peticiones.enviar(datos_consulta, "", false);
        const contenedor = document.getElementById('contenedor-actividad');
        
        if (!contenedor) return;

        if (!respuesta.estatus) {
            contenedor.innerHTML = `<p class="text-center text-muted">${respuesta.mensaje}</p>`;
            return;
        }

        const actividades = respuesta.datos;
        contenedor.innerHTML = ''; // Limpiar el esqueleto

        if (actividades.length === 0) {
            contenedor.innerHTML = '<p class="text-center text-muted">No hay actividad reciente.</p>';
            return;
        }

        let fragment = document.createDocumentFragment();
        let fechaAnterior = null; // Para llevar control de las agrupaciones

        actividades.forEach((item, index) => {
            // 1. Lógica de agrupación por fechas
            let fechaSoloDia = item.fecha_evento.split(' ')[0]; // Extrae 'YYYY-MM-DD'
            
            if (fechaSoloDia !== fechaAnterior) {
                let etiquetaFecha = obtenerEtiquetaFecha(fechaSoloDia);
                let separador = document.createElement('div');
                separador.className = 'timeline-date-separator animacion-aparecer';
                separador.textContent = etiquetaFecha;
                fragment.appendChild(separador);
                fechaAnterior = fechaSoloDia;
            }

            // 2. Lógica del Avatar (Iniciales + Color)
            let iniciales = obtenerIniciales(item.nombre_usuario);
            let colorAvatar = obtenerColorPorNombre(item.nombre_usuario);

            // 3. Configuración del ícono de acción (ahora pequeñito)
            let config = obtenerConfiguracionIcono(item.accion);

            // Quitar el borde inferior al último elemento para estética
            let borderClass = index === actividades.length - 1 ? '' : 'mb-3 pb-3 border-bottom';

            // Hora del evento (ya no necesitamos la fecha completa aquí, solo la hora o 'Hace X')
            let horaEvento = item.fecha_evento.split(' ')[1].substring(0, 5); // Ej: 14:30

            // 4. Construcción del texto
            let textoActividad = '';
            if (item.accion === 'Inició sesión' || item.accion === 'Cerró sesión') {
                textoActividad = `<span class="text-muted">${item.accion.toLowerCase()} en el sistema.</span>`;
            } else {
                textoActividad = `<span class="text-muted">${item.accion.toLowerCase()} en ${item.nombre_modulo}:</span>
                                  <span class="text-dark"> ${item.descripcion}</span>`;
            }

            let divItem = document.createElement('div');
            divItem.className = `d-flex align-items-start ${borderClass} animacion-aparecer`;
            
            divItem.innerHTML = `
                <div class="avatar-container">
                    <div class="user-avatar" style="background-color: ${colorAvatar};">
                        ${iniciales}
                    </div>
                    <div class="action-badge ${config.color}">
                        ${config.icono}
                    </div>
                </div>
                <div>
                    <div class="mb-1">
                        <span class="fw-bold text-dark">${item.nombre_usuario}</span> 
                        ${textoActividad}
                    </div>
                    <div class="text-muted-custom" style="font-size: 0.75rem;">
                        <i class="far fa-clock me-1"></i>${horaEvento} 
                    </div>
                </div>
            `;

            divItem.style.cursor = 'pointer';
            divItem.addEventListener('click', () => {
                mostrarDetalleBitacoraRapido(item);
            });
            
            fragment.appendChild(divItem);
        });

        contenedor.appendChild(fragment);

    } catch (error) {
        console.error("Error en consulta de actividad de bitácora:", error);
    }
}

// --- FUNCIÓN: Mostrar detalles de una Actividad en el Modal del Dashboard ---
function mostrarDetalleBitacoraRapido(item) {
    // 1. Configuración de colores
    const colores = { 
        'consultó': "bg-secondary", 
        'eliminó': "bg-danger", 
        'registró': "bg-primary", 
        'modificó': "bg-success", 
        'inició sesión': "bg-info text-dark", 
        'cerró sesión': "bg-danger text-white",
        'respaldó': "bg-indigo text-white",
        'restauró': "bg-teal text-white"    
    };
    
    const accionNormalizada = item.accion.toLowerCase();
    const claseColor = colores[accionNormalizada] || "bg-secondary";

    // 2. Referencias al DOM
    const divConsulta = document.getElementById('detalle_consulta_dash');
    const iconoConsulta = document.getElementById('icono_consulta_dash');
    const mensajeConsulta = document.getElementById('mensaje_consulta_dash');
    const divCambios = document.getElementById('detalle_cambios_dash');

    // 3. Llenar datos generales
    document.getElementById('detalle_usuario_dash').textContent = item.nombre_usuario;
    document.getElementById('detalle_fecha_dash').textContent = FormatoFechas.tiempoRelativo(item.fecha_evento) || item.fecha_evento;
    document.getElementById('detalle_modulo_dash').textContent = item.nombre_modulo;

    const accionBadge = document.getElementById('detalle_accion_dash');
    accionBadge.textContent = item.accion;
    accionBadge.className = `badge ${claseColor}`;

    // 4. Lógica de visualización (Mensaje simple vs Pestañas de JSON)
    const accionesDeSoloMensaje = ['consultó', 'inició sesión', 'cerró sesión', 'respaldó', 'restauró'];

    if (accionesDeSoloMensaje.includes(accionNormalizada)) {
        // Modo Mensaje
        divConsulta.classList.remove('d-none');
        divCambios.classList.add('d-none');
        mensajeConsulta.textContent = item.descripcion;
    } else {
        // Modo JSON (Requiere datos que actualmente tu consulta de dashboard no trae)
        // NOTA: Tu modelo Bitacora._consultar_actividad_dashboard actual borra los JSON. 
        // Mostraremos un aviso elegante si no están disponibles.
        divConsulta.classList.add('d-none');
        divCambios.classList.remove('d-none');

        let anterioresHtml = '<p class="text-muted">Detalles técnicos no cargados en la vista rápida.</p>';
        let nuevosHtml = '<p class="text-muted">Detalles técnicos no cargados en la vista rápida.</p>';

        if(item.valores_anteriores) anterioresHtml = objetoAListaDash(JSON.parse(item.valores_anteriores));
        if(item.valores_nuevos) nuevosHtml = objetoAListaDash(JSON.parse(item.valores_nuevos));

        if (accionNormalizada === 'registró') {
            document.getElementById('anteriores-tab-dash').parentElement.style.display = 'none';
            document.getElementById('nuevos-tab-dash').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab-dash').click(); 
        } else if (accionNormalizada === 'eliminó') {
            document.getElementById('anteriores-tab-dash').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab-dash').parentElement.style.display = 'none';
            document.getElementById('anteriores-tab-dash').click(); 
        } else { 
            document.getElementById('anteriores-tab-dash').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab-dash').parentElement.style.display = 'block';
            document.getElementById('nuevos-tab-dash').click();
        }

        document.getElementById('valores_anteriores_dash').innerHTML = anterioresHtml;
        document.getElementById('valores_nuevos_dash').innerHTML = nuevosHtml;
    }

    // 5. Mostrar Modal
    const modalElement = document.getElementById('modalDetalleBitacoraDashboard');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

function objetoAListaDash(obj) {
    if (!obj || Object.keys(obj).length === 0) return '<p class="text-muted">No hay datos</p>';
    let html = '<ul class="list-group">';
    for (let [key, value] of Object.entries(obj)) {
        let valorMostrar = typeof value === 'object' && value !== null ? JSON.stringify(value) : value;
        html += `<li class="list-group-item"><strong>${key}:</strong> ${valorMostrar}</li>`;
    }
    html += '</ul>';
    return html;
}

// --- FUNCIONES AUXILIARES PARA LA BITÁCORA ---

function obtenerIniciales(nombreCompleto) {
    let partes = nombreCompleto.trim().split(' ');
    if (partes.length >= 2) {
        return (partes[0][0] + partes[1][0]).toUpperCase();
    } else if (partes.length === 1) {
        return partes[0].substring(0, 2).toUpperCase();
    }
    return "?";
}

// Genera un color consistente basado en el string del nombre
function obtenerColorPorNombre(nombre) {
    let hash = 0;
    for (let i = 0; i < nombre.length; i++) {
        hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
    }
    // Paleta de colores agradables
    const colores = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
    let index = Math.abs(hash) % colores.length;
    return colores[index];
}

function obtenerEtiquetaFecha(fechaString) {
    // fechaString viene en formato 'YYYY-MM-DD'
    let partes = fechaString.split('-');
    let fechaEvento = new Date(partes[0], partes[1] - 1, partes[2]);
    let hoy = new Date();
    hoy.setHours(0,0,0,0);
    
    let ayer = new Date(hoy);
    ayer.setDate(hoy.getDate() - 1);

    if (fechaEvento.getTime() === hoy.getTime()) return "Hoy";
    if (fechaEvento.getTime() === ayer.getTime()) return "Ayer";
    
    // Si es más antiguo, devolvemos formato DD/MM/YYYY
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

/**
 * Retorna un color e ícono SVG basado en la acción formateada
 * (Los SVGs se han ajustado a viewBox="0 0 24 24" y tamaño 12x12 para que quepan en el badge)
 */
function obtenerConfiguracionIcono(accion) {
    let accionUpper = accion.toUpperCase();
    let svgProps = 'width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"';
    
    if (accionUpper.includes('REGISTRÓ') || accionUpper.includes('CREÓ')) {
        return {
            color: 'icon-box-green',
            icono: `<svg ${svgProps}><path d="M12 5v14M5 12h14"></path></svg>`
        };
    } else if (accionUpper.includes('ELIMINÓ') || accionUpper.includes('ANULÓ')) {
        return {
            color: 'icon-box-red',
            icono: `<svg ${svgProps}><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`
        };
    } else if (accionUpper.includes('MODIFICÓ') || accionUpper.includes('ACTUALIZÓ')) {
        return {
            color: 'icon-box-yellow',
            icono: `<svg ${svgProps}><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`
        };
    } else if (accionUpper.includes('INICIÓ')) {
        return {
            color: 'icon-box-blue',
            icono: `<svg ${svgProps}><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>`
        };
    } else if (accionUpper.includes('CERRÓ')) {
        return {
            color: 'icon-box-red', 
            icono: `<svg ${svgProps}><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>`
        };
    } else { // Consultó y otros
        return {
            color: 'icon-box-blue',
            icono: `<svg ${svgProps}><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`
        };
    }
}

// --- FUNCIÓN PARA VISTA PREVIA DE PUBLICACIONES ---
function mostrarVistaPreviaPublicacion(pub) {
    // Llenar los textos básicos
    document.getElementById('vista_titulo').textContent = pub.titulo;
    document.getElementById('vista_autor').textContent = pub.nombre_usuario;
    document.getElementById('vista_fecha').textContent = FormatoFechas.tiempoRelativo(pub.fecha) || pub.fecha;
    document.getElementById('vista_descripcion').textContent = pub.descripcion;
    
    // Lógica de la etiqueta de prioridad
    const badge = document.getElementById('vista_prioridad');
    if (pub.prioridad == 1) {
        badge.textContent = "Urgente";
        badge.className = "badge bg-danger mb-2";
    } else if (pub.prioridad == 2) {
        badge.textContent = "Importante";
        badge.className = "badge bg-warning text-dark mb-2";
    } else {
        badge.textContent = "Informativo";
        badge.className = "badge bg-success mb-2";
    }

    // --- LÓGICA DE LA IMAGEN CORREGIDA ---
    const imgContainer = document.getElementById('contenedor_imagen');
    const img = document.getElementById('vista_imagen');
    const msgError = document.getElementById('mensaje_error_imagen');
    
    // 1. REINICIAR ESTADOS: Volvemos a hacer visible el tag <img> y ocultamos el error
    img.style.display = 'inline-block'; 
    if(msgError) msgError.classList.add('d-none');

    if (pub.imagen && pub.imagen.trim() !== '') {
        img.src = `recursos/img/cartelera_virtual/${pub.imagen}`;
        imgContainer.style.display = 'block'; // Mostramos todo el bloque de la foto
    } else {
        imgContainer.style.display = 'none'; // Ocultamos todo el bloque
        img.removeAttribute('src'); // Evitamos usar src='' para no provocar el evento 'onerror'
    }

    // Mostrar el Modal usando la API de Bootstrap 5
    const modalElement = document.getElementById('modalVistaPreviaPublicacion');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
    modalInstance.show();
}