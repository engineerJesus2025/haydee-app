import React from 'react';
import { Text, View, StyleSheet } from 'react-native';

import useValidaciones from '../hooks/useValidaciones';
import useRecuperarContrasenia from '../hooks/useRecuperarContrasenia';
import { useTema } from '../hooks/useTema';

import ModalGeneral from './ModalGeneral';
import CampoFormulario from './CampoFormulario';
import CustomBoton from './CustomBoton';
import InputOTP from './InputOTP';

const ModalRecuperarContrasena = ({ visible, onClose }) => {
  const { colores } = useTema();
  const validaciones = useValidaciones();

  // Inyectamos nuestro Hook Director con las nuevas funciones
  const {
    control,
    errors,
    isValid,
    loading,
    paso,
    handleCerrar,
    correoIngresado,
    onSubmitPaso1,
    onSubmitPaso2,
    onSubmitPaso3
  } = useRecuperarContrasenia(onClose);

  const renderBotones = () => {
    let tituloAccion = '';
    let eventoAccion = null;
    let iconoAccion = '';

    if (paso === 1) {
      tituloAccion = loading ? 'Enviando...' : 'Enviar Código';
      eventoAccion = onSubmitPaso1;
      iconoAccion = 'send';
    } else if (paso === 2) {
      tituloAccion = loading ? 'Validando...' : 'Validar Código';
      eventoAccion = onSubmitPaso2;
      iconoAccion = 'checkmark-circle-outline';
    } else {
      tituloAccion = loading ? 'Guardando...' : 'Restablecer Clave';
      eventoAccion = onSubmitPaso3;
      iconoAccion = 'save-outline'; 
    }

    return (
      <>
        <CustomBoton
          titulo='Cancelar'
          evento={handleCerrar}
          icono={{ nombre: 'close', color: '#fff' }}
          estilos={{ backgroundColor: '#95a5a6' }}
          fuente={16}
          disabled={loading}
        />
        <CustomBoton
          titulo={tituloAccion}
          evento={eventoAccion}
          icono={{ nombre: iconoAccion, color: '#fff' }}
          disabled={!isValid || loading}
          estilos={{ backgroundColor: '#27ae60', opacity: (isValid && !loading) ? 1 : 0.6 }}
          fuente={16}
          loading={loading}
        />
      </>
    );
  };

  return (
    <ModalGeneral
      visible={visible}
      onClose={handleCerrar}
      titulo="Recuperar Contraseña"
      iconoHeader={{ name: 'key-outline', color: '#E1E1F7' }}
      footer={renderBotones()}
      esFormulario={true}
    >
      <View style={styles.pasoContainer}>
        
        {paso === 1 && (
          <>
            <Text style={[styles.modalSubtitle, { color: colores.text }]}>
              Ingresa tu correo electrónico y te enviaremos un código de seguridad para restablecer tu contraseña.
            </Text>

            <CampoFormulario
              tituloLabel='Correo Electrónico'
              iconoLabel={{ nombre: 'mail-outline', color: '#3498db' }}
              control={control}
              name='correo'
              rules={validaciones.correo}
              iconoInput={{ nombre: 'mail', color: '#95a5a6' }}
              error={errors.correo}
              placeholder='Ejm: ejemplo@gmail.com'
              keyboardType='email-address'
              autoCapitalize='none'
              editable={!loading} 
            />
          </>
        )}

        {paso === 2 && (
          <>
            <Text style={[styles.modalSubtitle, { color: colores.text }]}>
              Hemos enviado un código de 6 dígitos a <Text style={{fontWeight: 'bold'}}>{correoIngresado}</Text>. 
              Ingrésalo a continuación.
            </Text>

            <InputOTP 
              control={control} 
              name="codigo" 
              error={errors.codigo} 
              longitud={6} 
            />
            {errors.codigo && (
              <Text style={{ color: '#e74c3c', textAlign: 'center', marginTop: -10, marginBottom: 10 }}>
                {errors.codigo.message || 'El código es requerido.'}
              </Text>
            )}
          </>
        )}

        {paso === 3 && (
          <>
            <Text style={[styles.modalSubtitle, { color: colores.text }]}>
              Código validado correctamente. Por favor, ingresa tu nueva contraseña a continuación.
            </Text>

            <CampoFormulario
              tituloLabel='Nueva Contraseña'
              iconoLabel={{ nombre: 'lock-closed-outline', color: '#3498db' }}
              control={control}
              name='contra'
              rules={validaciones.contra}
              iconoInput={{ nombre: 'key', color: '#95a5a6' }}
              error={errors.contra}
              placeholder='Mínimo 5 caracteres'
              esPassword={true} 
              editable={!loading}
            />
          </>
        )}

      </View>
    </ModalGeneral>
  );
};

const styles = StyleSheet.create({
  pasoContainer: {
    paddingBottom: 10,
  },
  modalSubtitle: { 
    fontSize: 15, 
    textAlign: 'center', 
    marginBottom: 22, 
    lineHeight: 22, 
    paddingHorizontal: 4 
  }
});

export default ModalRecuperarContrasena;