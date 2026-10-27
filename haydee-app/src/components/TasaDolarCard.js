import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { useTema } from '../hooks/useTema';

export default function TasaDolarCard({
  tasa = '1.00',
  equivalente = '0.00',
  moneda = 'Bs.',
  mostrarEquivalente = true,
}) {
  const { colores } = useTema();
  const styles = getStyles(colores);

  return (
    <View style={styles.container}>
      <View>
        <Text style={styles.label}>Tasa Oficial (BCV)</Text>
        <Text style={styles.valor}>{tasa} {moneda}</Text>
      </View>
      {mostrarEquivalente && (
        <View style={styles.right}>
          <Text style={styles.label}>Equivalente aprox.</Text>
          <Text style={[styles.valor, styles.valorVerde]}>$ {equivalente}</Text>
        </View>
      )}
    </View>
  );
}

const getStyles = (colores) => StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: colores.primario + '10',
    padding: 12,
    borderRadius: 10,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: colores.primario + '30',
  },
  label: {
    fontSize: 12,
    color: colores.textPlaceholder,
  },
  valor: {
    fontSize: 15,
    fontWeight: 'bold',
    color: colores.text,
  },
  valorVerde: {
    color: '#27ae60',
  },
  right: {
    alignItems: 'flex-end',
  },
});