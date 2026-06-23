import React, { useMemo } from 'react';
import { StatusBar, View } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useSelector } from 'react-redux';
import { usePermisos } from '../hooks/usePermisos';
import { useTema } from '../hooks/useTema';
import { EscuchadorNotificaciones } from '../hooks/useInteraccionPush';
import Icon from 'react-native-vector-icons/Ionicons';

import { useSafeAreaInsets } from 'react-native-safe-area-context'; 

// Screens
import LoginScreen from '../screens/LoginScreen';
import InicioScreen from '../screens/InicioScreen';
import PagosScreen from '../screens/PagosScreen';
import GastosScreen from '../screens/GastosScreen';
import MensualidadesScreen from '../screens/MensualidadesScreen';
import CarteleraVirtualScreen from '../screens/CarteleraVirtualScreen';
import PerfilScreen from '../screens/PerfilScreen';

import DetalleCarteleraScreen from '../screens/DetalleCarteleraScreen';
import DetallePagoScreen from '../screens/DetallePagoScreen';

const Stack = createNativeStackNavigator();
const AppStack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

const TabBarIcon = React.memo(({ routeName, focused, color }) => {
  let iconName;

  switch (routeName) {
    case 'Inicio': iconName = focused ? 'home' : 'home-outline'; break;
    case 'Pagos': iconName = focused ? 'cash' : 'cash-outline'; break;
    case 'Cartelera': iconName = focused ? 'megaphone' : 'megaphone-outline'; break;
    case 'Gastos': iconName = focused ? 'cart' : 'cart-outline'; break;
    case 'Mensualidad': iconName = focused ? 'calendar' : 'calendar-outline'; break;
  }

  return <Icon name={iconName} size={26} color={color} />;
});

function LoggedInStack() {
  return (
    <>
      {/* Componente silencioso que escucha los toques en notificaciones en toda la app autenticada */}
      <EscuchadorNotificaciones />
      
      <AppStack.Navigator screenOptions={{ headerShown: false }}>
        {/* Las pestañas principales */}
        <AppStack.Screen name="MainTabs" component={MainTabs} />
        
        {/* Rutas de detalle a las que apuntará el Push (Navegación Stack) */}
        <AppStack.Screen name="DetalleCartelera" component={DetalleCarteleraScreen} />
        <AppStack.Screen name="DetallePago" component={DetallePagoScreen} />
        
        <AppStack.Screen 
          name="Perfil" 
          component={PerfilScreen} 
          options={{ 
            presentation: 'modal', 
            gestureEnabled: true,  
          }}
        />
      </AppStack.Navigator>
    </>
  );
}

function MainTabs () {
  const { colores } = useTema();
  const insets = useSafeAreaInsets();
  
  const paddingAbajo = Math.max(insets.bottom, 10);
  const alturaBarra = 20 + paddingAbajo;

  const { 
    puedeVerGastos, 
    puedeVerMensualidad, 
    puedeVerCartelera 
  } = usePermisos();

  // Almacena la función de opciones en memoria y SOLO la recalcula si cambia el tema (colores) o la altura.
  const navigatorScreenOptions = useMemo(() => ({ route }) => ({
    headerShown: false, 
    safeAreaInsets: { bottom: 0 }, 
    tabBarStyle: {
      backgroundColor: colores.card, 
      borderTopWidth: 1,
      borderTopColor: colores.border, 
      height: alturaBarra, 
      paddingBottom: 0, 
      paddingTop: 5,
      position: 'absolute', 
      left: 0, 
      right: 0, 
      bottom: 0,
      elevation: 5, 
    },
    tabBarLabelStyle: { fontSize: 11, fontWeight: 'bold' },
    tabBarActiveTintColor: colores.primario || '#3498db', 
    tabBarInactiveTintColor: colores.textPlaceholder || '#95a5a6', 
    tabBarIcon: ({ focused, color }) => (
      <TabBarIcon routeName={route.name} focused={focused} color={color} />
    ),
  }), [colores, alturaBarra]); // Dependencias estrictas

  return (
    <>
    <Tab.Navigator screenOptions={navigatorScreenOptions}>
      <Tab.Screen name='Inicio' component={InicioScreen} />
      <Tab.Screen name='Pagos' component={PagosScreen} />

      {puedeVerCartelera && (
        <Tab.Screen name='Cartelera' component={CarteleraVirtualScreen} options={{ tabBarLabel: 'Cartelera' }} />
      )}

      {puedeVerGastos && (
        <Tab.Screen name='Gastos' component={GastosScreen} />
      )}
      
      {puedeVerMensualidad && (
        <Tab.Screen name='Mensualidad' component={MensualidadesScreen} />
      )}
    </Tab.Navigator>
    <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
    </>
  );
}

function AuthStack () {
  return (
    <Stack.Navigator>
      <Stack.Screen name='Login' component={LoginScreen} options={{ headerShown: false }} />
    </Stack.Navigator>
  );
}

export default function Navigation () {
  const { isLogueado } = usePermisos();

  return (
    <NavigationContainer>
      <StatusBar barStyle='light-content' backgroundColor='#000' />
      {isLogueado ? <LoggedInStack /> : <AuthStack />}
    </NavigationContainer>
  );
}