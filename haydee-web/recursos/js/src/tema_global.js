document.addEventListener('DOMContentLoaded', () => {
    inicializarSwitchModoOscuro();
});

// Función para aplicar el tema al elemento <html>
function aplicarTema(tema) {
    document.documentElement.setAttribute('data-bs-theme', tema);
}

function inicializarSwitchModoOscuro() {
    const togglesTema = document.querySelectorAll('.toggle-tema-global');

    if (togglesTema.length === 0) return;

    const temaActual = localStorage.getItem('temaSistemaHaydee') || 'light';
    const esOscuroInicial = (temaActual === 'dark');

    // Función interna para actualizar la interfaz de un switch individual
    const actualizarUI = (toggle, esOscuro) => {
        const wrapper = toggle.closest(".theme-switch-wrapper");
        const titulo = esOscuro ? 'Cambiar a modo Claro' : 'Cambiar a modo Noche';
        
        wrapper.title = titulo;
        if (typeof Tooltips !== 'undefined') {
            Tooltips.actualizarDinamicamente(wrapper, titulo);
        }

        const icon = wrapper.querySelector('.thumb-icon-global');
        if (icon) {
            // Evaluamos si es el ícono del header para mantener su tamaño y margen
            const clasesExtra = toggle.id === 'checkbox_tema_header' ? 'me-2 fs-5' : esOscuro ? '' : 'text-white';
            icon.setAttribute("class", `bi bi-${esOscuro ? 'sun' : 'moon'} ${esOscuro? 'text-warning' :''} thumb-icon-global ${clasesExtra}`);
        }

        // Si estamos en el perfil, actualizamos los textos externos
        if (toggle.id === 'checkbox_tema_perfil') {
            const textoTema = document.getElementById('texto_tema');
            if (textoTema) textoTema.textContent = esOscuro ? 'Oscuro' : 'Claro';
            
            const iconoTema = document.getElementById('icono_tema');
            if (iconoTema) iconoTema.setAttribute("class", `bi bi-${esOscuro ? 'moon' : 'sun'} fs-4`);
            // if (icon) {}
        }
    };

    // Inicializamos todos los switches con el estado guardado
    togglesTema.forEach(toggle => {
        toggle.checked = esOscuroInicial;
        actualizarUI(toggle, esOscuroInicial);

        toggle.addEventListener('change', function(e) {
            const esOscuro = e.target.checked;
            const nuevoTema = esOscuro ? 'dark' : 'light';

            localStorage.setItem('temaSistemaHaydee', nuevoTema);
            aplicarTema(nuevoTema);

            togglesTema.forEach(t => {
                t.checked = esOscuro;
                actualizarUI(t, esOscuro);
            });
        });

        if (toggle.id === 'checkbox_tema_header') {
            const filaHeader = toggle.closest('.fila-switch-header');
            if (filaHeader) {
                filaHeader.addEventListener('click', function(e) {
                    if (e.target === toggle) return;

                    e.stopPropagation();

                    toggle.checked = !toggle.checked;
                    
                    toggle.dispatchEvent(new Event('change'));
                });
            }
        }
    });
}