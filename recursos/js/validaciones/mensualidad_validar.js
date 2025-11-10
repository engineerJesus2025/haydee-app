document.addEventListener('DOMContentLoaded', function() {
	boton_formulario.addEventListener("click",async e=>{
		let select =document.getElementById('mes_select_asignar');
		let valido = validarKeyUp(/^\d{1,2}\/\d{1,2}\/\d{4}$/,
		select,select.nextElementSibling,"el valor de la fecha no es válido");

		if (!valido) {
			mensajes('warning',4000,'Atencion', `La Fecha seleccionada no posee un formato valido`);
			return;
		}

		let datos = new FormData();
		datos.append('operacion','consultar_presupuestos_mensualidades');		
		datos.append('fecha',select.selectedOptions[0].id);

		valido = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;
		});
		
		if (valido.length === 0) {
			select.classList.remove('is-valid');
			select.classList.add('is-invalid');
			select.nextElementSibling.textContent = "La Fecha seleccionada no existe";
			mensajes('warning',4000,'Atencion', `La Fecha seleccionada no es una opcion valida`);
			return;
		}
		else{
			select.classList.add('is-valid');
			select.classList.remove('is-invalid');
			select.nextElementSibling.textContent = "";			
		}

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
				confirmButtonText: "Si, " + accion,
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

	document.getElementById('mes_select_asignar').addEventListener("change",async e=>{
		let valido = validarKeyUp(/^\d{1,2}\/\d{1,2}\/\d{4}$/,
		e.target,e.target.nextElementSibling,"el valor de la fecha no es válido");

		if (!valido) return;

		let datos = new FormData();
		datos.append('operacion','consultar_presupuestos_mensualidades');		
		datos.append('fecha',e.target.selectedOptions[0].id);

		valido = await fetch("",{method:"POST", body:datos}).then(res=>{		
		let result = res.json()
			return result;
		});
		
		if (valido.length === 0) {
			e.target.classList.remove('is-valid');
			e.target.classList.add('is-invalid');
			e.target.nextElementSibling.textContent = "La Fecha seleccionada no existe";
		}
		else{
			e.target.classList.add('is-valid');
			e.target.classList.remove('is-invalid');
			e.target.nextElementSibling.textContent = "";
		}
	});
  	
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

function validarKeyUp(er,etiqueta,etiquetamensaje,
mensaje){
	a = er.test(etiqueta.selectedOptions[0].id);
	
	if(a){
		etiqueta.classList.add('is-valid');
		etiqueta.classList.remove('is-invalid');

		if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
			etiqueta.nextElementSibling.classList.remove('border-danger');
			etiqueta.nextElementSibling.classList.remove('text-danger');

			etiqueta.nextElementSibling.classList.add('border-success');
			etiqueta.nextElementSibling.classList.add('text-success');
		}
		etiquetamensaje.textContent = "";
		return 1;
	}
	else{
		etiqueta.classList.add('is-invalid');
		etiqueta.classList.remove('is-valid');

		if (etiqueta.id == "contra" || etiqueta.id == "confir_contra") {
			etiqueta.nextElementSibling.classList.remove('border-success');
			etiqueta.nextElementSibling.classList.remove('text-success');

			etiqueta.nextElementSibling.classList.add('border-danger');
			etiqueta.nextElementSibling.classList.add('text-danger');
		}
		etiquetamensaje.textContent = mensaje;
		return 0;
	}
}