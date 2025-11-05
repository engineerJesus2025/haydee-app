<!DOCTYPE html>
<html>

<head>
    <title>Ayuda Interactiva | Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/navbar.php";
            ?>
            <div class="col d-flex flex-column  min-vh-100 gris">
                <?php
                require_once "vista/componentes/header.php";
                ?>
                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3 mb-3">
                        <h1 class="mb-4 text-center">Ayuda Interactiva del Sistema</h1>

                        <!-- Buscador -->
                        <div class="mb-4">
                            <input type="text" id="buscador" class="form-control" placeholder="🔍 Buscar módulo, palabra clave, etc...">
                        </div>

                        <!-- Acordeón -->
                        <div class="accordion" id="ayudaAccordion">

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        Módulo de Pagos
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Pagos</strong> es una de las piezas más importantes del sistema, ya que permite registrar y administrar todos los pagos realizados por los residentes del conjunto residencial. A través de este módulo, se puede llevar un control detallado de cada operación financiera asociada a mensualidades, formas de pago, transacciones bancarias y comprobantes.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registrar pagos:</strong> El sistema permite crear un nuevo pago, especificando la fecha, monto, tasa de cambio (si aplica), tipo de pago (efectivo, transferencia o pago móvil), observaciones, e indicando a qué apartamento y mensualidad corresponde.</li>
                                            <li><strong>Asociación con mensualidades:</strong> Cada pago puede vincularse directamente a una mensualidad específica del apartamento, asegurando que el historial financiero esté correctamente conectado.</li>
                                            <li><strong>Detalle del pago:</strong> Se registra un detalle interno por cada pago realizado, lo cual permite almacenar información más granular como la caja asociada, el método de pago, y la conversión en bolívares o dólares.</li>
                                            <li><strong>Transacciones bancarias:</strong> Si el pago es mediante una transacción (como transferencia o pago móvil), se puede registrar el banco, la referencia y cargar una imagen del comprobante.</li>
                                            <li><strong>Modificación y edición:</strong> Siempre que el usuario tenga los permisos necesarios, puede editar un pago ya registrado. Esto incluye modificar datos del pago principal, su detalle o la transacción bancaria.</li>
                                            <li><strong>Eliminación:</strong> También se permite eliminar un pago. Esta acción borra todos los datos relacionados: el pago principal, su detalle, la transacción bancaria (si aplica) y su vínculo con la mensualidad.</li>
                                            <li><strong>Vista previa:</strong> Se puede consultar un pago específico en detalle mediante la opción de vista previa, que muestra toda la información asociada, incluyendo montos, fecha, estado, mensualidad y comprobante.</li>
                                            <!-- <li><strong>Generación de PDF:</strong> Tras registrar un pago, el sistema puede generar automáticamente un comprobante en formato PDF, listo para ser guardado o impreso.</li> -->
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Verifica que la <strong>caja esté abierta</strong> antes de registrar un pago. Si no hay una caja activa, el sistema no permitirá continuar.</li>
                                            <li>Si vas a registrar una transferencia, asegúrate de incluir una referencia válida y, si es posible, una <strong>imagen del comprobante</strong>.</li>
                                            <li>Antes de eliminar un pago, confirma que no esté vinculado a otras transacciones importantes. El sistema realiza eliminaciones encadenadas y podrías perder información valiosa.</li>
                                            <li>Recuerda que los montos de mensualidad y de pago pueden no coincidir si hay ajustes manuales. El sistema muestra ambos por separado.</li>
                                            <li>Para buscar un pago específico, puedes usar filtros por fecha, estado o apartamento directamente en la tabla.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>A continuación, una imagen referencial del formulario de pagos:</p>
                                        <img src="recursos/img/ayuda/pagos/2.WEBP" alt="Formulario de pago" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo está diseñado para garantizar el control total sobre los pagos, su trazabilidad, y la generación automática de comprobantes. Es una herramienta clave para el seguimiento financiero del sistema y la transparencia con los propietarios.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        Módulo de Gastos
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Gastos</strong> es el corazón silencioso que controla y administra todas las salidas financieras dentro del sistema. A través de este módulo, podrás registrar, modificar y supervisar cada gasto realizado para mantener la salud económica del conjunto residencial al día y bajo control absoluto.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registrar gastos:</strong> Permite ingresar un nuevo gasto, especificando la fecha, el monto, la categoría, la descripción detallada y el método de pago usado.</li>
                                            <li><strong>Categorías y subcategorías:</strong> Organiza los gastos por tipos, facilitando la clasificación y el análisis financiero, desde mantenimiento hasta servicios generales o imprevistos.</li>
                                            <li><strong>Gestión de comprobantes:</strong> Se puede adjuntar una imagen o documento que respalde cada gasto, asegurando la trazabilidad y soporte documental para auditorías.</li>
                                            <li><strong>Modificación y edición:</strong> El usuario autorizado puede actualizar cualquier dato del gasto registrado, corrigiendo errores o ajustando detalles sin perder el historial.</li>
                                            <li><strong>Eliminación segura:</strong> Los gastos pueden eliminarse sólo si no afectan balances críticos; el sistema controla estas dependencias para evitar inconsistencias financieras.</li>
                                            <li><strong>Visualización detallada:</strong> Consulta gastos específicos con toda su información asociada, desde el monto hasta la categoría, la fecha y el comprobante adjunto.</li>
                                            <li><strong>Generación de reportes:</strong> El sistema puede exportar listados y reportes de gastos en formatos PDF o Excel, permitiendo un análisis transparente y eficiente.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Antes de registrar un gasto, verifica que todos los campos obligatorios estén completos para evitar errores o pérdidas de datos.</li>
                                            <li>Adjunta siempre el comprobante o factura, esto facilitará la auditoría y la gestión administrativa.</li>
                                            <li>Utiliza categorías claras y precisas para que los reportes financieros sean útiles y comprensibles.</li>
                                            <li>Al eliminar un gasto, asegúrate que no afecte registros contables importantes; el sistema te alertará si existen dependencias.</li>
                                            <li>Revisa periódicamente los reportes para identificar patrones de gasto y oportunidades de ahorro.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>A continuación, una imagen representativa del formulario de gastos:</p>
                                        <img src="recursos/img/ayuda/gastos/2.WEBP" alt="Formulario de gasto" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo está diseñado para brindar un control preciso y transparente de todas las salidas financieras, fomentando una gestión responsable y eficiente del presupuesto del conjunto residencial.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        Módulo de Caja Chica
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Caja Chica</strong> es la llave maestra para gestionar los fondos menores y gastos inmediatos dentro del conjunto residencial. Aquí se controla el flujo de efectivo pequeño, facilitando la administración ágil, segura y transparente de desembolsos cotidianos.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Apertura y cierre de caja:</strong> Controla el estado de la caja chica, registrando cuándo se abre y cuándo se cierra para evitar movimientos fuera de periodo.</li>
                                            <li><strong>Registro de ingresos y egresos:</strong> Permite ingresar movimientos de dinero, detallando concepto, monto, fecha y responsable, manteniendo siempre un saldo actualizado y confiable.</li>
                                            <li><strong>Control de saldo:</strong> Visualiza en tiempo real el saldo disponible, evitando sobregiros y garantizando que la caja tenga fondos suficientes para cubrir gastos menores.</li>
                                            <li><strong>Justificación de gastos:</strong> Cada egreso debe ir acompañado de una descripción y, opcionalmente, comprobantes o facturas digitales que respalden la operación.</li>
                                            <li><strong>Modificación y auditoría:</strong> Solo usuarios autorizados pueden editar o eliminar movimientos, con registro histórico para auditorías futuras y control interno estricto.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Mantén la caja chica siempre con saldo suficiente para gastos imprevistos, pero evita acumular montos excesivos que dificulten el control.</li>
                                            <li>Registra cada movimiento con precisión y detalle para evitar confusiones y facilitar futuras revisiones.</li>
                                            <li>Adjunta comprobantes siempre que sea posible para tener respaldo documental y cumplir con las buenas prácticas contables.</li>
                                            <li>Realiza cierres periódicos y revisiones para detectar inconsistencias a tiempo y mantener la confianza en el sistema.</li>
                                            <li>Limita el acceso al módulo solo a usuarios responsables para proteger la integridad del fondo.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>A continuación, un ejemplo del formulario de caja chica donde se registran movimientos:</p>
                                        <img src="recursos/img/ayuda/caja_chica/formulario.png" alt="Formulario de caja chica" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo es fundamental para la gestión ágil y transparente de los gastos menores, contribuyendo a la estabilidad financiera y el buen manejo de los recursos del conjunto residencial.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading5">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                        Módulo de Mensualidades
                                    </button>
                                </h2>
                                <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Mensualidades</strong> es el núcleo de la planificación financiera del conjunto residencial. Aquí se gestionan las cuotas periódicas que cada apartamento debe pagar según su participación y el calendario de facturación.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro de mensualidades:</strong> Permite crear una mensualidad indicando el mes, año, monto a pagar, tasa del dólar (si aplica), y a qué apartamento está asociada.</li>
                                            <li><strong>Asignación por apartamento:</strong> Cada mensualidad queda directamente vinculada a un apartamento, permitiendo calcular la deuda por vivienda de forma clara.</li>
                                            <li><strong>Control de estado:</strong> El sistema diferencia mensualidades pagadas de pendientes, facilitando el seguimiento y la gestión de deudas.</li>
                                            <li><strong>Modificación flexible:</strong> Las mensualidades pueden editarse siempre que el usuario tenga los permisos adecuados. Esto incluye ajustes en el monto, tasa o asociación.</li>
                                            <li><strong>Eliminación segura:</strong> Se puede eliminar una mensualidad siempre que no esté vinculada a un pago. El sistema te alertará si hay dependencias activas.</li>
                                            <li><strong>Consulta avanzada:</strong> Puedes buscar mensualidades filtrando por apartamento, mes, año o estado (pagada/pendiente).</li>
                                            <li><strong>Vista previa:</strong> Cada mensualidad puede visualizarse con todo detalle, incluyendo el historial de pagos asociados.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Antes de registrar mensualidades, asegúrate de haber cargado todos los apartamentos en el sistema.</li>
                                            <li>La tasa del dólar debe ser consistente con la usada al momento de los pagos, para evitar diferencias contables.</li>
                                            <li>Si necesitas aplicar aumentos generales, considera una función de actualización masiva o por lote, si tu sistema lo permite.</li>
                                            <li>Verifica que las mensualidades estén correctamente asociadas a sus respectivos pagos para mantener la trazabilidad.</li>
                                            <li>Consulta regularmente el estado de las mensualidades para detectar morosidad o errores en la asignación.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>Este es un ejemplo del formulario donde se registran las mensualidades por apartamento:</p>
                                        <img src="recursos/img/ayuda/mensualidad/2.WEBP" alt="Formulario de mensualidades" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo es clave para garantizar una gestión ordenada de los cobros periódicos. Con él, el sistema mantiene al día el flujo de ingresos, asegurando transparencia, seguimiento y eficiencia en la administración financiera.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading6">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                        Módulo de Cartelera Virtual
                                    </button>
                                </h2>
                                <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Cartelera Virtual</strong> es el puente entre la administración y la comunidad. Aquí se publican anuncios, avisos y eventos que se muestran directamente en el inicio del sistema, funcionando como una ventana dinámica de información para todos los usuarios.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Crear publicaciones:</strong> Permite redactar anuncios con título, descripción, fecha y, opcionalmente, una imagen ilustrativa.</li>
                                            <li><strong>Visibilidad inmediata:</strong> Todo lo publicado en la cartelera se muestra en el <strong>inicio del sistema</strong>, asegurando que todos los usuarios lo vean apenas ingresen.</li>
                                            <li><strong>Edición flexible:</strong> Las publicaciones pueden modificarse en cualquier momento, actualizando contenido o imágenes sin perder el historial visual.</li>
                                            <li><strong>Eliminación controlada:</strong> Las publicaciones caducadas o innecesarias pueden eliminarse para mantener la cartelera limpia y relevante.</li>
                                            <li><strong>Orden cronológico:</strong> Las publicaciones se organizan por fecha, mostrando primero los eventos más recientes o importantes.</li>
                                            <li><strong>Estilo visual atractivo:</strong> Los mensajes pueden incluir íconos, resaltados y colores para destacar anuncios urgentes o especiales.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Usa títulos breves y llamativos que capturen la atención rápidamente.</li>
                                            <li>Incluye siempre una fecha de publicación o evento para contextualizar la información.</li>
                                            <li>Evita saturar la cartelera: publica solo lo necesario y actualiza con regularidad.</li>
                                            <li>Las imágenes refuerzan el mensaje: usa gráficos claros, formatos livianos y dimensiones adecuadas.</li>
                                            <li>Recuerda que esta es la primera impresión del sistema. Haz que cada publicación cuente.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>A continuación, un ejemplo de cómo se crea una nueva publicación en la cartelera:</p>
                                        <img src="recursos/img/ayuda/cartelera/2.WEBP" alt="Formulario de publicación en cartelera" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>El módulo de cartelera es más que un tablón de anuncios: es el alma comunicacional del sistema. Con él, mantienes informada a toda la comunidad, fomentas la participación y aseguras que nada importante pase desapercibido.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading7">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                        Módulo de Apartamentos
                                    </button>
                                </h2>
                                <div id="collapse7" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Apartamentos</strong> permite gestionar toda la información estructural y operativa de las unidades residenciales del conjunto. Cada apartamento se define no solo por su número, sino también por su funcionalidad, servicios y relaciones con los propietarios y habitantes.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro completo del apartamento:</strong> Incluye número o identificador, estado de ocupación (<em>propio</em> o <em>alquilado</em>), y su porcentaje de participación en el cálculo de mensualidades.</li>
                                            <li><strong>Servicios activos:</strong> Permite marcar si el apartamento cuenta con servicios como <strong>agua</strong> o <strong>gas</strong>, para su control y visualización rápida.</li>
                                            <li><strong>Vista previa detallada:</strong> Desde esta opción puedes consultar información clave del apartamento, incluyendo el <strong>propietario</strong> y todos los <strong>habitantes</strong> asociados.</li>
                                            <li><strong>Modificación de datos:</strong> Actualiza el estado de ocupación, servicios activos o porcentaje de participación cuando ocurran cambios.</li>
                                            <li><strong>Eliminación segura:</strong> Solo se permite eliminar un apartamento si no está vinculado a pagos, mensualidades u otros módulos dependientes.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Actualiza la condición del apartamento (alquilado o propio) cuando cambie el contrato o el tipo de ocupación.</li>
                                            <li>Activa o desactiva los servicios de agua/gas según la disponibilidad, ya que estos afectan la facturación o asignación de recursos.</li>
                                            <li>Asegúrate de que el porcentaje de participación esté bien calculado, pues influye directamente en el monto mensual a pagar por el apartamento.</li>
                                            <li>Usa la vista previa para verificar de forma rápida quién es el dueño y quién vive en el apartamento, facilitando la gestión de incidencias o avisos.</li>
                                            <li>No elimines apartamentos activos; si necesitas excluirlo del sistema temporalmente, considera marcarlo como inactivo si tu sistema lo permite.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>Así se muestra el formulario de registro/modificación de un apartamento:</p>
                                        <img src="recursos/img/ayuda/apartamentos/vista.png" alt="Formulario de apartamento" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo es esencial para llevar un control ordenado y transparente de la infraestructura del conjunto residencial. Desde el gas que fluye hasta el nombre que figura en el buzón, aquí todo queda registrado.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading8">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                        Módulo de Habitantes
                                    </button>
                                </h2>
                                <div id="collapse8" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        <p><b class="text-primary">Este módulo se encuentra dentro del módulo de apartamentos</b></p>
                                        El <strong>Módulo de Habitantes</strong> reúne y gestiona la información personal de todos los individuos registrados en el sistema. Ya sean propietarios, habitantes, encargados o cualquier otro perfil, este módulo permite mantener actualizados sus datos de contacto, identidad y relaciones con los apartamentos.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro de personas:</strong> Agrega personas al sistema ingresando datos como cédula, nombre, apellido, teléfono, correo y dirección.</li>
                                            <li><strong>Clasificación por rol:</strong> Permite identificar si una persona es <em>propietario</em> o <em>habitante</em> dentro del conjunto.</li>
                                            <li><strong>Vinculación con apartamentos:</strong> Relaciona cada persona con uno o varios apartamentos según su función.</li>
                                            <li><strong>Edición de datos:</strong> Puedes modificar cualquier información personal en caso de cambios o correcciones.</li>
                                            <li><strong>Vista previa detallada:</strong> Con un solo clic puedes visualizar todos los datos de la persona en una tarjeta detallada y ordenada.</li>
                                            <li><strong>Eliminación con seguridad:</strong> Solo se permite eliminar a una persona si no está vinculada a registros críticos como pagos o mensualidades.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Usa la <strong>vista previa</strong> para revisar rápidamente todos los datos de una persona sin necesidad de entrar al formulario de modificación.</li>
                                            <li>Verifica que la cédula esté bien escrita al momento del registro para evitar duplicados o confusiones.</li>
                                            <li>Si una persona ocupa múltiples roles (por ejemplo, propietario y habitante), asegúrate de vincular correctamente cada uno.</li>
                                            <li>Recuerda actualizar teléfonos o correos si los residentes cambian de contacto, así garantizas una comunicación fluida.</li>
                                            <li>No elimines personas que tengan historial de pagos o transacciones asociadas; el sistema te avisará si esto ocurre.</li>
                                        </ul>

                                        <h5 class="mt-3">📷 Ejemplo visual:</h5>
                                        <p>Vista previa de una persona registrada en el sistema:</p>
                                        <img src="recursos/img/ayuda/apartamentos/vista_habitante.png" alt="Vista previa de persona" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo es el núcleo humano del sistema. Desde los propietarios hasta los visitantes frecuentes, aquí se construye la red de relaciones que da vida al conjunto residencial.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading9">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                        Módulo de Solicitud de Gastos
                                    </button>
                                </h2>
                                <div id="collapse9" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Solicitud de Gastos</strong> permite a los usuarios del sistema <strong>reportar necesidades económicas</strong> que aún no han sido ejecutadas, pero que son necesarias para el mantenimiento, funcionamiento o mejora del conjunto residencial.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registrar solicitudes:</strong> Se puede ingresar una nueva solicitud indicando el concepto del gasto, el motivo y el área o finalidad para la cual se requiere.</li>
                                            <li><strong>Control de propuestas:</strong> Todas las solicitudes quedan registradas con su fecha, detalle y estado, para evaluación por parte del equipo administrativo.</li>
                                            <li><strong>Evaluación interna:</strong> La administración podrá revisar cada propuesta, decidir si se aprueba, se posterga o se descarta, con base en el presupuesto y prioridades.</li>
                                            <li><strong>Transparencia:</strong> Este módulo permite dejar constancia de cada gasto sugerido, promoviendo una gestión ordenada y participativa.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Especifica claramente el motivo del gasto y su importancia. Cuanto más claro seas, más fácil será su aprobación.</li>
                                            <li>No se necesita adjuntar comprobantes ni imágenes, ya que este módulo es solo para registrar solicitudes, no gastos ejecutados.</li>
                                            <li>Evita repetir solicitudes que ya estén registradas. Puedes revisar la lista antes de enviar una nueva.</li>
                                            <li>Este módulo no ejecuta gastos directamente. Solo informa y deja constancia de la necesidad de invertir.</li>
                                            <li>Usa este recurso con responsabilidad. Cada solicitud impacta en el presupuesto general del conjunto.</li>
                                        </ul>

                                        <h5 class="mt-3">📋 Ejemplo visual:</h5>
                                        <p>Así luce el formulario para registrar una nueva solicitud de gasto:</p>
                                        <img src="recursos/img/ayuda/solicitud_gasto/formulario.png" alt="Formulario de solicitud de gasto" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo fortalece la planificación financiera y la participación colectiva. Porque cada gasto comienza con una idea, y cada idea merece ser escuchada.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading10">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                        Módulo de Presupuesto Mensual
                                    </button>
                                </h2>
                                <div id="collapse10" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Presupuesto Mensual</strong> permite definir, controlar y hacer seguimiento al <strong>presupuesto estimado</strong> para cada mes, estableciendo límites de gasto y metas financieras para el conjunto residencial. Es una herramienta clave para anticipar necesidades y evitar desequilibrios económicos.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro de presupuesto:</strong> Permite ingresar un presupuesto mensual indicando el monto total disponible para gastos en un periodo determinado.</li>
                                            <li><strong>Distribución del presupuesto:</strong> Se puede asignar parte del presupuesto a categorías específicas como mantenimiento, servicios, imprevistos o mejoras.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Registra el presupuesto a inicios de cada mes para tener un control desde el día uno.</li>
                                            <li>Consulta el saldo restante antes de aprobar nuevos gastos. Evitarás sobrepasos innecesarios.</li>
                                            <li>Usa categorías claras en la distribución del presupuesto para facilitar el análisis de fin de mes.</li>
                                            <li>Revisa la ejecución presupuestaria del mes anterior para ajustar el nuevo presupuesto con base en la realidad.</li>
                                            <li>Un presupuesto bien definido es la brújula de toda administración responsable.</li>
                                        </ul>

                                        <h5 class="mt-3">📈 Ejemplo visual:</h5>
                                        <p>A continuación, una imagen representativa del módulo de presupuesto mensual:</p>
                                        <img src="recursos/img/ayuda/presupuesto_mensual/vista_inicial.png" alt="Formulario de presupuesto mensual" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo brinda una visión clara de la capacidad financiera del conjunto, promoviendo una gestión estratégica, ordenada y eficiente. Porque no se trata solo de gastar, sino de planificar con inteligencia.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading11">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                        Módulo de Año Fiscal
                                    </button>
                                </h2>
                                <div id="collapse11" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Año Fiscal</strong> permite definir y gestionar los ciclos fiscales del sistema, estableciendo claramente las <strong>fechas de inicio y cierre</strong> de cada ejercicio contable. Esta funcionalidad es esencial para organizar los periodos financieros, generar reportes exactos y asegurar una contabilidad ordenada.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Definición de año fiscal:</strong> Permite registrar un nuevo año fiscal con su fecha de inicio y su fecha de cierre, delimitando el periodo válido para operaciones contables.</li>
                                            <li><strong>Bloqueo automático de operaciones:</strong> Una vez que el año fiscal se cierra, el sistema impide registrar nuevos pagos, gastos o presupuestos dentro de ese periodo cerrado.</li>
                                            <li><strong>Consulta de periodos:</strong> Se puede revisar el historial de años fiscales anteriores y sus fechas, útil para auditorías o análisis comparativos.</li>
                                            <li><strong>Asociación automática:</strong> Todas las operaciones (pagos, gastos, presupuestos) se vinculan automáticamente al año fiscal vigente al momento del registro.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Establece el año fiscal apenas comience la gestión administrativa. No lo dejes pasar.</li>
                                            <li>Evita modificar las fechas una vez haya comenzado la ejecución del año fiscal, salvo casos extraordinarios.</li>
                                            <li>Cierra el año fiscal únicamente cuando estés seguro de que no se registrarán más movimientos en ese periodo.</li>
                                            <li>Si vas a iniciar un nuevo ciclo, asegúrate de que el anterior esté completamente cerrado.</li>
                                        </ul>

                                        <h5 class="mt-3">📆 Ejemplo visual:</h5>
                                        <p>A continuación, una imagen representativa del formulario de año fiscal:</p>
                                        <img src="recursos/img/ayuda/anio_fiscal/formulario.png" alt="Formulario de año fiscal" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>El módulo de año fiscal garantiza un control riguroso y profesional de los periodos contables, alineando todas las funciones del sistema con los ciclos administrativos vigentes. Donde empieza el orden, comienza la claridad financiera.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading12">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                        Módulo de Reportes
                                    </button>
                                </h2>
                                <div id="collapse12" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Reportes</strong> es la herramienta clave para analizar y visualizar la información financiera y operativa del sistema mediante informes claros y detallados. Permite generar reportes estadísticos y documentos PDF que facilitan la toma de decisiones estratégicas y la transparencia administrativa.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Reportes estadísticos:</strong> Genera gráficos y tablas dinámicas que resumen el comportamiento de pagos, gastos, presupuestos y otros indicadores relevantes.</li>
                                            <li><strong>Exportación a PDF:</strong> Permite descargar reportes formateados en PDF, facilitando la impresión, almacenamiento o envío a terceros.</li>
                                            <li><strong>Filtros avanzados:</strong> Los usuarios pueden seleccionar rangos de fechas, tipos de reportes, estados y otros criterios para personalizar la información mostrada.</li>
                                            <li><strong>Programación de reportes:</strong> Posibilidad de programar envíos automáticos periódicos por correo electrónico para mantener informado al equipo administrativo.</li>
                                            <li><strong>Visualización interactiva:</strong> La interfaz permite explorar datos con zoom, detalle y comparación entre periodos.</li>
                                        </ul>

                                        <h5 class="mt-3">🧠 Consejos y recomendaciones:</h5>
                                        <ul>
                                            <li>Antes de generar reportes, asegúrate de seleccionar correctamente los filtros para obtener información precisa y útil.</li>
                                            <li>Utiliza los reportes PDF para respaldar auditorías o presentaciones ante la junta administrativa.</li>
                                            <li>Revisa periódicamente los reportes estadísticos para detectar tendencias o posibles anomalías financieras.</li>
                                            <li>Programa envíos automáticos para ahorrar tiempo y mantener siempre actualizada a la gerencia.</li>
                                        </ul>

                                        <h5 class="mt-3">📊 Ejemplo visual:</h5>
                                        <p>A continuación, una imagen representativa del panel de reportes con opciones de gráficos y exportación:</p>
                                        <img src="recursos/img/ayuda/reportes/vista_reportes_habitantes.png" alt="Panel de reportes" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                        <img src="recursos/img/ayuda/reportes/vista_reportes_ingresos_egresos.png" alt="Panel de reportes" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                        <img src="recursos/img/ayuda/reportes/grafico_habitantes.png" alt="Panel de reportes" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">

                                        <br><br>
                                        <p>Este módulo potencia la capacidad analítica y de gestión del sistema, convirtiendo datos en conocimiento y decisiones acertadas. Con reportes bien hechos, el control financiero se vuelve una sinfonía ordenada y clara.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingConfig">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConfig" aria-expanded="false" aria-controls="collapseConfig">
                                        Módulo de Configuración
                                    </button>
                                </h2>
                                <div id="collapseConfig" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Configuración</strong> permite registrar los bancos del condominio para relacionarlos con pagos en el módulo de pagos, así como gestionar proveedores y tipos de gastos del condominio.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro de bancos:</strong> Añade y administra los bancos que se utilizarán para relacionar pagos.</li>
                                            <li><strong>Gestión de proveedores:</strong> Registra proveedores que proveen servicios o productos al condominio.</li>
                                            <li><strong>Tipos de gastos:</strong> Define y administra las categorías de gastos para clasificar correctamente cada gasto.</li>
                                        </ul>

                                        <h5 class="mt-3">⚙️ Ejemplo visual:</h5>
                                        <p>Vistas simples para gestionar bancos, proveedores y tipos de gastos en el sistema.</p>
                                        <img src="recursos/img/ayuda/bancos/vista.png" alt="Panel de configuración" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                        <img src="recursos/img/ayuda/proveedores/vista.png" alt="Panel de configuración" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                        <img src="recursos/img/ayuda/tipo_gasto/vista.png" alt="Panel de configuración" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingUsuarios">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                                        Módulo de Usuarios
                                    </button>
                                </h2>
                                <div id="collapseUsuarios" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Usuarios</strong> permite gestionar las cuentas de los usuarios del sistema, con la capacidad de asignar roles específicos para controlar accesos y permisos.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Registro y administración:</strong> Crear, modificar y eliminar usuarios con datos personales y credenciales.</li>
                                            <li><strong>Asignación de roles:</strong> Definir roles como administrador, cajero, supervisor, entre otros, para controlar los permisos dentro del sistema.</li>
                                            <li><strong>Control de accesos:</strong> Los roles determinan qué módulos y funciones puede usar cada usuario.</li>
                                            <li><strong>Seguridad:</strong> Gestión segura de contraseñas y opciones de recuperación.</li>
                                        </ul>

                                        <h5 class="mt-3">⚙️ Ejemplo visual:</h5>
                                        <p>Interfaz para administrar usuarios y asignar roles en el sistema.</p>
                                        <img src="recursos/img/ayuda/usuarios/2.WEBP" alt="Panel de usuarios" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSeguridad">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeguridad" aria-expanded="false" aria-controls="collapseSeguridad">
                                        Módulo de Seguridad
                                    </button>
                                </h2>
                                <div id="collapseSeguridad" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Seguridad</strong> es la fortaleza del sistema. Aquí se gestiona todo lo relacionado con los roles de usuario y el control exhaustivo de las actividades mediante la bitácora.

                                        <br><br>

                                        <h5 class="mt-3">📌 Funciones principales del módulo:</h5>
                                        <ul>
                                            <li><strong>Gestión de roles:</strong> Permite crear, modificar y eliminar roles personalizados que definen permisos y accesos específicos a las funcionalidades del sistema. Esto garantiza que cada usuario solo pueda interactuar con lo que realmente debe.</li>
                                            <li><strong>Bitácora de actividades:</strong> Registra cada acción que realizan los usuarios dentro del sistema. Desde inicios de sesión hasta modificaciones de datos críticos, todo queda registrado para auditoría y seguimiento.</li>
                                            <li><strong>Control y supervisión:</strong> Facilita la detección de comportamientos anómalos o intentos de acceso indebidos, brindando transparencia y seguridad absoluta en el manejo del sistema.</li>
                                        </ul>

                                        <h5 class="mt-3">🔐 Importancia y recomendaciones:</h5>
                                        <ul>
                                            <li>Configura los roles con precisión para evitar accesos no autorizados.</li>
                                            <li>Revisa periódicamente la bitácora para monitorear la actividad y mantener la integridad del sistema.</li>
                                            <li>Utiliza esta herramienta como base para auditorías internas y cumplimiento de políticas de seguridad.</li>
                                        </ul>

                                        <h5 class="mt-3">🛡️ Ejemplo visual:</h5>
                                        <p>Vistas para administrar roles y visualizar la bitácora de actividades del sistema.</p>
                                        <img src="recursos/img/ayuda/roles/vista.png" alt="Panel de seguridad" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                        <img src="recursos/img/ayuda/bitacora/vista.png" alt="Panel de seguridad" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingNotificaciones">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotificaciones" aria-expanded="false" aria-controls="collapseNotificaciones">
                                        Módulo de Notificaciones
                                    </button>
                                </h2>
                                <div id="collapseNotificaciones" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Notificaciones</strong> es el sistema que avisa, alerta y deja registro de eventos importantes dentro del sistema.  
                                        <br><br>
                                        Solo notifica y guarda el historial para que nada quede en el aire.
                                        <br><br>
                                        <h5 class="mt-3">📷 Visual:</h5>
                                        <img src="recursos/img/ayuda/notificaciones/vista.png" alt="Panel de notificaciones" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMantenimiento">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMantenimiento" aria-expanded="false" aria-controls="collapseMantenimiento">
                                        Módulo de Mantenimiento
                                    </button>
                                </h2>
                                <div id="collapseMantenimiento" class="accordion-collapse collapse" data-bs-parent="#ayudaAccordion">
                                    <div class="accordion-body">
                                        El <strong>Módulo de Mantenimiento</strong> es la herramienta que asegura la supervivencia del sistema.  
                                        <br><br>
                                        Aquí solo puedes hacer dos cosas: exportar una copia de seguridad de la base de datos o importar una copia previamente guardada.  
                                        <br><br>
                                        Nada más. Sin misterios, sin distracciones.
                                        <br><br>
                                        <h5 class="mt-3">📷 Visual:</h5>
                                        <img src="recursos/img/ayuda/mantenimiento/vista.png" alt="Formulario de mantenimiento" class="img-fluid rounded shadow-sm" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>



                            <!-- Agrega más muchachos ya me quede sin ideas xd -->

                        </div>
                    </div>
                </main>

                <?php
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="recursos/js/ayuda.js"></script>
</body>

</html>