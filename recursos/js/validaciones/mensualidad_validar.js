// document.querySelector(`#modal_mensualidad`).addEventListener("show.bs.modal",()=>{	
	
// });

boton_formulario.addEventListener("click",e=>{
	let filas_cuerpo = tabla_mensualidad_asignar.querySelectorAll("tbody tr");
	let asignado = false;
	for(fila of filas_cuerpo){
		asignado = false;
		let apartamento_sin_asignar;
		
		let checkbox_fila = fila.querySelectorAll("input");
		checkbox_fila.forEach(input=>{
			if(input.checked) {
				asignado = true;
			}else{
				apartamento_sin_asignar = fila.firstElementChild.textContent;
			}
		});
		if(!asignado){
			mensajes('warning',4000,'Atencion', `Al ${apartamento_sin_asignar} No se asigno Mensualidad`);
			break;
		}
	}
	if (asignado) {
		let accion = boton_formulario.getAttribute("op");
		Swal.fire({
			title: "¿Estás seguro?",
			text: `¿Está seguro que desea ${accion} esta Mensualidad?`,
			showCancelButton: true,
			confirmButtonText: accion,
			confirmButtonColor: "#1b8a40",
			cancelButtonText: "Cancelar",
			icon: "warning"
		})
		.then((result) => {
			if (result.isConfirmed) {
				envio(accion);					
			}
		});
	}
});

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
