import { useEffect } from 'react';
import * as Notifications from 'expo-notifications';
import { useNavigation } from '@react-navigation/native';
import { usePermisos } from './usePermisos';

export const useInteraccionPush = () => {
  const navigation = useNavigation();
  const permisos = usePermisos(); 

  useEffect(() => {
    const subscription = Notifications.addNotificationResponseReceivedListener(response => {
      const data = response.notification.request.content.data;

      if (!data?.ruta) return;

      let rutaDestino = data.ruta;

      // Diccionario de reglas
      const reglasAcceso = {
        'Mensualidad': permisos.puedeVerMensualidad,
        'Gastos': permisos.puedeVerGastos,
        'Cartelera': permisos.puedeVerCartelera,
        'DetalleCartelera': permisos.puedeVerCartelera,
        'DetallePago': true, 
      };

      // Si no tiene permisos, lo mandamos a Inicio
      if (reglasAcceso[rutaDestino] === false) {
        console.warn(`Acceso denegado por Push a: ${rutaDestino}. Redirigiendo a Inicio.`);
        rutaDestino = 'Inicio'; 
      }

      // Definimos qué rutas pertenecen al BottomTabNavigator
      const rutasBottomTabs = ['Inicio', 'Pagos', 'Cartelera', 'Gastos', 'Mensualidad'];

      if (rutasBottomTabs.includes(rutaDestino)) {
        // Si va a una pestaña, navegamos primero al MainTabs y le decimos qué pantalla abrir adentro
        navigation.navigate('MainTabs', {
          screen: rutaDestino,
          params: {
            id_registro: data.id_registro,
            tabla_origen: data.tabla_origen
          }
        });
      } else {
        // Si va a una vista de Detalle (que están en el AppStack general), navega directo
        navigation.navigate(rutaDestino, {
          id_registro: data.id_registro,
          tabla_origen: data.tabla_origen
        });
      }
    });

    return () => subscription.remove();
  }, [navigation, permisos]);
};

// Componente "Wrapper" para montar dentro del Stack de Navegación
export const EscuchadorNotificaciones = () => {
  useInteraccionPush();
  return null; 
};