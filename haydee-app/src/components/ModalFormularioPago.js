import React, { useRef } from 'react';
import { View, ScrollView, TouchableOpacity, Text, ActivityIndicator, TextInput  } from 'react-native';
import { useTema } from '../hooks/useTema';
import { useFormularioPago } from '../hooks/useFormularioPago';
import useValidaciones from '../hooks/useValidaciones';
import { Controller } from 'react-hook-form';

import ModalGeneral from './ModalGeneral';
import CampoFormulario from './CampoFormulario';
import ErrorFormulario from './ErrorFormulario';
import LabelInput from './LabelInput';
import CustomBoton from './CustomBoton';
import MostrarVistaPrevia from './MostrarVistaPrevia';
import SelectorDesplegable from './SelectorDesplegable';
import TasaDolarCard from './TasaDolarCard';
import { formatearMesAnio } from '../utils/dateUtils';

export default function ModalFormularioPago({ visible, onClose, modo = 'crear', pagoAEditar = null }) {
  const { colores } = useTema();
  const montoRef = useRef(null);
  const referenciaRef = useRef(null);
  const esEdicion = modo === 'editar';

  const {
    control, errors, comprobanteUri, isSubmitting, isValid,
    handleSubmit, handleImagePick, removeImage, onSubmit,
    bancosDisponibles, cuentasDisponibles,
    apartamentosDisponibles, mesesPendientes,
    requiereBanco, requiereCuenta,
    esAdmin, tasaDolar, equivalenteDolares, apartamentoSeleccionado,
    cargandoMensualidades, onError, pendienteMensualidadSeleccionada,
    validarMontoContraDeuda,
    escanearComprobante, escanerCargando, OCR_HABILITADO,
  } = useFormularioPago(onClose, { modo, pagoAEditar, visible });

  const validaciones = useValidaciones();

  const BotonesFooter = (
    <>
      <CustomBoton
        titulo="Cancelar"
        evento={onClose}
        icono={{ nombre: 'close-circle-outline', color: '#fff' }}
        estilos={{ backgroundColor: '#95a5a6' }}
        fuente={16}
      />
      <CustomBoton
        titulo={esEdicion ? 'Guardar Cambios' : 'Enviar Pago'}
        evento={handleSubmit(onSubmit, onError)}
        icono={{ nombre: esEdicion ? 'save-outline' : 'send-outline', color: '#fff' }}
        disabled={isSubmitting}
        estilos={{ backgroundColor: esEdicion ? '#f39c12' : '#27ae60', opacity: !isSubmitting ? 1 : 0.6 }}
        fuente={16}
        loading={isSubmitting}
      />
    </>
  );

  return (
    <ModalGeneral
      visible={visible}
      onClose={onClose}
      titulo={esEdicion ? 'Editar Pago' : 'Registrar Pago'}
      iconoHeader={{ name: 'cash-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={true}
    >
      {/* 1. ESTADO */}
      <LabelInput titulo="Estado del Pago" icono={{ nombre: 'shield-checkmark-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="estado"
        rules={validaciones.requeridoSimple('Selecciona un estado')}
        render={({ field: { onChange, value } }) => (
          <View style={{ flexDirection: 'row', flexWrap: 'wrap', marginBottom: 15, gap: 10 }}>
            {['PENDIENTE', 'PROCESADO', 'RECHAZADO'].map((opcion) => {
              const isSelected = value === opcion;
              let colorBorde = isSelected ? (colores.primario || '#ffc107') : colores.border;
              if (isSelected && opcion === 'PROCESADO') colorBorde = '#27ae60';
              if (isSelected && opcion === 'RECHAZADO') colorBorde = '#e74c3c';
              return (
                <TouchableOpacity
                  key={opcion}
                  activeOpacity={0.7}
                  onPress={() => onChange(opcion)}
                  style={{
                    paddingHorizontal: 14, paddingVertical: 10, borderRadius: 20, borderWidth: 1.5,
                    borderColor: colorBorde,
                    backgroundColor: isSelected ? colorBorde + '15' : colores.card,
                  }}
                >
                  <Text style={{ color: isSelected ? colorBorde : colores.textPlaceholder, fontWeight: isSelected ? 'bold' : '500' }}>
                    {opcion.charAt(0).toUpperCase() + opcion.toLowerCase().slice(1)}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        )}
      />
      <ErrorFormulario error={errors.estado} />

      {/* 2. APARTAMENTO */}
      <LabelInput titulo="Apartamento" icono={{ nombre: 'business-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="apartamento_id"
        rules={{ required: 'Debes seleccionar un apartamento' }}
        render={({ field: { onChange, value } }) => {
          const opcionesApartamentos = apartamentosDisponibles.map(apt => ({
            label: `Apartamento Nº ${apt.nro_apartamento}`,
            value: apt.id_apartamento
          }));
          return (
            <SelectorDesplegable
              opciones={opcionesApartamentos}
              valorSeleccionado={value}
              onSelect={onChange}
              placeholder="Seleccione el apartamento..."
              icono="home-outline"
              deshabilitado={esEdicion}
            />
          );
        }}
      />
      <ErrorFormulario error={errors.apartamento_id} />

      {/* 3. MENSUALIDAD */}
      <LabelInput titulo="Mensualidad a pagar" icono={{ nombre: 'calendar-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="mensualidad_id"
        rules={{ required: 'Seleccione el mes a pagar' }}
        render={({ field: { onChange, value } }) => {
          const opcionesMensualidades = mesesPendientes.map(item => {
            const idResuelto = item.id_mensualidad || item.id;
            let etiquetaResuelta = 'Mes no definido';
            if (item.mes) {
              const mesString = String(item.mes);
              if (mesString.includes(' ')) {
                const [mesNumero, anioNumero] = mesString.split(' ');
                etiquetaResuelta = formatearMesAnio(mesNumero, anioNumero);
              } else if (isNaN(mesString)) {
                etiquetaResuelta = item.mes;
              }
            } else if (item.mensualidad) {
              etiquetaResuelta = item.mensualidad;
            }
            return { label: etiquetaResuelta, value: idResuelto };
          });
          return (
            <SelectorDesplegable
              opciones={opcionesMensualidades}
              valorSeleccionado={value}
              onSelect={onChange}
              placeholder={cargandoMensualidades ? 'Cargando deudas...' : 'Seleccione el mes...'}
              icono="calendar"
              deshabilitado={esEdicion || cargandoMensualidades || opcionesMensualidades.length === 0}
            />
          );
        }}
      />
      <ErrorFormulario error={errors.mensualidad_id} />

      {/* 4. MÉTODO DE PAGO */}
      <LabelInput titulo="Método de Pago" icono={{ nombre: 'options-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="tipo_pago"
        rules={validaciones.requeridoSimple('Seleccione un metodo de pago')}
        render={({ field: { onChange, value } }) => (
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 15 }}>
            {['EFECTIVO', 'PAGO MOVIL', 'TRANSFERENCIA'].map((opcion) => {
              const isSelected = value === opcion;
              return (
                <TouchableOpacity
                  key={opcion}
                  activeOpacity={0.7}
                  onPress={() => onChange(opcion)}
                  style={{
                    paddingHorizontal: 14, paddingVertical: 10, borderRadius: 20, borderWidth: 1.5, marginRight: 8,
                    borderColor: isSelected ? (colores.primario || '#3498db') : colores.border,
                    backgroundColor: isSelected ? (colores.primario + '15' || '#eaf4fc') : colores.card,
                  }}
                >
                  <Text style={{ color: isSelected ? colores.primario : colores.textPlaceholder, fontWeight: isSelected ? 'bold' : '500' }}>
                    {opcion.charAt(0).toUpperCase() + opcion.toLowerCase().slice(1)}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </ScrollView>
        )}
      />

      {/* 5. COMPROBANTE (antes que los datos que puede prellenar) */}
      {requiereBanco && (
        <>
          <LabelInput titulo="Comprobante" icono={{ nombre: 'image-outline', color: '#3498db' }} />
          <ErrorFormulario error={errors.imagen} />

          {/* ── SIN IMAGEN: un único botón full-width ── */}
          {!comprobanteUri ? (
            <CustomBoton
              titulo="Subir Foto"
              evento={handleImagePick}
              icono={{ nombre: 'camera-outline', color: '#fff' }}
              estilos={{ backgroundColor: '#3498db', alignSelf: 'stretch' }}
              wrapperStyle={{ width: '100%', marginBottom: 15 }}
              disabled={isSubmitting}
            />
          ) : (
            /* ── CON IMAGEN: dos botones, sin wrap ── */
            <View style={{ flexDirection: 'row', gap: 10, marginBottom: 10 }}>
              <CustomBoton
                titulo="Cambiar"
                evento={handleImagePick}
                icono={{ nombre: 'camera-outline', color: '#fff' }}
                estilos={{ backgroundColor: '#3498db', alignSelf: 'stretch' }}
                wrapperStyle={{ flex: 1 }}
                disabled={isSubmitting || escanerCargando}
              />
              {OCR_HABILITADO && (
                <CustomBoton
                  titulo={escanerCargando ? 'Leyendo…' : 'Escanear'}
                  evento={() => escanearComprobante(comprobanteUri)}
                  icono={{ nombre: 'scan-outline', color: '#fff' }}
                  estilos={{ backgroundColor: '#8e44ad', alignSelf: 'stretch' }}
                  wrapperStyle={{ flex: 1 }}
                  loading={escanerCargando}
                  disabled={escanerCargando || isSubmitting}
                />
              )}
            </View>
          )}

          {/* Vista previa con X overlay para quitar */}
          {comprobanteUri && (
            <MostrarVistaPrevia
              titulo="Recibo adjunto:"
              imageUri={comprobanteUri}
              onRemove={escanerCargando ? null : removeImage}
              escaneando={escanerCargando}
            />
          )}

          {/* Hint compacto, sin bloque de color */}
          {OCR_HABILITADO && comprobanteUri && !escanerCargando && (
            <View style={{
              flexDirection: 'row',
              alignItems: 'center',
              gap: 6,
              marginTop: -5,
              marginBottom: 15,
              paddingHorizontal: 4,
            }}>
              <Text style={{ color: '#8e44ad', fontSize: 12, fontWeight: '500', flex: 1 }}>
                💡 Toca "Escanear" para autocompletar monto, banco y referencia.
              </Text>
            </View>
          )}
        </>
      )}

      {/* 6. TASA Y EQUIVALENTE */}
      <TasaDolarCard
        tasa={tasaDolar}
        equivalente={equivalenteDolares}
        moneda="Bs."
      />

      {/* 7. MONTO */}
      <CampoFormulario
        tituloLabel="Monto (Bs)"
        iconoLabel={{ nombre: 'cash-outline', color: '#3498db' }}
        control={control}
        name="monto"
        rules={{ ...validaciones.monto, validate: validarMontoContraDeuda }}
        iconoInput={{ nombre: 'cash', color: '#95a5a6' }}
        error={errors.monto}
        placeholder={pendienteMensualidadSeleccionada
          ? `Máx: ${pendienteMensualidadSeleccionada.toFixed(2)}`
          : 'Ejm: 150.50'}
        keyboardType="decimal-pad"
        inputRef={montoRef}
      />

      {/* 8. SECCIÓN BANCARIA (banco → cuenta → referencia) */}
      {requiereBanco && (
        <>
          <LabelInput titulo="Banco de Origen" icono={{ nombre: 'business-outline', color: '#3498db' }} />
          <Controller
            control={control}
            name="banco_id"
            rules={validaciones.requeridoSimple('Selecciona un banco')}
            render={({ field: { onChange, value } }) => (
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 15 }}>
                {bancosDisponibles.map((banco) => {
                  const isSelected = value === banco.id_banco;
                  return (
                    <TouchableOpacity
                      key={banco.id_banco}
                      activeOpacity={0.7}
                      onPress={() => onChange(banco.id_banco)}
                      style={{
                        paddingHorizontal: 14, paddingVertical: 10, borderRadius: 20, borderWidth: 1.5, marginRight: 8,
                        borderColor: isSelected ? (colores.primario || '#3498db') : colores.border,
                        backgroundColor: isSelected ? (colores.primario + '15' || '#eaf4fc') : colores.card,
                      }}
                    >
                      <Text style={{ color: isSelected ? colores.primario : colores.textPlaceholder, fontWeight: isSelected ? 'bold' : '500' }}>
                        {banco.nombre_banco}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </ScrollView>
            )}
          />
          <ErrorFormulario error={errors.banco_id} />

          {/* CUENTA DEL CONDOMINIO */}
          <LabelInput titulo="Cuenta del Condominio" icono={{ nombre: 'wallet-outline', color: '#3498db' }} />
          <Controller
            control={control}
            name="cuenta_id"
            rules={validaciones.requeridoSimple('Selecciona la cuenta del condominio')}
            render={({ field: { onChange, value } }) => {
              const opcionesCuentas = cuentasDisponibles.map(cuenta => {
                const banco = bancosDisponibles.find(b => String(b.id_banco) === String(cuenta.banco_id));
                const nombreBanco = cuenta.nombre_banco || banco?.nombre_banco || 'Banco';
                return {
                  label: `${nombreBanco} — ${cuenta.numero_cuenta}`,
                  value: cuenta.id_cuenta,
                };
              });
              return (
                <SelectorDesplegable
                  opciones={opcionesCuentas}
                  valorSeleccionado={value}
                  onSelect={onChange}
                  placeholder={opcionesCuentas.length === 0 ? 'Sin cuentas registradas' : 'Seleccione la cuenta receptora...'}
                  icono="wallet-outline"
                  deshabilitado={opcionesCuentas.length === 0}
                />
              );
            }}
          />
          <ErrorFormulario error={errors.cuenta_id} />

          <CampoFormulario
            tituloLabel="Nro. Referencia"
            iconoLabel={{ nombre: 'document-text-outline', color: '#3498db' }}
            control={control}
            name="referencia"
            rules={validaciones.referencia}
            iconoInput={{ nombre: 'barcode', color: '#95a5a6' }}
            error={errors.referencia}
            placeholder="Ejm: 004589234"
            keyboardType="numeric"
            inputRef={referenciaRef}
          />
        </>
      )}

      {/* Observación */}
      <LabelInput
        titulo="Observación (opcional)"
        icono={{ nombre: 'chatbox-outline', color: '#3498db' }}
      />
      <Controller
        control={control}
        name="observacion"
        rules={{
          maxLength: { value: 500, message: 'Máximo 500 caracteres' },
        }}
        render={({ field: { onChange, value } }) => (
          <TextInput
            value={value || ''}
            onChangeText={onChange}
            placeholder="Ej: Pago parcial del mes, abono a deuda anterior, etc."
            placeholderTextColor={colores.textPlaceholder}
            multiline
            numberOfLines={4}
            maxLength={500}
            style={{
              backgroundColor: colores.inputBackground || colores.card,
              color: colores.text,
              borderRadius: 10,
              borderWidth: 1,
              borderColor: errors.observacion ? '#e74c3c' : colores.border,
              paddingHorizontal: 12,
              paddingVertical: 10,
              minHeight: 90,
              textAlignVertical: 'top',
              marginBottom: 8,
              fontSize: 14,
            }}
          />
        )}
      />
      <ErrorFormulario error={errors.observacion} />

    </ModalGeneral>
  );
}