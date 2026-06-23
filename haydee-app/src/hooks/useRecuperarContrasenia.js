import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Alert } from 'react-native';
import clienteApi from '../utils/clienteApi';
import { procesarErrorApi } from '../utils/gestorErroresUI';

export default function useRecuperarContrasenia(onClose) {
  const [paso, setPaso] = useState(1);
  const [loading, setLoading] = useState(false);
  const [correoIngresado, setcorreoIngresado] = useState('');
  const [tokenAutorizacion, setTokenAutorizacion] = useState('');

  const { control, handleSubmit, formState: { isValid, errors }, reset } = useForm({
    mode: 'onChange',
    defaultValues: {
      correo: '',
      codigo: '',
      contra: ''
    }
  });

  // PASO 1: Solicitar código OTP al correo
  const solicitarCodigo = async (data) => {
    setLoading(true);
    try {
      const respuesta = await clienteApi.post('', 
      { correo: data.correo, operacion: 'solicitar_otp'}, {
      params: { endpoint: 'recuperar' },
      skipCrypto: true 
    });
      
      if (respuesta.data.estatus) {
        setPaso(2); 
        setcorreoIngresado(data.correo);
      }
    } catch (error) {
      procesarErrorApi(error);
    } finally {
      setLoading(false);
    }
  };

  // PASO 2: Validar el código OTP y obtener el Token de Autorización
  const validarCodigo = async (data) => {
    setLoading(true);
    try {
      const respuesta = await clienteApi.post('', { 
        correo: correoIngresado, 
        codigo: data.codigo,
        operacion: 'validar_otp'
      }, {
        params: { endpoint: 'recuperar'},
        skipCrypto: true
      });
      
      if (respuesta.data.estatus) {
        // Guardamos el token
        setTokenAutorizacion(respuesta.data.token_autorizacion);
        setPaso(3);
      }
    } catch (error) {
      procesarErrorApi(error);
    } finally {
      setLoading(false);
    }
  };

  // PASO 3: Restablecer la contraseña usando el Token de Autorización
  const restablecerContrasenia = async (data) => {
    setLoading(true);
    try {
      const respuesta = await clienteApi.post('', { 
        correo: correoIngresado, 
        token_autorizacion: tokenAutorizacion,
        contra: data.contra, 
        operacion: 'restablecer_con_token'
      }, {
        params: { endpoint: 'recuperar'},
        skipCrypto: true
      });
      
      if (respuesta.data.estatus) {
        Alert.alert("¡Éxito!", "Tu contraseña ha sido actualizada correctamente.", [
          { text: "Ingresar", onPress: () => handleCerrar() }
        ]);
      }
    } catch (error) {
      procesarErrorApi(error);
    } finally {
      setLoading(false);
    }
  };

  const handleCerrar = () => {
    reset();
    setPaso(1);
    setTokenAutorizacion('');
    onClose();
  };

  return {
    control,
    errors,
    isValid,
    loading,
    paso,
    handleCerrar,
    correoIngresado,
    onSubmitPaso1: handleSubmit(solicitarCodigo),
    onSubmitPaso2: handleSubmit(validarCodigo),
    onSubmitPaso3: handleSubmit(restablecerContrasenia)
  };
}