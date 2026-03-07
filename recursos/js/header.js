document.addEventListener("DOMContentLoaded", function () {
    // 1. CAPTURAMOS LOS ELEMENTOS DEL DOM
    const toggle = document.getElementById('header-toggle');
    const nav = document.getElementById('nav-bar');
    const bodypd = document.getElementById('body-pd') || document.body;
    const headerpd = document.getElementById('header');
    const enlaces = document.querySelectorAll(".collapse a");

    // 2. INICIALIZACIÓN MÓVIL (Asegura que el menú inicie cerrado en teléfonos)
    if (window.innerWidth < 769 && nav) {
        nav.classList.remove('show');
        if (toggle) toggle.classList.remove('bi-x-lg');
        bodypd.classList.remove('body-pd');
        if (headerpd) headerpd.classList.remove('body-pd');

        // Ajuste de márgenes para submenús en móvil
        enlaces.forEach(a => {
            a.classList.add('ps-2');
            a.parentElement.classList.remove('rounded', 'ms-4');
        });
    }

    // 3. LÓGICA DE APERTURA Y CIERRE (TOGGLE)
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('show');
            toggle.classList.toggle('bi-x-lg');
            bodypd.classList.toggle('body-pd');
            if (headerpd) headerpd.classList.toggle('body-pd');

            // Solo aplica clases de Bootstrap a submenús en escritorio
            if (window.innerWidth >= 769) {
                let id_submenu = '';
                enlaces.forEach(a => {
                    a.classList.toggle('ps-2');
                    if (id_submenu !== a.parentElement.id) {
                        a.parentElement.classList.toggle('rounded');
                        a.parentElement.classList.toggle('ms-4');
                        id_submenu = a.parentElement.id;
                    }
                });
            }
        });

        // 4. CERRAR AL HACER CLIC AFUERA (Solo móviles)
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 769 && nav.classList.contains('show')) {
                if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                    nav.classList.remove('show');
                    toggle.classList.remove('bi-x-lg');
                    bodypd.classList.remove('body-pd');
                    if (headerpd) headerpd.classList.remove('body-pd');
                }
            }
        });
    }

    // 5. INICIALIZAR TOOLTIPS DE BOOTSTRAP
    if (typeof bootstrap !== 'undefined') {
        const navLinks = document.querySelectorAll('.nav_link');
        const linkInicio = document.querySelector(".nav_logo");

        const tooltipConfig = { placement: 'right', animation: true, trigger: 'hover' };

        navLinks.forEach(link => {
            if (link.hasAttribute('title')) new bootstrap.Tooltip(link, tooltipConfig);
        });

        if (linkInicio && linkInicio.hasAttribute('title')) {
            new bootstrap.Tooltip(linkInicio, tooltipConfig);
        }
    }

    // 6. AUTO-SCROLL AL MÓDULO ACTIVO
    const activeLink = document.querySelector('.nav_link.active');
    if (activeLink) {
        setTimeout(() => {
            activeLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
});