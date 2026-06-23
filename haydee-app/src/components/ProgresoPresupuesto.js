import React, { useEffect, useRef, memo } from 'react';
import { View, Text, StyleSheet, Animated } from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import { useTema } from '../hooks/useTema';
import { useProgresoAnimado } from '../hooks/useProgresoAnimado';

const ProgresoPresupuesto = ({ 
  gastado = 0, 
  total = 0, 
  moneda = "Bs.",
  titulo = "Presupuesto del mes",
  icono = "wallet-outline",
  cargando = false
}) => {
  const { colores } = useTema();
  const estilos = getEstilos(colores);

  const porcentaje = (total > 0 && !cargando) ? Math.min((gastado / total) * 100, 100) : 0;
  const disponible = cargando ? 0 : total - gastado;

  const { anchoAnimado } = useProgresoAnimado(porcentaje);
  const animacionPulso = useRef(new Animated.Value(0.4)).current;

  // Control de la animación de los esqueletos
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

  // Colores y Alertas (solo se calculan si hay datos)
  let colorBarra = colores.success || '#27ae60';
  let mensajeEstado = "Saludable";
  let colorEstado = colores.success || '#27ae60';

  if (porcentaje > 90) {
    colorBarra = colores.error || '#e74c3c';
    mensajeEstado = "Crítico";
    colorEstado = colores.error || '#e74c3c';
  } else if (porcentaje > 70) {
    colorBarra = colores.warning || '#f39c12';
    mensajeEstado = "Precaución";
    colorEstado = colores.warning || '#f39c12';
  }

  const formatearMonto = (valor) => valor.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  return (
    <View style={estilos.container}>
      
      {/* CABECERA */}
      <View style={estilos.header}>
        <View style={estilos.headerIzquierda}>
          <View style={estilos.iconoContainer}>
            <Icon name={icono} size={20} color={colores.primario} />
          </View>
          <Text style={estilos.titulo} numberOfLines={2}>{titulo}</Text>
        </View>
        
        {cargando ? (
          <Animated.View style={[estilos.skeletonBadge, { opacity: animacionPulso }]} />
        ) : (
          <View style={[estilos.badge, { backgroundColor: colorEstado + '20' }]}>
            <Text style={[estilos.badgeText, { color: colorEstado }]}>{mensajeEstado}</Text>
          </View>
        )}
      </View>

      {/* DESGLOSE DE MONTOS */}
      <View style={estilos.montosContainer}>
        <View style={estilos.montoColumna}>
          <Text style={estilos.labelSecundario}>Gastado</Text>
          <View style={estilos.filaMontoSkel}>
            <Text style={estilos.monedaSkel}>{moneda}</Text>
            {cargando ? (
               <Animated.View style={[estilos.skeletonMonto, { opacity: animacionPulso, width: 90 }]} />
            ) : (
              <Text style={estilos.montoPrincipal} numberOfLines={1} adjustsFontSizeToFit>
                {formatearMonto(gastado)}
              </Text>
            )}
          </View>
        </View>
        
        <View style={[estilos.montoColumna, { alignItems: 'flex-end' }]}>
          <Text style={estilos.labelSecundario}>Disponible</Text>
          <View style={estilos.filaMontoSkel}>
            <Text style={[estilos.monedaSkel, { color: cargando ? colores.text : (disponible < 0 ? colorEstado : colores.text) }]}>
              {moneda}
            </Text>
            {cargando ? (
               <Animated.View style={[estilos.skeletonMonto, { opacity: animacionPulso, width: 80 }]} />
            ) : (
              <Text style={[estilos.montoPrincipal, { color: disponible < 0 ? colorEstado : colores.text }]} numberOfLines={1} adjustsFontSizeToFit>
                {formatearMonto(Math.max(disponible, 0))}
              </Text>
            )}
          </View>
        </View>
      </View>

      {/* BARRA DE PROGRESO */}
      <View style={estilos.barraFondo}>
        {cargando ? (
          <Animated.View style={[estilos.skeletonBarra, { opacity: animacionPulso }]} />
        ) : (
          <Animated.View style={[estilos.barraProgreso, { width: anchoAnimado, backgroundColor: colorBarra }]} />
        )}
      </View>
      
      {/* FOOTER */}
      <View style={estilos.footer}>
        <View style={estilos.footerBloque}>
          <Text style={estilos.textoFooter}>De un total de <Text style={{ fontWeight: 'bold' }}>{moneda}</Text> </Text>
          {cargando ? (
            <Animated.View style={[estilos.skeletonTextoPeque, { opacity: animacionPulso, width: 70 }]} />
          ) : (
            <Text style={[estilos.textoFooter, { fontWeight: 'bold' }]}>{formatearMonto(total)}</Text>
          )}
        </View>

        <View style={estilos.footerBloque}>
          {cargando ? (
            <Animated.View style={[estilos.skeletonTextoPeque, { opacity: animacionPulso, width: 25, marginRight: 4 }]} />
          ) : (
            <Text style={estilos.textoPorcentaje}>{porcentaje.toFixed(0)}</Text>
          )}
          <Text style={estilos.textoPorcentaje}>% consumido</Text>
        </View>
      </View>
    </View>
  );
};

const getEstilos = (colores) => StyleSheet.create({
  container: { backgroundColor: colores.card || '#FFFFFF', borderRadius: 16, padding: 20, marginVertical: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.06, shadowRadius: 6, elevation: 3, borderWidth: 1, borderColor: colores.border || 'rgba(0,0,0,0.03)' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 },
  headerIzquierda: { flexDirection: 'row', alignItems: 'center', flex: 1, paddingRight: 10 },
  iconoContainer: { backgroundColor: colores.primario + '15', padding: 8, borderRadius: 10, marginRight: 12 },
  titulo: { fontSize: 15, fontWeight: '700', color: colores.textTitle || '#333', flexShrink: 1 },
  badge: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 12, flexShrink: 0 },
  badgeText: { fontSize: 12, fontWeight: '700' },
  montosContainer: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  montoColumna: { flex: 1 },
  labelSecundario: { fontSize: 13, color: colores.textPlaceholder || '#888', marginBottom: 2 },
  
  filaMontoSkel: { flexDirection: 'row', alignItems: 'center' },
  monedaSkel: { fontSize: 16, fontWeight: '600', color: colores.text, marginRight: 4 },
  montoPrincipal: { fontSize: 22, fontWeight: '800', color: colores.text || '#222', letterSpacing: -0.5 },
  
  barraFondo: { height: 8, backgroundColor: colores.inputBackground || '#F0F0F0', borderRadius: 4, overflow: 'hidden', marginBottom: 10 },
  barraProgreso: { height: '100%', borderRadius: 4 },
  
  footer: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', rowGap: 4 },
  footerBloque: { flexDirection: 'row', alignItems: 'center' },
  textoFooter: { fontSize: 13, color: colores.textPlaceholder || '#888' },
  textoPorcentaje: { fontSize: 13, fontWeight: '700', color: colores.textPlaceholder || '#888' },
  
  skeletonBadge: { 
    width: 75, height: 26, borderRadius: 12, 
    backgroundColor: colores.border || '#E0E0E0' 
  },
  skeletonMonto: { 
    height: 22, borderRadius: 6, 
    backgroundColor: colores.border || '#E0E0E0', 
    marginTop: 2 
  },
  skeletonBarra: { 
    width: '100%', height: '100%', 
    backgroundColor: colores.border || '#E0E0E0', 
    borderRadius: 4 
  },
  skeletonTextoPeque: { 
    height: 14, borderRadius: 4, 
    backgroundColor: colores.border || '#E0E0E0' 
  }
});

export default memo(ProgresoPresupuesto);