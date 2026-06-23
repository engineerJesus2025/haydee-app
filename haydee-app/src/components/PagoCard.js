import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import { formatearFechaLegible, formatearMesAnio } from '../utils/dateUtils';

export default function PagoCard({ pago, onPressDetalles }) {
  const { colores } = useTema();
  
  const estado = pago.estado?.toLowerCase();
  const [mes,anio] = pago.mensualidad.split('/');

  let colorEstado = '#f39c12'; // (Pendiente)
  let iconoEstado = 'time';

  if (estado === 'procesado') {
    colorEstado = '#27ae60'; // Verde
    iconoEstado = 'checkmark-circle';
  } else if (estado === 'rechazado') {
    colorEstado = '#e74c3c'; // Rojo
    iconoEstado = 'close-circle'; 
  }

  return (
    <View style={[styles.card, { backgroundColor: colores.inputBackground }]}>
      
      <View style={styles.header}>
        <Text style={[styles.monto, { color: colores.text }]}>{pago.monto}</Text>
        
        <View style={[styles.estadoContainer, { backgroundColor: colorEstado + '20' }]}>
          <Icon name={iconoEstado} size={16} color={colorEstado} style={{ marginRight: 4 }} />
          <Text style={[styles.estadoTexto, { color: colorEstado }]}>{pago.estado}</Text>
        </View>
      </View>

      <View style={styles.body}>
        <View style={styles.detalleFila}>
          <Icon name="calendar-outline" size={16} color="#7f8c8d" />
          <Text style={[styles.detalleTexto, { color: colores.textTitle }]}>
            Mensualidad pagada: {formatearMesAnio(mes,anio)}
          </Text>
        </View>
        <View style={styles.detalleFila}>
          <Icon name="cash-outline" size={16} color="#7f8c8d" />
          <Text style={[styles.detalleTexto, { color: colores.textPlaceholder }]}>
            Fecha de pago: {formatearFechaLegible(pago.fecha)}
          </Text>
        </View>
      </View>

      <TouchableOpacity 
        style={[styles.botonVerMas, { borderTopColor: colores.border }]} 
        onPress={() => onPressDetalles(pago)}
      >
        <Text style={styles.botonTexto}>Ver detalles del recibo</Text>
        <Icon name="chevron-forward" size={18} color="#007BFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: 12,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    overflow: 'hidden',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    paddingBottom: 10,
  },
  monto: {
    fontSize: 22,
    fontWeight: 'bold',
  },
  estadoContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20,
  },
  estadoTexto: {
    fontWeight: '600',
    fontSize: 13,
  },
  body: {
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  detalleFila: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
  },
  detalleTexto: {
    marginLeft: 8,
    fontSize: 14,
  },
  botonVerMas: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 12,
    paddingHorizontal: 16,
    borderTopWidth: 1,
  },
  botonTexto: {
    color: '#007BFF',
    fontWeight: '600',
    fontSize: 14,
  }
});