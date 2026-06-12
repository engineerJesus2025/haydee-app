document.addEventListener("DOMContentLoaded", function () {
    const selectClasificacion = document.getElementById("clasificacion");
    if (selectClasificacion) {
        selectClasificacion.addEventListener("change", function () { Validador.evaluarSelect(this.id); });
    }

    const selectTipoGasto = document.getElementById("tipo_gasto_id");
    if (selectTipoGasto) {
        selectTipoGasto.addEventListener("change", async function () {
            if (!Validador.evaluarSelect(this.id)) return;
            await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'tipo_gasto', nombre_clave: 'id_tipo_gasto', valor: this.value }, this, 'El tipo seleccionado no existe');
        });
    }

    const selectProveedor = document.getElementById("proveedor_id");
    if (selectProveedor) {
        selectProveedor.addEventListener("change", async function () {
            if (!Validador.evaluarSelect(this.id)) return;
            await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'proveedores', nombre_clave: 'id_proveedor', valor: this.value }, this, 'El proveedor seleccionado no existe');
        });
    }

    const inputSolicitud = document.getElementById("solicitud");
    if (inputSolicitud) {
        inputSolicitud.addEventListener("change", async function () {
            if (this.value === "") {
                EstadoInputs.limpiar(this);
                return;
            }
            if (!Patrones.digitos.test(this.value)) {
                EstadoInputs.marcarError(this, "ID inválido");
                return;
            }
            await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'solicitudes_gasto', nombre_clave: 'id_solicitud', valor: this.value }, this, 'La solicitud no existe');
        });
    }

    const inputDescGasto = document.getElementById("descripcion_gasto");
    if (inputDescGasto) {
        inputDescGasto.addEventListener("keyup", function () {
            Validador.evaluarInput(this, Patrones.textoLargo, "La descripción debe tener al menos 10 caracteres");
        });
    }

    // ============================================================
    // VALIDACIONES EN TIEMPO REAL - DETALLES (Delegación)
    // ============================================================
    const contenedor = document.getElementById("detalles-container");

    if (contenedor) {
        // Evento CHANGE
        contenedor.addEventListener("change", async function (e) {
            const target = e.target;

            if (target.classList.contains("fecha_detalle")) {
                Validador.evaluarFecha(target, "Fecha inválida");
            }
            else if (target.classList.contains("metodo_pago")) {
                Validador.evaluarInput(target, Patrones.textoCorto, "Método de pago inválido");
            }
            else if (target.classList.contains("banco")) {
                if (target.value === "") {
                    EstadoInputs.marcarError(target, "Seleccione un banco");
                    return;
                }
                if (!Patrones.digitos.test(target.value)) {
                    EstadoInputs.marcarError(target, "ID inválido");
                    return;
                }
                await Validador.verificarExistenciaEnServidor('validar_clave_foranea', { tabla: 'bancos', nombre_clave: 'id_banco', valor: target.value }, target, 'El banco no existe');
            }
            else if (target.classList.contains("referencia")) {
                if (Validador.evaluarInput(target, Patrones.referenciaBancaria, "De 4 a 20 caracteres alfanuméricos")) {
                    // Capturamos el ID del gasto si estamos modificando
                    const idGastoActual = document.getElementById("boton_formulario").dataset.id || ""; 
                    
                    Validador.verificarDatoUnico(
                        'referencia', 
                        { referencia: target.value, id_gasto: idGastoActual }, 
                        target, 
                        'Referencia en uso'
                    );
                }
            }
            // --- Mostrar nombre de la imagen ---
            else if (target.classList.contains("imagen")) {
                const bloque = target.closest(".detalle-gasto");
                const textoNombre = bloque.querySelector(".nombre_imagen_cargada");
                
                if (target.files && target.files.length > 0) {
                    // Si hay archivo, mostramos el nombre y quitamos el borde rojo (si lo tuviera)
                    textoNombre.textContent = "Archivo seleccionado: " + target.files[0].name;
                    target.classList.remove("is-invalid");
                    target.classList.add("is-valid");
                } else {
                    // Si el usuario cancela la selección, limpiamos
                    textoNombre.textContent = "";
                    target.classList.remove("is-valid");
                }
            }
        });

        // Evento KEYPRESS
        contenedor.addEventListener("keypress", function (e) {
            const target = e.target;
            if (target.classList.contains("monto")) {
                Validador.bloquearTeclasInvalidas(e, Patrones.teclasMonto);
            }
            else if (target.classList.contains("referencia")) {
                Validador.bloquearTeclasInvalidas(e, /^[0-9]$/); // Solo números según el original
            }
        });

        // Evento KEYUP
        contenedor.addEventListener("keyup", function (e) {
            const target = e.target;
            if (target.classList.contains("monto")) {
                Validador.evaluarInput(target, Patrones.monto, "Monto inválido (ej: 150.50)");
            }
            else if (target.classList.contains("descripcion_detalle_gasto")) {
                Validador.evaluarInput(target, Patrones.textoBreve, "Mínimo 3 caracteres");
            }
            else if (target.classList.contains("referencia")) {
                // Según el original era \d{4,20}
                Validador.evaluarInput(target, /^\d{4,20}$/, "Solo números, de 4 a 20 dígitos");
            }
        });
    }

    // VALIDACIÓN AL ENVIAR EL FORMULARIO
    const btnFormulario = document.getElementById("boton_formulario");
    if (btnFormulario) {
        btnFormulario.addEventListener("click", async function (e) {
            e.preventDefault();
            const accion = this.hasAttribute("modificar") ? "modificar" : "Registrar";

            if (await validarFormularioCompleto()) {
                Alertas.confirmarAccion(
                    "Confirmar Operación",
                    `¿Está seguro que desea ${accion.toLowerCase()} este gasto?`,
                    "question", 
                    () => {
                        if (accion === "Registrar") {
                            registrar();
                        } else {
                            modificar(this.getAttribute("id_modificar"));
                        }
                    }
                );
            }
        });
    }

}); // Fin DOMContentLoaded

// ============================================================
// FUNCIONES AUXILIARES DE VALIDACIÓN
// ============================================================

async function validarFormularioCompleto() {
    // 1. Validar campos principales
    const selectClasificacion = document.getElementById("clasificacion");
    const selectTipoGasto = document.getElementById("tipo_gasto_id");
    const selectProveedor = document.getElementById("proveedor_id");
    const inputSolicitud = document.getElementById("solicitud");
    const inputDescGasto = document.getElementById("descripcion_gasto");

    if (!Validador.evaluarSelect(selectClasificacion.id)) {
        Alertas.mostrar("error", "Error", "Debe seleccionar una clasificación");
        return false;
    }

    if (!Validador.evaluarSelect(selectTipoGasto.id)) {
        Alertas.mostrar("error", "Error", "Debe seleccionar un tipo de gasto");
        return false;
    }
    
    const tipoValido = await Validador.verificarExistenciaEnServidor("validar_clave_foranea", { tabla: "tipo_gasto", nombre_clave: "id_tipo_gasto", valor: selectTipoGasto.value }, selectTipoGasto, "El tipo no existe");
    if (!tipoValido) return false;

    if (!Validador.evaluarSelect(selectProveedor.id)) {
        Alertas.mostrar("error", "Error", "Debe seleccionar un proveedor");
        return false;
    }
    
    const provValido = await Validador.verificarExistenciaEnServidor("validar_clave_foranea", { tabla: "proveedores", nombre_clave: "id_proveedor", valor: selectProveedor.value }, selectProveedor, "El proveedor no existe");
    if (!provValido) return false;

    if (inputSolicitud && inputSolicitud.value.trim() !== "") {
        if (!Patrones.digitos.test(inputSolicitud.value)) {
            EstadoInputs.marcarError(inputSolicitud, "ID inválido");
            Alertas.mostrar("error", "Error", "La solicitud tiene formato inválido");
            return false;
        }
        const solValida = await Validador.verificarExistenciaEnServidor("validar_clave_foranea", { tabla: "solicitudes_gasto", nombre_clave: "id_solicitud", valor: inputSolicitud.value }, inputSolicitud, "La solicitud no existe");
        if (!solValida) return false;
    }

    if (!Validador.evaluarInput(inputDescGasto, Patrones.textoLargo, "La descripción general debe tener al menos 10 caracteres")) {
        Alertas.mostrar("error", "Error", "La descripción general debe tener al menos 10 caracteres");
        return false;
    }

    // 2. Validar que haya al menos un detalle (El -1 que tenías en el bucle original asumo es por alguna fila "plantilla" oculta. Lo respetamos).
    const bloques = document.querySelectorAll(".detalle-gasto");
    if (bloques.length === 0 || (bloques.length === 1 && bloques[0].classList.contains("d-none"))) {
        Alertas.mostrar("error", "Error", "Debe agregar al menos un detalle de gasto");
        return false;
    }

    // 3. Validar cada detalle
    for (let i = 0; i < bloques.length; i++) {
        const bloque = bloques[i];
        const num = i + 1;

        if (!Validador.evaluarFecha(bloque.querySelector(".fecha_detalle"))) {
            Alertas.mostrar("error", `Detalle #${num}`, "Fecha inválida");
            return false;
        }

        const metodoSelect = bloque.querySelector(".metodo_pago");
        if (!Validador.evaluarInput(metodoSelect, Patrones.textoCorto, "Método inválido")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Seleccione un método de pago válido");
            return false;
        }

        const montoInput = bloque.querySelector(".monto");
        if (!Validador.evaluarInput(montoInput, Patrones.monto, "Monto inválido")) {
            Alertas.mostrar("error", `Detalle #${num}`, "Monto inválido (use números y hasta 2 decimales)");
            return false;
        }

        const grupoRef = bloque.querySelector(".grupo_bancario");
        if (grupoRef && !grupoRef.classList.contains("d-none")) {
            const refInput = bloque.querySelector(".referencia");
            if (!Validador.evaluarInput(refInput, Patrones.referenciaBancaria, "Referencia inválida")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Referencia bancaria inválida");
                return false;
            }

            const idGastoActual = document.getElementById("boton_formulario").dataset.id || ""; 

            const refValida = await Validador.verificarDatoUnico(
                'referencia', 
                { referencia: refInput.value, id_gasto: idGastoActual }, 
                refInput, 
                'Referencia en uso'
            );

            if (!refValida) {
                Alertas.mostrar("error", `Detalle #${num}`, "La referencia bancaria ya está registrada en otro gasto");
                return false;
            }
            

            const bancoSelect = bloque.querySelector(".banco");
            if (!Validador.evaluarInput(bancoSelect, Patrones.digitos, "Seleccione un banco")) {
                Alertas.mostrar("error", `Detalle #${num}`, "Debe seleccionar un banco");
                return false;
            }
            
            const banValido = await Validador.verificarExistenciaEnServidor("validar_clave_foranea", { tabla: "bancos", nombre_clave: "id_banco", valor: bancoSelect.value }, bancoSelect, "El banco no existe");
            if (!banValido) {
                Alertas.mostrar("error", `Detalle #${num}`, "El banco seleccionado no existe");
                return false;
            }

            const inputImagen = bloque.querySelector(".imagen");
            const nombreImagen = bloque.querySelector(".nombre_imagen_cargada")?.textContent.trim();
            const hayArchivo = inputImagen && inputImagen.files.length > 0;
            const hayImagenPrevia = nombreImagen && nombreImagen !== "";

            if (!hayArchivo && !hayImagenPrevia) {
                if (inputImagen) {
                    EstadoInputs.marcarError(inputImagen, "Debe adjuntar el comprobante");
                }
                Alertas.mostrar("error", `Detalle #${num}`, "Debe adjuntar un comprobante de pago");
                return false;
            } else {
                if (inputImagen) {
                    inputImagen.classList.remove("is-invalid");
                }
            }
        }
    }

    return true;
}