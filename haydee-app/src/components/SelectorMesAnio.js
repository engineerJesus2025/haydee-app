import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
// Importamos la utilidad centralizada de fechas
import { obtenerNombreMes } from '../utils/dateUtils';

// periodosDisponibles debe ser un array tipo: [{mes: 5, anio: 2026}, {mes: 2, anio: 2026}]
export default function SelectorMesAnio({ periodosDisponibles = [], mesActual, anioActual, onCambiarMes }) {
  const { colores } = useTema();

  // Si no hay datos en la BD, mostramos un estado bloqueado
  if (periodosDisponibles.length === 0) {
    return (
      <View style={[styles.container, { backgroundColor: colores.card }]}>
        <Text style={[styles.texto, { color: colores.textPlaceholder }]}>Sin registros históricos</Text>
      </View>
    );
  }

  // Buscamos en qué posición del arreglo estamos parados
  const indexActual = periodosDisponibles.findIndex(
    p => Number(p.mes) === Number(mesActual) && Number(p.anio) === Number(anioActual)
  );

  // - Retroceder en el tiempo = Ir hacia adelante en los índices del arreglo (los más antiguos están al final)
  const esElMasAntiguo = indexActual === periodosDisponibles.length - 1;
  const retrocederMes = () => {
    if (!esElMasAntiguo && indexActual !== -1) {
      const periodoAnterior = periodosDisponibles[indexActual + 1];
      onCambiarMes(Number(periodoAnterior.mes), Number(periodoAnterior.anio));
    }
  };

  // - Avanzar en el tiempo = Ir hacia atrás en los índices del arreglo (el índice 0 es el más reciente)
  const esElMasReciente = indexActual === 0;
  const avanzarMes = () => {
    if (!esElMasReciente && indexActual !== -1) {
      const periodoSiguiente = periodosDisponibles[indexActual - 1];
      onCambiarMes(Number(periodoSiguiente.mes), Number(periodoSiguiente.anio));
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colores.card }]}>
      <TouchableOpacity 
        onPress={retrocederMes} 
        style={styles.boton}
        disabled={esElMasAntiguo || indexActual === -1}
      >
        <Icon name="chevron-back" size={24} color={(esElMasAntiguo || indexActual === -1) ? colores.border : "#007BFF"} />
      </TouchableOpacity>
      
      <Text style={[styles.texto, { color: colores.textTitle }]}>
        {obtenerNombreMes(mesActual)} {anioActual}
      </Text>

      <TouchableOpacity 
        onPress={avanzarMes} 
        style={styles.boton}
        disabled={esElMasReciente || indexActual === -1}
      >
        <Icon name="chevron-forward" size={24} color={(esElMasReciente || indexActual === -1) ? colores.border : "#007BFF"} />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 12,
    marginHorizontal: 16,
    marginVertical: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 2,
  },
  boton: {
    padding: 6,
    borderRadius: 8,
  },
  texto: {
    fontSize: 16,
    fontWeight: '700',
    textTransform: 'capitalize',
  },
});