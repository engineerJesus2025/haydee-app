import { Alert } from 'react-native';
import { HTTP_CODIGO } from './HttpCodigos';

export const procesarErrorApi = (errorObj, manejadorEspecificoFormulario = null) => {
  // Si llega vacío ignora en silencio
  if (!errorObj) return; 

  // errores de red crudos de Axios 
  const esErrorDeRedCrudo = 
    errorObj.message === 'Network Error' || 
    errorObj.code === 'ERR_NETWORK' ||
    (!errorObj.response && errorObj.request);

  if (errorObj.tipo === 'API_ERROR' || errorObj.tipo === 'SERVIDOR') {
    
    if (manejadorEspecificoFormulario) {
      const errorManejado = manejadorEspecificoFormulario(
        errorObj.status, 
        errorObj.mensaje, 
        errorObj.errores
      );
      if (errorManejado) return; 
    }

    switch (errorObj.status) {
      case HTTP_CODIGO.RATE_LIMIT: // 429
        Alert.alert('Bloqueo de Seguridad', errorObj.mensaje);
        break;

      case HTTP_CODIGO.NO_AUTORIZADO: // 401
        Alert.alert('Acceso Denegado', errorObj.mensaje || 'Por favor, inicie sesión nuevamente.');
        break;

      case HTTP_CODIGO.PROHIBIDO: // 403
        Alert.alert(
          'Acceso Restringido', 
          errorObj.mensaje || 'No tiene los permisos requeridos (Rol) para realizar esta acción.'
        );
        break;

      case HTTP_CODIGO.BAD_REQUEST: // 400
        Alert.alert('Datos Inválidos', errorObj.mensaje);
        break;

      case HTTP_CODIGO.NO_ENCONTRADO: // 404
        Alert.alert(
          'Registro no encontrado', 
          errorObj.mensaje || 'El elemento que intenta consultar o modificar ya no existe.'
        );
        break;

      default:
        Alert.alert('Error del Servidor', errorObj.mensaje || 'Operación fallida.');
    }
    
  } else if (errorObj.tipo === 'RED' || esErrorDeRedCrudo) {
    Alert.alert(
      'Problema de conexión', 
      'No hay conexión a internet o el servidor no responde. Verifica tu señal.'
    );
  } else {
    const mensajeFinal = errorObj?.message || errorObj?.mensaje || 'Ocurrió un error desconocido.';
    Alert.alert('Error inesperado', mensajeFinal);
  }
};