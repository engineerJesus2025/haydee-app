let notificacion_select;
$(document).ready(function (){
	let notificaciones = document.querySelector("#notificaciones-list");
	if (notificaciones != null) {
		notificaciones = document.querySelector("#notificaciones-list").querySelectorAll(".notification-item");
		
		notificaciones.forEach(notificacion =>{
			notificacion.addEventListener("click",e => {
				// console.log(e.target);
			 	e.preventDefault();
			 	notificacion_select = notificacion;
			 	let datos = new FormData();
			 	datos.append('id',notificacion.dataset.id);
			 	enviaAjax(datos);
			})
		})
	}
	
})


function enviaAjax(datos) {
  $.ajax({
    async: true,
    url: "?pagina=notificaciones_controlador.php&accion=quitar",
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
    beforeSend: function () {},
    timeout: 10000,
    success: function (respuesta) {
  if (respuesta.ok) {
    const divider = notificacion_select.nextElementSibling;
    if (divider && divider.classList.contains("dropdown-divider")) {
      divider.remove();
    }

    notificacion_select.remove();

    const countLabel = document.querySelector("#count-label");
    if (countLabel) {
      let count = parseInt(countLabel.textContent);
      if (count > 1) {
        countLabel.textContent = count - 1;
      } else {
        countLabel.remove();
        document.querySelector("#notificaciones").insertAdjacentHTML("beforeend",
          '<li><span class="dropdown-item text-muted small">No hay notificaciones nuevas</span></li>'
        );
      }
    }
  } else {
    alert("No se pudo eliminar la notificación.");
  }
},
    error: function (request, status, err) {
      if (status == "timeout") {
        alert("Servidor ocupado, intente de nuevo");
      } else {
        alert("ERROR:" + request + status + err);
      }
    },
    complete: function () {},
  });
}