/**
 * EstadoInputs.js
 * Propósito: Cambiar el estado visual de los inputs (verde/rojo) y mostrar mensajes.
 */
const EstadoInputs = {
    marcarError(input, mensaje) {
        if (!input) return;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        const contenedor = input.closest('.input-group, .mi-input-group') || input.parentElement;
        
        const feedback = contenedor.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = mensaje;
            
            // colorear el ícono del ojo si existe (para contraseñas)
            const iconoOjo = contenedor.querySelector('.contra-btn i');
            if (iconoOjo) {
                iconoOjo.classList.remove('text-success');
                iconoOjo.classList.add('text-danger');
            }
        }
    },

    marcarExito(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        
        const contenedor = input.closest('.input-group, .mi-input-group') || input.parentElement;
        const feedback = contenedor.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.textContent = "";
            
            // Lógica para colorear el ícono del ojo si existe
            const iconoOjo = contenedor.querySelector('.contra-btn i');
            if (iconoOjo) {
                iconoOjo.classList.remove('text-danger');
                iconoOjo.classList.add('text-success');
            }
        }
    },

    limpiar(input) {
        if (!input) return;
        input.classList.remove('is-invalid', 'is-valid');
        
        const contenedor = input.closest('.input-group, .mi-input-group') || input.parentElement;
        const feedback = contenedor.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.textContent = '';
            
            const iconoOjo = contenedor.querySelector('.contra-btn i');
            if (iconoOjo) {
                iconoOjo.classList.remove('text-danger', 'text-success');
            }
        }
    }
};