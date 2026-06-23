import * as Device from 'expo-device';
import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';
import Constants from 'expo-constants';
import clienteApi from '../utils/clienteApi';

export const CANALES_PUSH = Object.freeze({
  IMPORTANTE: 'haydee-importante', // <-- Refactorizado
  SILENCIOSO: 'haydee-silencioso',
  DEFAULT: 'haydee-default'
});

const CONFIGURACION_CANALES = Object.freeze([
  {
    id: CANALES_PUSH.IMPORTANTE,
    name: 'Avisos Importantes', // Nombre que verá el usuario en los ajustes de su teléfono
    importance: Notifications.AndroidImportance.MAX,
    vibrationPattern: [0, 250, 250, 250],
    lightColor: '#FF231F7C',
  },
  {
    id: CANALES_PUSH.SILENCIOSO,
    name: 'Finanzas y Pagos',
    importance: Notifications.AndroidImportance.LOW,
    vibrationPattern: [0, 0, 0, 0], 
    lightColor: '#00000000',
  },
  {
    id: CANALES_PUSH.DEFAULT,
    name: 'General',
    importance: Notifications.AndroidImportance.DEFAULT,
  }
]);

// HANDLER GLOBAL
Notifications.setNotificationHandler({
  handleNotification: async (notification) => {
    const isSilencioso = notification.request.trigger.channelId === CANALES_PUSH.SILENCIOSO;
    return {
      shouldShowBanner: true,
      shouldShowList: true,
      shouldPlaySound: !isSilencioso,
      shouldSetBadge: false,
    };
  },
});

// FUNCIONES PRIVADAS

const configurarCanalesAndroid = async () => {
  if (Platform.OS !== 'android') return;
  
  await Promise.all(
    CONFIGURACION_CANALES.map(canal => 
      Notifications.setNotificationChannelAsync(canal.id, canal)
    )
  );
};

const solicitarPermisosPush = async () => {
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  if (existingStatus === 'granted') return true;

  const { status } = await Notifications.requestPermissionsAsync();
  return status === 'granted';
};

const sincronizarTokenConBackend = async (expoToken) => {
  const paquete = {
    operacion: 'registrar_suscripcion_movil',
    expo_token: expoToken,
    plataforma: Platform.OS.toUpperCase()
  };

  const respuesta = await clienteApi.post('', paquete, {
      params: { endpoint: 'suscripcion_push' } 
  });
  
  // colapsos del servidor (HTML en vez de JSON)
  if (typeof respuesta.data === 'string') {
    throw new Error('El servidor PHP devolvió HTML. Verifica el error_log.');
  }

  if (!respuesta.data?.estatus) {
    throw new Error(respuesta.data?.mensaje || JSON.stringify(respuesta.data?.errores));
  }

  return respuesta.data;
};

// FUNCIÓN PRINCIPAL
export async function registrarSuscripcionPush() {
  if (!Device.isDevice) {
    console.log('Las notificaciones Push no funcionan en simuladores.');
    return false;
  }

  try {
    const permisosConcedidos = await solicitarPermisosPush();
    if (!permisosConcedidos) {
      console.warn('Permisos Push denegados por el usuario.');
      return false;
    }

    await configurarCanalesAndroid();

    const projectId = Constants.expoConfig?.extra?.eas?.projectId ?? Constants.easConfig?.projectId;
    const tokenResponse = await Notifications.getExpoPushTokenAsync({ projectId });
    
    const resultadoSincronizacion = await sincronizarTokenConBackend(tokenResponse.data);
    
    console.log('BACKEND:', resultadoSincronizacion.mensaje);
    return true;

  } catch (error) {
    const mensajeError = error.mensaje || error.message || String(error);
    console.error('Error en el flujo Push:', mensajeError);
    throw error; 
  }
}