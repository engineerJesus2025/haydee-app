import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import { formatearFechaLegible } from '../utils/dateUtils';

export default function GastoCard({ gasto, onPressDetalles }) {
  const { colores } = useTema();
  
  const esFijo = gasto.tipo.toLowerCase() === 'fijo';
  const colorTipo = esFijo ? '#3498db' : '#e67e22'; 
  const iconoTipo = esFijo ? 'business-outline' : 'flash-outline';

  return (
    <View style={[styles.card, { backgroundColor: colores.inputBackground }]}>
      
      <View style={styles.header}>
        <View style={styles.infoPrincipal}>
          <Text style={[styles.categoria, { color: colores.textTitle }]} numberOfLines={1}>
            {gasto.tipo_gasto}
          </Text>
          <Text style={styles.proveedor}>{gasto.proveedor}</Text>
        </View>
        <Text style={[styles.monto, { color: colores.text }]}>{gasto.monto}</Text>
      </View>

      <View style={styles.body}>
        <View style={[styles.etiquetaTipo, { backgroundColor: colorTipo + '20' }]}>
          <Icon name={iconoTipo} size={14} color={colorTipo} style={{ marginRight: 4 }} />
          <Text style={[styles.textoTipo, { color: colorTipo }]}>Gasto {gasto.tipo.charAt(0).toUpperCase() + gasto.tipo.toLowerCase().slice(1)}</Text>
        </View>

        <Text style={styles.fecha}>{formatearFechaLegible(gasto.fecha)}</Text>
      </View>
      <TouchableOpacity 
        style={[styles.botonVerMas, { borderTopColor: colores.border }]} 
        onPress={() => onPressDetalles(gasto)}
      >
        <Text style={styles.botonTexto}>Ver factura / Detalles</Text>
        <Icon name="chevron-forward" size={18} color="#007BFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  card: { borderRadius: 12, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1, shadowRadius: 3, overflow: 'hidden' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', padding: 16, paddingBottom: 8 },
  infoPrincipal: { flex: 1, paddingRight: 10 },
  categoria: { fontSize: 16, fontWeight: 'bold', marginBottom: 4 },
  proveedor: { fontSize: 13, color: '#7f8c8d' },
  monto: { fontSize: 18, fontWeight: 'bold' },
  body: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingBottom: 16 },
  etiquetaTipo: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
  textoTipo: { fontSize: 12, fontWeight: '600' },
  fecha: { fontSize: 13, color: '#95a5a6' },
  botonVerMas: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 12, paddingHorizontal: 16, borderTopWidth: 1 },
  botonTexto: { color: '#007BFF', fontWeight: '600', fontSize: 14 }
});