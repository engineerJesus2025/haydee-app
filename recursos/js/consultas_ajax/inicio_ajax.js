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

async function cargaInicio() {
	consultarPublicaciones();

	if (document.getElementById('div_graficos') == null) return;
	
	let datos_consulta = new FormData();

    datos_consulta.append("operacion", "consulta_inicio_grafico");

    let data = await query(datos_consulta);
	console.log(data)
    if(!(data.estatus == undefined)){
		mensajes('error',4000,'Atencion', data.mensaje);
		return;
	}
	const ctx_1 = document.getElementById('canva_1').getContext('2d');
	const ctx_2 = document.getElementById('canva_2').getContext('2d');	

	if (!(data[0].valor == 0 && data[1].valor == 0)) {
		document.getElementById('b_moroso').textContent = (data[0]?.valor?.toFixed(2) || 0) + " Bs.";
		document.getElementById('b_solvente').textContent = (data[1]?.valor?.toFixed(2) || 0) + " Bs.";
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
	}
	else{
		document.getElementById('div_alert_1').textContent = "No hay Datos para el gráfico";
		document.getElementById('div_alert_1').removeAttribute("hidden");
		canva_borrar_1 = document.getElementById('canva_1');
		canva_borrar_1.parentElement.removeChild(canva_borrar_1);

		document.getElementById('b_moroso').setAttribute("hidden");
		document.getElementById('b_solvente').setAttribute("hidden");
	}

	if (!(data[2].valor == null && data[3].valor == null)) {
		document.getElementById('b_gastos').textContent = (data[2]?.valor?.toFixed(2) || 0) + " Bs.";
		document.getElementById('b_ingresos').textContent = (data[3]?.valor?.toFixed(2) || 0) + " Bs.";

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
	}
	else{
		document.getElementById('div_alert_2').textContent = "No hay Datos para el gráfico";
		document.getElementById('div_alert_2').removeAttribute("hidden");
		canva_borrar_2 = document.getElementById('canva_2');
		canva_borrar_2.parentElement.setAttribute("class","col-4");
		canva_borrar_2.parentElement.removeChild(canva_borrar_2);

		document.getElementById('b_gastos').parentElement.setAttribute("hidden",'');
		document.getElementById('b_ingresos').parentElement.setAttribute("hidden",'');
	}
}

async function consultarPublicaciones() {
	if (fin) {return}

	let datos_consulta = new FormData();

    datos_consulta.append("operacion", "consulta_inicio");
    datos_consulta.append("limite", limite);
    let data = await query(datos_consulta);

    if (data.length == 0) {
    	let div_no_hay = document.createElement("div");
    	div_no_hay.setAttribute("class","col-10 text-center my-2");
    	div_no_hay.textContent = "No hay más resultados";

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
	    div_col_1.setAttribute("class","col-md-7 mt-2 d-flex flex-column");

	    let h5 = document.createElement("h2");
	    h5.setAttribute("class","card-title");
	    h5.textContent = publicacion.titulo;

	    let p_fecha = document.createElement("p");
	    p_fecha.setAttribute("class","card-text");

	    let small_fecha = document.createElement("small");
	    small_fecha.setAttribute("class","text-body-secondary");
	    small_fecha.textContent = `Publicado el ${publicacion.fecha}`;

	    let p_contenido = document.createElement("p");
	    p_contenido.setAttribute("class","card-text flex-grow-1");
	    p_contenido.textContent = publicacion.descripcion;

	    let spam_usuario = document.createElement("span");
	    spam_usuario.setAttribute("class","badge bg-primary align-self-start");
	    spam_usuario.textContent = `Publicado por ${publicacion.nombre_usuario}`;

	    let div_col_2 = document.createElement("div");
	    div_col_2.setAttribute("class","col-md-5 mt-2");

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
		div_col_1.appendChild(spam_usuario);

		div_row.appendChild(div_col_1);
		div_row.appendChild(div_col_2);

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

cargaInicio();