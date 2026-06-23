import React, { useRef, useState } from 'react';
import { View, Text, TextInput, Pressable, StyleSheet } from 'react-native';
import { Controller } from 'react-hook-form';
import { useTema } from '../hooks/useTema';

export default function InputOTP({ control, name, error, longitud = 6 }) {
  const { colores } = useTema();
  const inputRef = useRef(null);
  const [isFocused, setIsFocused] = useState(false);

  return (
    <Controller
      control={control}
      name={name}
      rules={{ required: true, minLength: longitud, maxLength: longitud }}
      render={({ field: { onChange, value } }) => {
        const val = value || '';
        const digitos = val.split('');

        return (
          <View style={styles.container}>
            {/* LAS CAJAS VISUALES */}
            <Pressable
              style={styles.cajasContainer}
              onPress={() => inputRef.current?.focus()}
            >
              {Array(longitud).fill(0).map((_, index) => {
                const digito = digitos[index] || '';
                const activo = isFocused && digitos.length === index;
                const lleno = digito !== '';
                
                // Si la caja actual es la que está esperando el número, la pintamos de azul
                let colorBorde = '#bdc3c7';
                if (error) colorBorde = '#e74c3c';
                else if (activo || lleno) colorBorde = '#3498db';

                return (
                  <View
                    key={index}
                    style={[
                      styles.caja,
                      { borderColor: colorBorde, backgroundColor: colores.inputBackground || '#fff' }
                    ]}
                  >
                    <Text style={[styles.texto, { color: colores.text }]}>{digito}</Text>
                  </View>
                );
              })}
            </Pressable>

            {/* EL INPUT REAL (INVISIBLE PERO ACTIVO) */}
            <TextInput
              ref={inputRef}
              value={val}
              onChangeText={(texto) => {
                const filtrado = texto.replace(/[^0-9]/g, ''); // Solo números
                onChange(filtrado);
              }}
              maxLength={longitud}
              keyboardType="number-pad"
              // MAGIA PARA AUTOCOMPLETADO DEL OS
              textContentType="oneTimeCode" 
              autoComplete="one-time-code"
              onFocus={() => setIsFocused(true)}
              onBlur={() => setIsFocused(false)}
              style={styles.inputOculto}
              caretHidden={true} // Oculta la barrita parpadeante
            />
          </View>
        );
      }}
    />
  );
}

const styles = StyleSheet.create({
  container: {
    marginVertical: 20,
    alignItems: 'center'
  },
  cajasContainer: {
    flexDirection: 'row',
    gap: 10,
  },
  caja: {
    width: 48,
    height: 60,
    borderWidth: 2,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  texto: {
    fontSize: 26,
    fontWeight: 'bold'
  },
  inputOculto: {
    position: 'absolute',
    width: 1,
    height: 1,
    opacity: 0
  }
});