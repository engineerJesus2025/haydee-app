import React from 'react';
import { FlatList, RefreshControl, Text, View, ActivityIndicator } from 'react-native';
import { useTema } from '../hooks/useTema';

export default function ListaRefrescable({ 
  data, 
  renderItem, 
  keyExtractor,
  cargando, 
  cargandoInicial = false,
  onRefresh, 
  ListHeaderComponent,
  mensajeVacio = "No hay información disponible.",
  contentContainerStyle,
  onEndReached,
  cargandoMas = false,
  ...restoProps 
}) {
  const { colores } = useTema();

  return (
    <FlatList
      data={data}
      keyExtractor={keyExtractor}
      renderItem={renderItem}
      ListHeaderComponent={ListHeaderComponent}
      showsVerticalScrollIndicator={false}
      contentContainerStyle={contentContainerStyle || { paddingBottom: 100, paddingHorizontal: 16, paddingTop: 10 }}

      initialNumToRender={2}
      removeClippedSubviews={false}
      maxToRenderPerBatch={2}
      updateCellsBatchingPeriod={150}
      windowSize={11}

      onEndReached={onEndReached}
      onEndReachedThreshold={0.5}
      ListFooterComponent={
        cargandoMas ? (
          <View style={{ paddingVertical: 20, alignItems: 'center', justifyContent: 'center' }}>
             <ActivityIndicator size="small" color={colores.primario || '#007BFF'} />
             <Text style={{ marginTop: 6, fontSize: 12, color: colores.textPlaceholder }}>
               Cargando más publicaciones...
             </Text>
          </View>
        ) : null
      }
      ListEmptyComponent={
        /* EL MENSAJE SI NO ESTÁ CARGANDO NADA */
        (!cargando && !cargandoInicial) ? (
          <Text style={{ color: colores.textPlaceholder, textAlign: 'center', marginTop: 20 }}>
            {mensajeVacio}
          </Text>
        ) : null
      }
      refreshControl={
        <RefreshControl
          refreshing={cargando}
          onRefresh={onRefresh}
          colors={[colores.primario || '#007BFF']}
          tintColor={colores.primario || '#007BFF'}
        />
      }
      {...restoProps}
    />
  );
}