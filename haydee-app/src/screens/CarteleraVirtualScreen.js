import { useMemo, useState, useEffect, useCallback } from 'react';
import { View, Text, FlatList, RefreshControl, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import { Calendar } from 'react-native-calendars';
import { configurarCalendarioIdioma } from '../utils/configuracionCalendario';

import HeaderPrincipal from '../components/HeaderPrincipal';
import ModalFormularioPublicaciones from '../components/ModalFormularioPublicaciones';
import PublicacionCard from '../components/PublicacionCard'; 
import ListaRefrescable from '../components/ListaRefrescable';
import BotonRegistrar from '../components/BotonRegistrar';
import SkeletonCard from '../components/SkeletonCard';
import ModalDetallePublicacion from '../components/ModalDetallePublicacion';
import VistaError from '../components/VistaError';

import { useTema } from '../hooks/useTema';
import { useCarteleraVirtual } from '../hooks/useCarteleraVirtual';
import { usePermisos } from '../hooks/usePermisos';

import { criptografiaMovil } from '../utils/criptografiaMovil'; 

configurarCalendarioIdioma();

export default function CarteleraVirtualScreen () {
  const { colores } = useTema();
  const estilosCarteleraVirtual = useMemo(() => getEstilosCarteleraVirtual(colores), [colores]);
  const calendarTheme = useMemo(() => ({
    calendarBackground: colores.card,
    textSectionTitleColor: colores.textPlaceholder,
    dayTextColor: colores.text,
    todayTextColor: '#007BFF',
    monthTextColor: colores.textTitle,
    arrowColor: '#007BFF',
  }), [colores]);

  const { puedePublicarCartelera, usuario: user } = usePermisos();

  const {
    listaPublicacionesMostrar,
    markedDates,
    fechaSeleccionada,
    setFechaSeleccionada,
    cargando,
    error,
    cargandoMas,
    cargarMasPublicaciones,
    obtenerPublicaciones,
    modalVisible,
    modalDetalleVisible,
    publicacionSeleccionada,
    abrirModalNuevaPublicacion,
    cerrarModalNuevaPublicacion,
    abrirModalDetalle,
    cerrarModalDetalle,
    handleGuardarEdicion
  } = useCarteleraVirtual(colores);

  const renderPublicacion = useCallback(({ item }) => (
    <PublicacionCard 
      post={item} 
      onPress={abrirModalDetalle} 
    />
  ), [abrirModalDetalle]);

  const headerComponent = useMemo(() => (
    <View style={{ marginBottom: 20 }}>
      <Text style={[estilosCarteleraVirtual.title, { marginBottom: 10 }]}>Cartelera Virtual</Text>
      
      <View style={estilosCarteleraVirtual.calendarContainer}>
        <Calendar
          markingType={'multi-dot'}
          onDayPress={(day) => {
            setFechaSeleccionada(fechaSeleccionada === day.dateString ? '' : day.dateString);
          }}
          markedDates={markedDates}
          theme={calendarTheme} 
        />
      </View>

      {/* LEYENDA DE COLORES */}
      <View style={estilosCarteleraVirtual.leyendaContainer}>
        <View style={estilosCarteleraVirtual.leyendaItem}>
          <View style={[estilosCarteleraVirtual.leyendaPunto, { backgroundColor: '#f39c12' }]} />
          <Text style={{ color: colores.text, fontSize: 13 }}>Aviso</Text>
        </View>
        <View style={estilosCarteleraVirtual.leyendaItem}>
          <View style={[estilosCarteleraVirtual.leyendaPunto, { backgroundColor: '#e74c3c' }]} />
          <Text style={{ color: colores.text, fontSize: 13 }}>Evento</Text>
        </View>
        <View style={estilosCarteleraVirtual.leyendaItem}>
          <View style={[estilosCarteleraVirtual.leyendaPunto, { backgroundColor: '#3498db' }]} />
          <Text style={{ color: colores.text, fontSize: 13 }}>Noticia</Text>
        </View>
      </View>

      <View style={estilosCarteleraVirtual.filtroInfo}>
        <Text style={{ color: colores.textTitle, fontSize: 18, fontWeight: 'bold' }}>
          {fechaSeleccionada ? `Publicaciones del día` : 'Últimas publicaciones'}
        </Text>
        {fechaSeleccionada ? (
          <TouchableOpacity onPress={() => setFechaSeleccionada('')}>
            <Text style={{ color: '#007BFF', fontWeight: 'bold' }}>Ver todas</Text>
          </TouchableOpacity>
        ) : null}
      </View>
    </View>
  ), [fechaSeleccionada, markedDates, estilosCarteleraVirtual, calendarTheme]);

  const ALTURA_HEADER_CALENDARIO = 450;
  const ALTURA_ITEM_POST = 340;

  // const elGetItemLayout = useCallback((data, index) => ({
  //   length: ALTURA_ITEM_POST,
  //   offset: (ALTURA_ITEM_POST * index) + ALTURA_HEADER_CALENDARIO,
  //   index,
  // }), []);

  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}>
      <HeaderPrincipal />

      <View style={[estilosCarteleraVirtual.mainContentContainer, { flex: 1, paddingHorizontal: 16, paddingTop: 10 }]}>
        {cargando ? (
           <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
             <ActivityIndicator size="large" color={colores.primario || '#007BFF'} />
             <Text style={{ marginTop: 10, color: colores.textPlaceholder }}>Cargando cartelera...</Text>
           </View>
        ) : error ? (
           <VistaError 
            mensaje={error} 
            onRetry={obtenerPublicaciones} 
          />
        ) : (
          <ListaRefrescable
            data={listaPublicacionesMostrar}
            keyExtractor={(item) => item.id.toString()}
            cargando={cargando}
            onRefresh={() => obtenerPublicaciones(true)}
            onEndReached={cargarMasPublicaciones}
            cargandoMas={cargandoMas}
            ListHeaderComponent={() => headerComponent}
            renderItem={renderPublicacion}
            mensajeVacio="No hay publicaciones para esta fecha."
          />
        )}
      </View>

      <BotonRegistrar 
        puedeRegistrar={puedePublicarCartelera}
        modalAbrir={abrirModalNuevaPublicacion}
      />

      <ModalFormularioPublicaciones
        visible={modalVisible}
        onClose={cerrarModalNuevaPublicacion}
      />

      {/* MODAL DE DETALLES */}
      <ModalDetallePublicacion
        visible={modalDetalleVisible}
        onClose={cerrarModalDetalle}
        publicacion={publicacionSeleccionada}
      />
    </View>
  );
}

const getEstilosCarteleraVirtual = (colores) => StyleSheet.create({
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
  },
  calendarContainer: {
    borderRadius: 12,
    overflow: 'hidden',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  leyendaContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 15,
    marginBottom: 5,
    gap: 15
  },
  leyendaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: 5
  },
  leyendaPunto: {
    width: 10,
    height: 10,
    borderRadius: 5,
    marginRight: 6
  },
  filtroInfo: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 15,
    paddingHorizontal: 5
  },
})
