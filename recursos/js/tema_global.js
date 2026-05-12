document.addEventListener('DOMContentLoaded', () => {
    inicializarSwitchModoOscuro();
});

// Función para aplicar el tema al elemento <html>
function aplicarTema(tema) {
    document.documentElement.setAttribute('data-bs-theme', tema);
}

function inicializarSwitchModoOscuro() {
    const toggleTema = document.getElementById('checkbox_tema');

    if (!toggleTema) return; // Si no estamos en perfil, no hacemos nada

    const temaActual = localStorage.getItem('temaSistemaHaydee') || 'light';
    toggleTema.checked = (temaActual === 'dark');

    let icono = document.getElementById('thumb_icon');
    if (icono) icono.setAttribute("class",`bi bi-${temaActual === 'dark'?'sun':'moon'} text-${temaActual === 'dark'?'warning':'info'}`);

    toggleTema.addEventListener('change', function(e) {
        const esOscuro = e.target.checked;
        const nuevoTema = esOscuro ? 'dark' : 'light';

        const icon = document.getElementById('thumb_icon');
        if (icon) icon.setAttribute("class",`bi bi-${esOscuro?'sun':'moon'} text-${esOscuro?'warning':'info'}`);

        localStorage.setItem('temaSistemaHaydee', nuevoTema);

        aplicarTema(nuevoTema);
    });
}