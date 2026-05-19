document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById("buscador");
    // Seleccionamos TODOS los items de acordeón de toda la página
    const items = document.querySelectorAll(".accordion-item");
    let primerCoincidencia = null;

    // Guardamos contenido original
    items.forEach(item => {
        const boton = item.querySelector(".accordion-button");
        const cuerpo = item.querySelector(".accordion-body");
        if (boton) boton.dataset.original = boton.innerHTML;
        if (cuerpo) cuerpo.dataset.original = cuerpo.innerHTML;
    });

    function normalizar(texto) {
        return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function limpiarResaltadoYRestaurar(elemento) {
        if (!elemento) return;
        if (elemento.dataset.original) {
            elemento.innerHTML = elemento.dataset.original;
        }
    }

    function resaltarTexto(elemento, palabras) {
        if (!elemento || palabras.length === 0) return;
        const regex = new RegExp(`(${palabras.map(p => escapeRegex(p)).join('|')})`, "gi");

        function procesarNodo(nodo) {
            if (nodo.nodeType === 3) { 
                const contenido = nodo.textContent;
                if (regex.test(contenido)) {
                    const span = document.createElement('span');
                    span.innerHTML = contenido.replace(regex, '<mark class="bg-warning text-dark rounded px-1">$1</mark>');
                    nodo.parentNode.replaceChild(span, nodo);
                }
            } else if (nodo.nodeType === 1 && nodo.tagName !== 'MARK') { 
                Array.from(nodo.childNodes).forEach(procesarNodo);
            }
        }
        Array.from(elemento.childNodes).forEach(procesarNodo);
    }

    buscador.addEventListener("input", (e) => {
        const textoBusquedaOriginal = e.target.value.trim();
        const textoBusqueda = normalizar(textoBusquedaOriginal);
        const palabras = textoBusquedaOriginal.split(/\s+/).filter(p => p.length > 0);
        
        let encontrados = 0;
        primerCoincidencia = null;

        const msgPrevio = document.getElementById("mensaje-no-encontrado");
        if (msgPrevio) msgPrevio.remove();

        items.forEach(item => {
            const boton = item.querySelector(".accordion-button");
            const cuerpo = item.querySelector(".accordion-body");
            const collapseEl = item.querySelector(".accordion-collapse");

            limpiarResaltadoYRestaurar(boton);
            limpiarResaltadoYRestaurar(cuerpo);

            if (textoBusqueda === "") {
                item.style.display = ""; 
                const collapse = bootstrap.Collapse.getInstance(collapseEl);
                if (collapse) collapse.hide(); 
                return;
            }

            const textoTitulo = normalizar(boton ? boton.textContent : "");
            const textoCuerpo = normalizar(cuerpo ? cuerpo.textContent : "");

            if (textoTitulo.includes(textoBusqueda) || textoCuerpo.includes(textoBusqueda)) {
                item.style.display = ""; 
                encontrados++;

                const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                collapse.show();

                if (boton) resaltarTexto(boton, palabras);
                if (cuerpo) resaltarTexto(cuerpo, palabras);

                if (!primerCoincidencia) primerCoincidencia = item;

            } else {
                item.style.display = "none"; 
            }
        });

        if (primerCoincidencia) {
            primerCoincidencia.scrollIntoView({ behavior: "smooth", block: "center" });
        }

        if (textoBusqueda !== "" && encontrados === 0) {
            const mensaje = document.createElement("div");
            mensaje.id = "mensaje-no-encontrado";
            mensaje.className = "alert alert-warning mt-3 text-center";
            mensaje.innerHTML = `<i class="bi bi-emoji-frown me-2"></i> No encontramos resultados para "<strong>${textoBusquedaOriginal}</strong>".`;
            buscador.parentElement.after(mensaje);
        }
    });
});