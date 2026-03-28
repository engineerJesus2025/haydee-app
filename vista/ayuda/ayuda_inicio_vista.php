<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Centro de Ayuda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once "vista/componentes/estilos.php"; ?>
    
    <style>
        /* Estilos para Ayuda */
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            color: white;
            padding: 4rem 1rem;
            border-radius: 0 0 30px 30px;
            margin-bottom: 3rem;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .search-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #0d6efd;
            font-size: 1.2rem;
        }

        #buscador {
            padding-left: 50px;
            height: 60px;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .section-title {
            border-left: 5px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* Estilo para la tarjeta de descarga del Manual */
        .download-card {
            background: #fff;
            border-left: 5px solid #dc3545; /* Rojo PDF */
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        
        .download-card:hover {
            transform: scale(1.01);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .faq-item {
            border: 1px solid #e9ecef;
            margin-bottom: 12px;
            border-radius: 10px !important;
            overflow: hidden;
            background: white;
        }

        .faq-button {
            background-color: #ffffff;
            color: #495057;
            font-weight: 600;
            padding: 1.2rem;
        }
        
        .faq-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0d6efd;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
        }

        .manual-card {
            border: none;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .manual-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .manual-header button {
            font-weight: 700;
            font-size: 1.05rem;
        }

        .manual-body {
            background-color: #fcfcfc;
            line-height: 1.6;
            color: #555;
        }

        .manual-body ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        .contact-box {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php require_once "vista/componentes/navbar.php"; ?>
            
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once "vista/componentes/header.php"; ?>
                
                <main class="col ps-0 pt-0 mb-5">
                    
                    <div class="hero-section">
                        <h1 class="display-5 fw-bold mb-3"><i class="bi bi-life-preserver me-3"></i>Centro de Soporte</h1>
                        <p class="fs-5 mb-4 text-light opacity-90">Escribe tu duda y encuentra la respuesta al instante.</p>
                        
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
                                            <i class="bi bi-file-earmark-pdf-fill text-danger display-3 me-3"></i>
                                            <div>
                                                <h4 class="mb-1 text-dark fw-bold">Manual de Usuario Completo</h4>
                                                <p class="mb-0 text-muted">Descarga la guía oficial en formato PDF para consultarla sin conexión.</p>
                                            </div>
                                        </div>
                                        <a href="<?php echo URL_BASE; ?>recursos/documentos/manual_usuario.pdf" target="_blank" class="btn btn-outline-danger btn-lg px-4 shadow-sm">
                                            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Descargar PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-12">
                                <h3 class="section-title"><i class="bi bi-patch-question me-2"></i>Preguntas Frecuentes</h3>
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
                                                Si no puedes ver un módulo (como Bancos o Usuarios), significa que tu <strong>Rol de Usuario</strong> no tiene los permisos necesarios. Contacta al Administrador del sistema para que edite tu Rol en el módulo de <strong>Roles y Permisos</strong>.
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
                            <h3 class="section-title"><i class="bi bi-book-half me-2"></i>Documentación por Módulos</h3>

                            <div class="col-lg-6">
                                <h5 class="text-primary mb-3 ms-2">💰 Finanzas y Tesorería</h5>
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
                                                    <li><strong>Recibos:</strong> Genera PDF automáticos para enviar por correo.</li>
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
                                                <i class="bi bi-calendar-check me-2 text-dark"></i> Año Fiscal
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
                                <h5 class="text-danger mb-3 ms-2">⚙️ Gestión y Operaciones</h5>
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
                                                <i class="bi bi-truck me-2 text-dark"></i> Proveedores
                                            </button>
                                        </h2>
                                        <div id="manProveedores" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Directorio de servicios externos.
                                                <ul>
                                                    <li>Registra empresas de limpieza, vigilancia, o servicios públicos (Corpoelec, Hidrolara).</li>
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
                                                    <li>Publica noticias, avisos de cobro o convocatorias.</li>
                                                    <li><strong>Imágenes:</strong> Puedes subir afiches o fotos.</li>
                                                    <li><strong>Prioridad:</strong> Destaca avisos urgentes en rojo.</li>
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
                                                    <li>Crea categorías como "Mantenimiento", "Servicios", "Nómina".</li>
                                                    <li>Ayuda a organizar los reportes de egresos.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item manual-card">
                                        <h2 class="accordion-header manual-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manSeguridad">
                                                <i class="bi bi-shield-lock me-2 text-dark"></i> Usuarios y Seguridad
                                            </button>
                                        </h2>
                                        <div id="manSeguridad" class="accordion-collapse collapse" data-bs-parent="#accordionOperaciones">
                                            <div class="manual-body accordion-body">
                                                Control de acceso al sistema.
                                                <ul>
                                                    <li><strong>Usuarios:</strong> Crea cuentas para administradores o propietarios.</li>
                                                    <li><strong>Roles:</strong> Define perfiles (Admin, Contadora, Propietario).</li>
                                                    <li><strong>Permisos:</strong> Otorga o revoca el acceso a módulos específicos.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="contact-box mb-5">
                            <h4><i class="bi bi-headset me-2"></i>¿Aún necesitas ayuda?</h4>
                            <p class="text-muted mb-4">Si tienes un problema técnico que no aparece aquí, contacta al desarrollador o al administrador principal.</p>
                            <button id="btn-soporte" class="btn btn-outline-primary btn-lg px-5">
                                Contactar Soporte
                            </button>
                        </div>

                    </div>
                </main>
                <?php require_once "vista/componentes/script.php"; ?>
                
                <script>
                    document.getElementById('btn-soporte').addEventListener('click', () => {
                        Swal.fire({
                            title: 'Contacto de Soporte',
                            html: `<div class="text-start">
                                     <p>Para asistencia técnica avanzada, comunícate con:</p>
                                     <ul class="list-unstyled">
                                        <li class="mb-2"><i class="bi bi-envelope-at text-primary me-2"></i> jesusgescalonae@gmail.com</li>
                                        <li class="mb-2"><i class="bi bi-whatsapp text-success me-2"></i> +58 424-5528892</li>
                                     </ul>
                                     <small class="text-muted">Horario de atención: 8:00 AM - 5:00 PM</small>
                                   </div>`,
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#0d6efd',
                            showCloseButton: true
                        });
                    });
                </script>

            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/ayuda.js"></script>
</body>
</html>