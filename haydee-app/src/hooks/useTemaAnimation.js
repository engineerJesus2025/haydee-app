import { useEffect, useRef, useState } from 'react'
import { Animated, AccessibilityInfo } from 'react-native'

export const useTemaAnimation = (modoOscuro) => {
  const [mounted, setMounted] = useState(true)

  // 0 = claro, 1 = oscuro
  const anim = useRef(new Animated.Value(modoOscuro ? 0 : 1)).current

  useEffect(() => {
    // Inicializar con el valor actual del tema
    anim.setValue(modoOscuro ? 0 : 1)
    setMounted(true)
  }, [])

  useEffect(() => {
    // Animar cuando cambia el modo desde fuera
    Animated.timing(anim, {
      toValue: modoOscuro ? 0 : 1,
      duration: 420,
      useNativeDriver: true
    }).start()
  }, [modoOscuro])

  // Transformaciones y interpolaciones
  const rotate = anim.interpolate({
    inputRange: [0, 1],
    outputRange: ['0deg', '40deg']
  })

  const scale = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [1, 0.86]
  })

  const sunOpacity = anim.interpolate({
    inputRange: [0, 0.5],
    outputRange: [1, 0],
    extrapolate: 'clamp'
  })

  const moonOpacity = anim.interpolate({
    inputRange: [0.5, 1],
    outputRange: [0, 1],
    extrapolate: 'clamp'
  })

  const backgroundColor = anim.interpolate({
    inputRange: [0, 1],
    outputRange: ['rgba(255,255,255,0.06)', 'rgba(255,255,255,0.12)']
  })

  const sunScale = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [1, 0.9]
  })

  const moonTranslateY = anim.interpolate({
    inputRange: [0, 1],
    outputRange: [6, 0]
  })

  const anunciarCambioTema = () => {
    const label = modoOscuro ? 'Modo claro activado' : 'Modo oscuro activado'
    AccessibilityInfo.announceForAccessibility(label)
  }

  const getAccessibilityLabel = () => {
    return modoOscuro ? 'Activar modo claro' : 'Activar modo oscuro'
  }

  return {
    mounted,
    anim,
    rotate,
    scale,
    sunOpacity,
    moonOpacity,
    backgroundColor,
    sunScale,
    moonTranslateY,
    anunciarCambioTema,
    getAccessibilityLabel
  }
}
