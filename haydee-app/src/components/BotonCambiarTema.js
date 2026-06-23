import React, { useEffect, useRef, useState } from 'react';
import { TouchableOpacity, Animated, StyleSheet, View, Image } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useDispatch } from 'react-redux';

import { cambiarTema } from '../store/slices/temaSlice';
import { useTema } from '../hooks/useTema';

export default function BotonCambiarTema() {
  const dispatch = useDispatch();
  const { modoOscuro } = useTema();
  
  // Estado para bloquear el botón temporalmente
  const [deshabilitado, setDeshabilitado] = useState(false);
  
  // 0 = Modo Claro (Día), 1 = Modo Oscuro (Noche)
  const anim = useRef(new Animated.Value(modoOscuro ? 0 : 1)).current;

  useEffect(() => {
    Animated.timing(anim, {
      toValue: modoOscuro ? 0 : 1,
      duration: 350, 
      useNativeDriver: false, 
    }).start();
  }, [modoOscuro]);

  const handlePress = () => {
    if (deshabilitado) return;
    setDeshabilitado(true);   

    dispatch(cambiarTema());

    setTimeout(() => {
      setDeshabilitado(false);
    }, 450); 
  };

  // Movimiento: 0 (Día) -> Izquierda, 1 (Noche) -> Derecha
  const translateX = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [62, 4] 
  });

  const dayOpacity = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [1, 0]
  });

  const nightOpacity = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [0, 1]
  });

  const glowColor = anim.interpolate({
    inputRange: [0, 1],
    outputRange: ['rgba(255, 215, 0, 0.9)', 'rgba(0, 229, 255, 0.5)'] 
  });

  return (
    <TouchableOpacity 
      activeOpacity={0.9} 
      onPress={handlePress}
      disabled={deshabilitado}
    >
      
      {/* CAPA EXTERIOR (NEÓN Y BORDE) */}
      <Animated.View style={[
        styles.glowWrapper,
        { 
          borderColor: glowColor, 
          shadowColor: glowColor  
        } 
      ]}>
        
        {/* CAPA INTERIOR (CORTA LAS IMÁGENES) */}
        <View style={styles.innerContainer}>
          
          {/* FONDO DE DÍA */}
          <Animated.View style={[styles.bgImageContainer, { opacity: dayOpacity }]}>
            <Image 
              source={require('../../assets/dia_switch.png')} 
              style={styles.bgImage} 
            />
          </Animated.View>

          {/* FONDO DE NOCHE */}
          <Animated.View style={[styles.bgImageContainer, { opacity: nightOpacity }]}>
            <Image 
              source={require('../../assets/noche_switch.png')} 
              style={styles.bgImage} 
            />
          </Animated.View>

          {/* EL CÍRCULO DESLIZABLE (THUMB) */}
          <Animated.View style={[styles.thumb, { transform: [{ translateX }] }, {marginLeft:modoOscuro ? -3:0}]}>
            <Icon
              name={modoOscuro ? "sunny-outline" : "moon-outline"} 
              size={22}
              color={modoOscuro ? '#ffd700' : '#00e5ffee'} 
            />
          </Animated.View>
          
        </View>
      </Animated.View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  glowWrapper: {
    width: 100, 
    height: 42,
    borderRadius: 21, 
    backgroundColor: '#1a1a2e', 
    borderWidth: 2,
    elevation: 10,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 1,
    shadowRadius: 10, 
  },
  innerContainer: {
    flex: 1,
    borderRadius: 19, 
    overflow: 'hidden', 
  },
  bgImageContainer: {
    ...StyleSheet.absoluteFillObject,
    width: '100%',
    height: '150%',
    top: -12      
  },
  bgImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  thumb: {
    width: 34,
    height: 34,
    marginTop: 2,
    borderRadius: 17,
    backgroundColor: '#1a1a2e', 
    justifyContent: 'center',
    alignItems: 'center',
    position: 'absolute', 
  }
});