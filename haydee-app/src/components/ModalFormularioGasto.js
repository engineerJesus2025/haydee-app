import React, { useRef } from 'react';
import { View, Text, TouchableOpacity, ScrollView } from 'react-native';
import { useTema } from '../hooks/useTema';
import { useFormularioGasto } from '../hooks/useFormularioGasto';
import useValidaciones from '../hooks/useValidaciones';
import { Controller } from 'react-hook-form';

import ModalGeneral from './ModalGeneral';
import CampoFormulario from './CampoFormulario';
import CustomBoton from './CustomBoton';
import MostrarVistaPrevia from './MostrarVistaPrevia';
import LabelInput from './LabelInput';
import ErrorFormulario from './ErrorFormulario';
import SelectorDesplegable from './SelectorDesplegable';
import TasaDolarCard from './TasaDolarCard';

export default function ModalFormularioGasto({ visible, onClose, modo = 'crear', gastoAEditar = null }) {
  const { colores } = useTema();
  const esEdicion = modo === 'editar';

  const montoRef = useRef(null);
  const descripcionRef = useRef(null);
  const referenciaRef = useRef(null);

  const {
    control, errors, comprobanteUri, isSubmitting, canSubmit,
    handleSubmit, handleImagePick, removeImage, onSubmit, handleCancel,
    catalogos, requiereBanco, onError,
    opcionesTiposGasto,
    opcionesConceptos,
    opcionesProveedores,
    opcionesCuentas,
    handleTipoGastoChange,
    escanearComprobante,
    escanerCargando,
    OCR_HABILITADO,
    tasaDolar,
    equivalenteDolares,
  } = useFormularioGasto(onClose, { modo, gastoAEditar, visible });

  const validaciones = useValidaciones();

  const BotonesFooter = (
    <>
      <CustomBoton
        titulo="Cancelar"
        evento={handleCancel}
        icono={{ nombre: 'close-circle-outline', color: '#fff' }}
        estilos={{ backgroundColor: '#95a5a6', flex: 1 }}
        fuente={16}
      />
      <CustomBoton
        titulo={esEdicion ? 'Guardar Cambios' : 'Registrar'}
        evento={handleSubmit(onSubmit, onError)}
        icono={{ nombre: esEdicion ? 'save-outline' : 'add-circle-outline', color: '#fff' }}
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
      onClose={handleCancel}
      titulo={esEdicion ? 'Editar Gasto' : 'Registrar Gasto'}
      iconoHeader={{ name: 'cart-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={true}
    >
      {/* CLASIFICACIÓN FIJO / VARIABLE */}
      <LabelInput titulo="Clasificación" icono={{ nombre: 'pricetag-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="clasificacion"
        rules={validaciones.requeridoSimple('Selecciona un tipo de gasto')}
        render={({ field: { onChange, value } }) => (
          <View style={{ flexDirection: 'row', marginBottom: 15, gap: 10 }}>
            {['FIJO', 'VARIABLE'].map((opcion) => {
              const isSelected = value === opcion;
              return (
                <TouchableOpacity
                  key={opcion}
                  activeOpacity={0.7}
                  onPress={() => onChange(opcion)}
                  style={{
                    flex: 1, paddingVertical: 12, borderRadius: 12, borderWidth: 1.5, alignItems: 'center',
                    borderColor: isSelected ? (colores.primario || '#3498db') : colores.border,
                    backgroundColor: isSelected ? (colores.primario + '15' || '#eaf4fc') : colores.card,
                  }}
                >
                  <Text style={{
                    color: isSelected ? (colores.primario || '#3498db') : colores.textPlaceholder,
                    fontWeight: isSelected ? 'bold' : '500',
                  }}>
                    {opcion.charAt(0).toUpperCase() + opcion.toLowerCase().slice(1)}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        )}
      />
      <ErrorFormulario error={errors.clasificacion} />

      {/* TIPO DE GASTO */}
      <LabelInput titulo="Tipo de Gasto" icono={{ nombre: 'pricetag-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="tipo_gasto_id"
        rules={{ required: 'Seleccione un tipo' }}
        render={({ field }) => (
          <SelectorDesplegable
            opciones={opcionesTiposGasto}
            valorSeleccionado={field.value}
            onSelect={handleTipoGastoChange}
            placeholder="Seleccione el tipo..."
            icono="pricetag-outline"
          />
        )}
      />
      <ErrorFormulario error={errors.tipo_gasto_id} />

      {/* CONCEPTO */}
      <LabelInput titulo="Concepto" icono={{ nombre: 'list-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="concepto_id"
        rules={{ required: 'Seleccione un concepto' }}
        render={({ field }) => (
          <SelectorDesplegable
            opciones={opcionesConceptos}
            valorSeleccionado={field.value}
            onSelect={field.onChange}
            placeholder={
              opcionesConceptos.length === 0
                ? 'Sin conceptos para este tipo'
                : 'Seleccione el concepto...'
            }
            icono="list-outline"
            deshabilitado={opcionesConceptos.length === 0}
          />
        )}
      />
      <ErrorFormulario error={errors.concepto_id} />

      {/* PROVEEDOR */}
      <LabelInput titulo="Proveedor / Beneficiario" icono={{ nombre: 'business-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="proveedor_id"
        rules={{ required: 'Seleccione un proveedor' }}
        render={({ field }) => (
          <SelectorDesplegable
            opciones={opcionesProveedores}
            valorSeleccionado={field.value}
            onSelect={field.onChange}
            placeholder="Seleccione el proveedor..."
            icono="person-outline"
          />
        )}
      />
      <ErrorFormulario error={errors.proveedor_id} />

      {/* MÉTODO DE PAGO */}
      <LabelInput titulo="Método de Pago" icono={{ nombre: 'wallet-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="metodo_pago"
        rules={validaciones.requeridoSimple('Selecciona un metodo de pago')}
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

      {/* COMPROBANTE + ESCANEAR */}
      {requiereBanco && (
        <>
          <LabelInput titulo="Comprobante Bancario" icono={{ nombre: 'image-outline', color: '#3498db' }} />
          <ErrorFormulario error={errors.imagen} />

          {!comprobanteUri ? (
            <CustomBoton
              titulo="Subir Captura"
              evento={handleImagePick}
              icono={{ nombre: 'camera-outline', color: '#fff' }}
              estilos={{ backgroundColor: '#3498db', alignSelf: 'stretch' }}
              wrapperStyle={{ width: '100%', marginBottom: 15 }}
              disabled={isSubmitting}
            />
          ) : (
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

          {comprobanteUri && (
            <MostrarVistaPrevia
              titulo="Recibo adjunto:"
              imageUri={comprobanteUri}
              onRemove={escanerCargando ? null : removeImage}
              escaneando={escanerCargando}
            />
          )}

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
                💡 Toca "Escanear" para autocompletar monto, cuenta y referencia.
              </Text>
            </View>
          )}
        </>
      )}

      {/* TASA */}
      <TasaDolarCard
        tasa={tasaDolar}
        equivalente={equivalenteDolares}
        moneda="Bs."
      />

      {/* MONTO */}
      <CampoFormulario
        tituloLabel="Monto Total (Bs)"
        iconoLabel={{ nombre: 'cash-outline', color: '#3498db' }}
        control={control}
        name="monto"
        rules={validaciones.monto}
        iconoInput={{ nombre: 'cash', color: '#95a5a6' }}
        error={errors.monto}
        placeholder="Ejm: 250.00"
        keyboardType="decimal-pad"
        inputRef={montoRef}
      />

      {/* CUENTA y REFERENCIA */}
      {requiereBanco && (
        <>
          <LabelInput titulo="Cuenta del Condominio" icono={{ nombre: 'wallet-outline', color: '#3498db' }} />
          <Controller
            control={control}
            name="cuenta_id"
            rules={validaciones.requeridoSimple('Selecciona una cuenta')}
            render={({ field }) => (
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 15 }}>
                {(catalogos.cuentas || []).map((cta) => {
                  const isSelected = field.value === cta.id_cuenta;
                  return (
                    <TouchableOpacity
                      key={cta.id_cuenta}
                      activeOpacity={0.7}
                      onPress={() => field.onChange(cta.id_cuenta)}
                      style={{
                        paddingHorizontal: 14, paddingVertical: 10, borderRadius: 20, borderWidth: 1.5, marginRight: 8,
                        borderColor: isSelected ? (colores.primario || '#3498db') : colores.border,
                        backgroundColor: isSelected ? (colores.primario + '15' || '#eaf4fc') : colores.card,
                      }}
                    >
                      <Text style={{ color: isSelected ? colores.primario : colores.textPlaceholder, fontWeight: isSelected ? 'bold' : '500' }}>
                        {cta.nombre_banco} · {cta.numero_cuenta}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </ScrollView>
            )}
          />
          <ErrorFormulario error={errors.cuenta_id} />

          <CampoFormulario
            tituloLabel="Nro. de Referencia"
            iconoLabel={{ nombre: 'barcode-outline', color: '#3498db' }}
            control={control}
            name="referencia"
            rules={validaciones.referencia}
            iconoInput={{ nombre: 'keypad', color: '#95a5a6' }}
            error={errors.referencia}
            placeholder="Ejm: 00123456"
            keyboardType="numeric"
            inputRef={referenciaRef}
          />
        </>
      )}

      {/* DESCRIPCIÓN */}
      <CampoFormulario
        tituloLabel="Descripción del Gasto"
        iconoLabel={{ nombre: 'document-text-outline', color: '#3498db' }}
        control={control}
        name="descripcion_gasto"
        rules={validaciones.descripcionGasto}
        iconoInput={{ nombre: 'reader-outline', color: '#95a5a6' }}
        error={errors.descripcion_gasto}
        placeholder="Motivo detallado..."
        inputRef={descripcionRef}
      />

    </ModalGeneral>
  );
}