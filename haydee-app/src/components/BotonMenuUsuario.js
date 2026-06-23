import { TouchableOpacity, StyleSheet, Text } from 'react-native';
import { useTema } from '../hooks/useTema';
import AvatarUsuario from './AvatarUsuario';

export default function BotonMenuUsuario({ evento, user }) {
  const { colores } = useTema();

  const inicial = user?.usuario ? user.usuario.charAt(0).toUpperCase() : 'U';

  return (
    <TouchableOpacity
      style={[
        estilos.avatarContainer, 
        { backgroundColor: colores.primario || '#3498db' }
      ]}
      onPress={evento}
      activeOpacity={0.8}
    >
      <AvatarUsuario usuario={user} size={38} />
    </TouchableOpacity>
  );
}

const estilos = StyleSheet.create({
  avatarContainer: {
    width: 38,
    height: 38,
    borderRadius: 19, 
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
  },
  avatarTexto: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  }
});