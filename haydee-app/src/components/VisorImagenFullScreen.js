import React from 'react';
import { Modal, View, Image, StyleSheet, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';

export default function VisorImagenFullScreen({ visible, onClose, imageUri }) {
  if (!imageUri) return null;

  return (
    <Modal 
      visible={visible} 
      transparent={true} 
      animationType="fade" 
      onRequestClose={onClose}
    >
      <View style={styles.fullScreenContainer}>
        
        <TouchableOpacity 
          style={styles.closeFullScreenBtn} 
          onPress={onClose}
          activeOpacity={0.7}
        >
          <Icon name="close" size={32} color="#fff" />
        </TouchableOpacity>

        <Image 
          source={{ uri: imageUri }} 
          style={styles.fullScreenImage} 
          resizeMode="contain" // Mantiene las proporciones reales de la foto
        />
        
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  fullScreenContainer: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.95)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  closeFullScreenBtn: {
    position: 'absolute',
    top: 50, 
    right: 20,
    zIndex: 10,
    backgroundColor: 'rgba(255,255,255,0.2)', 
    padding: 8,
    borderRadius: 20,
  },
  fullScreenImage: {
    width: '100%',
    height: '100%',
  }
});