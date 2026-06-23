import { TouchableOpacity, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from './../hooks/useTema';
import { useBottomTabBarHeight } from '@react-navigation/bottom-tabs'; 

export default function BotonRegistrar({puedeRegistrar, modalAbrir, icono = "add-outline"}) {
	const { colores } = useTema();
	const estilosBotonRegistrar = getEstilosGastos();
	
	const tabBarHeight = useBottomTabBarHeight();
	
	const bottomDinamico = tabBarHeight + 20; 

	return (
		<>
		{puedeRegistrar && (
	        <TouchableOpacity 
	          style={[
	            estilosBotonRegistrar.fab, 
	            { 
	              backgroundColor: colores.backgroundBotones || '#007BFF',
	              bottom: bottomDinamico
	            }
	          ]} 
	          onPress={modalAbrir}
	          activeOpacity={0.8}
	        >
	          <Icon name={icono} size={28} color="#fff" />
	        </TouchableOpacity>
	      )}
		</>
	)
}

const getEstilosGastos = () => StyleSheet.create({
  fab: {
    position: 'absolute',
    right: 20,
    width: 60,
    height: 60,
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 3,
  }
});