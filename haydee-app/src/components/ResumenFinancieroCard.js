import React, { useEffect, useRef, memo } from 'react';
import { View, Text, StyleSheet, Animated, TouchableOpacity } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';

const ResumenFinancieroCard = ({ 
  monto = 0, 
  titulo = "Resumen",
  moneda = "Bs.",
  tipo = "deuda", 
  onAccion,
  textoAccion = "Ver detalles",
  cargando = false
}) => {
  const { colores } = useTema();
  const animacionEscala = useRef(new Animated.Value(0.95)).current;
  const animacionOpacidad = useRef(new Animated.Value(0)).current;
  const animacionPulso = useRef(new Animated.Value(0.4)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.spring(animacionEscala, { toValue: 1, friction: 9, tension: 50, useNativeDriver: true }),
      Animated.timing(animacionOpacidad, { toValue: 1, duration: 350, useNativeDriver: true })
    ]).start();
  }, []);

  useEffect(() => {
    if (cargando) {
      Animated.loop(
        Animated.sequence([
          Animated.timing(animacionPulso, { toValue: 1, duration: 800, useNativeDriver: true }),
          Animated.timing(animacionPulso, { toValue: 0.4, duration: 800, useNativeDriver: true })
        ])
      ).start();
    } else {
      animacionPulso.stopAnimation();
    }
  }, [cargando]);

  const obtenerConfiguracion = () => {
    if (tipo === 'deuda') {
      const tieneDeuda = monto > 0;
      return {
        // El icono advierte (Rojo o Verde)
        colorIcono: tieneDeuda ? (colores.error || '#e74c3c') : (colores.success || '#27ae60'),
        // La moneda es neutra para no generar ansiedad
        colorMoneda: tieneDeuda ? (colores.error || '#e74c3c') : (colores.success || '#27ae60'),
        // El botón invita a avanzar usando el color Primario (Azul/Marca)
        colorBotonTexto: colores.primario || '#007BFF', 
        colorBotonFondo: (colores.primario || '#007BFF') + '15',
        icono: tieneDeuda ? "alert-circle-outline" : "checkmark-circle-outline",
        mostrarBoton: tieneDeuda && onAccion,
        mensajePositivo: !tieneDeuda ? "¡Estás al día!" : null
      };
    }
    
    if (tipo === 'gasto') {
      const colorGasto = '#F59E0B'; 
      
      return {
        colorIcono: colorGasto,
        colorMoneda: colorGasto,
        // Estandarizamos el botón de acción al color primario de la app
        colorBotonTexto: colores.primario || '#007BFF', 
        colorBotonFondo: (colores.primario || '#007BFF') + '15',
        icono: "receipt-outline",
        mostrarBoton: !!onAccion,
        mensajePositivo: null
      };
    }

    return {
      colorIcono: colores.primario || '#007BFF',
      colorMoneda: colores.primario || '#007BFF',
      colorBotonTexto: colores.primario || '#007BFF',
      colorBotonFondo: (colores.primario || '#007BFF') + '15',
      icono: "cash-outline",
      mostrarBoton: !!onAccion,
      mensajePositivo: null
    };
  };

  const config = obtenerConfiguracion();
  const estilos = getEstilos(colores); 
  const montoFormateado = monto.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  return (
    <Animated.View style={[
      estilos.card, 
      { opacity: animacionOpacidad, transform: [{ scale: animacionEscala }] }
    ]}>
      
      <View style={estilos.header}>
        <View style={estilos.tituloContainer}>
          <View style={[estilos.iconoContainer, { backgroundColor: config.colorIcono + '15' }]}>
            <Icon name={config.icono} size={20} color={config.colorIcono} />
          </View>
          <Text style={estilos.titulo}>{titulo}</Text>
        </View>

        {(!cargando && config.mensajePositivo) && (
          <View style={[estilos.badgePositivo, { backgroundColor: config.colorIcono + '10' }]}>
            <Text style={[estilos.textoBadge, { color: config.colorIcono }]}>{config.mensajePositivo}</Text>
          </View>
        )}
      </View>
      
      <View style={estilos.body}>
        {cargando ? (
          <Animated.View style={[estilos.skeletonMonto, { opacity: animacionPulso }]} />
        ) : (
          <View style={estilos.montoContainer}>
            <Text style={[estilos.moneda, { color: config.colorMoneda }]}>{moneda}</Text>
            <Text style={estilos.monto}>{montoFormateado}</Text>
          </View>
        )}

        {(!cargando && config.mostrarBoton) && (
          <TouchableOpacity 
            style={[estilos.botonAccion, { backgroundColor: config.colorBotonFondo }]} 
            activeOpacity={0.7} 
            onPress={onAccion}
          >
            <Text style={[estilos.textoBoton, { color: config.colorBotonTexto }]}>{textoAccion}</Text>
            <Icon name="chevron-forward-outline" size={16} color={config.colorBotonTexto} />
          </TouchableOpacity>
        )}
      </View>
    </Animated.View>
  );
};

const getEstilos = (colores) => StyleSheet.create({
  card: { backgroundColor: colores.card || '#FFFFFF', borderRadius: 16, padding: 20, marginVertical: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.06, shadowRadius: 6, elevation: 3, borderWidth: 1, borderColor: colores.border || 'rgba(0,0,0,0.03)' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 },
  tituloContainer: { flexDirection: 'row', alignItems: 'center', flex: 1, paddingRight: 10 },
  
  iconoContainer: { padding: 8, borderRadius: 10, marginRight: 10 },
  titulo: { fontSize: 18, fontWeight: '700', color: colores.textPlaceholder || '#666', textTransform: 'uppercase', letterSpacing: 0.5, flexShrink: 1 },
  badgePositivo: { paddingVertical: 6, paddingHorizontal: 10, borderRadius: 20 },
  textoBadge: { fontSize: 12, fontWeight: 'bold' },
  body: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end' },
  montoContainer: { flexDirection: 'row', alignItems: 'baseline', flex: 1 },
  moneda: { fontSize: 18, fontWeight: '600', marginRight: 6 },
  monto: { fontSize: 32, fontWeight: '800', color: colores.text || '#222', letterSpacing: -0.5 },
  botonAccion: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, paddingHorizontal: 14, borderRadius: 12, marginLeft: 10 },
  textoBoton: { fontSize: 13, fontWeight: '700', marginRight: 4 },
  
  skeletonMonto: { height: 38, width: 180, backgroundColor: colores.border || '#E0E0E0', borderRadius: 8, marginVertical: 4 }
});

export default memo(ResumenFinancieroCard);