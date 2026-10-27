import React, { useState, useMemo } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Alert } from 'react-native';
import { Image } from 'expo-image';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';

import ModalGeneral from './ModalGeneral';
import CustomBoton from './CustomBoton';
import VisorImagenFullScreen from './VisorImagenFullScreen';
import MenuAcciones from './MenuAcciones';
import { tiempoRelativo } from '../utils/dateUtils';

const IMAGEN_POR_DEFECTO = require('../../assets/publicacion-default.svg');

export default function ModalDetallePublicacion({
  visible, onClose, publicacion,
  onEditar = null, onEliminar = null, eliminando = false,
}) {
  const { colores } = useTema();
  const [imagenExpandida, setImagenExpandida] = useState(false);

  const accionesMenu = useMemo(() => {
    if (!publicacion?.id) return [];
    const items = [];

    if (onEditar) {
      items.push({
        label: 'Editar publicación',
        icono: 'create-outline',
        color: '#f39c12',
        onPress: () => onEditar(publicacion),
        disabled: eliminando,
      });
    }

    if (onEliminar) {
      items.push({
        label: 'Eliminar publicación',
        icono: 'trash-outline',
        color: '#c0392b',
        onPress: () => onEliminar(publicacion),
      });
    }

    return items;
  }, [publicacion?.id, publicacion?.titulo, onEditar, onEliminar, eliminando]);

  if (!publicacion) return null;

  const estiloTipo = getEstiloTipo(publicacion.tipo);

  const sourceImagen = publicacion.imagen ? { uri: publicacion.imagen } : IMAGEN_POR_DEFECTO;

  const headerAcciones = accionesMenu.length > 0 ? (
    <MenuAcciones
      acciones={accionesMenu}
      titulo="Acciones de la publicación"
    />
  ) : null;

  const BotonFooter = (
    <CustomBoton
      titulo="Cerrar"
      evento={onClose}
      icono={{ nombre: 'close-circle-outline', color: '#fff' }}
      estilos={{ backgroundColor: '#7f8c8d', alignSelf: 'stretch' }}
      wrapperStyle={{ width: '100%' }}
      fuente={15}
    />
  );

  return (
    <>
      <ModalGeneral
        visible={visible}
        onClose={onClose}
        titulo="Detalle de Publicación"
        iconoHeader={{ name: 'document-text-outline', color: '#E1E1F7' }}
        headerAcciones={headerAcciones}
        footer={BotonFooter}
      >

        {eliminando && (
          <View style={[styles.bannerEliminando, { backgroundColor: '#e74c3c20', borderColor: '#e74c3c60' }]}>
            <Icon name="hourglass-outline" size={16} color="#c0392b" />
            <Text style={[styles.bannerEliminandoTexto, { color: '#c0392b' }]}>
              Eliminando publicación…
            </Text>
          </View>
        )}

        <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 20 }}>

          {/* TÍTULO */}
          <Text style={[styles.titulo, { color: colores.textTitle }]}>
            {publicacion.titulo}
          </Text>

          {/* Tipo y Fecha */}
          <View style={styles.metaRow}>
            <View style={[styles.badgeTipo, { backgroundColor: estiloTipo.fondo }]}>
              <Icon name={estiloTipo.icono} size={14} color={estiloTipo.color} style={{ marginRight: 6 }} />
              <Text style={[styles.badgeText, { color: estiloTipo.color }]}>
                {estiloTipo.label}
              </Text>
            </View>

            <View style={styles.metaItem}>
              <Icon name="calendar-outline" size={14} color={colores.textPlaceholder} style={{ marginRight: 4 }} />
              <Text style={[styles.metaTexto, { color: colores.textPlaceholder }]}>
                {publicacion.fecha ? tiempoRelativo(publicacion.fecha) : 'Sin fecha'}
              </Text>
            </View>
          </View>

          {/* AUTOR */}
          <View style={[styles.autorCard, { backgroundColor: colores.inputBackground || '#f8f9fa', borderColor: colores.border }]}>
            <View style={[styles.avatarCirculo, { backgroundColor: colores.primario + '20' }]}>
              <Icon name="person" size={16} color={colores.primario} />
            </View>
            <View>
              <Text style={{ fontSize: 11, color: colores.textPlaceholder }}>Publicado por:</Text>
              <Text style={{ fontSize: 14, fontWeight: '600', color: colores.text }}>
                {publicacion.autor || 'Administración'}
              </Text>
            </View>
          </View>

          {/* CUERPO DE LA DESCRIPCIÓN */}
          <View style={styles.cuerpoContainer}>
            <Text style={[styles.descripcion, { color: colores.text, strokeColor: colores.text }]}>
              {publicacion.descripcion}
            </Text>
          </View>

          {/* SECCIÓN DE IMAGEN */}
          <View style={[styles.contenedorImagen, { borderColor: colores.border }]}>
            <TouchableOpacity
              activeOpacity={0.9}
              onPress={() => publicacion.imagen && setImagenExpandida(true)}
              style={{ width: '100%', height: '100%' }}
            >
              <Image
                source={sourceImagen}
                style={styles.imagen}
                contentMode="cover"
                transition={300}
              />
              {publicacion.imagen && (
                <View style={styles.badgeExpandir}>
                  <Icon name="expand-outline" size={16} color="#fff" />
                </View>
              )}
            </TouchableOpacity>
          </View>

        </ScrollView>
      </ModalGeneral>

      {/* VISOR DE PANTALLA COMPLETA */}
      {publicacion.imagen && (
        <VisorImagenFullScreen
          visible={imagenExpandida}
          onClose={() => setImagenExpandida(false)}
          imageUri={publicacion.imagen}
        />
      )}
    </>
  );
}

const getEstiloTipo = (tipo) => {
  switch (tipo?.toLowerCase()) {
    case 'evento':
      return { color: '#27ae60', fondo: 'rgba(39, 174, 96, 0.12)', icono: 'calendar-outline', label: 'Evento' };
    case 'aviso':
      return { color: '#e67e22', fondo: 'rgba(230, 126, 34, 0.12)', icono: 'warning-outline', label: 'Aviso urgente' };
    default:
      return { color: '#2980b9', fondo: 'rgba(41, 128, 185, 0.12)', icono: 'information-circle-outline', label: 'Noticia' };
  }
};

const styles = StyleSheet.create({
  titulo: {
    fontSize: 22,
    fontWeight: '800',
    lineHeight: 28,
    marginTop: 8,
    marginBottom: 14,
    letterSpacing: -0.3
  },
  metaRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16
  },
  badgeTipo: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.3
  },
  metaItem: {
    flexDirection: 'row',
    alignItems: 'center'
  },
  metaTexto: {
    fontSize: 13,
    fontWeight: '500'
  },
  autorCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 10,
    borderRadius: 10,
    borderWidth: 1,
    marginBottom: 18,
    gap: 10
  },
  avatarCirculo: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center'
  },
  cuerpoContainer: {
    marginBottom: 20,
    paddingHorizontal: 2
  },
  descripcion: {
    fontSize: 15,
    lineHeight: 23,
    fontWeight: '400',
    textAlign: 'justify'
  },
  contenedorImagen: {
    width: '100%',
    height: 190,
    borderRadius: 12,
    overflow: 'hidden',
    borderWidth: 1,
    backgroundColor: '#eaeaea',
    position: 'relative',
    marginTop: 10
  },
  imagen: {
    width: '100%',
    height: '100%',
  },
  badgeExpandir: {
    position: 'absolute',
    bottom: 10,
    right: 10,
    backgroundColor: 'rgba(0,0,0,0.6)',
    padding: 6,
    borderRadius: 8
  },
  bannerEliminando: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderRadius: 10,
    borderWidth: 1,
    paddingVertical: 10,
    paddingHorizontal: 12,
    marginBottom: 12,
  },
  bannerEliminandoTexto: { flex: 1, fontSize: 13, fontWeight: '600' },
});