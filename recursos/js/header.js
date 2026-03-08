document.addEventListener("DOMContentLoaded", function () {
    // 1. CAPTURAMOS LOS ELEMENTOS DEL DOM
    const toggle = document.getElementById('header-toggle');
    const nav = document.getElementById('nav-bar');
    const bodypd = document.getElementById('body-pd') || document.body;
    const headerpd = document.getElementById('header');
    const enlaces = document.querySelectorAll(".collapse a");

    // --- NUEVO: ALMACÉN DE TOOLTIPS DEL MENÚ ---
    let tooltipsMenu = [];

    // 2. INICIALIZAR TOOLTIPS DE BOOTSTRAP (Lo subimos para usarlo globalmente aquí)
    if (typeof bootstrap !== 'undefined') {
        const navLinks = document.querySelectorAll('.nav_link, .nav_logo');
        const tooltipConfig = { placement: 'right', animation: true, trigger: 'hover' };

        navLinks.forEach(link => {
            if (link.hasAttribute('title')) {
                // Guardamos la instancia de cada tooltip del menú
                const inst = new bootstrap.Tooltip(link, tooltipConfig);
                tooltipsMenu.push(inst);
            }
        });
    }

    // --- NUEVO: Función para prender/apagar SOLO los tooltips del menú ---
    const manejarTooltipsMenu = (menuExpandido) => {
        tooltipsMenu.forEach(t => {
            if (menuExpandido) t.disable();  // Los apaga si el menú está abierto
            else t.enable();                 // Los enciende si está contraído
        });
    };

    // 3. INICIALIZACIÓN MÓVIL
    if (window.innerWidth < 769 && nav) {
        nav.classList.remove('show');
        if (toggle) toggle.classList.remove('bi-x-lg');
        bodypd.classList.remove('body-pd');
        if (headerpd) headerpd.classList.remove('body-pd');

        enlaces.forEach(a => {
            a.classList.add('ps-2');
            a.parentElement.classList.remove('rounded', 'ms-4');
        });
    }

    // Ajustamos los tooltips del menú en la carga inicial
    if (nav) manejarTooltipsMenu(nav.classList.contains('show'));

    // 4. LÓGICA DE APERTURA Y CIERRE (TOGGLE)
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('show');
            toggle.classList.toggle('bi-x-lg');
            bodypd.classList.toggle('body-pd');
            if (headerpd) headerpd.classList.toggle('body-pd');

            // Actualizamos los tooltips al hacer click
            manejarTooltipsMenu(nav.classList.contains('show'));
        });
    }

    // 5. CERRAR AL HACER CLIC AFUERA (Solo móviles)
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && nav && nav.classList.contains('show')) {
            if (!nav.contains(e.target) && toggle && !toggle.contains(e.target)) {
                nav.classList.remove('show');
                toggle.classList.remove('bi-x-lg');
                bodypd.classList.remove('body-pd');
                if (headerpd) headerpd.classList.remove('body-pd');
                
                // Al cerrarse el menú, encendemos los tooltips nuevamente
                manejarTooltipsMenu(false); 
            }
        }
    });

    // 6. AUTO-SCROLL AL MÓDULO ACTIVO
    const activeLink = document.querySelector('.nav_link.active');
    if (activeLink && nav) {
        nav.scrollTo({
            top: activeLink.offsetTop - 50,
            behavior: 'smooth'
        });
    }
});