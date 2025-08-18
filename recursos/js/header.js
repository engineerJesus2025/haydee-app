document.addEventListener("DOMContentLoaded", function(event) {
   
	const showNavbar = (toggleId, navId, bodyId, headerId) =>{
		let toggle = document.getElementById(toggleId),
		nav = document.getElementById(navId),
		bodypd = document.getElementById(bodyId),
		headerpd = document.getElementById(headerId);
		if (bodypd === null) {
			bodypd = document.querySelector("body")
		}
		if(toggle && nav && bodypd && headerpd){
			toggle.addEventListener('click', ()=>{
				
				nav.classList.toggle('show');
				
				toggle.classList.toggle('bi-x-lg');
				
				bodypd.classList.toggle('body-pd');
				
				headerpd.classList.toggle('body-pd');

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
	headerpd = document.getElementById(headerId)

	if (anchoVentana < 769) {
		if(toggle && nav && bodypd && headerpd){	
			nav.classList.remove('show');
			
			toggle.classList.remove('bi-x-lg');
			
			bodypd.classList.remove('body-pd');
			
			headerpd.classList.remove('body-pd');
		}
	}
}