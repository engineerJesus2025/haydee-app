import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

export default function AvatarUsuario({ usuario, size = 40, style }) {
  const inicial = usuario?.nombre_usuario 
    ? usuario.nombre_usuario.charAt(0).toUpperCase()
    : usuario?.usuario 
      ? usuario.usuario.charAt(0).toUpperCase() 
      : 'U';
  const rolTexto = usuario?.nombre_rol
    ? usuario?.nombre_rol.toLowerCase() 
    : usuario?.rol
      ? usuario?.rol.toLowerCase()
      :'Usuario';
  
  let colorFondo = '#95a5a6'; // Gris por defecto (secondary)
  let colorTexto = '#ffffff';

  if (rolTexto === 'administrador global') {
    colorFondo = '#f1c40f'; // warning
    colorTexto = '#000000'; // text-dark
  } else if (rolTexto === 'administrador') {
    colorFondo = '#0d6efd'; // primary
  } else if (rolTexto === 'propietario') {
    colorFondo = '#198754'; // success
  } else if (rolTexto === 'contador') {
    colorFondo = '#dc3545'; // danger
  } else if (rolTexto === 'presidente') {
    colorFondo = '#0dcaf0'; // info
    colorTexto = '#000000'; // text-dark
  }

  return (
    <View
      style={[
        estilos.avatarContainer,
        { 
          width: size, 
          height: size, 
          borderRadius: size / 2,
          backgroundColor: colorFondo 
        },
        style
      ]}
    >
      <Text style={[estilos.avatarTexto, { fontSize: size * 0.45, color: colorTexto }]}>
        {inicial}
      </Text>
    </View>
  );
}

const estilos = StyleSheet.create({
  avatarContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
  },
  avatarTexto: {
    fontWeight: 'bold',
  }
});