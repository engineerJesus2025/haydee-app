import React, { useEffect, useRef } from 'react';
import { Text, StyleSheet, Animated, Easing } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';

export default function BannerPagosPendientes({ visible }) {
  const { modoOscuro, colores } = useTema();
  
  const animacionCrecimiento = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Disparar animación según la prop 'visible'
    Animated.timing(animacionCrecimiento, {
      toValue: visible ? 1 : 0,
      duration: 450, // Un poco más lento para que se note la elegancia de francia
      easing: Easing.bezier(0.33, 1, 0.68, 1), // Curva de salida suave (Out-Expo)
      useNativeDriver: true, // Optimización por hardware
    }).start();
  }, [visible]);

  // 3. Interpolaciones para suavizar los efectos
  const escala = animacionCrecimiento.interpolate({
    inputRange: [0, 1],
    outputRange: [0.92, 1], // Escala sutil
  });

  const desplazamiento = animacionCrecimiento.interpolate({
    inputRange: [0, 1],
    outputRange: [-10, 0], // Sube ligeramente mientras aparece
  });

  const themeStyles = {
    backgroundColor: modoOscuro ? 'rgba(243, 156, 18, 0.15)' : '#FFF8E1',
    borderColor: modoOscuro ? 'rgba(243, 156, 18, 0.4)' : '#FFE082',
    iconColor: modoOscuro ? '#FFCA28' : '#F39C12',
    textColor: modoOscuro ? colores.textTitle : '#5D4037',
  };

  return (
    <Animated.View 
      style={[
        styles.container, 
        { 
          backgroundColor: themeStyles.backgroundColor, 
          borderColor: themeStyles.borderColor,
          opacity: animacionCrecimiento, 
          transform: [
            { scale: escala },
            { translateY: desplazamiento }
          ]
        }
      ]}
    >
      <Icon 
        name="time-outline" 
        size={24} 
        color={themeStyles.iconColor} 
        style={styles.icon} 
      />
      <Text style={[styles.text, { color: themeStyles.textColor }]}>
        Tienes pagos en verificación. Tu deuda se actualizará en cuanto el administrador los procese.
      </Text>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 12,
    borderRadius: 8,
    marginTop: 10,
    marginBottom: 5,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
  },
  icon: {
    marginRight: 12,
  },
  text: {
    fontSize: 13,
    fontWeight: '500',
    flex: 1,
    lineHeight: 18,
  },
});