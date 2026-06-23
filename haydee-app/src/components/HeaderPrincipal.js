import { View, Text, Pressable, StyleSheet, TouchableOpacity } from 'react-native'
import { useNavigation } from '@react-navigation/native';

import Icon from 'react-native-vector-icons/Ionicons'; 

import BotonMenuUsuario from '../components/BotonMenuUsuario'
import { useSelector } from 'react-redux'
import { useTema } from './../hooks/useTema'
import { useSafeAreaInsets } from 'react-native-safe-area-context'

export default function HeaderPrincipal ({ mostrarBotonAtras = false }) {
  const navigation = useNavigation();

  const { user } = useSelector(state => state.usuario)
  const insets = useSafeAreaInsets()
  const { colores } = useTema()
  const estilosHeader = getEstilosHeader(colores)
  
  return (
    <>
      <View style={{ height: insets.top, backgroundColor: '#000' }} />
      <View style={[estilosHeader.appHeader]}>

        <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: 10 }}>
          {mostrarBotonAtras && (
            <TouchableOpacity 
              onPress={() => navigation.goBack()} 
              style={{ marginRight: 10, padding: 6 }}
              activeOpacity={0.7}
            >
              <Icon name="arrow-back-outline" size={26} color="#fff" />
            </TouchableOpacity>
          )}

          <Text 
            style={{ color: '#fff', fontSize: 18, fontWeight: 'bold', letterSpacing: 0.5 }}
            numberOfLines={1} 
          >
            Residencias
          </Text>
          <Text 
            style={{ color: '#3498db', fontSize: 18, fontWeight: '300', marginLeft: 4 }}
            numberOfLines={1}
          >
            Haydee
          </Text>
        </View>

        <View style={[estilosHeader.headerRight, { gap: 15 }]}>
          {/* Si estamos en el perfil, oculto el avatar para no ser redundantes */}
          {!mostrarBotonAtras && (
            <BotonMenuUsuario 
              evento={() => navigation.navigate('Perfil')} 
              user={user} 
            />
          )}
        </View>
      </View>
    </>
  )
}

const getEstilosHeader = (colores) => StyleSheet.create({
  appHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    paddingHorizontal: 15,
    backgroundColor: colores.navigation,
    borderBottomWidth: 1,
    borderColor: '#3E4756',
    zIndex: 10
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center'
  }
})