import { View, Text, StyleSheet, TouchableOpacity } from 'react-native'
import Icon from 'react-native-vector-icons/Ionicons'

import { useTema } from './../hooks/useTema'

export default function HeaderFormulario({
  icono = false,
  titulo,
  evento,
  estilos = {},
  noDark = false,
  headerAcciones = null,
}) {
  const { colores } = useTema()
  const s = getEstilos(colores, noDark)

  return (
    <View style={[s.header, estilos]}>
      {/* Zona izquierda — solo si hay ícono */}
      {icono ? (
        <View style={s.iconZone}>
          <Icon name={icono.name} size={22} color={icono.color} />
        </View>
      ) : (
        <View style={s.iconZone} />  // placeholder para mantener simetría
      )}

      {/* Título — centro real */}
      <Text style={s.title} numberOfLines={1} ellipsizeMode="tail">
        {titulo}
      </Text>

      {/* Zona derecha — acciones + X */}
      <View style={s.rightZone}>
        {headerAcciones}
        <TouchableOpacity
          onPress={evento}
          style={s.closeBtn}
          hitSlop={{ top: 10, bottom: 10, left: 6, right: 6 }}
          accessibilityRole="button"
          accessibilityLabel="Cerrar"
        >
          <Icon name="close" size={24} color="#E1E1F7" />
        </TouchableOpacity>
      </View>
    </View>
  )
}

const getEstilos = (colores, noDark = false) => StyleSheet.create({
  header: {
    backgroundColor: noDark ? '#007BFF' : colores.backgroundBotones,
    paddingVertical: 10,
    paddingHorizontal: 12,
    flexDirection: 'row',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: noDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 2,
    elevation: 2,
    zIndex: 10,
  },
  iconZone: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    flex: 1,
    fontSize: 18,
    fontWeight: '700',
    color: '#E1E1F7',
    textAlign: 'center',
    marginHorizontal: 8,
    letterSpacing: 0.2,
  },
  rightZone: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  closeBtn: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 22,
  },
})