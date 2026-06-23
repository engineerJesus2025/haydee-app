import { View, Text, StyleSheet, Image } from 'react-native'
import { useTema } from './../hooks/useTema'
import Icon from 'react-native-vector-icons/Ionicons'

export default function MostrarVistaPrevia ({ titulo, imageUri, icono = false }) {
  const { colores } = useTema()
  const estilosMostrarVistaPrevia = getEstilosMostrarVistaPrevia(colores)

  return (
    <View style={estilosMostrarVistaPrevia.imagePreviewContainer}>
      <View style={estilosMostrarVistaPrevia.previewHeader}>
        {icono && (<Icon name={icono.name} size={16} color={icono.color} />)}
        <Text style={estilosMostrarVistaPrevia.previewText}>{titulo}</Text>
      </View>
      <View style={estilosMostrarVistaPrevia.imageWrapper}>
        <Image
          source={{ uri: imageUri }}
          style={estilosMostrarVistaPrevia.imagePreview}
          resizeMode='cover'
        />
        <View style={estilosMostrarVistaPrevia.imageOverlay}>
          <Icon name='checkmark-circle' size={32} color='#27ae60' />
        </View>
      </View>
    </View>
  )
};

const getEstilosMostrarVistaPrevia = (colores) => StyleSheet.create({
  imagePreviewContainer: {
    alignItems: 'center',
    marginVertical: 15
  },
  previewHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12
  },
  previewText: {
    fontSize: 14,
    color: colores.textPlaceholder,
    fontWeight: '500',
    marginLeft: 6
  },
  imageWrapper: {
    position: 'relative'
  },
  imagePreview: {
    width: 200,
    height: 200,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: '#ddd',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 4
  },
  imageOverlay: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: 'rgba(255,255,255,0.9)',
    borderRadius: 16,
    padding: 4
  }
})
