import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import CustomBoton from './CustomBoton';

export default function VistaError({ 
  mensaje = 'Ocurrió un error inesperado al conectar con el servidor.', 
  onRetry 
}) {
  const { colores } = useTema();
console.log(mensaje)
  return (
    <View style={[styles.container, { backgroundColor: colores.background }]}>
      <Icon 
        name="cloud-offline-outline" 
        size={80} 
        color="#e74c3c" 
        style={styles.icono} 
      />
      
      <Text style={[styles.titulo, { color: colores.text }]}>
        Algo salió mal
      </Text>
      
      <Text style={styles.mensaje}>
        {mensaje}
      </Text>

      {onRetry && (
        <View style={styles.botonContainer}>
          <CustomBoton
            titulo="Intentar de nuevo"
            evento={onRetry}
            icono={{ nombre: 'refresh', color: '#fff' }}
            estilos={{ backgroundColor: '#3498db' }}
            fuente={16}
          />
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'start',
    alignItems: 'center',
    padding: 30,
    marginTop: 90,
  },
  icono: {
    marginBottom: 20,
    opacity: 0.9,
  },
  titulo: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 10,
    textAlign: 'center',
  },
  mensaje: {
    fontSize: 16,
    color: '#7f8c8d',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 30,
  },
  botonContainer: {
    width: '100%',
    maxWidth: 250,
    alignItems: 'center',
  }
});