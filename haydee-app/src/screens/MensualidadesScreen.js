import React, { useState, useEffect } from 'react';
import { View, Text, ActivityIndicator, StyleSheet } from 'react-native';
import { useDispatch, useSelector } from 'react-redux';
import { fetchMensualidades, fetchDetalleMensualidad } from '../store/slices/mensualidadesSlice';

import HeaderPrincipal from '../components/HeaderPrincipal';
import MensualidadCard from '../components/MensualidadCard';
import ProgresoPresupuesto from '../components/ProgresoPresupuesto';
import ModalDesgloseMensualidad from '../components/ModalDesgloseMensualidad';
import ListaRefrescable from '../components/ListaRefrescable';
import CargandoOverlay from '../components/CargandoOverlay';
import SkeletonCard from '../components/SkeletonCard';
import VistaError from '../components/VistaError';

import { useTema } from './../hooks/useTema';
import { useMensualidades } from '../hooks/useMensualidades'; 

export default function MensualidadesScreen () {
  const { colores } = useTema();
  const estilosMensualidad = getEstilosMensualidades(colores);
  
  const {
    listaMensualidades,
    loading,
    obtenerMensualidades,
    manejarVerDetalles,
    modalVisible,
    cargandoDetalle,
    setModalVisible,
    mensualidadSeleccionada,
    gastado,
    presupuestoTotal,
    recaudado,
    error
  } = useMensualidades();

  const renderHeader = () => (
    <View style={{ marginBottom: 15 }}>
      <Text style={estilosMensualidad.title}>Balance General de Deuda</Text>
      
      {loading && !gastado ? (
         <View style={{ padding: 20, alignItems: 'center' }}>
           <ActivityIndicator size="small" color={colores.primario} />
         </View>
      ) : (
        <ProgresoPresupuesto
          gastado={gastado}
          total={recaudado}
          moneda="Bs"
          titulo="Gastos del Condominio"
          icono="business-outline"
          cargando={loading}
        />
      )}
      
      <Text style={[estilosMensualidad.title, { marginTop: 20, fontSize: 20 }]}>Historial de Mensualidades</Text>
    </View>
  );

  const renderCargandoSkeletons = () => (
    <View style={{ paddingHorizontal: 16, paddingTop: 10 }}>
      {renderHeader()}
      <SkeletonCard tipo="mensualidad" />
      <SkeletonCard tipo="mensualidad" />
      <SkeletonCard tipo="mensualidad" />
    </View>
  );

  const ALTURA_HEADER = 180;
  const ALTURA_ITEM = 175;

  const getItemLayoutMensualidades = React.useCallback((data, index) => ({
    length: ALTURA_ITEM,
    offset: (ALTURA_ITEM * index) + ALTURA_HEADER,
    index,
  }), []);

  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}> 
      <HeaderPrincipal />

      <View style={[estilosMensualidad.mainContentContainer, { flex: 1, paddingHorizontal: 0 }]}>
        
        {loading && listaMensualidades.length === 0 ? (
          renderCargandoSkeletons()
        ) : error ? (
            <VistaError 
            mensaje={error} 
            onRetry={() => obtenerMensualidades(true)} 
          />
        ) : (
          <ListaRefrescable
            data={listaMensualidades}
            keyExtractor={(item) => item.id.toString()}
            cargando={loading}
            onRefresh={() => {
              obtenerMensualidades(true);
            }}
            ListHeaderComponent={renderHeader}
            renderItem={({ item }) => (
              <MensualidadCard 
                mensualidad={item} 
                onPressDetalles={manejarVerDetalles} 
              />
            )}
            mensajeVacio="No hay mensualidades registradas."
            getItemLayout={getItemLayoutMensualidades}
          />
        )}

      </View>

      <ModalDesgloseMensualidad 
        visible={modalVisible}
        onClose={() => setModalVisible(false)}
        mensualidad={mensualidadSeleccionada}
      />

      <CargandoOverlay 
        visible={cargandoDetalle} 
        mensaje="Obteniendo recibo..." 
      />
    </View>
  );
}

const getEstilosMensualidades = (colores) => StyleSheet.create({
  mainContentContainer: {
    flex: 1,
    backgroundColor: colores.background,
    padding: 14
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: colores.textTitle,
    marginBottom: 16,
    paddingHorizontal: 4
  }
});