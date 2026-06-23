import { View, Text, TouchableOpacity, StyleSheet } from 'react-native'
import { useTema } from './../hooks/useTema'
import Icon from 'react-native-vector-icons/Ionicons'

export default function BotonRecuperarContra ({ titulo = 'Nuevo', evento = () => {}, icono = false, disabled = false, estilos = {}, fuente = false }) {
  const { colores } = useTema()
  const estilosCustomBoton = getEstilosCustomBoton(colores)
  return (
    <View style={estilosCustomBoton.topRow}>
      <TouchableOpacity
        style={[estilosCustomBoton.forgotPassword, disabled && { opacity: 0.7 }, { ...estilos }]}
        activeOpacity={0.8}
        onPress={evento}
        disabled={disabled}
      >
        <Text
          style={[estilosCustomBoton.forgotPasswordText,
            fuente && { fontSize: fuente }]}
        >
          {icono && (<Icon name={icono.nombre} size={fuente || 16} color={icono.color} style={estilosCustomBoton.inputIcon} />)}
          {titulo}
        </Text>

      </TouchableOpacity>
    </View>
  )
}

const getEstilosCustomBoton = (colores) => StyleSheet.create({
  forgotPassword: {
    backgroundColor: 'none',
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-end',
    borderWhidth: 0,
    marginBottom: 20,
    marginTop: -5,
    gap: 6
  },
  forgotPasswordText: {
    color: colores.primario || '#007AFF',
    fontSize: 14,
    fontWeight: '500'
  }
})
