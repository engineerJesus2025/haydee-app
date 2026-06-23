import { useState, useEffect, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import clienteApi from '../utils/clienteApi';
import { criptografiaMovil } from '../utils/criptografiaMovil';
import { procesarErrorApi } from '../utils/gestorErroresUI';

export default function useHandshake() {
  const [llaveLista, setLlaveLista] = useState(false);
  const [estadoConexion, setEstadoConexion] = useState('conectando'); 

  const iniciarHandshake = useCallback(async (intento = 1) => {
    const MAX_INTENTOS = 3;

    try {
      if (intento > 1) setEstadoConexion('reintentando');
      else setEstadoConexion('conectando');

      const respuesta = await clienteApi.get('', { params: { endpoint: 'handshake' } });

      if (respuesta.data.estatus) {
        criptografiaMovil.setLlavePublica(respuesta.data.public_key);
        setLlaveLista(true);
        setEstadoConexion('conectado');
        console.log("Canal criptográfico inicial preparado.");

        sincronizarTasaDolar();
      }
    } catch (error) {
      console.error(`Fallo Handshake Inicial (Intento ${intento}):`, error);

      // Si el fallo es en la carga inicial de la app, aplicamos reintentos lineales
      if (intento < MAX_INTENTOS) {
        setTimeout(() => iniciarHandshake(intento + 1), 3000);
      } else {
        setEstadoConexion('fallo');
        procesarErrorApi(error);
      }
    }
  }, [sincronizarTasaDolar]);

  useEffect(() => {
    iniciarHandshake();
  }, [iniciarHandshake]);

  const sincronizarTasaDolar = useCallback(async () => {
    try {
      const fechaGuardada = await AsyncStorage.getItem('fecha_tasa_dolar');
      const hoy = new Date().toDateString();

      // Si ya se consultó hoy, no gastamos peticiones
      if (fechaGuardada && new Date(fechaGuardada).toDateString() === hoy) {
        console.log("DolarAPI: Caché del día de hoy válida.");
        return;
      }

      const respuesta = await fetch("https://ve.dolarapi.com/v1/dolares/oficial");
      if (!respuesta.ok) throw new Error('Error al conectar con DolarAPI');
      const data = await respuesta.json();
      
      await AsyncStorage.setItem('fecha_tasa_dolar', data.fechaActualizacion);
      await AsyncStorage.setItem('tasa_dolar', data.promedio.toFixed(2).toString());
      console.log(`DolarAPI: Tasa del día sincronizada: ${data.promedio.toFixed(2)} Bs.`);
    } catch (error) {
      console.warn("DolarAPI Silencioso: No se pudo actualizar, se mantendrá el fallback previo.", error.message);
    }
  }, []);

  return { 
    llaveLista, 
    estadoConexion, 
    reintentarManual: () => iniciarHandshake(1) 
  };
}