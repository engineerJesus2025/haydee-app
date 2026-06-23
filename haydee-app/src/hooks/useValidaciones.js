import { 
  CONTRA_REGEX, 
  CORREO_REGEX, 
  TITULO_REGEX, 
  DESCRIPCION_REGEX,
  MONTO_REGEX,
  REFERENCIA_REGEX
} from '../utils/regex';

export default function useValidaciones() {
  return {
    correo: {
      required: CORREO_REGEX.requerido,
      minLength: { value: CORREO_REGEX.limites.minimo, message: CORREO_REGEX.limites.mensaje_min },
      maxLength: { value: CORREO_REGEX.limites.maximo, message: CORREO_REGEX.limites.mensaje_max },
      pattern: { value: CORREO_REGEX.patron, message: CORREO_REGEX.mensaje },
      filtro: CORREO_REGEX.filtro 
    },
    contra: {
      required: CONTRA_REGEX.requerido,
      minLength: { value: CONTRA_REGEX.limites.minimo, message: CONTRA_REGEX.limites.mensaje_min },
      maxLength: { value: CONTRA_REGEX.limites.maximo, message: CONTRA_REGEX.limites.mensaje_max },
      pattern: { value: CONTRA_REGEX.patron, message: CONTRA_REGEX.mensaje },
      filtro: CONTRA_REGEX.filtro 
    },
    tituloPublicacion: { 
      required: TITULO_REGEX.requerido,
      minLength: { value: TITULO_REGEX.limites.minimo, message: TITULO_REGEX.limites.mensaje_min },
      maxLength: { value: TITULO_REGEX.limites.maximo, message: TITULO_REGEX.limites.mensaje_max },
      pattern: { value: TITULO_REGEX.patron, message: TITULO_REGEX.mensaje },
      filtro: TITULO_REGEX.filtro
    },
    descripcionPublicacion: {
      required: DESCRIPCION_REGEX.requerido,
      minLength: { value: DESCRIPCION_REGEX.limites.minimo, message: DESCRIPCION_REGEX.limites.mensaje_min },
      maxLength: { value: DESCRIPCION_REGEX.limites.maximo, message: DESCRIPCION_REGEX.limites.mensaje_max },
      pattern: { value: DESCRIPCION_REGEX.patron, message: DESCRIPCION_REGEX.mensaje },
      filtro: DESCRIPCION_REGEX.filtro 
    },
    monto: {
      required: MONTO_REGEX.requerido,
      pattern: { value: MONTO_REGEX.patron, message: MONTO_REGEX.mensaje },
      filtro: MONTO_REGEX.filtro 
    },
    referencia: {
      required: REFERENCIA_REGEX.requerido,
      minLength: { value: REFERENCIA_REGEX.limites.minimo, message: REFERENCIA_REGEX.limites.mensaje_min },
      maxLength: { value: REFERENCIA_REGEX.limites.maximo, message: REFERENCIA_REGEX.limites.mensaje_max },
      pattern: { value: REFERENCIA_REGEX.patron, message: REFERENCIA_REGEX.mensaje },
      filtro: REFERENCIA_REGEX.filtro 
    },
    descripcionGasto: {
      required: { value: true, message: 'La descripción es obligatoria' },
      maxLength: { value: 255, message: 'Máximo 255 caracteres' },
      pattern: { value: DESCRIPCION_REGEX.patron, message: DESCRIPCION_REGEX.mensaje }, // <-- Lo unificamos
      filtro: DESCRIPCION_REGEX.filtro 
    },
    // para Selects (Apartamentos, Bancos, etc.)
    requeridoSimple: (mensajePersonalizado = 'Este campo es obligatorio') => ({
      required: { value: true, message: mensajePersonalizado }
    })
  };
}