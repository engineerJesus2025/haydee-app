import { View, Text, StyleSheet } from 'react-native'
import Icon from 'react-native-vector-icons/Ionicons'

import { useTema } from './../hooks/useTema'

export default function ErrorFormulario ({ error }) {
  const { colores } = useTema()
  const estilosErrorFormulario = getEstilosErrorFormulario(colores)

  return (
    <>
      {error && (
        <View style={estilosErrorFormulario.errorContainer}>
          <Icon name='warning-outline' size={14} color='#e74c3c' />
          <Text style={estilosErrorFormulario.errorText}>
            {error.message}
          </Text>
        </View>
      )}
    </>
  )
}

const getEstilosErrorFormulario = (colores) => StyleSheet.create({
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15
  },
  errorText: {
    color: '#e74c3c',
    fontSize: 13,
    fontWeight: '500',
    marginLeft: 6
  }
})
