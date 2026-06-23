import { useEffect, useRef } from 'react';
import { Animated } from 'react-native';

export const useProgresoAnimado = (porcentajeTarget, duracion = 1000) => {
  const animacionAncho = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(animacionAncho, {
      toValue: porcentajeTarget,
      duration: duracion,
      useNativeDriver: false, // width no es soportado por el driver nativo
    }).start();
  }, [porcentajeTarget, duracion]);

  // Retornamos la interpolación ya procesada para que el componente no haga cálculos visuales
  const anchoAnimado = animacionAncho.interpolate({
    inputRange: [0, 100],
    outputRange: ['0%', '100%'],
  });

  return {
    anchoAnimado,
  };
};