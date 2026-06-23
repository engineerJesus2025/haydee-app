import React, { useEffect, useRef } from 'react';
import { View, Animated, StyleSheet } from 'react-native';
import { useTema } from '../hooks/useTema';

export default function SkeletonCard({ tipo = 'gasto' }) {
  const { colores } = useTema();
  const opacidadAnimada = useRef(new Animated.Value(0.3)).current;

  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(opacidadAnimada, {
          toValue: 0.8,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(opacidadAnimada, {
          toValue: 0.3,
          duration: 800,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, [opacidadAnimada]);

  const colorBloque = colores.textPlaceholder;

  // Footer reutilizable para todas las tarjetas
  const renderFooter = () => (
    <View style={[styles.footer, { borderTopColor: colores.border }]}>
      <View style={[styles.bone, { backgroundColor: colorBloque, width: 140, height: 14 }]} />
      <View style={[styles.bone, { backgroundColor: colorBloque, width: 18, height: 18, borderRadius: 9 }]} />
    </View>
  );

  // Layout específico para Gasto
  const renderGasto = () => (
    <>
      <View style={styles.header}>
        <View style={styles.infoPrincipal}>
          <View style={[styles.bone, { backgroundColor: colorBloque, width: '70%', height: 16, marginBottom: 8 }]} />
          <View style={[styles.bone, { backgroundColor: colorBloque, width: '40%', height: 12 }]} />
        </View>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 60, height: 18 }]} />
      </View>
      <View style={styles.bodyRow}>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 80, height: 24, borderRadius: 12 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 70, height: 12 }]} />
      </View>
      {renderFooter()}
    </>
  );

  // Layout específico para Pago
  const renderPago = () => (
    <>
      <View style={styles.header}>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 80, height: 22 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 90, height: 24, borderRadius: 12 }]} />
      </View>
      <View style={styles.bodyCol}>
        <View style={styles.detalleFila}>
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 16, height: 16, borderRadius: 8, marginRight: 8 }]} />
          <View style={[styles.bone, { backgroundColor: colorBloque, width: '60%', height: 14 }]} />
        </View>
        <View style={[styles.detalleFila, { marginTop: 10 }]}>
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 16, height: 16, borderRadius: 8, marginRight: 8 }]} />
          <View style={[styles.bone, { backgroundColor: colorBloque, width: '40%', height: 14 }]} />
        </View>
      </View>
      {renderFooter()}
    </>
  );

  // Layout específico para Mensualidad
  const renderMensualidad = () => (
    <>
      <View style={[styles.header, { borderBottomWidth: 1, borderBottomColor: colores.border, paddingBottom: 10, marginBottom: 10 }]}>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 100, height: 18 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 80, height: 18 }]} />
      </View>
      <View style={styles.bodyCol}>
        <View style={styles.filaMonto}>
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 100, height: 14 }]} />
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 60, height: 14 }]} />
        </View>
        <View style={[styles.filaMonto, { marginTop: 10 }]}>
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 120, height: 14 }]} />
          <View style={[styles.bone, { backgroundColor: colorBloque, width: 60, height: 14 }]} />
        </View>
      </View>
      {renderFooter()}
    </>
  );

  const renderPublicacion = () => (
    <>
      <View style={styles.header}>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: '60%', height: 20, marginBottom: 8 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: 80, height: 24, borderRadius: 12 }]} />
      </View>
      <View style={styles.bodyCol}>
        <View style={[styles.bone, { backgroundColor: colorBloque, width: '100%', height: 14, marginBottom: 8 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: '90%', height: 14, marginBottom: 8 }]} />
        <View style={[styles.bone, { backgroundColor: colorBloque, width: '40%', height: 14 }]} />
      </View>
      {renderFooter()}
    </>
  );

  return (
    <View style={[
      styles.card, 
      { backgroundColor: tipo === 'mensualidad' ? colores.card : colores.inputBackground }
    ]}>
      <Animated.View style={{ opacity: opacidadAnimada }}>
        {tipo === 'gasto' && renderGasto()}
        {tipo === 'pago' && renderPago()}
        {tipo === 'mensualidad' && renderMensualidad()}
        {tipo === 'publicacion' && renderPublicacion()}
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: { borderRadius: 12, marginBottom: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1, shadowRadius: 3, overflow: 'hidden' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', padding: 16, paddingBottom: 8 },
  infoPrincipal: { flex: 1, paddingRight: 10 },
  
  // Variantes para el cuerpo de la tarjeta
  bodyRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingBottom: 16 },
  bodyCol: { paddingHorizontal: 16, paddingBottom: 16 },
  
  // Específicos de Pago
  detalleFila: { flexDirection: 'row', alignItems: 'center' },
  
  // Específicos de Mensualidad
  filaMonto: { flexDirection: 'row', justifyContent: 'space-between' },

  footer: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 12, paddingHorizontal: 16, borderTopWidth: 1 },
  bone: { borderRadius: 4 }
});