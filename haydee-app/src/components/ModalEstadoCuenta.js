import React, { useMemo } from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { useTema } from '../hooks/useTema';
import Icon from 'react-native-vector-icons/Ionicons';

import ModalGeneral from './ModalGeneral';
import CustomBoton from './CustomBoton';
import { formatearMesAnio } from '../utils/dateUtils';
  
export default function ModalEstadoCuenta({ visible, onClose, totalDeuda, listaDeudas = [], moneda = "$" }) {
  const { colores } = useTema();

  // Agrupamos las deudas por número de apartamento automáticamente
  const deudasAgrupadas = useMemo(() => {
    return listaDeudas.reduce((acc, item) => {
      const apto = item.nro_apartamento || 'Sin asignar';
      if (!acc[apto]) {
        acc[apto] = { total: 0, items: [] };
      }
      acc[apto].items.push(item);
      acc[apto].total += parseFloat(item.pendiente);
      return acc;
    }, {});
  }, [listaDeudas]);

  const BotonesFooter = (
    <CustomBoton 
      titulo="Cerrar Detalles" 
      evento={onClose} 
      icono={{ nombre: 'close-circle-outline', color: '#fff' }}
      estilos={{ backgroundColor: '#95a5a6' }} 
      fuente={16}
    />
  );

  return (
    <ModalGeneral
      visible={visible}
      onClose={onClose}
      titulo="Estado de Cuenta"
      iconoHeader={{ name: 'receipt-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={false}
    >
      
      {/* --- CAJA DE RESUMEN GLOBAL --- */}
      <View style={[styles.resumenCaja, { backgroundColor: colores.card }]}>
        <Text style={styles.resumenTexto}>Deuda Global Pendiente</Text>
        <Text style={[styles.resumenTotal, { color: colores.error || '#e74c3c' }]}>
          {moneda} {totalDeuda.toFixed(2)}
        </Text>
      </View>

      <Text style={[styles.tituloDesglose, { color: colores.textTitle }]}>
        Desglose por Apartamento
      </Text>

      {/* --- LISTA AGRUPADA POR APARTAMENTO --- */}
      {Object.entries(deudasAgrupadas).map(([apto, data]) => (
        <View key={apto} style={[styles.tarjetaApto, { backgroundColor: colores.card, borderColor: colores.border }]}>
          
          {/* Cabecera del Apartamento */}
          <View style={[styles.headerApto, { borderBottomColor: colores.border }]}>
            <View style={styles.headerIzquierda}>
              <Icon name="business-outline" size={20} color={colores.primario} />
              <Text style={[styles.textoApto, { color: colores.textTitle }]}>Apto {apto}</Text>
            </View>
            <Text style={[styles.totalApto, { color: colores.error || '#e74c3c' }]}>
              {moneda} {data.total.toFixed(2)}
            </Text>
          </View>

          {/* Lista de meses que debe ese apartamento */}
          {data.items.map((item, index) => {
            const montoOriginal = parseFloat(item.monto_original);
            const pendiente = parseFloat(item.pendiente);
            const tieneAbono = pendiente < montoOriginal;
            
            // Para no ponerle borde inferior al último elemento de la lista
            const esUltimo = index === data.items.length - 1;

            return (
              <View key={item.id_mensualidad} style={[styles.itemDeuda, !esUltimo && { borderBottomWidth: 1, borderBottomColor: colores.border + '50' }]}>
                <View style={styles.itemInfo}>
                  <Text style={[styles.itemConcepto, { color: colores.text }]}>
                    {formatearMesAnio(item.mes, item.anio)}
                  </Text>
                  {tieneAbono && (
                    <Text style={[styles.itemAbono, { color: colores.success || '#27ae60' }]}>
                      (Abonó {moneda} {(montoOriginal - pendiente).toFixed(2)})
                    </Text>
                  )}
                </View>
                
                <View style={styles.itemMontosDer}>
                  <Text style={[styles.itemMontoActual, { color: colores.textTitle }]}>
                    {moneda} {pendiente.toFixed(2)}
                  </Text>
                  {tieneAbono && (
                    <Text style={[styles.itemMontoOriginal, { color: colores.textPlaceholder }]}>
                      de {moneda} {montoOriginal.toFixed(2)}
                    </Text>
                  )}
                </View>
              </View>
            );
          })}
        </View>
      ))}

      {listaDeudas.length === 0 && (
        <Text style={{ textAlign: 'center', marginTop: 20, fontSize: 16, color: colores.success || '#27ae60' }}>
          ¡Todo está al día! No hay deudas registradas.
        </Text>
      )}
      
      {/* --- MENSAJE INFORMATIVO --- */}
      <View style={styles.infoContainer}>
        <Icon name="information-circle-outline" size={20} color={colores.textPlaceholder} />
        <Text style={[styles.infoTexto, { color: colores.textPlaceholder }]}>
          Si alguno de estos montos ya fue cancelado, por favor repórtalo en el botón "+" de la pantalla principal de Pagos.
        </Text>
      </View>

    </ModalGeneral>
  );
}

const styles = StyleSheet.create({
  // Resumen Global
  resumenCaja: { padding: 20, borderRadius: 12, alignItems: 'center', marginBottom: 20, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1, shadowRadius: 2 },
  resumenTexto: { fontSize: 14, color: '#7f8c8d', marginBottom: 5, textTransform: 'uppercase', letterSpacing: 0.5 },
  resumenTotal: { fontSize: 34, fontWeight: '800' },
  tituloDesglose: { fontSize: 18, fontWeight: '700', marginBottom: 12, marginLeft: 4 },
  
  // Tarjetas agrupadas por apartamento
  tarjetaApto: { borderWidth: 1, borderRadius: 12, marginBottom: 16, overflow: 'hidden' },
  headerApto: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 12, backgroundColor: 'rgba(0,0,0,0.02)', borderBottomWidth: 1 },
  headerIzquierda: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  textoApto: { fontSize: 16, fontWeight: 'bold' },
  totalApto: { fontSize: 16, fontWeight: 'bold' },
  
  // Renglones de deuda individual
  itemDeuda: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 12, paddingHorizontal: 14 },
  itemInfo: { flex: 1, paddingRight: 10 },
  itemConcepto: { fontSize: 15, fontWeight: '600', textTransform: 'capitalize' },
  itemAbono: { fontSize: 12, marginTop: 2, fontWeight: '500' },
  
  itemMontosDer: { alignItems: 'flex-end' },
  itemMontoActual: { fontSize: 15, fontWeight: 'bold' },
  itemMontoOriginal: { fontSize: 12, marginTop: 2 },
  
  // Info Footer
  infoContainer: { flexDirection: 'row', marginTop: 10, padding: 15, backgroundColor: 'rgba(0,0,0,0.03)', borderRadius: 10, alignItems: 'center' },
  infoTexto: { flex: 1, marginLeft: 10, fontSize: 13, lineHeight: 18 }
});