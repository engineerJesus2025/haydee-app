import React from 'react';
import { Modal, View, ScrollView, KeyboardAvoidingView, Platform, Keyboard, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';  
import { useTema } from '../hooks/useTema';

import HeaderFormulario from './HeaderFormulario';

export default function ModalGeneral({
  visible,
  onClose,
  titulo,
  iconoHeader,
  children,
  footer, 
  esFormulario = false
}) {
  const { colores } = useTema();

  const ejecutarConTecladoCerrado = (accion) => {
    Keyboard.dismiss();
    setTimeout(() => {
      if (accion) accion();
    }, 300);
  };

  const ContenedorTeclado = esFormulario ? KeyboardAvoidingView : View; // otro level

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={false}
      onRequestClose={() => ejecutarConTecladoCerrado(onClose)}
      statusBarTranslucent={true} // Permite dibujar detrás de la barra de estado
    >
      {/* barra de estado a oscuras */}
      <StatusBar backgroundColor="#000000" barStyle="light-content" />

      <SafeAreaView style={{ flex: 1, backgroundColor: '#000000' }}>
        
        <View style={{ flex: 1, backgroundColor: colores.background }}>
          <HeaderFormulario
            titulo={titulo}
            evento={() => ejecutarConTecladoCerrado(onClose)}
            icono={iconoHeader}
          />

          <ContenedorTeclado
            style={{ flex: 1 }}
            behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          >
            <ScrollView
              style={{ flex: 1, paddingHorizontal: 16 }}
              contentContainerStyle={{ paddingBottom: 20, paddingTop: 10 }}
              showsVerticalScrollIndicator={false}
              keyboardShouldPersistTaps="handled"
            >
              {children}
            </ScrollView>

            {footer && (
              <View style={[styles.actionButtons, { backgroundColor: colores.backgroundBotones }]}>
                {footer}
              </View>
            )}

          </ContenedorTeclado>
        </View>
      </SafeAreaView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  actionButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 20,
    paddingBottom: 0,
    borderTopWidth: 1,
    borderTopColor: '#e9ecef',
    gap: 12
  }
});