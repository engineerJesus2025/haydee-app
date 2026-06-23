import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context'; 

import { useRoute, useNavigation } from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context'
import { Image } from 'expo-image';
import Icon from 'react-native-vector-icons/Ionicons';

import { useTema } from '../hooks/useTema';
import { useDetalleCartelera } from '../hooks/useDetalleCartelera';
import VisorImagenFullScreen from '../components/VisorImagenFullScreen';
import VistaError from '../components/VistaError';
import { tiempoRelativo } from '../utils/dateUtils';

const IMAGEN_POR_DEFECTO = require('../../assets/publicacion-default.svg');

export default function DetalleCarteleraScreen() {
  const { colores } = useTema();
  const route = useRoute();
  const navigation = useNavigation();
  const insets = useSafeAreaInsets();
  
  const { id_registro } = route.params || {};
  const { publicacion, cargando, error, reintentar } = useDetalleCartelera(id_registro);
  const [imagenExpandida, setImagenExpandida] = useState(false);

  if (cargando) {
    return (
      <>
      <View style={{ height: insets.top, backgroundColor: '#000' }} />
      <View style={[styles.centerContainer, { backgroundColor: colores.background }]}>
        <ActivityIndicator size="large" color={colores.primario} />
        <Text style={{ color: colores.textPlaceholder, marginTop: 10 }}>Cargando aviso...</Text>
      </View>
      <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
      </>
    );
  }

  if (error || !publicacion) {
    return (
      <>
      <View style={{ height: insets.top, backgroundColor: '#000' }} />
      <View style={[styles.centerContainer, { backgroundColor: colores.background }]}>
        <VistaError mensaje={error || "Aviso no encontrado"} onRetry={reintentar} />
        <TouchableOpacity style={{ marginTop: 20 }} onPress={() => navigation.goBack()}>
          <Text style={{ color: colores.primario, fontWeight: 'bold' }}>Volver</Text>
        </TouchableOpacity>
      </View>
      <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
      </>
    );
  }

  const estiloTipo = getEstiloTipo(publicacion.tipo);
  const sourceImagen = publicacion.imagen ? { uri: publicacion.imagen } : IMAGEN_POR_DEFECTO;

  return (
    <>
    <View style={{ height: insets.top, backgroundColor: '#000' }} />
    <View style={{ flex: 1, backgroundColor: colores.background}}>
      <View style={[styles.header, { borderBottomColor: colores.border, backgroundColor: colores.card }]}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.btnAtras}>
          <Icon name="arrow-back" size={24} color={colores.textTitle} />
        </TouchableOpacity>
        <Text style={[styles.headerTitle, { color: colores.textTitle }]}>Detalle del Aviso</Text>
        <View style={{ width: 24 }} /> 
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ padding: 16, paddingBottom: 40 }}>
        <Text style={[styles.titulo, { color: colores.textTitle }]}>{publicacion.titulo}</Text>

        <View style={styles.metaRow}>
          <View style={[styles.badgeTipo, { backgroundColor: estiloTipo.fondo }]}>
            <Icon name={estiloTipo.icono} size={14} color={estiloTipo.color} style={{ marginRight: 6 }} />
            <Text style={[styles.badgeText, { color: estiloTipo.color }]}>{estiloTipo.label}</Text>
          </View>
          
          <View style={styles.metaItem}>
            <Icon name="calendar-outline" size={14} color={colores.textPlaceholder} style={{ marginRight: 4 }} />
            <Text style={[styles.metaTexto, { color: colores.textPlaceholder }]}>
              {publicacion.fecha ? tiempoRelativo(publicacion.fecha) : 'Sin fecha'}
            </Text>
          </View>
        </View>

        <View style={[styles.autorCard, { backgroundColor: colores.inputBackground, borderColor: colores.border }]}>
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

        <View style={styles.cuerpoContainer}>
          <Text style={[styles.descripcion, { color: colores.text }]}>{publicacion.descripcion}</Text>
        </View>

        <View style={[styles.contenedorImagen, { borderColor: colores.border }]}>
          <TouchableOpacity activeOpacity={0.9} onPress={() => publicacion.imagen && setImagenExpandida(true)} style={{ width: '100%', height: '100%' }}>
            <Image source={sourceImagen} style={styles.imagen} contentMode="cover" transition={300} />
            
            {/* 3. CORRECCIÓN DEL TEXT ERROR: Usar operador ternario */}
            {publicacion.imagen ? (
              <View style={styles.badgeExpandir}>
                <Icon name="expand-outline" size={16} color="#fff" />
              </View>
            ) : null}

          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* 3. CORRECCIÓN DEL TEXT ERROR: Usar operador ternario */}
      {publicacion.imagen ? (
        <VisorImagenFullScreen visible={imagenExpandida} onClose={() => setImagenExpandida(false)} imageUri={publicacion.imagen} />
      ) : null}
    </View>
    <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
    </>
  );
}

const getEstiloTipo = (tipo) => {
  switch (tipo?.toLowerCase()) {
    case 'evento': return { color: '#27ae60', fondo: 'rgba(39, 174, 96, 0.12)', icono: 'calendar-outline', label: 'Evento' };
    case 'aviso': return { color: '#e67e22', fondo: 'rgba(230, 126, 34, 0.12)', icono: 'warning-outline', label: 'Aviso urgente' };
    default: return { color: '#2980b9', fondo: 'rgba(41, 128, 185, 0.12)', icono: 'information-circle-outline', label: 'Noticia' };
  }
};

const styles = StyleSheet.create({
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingVertical: 14, borderBottomWidth: 1 },
  btnAtras: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: 'bold' },
  titulo: { fontSize: 24, fontWeight: '800', lineHeight: 30, marginBottom: 14 },
  metaRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  badgeTipo: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 20 },
  badgeText: { fontSize: 11, fontWeight: '700', textTransform: 'uppercase' },
  metaItem: { flexDirection: 'row', alignItems: 'center' },
  metaTexto: { fontSize: 13, fontWeight: '500' },
  autorCard: { flexDirection: 'row', alignItems: 'center', padding: 10, borderRadius: 10, borderWidth: 1, marginBottom: 18, gap: 10 },
  avatarCirculo: { width: 32, height: 32, borderRadius: 16, alignItems: 'center', justifyContent: 'center' },
  cuerpoContainer: { marginBottom: 20 },
  descripcion: { fontSize: 16, lineHeight: 24, fontWeight: '400', textAlign: 'justify' },
  contenedorImagen: { width: '100%', height: 220, borderRadius: 12, overflow: 'hidden', borderWidth: 1, backgroundColor: '#eaeaea' },
  imagen: { width: '100%', height: '100%' },
  badgeExpandir: { position: 'absolute', bottom: 10, right: 10, backgroundColor: 'rgba(0,0,0,0.6)', padding: 6, borderRadius: 8 }
});