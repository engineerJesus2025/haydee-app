import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native'
import { useTema } from './../hooks/useTema'
import Icon from 'react-native-vector-icons/Ionicons'

export default function CustomBoton ({ 
  titulo = 'Nuevo', 
  evento = () => {}, 
  icono = false, 
  disabled = false, 
  loading = false, 
  estilos = {}, 
  fuente = false, 
  noDark = false 
}) {
  const { colores } = useTema()
  const estilosCustomBoton = getEstilosCustomBoton(colores, noDark)

  const isBotonDeshabilitado = disabled || loading;
  
  return (
    <View style={estilosCustomBoton.topRow}>
      <TouchableOpacity
        style={[
          estilosCustomBoton.primaryBtn, 
          isBotonDeshabilitado && { opacity: 0.7 }, 
          { ...estilos }
        ]}
        activeOpacity={0.8}
        onPress={evento}
        disabled={isBotonDeshabilitado}
      >
        {loading ? (
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <ActivityIndicator color="#fff" size="small" style={{ marginRight: 8 }} />
            <Text style={[estilosCustomBoton.primaryBtnText, fuente && { fontSize: fuente }]}>
              {titulo}
            </Text>
          </View>
        ) : (
          <Text style={[estilosCustomBoton.primaryBtnText, fuente && { fontSize: fuente }]}>
            {titulo} {icono && (<Icon name={icono.nombre} size={fuente || 16} color={icono.color} />)}
          </Text>
        )}
      </TouchableOpacity>
    </View>
  )
}

const getEstilosCustomBoton = (colores, noDark = false) => StyleSheet.create({
  topRow: { marginBottom: 12 },
  primaryBtn: {
    alignSelf: 'flex-start',
    backgroundColor: noDark ? '#007BFF' : colores.backgroundBotones,
    paddingVertical: 10,
    paddingHorizontal: 14,
    borderRadius: 6,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.20,
    shadowRadius: 1.41,
    elevation: 2,
    minWidth: 100, 
    alignItems: 'center',
    justifyContent: 'center'
  },
  primaryBtnText: { color: '#fff', fontWeight: '700' },
})
