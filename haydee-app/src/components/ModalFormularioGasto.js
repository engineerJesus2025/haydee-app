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

export default function ModalFormularioGasto({ visible, onClose }) {
  const { colores } = useTema();
  
  const montoRef = useRef(null);
  const descripcionRef = useRef(null);
  const referenciaRef = useRef(null);

  const {
    control, errors, comprobanteUri, isSubmitting, canSubmit,
    handleSubmit, handleImagePick, removeImage, onSubmit, handleCancel,
    catalogos, requiereBanco, onError
  } = useFormularioGasto(onClose);

  const validaciones = useValidaciones();

  const BotonesFooter = (
    <>
      <CustomBoton titulo="Cancelar" evento={handleCancel} icono={{ nombre: 'close-circle-outline', color: '#fff' }} estilos={{ backgroundColor: '#95a5a6', flex: 1 }} fuente={16} />
      <CustomBoton titulo="Registrar" 
        evento={handleSubmit(onSubmit, onError)}
        icono={{ nombre: 'save-outline', color: '#fff' }} 
        disabled={isSubmitting} 
        estilos={{ backgroundColor: '#27ae60', opacity: !isSubmitting ? 1 : 0.6 }} 
        fuente={16} 
        loading={isSubmitting}
      />
    </>
  );

  return (
    <ModalGeneral
      visible={visible}
      onClose={handleCancel}
      titulo="Registrar Gasto"
      iconoHeader={{ name: 'cart-outline', color: '#E1E1F7' }}
      footer={BotonesFooter}
      esFormulario={true}
    >
      <LabelInput titulo="Tipo de Gasto" icono={{ nombre: 'pricetag-outline', color: '#3498db' }} />
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

      {/* --- SELECTOR DE TIPO DE GASTO --- */}
      <LabelInput titulo="Tipo de Gasto" icono={{ nombre: 'pricetag-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="tipo_gasto_id"
        rules={{ required: 'Seleccione un tipo' }}
        render={({ field: { onChange, value } }) => {
          
          const opcionesTipoGasto = (catalogos.tipos_gasto || []).map(tipo => ({
            label: tipo.nombre_tipo_gasto, 
            value: tipo.id_tipo_gasto
          }));

          return (
            <SelectorDesplegable
              opciones={opcionesTipoGasto}
              valorSeleccionado={value}
              onSelect={onChange}
              placeholder="Seleccione la categoría..."
              icono="pricetag-outline"
            />
          );
        }}
      />
      <ErrorFormulario error={errors.tipo_gasto_id} />


      {/* --- SELECTOR DE PROVEEDOR --- */}
      <LabelInput titulo="Proveedor / Beneficiario" icono={{ nombre: 'business-outline', color: '#3498db' }} />
      <Controller
        control={control}
        name="proveedor_id"
        rules={{ required: 'Seleccione un proveedor' }}
        render={({ field: { onChange, value } }) => {
          
          const opcionesProveedores = (catalogos.proveedores || []).map(prov => ({
            // Concatenamos nombre y RIF/Cédula para mayor claridad del administrador
            label: `${prov.nombre_proveedor} (${prov.rif || 'Sin RIF'})`, 
            value: prov.id_proveedor
          }));

          return (
            <SelectorDesplegable
              opciones={opcionesProveedores}
              valorSeleccionado={value}
              onSelect={onChange}
              placeholder="Seleccione el proveedor..."
              icono="person-outline"
            />
          );
        }}
      />
      <ErrorFormulario error={errors.proveedor_id} />

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
        returnKeyType="next"
        onSubmitEditing={() => montoRef.current?.focus()}
      />

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

      {/* --- SECCIÓN DE PAGOS --- */}
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

      {requiereBanco && (
        <>
          <LabelInput titulo="Banco" icono={{ nombre: 'business', color: '#3498db' }} />
          <Controller
            control={control}
            name="banco_id"
            rules={validaciones.requeridoSimple('Selecciona un banco')}
            render={({ field: { onChange, value } }) => (
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 15 }}>
                {catalogos.bancos.map((banco) => {
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

          <LabelInput titulo="Comprobante Bancario" icono={{ nombre: 'image-outline', color: '#3498db' }} />
          <ErrorFormulario error={errors.imagen} />
          <View style={{ flexDirection: 'row', gap: 10, marginBottom: 10 }}>
            <CustomBoton titulo={comprobanteUri ? 'Cambiar Foto' : 'Subir Captura'} evento={handleImagePick} icono={{ nombre: 'camera-outline', color: '#fff' }} estilos={{ flex: 1 }} />
            {comprobanteUri && (
              <CustomBoton titulo="Quitar" evento={removeImage} icono={{ nombre: 'trash-outline', color: '#fff' }} estilos={{ backgroundColor: '#e74c3c' }} />
            )}
          </View>
          {comprobanteUri && <MostrarVistaPrevia titulo="Recibo adjunto:" imageUri={comprobanteUri} />}
        </>
      )}

    </ModalGeneral>
  );
}