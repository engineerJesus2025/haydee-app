import React, { memo } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native'; 
import { Image } from 'expo-image';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import { formatoAyerHoy } from '../utils/dateUtils';

const IMAGEN_POR_DEFECTO = require('../../assets/publicacion-default.svg');

function PublicacionCard({ post, onPress }) {
  const { colores } = useTema();

  const estiloTipo = getEstiloTipo(post.tipo);
  const sourceImagen = post.imagen ? { uri: post.imagen } : IMAGEN_POR_DEFECTO;

  return (
    <TouchableOpacity 
      onPress={() => onPress(post)}
      activeOpacity={0.85}
      style={[styles.card, { backgroundColor: colores.card }]}
      // Estándares de Accesibilidad
      accessible={true}
      accessibilityRole="button"
      accessibilityLabel={`Ver detalles de la publicación: ${post.titulo}`}
    >
      {/* Imagen en la parte superior, abarcando el ancho total */}
      <Image
        source={sourceImagen}
        style={styles.imagen}
        contentFit="cover"
        transition={300}
        cachePolicy="disk"
      />

      {/* Contenedor del contenido con el padding de la tarjeta */}
      <View style={styles.contenido}>
        <View style={styles.headerContainer}>
          <View style={[styles.badge, { backgroundColor: estiloTipo.fondo }]}>
            <Icon name={estiloTipo.icono} size={14} color={estiloTipo.color} style={{ marginRight: 4 }} />
            <Text style={[styles.badgeText, { color: estiloTipo.color }]}>
              {estiloTipo.label}
            </Text>
          </View>
          <Text style={[styles.fecha, { color: colores.textPlaceholder }]}>
            {post.fecha ? formatoAyerHoy(post.fecha) : 'Sin fecha'}
          </Text>
        </View>

        <Text style={[styles.titulo, { color: colores.textTitle }]}>
          {post.titulo}
        </Text>
        <Text style={[styles.descripcion, { color: colores.text }]} numberOfLines={3}>
          {post.descripcion}
        </Text>
      </View>
    </TouchableOpacity>
  );
}

export default memo(PublicacionCard);

const getEstiloTipo = (tipoRaw) => {
    const tipo = tipoRaw?.toLowerCase() || 'noticia';
    
    switch (tipo) {
      case 'evento':
        return { color: '#f39c12', fondo: 'rgba(243, 156, 18, 0.12)', icono: 'calendar-outline', label: 'Evento' };
      case 'aviso':
        return { color: '#e74c3c', fondo: 'rgba(231, 76, 60, 0.12)', icono: 'warning-outline', label: 'Aviso' };
      default: 
        return { color: '#3498db', fondo: 'rgba(52, 152, 219, 0.12)', icono: 'information-circle-outline', label: 'Noticia' };
    }
};

const styles = StyleSheet.create({
  card: {
    borderRadius: 14,
    marginBottom: 16, // El padding se movió al contenedor 'contenido'
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.1,
    shadowRadius: 5,
    overflow: 'hidden', // Asegura que la imagen respete los bordes redondeados superiores
  },
  imagen: { 
    width: '100%', 
    height: 180 
  },
  contenido: {
    padding: 16,
  },
  headerContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12, 
  },
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
  },
  badgeText: {
    fontSize: 11,
    fontWeight: 'bold',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  fecha: {
    fontSize: 12,
    fontWeight: '500',
  },
  titulo: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 6,
  },
  descripcion: {
    fontSize: 14,
    lineHeight: 22,
    // Eliminado el marginBottom innecesario para el último elemento
  },
});