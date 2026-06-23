import { View, Text, StyleSheet } from 'react-native'
import { useState, useRef } from 'react'

import useValidaciones from '../hooks/useValidaciones'
import useLogin from '../hooks/useLogin'
import { useTema } from '../hooks/useTema'

import InputFormulario from './InputFormulario'
import ErrorFormulario from '../components/ErrorFormulario'
import CustomBoton from '../components/CustomBoton'
import LabelInput from '../components/LabelInput'
import BotonRecuperarContra from '../components/BotonRecuperarContra'
import ModalRecuperarContrasena from './ModalRecuperarContrasena'

export default function FormularioLogin ({ botonBloqueadoPorSeguridad, estadoConexion, onReintentarConexion }) {
  const { colores } = useTema()
  const estilosFormulario = getEstilosFormularioLogin(colores)
  const contraRef = useRef(null);

  const {
    control,
    handleSubmit,
    isValid,
    errors,
    loading
  } = useLogin()
  const validaciones = useValidaciones()

  let textoBoton = 'Ingresar';
  let accionBoton = handleSubmit;
  let colorBoton = { backgroundColor: '#007BFF' };
  let iconoBoton = { nombre: 'send', color: '#fff' };
  let mostrarLoader = loading;
  if (estadoConexion === 'conectando') {
    textoBoton = 'Conectando...';
    mostrarLoader = true;
  } else if (estadoConexion === 'reintentando') {
    textoBoton = 'Reintentando...';
    mostrarLoader = true;
  } else if (estadoConexion === 'fallo') {
    textoBoton = 'Fallo de red. Reintentar';
    accionBoton = onReintentarConexion; // Sobrescribimos el submit para que el botón intente reconectar
    iconoBoton = { nombre: 'refresh-outline', color: '#fff' };
    colorBoton = { backgroundColor: '#e74c3c' }; // Lo pintamos de rojo para indicar el fallo
    mostrarLoader = false;
  } else if (loading) {
    textoBoton = 'Autenticando...';
  }

  const botonDesabilitado = (!isValid && estadoConexion === 'conectado') || estadoConexion === 'conectando' || estadoConexion === 'reintentando' || loading;
  
  const [modalRecuperarVisible, setModalRecuperarVisible] = useState(false)

  return (
    <View style={estilosFormulario.formContainer}>
      <Text style={estilosFormulario.title}>Iniciar sesión</Text>

      <LabelInput titulo='Correo Electrónico' icono={{ nombre: 'mail-outline', color: '#3498db' }} noDark={true} />
      <InputFormulario
        control={control}
        name='correo'
        rules={validaciones.correo}
        icono={{ nombre: 'mail', color: '#000' }}
        error={errors.correo}
        placeholder='Ejm: usuario@gmail.com'
        keyboardType='email-address'
        autoCapitalize='none'
        noDark={true}
        returnKeyType="next"
        blurOnSubmit={false}
        onSubmitEditing={() => contraRef.current?.focus()}
      />
      <ErrorFormulario error={errors.correo} />

      <LabelInput titulo='Contraseña' icono={{ nombre: 'lock-closed-outline', color: '#3498db' }} noDark={true} />
      <InputFormulario
        control={control}
        name='contra'
        rules={validaciones.contra}
        icono={{ nombre: 'lock-closed', color: '#000' }}
        error={errors.contra}
        placeholder='Ejm: 12345'
        keyboardType='password'
        autoCapitalize='none'
        noDark={true}
        inputRef={contraRef}
        returnKeyType="send"
        onSubmitEditing={handleSubmit}
        esPassword={true} 
      />
      <ErrorFormulario error={errors.contra} />

      <View style={{ marginBottom: 20, marginTop: 5 }}>
        <BotonRecuperarContra
          titulo='¿Olvidaste tu contraseña?'
          evento={() => setModalRecuperarVisible(true)}
          fuente={16}
        />
      </View>

      <CustomBoton
        titulo={textoBoton}
        evento={accionBoton}
        icono={iconoBoton}
        disabled={botonDesabilitado}
        loading={mostrarLoader}
        estilos={{...estilosFormulario.button,colorBoton}}
        fuente={18}
        noDark={true}
      />

      <ModalRecuperarContrasena
        visible={modalRecuperarVisible}
        onClose={() => setModalRecuperarVisible(false)}
      />
    </View>
  )
}

const getEstilosFormularioLogin = (colores) => StyleSheet.create({
  formContainer: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    paddingHorizontal: 20,
    paddingTop: 18,
    paddingBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 10,
    elevation: 8,
  },
  title: {
    fontSize: 22,
    fontWeight: '700',
    color: '#222',
    marginBottom: 20,
    textAlign: 'center'
  },
  button: {
    alignSelf: 'center',
  }
})