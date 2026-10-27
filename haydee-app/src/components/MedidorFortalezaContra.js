import { View, Text, StyleSheet } from 'react-native'
import { useTema } from '../hooks/useTema'

const CRITERIOS = [
  { test: v => v.length >= 8,        peso: 20, etiqueta: '8+ caracteres' },
  { test: v => /[a-z]/.test(v),      peso: 20, etiqueta: 'minúscula' },
  { test: v => /[A-Z]/.test(v),      peso: 20, etiqueta: 'mayúscula' },
  { test: v => /\d/.test(v),         peso: 20, etiqueta: 'número' },
  { test: v => /[_.+*$#%&@-]/.test(v), peso: 20, etiqueta: 'especial' }
]

export default function MedidorFortalezaContra({ valor }) {
  const { colores } = useTema()
  const estilos = getEstilos(colores)

  const fortaleza = CRITERIOS.reduce((acc, c) => acc + (c.test(valor) ? c.peso : 0), 0)
  const fallas = CRITERIOS.filter(c => !c.test(valor)).map(c => c.etiqueta)

  let color = '#e74c3c'
  let etiqueta = 'Muy Débil'
  if (valor.length === 0) { color = '#ccc'; etiqueta = 'Vacío' }
  else if (fortaleza <= 40) { color = '#e74c3c'; etiqueta = 'Muy Débil' }
  else if (fortaleza === 60) { color = '#f39c12'; etiqueta = 'Débil' }
  else if (fortaleza === 80) { color = '#3498db'; etiqueta = 'Buena' }
  else { color = '#2ecc71'; etiqueta = 'Muy Fuerte' }

  return (
    <View style={{ marginTop: 6 }}>
      <View style={estilos.barraFondo}>
        <View style={[estilos.barraRelleno, { width: `${fortaleza}%`, backgroundColor: color }]} />
      </View>
      <Text style={[estilos.texto, { color }]}>
        {valor.length === 0 ? 'Nivel de seguridad: Vacío' : `Nivel: ${etiqueta}`}
        {fallas.length > 0 && valor.length > 0 ? ` · Falta: ${fallas.join(', ')}` : ''}
      </Text>
    </View>
  )
}

const getEstilos = (colores) => StyleSheet.create({
  barraFondo: {
    height: 6, borderRadius: 10,
    backgroundColor: colores?.inputBackground || '#eee', overflow: 'hidden'
  },
  barraRelleno: { height: '100%', borderRadius: 10 },
  texto: { fontSize: 11, marginTop: 4, fontWeight: '500' }
})