import React, { useState } from 'react';
import {
  View, Text, TouchableOpacity, StyleSheet,
  Modal, TouchableWithoutFeedback, Platform
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';

export default function MenuAcciones({
  acciones = [],
  icono = 'ellipsis-horizontal',
  colorIcono = '#E1E1F7',
  sizeIcono = 22,
  titulo = 'Opciones',
}) {
  const { colores } = useTema();
  const insets = useSafeAreaInsets(); 
  const [visible, setVisible] = useState(false);

  if (!acciones || acciones.length === 0) return null;

  const abrir = () => setVisible(true);
  const cerrar = () => setVisible(false);

  const ejecutar = (accion) => {
    if (accion.disabled) return;
    cerrar();
    setTimeout(() => accion.onPress && accion.onPress(), 180);
  };

  const paddingInferior = Math.max(insets.bottom, 12);

  return (
    <>
      <TouchableOpacity
        onPress={abrir}
        style={styles.gatillo}
        activeOpacity={0.6}
        accessibilityLabel={titulo}
        accessibilityRole="button"
      >
        <Icon name={icono} size={sizeIcono} color={colorIcono} />
      </TouchableOpacity>

      <Modal
        visible={visible}
        transparent
        animationType="slide"
        onRequestClose={cerrar}
        statusBarTranslucent
      >
        <TouchableWithoutFeedback onPress={cerrar}>
          <View style={styles.overlay}>
            <TouchableWithoutFeedback>
              <View
                style={[
                  styles.sheet,
                  {
                    backgroundColor: colores.card,
                    paddingBottom: paddingInferior,
                  },
                ]}
              >
                <View style={[styles.handle, { backgroundColor: colores.border }]} />
                <Text style={[styles.titulo, { color: colores.textPlaceholder }]}>
                  {titulo}
                </Text>

                {acciones.map((accion, i) => (
                  <TouchableOpacity
                    key={`${accion.label}-${i}`}
                    style={[
                      styles.item,
                      i !== acciones.length - 1 && {
                        borderBottomColor: colores.border,
                        borderBottomWidth: 1,
                      },
                      accion.disabled && { opacity: 0.4 },
                    ]}
                    onPress={() => ejecutar(accion)}
                    activeOpacity={0.7}
                    disabled={accion.disabled}
                    accessibilityRole="button"
                  >
                    {accion.icono && (
                      <Icon
                        name={accion.icono}
                        size={20}
                        color={accion.color || colores.text}
                        style={{ marginRight: 12 }}
                      />
                    )}
                    <Text style={[styles.itemTexto, { color: accion.color || colores.text }]}>
                      {accion.label}
                    </Text>
                  </TouchableOpacity>
                ))}

                <TouchableOpacity
                  style={[styles.cancelar, { borderTopColor: colores.border }]}
                  onPress={cerrar}
                  activeOpacity={0.7}
                >
                  <Text style={[styles.cancelarTexto, { color: colores.textPlaceholder }]}>
                    Cancelar
                  </Text>
                </TouchableOpacity>
              </View>
            </TouchableWithoutFeedback>
          </View>
        </TouchableWithoutFeedback>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  gatillo: { padding: 6, borderRadius: 20 },
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheet: {
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingTop: 8,
  },
  handle: { width: 40, height: 4, borderRadius: 2, alignSelf: 'center', marginBottom: 8 },
  titulo: {
    fontSize: 11, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 1,
    paddingHorizontal: 20, paddingVertical: 10,
  },
  item: { flexDirection: 'row', alignItems: 'center', paddingVertical: 16, paddingHorizontal: 20 },
  itemTexto: { fontSize: 16, fontWeight: '500' },
  cancelar: { borderTopWidth: 1, paddingVertical: 16, alignItems: 'center' },
  cancelarTexto: { fontSize: 15, fontWeight: '600' },
});