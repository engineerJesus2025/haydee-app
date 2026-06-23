import { View, Text, StyleSheet } from 'react-native'
import Icon from 'react-native-vector-icons/Ionicons'

import { useTema } from './../hooks/useTema'
import CustomBoton from '../components/CustomBoton'

export default function HeaderFormulario ({ icono = false, titulo, evento, estilos = {},  noDark = false }) {
  const { colores } = useTema()
  const estilosHeaderFormulario = getEstilosHeaderFormulario(colores, noDark)

  return (
    <View style={[estilosHeaderFormulario.header, estilos]}>
      <View style={estilosHeaderFormulario.actionContainer}>
        {icono && <Icon name={icono.name} size={24} color={icono.color} />}
      </View>
      
      <Text style={estilosHeaderFormulario.title}>
        {titulo}
      </Text>

      {/* 2. El botón derecho ahora tiene un contenedor balanceado */}
      <View style={estilosHeaderFormulario.actionContainer}>
        <CustomBoton
          titulo=''
          evento={evento}
          icono={{ nombre: 'close-outline', color: '#E1E1F7' }}
          estilos={estilosHeaderFormulario.closeButton}
          fuente={24}
          noDark={false}
        />
      </View>
    </View>
  )
};

const getEstilosHeaderFormulario = (colores, noDark = false) => StyleSheet.create({
  header: {
    backgroundColor: noDark?'#007BFF':colores.backgroundBotones,
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderBottomWidth: 1,
    borderBottomColor: noDark?'#2c3e50':colores.text,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between'
  },
  actionContainer: {
    width: 40, 
    alignItems: 'center',
    justifyContent: 'center'
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: '#E1E1F7',
    textAlign: 'center',
    flex: 1,
    marginHorizontal: 10
  },
  closeButton: {
    paddingVertical: 0,
    paddingTop: 5,
    paddingHorizontal: 0,
    elevation: 0,
    marginBottom: 0,
    marginTop: 0, 
    minWidth: 40,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'start'
  }
})