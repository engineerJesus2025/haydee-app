import React, { useCallback } from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';

import HeaderPrincipal from '../components/HeaderPrincipal';
import PagoCard from '../components/PagoCard';
import ModalDetalles from '../components/ModalDetalles';
import ResumenFinancieroCard from '../components/ResumenFinancieroCard';
import ModalFormularioPago from '../components/ModalFormularioPago'; 
import ModalEstadoCuenta from '../components/ModalEstadoCuenta';
import ListaRefrescable from '../components/ListaRefrescable';
import BotonRegistrar from '../components/BotonRegistrar';
import CargandoOverlay from '../components/CargandoOverlay';
import SkeletonCard from '../components/SkeletonCard';
import SelectorMesAnio from '../components/SelectorMesAnio';
import VistaError from '../components/VistaError';
import BannerPagosPendientes from '../components/BannerPagosPendientes';

import { useTema } from './../hooks/useTema';
import { usePagos } from '../hooks/usePagos';

export default function PagosScreen() {
  const { colores } = useTema();
  const estilosPagos = getEstilosPagos(colores);

  const {
    listaPagos,
    loading,
    error,
    totalDeuda,
    listaDeudas,
    esAdmin,
    modalPagoVisible,
    modalEstadoCuentaVisible,
    cargandoDetalle,
    pagoSeleccionado,
    setModalPagoVisible,
    setModalEstadoCuentaVisible,
    abrirDetalles,
    cerrarDetalles,
    obtenerPagos,
    handleAprobar,
    handleRechazar,
    mesFiltro,
    anioFiltro,
    cambiarFiltroFecha,
    periodosDisponibles,
    puedeAprobarPagos,
    procesandoEstado,
    inicializando
  } = usePagos();

  const estaCargando = loading || inicializando;

  const renderHeader = () => {
  const tienePagosPendientes = listaPagos.some(p => p.estado.toUpperCase() === 'PENDIENTE');

  return (
    <View style={{ paddingBottom: 5 }}>
      <ResumenFinancieroCard 
        monto={totalDeuda} 
        titulo={esAdmin ? "Deuda de los Apartamentos" : "Tu Deuda Pendiente"}
        moneda="Bs." 
        tipo="deuda"
        onAccion={() => setModalEstadoCuentaVisible(true)}
        textoAccion="Ver Estado"
        cargando={estaCargando}
      />
      
      {!esAdmin && tienePagosPendientes && (
        <BannerPagosPendientes visible={!esAdmin && tienePagosPendientes} />
      )}

      <Text style={[estilosPagos.title, { marginTop: 15, marginBottom: 10, paddingHorizontal: 0 }]}>
        Historial de Pagos
      </Text>

      <SelectorMesAnio 
        periodosDisponibles={periodosDisponibles}
        mesActual={mesFiltro} 
        anioActual={anioFiltro} 
        onCambiarMes={(m, a) => cambiarFiltroFecha(m, a)} 
      />

      {estaCargando && (
        <View style={{ paddingTop: 10 }}>
          <SkeletonCard tipo="pago" />
          <SkeletonCard tipo="pago" />
          <SkeletonCard tipo="pago" />
        </View>
      )}
    </View>
  );
};

  const ALTURA_HEADER = 200; 
  const ALTURA_ITEM = 150; 

  const elGetItemLayout = useCallback((data, index) => ({
    length: ALTURA_ITEM,
    offset: (ALTURA_ITEM * index) + ALTURA_HEADER,
    index,
  }), []);

  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}>
      <HeaderPrincipal />

      <View style={[estilosPagos.mainContentContainer, { flex: 1, paddingHorizontal: 0, paddingVertical: 0, padding: 0 }]}>
        
        {error ? (
          <VistaError 
            mensaje={error} 
            onRetry={() => obtenerPagos(true)} 
          />
        ) : (
          <ListaRefrescable
            data={estaCargando ? [] : listaPagos}
            keyExtractor={(item) => item.id.toString()}
            cargando={estaCargando && listaPagos.length > 0}
            onRefresh={() => obtenerPagos(true)}
            ListHeaderComponent={renderHeader()} 
            cargandoInicial={estaCargando && listaPagos.length === 0}
            renderItem={({ item }) => (
              <PagoCard pago={item} onPressDetalles={abrirDetalles} />
            )}
            mensajeVacio="No hay pagos registrados este mes."
            getItemLayout={elGetItemLayout}
          />
        )}
      </View>

      <BotonRegistrar 
        puedeRegistrar={puedeAprobarPagos}
        modalAbrir={() => setModalPagoVisible(true)}
      />

      <ModalDetalles
        visible={modalEstadoCuentaVisible === false && pagoSeleccionado !== null} // Evita solapamientos si abres deudas
        onClose={cerrarDetalles}
        titulo="Detalles del Pago"
        datos={pagoSeleccionado}
        campos={[
          // Lectura rápida en Z
          { key: 'estado', label: 'Estado' },
          { key: 'monto', label: 'Monto Total' }, 
          { key: 'mensualidad', label: 'Mensualidad' },
          { key: 'apartamento', label: 'Apartamento' },
          // Lectura en bloque
          { key: 'fecha', label: 'Fecha de registro', formato: 'fecha_legible' },
          { key: 'observacion', label: 'Observación' }
        ]}
        mostrarImagen={true}
        esAdmin={esAdmin}
        onAprobar={handleAprobar}
        onRechazar={handleRechazar}
        procesando={procesandoEstado}
      />

      <ModalFormularioPago 
        visible={modalPagoVisible} 
        onClose={() => setModalPagoVisible(false)}
      />

      <ModalEstadoCuenta 
        visible={modalEstadoCuentaVisible}
        onClose={() => setModalEstadoCuentaVisible(false)}
        totalDeuda={totalDeuda}
        listaDeudas={listaDeudas}
        moneda="Bs."
      />

      <CargandoOverlay 
        visible={cargandoDetalle} 
        mensaje="Obteniendo recibo..." 
      />
    </View>
  );
}

const getEstilosPagos = (colores) => StyleSheet.create({
  mainContentContainer: {
    flex: 1,
    backgroundColor: colores.background,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: colores.textTitle,
    marginBottom: 16,
    paddingHorizontal: 4
  },
});