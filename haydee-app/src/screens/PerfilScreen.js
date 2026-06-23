import React, { useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';

import HeaderPrincipal from '../components/HeaderPrincipal';
import BotonCambiarTema from '../components/BotonCambiarTema';
import AvatarUsuario from '../components/AvatarUsuario'; 
import { useTema } from '../hooks/useTema';
import { usePerfil } from '../hooks/usePerfil';
import { useSafeAreaInsets } from 'react-native-safe-area-context'

export default function PerfilScreen() {
  const insets = useSafeAreaInsets();
  const { colores, modoOscuro } = useTema();
  const { usuario, loading, handleLogout, cargarDatosServidor } = usePerfil();
  const estilos = getEstilos(colores, modoOscuro); 
  
  return (
    <View style={{ flex: 1, backgroundColor: colores.background }}>
      <HeaderPrincipal mostrarBotonAtras={true} />
      
      <ScrollView 
        contentContainerStyle={estilos.container} 
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={loading}
            onRefresh={cargarDatosServidor}
            colors={[colores.primario || '#007BFF']}
            tintColor={colores.primario || '#007BFF'}
          />
        }
      >
        
        {/* CABECERA DE IDENTIDAD */}
        <View style={estilos.headerPerfil}>
          <View style={estilos.avatarContenedor}>
            <AvatarUsuario usuario={usuario} size={110} />
          </View>
          
          <Text style={estilos.nombre}>
            {usuario?.nombre_usuario} {usuario?.apellido}
          </Text>
          
          {/* SOFT BADGE */}
          <View style={[
            estilos.badgeRol, 
            { backgroundColor: modoOscuro ? colores.primario + '35' : colores.primario + '15' }
          ]}>
            <Icon 
              name="shield-checkmark" 
              size={14} 
              color={modoOscuro ? '#f8f9fa' : colores.primario} 
              style={{ marginRight: 6 }} 
            />
            <Text style={[
              estilos.rolTexto, 
              { color: modoOscuro ? '#f8f9fa' : colores.primario }
            ]}>
              {(usuario?.nombre_rol || 'Usuario del Sistema').toUpperCase()}
            </Text>
          </View>
        </View>

        {/* INFORMACIÓN PERSONAL */}
        <Text style={estilos.seccionTitulo}>Información de Contacto</Text>
        <View style={estilos.card}>
          <View style={estilos.infoRow}>
            <View style={[estilos.iconoCaja, { backgroundColor: modoOscuro ? 'rgba(230, 230, 246, 0.1)' : '#99999920' }]}>
              <Icon name="mail" size={20} color={colores.textPlaceholder} />
            </View>
            <View style={estilos.infoTextos}>
              <Text style={estilos.infoLabel}>Correo Electrónico</Text>
              <Text style={estilos.infoValor}>{usuario?.correo || 'No disponible'}</Text>
            </View>
          </View>
          
          <View style={estilos.separador} />

          <View style={estilos.infoRow}>
            <View style={[estilos.iconoCaja, { backgroundColor: modoOscuro ? 'rgba(230, 230, 246, 0.1)' : '#99999920' }]}>
              <Icon name="business" size={20} color={colores.textPlaceholder} />
            </View>
            <View style={estilos.infoTextos}>
              <Text style={estilos.infoLabel}>Residencia</Text>
              <Text style={estilos.infoValor}>Condominios Haydee</Text>
            </View>
          </View>
        </View>

        {/* CONFIGURACIÓN */}
        <Text style={estilos.seccionTitulo}>Preferencias</Text>
        <View style={estilos.card}>
          <View style={estilos.ajusteRow}>
            <View style={estilos.ajusteInfo}>
              
              <View style={[estilos.iconoCaja, { backgroundColor: modoOscuro ? '#00e5ff15' : '#ffd70015', marginRight: 15 }]}>
                <Icon 
                  name={modoOscuro ? "moon" : "sunny"} 
                  size={20} 
                  color={modoOscuro ? '#00e5ff' : '#d35400'} 
                />
              </View>
              
              <View style={{ flex: 1, paddingRight: 15 }}>
                <Text style={estilos.ajusteTexto}>Apariencia</Text>
                <Text style={estilos.infoLabel}>{modoOscuro ? 'Modo Nocturno' : 'Modo Diurno'}</Text>
              </View>

            </View>
            <BotonCambiarTema />
          </View>
        </View>

        {/* BOTON CERRAR SESIÓN */}
        <TouchableOpacity 
          style={estilos.botonLogout} 
          onPress={handleLogout}
          activeOpacity={0.7}
        >
          <Icon name="power-outline" size={22} color="#e74c3c" />
          <Text style={estilos.logoutTexto}>Cerrar Sesión</Text>
        </TouchableOpacity>

        <Text style={estilos.version}>Condominios Haydee • v1.2.2</Text>
      </ScrollView>
      <View style={{ height: Math.max(insets.bottom, 10), backgroundColor: '#000' }} />
    </View>
  );
}

const getEstilos = (colores, modoOscuro) => StyleSheet.create({
  container: { padding: 20, paddingBottom: 50 },
  
  headerPerfil: { alignItems: 'center', marginBottom: 35, marginTop: 15 },
  avatarContenedor: {
    padding: 4,
    backgroundColor: colores.background,
    borderRadius: 60,
    elevation: 8,
    shadowColor: colores.primario,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: modoOscuro ? 0.6 : 0.3,
    shadowRadius: 8,
    position: 'relative', 
  },
  nombre: { fontSize: 26, fontWeight: '800', color: colores.textTitle, marginTop: 18, letterSpacing: -0.5 },
  
  badgeRol: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 6, borderRadius: 20, marginTop: 8 },
  rolTexto: { fontSize: 12, fontWeight: '700', letterSpacing: 0.5 },
  
  seccionTitulo: { fontSize: 13, fontWeight: '700', color: colores.textPlaceholder, marginBottom: 12, marginTop: 10, textTransform: 'uppercase', letterSpacing: 1.5, marginLeft: 5 },
  
  card: { backgroundColor: colores.card, borderRadius: 20, padding: 18, marginBottom: 20, elevation: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 6, borderWidth: 1, borderColor: colores.border },
  
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 4 },
  iconoCaja: { width: 42, height: 42, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
  infoTextos: { marginLeft: 15, flex: 1 },
  infoLabel: { fontSize: 12, color: colores.textPlaceholder, fontWeight: '500' },
  infoValor: { fontSize: 16, color: colores.text, fontWeight: '600', marginTop: 3 },
  
  separador: { height: 1, backgroundColor: colores.border, marginVertical: 16, opacity: 0.7 },
  
  ajusteRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 2 },
  ajusteInfo: { flexDirection: 'row', alignItems: 'center', flex: 1 },
  ajusteTexto: { fontSize: 16, color: colores.textTitle, fontWeight: '600', marginBottom: 2 },
  
  botonLogout: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: 'transparent', borderWidth: 1.5, borderColor: '#e74c3c', padding: 16, borderRadius: 16, marginTop: 25 },
  logoutTexto: { color: '#e74c3c', fontWeight: 'bold', marginLeft: 10, fontSize: 16, letterSpacing: 0.5 },
  
  version: { textAlign: 'center', color: colores.textPlaceholder, fontSize: 12, marginTop: 35, fontWeight: '500', letterSpacing: 1 }
});