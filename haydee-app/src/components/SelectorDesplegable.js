import React, { useState } from 'react';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';

export default function SelectorDesplegable({
  opciones = [],
  valorSeleccionado,
  onSelect,
  placeholder = "Seleccione una opción",
  icono,
  deshabilitado = false
}) {
  const { colores } = useTema();
  const [expandido, setExpandido] = useState(false);

  // Buscamos la opción seleccionada para mostrar su etiqueta
  const seleccion = opciones.find(opt => opt.value === valorSeleccionado);

  return (
    <View style={{ marginBottom: 15 }}>
      {/* BOTÓN PRINCIPAL */}
      <TouchableOpacity
        activeOpacity={0.8}
        onPress={() => setExpandido(!expandido)}
        disabled={deshabilitado}
        style={[
          styles.botonSeleccion, 
          { 
            backgroundColor: deshabilitado ? colores.inputBackground : colores.card, 
            borderColor: expandido ? (colores.primario || '#3498db') : colores.border,
            opacity: deshabilitado ? 0.6 : 1
          }
        ]}
      >
        <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
          {icono && <Icon name={icono} size={20} color={colores.textPlaceholder} style={{ marginRight: 10 }} />}
          <Text 
            style={{ color: seleccion ? colores.text : colores.textPlaceholder, fontSize: 15 }}
            numberOfLines={1}
          >
            {seleccion ? seleccion.label : placeholder}
          </Text>
        </View>
        <Icon name={expandido ? 'chevron-up' : 'chevron-down'} size={20} color={colores.textPlaceholder} />
      </TouchableOpacity>

      {/* LISTA DESPLEGABLE */}
      {expandido && !deshabilitado && (
        <View style={[styles.contenedorOpciones, { backgroundColor: colores.card, borderColor: colores.border }]}>
          <ScrollView 
            nestedScrollEnabled={true} 
            showsVerticalScrollIndicator={true}
            style={{ maxHeight: 200 }} // Altura máxima para no saturar la pantalla
          >
            {opciones.length === 0 ? (
              <Text style={{ padding: 15, color: colores.textPlaceholder, textAlign: 'center' }}>
                No hay opciones disponibles
              </Text>
            ) : (
              opciones.map((opcion, index) => {
                const isSelected = valorSeleccionado === opcion.value;
                return (
                  <TouchableOpacity
                    key={String(opcion.value)}
                    style={[
                      styles.opcion, 
                      index !== opciones.length - 1 && { borderBottomWidth: 1, borderBottomColor: colores.border },
                      isSelected && { backgroundColor: (colores.primario || '#3498db') + '15' }
                    ]}
                    onPress={() => {
                      onSelect(opcion.value);
                      setExpandido(false);
                    }}
                  >
                    <Text style={{ 
                      color: isSelected ? (colores.primario || '#3498db') : colores.text, 
                      fontWeight: isSelected ? 'bold' : 'normal',
                      fontSize: 15
                    }}>
                      {opcion.label}
                    </Text>
                    {isSelected && (
                      <Icon name="checkmark-circle" size={20} color={colores.primario || '#3498db'} />
                    )}
                  </TouchableOpacity>
                );
              })
            )}
          </ScrollView>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  botonSeleccion: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    alignItems: 'center', 
    borderWidth: 1.5, 
    borderRadius: 10, 
    paddingHorizontal: 14,
    paddingVertical: 12
  },
  contenedorOpciones: { 
    borderWidth: 1, 
    borderRadius: 10, 
    marginTop: 6, 
    overflow: 'hidden',
    elevation: 2, // Sombra ligera en Android
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 3 // Sombra iOS
  },
  opcion: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    alignItems: 'center', 
    paddingHorizontal: 16,
    paddingVertical: 14 
  }
});