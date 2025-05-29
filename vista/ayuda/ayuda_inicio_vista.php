<!DOCTYPE html>
<html>

<head>
    <title>Manual Interactivo | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php"; ?>
</head>
<style>
    .step {
        display: none;
    }

    .step.active {
        display: block;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        background-color: #fff;
        /* antes estaba con transparencia */
    }

    .card h5 {
        font-weight: bold;
        color: #343a40;
    }

    .card p {
        color: #212529;
        font-size: 1.05rem;
    }
</style>

<body class="body-pd">

    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2">
                    <div class="page-header pt-3">
                        <h1 class="mb-4 text-center">Manual Interactivo del Sistema</h1>
                        <div class="card p-4" id="manualSteps">

                            <!-- Los pasos se generan aquí -->

                        </div>
                    </div>

                    <script>
    const modulos = [
        {
            nombre: "PAGOS",
            descripcion: "Este módulo permite gestionar funcionalidades relacionadas con pagos.",
            imagenes: [
                { src: "recursos/img/ayuda/pagos/1.webp", texto: "Vista principal del módulo de pagos. También puedes ver los botones para editar y eliminar algún pago." },
                { src: "recursos/img/ayuda/pagos/2.webp", texto: "Formulario para registrar un nuevo pago." }
            ]
        },
        {
            nombre: "GASTOS",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con gastos.",
            imagenes: [
                { src: "recursos/img/ayuda/gastos/1.webp", texto: "Vista principal del módulo de gastos. Puedes seleccionar el mes del que deseas ver los gastos y un resumen de gastos." },
                { src: "recursos/img/ayuda/gastos/2.webp", texto: "Formulario para registrar un nuevo gasto." }
            ]
        },
        {
            nombre: "CONCILIACIÓN BANCARIA",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con conciliación bancaria.",
            imagenes: [
                { src: "recursos/img/ayuda/conciliacion/1.webp", texto: "Vista principal del módulo de conciliación bancaria. Tambien puedes ver el resumen de conciliación bancaria." },
                { src: "recursos/img/ayuda/conciliacion/2.webp", texto: "Formulario para registrar una nueva conciliación bancaria." }
            ]
        },
        {
            nombre: "MENSUALIDAD",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con mensualidad.",
            imagenes: [
                { src: "recursos/img/ayuda/mensualidad/1.webp", texto: "Vista principal del módulo de mensualidad. Tambien puedes ver los botones para editar y eliminar alguna mensualidad." },
                { src: "recursos/img/ayuda/mensualidad/2.webp", texto: "Formulario para registrar una nueva mensualidad." }
            ]
        },
        {
            nombre: "CARTELERA VIRTUAL",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con la cartelera virtual.",
            imagenes: [
                { src: "recursos/img/ayuda/cartelera/1.webp", texto: "Vista principal del módulo de cartelera virtual. Tambien puedes ver ." },
                { src: "recursos/img/ayuda/cartelera/2.webp", texto: "Formulario para registrar un nuevo usuario." }
            ]
        },
        {
            nombre: "HABITANTES",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con habitantes.",
            imagenes: [
                { src: "recursos/img/ayuda/habitantes/1.webp", texto: "Vista principal del módulo de habitantes." },
                { src: "recursos/img/ayuda/habitantes/2.webp", texto: "Formulario para registrar un nuevo habitante." }
            ]
        },
        {
            nombre: "PROPIETARIOS",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con propietarios.",
            imagenes: [
                { src: "recursos/img/ayuda/propietarios/1.webp", texto: "Vista principal del módulo de propietarios." },
                { src: "recursos/img/ayuda/propietarios/2.webp", texto: "Formulario para registrar un nuevo propietario." }
            ]
        },
        {
            nombre: "CONFIGURACIÓN",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con configuración.",
            imagenes: [
                { src: "recursos/img/ayuda/configuracion/1.webp", texto: "Vista principal del módulo de configuración." }
            ]
        },
        {
            nombre: "USUARIOS",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con usuarios.",
            imagenes: [
                { src: "recursos/img/ayuda/usuarios/1.webp", texto: "Vista principal del módulo de usuarios." },
                { src: "recursos/img/ayuda/usuarios/2.webp", texto: "Formulario para registrar un nuevo usuario." }
            ]
        },
        {
            nombre: "SEGURIDAD",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con seguridad.",
            imagenes: [
                { src: "recursos/img/ayuda/seguridad/1.webp", texto: "Vista principal del módulo de seguridad." },
            ]
        },
        {
            nombre: "NOTIFICACIONES",
            descripcion: "Este módulo permite gestionar las funcionalidades relacionadas con notificaciones.",
            imagenes: [
                { src: "recursos/img/ayuda/notificaciones/1.webp", texto: "Vista principal del módulo de notificaciones." }
            ]
        }

    ];

    const container = document.getElementById('manualSteps');
    const imagenActual = [];

    modulos.forEach((modulo, index) => {
        imagenActual[index] = 0;

        const paso = document.createElement('div');
        paso.classList.add('step');
        if (index === 0) paso.classList.add('active');
        paso.id = `step${index + 1}`;

        paso.innerHTML = `
            <h2 class="text-primary"><i class="fas fa-cogs"></i> ${modulo.nombre}</h2>
            <h5 class="mb-3 text-dark fw-bold">Paso ${index + 1}: Información del módulo</h5>
            <p class="text-dark">${modulo.descripcion}</p>

            <div class="text-center mb-3">
                <img id="img-${index}" src="${modulo.imagenes[0].src}" class="img-fluid rounded shadow-sm" style="max-height: 300px;" />
                <p id="img-desc-${index}" class="mt-2">${modulo.imagenes[0].texto}</p>

                <div class="btn-group mt-2" role="group">
                    <button class="btn btn-outline-secondary img-prev" data-index="${index}">Anterior imagen</button>
                    <button class="btn btn-outline-secondary img-next" data-index="${index}">Siguiente imagen</button>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                ${index > 0
                    ? `<button class="btn btn-outline-secondary" onclick="goToStep(${index})"><i class='fas fa-arrow-left'></i> Anterior</button>`
                    : '<div></div>'}
                ${index < modulos.length - 1
                    ? `<button class="btn btn-primary" onclick="goToStep(${index + 2})">Siguiente <i class='fas fa-arrow-right'></i></button>`
                    : `<button class="btn btn-success" onclick="goToStep(1)"><i class='fas fa-flag-checkered'></i> Finalizar</button>`}
            </div>
        `;

        container.appendChild(paso);
    });

    function goToStep(stepNumber) {
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, i) => {
            step.classList.remove('active');
            if (i === stepNumber - 1) step.classList.add('active');
        });
    }

    // Manejar eventos con delegación (por si el DOM aún no tiene los botones)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('img-prev')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            cambiarImagen(index, -1);
        } else if (e.target.classList.contains('img-next')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            cambiarImagen(index, 1);
        }
    });

    function cambiarImagen(moduloIndex, direccion) {
        const total = modulos[moduloIndex].imagenes.length;
        imagenActual[moduloIndex] += direccion;

        if (imagenActual[moduloIndex] < 0) imagenActual[moduloIndex] = total - 1;
        if (imagenActual[moduloIndex] >= total) imagenActual[moduloIndex] = 0;

        const nueva = modulos[moduloIndex].imagenes[imagenActual[moduloIndex]];
        document.getElementById(`img-${moduloIndex}`).src = nueva.src;
        document.getElementById(`img-desc-${moduloIndex}`).textContent = nueva.texto;
    }
</script>

                    <?php
                    require_once "vista/componentes/footer.php";
                    require_once "vista/componentes/script.php";
                    ?>

</body>

</html>