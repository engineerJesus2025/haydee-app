import { View, Text, StyleSheet, Pressable } from 'react-native'
import { TextInput } from 'react-native-gesture-handler'
import Icon from 'react-native-vector-icons/Ionicons'
import { Controller } from 'react-hook-form'
import { useTema } from './../hooks/useTema'
import { useState, useEffect } from 'react' 
import useToggle from '../hooks/useToggle'

export default function InputFormulario ({ control, name, rules, icono, estilos, error, placeholder = '', noDark = false, inputRef, ...props }) {
  const { colores } = useTema()
  const estilosInputFormulario = getEstilosInputFormulario(colores, noDark)
  const maxLengthValue = rules?.maxLength?.value;

  const esCampoPassword = props.esPassword || props.secureTextEntry;
  
  const [ocultarPassword, toggleOcultarPassword] = useToggle(!!esCampoPassword);

  return (
    <Controller
      control={control}
      rules={rules}
      render={({ field: { onChange, onBlur, value } }) => {
        const [valorLocal, setValorLocal] = useState(value || '');

        useEffect(() => {
          setValorLocal(value || '');
        }, [value]);

        return (
          <>
            <View style={estilosInputFormulario.inputContainer}>
              <Icon name={icono.nombre} size={20} color={error ? 'red' : icono.color} style={estilosInputFormulario.inputIcon} />
              
              <TextInput
                {...props}
                secureTextEntry={esCampoPassword ? ocultarPassword : false} 
                placeholder={placeholder}
                placeholderTextColor='#999'
                onBlur={onBlur}
                value={valorLocal}
                onChangeText={(textoIngresado) => {
                  let textoFinal = textoIngresado;
                  if (rules && rules.filtro) {
                    textoFinal = textoIngresado.replace(rules.filtro, ''); 
                  }
                  setValorLocal(textoFinal);
                  onChange(textoFinal);
                }}
                multiline={props.multiline || false}
                ref={inputRef}
                maxLength={maxLengthValue}
                style={[
                  estilosInputFormulario.input,
                  error && estilosInputFormulario.inputError,
                  esCampoPassword && { paddingRight: 50 }, 
                  { ...estilos }
                ]}
              />

              {esCampoPassword && (
                <Pressable
                  onPress={toggleOcultarPassword} 
                  style={estilosInputFormulario.eyeButton}
                >
                  <Icon 
                    name={ocultarPassword ? 'eye-off-outline' : 'eye-outline'} 
                    size={22} 
                    color={error ? '#e74c3c' : '#95a5a6'} 
                  />
                </Pressable>
              )}
            </View>
            
            {maxLengthValue && (
              <View style={[estilosInputFormulario.charCounterContainer, !error && { marginBottom: 15 }]}>
                <Icon name='ellipsis-horizontal' size={14} color='#95a5a6' />
                <Text style={estilosInputFormulario.charCounter}>
                  {valorLocal?.length || 0}/{maxLengthValue}
                </Text>
              </View>
            )}
          </>
        );
      }}
      name={name}
    />
  )
}

const getEstilosInputFormulario = (colores, noDark = false) => StyleSheet.create({
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 5
  },
  inputIcon: {
    marginRight: 12,
    position: 'absolute',
    left: 15,
    zIndex: 1
  },
  eyeButton: {
    position: 'absolute',
    right: 15,
    zIndex: 1,
    padding: 6
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 15,
    paddingLeft: 50,
    borderRadius: 12,
    fontSize: 16,
    backgroundColor: noDark ? '#ffffff' : colores.inputBackground,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    flex: 1,
    color: noDark ? '#2c3e50' : colores.text
  },
  inputError: {
    borderColor: '#e74c3c',
    borderWidth: 1.5
  },
  charCounterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginBottom: 1
  },
  charCounter: {
    fontSize: 12,
    color: '#95a5a6',
    marginLeft: 4
  }
});