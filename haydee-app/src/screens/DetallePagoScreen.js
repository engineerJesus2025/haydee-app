import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Image } from 'react-native';
import { useRoute, useNavigation } from '@react-navigation/native';
import Icon from 'react-native-vector-icons/Ionicons';
import { SafeAreaView } from 'react-native-safe-area-context'; 

import { useTema } from '../hooks/useTema';
import { useDetallePago } from '../hooks/useDetallePago';
import VisorImagenFullScreen from '../components/VisorImagenFullScreen';
import VistaError from '../components/VistaError';
import { formatearFechaLegible } from '../utils/dateUtils';
import { useSafeAreaInsets } from 'react-native-safe-area-context'

export default function DetallePagoScreen() {
  const { colores } = useTema();
  const route = useRoute();
  const navigation = useNavigation();
  const insets = useSafeAreaInsets();

  const { id_registro } = route.params || {};

  const { pago: datos, cargando, error, reintentar } = useDetallePago(id_registro);
  const [imagenExpandida, setImagenExpandida] = useState(false);
  const [imagenActiva, setImagenActiva] = useState(null);

  if (cargando) {
    return (
      <>
      <View style={{ height: insets.top, backgroundColor: '#000' }} />
      <View style={[styles.centerContainer, { backgroundColor: colores.background }]}>
        <ActivityIndicator size="large" color={colores.primario} />
      </View>
      <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
      </>
    );
  }

  if (error || !datos) {
    return (
      <>
      <View style={{ height: insets.top, backgroundColor: '#000' }} />
      <View style={[styles.centerContainer, { backgroundColor: colores.background }]}>
        <VistaError mensaje={error || "Pago no encontrado"} onRetry={reintentar} />
        <TouchableOpacity style={{ marginTop: 20 }} onPress={() => navigation.goBack()}>
          <Text style={{ color: colores.primario, fontWeight: 'bold' }}>Volver</Text>
        </TouchableOpacity>
      </View>
      <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
      </>
    );
  }

  const campos = [
    { key: 'estado', label: 'Estado' },
    { key: 'fecha', label: 'Fecha de registro', formato: 'fecha_legible' },
    { key: 'monto', label: 'Monto Total' },
    { key: 'mensualidad', label: 'Mensualidad' },
    { key: 'apartamento', label: 'Apartamento' },
    { key: 'observacion', label: 'Observación' }
  ];

  const abrirVisor = (uri) => { setImagenActiva(uri); setImagenExpandida(true); };

  return (
    <>
    <View style={{ height: insets.top, backgroundColor: '#000' }} />
    <View style={{ flex: 1, backgroundColor: colores.background}}>
      <View style={[styles.header, { borderBottomColor: colores.border, backgroundColor: colores.card }]}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.btnAtras}>
          <Icon name="arrow-back" size={24} color={colores.textTitle} />
        </TouchableOpacity>
        <Text style={[styles.headerTitle, { color: colores.textTitle }]}>Detalle del Recibo</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ padding: 16 }}>
        
        {/* TARJETA DE DATOS PRINCIPALES */}
        <View style={[styles.reciboContainer, { backgroundColor: colores.card, borderColor: colores.border }]}>
          {campos.map((campo, index) => {
            const valor = datos[campo.key];
            const isEstado = campo.key === 'estado';
            const isMonto = campo.key === 'monto';
            let valorMostrar = valor;
            
            if (valor && campo.formato === 'fecha_legible') valorMostrar = formatearFechaLegible(valor);
            
            return (
              <View key={index} style={[styles.fila, index !== campos.length - 1 && { borderBottomColor: colores.border, borderBottomWidth: 1 }]}>
                <Text style={[styles.label, { color: colores.textPlaceholder }]}>{campo.label}</Text>
                {isEstado ? (
                  <View style={[ styles.badge, { backgroundColor: valorMostrar?.toLowerCase() === 'procesado' ? '#27ae6020' : valorMostrar?.toLowerCase() === 'rechazado' ? '#e74c3c20' : '#f39c1220'} ]}>
                    <Text style={[ styles.badgeText, { color: valorMostrar?.toLowerCase() === 'procesado' ? '#27ae60' : valorMostrar?.toLowerCase() === 'rechazado' ? '#e74c3c' : '#f39c12' } ]}>
                      {valorMostrar || 'Desconocido'}
                    </Text>
                  </View>
                ) : isMonto ? (
                  <Text style={[styles.valor, styles.valorMonto, { color: colores.textTitle }]}>{valorMostrar || '---'}</Text>
                ) : (
                  <Text style={[styles.valor, { color: colores.textTitle }]} numberOfLines={2}>{valorMostrar || 'No especificado'}</Text>
                )}
              </View>
            );
          })}
        </View>

        {/* DESGLOSE DE TRANSACCIONES */}
        {datos?.detalles && datos.detalles.length > 0 && (
          <View style={styles.seccionDesglose}>
            <View style={styles.imagenHeader}>
              <Icon name="list-outline" size={18} color={colores.textPlaceholder} />
              <Text style={[styles.imagenTitulo, { color: colores.textPlaceholder }]}>
                Desglose de Operaciones ({datos.detalles.length})
              </Text>
            </View>

            {datos.detalles.map((det, index) => (
              <View key={index} style={[styles.tarjetaDetalle, { backgroundColor: colores.inputBackground, borderColor: colores.border }]}>
                <View style={styles.detalleFilaInterna}>
                  <Text style={[styles.textoMetodo, { color: colores.textTitle }]}>{det.tipo_pago || det.metodo_pago}</Text>
                  <Text style={[styles.textoMontoDetalle, { color: colores.textTitle }]}>{det.monto}</Text>
                </View>
                <Text style={[styles.textoInfoDetalle, { color: colores.textPlaceholder }]}>
                  Banco: {det.banco} | Ref: {det.referencia}
                </Text>
                {det.imagen && (
                  <TouchableOpacity activeOpacity={0.8} onPress={() => abrirVisor(det.imagen)} style={styles.miniaturaContainer}>
                    <Image source={{ uri: det.imagen }} style={styles.miniaturaImagen} resizeMode="cover" />
                    <View style={styles.overlayMiniatura}>
                      <Icon name="expand-outline" size={16} color="#fff" />
                    </View>
                  </TouchableOpacity>
                )}
              </View>
            ))}
          </View>
        )}
      </ScrollView>

      <VisorImagenFullScreen visible={imagenExpandida} onClose={() => setImagenExpandida(false)} imageUri={imagenActiva} />
    </View>
    <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
    </>
  );
}

const styles = StyleSheet.create({
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingVertical: 14, borderBottomWidth: 1 },
  btnAtras: { padding: 4 },
  headerTitle: { fontSize: 18, fontWeight: 'bold' },
  reciboContainer: { borderRadius: 14, borderWidth: 1, overflow: 'hidden', marginBottom: 20 },
  fila: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 14, paddingHorizontal: 16 },
  label: { fontSize: 14, flex: 1 },
  valor: { fontSize: 15, fontWeight: '500', flex: 1.5, textAlign: 'right' },
  valorMonto: { fontSize: 18, fontWeight: 'bold' },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  badgeText: { fontSize: 12, fontWeight: 'bold', textTransform: 'uppercase' },
  seccionDesglose: { marginTop: 5, paddingBottom: 30 },
  imagenHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 10, alignSelf: 'flex-start', marginLeft: 5 },
  imagenTitulo: { fontSize: 14, fontWeight: '600', marginLeft: 6, textTransform: 'uppercase' },
  tarjetaDetalle: { borderWidth: 1, borderRadius: 12, padding: 14, marginBottom: 12 },
  detalleFilaInterna: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
  textoMetodo: { fontSize: 15, fontWeight: 'bold' },
  textoMontoDetalle: { fontSize: 16, fontWeight: 'bold' },
  textoInfoDetalle: { fontSize: 13 },
  miniaturaContainer: { height: 120, width: '100%', borderRadius: 8, overflow: 'hidden', marginTop: 12, backgroundColor: '#1e1e1e' },
  miniaturaImagen: { width: '100%', height: '100%' },
  overlayMiniatura: { position: 'absolute', bottom: 8, right: 8, backgroundColor: 'rgba(0,0,0,0.6)', padding: 6, borderRadius: 20 },
});