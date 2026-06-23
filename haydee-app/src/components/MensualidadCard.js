import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons'; 
import { useTema } from '../hooks/useTema'; 

export default function MensualidadCard({ mensualidad, onPressDetalles }) {
  const { colores } = useTema();

  const tieneDeuda = mensualidad.restante && mensualidad.restante !== '0' && mensualidad.restante !== '0.00';

  return (
    <View style={[styles.card, { backgroundColor: colores.card }]}>
      
      <View style={[styles.header, { borderBottomColor: colores.border }]}>
        <Text style={[styles.mesTexto, { color: colores.textTitle }]}>
          {mensualidad.fecha || mensualidad.mes}
        </Text>
        <Text style={[styles.valor, { color: colores.text }]}>
          {mensualidad.monto || mensualidad.total}
        </Text>
      </View>

      <View style={styles.body}>
        <View style={styles.filaMonto}>
          <Text style={[styles.etiqueta, { color: colores.textPlaceholder }]}>Total del mes:</Text>
          <Text style={[styles.valor, { color: colores.text }]}>{mensualidad.total}</Text>
        </View>
        <View style={styles.filaMonto}>
          <Text style={[styles.etiqueta, { color: colores.textPlaceholder }]}>Restante por pagar:</Text>
          <Text style={[styles.valor, { color: colores.text }, tieneDeuda && styles.textoAlerta]}>
            {mensualidad.restante}
          </Text>
        </View>
      </View>

      <TouchableOpacity 
        style={[styles.botonDetalle, { borderTopColor: colores.border }]} 
        onPress={() => onPressDetalles(mensualidad)}
      >
        <Text style={styles.botonTextoAzul}>Ver Presupuesto</Text>
        <Icon name="chevron-forward" size={18} color="#007BFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    elevation: 3, 
    shadowColor: '#000', 
    shadowOpacity: 0.1,
    shadowRadius: 5,
    shadowOffset: { width: 0, height: 2 },
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    paddingBottom: 10,
    marginBottom: 10,
  },
  mesTexto: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  body: {
    marginBottom: 15,
  },
  filaMonto: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 5,
  },
  etiqueta: {
    fontSize: 14,
  },
  valor: {
    fontSize: 15,
    fontWeight: '600',
  },
  textoAlerta: {
    color: '#e74c3c', 
  },
  botonDetalle: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
    borderTopWidth: 1,
  },
  botonTextoAzul: {
    color: '#007BFF',
    fontWeight: '600',
    fontSize: 15,
  }
});