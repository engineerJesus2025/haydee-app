// Parte de esto se pudo hacer con php pero bueno...
let contenido_principal = document.getElementById('contenido');
let limite = 0, fin = false;

window.addEventListener("scroll",e=>{
	const alturaPagina = document.documentElement.scrollHeight;
  	const alturaVentana = window.innerHeight;
  	const desplazamientoActual = window.scrollY;

  	if (desplazamientoActual + alturaVentana >= alturaPagina){
  		consultarPublicaciones();
  	}
});

async function consultarPublicaciones() {
	if (fin) {return}

	let datos_consulta = new FormData();

    datos_consulta.append("operacion", "consulta_inicio");
    datos_consulta.append("limite", limite);

    let data = await query(datos_consulta);

    if (data.length == 0) {
    	let div_no_hay = document.createElement("div");
    	div_no_hay.setAttribute("class","col-10 text-center my-2");
    	div_no_hay.textContent = "No hay mas resultados";

    	contenido_principal.appendChild(div_no_hay);
    	fin = true;
    	return;
    }
    let fragment = document.createDocumentFragment();
    data.map(publicacion=>{
    	let div_card = document.createElement("div");
	    div_card.setAttribute("class","card p-4 col-10 my-4");

	    let div_row = document.createElement("div");
	    div_row.setAttribute("class","row");

	    let div_col_1 = document.createElement("div");
	    div_col_1.setAttribute("class","col-7");

	    let h5 = document.createElement("h5");
	    h5.setAttribute("class","card-title");
	    h5.textContent = publicacion.titulo;

	    let p_fecha = document.createElement("p");
	    p_fecha.setAttribute("class","card-text");

	    let small_fecha = document.createElement("small");
	    small_fecha.setAttribute("class","text-body-secondary");
	    small_fecha.textContent = `Publicado el ${publicacion.fecha}`;

	    let p_contenido = document.createElement("p");
	    p_contenido.setAttribute("class","card-text");
	    p_contenido.textContent = publicacion.descripcion;

	    let p_usuario = document.createElement("p");
	    p_usuario.setAttribute("class","card-text");
	    p_usuario.textContent = `Publicado por ${publicacion.nombre_usuario}`;

	    let div_col_2 = document.createElement("div");
	    div_col_2.setAttribute("class","col-5");

	    if (publicacion.imagen != "") {
	    	let img = document.createElement("img");
			img.setAttribute("class","img-fluid rounded");
			img.setAttribute("src",`recursos/img/${publicacion.imagen}`);

			div_col_2.appendChild(img);
	    }

		p_fecha.appendChild(small_fecha);

		div_col_1.appendChild(h5);
		div_col_1.appendChild(p_fecha);
		div_col_1.appendChild(p_contenido);
		div_col_1.appendChild(p_usuario);

		div_row.appendChild(div_col_1);
		div_row.appendChild(div_col_2);

		div_card.appendChild(div_row);

		fragment.appendChild(div_card);
    });
    
    limite += 2;
	contenido_principal.appendChild(fragment);
}

async function query(datos) {
    let data = await fetch("", { method: "POST", body: datos }).then(res => {
        let result = res.json();
        return result;
    })
    return data;
}

consultarPublicaciones();