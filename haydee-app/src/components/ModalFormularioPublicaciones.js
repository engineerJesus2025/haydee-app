import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { Controller } from 'react-hook-form';

import { useFormularioPublicaciones } from './../hooks/useFormularioPublicaciones';
import useValidaciones from '../hooks/useValidaciones';
import { useTema } from './../hooks/useTema';

import ModalGeneral from '../components/ModalGeneral';
import CampoFormulario from '../components/CampoFormulario';
import LabelInput from '../components/LabelInput';
import ErrorFormulario from '../components/ErrorFormulario';
import CustomBoton from '../components/CustomBoton';
import MostrarVistaPrevia from '../components/MostrarVistaPrevia';

const ModalFormularioPublicaciones = ({
  visible,
  onClose,
  publicacionEditar = null
}) => {
  const {
    control,
    errors,
    imageUri,
    isSubmitting,
    canSubmit,
    handleSubmit,
    handleImagePick,
    removeImage,
    onSubmit,
    handleCancel,
    onError
  } = useFormularioPublicaciones(onClose, publicacionEditar);

  const validaciones = useValidaciones();
  const { colores } = useTema();
  const esEdicion = !!publicacionEditar;

  // Botones estandarizados para el Footer
  const BotonesFooter = (
    <>
      <CustomBoton
        titulo="Cancelar"
        evento={handleCancel}
        icono={{ nombre: 'close-circle-outline', color: '#fff' }}
        estilos={{ backgroundColor: '#95a5a6'}}
        fuente={16}
      />
      <CustomBoton
        titulo={esEdicion ? 'Actualizar' : 'Publicar'}
        evento={handleSubmit(onSubmit, onError)} 
        icono={{ nombre: esEdicion ? 'save-outline' : 'send-outline', color: '#fff' }}
        disabled={isSubmitting}
        estilos={{ backgroundColor: '#27ae60', opacity: isSubmitting ? 0.6 : 1 }}
        fuente={16}
        loading={isSubmitting}
      />
    </>
  );

  return (
    <ModalGeneral
      visible={visible}
      onClose={handleCancel}
      titulo={esEdicion ? 'Editar Publicación' : 'Nueva Publicación'}
      iconoHeader={{ name: esEdicion ? 'create-outline' : 'add-circle-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={true} 
    >
      
      {/* --- INPUTS DE TEXTO --- */}
      <CampoFormulario
        tituloLabel="Título"
        iconoLabel={{ nombre: 'text-outline', color: '#3498db' }}
        control={control}
        name="titulo"
        rules={validaciones.tituloPublicacion}
        iconoInput={{ nombre: 'pricetag-outline', color: '#95a5a6' }}
        error={errors.titulo}
        placeholder="Ejm: Aviso importante"
      />

      <CampoFormulario
        tituloLabel="Descripción"
        iconoLabel={{ nombre: 'document-text-outline', color: '#3498db' }}
        control={control}
        name="descripcion"
        rules={validaciones.descripcionPublicacion}
        iconoInput={{ nombre: 'reader-outline', color: '#95a5a6' }}
        error={errors.descripcion}
        placeholder="Ejm: Se fue el agua por..."
        estilos={styles.textArea} 
      />

      {/* --- SELECTOR DE TIPO --- */}
      <LabelInput titulo="Tipo de Publicación" icono={{ nombre: 'options-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="tipo"
        rules={{ required: 'Debes seleccionar un tipo' }}
        render={({ field: { onChange, value } }) => (
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 15, gap: 8 }}>
            {['Aviso', 'Evento', 'Noticia'].map((opcion) => {
              const isSelected = value === opcion.toLowerCase(); 

              // para que destaque el color del estado
              let colorBorde = isSelected ? (colores.primario || '#3498db') : colores.border;
              if (isSelected && opcion === 'Aviso') colorBorde = '#27ae60';
              if (isSelected && opcion === 'Evento') colorBorde = '#e74c3c';

              return (
                <TouchableOpacity
                  key={opcion}
                  activeOpacity={0.7}
                  onPress={() => onChange(opcion.toLowerCase())}
                  style={{
                    flex: 1,
                    paddingVertical: 12,
                    borderRadius: 8,
                    borderWidth: 1.5,
                    borderColor: colorBorde,
                    backgroundColor: isSelected ? colorBorde + '15' : colores.card,
                    alignItems: 'center'
                  }}
                >
                  <Text style={{
                    color: isSelected ? colorBorde : colores.textPlaceholder, 
                    fontWeight: isSelected ? 'bold' : '500',
                    fontSize: 14
                  }}>
                    {opcion}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        )}
      />
      <ErrorFormulario error={errors.tipo} />

      {/* --- SELECTOR DE IMAGEN --- */}
      <LabelInput titulo="Imagen" icono={{ nombre: 'image-outline', color: '#3498db' }} />
      <ErrorFormulario error={errors.imagen} />
      <View style={{ flexDirection: 'row', gap: 10, marginBottom: 10 }}>
        <CustomBoton
          titulo={imageUri ? 'Cambiar Imagen' : 'Seleccionar Imagen'}
          evento={handleImagePick}
          icono={{ nombre: imageUri ? 'camera-reverse-outline' : 'image-outline', color: '#fff' }}
          estilos={{ backgroundColor: colores.backgroundBotones, flex: 1 }}
          fuente={16}
        />

        {imageUri && (
          <CustomBoton
            titulo="Quitar"
            evento={removeImage}
            icono={{ nombre: 'trash-outline', color: '#fff' }}
            estilos={{ backgroundColor: '#e74c3c' }}
            fuente={16}
          />
        )}
      </View>

      {imageUri && (
        <MostrarVistaPrevia
          titulo="Vista previa:"
          imageUri={imageUri}
          icono={{ name: 'eye-outline', color: colores.textPlaceholder }}
        />
      )}

    </ModalGeneral>
  );
};

export default ModalFormularioPublicaciones;

// El único estilo que sobrevive es el del textarea
const styles = StyleSheet.create({
  textArea: {
    textAlignVertical: 'top',
    height: 120
  }
});