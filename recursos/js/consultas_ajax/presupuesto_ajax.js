/**
 * presupuesto_ajax.js
 * Gestión de Presupuestos - Peticiones AJAX
 * Dependencias: utilidades.js, validaciones.js
 */

// ============================================================
// VARIABLES GLOBALES
// ============================================================
let permiso_eliminar = document.querySelector("#permiso_eliminar")?.value;
let permiso_modificar = document.querySelector("#permiso_modificar")?.value;
let boton_formulario = document.querySelector("#boton_formulario");
let modal = new bootstrap.Modal(document.getElementById("modal_presupuesto"), { focus: false });
let formulario_usar = document.querySelector("#form_presupuesto");
let select_mes = document.querySelector("#fecha");
let detalles_presupuestos_base;

let tabla_presupuesto;
let modal_carga = new bootstrap.Modal("#modal_carga");
let tasa_dolar = parseFloat(localStorage.getItem("tasa_dolar") || 1).toFixed(2);

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    consultar();

    document.querySelector("#modal_presupuesto")?.addEventListener("hide.bs.modal", resetModal);
});

function resetModal() {
    formulario_usar.reset();
    boton_formulario.removeAttribute("modificar");
    boton_formulario.removeAttribute("id_modificar");
    boton_formulario.textContent = "Guardar";
    document.getElementById('titulo_modal').textContent = "Registrar presupuesto";

    // Eliminar fecha extra si existe
    let fechaExtra = document.getElementById('fecha_editada');
    if (fechaExtra) fechaExtra.remove();

    // Restaurar contenido original de los detalles
    document.getElementById('contenedor_presupuestos').innerHTML = detalles_presupuestos_base;

    // Reasignar eventos a los botones de agregar
    document.querySelectorAll("[accion='agregar']").forEach(boton => {
        boton.addEventListener('click', agregar_fila_presupuesto);
    });

    // Restablecer valores de inputs numéricos
    document.querySelectorAll("[type='number']").forEach(input => {
        if (input.id !== "cuota_reserva") input.value = 0;
    });
    document.querySelectorAll("[type='checkbox']").forEach(chk => chk.checked = false);
    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => el.classList.remove('is-valid', 'is-invalid'));

    asignarEventosCambioMoneda();
}

// ============================================================
// FUNCIONES AUXILIARES DE UI (agregar/eliminar filas, etc.)
// ============================================================
function agregar_fila_presupuesto(e) {
    if (typeof e.preventDefault === 'function') {
        e.preventDefault();
    }

    let div_global = e.target.closest(".accordion-collapse");

    let div_padre = document.createElement("div");
    div_padre.setAttribute("class","accordion-body row");

    let div_nombre = document.createElement("div");
    div_nombre.setAttribute("class","col-sm-5");

    let input_nombre = document.createElement("input");
    input_nombre.setAttribute('class','form-control');
    input_nombre.setAttribute('type','text');
    input_nombre.setAttribute('placeholder','nombre del gasto');

    let spam_nombre = document.createElement("spam");
    spam_nombre.setAttribute('class','w-100 invalid-feedback');

    div_nombre.appendChild(input_nombre);
    div_nombre.appendChild(spam_nombre);

    let div_inputs_montos = document.createElement("div");
    div_inputs_montos.setAttribute("class","col-sm-5 row my-sm-0 my-3");

    let div_monto = document.createElement("div");
    div_monto.setAttribute("class","col-md-6");

    let div_input_group = document.createElement("div");
    div_input_group.setAttribute("class","input-group");

    let input_monto = document.createElement("input");
    input_monto.setAttribute('class','form-control');
    input_monto.setAttribute('type','number');
    input_monto.setAttribute('title','Valor del monto en bolivares');
    input_monto.setAttribute('placeholder','Ingrese un monto');
    input_monto.setAttribute('value',0);
    input_monto.setAttribute('monto','bs');

    let spam_monto = document.createElement("spam");
    spam_monto.setAttribute('class','w-100 invalid-feedback');

    let spam_moneda = document.createElement("spam");
    spam_moneda.setAttribute('class','input-group-text icono_moneda');
    spam_moneda.textContent = "Bs.";

    div_input_group.appendChild(input_monto);
    div_input_group.appendChild(spam_monto);
    div_input_group.appendChild(spam_moneda);
    div_monto.appendChild(div_input_group);

    let div_intercambio = document.createElement("div");
    div_intercambio.setAttribute("class","col-lg-1 col-2 mt-sm-0 mt-2 d-flex justify-content-center align-items-center");

    let boton_intercambio = document.createElement("button");
    boton_intercambio.setAttribute('tabindex','-1');
    boton_intercambio.setAttribute("class","btn btn-outline-info boton_intercambio");

    let spam_intercambio = document.createElement("spam");

    let icono_intercambio = document.createElement("i");
    icono_intercambio.setAttribute('class','bi bi-arrow-left-right');

    spam_intercambio.appendChild(icono_intercambio);
    boton_intercambio.appendChild(spam_intercambio);

    div_intercambio.appendChild(boton_intercambio);     

    let div_monto_2 = document.createElement("div");
    div_monto_2.setAttribute("class","col-md-5 col-10 mt-sm-0 mt-2 text-center");

    let div_input_group_2 = document.createElement("div");
    div_input_group_2.setAttribute("class","input-group");

    let input_monto_2 = document.createElement("input");
    input_monto_2.setAttribute('class','form-control');
    input_monto_2.setAttribute('type','number');
    input_monto_2.setAttribute("disabled","");
    input_monto_2.setAttribute('value',0);
    input_monto_2.setAttribute('title','Valor del monto en dolares');
    input_monto_2.setAttribute('convertido','');

    let spam_moneda_2 = document.createElement("spam");
    spam_moneda_2.setAttribute('class','input-group-text icono_moneda');
    spam_moneda_2.textContent = "$";

    div_input_group_2.appendChild(input_monto_2);
    div_input_group_2.appendChild(spam_moneda_2);
    div_monto_2.appendChild(div_input_group_2); 

    div_inputs_montos.appendChild(div_monto);
    div_inputs_montos.appendChild(div_intercambio);
    div_inputs_montos.appendChild(div_monto_2);

    div_padre.appendChild(div_nombre);
    div_padre.appendChild(div_inputs_montos);

    let div_botones = document.createElement("div");
    div_botones.setAttribute("class","col-sm-2 justify-content-evenly d-flex align-items-baseline");

    let boton_agregar = document.createElement("button");
    boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
    boton_agregar.setAttribute('class','btn btn-success');
    boton_agregar.setAttribute('tabindex','-1');
    boton_agregar.setAttribute('accion',`agregar`);

    let icono_agregar = document.createElement('i');
    icono_agregar.setAttribute("class",'bi bi-plus-lg');

    boton_agregar.appendChild(icono_agregar);

    let boton_eliminar = document.createElement("button");
    boton_eliminar.setAttribute('title','eliminar monto');
    boton_eliminar.setAttribute('tabindex','-1');
    boton_eliminar.setAttribute('class','btn btn-danger');  

    let icono_eliminar = document.createElement('i');
    icono_eliminar.setAttribute("class",'bi bi-x-lg');

    boton_eliminar.appendChild(icono_eliminar);

    div_botones.appendChild(boton_agregar);
    div_botones.appendChild(boton_eliminar);
    
    div_padre.appendChild(div_botones);

    div_global.appendChild(div_padre);

    let boton_a_borrar = e.target;
    if (!(e.target.getAttribute("accion"))) {
        boton_a_borrar = e.target.parentElement;
    }
    boton_a_borrar.parentElement.removeChild(boton_a_borrar);

    //eventos
    boton_eliminar.addEventListener('click',eliminar_fila_presupuesto);
    boton_agregar.addEventListener('click',agregar_fila_presupuesto);

    input_nombre.addEventListener('keypress',e=>{
        let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]*$/;
        let key = e.keyCode;
        let tecla = String.fromCharCode(key);
        let a = er.test(tecla);
        if (!a) {
            e.preventDefault();
        }
    });

    input_nombre.addEventListener('keyup',e=>{
        let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]{4,50}$/;
        let a = er.test(input_nombre.value);    
        if(a){
            input_nombre.classList.add('is-valid');
            input_nombre.classList.remove('is-invalid');
            input_nombre.nextElementSibling.textContent = "";
            return 1;
        }
        else{
            input_nombre.classList.add('is-invalid')
            input_nombre.classList.remove('is-valid');
            input_nombre.nextElementSibling.textContent = 'Solo letras, no mas de 50 caracteres';
            return 0;
        }
    });

    input_monto.addEventListener('keypress',e=>{
        let er = /^[0-9,.]*$/;
        let key = e.keyCode;
        let tecla = String.fromCharCode(key);
        let a = er.test(tecla);
        if (!a) {
            e.preventDefault();
        }
    });

    input_monto.addEventListener('keyup',e=>{
        let er = /^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/;
        let a = er.test(input_monto.value); 
        if(a){
            input_monto.classList.add('is-valid');
            input_monto.classList.remove('is-invalid');
            input_monto.nextElementSibling.textContent = "";

            //Convertimos al contrario          
            let input_convertir = input_monto.closest(".row").querySelector("[convertido]");
            
            if (input_monto.getAttribute("monto") == "bs") {
                if (input_monto.value <= 0 || input_monto.value == '') {
                    input_convertir.value = 0;
                    return;
                }
                input_convertir.value = (parseFloat(input_monto.value) / tasa_dolar).toFixed(2) || 0;
            }
            else{
                if (input_monto.value <= 0 || input_monto.value == '') {
                    input_convertir.value = 0;
                    return;
                }
                input_convertir.value = (parseFloat(input_monto.value) * tasa_dolar).toFixed(2);
            }

            return 1;
        }
        else{
            input_monto.classList.add('is-invalid')
            input_monto.classList.remove('is-valid');
            input_monto.nextElementSibling.textContent = 'Solo numeros, no mas de 15 caracteres';
            return 0;
        }
    });

    boton_intercambio.addEventListener("click",e=>{
        if (typeof e.preventDefault === 'function') {
            e.preventDefault();
        }
        let valor_temporal = 0;

        if (input_monto.getAttribute("monto") == "bs") {
            input_monto.setAttribute("monto",'$');

            valor_temporal = input_monto.value;
            input_monto.value = input_monto_2.value;
            input_monto_2.value = valor_temporal;

            input_monto.parentElement.querySelector(".icono_moneda").textContent = "$";
            input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
        }
        else{
            input_monto.setAttribute("monto",'bs');

            valor_temporal = input_monto.value;
            input_monto.value = input_monto_2.value;
            input_monto_2.value = valor_temporal;

            input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
            input_monto_2.parentElement.querySelector(".icono_moneda").textContent = "$";
        }
    });
}

function eliminar_fila_presupuesto(e) {
    let boton_eliminar = e.target;
    if (e.target.title == '') {
        boton_eliminar = e.target.parentElement;
    }

    if (boton_eliminar.previousElementSibling != null) {
        let boton_agregar = document.createElement("button");
        boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
        boton_agregar.setAttribute('class','btn btn-success');
        boton_agregar.setAttribute('tabindex','-1');
        boton_agregar.setAttribute('accion',`agregar`);

        let icono_agregar = document.createElement('i');
        icono_agregar.setAttribute("class",'bi bi-plus-lg');

        boton_agregar.appendChild(icono_agregar);

        let div_botones_anterior = boton_eliminar.closest(".row").previousElementSibling.querySelector(".col-sm-2");
        
        div_botones_anterior.insertBefore(boton_agregar,div_botones_anterior.querySelector("[title='eliminar monto']"));

        boton_agregar.addEventListener('click',agregar_fila_presupuesto);
    }
    boton_eliminar.parentElement.parentElement.parentElement.removeChild(boton_eliminar.parentElement.parentElement);
}

function agregarGastoFijo(nombre_gasto,ultimo = false) {
    let div_padre = document.createElement("div");
    div_padre.setAttribute('class','accordion-body row');

    let div_nombre = document.createElement("div");
    div_nombre.setAttribute("class","col-sm-5");

    let input_nombre = document.createElement("input");
    input_nombre.setAttribute('class','form-control');
    input_nombre.setAttribute('type','text');
    input_nombre.setAttribute('placeholder','nombre del gasto');
    input_nombre.setAttribute('value',nombre_gasto);
    if (nombre_gasto !== '') {
        input_nombre.setAttribute('disabled','');
    }

    let spam_nombre = document.createElement("spam");
    spam_nombre.setAttribute('class','w-100 invalid-feedback');

    div_nombre.appendChild(input_nombre);
    div_nombre.appendChild(spam_nombre);    

    let div_inputs_montos = document.createElement("div");
    div_inputs_montos.setAttribute("class","col-sm-5 row my-sm-0 my-3");

    let div_monto = document.createElement("div");
    div_monto.setAttribute("class","col-md-6");

    let div_input_group = document.createElement("div");
    div_input_group.setAttribute("class","input-group");

    let input_monto = document.createElement("input");
    input_monto.setAttribute('class','form-control');
    input_monto.setAttribute('type','number');
    input_monto.setAttribute('value',0);
    input_monto.setAttribute('title','Valor del monto en bolivares');
    input_monto.setAttribute('placeholder','Ingrese un monto'); 
    input_monto.setAttribute('monto','bs');

    let spam_monto = document.createElement("spam");
    spam_monto.setAttribute('class','w-100 invalid-feedback');

    let spam_moneda = document.createElement("spam");
    spam_moneda.setAttribute('class','input-group-text icono_moneda');
    spam_moneda.textContent = "Bs.";

    div_input_group.appendChild(input_monto);
    div_input_group.appendChild(spam_monto);
    div_input_group.appendChild(spam_moneda);
    div_monto.appendChild(div_input_group);

    //boton intercambio
    let div_intercambio = document.createElement("div");
    div_intercambio.setAttribute("class","col-lg-1 col-2 mt-sm-0 mt-2 d-flex justify-content-center align-items-center");

    let boton_intercambio = document.createElement("button");
    boton_intercambio.setAttribute('tabindex','-1');
    boton_intercambio.setAttribute("class","btn btn-outline-info boton_intercambio");   

    let spam_intercambio = document.createElement("spam");

    let icono_intercambio = document.createElement("i");
    icono_intercambio.setAttribute('class','bi bi-arrow-left-right');

    spam_intercambio.appendChild(icono_intercambio);
    boton_intercambio.appendChild(spam_intercambio);

    div_intercambio.appendChild(boton_intercambio);     

    let div_monto_2 = document.createElement("div");
    div_monto_2.setAttribute("class","col-md-5 col-10 mt-sm-0 mt-2 text-center");

    let div_input_group_2 = document.createElement("div");
    div_input_group_2.setAttribute("class","input-group");

    let input_monto_2 = document.createElement("input");
    input_monto_2.setAttribute('class','form-control');
    input_monto_2.setAttribute('type','number');
    input_monto_2.setAttribute("disabled","");
    input_monto_2.setAttribute('value',0);
    input_monto_2.setAttribute('title','Valor del monto en dolares');
    input_monto_2.setAttribute('convertido','');

    let spam_moneda_2 = document.createElement("spam");
    spam_moneda_2.setAttribute('class','input-group-text icono_moneda');
    spam_moneda_2.textContent = "$";

    div_input_group_2.appendChild(input_monto_2);
    div_input_group_2.appendChild(spam_moneda_2);
    div_monto_2.appendChild(div_input_group_2); 

    div_inputs_montos.appendChild(div_monto);
    div_inputs_montos.appendChild(div_intercambio);
    div_inputs_montos.appendChild(div_monto_2);

    div_padre.appendChild(div_nombre);
    div_padre.appendChild(div_inputs_montos);

    let div_botones = document.createElement("div");
    div_botones.setAttribute("class","col-sm-2 justify-content-evenly d-flex align-items-baseline");

    if (ultimo) {
        let boton_agregar = document.createElement("button");
        boton_agregar.setAttribute('title','presione aquí para añadir otro monto');
        boton_agregar.setAttribute('class','btn btn-success');
        boton_agregar.setAttribute('tabindex','-1');
        boton_agregar.setAttribute('accion',`agregar`);

        let icono_agregar = document.createElement('i');
        icono_agregar.setAttribute("class",'bi bi-plus-lg');

        boton_agregar.appendChild(icono_agregar);

        div_botones.appendChild(boton_agregar);     
    }
    
    div_padre.appendChild(div_botones);


    //Eventos
    input_nombre.addEventListener('keypress',e=>{
        let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]*$/;
        let key = e.keyCode;
        let tecla = String.fromCharCode(key);
        let a = er.test(tecla);
        if (!a) {
            e.preventDefault();
        }
    });

    input_nombre.addEventListener('keyup',e=>{
        let er = /^[A-Za-z áéíóúÁÉÍÓÚñÑ\b]{4,50}$/;
        let a = er.test(input_nombre.value);    
        if(a){
            input_nombre.classList.add('is-valid');
            input_nombre.classList.remove('is-invalid');
            input_nombre.nextElementSibling.textContent = "";
            return 1;
        }
        else{
            input_nombre.classList.add('is-invalid')
            input_nombre.classList.remove('is-valid');
            input_nombre.nextElementSibling.textContent = 'Solo letras, no mas de 50 caracteres';
            return 0;
        }
    });

    input_monto.addEventListener('keypress',e=>{
        let er = /^[0-9,.]*$/;
        let key = e.keyCode;
        let tecla = String.fromCharCode(key);
        let a = er.test(tecla);
        if (!a) {
            e.preventDefault();
        }
    });

    input_monto.addEventListener('keyup',e=>{
        let er = /^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/;
        let a = er.test(input_monto.value); 
        if(a){
            input_monto.classList.add('is-valid');
            input_monto.classList.remove('is-invalid');
            input_monto.nextElementSibling.textContent = "";

            //Convertimos al contrario
            let input_convertir = input_monto.closest(".row").querySelector("[convertido]");            
            
            if (input_monto.getAttribute("monto") == "bs") {
                if (input_monto.value <= 0 || input_monto.value == '') {
                    input_convertir.value = 0;
                    return;
                }
                input_convertir.value = (parseFloat(input_monto.value) / tasa_dolar).toFixed(2) || 0;
            }
            else{
                if (input_monto.value <= 0 || input_monto.value == '') {
                    input_convertir.value = 0;
                    return;
                }
                input_convertir.value = (parseFloat(input_monto.value) * tasa_dolar).toFixed(2);
            }

            return 1;
        }
        else{
            input_monto.classList.add('is-invalid')
            input_monto.classList.remove('is-valid');
            input_monto.nextElementSibling.textContent = 'Solo numeros, no mas de 15 caracteres';
            return 0;
        }
    });

    return div_padre;
}

function asignarEventosDetalles() {
    detalles_presupuestos_base = document.getElementById('contenedor_presupuestos').innerHTML;
    document.querySelectorAll("[accion='agregar']").forEach(boton=>{        
        boton.addEventListener('click',agregar_fila_presupuesto);
    });
    document.querySelectorAll("[title='eliminar monto']").forEach((boton_eliminar)=>{
        boton_eliminar.addEventListener('click',eliminar_fila_presupuesto);
    });

    document.querySelectorAll("[type='number']").forEach(input=>{
        if (input.id == "cuota_reserva") return;
        input.addEventListener("click",e=>{if (e.target.value == 0) e.target.value = ''});
    });
}

function asignarEventosCambioMoneda(){
    let botones_intercambio = document.querySelectorAll(".boton_intercambio");
    botones_intercambio.forEach(boton=>{
        boton.addEventListener("click",e=>{
            if (typeof e.preventDefault === 'function') {
                e.preventDefault();
            }
            let input_monto = e.target.closest(".row").querySelector("[monto]"), 
            input_cambio = e.target.closest(".row").querySelector("[convertido]"),
            valor_temporal = 0;

            if (input_monto.getAttribute("monto") == "bs") {
                input_monto.setAttribute("monto",'$');

                valor_temporal = input_monto.value;
                input_monto.value = input_cambio.value;
                input_cambio.value = valor_temporal;

                input_monto.parentElement.querySelector(".icono_moneda").textContent = "$";
                input_cambio.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
            }
            else{
                input_monto.setAttribute("monto",'bs');

                valor_temporal = input_monto.value;
                input_monto.value = input_cambio.value;
                input_cambio.value = valor_temporal;

                input_monto.parentElement.querySelector(".icono_moneda").textContent = "Bs.";
                input_cambio.parentElement.querySelector(".icono_moneda").textContent = "$";
            }
        });
    });
}

// ============================================================
// CONSULTAS
// ============================================================
async function consultar() {
    const formatoPeriodo = (cell) => {
        let [anio, mes] = cell.getValue().split('-');
        return `${FormatoFechas.nombreMes(parseInt(mes))} del ${anio}`.toUpperCase();
    };
    const formatoMonto = (cell) => `${parseFloat(cell.getValue()).toFixed(2)} Bs. / ${(cell.getValue() / tasa_dolar).toFixed(2)} $`;

    const formatoBotones = (cell) => {
        const id = cell.getData().id_presupuesto;
        let html = `<div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-success btn-sm modificar" data-bs-toggle="modal" data-bs-target="#modal_presupuesto" title="Modificar" value="${id}"><i class="bi bi-pencil-square"></i></button>`;
        if (permiso_eliminar == 1) {
            html += `<button type="button" class="btn btn-danger btn-sm eliminar" title="Eliminar" value="${id}"><i class="bi bi-trash"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    const columnas = [
        { formatter: "responsiveCollapse", width: 40, minWidth: 40, hozAlign: "center", resizable: false, headerSort: false },
        { title: "PERÍODO", field: "fecha", formatter: formatoPeriodo, minWidth: 150, responsive: 0 },
        { title: "TOTAL ESTIMADO", field: "total_estimado", formatter: formatoMonto, minWidth: 180 },
        { title: "CUOTA RESERVA", field: "cuota_reserva", formatter: formatoMonto, minWidth: 180 },
        { title: "OBSERVACIÓN", field: "observacion", minWidth: 200 },
        {
            title: "ACCIONES", formatter: formatoBotones, headerSort: false, hozAlign: "center", vertAlign: "middle", minWidth: 100, responsive: 0, download: false,
            cellClick: function(e, cell) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const mockEvent = { target: btn };
                if (btn.classList.contains('modificar')) modificar_formulario(mockEvent);
                if (btn.classList.contains('eliminar')) eventoEliminar(mockEvent);
            }
        }
    ];

    tabla_presupuesto = Tablas.cargarTabulador("tabla_presupuesto", "", columnas);
    
    // Llamada vital del módulo
    await consultarInformacionFormulario();

    const inputBusqueda = document.getElementById("busqueda_global");
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", function(e) {
            let valor = e.target.value.trim();
            let filtros = columnas.filter(col => col.field).map(col => ({ field: col.field, type: "like", value: valor }));
            tabla_presupuesto.setFilter([filtros]);
        });
    }
}

async function consultarInformacionFormulario() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_meses_faltantes');
    const respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    const meses = respuesta.datos || [];
    if (meses.length === 0) {
        document.getElementById('boton_registrar').nextElementSibling.textContent = "No hay meses para definir presupuesto";
        document.getElementById('boton_registrar').setAttribute('style', 'display:none');
        return;
    }

    select_mes.innerHTML = '';
    let fragment = document.createDocumentFragment();
    meses.forEach(m => {
        let option = document.createElement("option");
        option.value = `${m.anio_faltante}-${m.mes_faltante}-01`;

        option.textContent = `${FormatoFechas.nombreMes(m.mes_faltante)} del ${m.anio_faltante}`.toUpperCase();

        fragment.appendChild(option);
    });
    select_mes.appendChild(fragment);

    await llenarDetallesPresupuestos();
}

async function llenarDetallesPresupuestos() {
    const formData = new FormData();
    formData.append('operacion', 'consultar_tipo_gastos');
    const respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    const tiposGasto = respuesta.datos || [];
    const contenedor = document.getElementById('contenedor_presupuestos');
    contenedor.innerHTML = '';
    let fragment = document.createDocumentFragment();

    tiposGasto.forEach(tipo => {
    	if (tipo.nombre_tipo_gasto === "Reposición de Caja Chica") return;

        let nombreFormat = tipo.nombre_tipo_gasto.replaceAll(" ", "-");
        let acordeon = document.createElement("div");
        acordeon.className = "accordion col-12";
        acordeon.id = nombreFormat;
        acordeon.setAttribute('id_tipo_gasto', tipo.id_tipo_gasto);

        let item = document.createElement("div");
        item.className = "accordion-item";
        let header = document.createElement("h2");
        header.className = "accordion-header";
        let boton = document.createElement("button");
        boton.className = "accordion-button";
        boton.type = "button";
        boton.setAttribute('data-bs-toggle', 'collapse');
        boton.setAttribute('data-bs-target', `#${nombreFormat}-body`);
        boton.textContent = tipo.nombre_tipo_gasto;
        header.appendChild(boton);

        let cuerpo = document.createElement("div");
        cuerpo.className = "align-items-center my-3 accordion-collapse collapse show";
        cuerpo.id = `${nombreFormat}-body`;

        // Agregar filas según el tipo (similar al código original)
        if (tipo.nombre_tipo_gasto === "Servicio de Gas") {
            cuerpo.appendChild(agregarGastoFijo("GAS LARA", true));
        } else if (tipo.nombre_tipo_gasto === "Servicios Públicos") {
            cuerpo.appendChild(agregarGastoFijo("CORPOELEC"));
            cuerpo.appendChild(agregarGastoFijo("HIDROLARA", true));
        } else if (tipo.nombre_tipo_gasto === "Personal y Obligaciones Laborales") {
            cuerpo.appendChild(agregarGastoFijo("Trabajadora Residencial"));
            cuerpo.appendChild(agregarGastoFijo("Bono de alimentacion"));
            cuerpo.appendChild(agregarGastoFijo("Bono de ayuda"));
            cuerpo.appendChild(agregarGastoFijo("Seguridad Social", true));
        } else if (tipo.nombre_tipo_gasto === "Mantenimientos y Reparaciones") {
            cuerpo.appendChild(agregarGastoFijo("Mantenimiento ascensor", true));
        } else if (tipo.nombre_tipo_gasto === "Suministros de Limpieza y Operacion") {
            cuerpo.appendChild(agregarGastoFijo("Bolsas de Basura"));
            cuerpo.appendChild(agregarGastoFijo("Productos de Limpieza", true));
        } else if (tipo.nombre_tipo_gasto === "Gastos Administrativos y Financieros") {
            cuerpo.appendChild(agregarGastoFijo("Comisiones Bancarias"));
            cuerpo.appendChild(agregarGastoFijo("Exencion cuota del administrador", true));
        } else {
            cuerpo.appendChild(agregarGastoFijo("", true));
        }

        item.appendChild(header);
        item.appendChild(cuerpo);
        acordeon.appendChild(item);
        fragment.appendChild(acordeon);
        fragment.appendChild(document.createElement("hr"));
    });

    contenedor.appendChild(fragment);
    asignarEventosDetalles();
    asignarEventosCambioMoneda();
    detalles_presupuestos_base = contenedor.innerHTML; // Guardar para reset
}

// ============================================================
// ACCIONES: REGISTRAR, modificar, ELIMINAR
// ============================================================

/**
 * Recolecta los datos del formulario (cabecera + detalles)
 */
function recolectarDatosPresupuesto(id_presupuesto = null) {
    let fecha = document.getElementById('fecha').value;
    let observacion = document.getElementById("observacion").value;

    // Cuota de reserva (siempre en Bs.)
    let input_reserva = document.getElementById("cuota_reserva");
    let cuota_reserva = input_reserva.getAttribute("monto") === "bs" ?
        input_reserva.value :
        input_reserva.closest(".row").querySelector("[convertido]").value;

    let detalles = [];
    let acordeones = document.querySelectorAll(".accordion");

    acordeones.forEach(acordion => {
        let tipo_gasto_id = acordion.getAttribute("id_tipo_gasto");
        let filas = acordion.querySelectorAll(".accordion-body");
        filas.forEach(fila => {
            let input_nombre = fila.querySelector("[type='text']");
            if (!input_nombre) return;
            let nombre = input_nombre.value.trim();
            if (nombre === "") return;

            let input_monto = fila.querySelector("[monto]");
            let monto = 0;
            if (input_monto) {
                monto = input_monto.getAttribute("monto") === "bs" ?
                    fila.querySelector("[type='number']").value :
                    fila.querySelector("[convertido]").value;
            }
            monto = parseFloat(monto);
            if (monto <= 0) return;

            detalles.push({
                nombre: nombre,
                monto: monto,
                tipo_gasto_id: tipo_gasto_id
            });
        });
    });

    return {
        id_presupuesto: id_presupuesto,
        fecha: fecha,
        cuota_reserva: parseFloat(cuota_reserva || 0),
        observacion: observacion,
        detalles: detalles
    };
}

/**
 * Registrar nuevo presupuesto (envía una sola petición)
 */
async function registrar() {
    let datos = recolectarDatosPresupuesto();
    if (datos.detalles.length === 0) {
        Alertas.mostrar('warning', 'Atención', 'Debe agregar al menos un detalle con monto > 0');
        return;
    }

    let formData = new FormData();
    formData.append('operacion', 'registrar_masivo');
    formData.append('tasa_dolar', tasa_dolar);
    formData.append('datos_presupuesto', JSON.stringify(datos));

    let respuesta = await Peticiones.enviar(formData, "", true);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    modal.hide();
    await consultarInformacionFormulario();
    tabla_presupuesto.replaceData();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

/**
 * Prepara el formulario para edición
 */
async function modificar_formulario(e) {
    if (document.getElementById("contenedor_presupuestos").childElementCount === 0) {
        await llenarDetallesPresupuestos();
    }

    let id = e.target.closest('button').value;

    let formData = new FormData();
    formData.append("id_presupuesto", id);
    formData.append('operacion', 'consulta_especifica');

    let respuesta = await Peticiones.enviar(formData, "", true);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    let presupuesto = respuesta.datos;

    // Mostrar la fecha en el select (puede que no esté en la lista, así que la creamos)
    let [anio, mes] = presupuesto.fecha.split('-');
    mes = parseInt(mes);
    let fechaStr = `${anio}-${mes.toString().padStart(2, '0')}-01`;

    let option = document.createElement("option");
    option.value = fechaStr;
    option.textContent = `${FormatoFechas.nombreMes(mes)} del ${anio}`.toUpperCase();
    option.id = 'fecha_editada';
    option.selected = true;
    select_mes.appendChild(option);
    select_mes.value = fechaStr;


    document.getElementById("observacion").value = presupuesto.observacion || '';
    document.getElementById("cuota_reserva").value = presupuesto.cuota_reserva;

    // Calcular el equivalente en dólares
    let inputConvertir = document.getElementById("cuota_reserva").closest(".row").querySelector("[convertido]");
    inputConvertir.value = (presupuesto.cuota_reserva / tasa_dolar).toFixed(2);

    // Marcar detalles existentes
    if (presupuesto.detalles && presupuesto.detalles.length > 0) {
        presupuesto.detalles.forEach(det => {
            let encontrado = false;
            // Buscar si ya existe un input de texto con ese nombre (gasto fijo)
            document.querySelectorAll("[type='text']").forEach(input => {
                if (input.value === det.nombre_detalle) {
                    input.closest(".row").querySelector("[type='number']").value = det.monto;
                    let convertido = input.closest(".row").querySelector("[convertido]");
                    convertido.value = (det.monto / tasa_dolar).toFixed(2);
                    encontrado = true;
                }
            });
            // Si no es gasto fijo, agregar nueva fila
            if (!encontrado) {
                let acordeon = document.querySelector(`[id_tipo_gasto='${det.tipo_gasto_id}']`);
                if (acordeon) {
                    let botonAgregar = acordeon.querySelector("[accion='agregar']");
                    if (botonAgregar) {
                        // Simular clic nativo para crear nueva fila de forma segura
                        let clickEvent = new MouseEvent('click', { bubbles: true, cancelable: true });
                        botonAgregar.dispatchEvent(clickEvent);
                        
                        // Pequeño delay para asegurar que el DOM se actualizó antes de buscar los inputs
                        setTimeout(() => {
                            let nuevosInputs = acordeon.querySelectorAll("[type='text']");
                            let ultimo = nuevosInputs[nuevosInputs.length - 1];
                            ultimo.value = det.nombre_detalle;
                            ultimo.closest(".row").querySelector("[type='number']").value = det.monto;
                            let convertido = ultimo.closest(".row").querySelector("[convertido]");
                            convertido.value = (det.monto / tasa_dolar).toFixed(2);
                        }, 10);
                    }
                }
            }
        });
    }

    if (permiso_modificar != 1) {
        boton_formulario.setAttribute("hidden", true);
    }

    boton_formulario.setAttribute("modificar", true);
    boton_formulario.setAttribute("id_modificar", id);
    boton_formulario.textContent = "Guardar Cambios";
    document.getElementById('titulo_modal').textContent = "Modificar presupuesto";
}

/**
 * modificar presupuesto existente
 */
async function modificar(id) {
    let datos = recolectarDatosPresupuesto(id);
    if (datos.detalles.length === 0) {
        Alertas.mostrar('warning', 'Atención', 'Debe agregar al menos un detalle con monto > 0');
        return;
    }

    let formData = new FormData();
    formData.append('operacion', 'modificar_masivo');
    formData.append('datos_presupuesto', JSON.stringify(datos));

    let respuesta = await Peticiones.enviar(formData, "", true);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }

    tabla_presupuesto.replaceData();
    modal.hide();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

// ============================================================
// ELIMINACIÓN
// ============================================================
function eventoEliminar(e) {
    let id = e.target.closest('button').value;
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Eliminar este presupuesto?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e01d22",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(result => {
        if (result.isConfirmed) eliminar(id);
    });
}

async function eliminar(id) {
    let formData = new FormData();
    formData.append("id_presupuesto", id);
    formData.append('operacion', 'eliminar');
    let respuesta = await Peticiones.enviar(formData);
    if (!respuesta.estatus) {
        Alertas.mostrar('error', 'Error', respuesta.mensaje);
        return;
    }
    tabla_presupuesto.replaceData();
    await consultarInformacionFormulario();
    Alertas.mostrar('success', 'Éxito', respuesta.mensaje);
}

// ============================================================
// MÓDULO DE AYUDA (DRIVER.JS) - PRESUPUESTO MENSUAL
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const driver = window.driver.js.driver;
    let tourActivo = null;

    // Micro-retraso para asegurar que la burbuja se ancle bien
    const alinearBurbuja = () => {
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 10);
    };

    // Configuración base para todos los tours
    const configBase = {
        showProgress: true,
        animate: true,
        smoothScroll: false, 
        allowKeyboardControl: false,
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        onHighlightStarted: (element) => {
            if (element) {
                element.scrollIntoView({ behavior: 'instant', block: 'center' });
                alinearBurbuja();
            }
        }
    };

    // 1. TOUR VISTA PRINCIPAL
    const stepsPrincipal = [
        { element: '.page-header', popover: { title: 'Gestión de Presupuestos', description: 'Aquí planificas los gastos del mes siguiente para calcular cuánto deberá pagar cada apartamento.', side: "bottom", align: 'center' } },
        // Usamos una lógica segura para encontrar el botón, incluso si está oculto por validaciones PHP
        { element: document.querySelector('#boton_registrar') || '.card', popover: { title: 'Nuevo Presupuesto', description: 'Si hay meses pendientes por planificar, usa este botón para iniciar la carga de gastos estimados.', side: "bottom", align: 'start' } },
        { element: '#tabla_presupuesto', popover: { title: 'Historial', description: 'Lista de presupuestos registrados. Puedes ver el monto total esperado y la cuota de reserva asignada.', side: 'top', align: 'center' } }
    ];

    // 2. TOUR MODAL DE REGISTRO
    const stepsModal = [
        { element: '#fecha', popover: { title: 'Periodo', description: 'Selecciona a qué mes corresponde este presupuesto. Solo aparecerán los meses futuros disponibles.', side: 'bottom', align: 'start' } },
        { element: '#cuota_reserva', popover: { title: 'Fondo de Reserva', description: 'Ingresa el monto destinado al fondo de reserva del condominio.', side: 'top', align: 'start' } },
        { element: '.boton_intercambio_cuota', popover: { title: 'Moneda', description: 'Usa este botón si necesitas ingresar el monto de la reserva en Dólares; el sistema hará la conversión.', side: 'top', align: 'start' } },
        { element: '#contenedor_presupuestos', popover: { title: 'Categorías de Gastos', description: 'Aquí aparecerán listados los tipos de gastos (Gas, Luz, Mantenimiento). Despliega cada acordeón para ingresar los montos estimados.', side: 'top', align: 'center' } },
        { element: '#observacion', popover: { title: 'Observaciones', description: 'Añade cualquier nota importante sobre este presupuesto.', side: 'top', align: 'start' } },
        { element: '#boton_formulario', popover: { title: 'Guardar', description: 'Registra el presupuesto para que luego puedas generar las mensualidades de cobro.', side: 'top', align: 'center' } }
    ];

    // LÓGICA DEL BOTÓN FLOTANTE
    const btnAyuda = document.getElementById('btn-ayuda-tour');
    const modalPresupuesto = document.getElementById('modal_presupuesto');

    if(btnAyuda) {
        btnAyuda.addEventListener('click', () => {
            if (modalPresupuesto && modalPresupuesto.classList.contains('show')) {
                // Si el modal está abierto, iniciamos el tour del formulario
                tourActivo = driver({ ...configBase, steps: stepsModal });
                tourActivo.drive();
            } else {
                // Si estamos en la tabla principal
                window.scrollTo({ top: 0, behavior: 'instant' });
                tourActivo = driver({ ...configBase, steps: stepsPrincipal });
                tourActivo.drive();
            }
        });
    }

    // Limpieza al cerrar el modal
    if (modalPresupuesto) {
        modalPresupuesto.addEventListener('hide.bs.modal', () => {
            if (tourActivo) {
                try { tourActivo.destroy(); } catch (e) {}
            }
        });
    }
});