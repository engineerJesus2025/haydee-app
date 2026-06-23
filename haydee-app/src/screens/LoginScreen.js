import { useState, useEffect } from 'react'; 
import { ImageBackground, Dimensions, StyleSheet } from 'react-native'
import { KeyboardAwareScrollView } from 'react-native-keyboard-aware-scroll-view' 

import useHandshake from '../hooks/useHandshake';
import FormularioLogin from '../components/FormularioLogin'
import clienteApi from '../utils/clienteApi';
import { criptografiaMovil } from '../utils/criptografiaMovil';

const { height: screenHeight } = Dimensions.get('window')

export default function LoginScreen () {
  const estilosLogin = getEstilosLogin()
  
  const { llaveLista, estadoConexion, reintentarManual } = useHandshake();

  return (
    <ImageBackground
      source={require('../../assets/apartament.jpg')}
      style={[estilosLogin.bg, { flex: 1 }]}
      imageStyle={estilosLogin.bgImage}
    >
      <KeyboardAwareScrollView
        style={{ flex: 1, width: '100%' }} 
        contentContainerStyle={{ 
          flexGrow: 1, 
          justifyContent: 'center', 
          alignItems: 'stretch', 
          padding: 20,
          minHeight: screenHeight 
        }}
        enableOnAndroid={true}
        extraScrollHeight={20} 
        keyboardShouldPersistTaps='handled' 
        showsVerticalScrollIndicator={false}
        bounces={false} 
      >
        {/* Pasamos la variable para bloquear el botón si la llave no ha llegado */}
        <FormularioLogin 
          botonBloqueadoPorSeguridad={!llaveLista} 
          estadoConexion={estadoConexion}
          onReintentarConexion={reintentarManual}
        />
      </KeyboardAwareScrollView>
    </ImageBackground>
  )
}

const getEstilosLogin = () => StyleSheet.create({
  bg: {
    flex: 1,
    width: '100%',
    justifyContent: 'center',
    alignItems: 'center'
  },
  bgImage: {
    resizeMode: 'cover'
  }
})