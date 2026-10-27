import { View, Text, StyleSheet, Image, TouchableOpacity } from 'react-native'
import { useTema } from './../hooks/useTema'
import Icon from 'react-native-vector-icons/Ionicons'
import EscaneoOverlay from './EscaneoOverlay';

export default function MostrarVistaPrevia ({ titulo, imageUri, icono = false, onRemove = null, escaneando = false }) {
  const { colores } = useTema()
  const estilos = getEstilosMostrarVistaPrevia(colores)

  return (
    <View style={estilos.imagePreviewContainer}>
      <View style={estilos.previewHeader}>
        {icono && (<Icon name={icono.name} size={16} color={icono.color} />)}
        <Text style={estilos.previewText}>{titulo}</Text>
      </View>
      <View style={estilos.imageWrapper}>
        <Image
          source={{ uri: imageUri }}
          style={estilos.imagePreview}
          resizeMode='cover'
        />
        <View style={estilos.imageOverlay}>
          <Icon name='checkmark-circle' size={32} color='#27ae60' />
        </View>
        {/* ✕ Botón de quitar*/}
        {onRemove && (
          <TouchableOpacity
            onPress={onRemove}
            style={estilos.removeButton}
            activeOpacity={0.8}
            accessibilityLabel="Quitar comprobante"
            accessibilityRole="button"
            hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
          >
            <Icon name='close' size={18} color='#fff' />
          </TouchableOpacity>
        )}
        <EscaneoOverlay visible={escaneando} width={200} height={200} />
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
  },
  removeButton: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: 'rgba(231, 76, 60, 0.95)',
    borderRadius: 16,
    width: 28,
    height: 28,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.3,
    shadowRadius: 2,
    elevation: 4,
  },
})