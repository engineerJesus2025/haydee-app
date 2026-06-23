import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { useTema } from '../hooks/useTema';
import Icon from 'react-native-vector-icons/Ionicons';

import ModalGeneral from './ModalGeneral';
import CustomBoton from './CustomBoton';

// Componente interno para renderizar a cada inquilino
const ApartamentoCard = ({ apto, coloresTema }) => {
  const montoTotal = parseFloat(apto.monto);
  const pagado = parseFloat(apto.pagado);
  const tasaBCV = parseFloat(apto.tasa_dolar);
  
  const deudaRestante = montoTotal - pagado;
  const estaSolvente = deudaRestante <= 0;
  
  const montoDolar = (montoTotal / tasaBCV).toFixed(2);

  return (
    <View style={[styles.cardContainer, { backgroundColor: coloresTema.card, borderColor: coloresTema.border }]}>
      
      <View style={styles.cardHeader}>
        <View style={styles.aptInfo}>
          <Icon name="business-outline" size={18} color={coloresTema.primario} style={{ marginRight: 6 }} />
          <Text style={[styles.aptTexto, { color: coloresTema.textTitle }]}>Apt. {apto.nro_apartamento}</Text>
        </View>
        <Text style={[styles.propietarioTexto, { color: coloresTema.textPlaceholder }]}>
          {apto.nombre} {apto.apellido}
        </Text>
      </View>

      <View style={[styles.separadorInterno, { backgroundColor: coloresTema.border }]} />

      <View style={styles.cardBody}>
        <View style={styles.columnaMonto}>
          <Text style={styles.labelPequeño}>Monto de la Cuota</Text>
          <Text style={[styles.montoPrincipal, { color: coloresTema.text }]}>{montoTotal.toFixed(2)} Bs.</Text>
          <Text style={styles.montoSecundario}>Ref: {montoDolar} $</Text>
        </View>

        <View style={styles.columnaEstado}>
          {estaSolvente ? (
            <View style={[styles.badge, styles.badgeSolvente]}>
              <Icon name="checkmark-circle-outline" size={14} color="#27ae60" style={{ marginRight: 4 }} />
              <Text style={styles.textoSolvente}>Solvente</Text>
            </View>
          ) : (
            <View style={styles.contenedorDeuda}>
              <View style={[styles.badge, styles.badgeDeuda]}>
                <Icon name="alert-circle-outline" size={14} color="#e74c3c" style={{ marginRight: 4 }} />
                <Text style={styles.textoDeuda}>Deuda</Text>
              </View>
              <Text style={styles.montoDeudaTexto}>{deudaRestante.toFixed(2)} Bs.</Text>
            </View>
          )}
        </View>
      </View>
    </View>
  );
};

export default function ModalDesgloseMensualidad({ visible, onClose, mensualidad }) {
  const { colores } = useTema();

  if (!mensualidad) return null;

  const listaAptos = mensualidad.apartamentos || [];
  const totalBs = parseFloat(mensualidad.total) || 0;
  const tasa = parseFloat(mensualidad.tasa) || 1;
  const totalDolar = (totalBs / tasa).toFixed(2);

  const BotonesFooter = (
    <CustomBoton 
      titulo="Cerrar Reporte" 
      evento={onClose} 
      estilos={{ backgroundColor: '#95a5a6' }} 
      icono={{ nombre: 'close-circle-outline', color: '#fff' }}
      fuente={16}
    />
  );

  return (
    <ModalGeneral
      visible={visible}
      onClose={onClose}
      titulo={`Reporte: ${mensualidad.mes}`}
      iconoHeader={{ name: 'document-text-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={false}
    >

      {/* --- CAJA DE RESUMEN --- */}
      <View style={[styles.resumenCaja, { backgroundColor: colores.card, borderColor: colores.border }]}>
        <View style={styles.resumenRow}>
          
          <View style={styles.resumenCol}>
            <Icon name="calendar-outline" size={16} color="#3498db" style={styles.iconoResumen} />
            <Text style={styles.resumenLabel}>PERÍODO</Text>
            <Text style={[styles.resumenValor, { color: colores.textTitle }]} numberOfLines={1}>
              {mensualidad.mes.toUpperCase()}
            </Text>
          </View>

          <View style={[styles.separadorVertical, { backgroundColor: colores.border }]} />

          <View style={styles.resumenCol}>
            <Icon name="cash-outline" size={16} color="#27ae60" style={styles.iconoResumen} />
            <Text style={styles.resumenLabel}>MONTO BASE</Text>
            <Text style={[styles.resumenValor, { color: colores.textTitle }]} numberOfLines={1}>
              {totalBs.toFixed(2)} Bs.
            </Text>
            <Text style={styles.resumenSubValor}>Ref: {totalDolar} $</Text>
          </View>

          <View style={[styles.separadorVertical, { backgroundColor: colores.border }]} />

          <View style={styles.resumenCol}>
            <Icon name="stats-chart-outline" size={16} color="#9b59b6" style={styles.iconoResumen} />
            <Text style={styles.resumenLabel}>TASA BCV</Text>
            <Text style={[styles.resumenValor, { color: colores.textTitle }]} numberOfLines={1}>
              {tasa} Bs/$
            </Text>
          </View>

        </View>
      </View>

      {/* --- LISTA DE APARTAMENTOS --- */}
      {listaAptos.length > 0 ? (
        listaAptos.map((item) => (
          <ApartamentoCard 
            key={item.id_mensualidad} 
            apto={item} 
            coloresTema={colores} 
          />
        ))
      ) : (
        <Text style={{ textAlign: 'center', color: colores.textPlaceholder, marginTop: 40 }}>
          Aún no hay apartamentos registrados para esta mensualidad.
        </Text>
      )}

    </ModalGeneral>
  );
}

const styles = StyleSheet.create({
  resumenCaja: { paddingVertical: 15, paddingHorizontal: 5, borderRadius: 12, marginBottom: 20, elevation: 1, borderWidth: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 2 },
  resumenRow: { flexDirection: 'row', justifyContent: 'space-evenly', alignItems: 'start', width: '100%' },
  resumenCol: { alignItems: 'center', flex: 1 },
  iconoResumen: { marginBottom: 4 },
  resumenLabel: { fontSize: 10, color: '#7f8c8d', letterSpacing: 0.5, marginBottom: 4 },
  resumenValor: { fontSize: 13, fontWeight: 'bold' },
  resumenSubValor: { fontSize: 10, color: '#95a5a6', marginTop: 3 },
  separadorVertical: { width: 1, height: '70%', opacity: 0.5 },
  
  cardContainer: { borderWidth: 1, borderRadius: 12, padding: 15, marginBottom: 15, elevation: 1, shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 3, shadowOffset: {width: 0, height: 2} },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  aptInfo: { flexDirection: 'row', alignItems: 'center' },
  aptTexto: { fontSize: 16, fontWeight: 'bold' },
  propietarioTexto: { fontSize: 14, fontWeight: '500', textTransform: 'capitalize' },
  
  separadorInterno: { height: 1, width: '100%', marginVertical: 12, opacity: 0.6 },
  
  cardBody: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  columnaMonto: { flex: 1 },
  labelPequeño: { fontSize: 11, color: '#7f8c8d', marginBottom: 2 },
  montoPrincipal: { fontSize: 15, fontWeight: 'bold' },
  montoSecundario: { fontSize: 12, color: '#95a5a6', marginTop: 2 },
  
  columnaEstado: { alignItems: 'flex-end' },
  badge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 20, borderWidth: 1 },
  badgeSolvente: { backgroundColor: '#e8f8f5', borderColor: '#27ae60' },
  textoSolvente: { color: '#27ae60', fontSize: 12, fontWeight: 'bold' },
  
  contenedorDeuda: { alignItems: 'flex-end' },
  badgeDeuda: { backgroundColor: '#fdedec', borderColor: '#e74c3c' },
  textoDeuda: { color: '#e74c3c', fontSize: 12, fontWeight: 'bold' },
  montoDeudaTexto: { color: '#e74c3c', fontSize: 12, fontWeight: 'bold', marginTop: 4 },
});