const buscador = document.getElementById("buscador");
const items = document.querySelectorAll(".accordion-item");

// Guardamos contenido original para restaurar
items.forEach(item => {
    const boton = item.querySelector(".accordion-button");
    const cuerpo = item.querySelector(".accordion-body");
    if (boton) boton.dataset.original = boton.innerHTML;
    if (cuerpo) cuerpo.dataset.original = cuerpo.innerHTML;
});

// Normaliza texto (quita acentos, minúsculas y trim)
function normalizar(texto) {
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
}

// Limpia resaltado previo y restaurar contenido original
function limpiarResaltadoYRestaurar(elemento) {
    if (!elemento) return;
    if (elemento.dataset.original) {
        elemento.innerHTML = elemento.dataset.original;
    }
}

// Resalta coincidencias recursivamente
function resaltarTexto(elemento, palabras) {
    if (!elemento || palabras.length === 0) return;
    const regex = new RegExp(`(${palabras.map(p => escapeRegex(p)).join('|')})`, "gi");

    elemento.childNodes.forEach(nodo => {
        if (nodo.nodeType === 3) {
            const contenido = nodo.textContent;
            if (regex.test(contenido)) {
                const nuevoHTML = contenido.replace(regex, '<mark>$1</mark>');
                const span = document.createElement("span");
                span.innerHTML = nuevoHTML;
                nodo.replaceWith(span);
            }
        } else if (nodo.nodeType === 1 && nodo.tagName !== "MARK") {
            resaltarTexto(nodo, palabras);
        }
    });
}

function escapeRegex(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

buscador.addEventListener("input", () => {
    const textoBusquedaOriginal = buscador.value.trim();
    const palabrasRaw = textoBusquedaOriginal.split(/\s+/).filter(Boolean);
    const palabras = palabrasRaw.map(normalizar);
    let encontrados = 0;
    let primerCoincidencia = null;

    items.forEach(item => {
        const boton = item.querySelector(".accordion-button");
        const cuerpo = item.querySelector(".accordion-body");
        const collapseEl = item.querySelector(".accordion-collapse");

        // Restaurar contenido original para evitar acumulación de marcas
        limpiarResaltadoYRestaurar(boton);
        limpiarResaltadoYRestaurar(cuerpo);

        const textoBoton = boton ? normalizar(boton.innerText) : "";
        const textoCuerpo = cuerpo ? normalizar(cuerpo.innerText) : "";

        const cumpleBusqueda = palabras.every(p => textoBoton.includes(p) || textoCuerpo.includes(p));

        if (textoBusquedaOriginal === "") {
            // Mostrar todo y cerrar acordeones sin resaltado
            item.style.display = "";
            const collapse = bootstrap.Collapse.getInstance(collapseEl);
            if (collapse) collapse.hide();
        } else if (cumpleBusqueda) {
            item.style.display = "";
            encontrados++;

            const collapse = bootstrap.Collapse.getInstance(collapseEl);
            if (!collapse) {
                new bootstrap.Collapse(collapseEl, { toggle: true });
            } else {
                collapse.show();
            }

            if (boton) resaltarTexto(boton, palabras);
            if (cuerpo) resaltarTexto(cuerpo, palabras);

            if (!primerCoincidencia && cuerpo && palabras.some(p => cuerpo.innerText.toLowerCase().includes(p))) {
                primerCoincidencia = item;
            }
        } else {
            item.style.display = "none";
            const collapse = bootstrap.Collapse.getInstance(collapseEl);
            if (collapse) collapse.hide();
        }
    });

    if (primerCoincidencia) {
        setTimeout(() => {
            primerCoincidencia.scrollIntoView({ behavior: "smooth", block: "center" });
        }, 250);
    }

    let mensaje = document.getElementById("mensaje-no-encontrado");
    if (!mensaje) {
        mensaje = document.createElement("div");
        mensaje.id = "mensaje-no-encontrado";
        mensaje.classList.add("alert", "alert-warning", "mt-3");
        mensaje.style.display = "none";
        buscador.parentNode.appendChild(mensaje);
    }

    if (textoBusquedaOriginal !== "" && encontrados === 0) {
        mensaje.textContent = "🔍 No se encontraron coincidencias.";
        mensaje.style.display = "block";
    } else {
        mensaje.style.display = "none";
    }
});
