let contenido_principal = document.getElementById('contenido');
let limite = 0, fin = false;
let graficaChart_1;
let graficaChart_2;
let consultando = false
window.addEventListener("scroll",e=>{
	if (consultando) {return}
	const alturaPagina = document.documentElement.scrollHeight;
  	const alturaVentana = window.innerHeight;
  	const desplazamientoActual = window.scrollY;

  	if (desplazamientoActual + alturaVentana >= alturaPagina){
  		consultando = true;
  		consultarPublicaciones();
  	}
});

function formatearFecha(fecha) {
    if (!fecha) {
        return "N/A";
    }

    const partes = fecha.split("-");
    if (partes.length === 3) {
        return `${partes[2]}-${partes[1]}-${partes[0]}`; // DD-MM-AAAA
    }
    return fecha;
}

async function cargaInicio() {
	consultarPublicaciones();

	if (document.getElementById('div_graficos') == null) return;
	
	let datos_consulta = new FormData();

    datos_consulta.append("operacion", "consulta_inicio_grafico");

    let data = await query(datos_consulta);
	
    if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', data.mensaje);
		return;
	}

	const titulo_1 = document.createElement("h5"),
	titulo_2 = document.createElement("h5");

	titulo_1.classList.add("text-center");
	titulo_1.textContent = "Deudas de apartamentos";
	titulo_2.classList.add("text-center");
	titulo_2.textContent = "Resumen de balance del mes";

	document.getElementById("esqueleto_titulo_1").replaceWith(titulo_1);
	document.getElementById("esqueleto_titulo_2").replaceWith(titulo_2);

	const ctx_1 = document.getElementById('canva_1').getContext('2d');
	const ctx_2 = document.getElementById('canva_2').getContext('2d');

	if (!(data[0].valor == 0 && data[1].valor == 0) && !(data[0].valor == null && data[1].valor == null)) {
		const dato_1_1 = document.createElement("p"), b_dato_1_1 = document.createElement("b"),
		dato_2_1 = document.createElement("p"), b_dato_2_1 = document.createElement("b");

		dato_1_1.id = "b_moroso";
		dato_1_1.textContent = "Total de deuda de los apartamentos: " ;

		b_dato_1_1.classList.add("text-danger");
		b_dato_1_1.textContent = (data[0]?.valor?.toFixed(2) || 0) + " Bs.";

		dato_2_1.textContent = "Total de ingresos de los apartamentos: " ;
		dato_2_1.id = "b_solvente";

		b_dato_2_1.textContent = (data[1]?.valor?.toFixed(2) || 0) + " Bs.";
		b_dato_2_1.classList.add("text-success");

		dato_1_1.appendChild(b_dato_1_1);
		dato_2_1.appendChild(b_dato_2_1);

		document.getElementById('esqueleto_dato_1_1').replaceWith(dato_1_1);
		document.getElementById('esqueleto_dato_2_1').replaceWith(dato_2_1);

		graficaChart_1 = new Chart(ctx_1, {
		    type: 'pie',
		    data: {
		        labels: [`Apartamentos Morosos: ${data[0].accion}`,`Apartamentos sin deudas: ${data[1].accion}`],
		        datasets: [{
		        	label: ["Valor en Bs"],
		            data: [data[0]?.valor?.toFixed(2),data[1]?.valor?.toFixed(2)],
		            backgroundColor: ['#e01d22','#0079C2']
		        }]
		    },
		    options: {
		        scales: {
		            y: {
		                beginAtZero: true
		            }
		        }
		    }
		});
		document.getElementById('esqueleto_canva_1').remove();
		document.getElementById("canva_1").removeAttribute("hidden");
	}
	else{
		document.getElementById('div_alert_1').removeAttribute("hidden");
		document.getElementById('esqueleto_canva_1').remove();
		document.getElementById('canva_1').remove();
		document.getElementById('esqueleto_dato_1_1').remove();
		document.getElementById('esqueleto_dato_2_1').remove();
	}
	
	if (!(data[2].valor == null && data[3].valor == null)) {
		const dato_1_2 = document.createElement("p"), b_dato_1_2 = document.createElement("b"),
		dato_2_2 = document.createElement("p"), b_dato_2_2 = document.createElement("b");

		dato_1_2.textContent = "Total de deuda de los apartamentos: " ;
		dato_1_2.id = "b_gastos";

		b_dato_1_2.textContent = (data[2]?.valor?.toFixed(2) || 0) + " Bs.";
		b_dato_1_2.classList.add("text-danger");	

		dato_2_2.textContent = "Total de ingresos de los apartamentos: " ;
		dato_2_2.id = "b_ingresos";

		b_dato_2_2.textContent = (data[3]?.valor?.toFixed(2) || 0) + " Bs.";
		b_dato_2_2.classList.add("text-success");

		dato_1_2.appendChild(b_dato_1_2);
		dato_2_2.appendChild(b_dato_2_2);

		document.getElementById('esqueleto_dato_1_2').replaceWith(dato_1_2);
		document.getElementById('esqueleto_dato_2_2').replaceWith(dato_2_2);

		graficaChart_2 = new Chart(ctx_2, {
		    type: 'bar',
		    data: {
		        labels: ["Total de Gastos","Total de Ingresos"],
		        datasets: [{
		        	label: "Cantidad Bs.",
		            data: [data[2]?.valor,data[3]?.valor],
		            backgroundColor: ['#e01d22','#0079C2']
		        }		        
		        ]
		    },
		    options: {
		        scales: {
		            y: {
		                beginAtZero: true
		            }
		        }
		    }
		});
		document.getElementById('esqueleto_canva_2').remove();
		document.getElementById("canva_1").removeAttribute("hidden");
	}
	else{
		document.getElementById('div_alert_2').removeAttribute("hidden");
		document.getElementById('esqueleto_canva_2').remove();
		document.getElementById('canva_2').remove();
		document.getElementById('esqueleto_dato_1_2').remove();
		document.getElementById('esqueleto_dato_2_2').remove();
	}
}

async function consultarPublicaciones() {
	if (fin) {return}
	document.getElementById('carga_publicaciones').removeAttribute('hidden');

	let datos_consulta = new FormData();

    datos_consulta.append("operacion", "consulta_inicio");
    datos_consulta.append("limite", limite);
    let data = await query(datos_consulta);

    document.getElementById('carga_publicaciones').setAttribute('hidden','');

    if (data.length == 0) {
    	fin = true;
    	let mensaje = (limite === 0)?"No hay publicaciones":"No hay más resultados";

    	let div_no_hay = document.createElement("div");
    	div_no_hay.setAttribute("class","col-10 text-center my-2");
    	div_no_hay.textContent = mensaje;

    	contenido_principal.appendChild(div_no_hay);
    	
    	return;
    }    

    let fragment = document.createDocumentFragment();
    data.map(publicacion=>{
    	let div_card = document.createElement("div");
	    div_card.setAttribute("class","col-11 card post-card mx-auto shadow-lg my-4 px-0");

	    let div_row = document.createElement("div");
	    div_row.setAttribute("class","row g-0 h-100");

	    let div_col_contenido = document.createElement("div");
	    div_col_contenido.setAttribute("class","col-md-7 order-md-1");

	    let div_content_area = document.createElement("div");
	    div_content_area.setAttribute("class","content-area");

	    let h2 = document.createElement("h2");
	    h2.setAttribute("class","post-title h3");
	    h2.textContent = publicacion.titulo;

	    let div_autor_fecha = document.createElement("div");
	    div_autor_fecha.setAttribute("class","post-meta my-2 mb-5");	    

	    let small_fecha = document.createElement("small");
	    small_fecha.setAttribute("class","text-uppercase fw-bold");
	    small_fecha.textContent = `Publicado el ${formatearFecha(publicacion.fecha)}`;

		let spam_usuario = document.createElement("span");
	    spam_usuario.setAttribute("class","author-badge");
	    spam_usuario.textContent = `Publicado por ${publicacion.nombre_usuario}`;

	    div_autor_fecha.appendChild(small_fecha);
	    div_autor_fecha.appendChild(spam_usuario);

	    let p_contenido = document.createElement("p");
	    p_contenido.setAttribute("class","post-description flex-grow-1");
	    p_contenido.textContent = publicacion.descripcion;

	    div_content_area.appendChild(h2);
	    div_content_area.appendChild(div_autor_fecha);
	    div_content_area.appendChild(p_contenido);

	    div_col_contenido.appendChild(div_content_area);


	    let div_col_imagen = document.createElement("div");
	    div_col_imagen.setAttribute("class","col-md-5 order-md-2 d-flex align-items-center");

	    if (publicacion.imagen != "") {
	    	let div_imagen = document.createElement("div");
	    	div_imagen.setAttribute("class","image-container w-100");

	    	let img = document.createElement("img");
			img.setAttribute("class","post-image");
			img.setAttribute("alt","Imagen de la publicación o evento");
			img.setAttribute("onerror","this.onerror=null; this.src='https://placehold.co/800x500/42a5f5/ffffff?text=Sin+Imagen';");
			img.setAttribute("src",`recursos/img/cartelera/${publicacion.imagen}`);

			div_imagen.appendChild(img);
			div_col_imagen.appendChild(div_imagen);
	    }		

		div_row.appendChild(div_col_contenido);
		div_row.appendChild(div_col_imagen);

		div_card.appendChild(div_row);

		fragment.appendChild(div_card);
    });
    
    limite += 2;
    consultando = false;
	contenido_principal.appendChild(fragment);
}

async function query(datos) {
	let modal_carga = new bootstrap.Modal("#modal_carga");
	let mostrarModal = false;
    let tiempoCarga;

	tiempoCarga = setTimeout(()=>{
		mostrarModal = true;
		modal_carga.show();
	}, 600);
	
	try{
		const tiempoInicio = performance.now();

		const res = await fetch("", { method: "POST", body: datos });
    	const data = await res.json();

		const tiempoTranscurido = performance.now() - tiempoInicio;
		const tiempoEsperaMin = 700;
		
		if (mostrarModal && tiempoTranscurido < tiempoEsperaMin) {
			
			const restante = tiempoEsperaMin - tiempoTranscurido;
			await new Promise(resolve => setTimeout(resolve,restante));
		}

		return data;
	}
	catch(error){
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
	}
	finally{
		clearTimeout(tiempoCarga);
		if (mostrarModal) {
			modal_carga.hide();
		}
	}
}

function mensajes(icono,tiempo,titulo,mensaje){
	Swal.fire({
	icon:icono,
    timer:tiempo,	
    title:titulo,
	text:mensaje,
	confirmButtonText:'Aceptar',
	confirmButtonColor: "#e01d22",
	});
}

cargaInicio();