import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Dimensions } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import { obtenerDiaMes } from '../utils/dateUtils';

const { width } = Dimensions.get('window');

const EventoCard = ({ evento, onPress }) => {
  const { colores } = useTema();
  const estilos = getEstilos(colores);

  const { dia, mes } = evento.fecha ? obtenerDiaMes(evento.fecha) : { dia: '--', mes: '---' };

  return (
    <TouchableOpacity style={estilos.card} onPress={() => onPress(evento)} activeOpacity={0.8}>
      <View style={estilos.fechaContainer}>
        <Text style={estilos.fechaDia}>{dia}</Text>
        <Text style={estilos.fechaMes}>{mes}</Text>
      </View>
      
      <View style={estilos.infoContainer}>
        <Text style={estilos.titulo} numberOfLines={1}>{evento.titulo}</Text>
        <Text style={estilos.descripcion} numberOfLines={2}>{evento.descripcion}</Text>
      </View>
    </TouchableOpacity>
  );
};

const getEstilos = (colores) => StyleSheet.create({
  card: {
    width: width * 0.75, 
    flexDirection: 'row',
    backgroundColor: colores.card,
    borderRadius: 12,
    padding: 12,
    marginRight: 12, 
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  fechaContainer: {
    backgroundColor: colores.primario || '#007BFF',
    borderRadius: 10,
    paddingVertical: 8,
    width: 55,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  fechaDia: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  fechaMes: {
    color: '#fff',
    fontSize: 11,
    textTransform: 'uppercase',
    fontWeight: '600'
  },
  infoContainer: {
    flex: 1, 
    justifyContent: 'center',
  },
  titulo: {
    fontSize: 16,
    fontWeight: '700',
    color: colores.textTitle,
    marginBottom: 4,
  },
  descripcion: {
    fontSize: 13,
    color: colores.textPlaceholder,
    lineHeight: 18,
  },
});

export default EventoCard;