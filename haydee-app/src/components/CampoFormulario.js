import React from 'react';
import { View } from 'react-native';
import LabelInput from './LabelInput';
import InputFormulario from './InputFormulario';
import ErrorFormulario from './ErrorFormulario';

export default function CampoFormulario({
  tituloLabel,
  iconoLabel,
  control,
  name,
  rules,
  iconoInput,
  error,
  placeholder,
  ...props 
}) {
  return (
    <View style={{ marginBottom: 5 }}>
      <LabelInput titulo={tituloLabel} icono={iconoLabel} />
      
      <InputFormulario
        control={control}
        name={name}
        rules={rules}
        icono={iconoInput}
        error={error}
        placeholder={placeholder}
        {...props} 
      />
      
      <ErrorFormulario error={error} />
    </View>
  );
}