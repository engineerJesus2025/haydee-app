<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Centro de Ayuda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once "vista/componentes/estilos.php"; ?>
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/ayuda.css">
</head>

<body id="body-pd" class="body-pd d-flex flex-column">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php require_once "vista/componentes/navbar.php"; ?>
            
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once "vista/componentes/header.php"; ?>
                
                <main class="col ps-0 pt-0 mb-5">
                    
                    <div class="hero-section">
                        <h1 class="display-5 fw-bold mb-3"><i class="bi bi-life-preserver me-3"></i>Centro de Soporte</h1>
                        <p class="fs-5 mb-4 text-white opacity-75">Escribe tu duda y encuentra la respuesta al instante.</p>
                        
                        <div class="search-container">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="buscador" class="form-control" placeholder="Ej: registrar pago, cerrar año fiscal, generar recibo...">
                        </div>
                    </div>

                    <div class="container px-4">
                        
                        <div class="row mb-5 justify-content-center">
                            <div class="col-lg-10">
                                <div class="card download-card p-4">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="badge-soft-danger rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                            </div>
                                            <div>
                                                <h4 class="mb-1 fw-bold text-body">Manual de Usuario Completo</h4>
                                                <p class="mb-0 text-muted-custom">Descarga la guía oficial en formato PDF para consultarla sin conexión.</p>
                                            </div>
                                        </div>
                                        <a href="<?php echo URL_BASE; ?>recursos/documentos/manual_usuario.pdf" target="_blank" class="btn btn-danger btn-lg px-4 shadow-sm rounded-pill fw-semibold">
                                            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Descargar PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-12">
                                <h3 class="section-title py-2"><i class="bi bi-patch-question me-2"></i>Preguntas Frecuentes</h3>
                                <div class="accordion" id="faqAccordion">
                                    
                                    <div class="accordion-item faq-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                ¿Cómo calculo el cobro del condominio del mes?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Para generar los cobros, primero debes haber registrado el <strong>Presupuesto Mensual</strong> (gastos estimados) o tener gastos reales cargados. Luego, dirígete al módulo de <strong>Mensualidad</strong>, selecciona el mes correspondiente y haz clic en "Guardar". El sistema distribuirá el monto entre los apartamentos según su alícuota.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item faq-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                ¿Qué hago si no veo un módulo en el menú?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Si no puedes ver un módulo (como Bancos o Usuarios), significa que tu <strong>Rol de Usuario</strong> no tiene los permisos necesarios. Contacta al Administrador del sistema para que edite tu Rol en el módulo de <strong>Seguridad</strong>.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item faq-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                ¿Cómo cierro un Año Fiscal?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Ve al módulo de <strong>Año Fiscal</strong>. Busca el año actual y selecciona la opción de editar. Cambia el estado de "Abierto" a "Cerrado" y define la fecha de cierre. <span class="text-danger fw-bold">¡Advertencia!</span> Una vez cerrado, no podrás registrar ni modificar pagos o gastos de ese periodo.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item faq-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                                ¿Cómo registro una transferencia en Dólares?
                                            </button>
                                        </h2>
                                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                En el módulo de <strong>Pagos</strong>, al registrar un nuevo ingreso, usa el botón de intercambio (<i class="bi bi-arrow-left-right"></i>) junto al campo de monto. Esto cambiará la divisa a USD ($). Asegúrate de que la tasa del día esté actualizada en el sistema.
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <h3 class="section-title py-2"><i class="bi bi-book-half me-2"></i>Documentación por Módulos</h3>

                            <div class="col-lg-6">
                                <h5 class="text-primary fw-bold mb-3 ms-2"><i class="bi bi-cash-stack me-2"></i>Finanzas y Tesorería</h5>
                                <div class="accordion" id="accordionFinanzas">
                                    
                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manPagos">
                                                <i class="bi bi-cash-coin me-2 text-success"></i> Pagos y Recibos
                                            </button>
                                        </h2>
                                        <div id="manPagos" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Registra los aportes de los propietarios.
                                                <ul>
                                                    <li><strong>Registrar:</strong> Ingresa fecha, monto, referencia y selecciona el apartamento.</li>
                                                    <li><strong>Divisas:</strong> Soporta pagos en Bs y USD.</li>
                                                    <li><strong>Recibos:</strong> Genera PDF automáticos para descargar o imprimir.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manGastos">
                                                <i class="bi bi-receipt me-2 text-danger"></i> Gastos del Condominio
                                            </button>
                                        </h2>
                                        <div id="manGastos" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Registro de facturas y egresos directos.
                                                <ul>
                                                    <li>Registra los pagos de servicios (Agua, Luz, Vigilancia).</li>
                                                    <li>Asocia cada gasto a un <strong>Proveedor</strong> registrado.</li>
                                                    <li>Estos gastos se descuentan del presupuesto y se reflejan en la relación de cuentas.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manPresupuesto">
                                                <i class="bi bi-calculator me-2 text-primary"></i> Presupuesto Mensual
                                            </button>
                                        </h2>
                                        <div id="manPresupuesto" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Planifica los gastos del mes siguiente antes de cobrar.
                                                <ul>
                                                    <li>Carga los montos estimados de luz, agua y vigilancia.</li>
                                                    <li>Define la cuota de <strong>Fondo de Reserva</strong>.</li>
                                                    <li>Este módulo es vital para calcular la mensualidad.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manCaja">
                                                <i class="bi bi-piggy-bank me-2 text-warning"></i> Caja Chica
                                            </button>
                                        </h2>
                                        <div id="manCaja" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Gestión de gastos menores y efectivo.
                                                <ul>
                                                    <li><strong>Apertura:</strong> Inicia el mes con un fondo fijo.</li>
                                                    <li><strong>Gastos:</strong> Registra salidas pequeñas (ej: bombillos, copias).</li>
                                                    <li><strong>Reposición:</strong> Solicita reintegro cuando el fondo se agote.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manBancos">
                                                <i class="bi bi-bank me-2 text-secondary"></i> Bancos y Cuentas
                                            </button>
                                        </h2>
                                        <div id="manBancos" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Directorio de cuentas receptoras del condominio.
                                                <ul>
                                                    <li>Registra cuentas nacionales (Pago Móvil) e internacionales (Zelle/Efectivo).</li>
                                                    <li>Estos datos aparecen en los reportes de pago para los vecinos.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manFiscal">
                                                <i class="bi bi-calendar-check me-2 text-info"></i> Año Fiscal
                                            </button>
                                        </h2>
                                        <div id="manFiscal" class="accordion-collapse collapse" data-bs-parent="#accordionFinanzas">
                                            <div class="manual-body accordion-body">
                                                Control de periodos contables.
                                                <ul>
                                                    <li>Abre el año para permitir operaciones.</li>
                                                    <li><strong>Cierre:</strong> Bloquea la modificación de datos históricos para proteger la contabilidad.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-lg-6">
                                <h5 class="text-danger fw-bold mb-3 ms-2"><i class="bi bi-gear me-2"></i>Gestión y Operaciones</h5>
                                <div class="accordion" id="accordionOperaciones">

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manMensualidad">
                                                <i class="bi bi-receipt-cutoff me-2 text-danger"></i> Mensualidad
                                            </button>
                                        </h2>
                                        <div id="manMensualidad" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                El corazón de la cobranza.
                                                <ul>
                                                    <li>Selecciona un mes presupuestado.</li>
                                                    <li>El sistema calcula cuánto debe pagar cada apartamento según su alícuota.</li>
                                                    <li>Genera la deuda en el sistema para que los propietarios puedan pagar.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manSolicitud">
                                                <i class="bi bi-file-earmark-text me-2 text-info"></i> Solicitud de Gasto
                                            </button>
                                        </h2>
                                        <div id="manSolicitud" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Formaliza las peticiones de dinero.
                                                <ul>
                                                    <li>Permite solicitar fondos para compras o reparaciones.</li>
                                                    <li>El sistema valida si hay dinero disponible en el presupuesto del mes seleccionado.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manProveedores">
                                                <i class="bi bi-truck me-2 text-body"></i> Proveedores
                                            </button>
                                        </h2>
                                        <div id="manProveedores" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Directorio de servicios externos.
                                                <ul>
                                                    <li>Registra empresas de limpieza, vigilancia, o servicios públicos.</li>
                                                    <li>Es obligatorio tener el proveedor registrado para asignarle un gasto.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manCartelera">
                                                <i class="bi bi-easel me-2 text-primary"></i> Cartelera Virtual
                                            </button>
                                        </h2>
                                        <div id="manCartelera" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Comunicación con los vecinos.
                                                <ul>
                                                    <li>Publica noticias, avisos de cobro o convocatorias en el dashboard principal.</li>
                                                    <li><strong>Imágenes:</strong> Puedes subir afiches o fotos ilustrativas.</li>
                                                    <li><strong>Prioridad:</strong> Destaca avisos urgentes con diferentes niveles de alerta.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manTipos">
                                                <i class="bi bi-tags me-2 text-secondary"></i> Tipos de Gasto
                                            </button>
                                        </h2>
                                        <div id="manTipos" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Categorización contable.
                                                <ul>
                                                    <li>Crea categorías dinámicas como "Mantenimiento", "Servicios" o "Nómina".</li>
                                                    <li>Ayuda a organizar de forma precisa los reportes estadísticos.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manSeguridad">
                                                <i class="bi bi-shield-lock me-2 text-success"></i> Usuarios y Seguridad
                                            </button>
                                        </h2>
                                        <div id="manSeguridad" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Control estricto de acceso al sistema.
                                                <ul>
                                                    <li><strong>Usuarios:</strong> Crea cuentas con credenciales seguras.</li>
                                                    <li><strong>Roles:</strong> Define perfiles administrativos.</li>
                                                    <li><strong>Permisos:</strong> Decide exactamente a qué módulo puede entrar cada rol.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="contact-box mb-5">
                            <h4 class="text-body fw-bold"><i class="bi bi-headset text-primary me-2"></i>¿Aún necesitas ayuda?</h4>
                            <p class="text-muted-custom mb-4">Si tienes un problema técnico que no logras resolver con esta guía, contacta al desarrollador de la plataforma.</p>
                            <button id="btn-soporte" class="btn btn-outline-primary btn-lg px-5 rounded-pill fw-semibold">
                                <i class="bi bi-envelope-paper me-2"></i>Contactar Soporte
                            </button>
                        </div>

                    </div>
                </main>
                
                <?php require_once "vista/componentes/script.php"; ?>
                
                <script>
                    document.getElementById('btn-soporte').addEventListener('click', () => {
                        // Detectamos el tema actual para que el SWAL también contraste
                        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                        
                        Swal.fire({
                            title: 'Contacto de Soporte',
                            html: `<div class="text-start mt-3">
                                     <p style="color: ${isDarkMode ? '#cbd5e1' : '#475569'};">Para asistencia técnica avanzada sobre el funcionamiento del sistema, comunícate a través de los siguientes canales:</p>
                                     <ul class="list-unstyled mt-4">
                                        <li class="mb-3 d-flex align-items-center">
                                            <div class="badge-soft-primary p-2 rounded-circle me-3">
                                                <i class="bi bi-envelope-at fs-5"></i>
                                            </div>
                                            <span style="color: ${isDarkMode ? '#f8fafc' : '#1e293b'}; font-weight: 500;">jesusgescalonae@gmail.com</span>
                                        </li>
                                        <li class="mb-3 d-flex align-items-center">
                                            <div class="badge-soft-success p-2 rounded-circle me-3">
                                                <i class="bi bi-whatsapp fs-5"></i>
                                            </div>
                                            <span style="color: ${isDarkMode ? '#f8fafc' : '#1e293b'}; font-weight: 500;">+58 424-5528892</span>
                                        </li>
                                     </ul>
                                     <hr class="opacity-25 mt-4">
                                     <div class="text-center">
                                        <small style="color: ${isDarkMode ? '#94a3b8' : '#64748b'};"><i class="bi bi-clock me-1"></i> Horario de atención: 8:00 AM - 5:00 PM</small>
                                     </div>
                                   </div>`,
                            background: isDarkMode ? '#1e293b' : '#ffffff',
                            color: isDarkMode ? '#f8fafc' : '#1e293b',
                            confirmButtonText: '<i class="bi bi-check2 me-1"></i> Entendido',
                            confirmButtonColor: '#0d6efd',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary rounded-pill px-4 shadow-sm'
                            },
                            showCloseButton: true
                        });
                    });
                </script>

            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/src/ayuda.js"></script>
</body>
</html> 