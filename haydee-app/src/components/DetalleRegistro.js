import { View, Text, StyleSheet } from 'react-native'

import { useTema } from './../hooks/useTema'

export default function DetalleRegistro ({ detalle = {}, index }) {
  const { colores } = useTema()
  const estilosDetalleRegistro = getEstilosDetalleRegistro(colores)

  return (
    <View key={index}>
      <Text style={estilosDetalleRegistro.detailsLabel}>{detalle.label}:</Text>
      <Text style={estilosDetalleRegistro.detailsValue}>
        {detalle.dato || 'No disponible'}
      </Text>
    </View>
  )
}

const getEstilosDetalleRegistro = (colores) => StyleSheet.create({
  detailsLabel: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#7f8c8d',
    marginTop: 12,
    marginBottom: 4
  },
  detailsValue: {
    fontSize: 16,
    color: colores.text,
    lineHeight: 22
  }
})
