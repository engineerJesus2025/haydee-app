import React, { useMemo } from 'react';
import { View, Dimensions, StyleSheet, Text, FlatList, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';

import HeaderPrincipal from '../components/HeaderPrincipal';
import PublicacionCard from '../components/PublicacionCard';
import ResumenFinancieroCard from '../components/ResumenFinancieroCard';
import ProgresoPresupuesto from '../components/ProgresoPresupuesto';
import ListaRefrescable from '../components/ListaRefrescable';
import ModalDetallePublicacion from '../components/ModalDetallePublicacion';
import SkeletonCard from '../components/SkeletonCard';
import VistaError from '../components/VistaError';

import { useTema } from '../hooks/useTema';
import { useInicio } from '../hooks/useInicio';

const { width } = Dimensions.get('window');
const SNAP_INTERVAL = (width * 0.75) + 12; 

export default function InicioScreen({ navigation }) {
  const { colores } = useTema();
  const estilosInicio = getEstilosInicio(colores);

  const {
    deudaTotal, 
    gastado, 
    presupuestoTotal, 
    loadingFinanzas, 
    listaPublicaciones, 
    loadingCartelera, 
    refreshing,
    cargarDatosInicio,
    error,
    obtenerItemLayout,
    modalDetalleVisible,
    publicacionSeleccionada,
    abrirModalDetalle,
    cerrarModalDetalle,
    puedeVerGastos,
    puedeVerMensualidad,
    recaudado,
    esAdmin
  } = useInicio();

  const DIMENSIONES_VISTA = useMemo(() => ({
    alturaItem: 340,        
    alturaHeaderBase: 380,  
    alturaEventos: 150,     
  }), []);

  const getItemLayoutInicio = useMemo(
    () => obtenerItemLayout(DIMENSIONES_VISTA),
    [obtenerItemLayout, DIMENSIONES_VISTA]
  );

  const renderFinanzasDashboard = () => (
    <View style={{ marginVertical: 4 }}>
      
      <ResumenFinancieroCard 
        monto={deudaTotal} 
        titulo={esAdmin?"Deuda de Apartamentos":"Tu Deuda Pendiente"}
        moneda="Bs." 
        tipo="deuda"
        onAccion={() => navigation.navigate('Pagos')}
        textoAccion="Pagar"
        cargando={loadingFinanzas} 
      />

      {puedeVerGastos && (
        <ResumenFinancieroCard 
          monto={gastado} 
          titulo="Gastos Ejecutados" 
          moneda="Bs." 
          tipo="gasto"
          onAccion={() => navigation.navigate('Gastos')}
          textoAccion="Historial"
          cargando={loadingFinanzas}
        />
      )}

      {puedeVerMensualidad && (
        <ProgresoPresupuesto 
          gastado={gastado} 
          total={recaudado}
          moneda="Bs."
          titulo="Gastos del Condominio"
          icono="business-outline"
          cargando={loadingFinanzas}
        />
      )}

      {loadingCartelera && (
        <View style={{ paddingTop: 10 }}>
          <Text style={[estilosInicio.title, { paddingHorizontal: 16, marginTop: 10, fontSize: 18, color: colores.textPlaceholder }]}>
            Actualizando cartelera...
          </Text>
          <View style={{ paddingHorizontal: 16, paddingTop: 10 }}>
            <SkeletonCard tipo="publicacion" />
            <SkeletonCard tipo="publicacion" />
          </View>
        </View>
      )}

    </View>
  );

  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}>
      <HeaderPrincipal titulo="Condominio Haydee" />
      
      <View style={[estilosInicio.mainContentContainer, { paddingHorizontal: 0 }]}>
        {error ? (
          <VistaError 
            mensaje={error} 
            onRetry={() => cargarDatosInicio(true)} 
          />
        ) : (
        <ListaRefrescable
          data={loadingCartelera ? [] : listaPublicaciones}
          keyExtractor={(item, index) => item.id ? item.id.toString() : index.toString()}
          cargando={(loadingCartelera || loadingFinanzas) && listaPublicaciones.length > 0}
          onRefresh={() => cargarDatosInicio(true)} 
          
          ListHeaderComponent={renderFinanzasDashboard()}
          
          renderItem={({ item }) => (
            <View style={{ paddingHorizontal: 14 }}>
              <PublicacionCard 
                post={item} 
                onPress={abrirModalDetalle} 
              />
            </View>
          )}
          mensajeVacio="No hay actividad reciente en el condominio."
          getItemLayout={getItemLayoutInicio}
        />
        )}
      </View>
      
      <ModalDetallePublicacion
        visible={modalDetalleVisible}
        onClose={cerrarModalDetalle}
        publicacion={publicacionSeleccionada}
      />
    </View>
  );
}

const getEstilosInicio = (colores) => StyleSheet.create({
  mainContentContainer: {
    flexGrow: 1,
    paddingBottom: 100,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: colores.textTitle,
    marginBottom: 16,
    paddingHorizontal: 4
  }
});