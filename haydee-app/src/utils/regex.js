/**
 * Expresiones regulares para validaciones (Sincronizado con Backend Zero Trust)
 */

export const CORREO_REGEX = {
  patron: /^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/,
  mensaje: 'Formato inválido. Ej: usuario@gmail.com',
  filtro: /[^a-zA-Z0-9_.@-]/g,
  limites: {
    minimo: 3,
    maximo: 50,
    mensaje_min: 'Al menos 3 caracteres',
    mensaje_max: 'Máximo 50 caracteres'
  },
  requerido: {
    value: true,
    message: 'El correo es obligatorio'
  }
}

export const CONTRA_REGEX = {
  patron: /^[A-Za-z0-9_.+*$#%&@-]{5,100}$/,
  mensaje: 'Solo letras, números y símbolos permitidos (_.+*$#%&@-)',
  filtro: /[^A-Za-z0-9_.+*$#%&@-]/g,
  limites: {
    minimo: 5,
    maximo: 100,
    mensaje_min: 'La contraseña debe tener mínimo 5 caracteres',
    mensaje_max: 'Máximo 100 caracteres'
  },
  requerido: {
    value: true,
    message: 'Contraseña requerida'
  }
}

// ---------------- CARTELERA VIRTUAL ----------------

export const TITULO_REGEX = {
  patron: /^[A-Za-zÁÉÍÓÚáéíóúñÑ ]{3,100}$/,
  filtro: /[^A-Za-zÁÉÍÓÚáéíóúñÑ ]/g,
  mensaje: 'Contiene caracteres no permitidos',
  limites: {
    minimo: 3,
    maximo: 200,
    mensaje_min: 'El título debe tener mínimo 3 caracteres',
    mensaje_max: 'El título no puede exceder 200 caracteres'
  },
  requerido: {
    value: true,
    message: 'El título es requerido'
  }
}

export const DESCRIPCION_REGEX = {
  patron: /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.,;:'"()¡!¿?]{3,200}$/,
  filtro: /[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.,;:'"()¡!¿? \n]/g,
  mensaje: 'Contiene caracteres no permitidos',
  limites: {
    minimo: 3,
    maximo: 200,
    mensaje_min: 'La descripción debe tener mínimo 3 caracteres',
    mensaje_max: 'La descripción no puede exceder 200 caracteres'
  },
  requerido: {
    value: true,
    message: 'La descripción es requerida'
  }
}

// ---------------- PAGOS Y FINANZAS ----------------

export const MONTO_REGEX = {
  patron: /^\d+(\.\d{1,2})?$/,
  mensaje: 'Formato inválido. Ej. 150.50 (Usa punto para decimales)',
  filtro: /[^0-9.]/g,
  requerido: {
    value: true,
    message: 'El monto es obligatorio'
  }
}

export const REFERENCIA_REGEX = {
  patron: /^[a-zA-Z0-9-]{4,20}$/,
  mensaje: 'Solo letras, números y guiones (sin espacios)',
  filtro: /[^a-zA-Z0-9-]/g,
  limites: {
    minimo: 4,
    maximo: 20,
    mensaje_min: 'Mínimo 4 caracteres',
    mensaje_max: 'Máximo 20 caracteres'
  },
  requerido: {
    value: true,
    message: 'La referencia es obligatoria'
  }
}