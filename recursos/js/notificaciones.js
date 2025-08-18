window.addEventListener('DOMContentLoaded',()=>{
	let notificaciones = document.querySelector("#notificaciones-list");
	if (notificaciones != null) {
		notificaciones = document.querySelector("#notificaciones-list").querySelectorAll(".notification-item");

		notificaciones.forEach(notificacion =>{
			notificacion.addEventListener("click",async e => {
			 	e.preventDefault();
			 	let datos_consulta = new FormData();

			 	datos_consulta.append('id',notificacion.dataset.id);
			 	datos_consulta.append('operacion',"quitar_notificacion");

			 	let resultado = await queryNotificacion(datos_consulta);

			 	if (!resultado.estatus) {
					mensajes('error',4000,'Atencion',resultado.mensaje);
					console.log(resultado.error);
					return;
				}

				const division = notificacion.nextElementSibling;
			    if (division && division.classList.contains("dropdown-divider")) {
					division.remove();
			    }

			    notificacion.remove();

			    const countLabel = document.querySelector("#total_notificaciones");
		      	const labelGeneral = document.querySelector("#count-label");
		      	if (countLabel) {
		        	let count = parseInt(countLabel.textContent);
			        if (count > 1) {
			          	if (count > 100) {
			            	labelGeneral.textContent = "+99";
			          	}
			          	else{
			            	labelGeneral.textContent = count - 1;
			          	}
			          	countLabel.textContent = (parseInt(countLabel.textContent) - 1);
			        } 
			        else {
				        labelGeneral.remove();
				        document.querySelector("#notificaciones").insertAdjacentHTML("beforeend",'<li><span class="dropdown-item text-muted small">No hay notificaciones nuevas</span></li>');
			        }
		      }
			});
		});
	}
});

async function queryNotificacion(datos) {
	try{
		const res = await fetch("?pagina=notificaciones_controlador.php&accion=quitar_notificacion", { method: "POST", body: datos });
    	const data = await res.json();
		return data;
	}
	catch(error){
		return {estatus:false,mensaje:"A ocurrido un error durante la consulta",error}
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