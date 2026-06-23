import React, { useEffect, useCallback } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';

import HeaderPrincipal from '../components/HeaderPrincipal';
import GastoCard from '../components/GastoCard';
import ModalDetalles from '../components/ModalDetalles';
import ModalFormularioGasto from '../components/ModalFormularioGasto';
import ListaRefrescable from '../components/ListaRefrescable';
import BotonRegistrar from '../components/BotonRegistrar';
import CargandoOverlay from '../components/CargandoOverlay';
import SkeletonCard from '../components/SkeletonCard';
import SelectorMesAnio from '../components/SelectorMesAnio';
import ResumenFinancieroCard from '../components/ResumenFinancieroCard';
import VistaError from '../components/VistaError';

import { useTema } from './../hooks/useTema';
import { useGastos } from '../hooks/useGastos';
import { usePermisos } from '../hooks/usePermisos';

export default function GastosScreen () {
  const { colores } = useTema();
  const estilosGastos = getEstilosGastos(colores);
  
  const { puedeRegistrarGasto, usuario: user } = usePermisos();

  const {
    listaGastos,
    totalGastadoMes,
    loading,
    error,
    cargandoDetalle,
    modalDetalleVisible,
    modalGastoVisible,
    gastoSeleccionado,
    obtenerGastos,
    abrirDetalles,
    cerrarDetalles,
    abrirNuevoGasto,
    cerrarNuevoGasto,
    mesFiltro, 
    anioFiltro, 
    cambiarFiltroFecha,
    periodosDisponibles,
    inicializando
  } = useGastos();

  const estaCargando = loading || inicializando;

  const renderHeader = () => (
    <View style={{ paddingBottom: 5 }}>
      <ResumenFinancieroCard 
        monto={totalGastadoMes} 
        titulo="Total Ejecutado este Mes" 
        moneda="Bs." 
        tipo="gasto"
        cargando={estaCargando} 
      />
      
      <Text style={[estilosGastos.title, { marginTop: 15, marginBottom: 10, paddingHorizontal: 0 }]}>
        Últimos Gastos Registrados
      </Text>
      
      <SelectorMesAnio 
        periodosDisponibles={periodosDisponibles}
        mesActual={mesFiltro} 
        anioActual={anioFiltro} 
        onCambiarMes={(m, a) => cambiarFiltroFecha(m, a)} 
      />

      {estaCargando && (
        <View style={{ paddingTop: 10 }}>
          <SkeletonCard tipo="gasto" />
          <SkeletonCard tipo="gasto" />
          <SkeletonCard tipo="gasto" />
        </View>
      )}
    </View>
  );

  const ALTURA_HEADER = 180;
  const ALTURA_ITEM = 165;

  const elGetItemLayout = useCallback((data, index) => ({
    length: ALTURA_ITEM,
    offset: (ALTURA_ITEM * index) + ALTURA_HEADER,
    index,
  }), []);

  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}>
      <HeaderPrincipal />

      {/* La vista ahora no tiene paddingHorizontal para que la lista maneje sus márgenes nativos */}
      <View style={[estilosGastos.mainContentContainer, { flex: 1, paddingHorizontal: 0, paddingBottom: 0 }]}>
        
        {error ? (
           <VistaError 
            mensaje={error} 
            onRetry={() => obtenerGastos(true)} 
          />
        ) : (
          <ListaRefrescable
            data={estaCargando ? [] : listaGastos} 
            keyExtractor={(item) => item.id.toString()}
            cargando={estaCargando && listaGastos.length > 0} 
            onRefresh={() => obtenerGastos(true)}
            cargandoInicial={estaCargando && listaGastos.length === 0}
            ListHeaderComponent={renderHeader()}
            renderItem={({ item }) => (
              <GastoCard gasto={item} onPressDetalles={abrirDetalles} />
            )}
            mensajeVacio="No hay gastos registrados este mes."
            getItemLayout={elGetItemLayout}
          />
        )}
      </View>
      
      <BotonRegistrar 
        puedeRegistrar={puedeRegistrarGasto}
        modalAbrir={abrirNuevoGasto}
      />
      
      <ModalDetalles
        visible={modalDetalleVisible}
        onClose={cerrarDetalles}
        titulo="Detalle de Gasto"
        datos={gastoSeleccionado}
        campos={[ 
          { key: 'tipo_gasto', label: 'Categoría' },
          { key: 'proveedor', label: 'Proveedor' },
          { key: 'monto', label: 'Monto Total' },
          { key: 'fecha', label: 'Fecha de registro', formato: 'fecha_legible' },
          { key: 'descripcion', label: 'Descripción' }
        ]}
        mostrarImagen={true}
      />
      
      <ModalFormularioGasto 
        visible={modalGastoVisible} 
        onClose={cerrarNuevoGasto} 
      />

      <CargandoOverlay 
        visible={cargandoDetalle} 
        mensaje="Obteniendo recibo..." 
      />
    </View>
  );
}

const getEstilosGastos = (colores) => StyleSheet.create({
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