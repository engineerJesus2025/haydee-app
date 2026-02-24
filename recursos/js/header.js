document.addEventListener("DOMContentLoaded", function(event) {
   
	const showNavbar = (toggleId, navId, bodyId, headerId) =>{
		const anchoVentana = window.innerWidth;
		let toggle = document.getElementById(toggleId),
		nav = document.getElementById(navId),
		bodypd = document.getElementById(bodyId),
		headerpd = document.getElementById(headerId);

		let enlaces = document.querySelectorAll(".collapse a");
		let barra_inferior = document.querySelector(".barra_inferior");

		if (bodypd === null) {
			bodypd = document.querySelector("body")
		}
		if(toggle && nav && bodypd && headerpd){
			if (anchoVentana < 769) {
				enlaces.forEach(a=>{
					a.classList.add('ps-2');
					a.parentElement.classList.remove('rounded');
					a.parentElement.classList.remove('ms-4');
				});
			}

			toggle.addEventListener('click', ()=>{
				
				nav.classList.toggle('show');
				
				toggle.classList.toggle('bi-x-lg');
				
				bodypd.classList.toggle('body-pd');
				
				headerpd.classList.toggle('body-pd');

				// barra_inferior.classList.toggle('ajustar');

				if (anchoVentana < 769) return;
				
				let id_submenu = '';
				enlaces.forEach(a=>{
					a.classList.toggle('ps-2');

					if (!(id_submenu == a.parentElement.id)) {
						a.parentElement.classList.toggle('rounded');
						a.parentElement.classList.toggle('ms-4');

						id_submenu = a.parentElement.id;
					}
				});
			});
		}
	}

	showNavbar('header-toggle','nav-bar','body-pd','header')

	const linkColor = document.querySelectorAll('.nav_link')

	function colorLink(){
		if(linkColor){
			linkColor.forEach(l=> l.classList.remove('active'))
			this.classList.add('active')
		}
	}
	linkColor.forEach(l=> l.addEventListener('click', colorLink));

	cambiarClasesMovil('header-toggle','nav-bar','body-pd','header');
});

function cambiarClasesMovil(toggleId, navId, bodyId, headerId) {
	const anchoVentana = window.innerWidth;
	let toggle = document.getElementById(toggleId),
	nav = document.getElementById(navId),
	bodypd = document.getElementById(bodyId),
	headerpd = document.getElementById(headerId);

	let enlaces = document.querySelectorAll(".collapse a");

	if (anchoVentana < 769) {
		if(toggle && nav && bodypd && headerpd){	
			nav.classList.remove('show');
			
			toggle.classList.remove('bi-x-lg');
			
			bodypd.classList.remove('body-pd');
			
			headerpd.classList.remove('body-pd');

			// let id_submenu = '';
			// enlaces.forEach(a=>{
			// 	a.classList.remove('ps-2');
			// 	console.log(a.parentElement)
			// 	a.parentElement.classList.remove('rounded');
			// 	a.parentElement.classList.remove('ms-4');
			// 	a.parentElement.classList.remove('ms-3');

			// 	id_submenu = a.parentElement.id;
				
			// });
		}
	}
}