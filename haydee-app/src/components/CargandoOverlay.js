import React from 'react';
import { View, Modal, ActivityIndicator, StyleSheet, Text } from 'react-native';
import { useTema } from '../hooks/useTema';

export default function CargandoOverlay({ visible, mensaje = "Cargando..." }) {
  const { colores } = useTema();

  return (
    <Modal
      transparent={true}
      animationType="fade"
      visible={visible}
      onRequestClose={() => {}}
    >
      <View style={styles.overlay}>
        <View style={[styles.caja, { backgroundColor: colores.card }]}>
          <ActivityIndicator size="large" color={colores.primario || '#3498db'} />
          <Text style={[styles.texto, { color: colores.textTitle }]}>
            {mensaje}
          </Text>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.4)', 
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 9999, 
  },
  caja: {
    padding: 20,
    borderRadius: 12,
    alignItems: 'center',
    flexDirection: 'row',
    gap: 15,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  texto: {
    fontSize: 16,
    fontWeight: '600',
  }
});