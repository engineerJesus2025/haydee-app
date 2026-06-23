import { View, Text, FlatList, StyleSheet } from 'react-native'

import { useTema } from './../hooks/useTema'
import PublicacionCard from './PublicacionCard'

const ListaPublicaciones = ({ posts }) => {
  const { colores } = useTema()
  const estilosListaPublicaciones = getEstilosListaPublicaciones(colores)
  
  if (!posts || posts.length === 0) {
    return (
      <View>
        <Text style={estilosListaPublicaciones.headerTitle}>Publicaciones Recientes</Text>
        <Text style={{ color: colores.text }}>No hay publicaciones disponibles.</Text>
      </View>
    )
  }

  return (
    <>
      <Text style={estilosListaPublicaciones.headerTitle}>Publicaciones Recientes</Text>
      <FlatList
        data={posts}
        renderItem={({ item }) => <PublicacionCard post={item} />}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={estilosListaPublicaciones.listContentContainer}
        ListEmptyComponent={<Text>No hay publicaciones para mostrar</Text>}
      />
    </>
  )
}

export default ListaPublicaciones

const getEstilosListaPublicaciones = (colores) => StyleSheet.create({
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    paddingHorizontal: 16,
    paddingVertical: 12,
    color: colores.textTitle
  },
  listContentContainer: {
    paddingHorizontal: 16,
    paddingBottom: 16
  }
})