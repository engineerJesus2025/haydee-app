import React, { useState, useMemo } from 'react';
import { View, Text, Image, StyleSheet, TouchableOpacity, Alert } from 'react-native';
import { useTema } from '../hooks/useTema';
import Icon from 'react-native-vector-icons/Ionicons';

import ModalGeneral from './ModalGeneral';
import CustomBoton from './CustomBoton';
import MenuAcciones from './MenuAcciones';
import VisorImagenFullScreen from './VisorImagenFullScreen';
import { formatearFechaLegible, tiempoRelativo, formatoAyerHoy } from '../utils/dateUtils';

export default function ModalDetalles({
  visible,
  onClose,
  datos = {},
  titulo = 'Detalles',
  campos = [],
  mostrarImagen = true,
  esAdmin = false,
  onAprobar = null,
  onRechazar = null,
  onEditar = null,
  onEliminar = null,
  procesando = false,
  eliminando = false,
  labelEditar = 'Editar',
  labelEliminar = 'Eliminar',
}) {
  const { colores } = useTema();

  const [imagenExpandida, setImagenExpandida] = useState(false);
  const [imagenActiva, setImagenActiva] = useState(null);

  const estadoLower = datos?.estado?.toLowerCase();
  const tieneEstado = Boolean(estadoLower);
  const esPendiente = estadoLower === 'pendiente';
  const esProcesado = estadoLower === 'procesado';
  const esRechazado = estadoLower === 'rechazado';
  const bloqueado = procesando || eliminando;

  const accionesMenu = useMemo(() => {
    if (!datos?.id) return [];

    const items = [];
    const esGestionable = tieneEstado
      ? (esPendiente || (!esAdmin && esRechazado))
      : true;

    if (onEditar && esGestionable) {
      items.push({
        label: labelEditar,
        icono: 'create-outline',
        color: '#f39c12',
        onPress: () => onEditar(datos),
        disabled: bloqueado,
      });
    }

    if (onEliminar && esGestionable) {
      items.push({
        label: labelEliminar,
        icono: 'trash-outline',
        color: '#c0392b',
        onPress: () => onEliminar(datos),
        disabled: bloqueado || eliminando,
      });
    }

    // descomentar cuando exista generación de recibo:
    // if (esProcesado) {
    //   items.push({
    //     label: 'Generar recibo',
    //     icono: 'document-text-outline',
    //     color: '#27ae60',
    //     onPress: () => { /* TODO: sprint recibos */ },
    //   });
    // }

    return items;
  }, [datos?.id, datos?.estado, esAdmin, esPendiente, esRechazado, esProcesado, bloqueado, eliminando, onEditar, onEliminar, labelEditar, labelEliminar,]);

  // ---- Early return DESPUÉS de todos los hooks (regla de React) ----
  if (!datos || Object.keys(datos).length === 0) return null;

  const abrirVisor = (uri) => { setImagenActiva(uri); setImagenExpandida(true); };
  const cerrarVisor = () => { setImagenExpandida(false); setImagenActiva(null); };

  const handleAprobarClick = () => {
    if (!onAprobar) return;
      onAprobar(datos);
  };

  const mostrarAprobarRechazar = esAdmin && esPendiente;

  const footer = (
    <View style={{ width: '100%' }}>
      {mostrarAprobarRechazar ? (
        <View style={{ flexDirection: 'row', gap: 10, width: '100%' }}>
          <CustomBoton
            titulo="Rechazar"
            evento={() => onRechazar && onRechazar(datos)}
            icono={{ nombre: 'close-circle-outline', color: '#fff' }}
            estilos={{ backgroundColor: '#e74c3c', alignSelf: 'stretch' }}
            wrapperStyle={{ flex: 1 }}
            fuente={15}
            disabled={bloqueado}
            loading={procesando}
          />
          <CustomBoton
            titulo="Aprobar"
            evento={handleAprobarClick}
            icono={{ nombre: 'checkmark-circle-outline', color: '#fff' }}
            estilos={{ backgroundColor: '#27ae60', alignSelf: 'stretch' }}
            wrapperStyle={{ flex: 1 }}
            fuente={15}
            disabled={bloqueado}
            loading={procesando}
          />
        </View>
      ) : (
        <CustomBoton
          titulo="Cerrar"
          evento={onClose}
          icono={{ nombre: 'close-circle-outline', color: '#fff' }}
          estilos={{ backgroundColor: '#95a5a6', alignSelf: 'stretch' }}
          wrapperStyle={{ width: '100%' }}
          fuente={15}
        />
      )}
    </View>
  );

  const headerAcciones = accionesMenu.length > 0 ? (
    <MenuAcciones
      acciones={accionesMenu}
      titulo="Acciones del pago"
      icono="ellipsis-horizontal"
      colorIcono="#E1E1F7"
    />
  ) : null;

  return (
    <>
      <ModalGeneral
        visible={visible}
        onClose={onClose}
        titulo={titulo}
        iconoHeader={{ name: 'document-text-outline', color: '#E1E1F7' }}
        headerAcciones={headerAcciones}
        footer={footer}
        esFormulario={false}
      >

        <View style={[styles.reciboContainer, { backgroundColor: colores.card, borderColor: colores.border }]}>
          {campos.map((campo, index) => {
            const valor = datos[campo.key];
            const isEstado = campo.key === 'estado';
            const isMonto = campo.key === 'monto';

            let valorMostrar = valor;
            if (valor && campo.formato === 'fecha_legible') {
              valorMostrar = formatoAyerHoy(valor);
            }

            const requiereApilar = campo.key === 'fecha' || campo.key === 'observacion';

            return (
              <View
                key={index}
                style={[
                  styles.fila,
                  index !== campos.length - 1 && { borderBottomColor: colores.border, borderBottomWidth: 1 },
                  requiereApilar && { flexDirection: 'column', alignItems: 'flex-start' },
                ]}
              >
                <Text
                  style={[
                    styles.label,
                    { color: colores.textPlaceholder },
                    requiereApilar && { marginBottom: 6 },
                  ]}
                >
                  {campo.label}
                </Text>

                {isEstado ? (
                  <View style={[styles.badge, { backgroundColor: valorMostrar?.toLowerCase() === 'procesado' ? '#27ae6020' : valorMostrar?.toLowerCase() === 'rechazado' ? '#e74c3c20' : '#f39c1220' }]}>
                    <Text style={[styles.badgeText, { color: valorMostrar?.toLowerCase() === 'procesado' ? '#27ae60' : valorMostrar?.toLowerCase() === 'rechazado' ? '#e74c3c' : '#f39c12' }]}>
                      {valorMostrar || 'Desconocido'}
                    </Text>
                  </View>
                ) : isMonto ? (
                  <Text style={[styles.valor, styles.valorMonto, { color: colores.textTitle }]}>{valorMostrar || '---'}</Text>
                ) : (
                  <Text
                    style={[
                      styles.valor,
                      { color: colores.textTitle },
                      requiereApilar && { textAlign: 'left', width: '100%', flex: undefined },
                    ]}
                    numberOfLines={requiereApilar ? undefined : 2}
                  >
                    {valorMostrar || 'No especificado'}
                  </Text>
                )}
              </View>
            );
          })}
        </View>

        {datos?.detalles && datos.detalles.length > 0 && (
          <View style={styles.seccionDesglose}>
            <View style={styles.imagenHeader}>
              <Icon name="list-outline" size={18} color={colores.textPlaceholder} />
              <Text style={[styles.imagenTitulo, { color: colores.textPlaceholder }]}>
                Desglose de Operaciones ({datos.detalles.length})
              </Text>
            </View>

            {datos.detalles.map((det, index) => (
              <View key={det.id_detalle || index} style={[styles.tarjetaDetalle, { backgroundColor: colores.inputBackground, borderColor: colores.border }]}>
                <View style={styles.detalleFilaInterna}>
                  <Text style={[styles.textoMetodo, { color: colores.textTitle }]}>
                    {det.tipo_pago || det.metodo_pago}
                  </Text>
                  <Text style={[styles.textoMontoDetalle, { color: colores.textTitle }]}>
                    {det.monto}
                  </Text>
                </View>

                <Text style={[styles.textoInfoDetalle, { color: colores.textPlaceholder }]}>
                  Banco: {det.banco} | Ref: {det.referencia}
                </Text>

                {mostrarImagen && det.imagen && (
                  <TouchableOpacity
                    activeOpacity={0.8}
                    onPress={() => abrirVisor(det.imagen)}
                    style={styles.miniaturaContainer}
                  >
                    <Image source={{ uri: det.imagen }} style={styles.miniaturaImagen} resizeMode="cover" />
                    <View style={styles.overlayMiniatura}>
                      <Icon name="expand-outline" size={16} color="#fff" />
                    </View>
                  </TouchableOpacity>
                )}
              </View>
            ))}
          </View>
        )}
      </ModalGeneral>

      <VisorImagenFullScreen visible={imagenExpandida} onClose={cerrarVisor} imageUri={imagenActiva} />
    </>
  );
}

const styles = StyleSheet.create({
  // --- Banner de pago propio (NUEVO) ---
  bannerPropio: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderRadius: 10,
    borderWidth: 1,
    paddingVertical: 10,
    paddingHorizontal: 12,
    marginBottom: 12,
  },
  bannerPropioTexto: { flex: 1, fontSize: 13, fontWeight: '600' },

  reciboContainer: { borderRadius: 14, borderWidth: 1, overflow: 'hidden', marginBottom: 20, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 3, elevation: 2 },
  fila: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 14, paddingHorizontal: 16 },
  label: { fontSize: 14, flex: 1 },
  valor: { fontSize: 15, fontWeight: '500', flex: 1.5, textAlign: 'right' },
  valorMonto: { fontSize: 18, fontWeight: 'bold' },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  badgeText: { fontSize: 12, fontWeight: 'bold', textTransform: 'uppercase' },

  seccionDesglose: { marginTop: 5, paddingBottom: 10 },
  imagenHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 10, alignSelf: 'flex-start', marginLeft: 5 },
  imagenTitulo: { fontSize: 14, fontWeight: '600', marginLeft: 6, textTransform: 'uppercase', letterSpacing: 0.5 },
  tarjetaDetalle: { borderWidth: 1, borderRadius: 12, padding: 14, marginBottom: 12, overflow: 'hidden' },
  detalleFilaInterna: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
  textoMetodo: { fontSize: 15, fontWeight: 'bold' },
  textoMontoDetalle: { fontSize: 16, fontWeight: 'bold' },
  textoInfoDetalle: { fontSize: 13 },
  miniaturaContainer: { height: 120, width: '100%', borderRadius: 8, overflow: 'hidden', marginTop: 12, backgroundColor: '#1e1e1e' },
  miniaturaImagen: { width: '100%', height: '100%' },
  overlayMiniatura: { position: 'absolute', bottom: 8, right: 8, backgroundColor: 'rgba(0,0,0,0.6)', padding: 6, borderRadius: 20 },
});