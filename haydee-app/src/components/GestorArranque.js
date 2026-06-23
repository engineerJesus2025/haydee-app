import React, { useEffect, useState, useRef } from 'react';
import { View, Text, StyleSheet, Animated } from 'react-native';
import { useDispatch } from 'react-redux';
import axios from 'axios';
import * as SplashScreen from 'expo-splash-screen';
import LottieView from 'lottie-react-native';

import { bootSilencioso } from '../store/slices/usuarioSlice';
import { cargarTemaGlobal } from '../store/slices/temaSlice'; 
import Navigation from '../navigation/Navigation';
import { criptografiaMovil } from '../utils/criptografiaMovil';
import { useTema } from '../hooks/useTema';

const URL_BASE = process.env.EXPO_PUBLIC_URL_BASE;

SplashScreen.preventAutoHideAsync();
    
export default function GestorArranque() {
  const dispatch = useDispatch();
  const { colores } = useTema();
  
  // Estados para controlar las fases
  const [bootFinalizado, setBootFinalizado] = useState(false);
  const [mostrarAnimacion, setMostrarAnimacion] = useState(true);
  
  // Controlador de la opacidad para el desvanecimiento suave
  const fadeAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    const inicializarApp = async () => {
      const tiempoInicio = Date.now(); 
      try {
        // Cargar preferencias visuales del usuario ANTES que todo
        await dispatch(cargarTemaGlobal()).unwrap();

        //  HANDSHAKE CRIPTOGRÁFICO
        const resHandshake = await axios.get(`${URL_BASE}/api/index.php`, {
          params: { endpoint: 'handshake' },
          timeout: 5000
        });

        if (resHandshake.data?.estatus) {
          criptografiaMovil.setLlavePublica(resHandshake.data.public_key);
        }

        // RESTAURACIÓN SILENCIOSA DE SESIÓN
        await dispatch(bootSilencioso()).unwrap();

      } catch (error) {
        console.log("Arranque finalizado sin sesión o con error:", error.message || error);
      } finally {
        // Marcamos que la lógica terminó.
        setBootFinalizado(true);

        const tiempoTranscurrido = Date.now() - tiempoInicio;
        const tiempoEspera = Math.max(0, 2000 - tiempoTranscurrido);

        // INICIAR EL FADE OUT
        setTimeout(() => {
          Animated.timing(fadeAnim, {
            toValue: 0, // Llevar la opacidad a 0 (invisible)
            duration: 600, // 600ms de desvanecimiento ultra fluido
            useNativeDriver: true, // Aceleración por hardware
          }).start(() => {
            // Cuando la animación termina, desmontamos Lottie del procesador
            setMostrarAnimacion(false);
          });
        }, tiempoEspera);
      }
    };

    inicializarApp();
  }, [dispatch]);

  const onLottieMontado = async () => {
    await SplashScreen.hideAsync();
  };

  return (
    <View style={{ flex: 1 }}>
      
      {bootFinalizado && <Navigation />}

      {/* LA PANTALLA DE CARGA*/}
      {mostrarAnimacion && (
        <Animated.View 
          style={[
            styles.contenedorOverlay, 
            { 
              backgroundColor: colores.background, 
              opacity: fadeAnim, 
              position: bootFinalizado ? 'absolute' : 'relative',
            }
          ]} 
          onLayout={onLottieMontado}
        >
          <LottieView
            source={require('../../assets/animacion-carga.json')} 
            autoPlay
            loop
            style={styles.animacionEdificio}
          />
          <Text style={[styles.textoCargando, { color: colores.textTitle }]}>
            Cargando...
          </Text>
        </Animated.View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  contenedorOverlay: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    top: 0, bottom: 0, left: 0, right: 0,
    zIndex: 9999, // que tape toda la navegación
  },
  animacionEdificio: {
    width: 280,
    height: 280,
  },
  textoCargando: {
    marginTop: 20,
    fontSize: 16,
    fontWeight: '600',
    letterSpacing: 1.5,
    textTransform: 'uppercase',
  },
});