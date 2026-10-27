import { useState, useEffect, useMemo } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch, useSelector } from 'react-redux';
import { Alert } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { crearGasto, fetchCatalogosGastos, modificarGasto } from '../store/slices/gastosSlice';
import clienteApi from '../utils/clienteApi';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const OCR_HABILITADO = true;

export const useFormularioGasto = (onClose, { modo = 'crear', gastoAEditar = null, visible = false } = {}) => {
  const dispatch = useDispatch();
  const esEdicion = modo === 'editar';
  const catalogos = useSelector(state => state.gastos.catalogos);

  const [permissionStatus, requestPermission] = ImagePicker.useMediaLibraryPermissions();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [tasaDolar, setTasaDolar] = useState('1.00');
  const [escanerCargando, setEscanerCargando] = useState(false);

  useEffect(() => {
    const leerTasaDeCacheLocal = async () => {
      try {
        const tasaGuardada = await AsyncStorage.getItem('tasa_dolar');
        if (tasaGuardada) {
          setTasaDolar(tasaGuardada);
        } else {
          setTasaDolar('1.00');
        }
      } catch (e) {
        setTasaDolar('1.00');
      }
    };

    leerTasaDeCacheLocal();

    if (!visible) return;
    if (catalogos.tipos_gasto.length === 0) {
      dispatch(fetchCatalogosGastos());
    }
  }, [visible, dispatch, catalogos.tipos_gasto.length]);

  const valoresIniciales = useMemo(() => {
    if (modo === 'editar' && gastoAEditar) {
      const det = gastoAEditar.detalles?.[0] ?? {};
      const conceptoId = String(gastoAEditar.concepto_id ?? '');

      const conceptoObj = (catalogos.conceptos || []).find(
        c => String(c.id_concepto) === conceptoId
      );

      return {
        clasificacion:     gastoAEditar.clasificacion || 'VARIABLE',
        tipo_gasto_id:     conceptoObj ? String(conceptoObj.id_tipo_gasto) : '',
        concepto_id:       conceptoId,
        proveedor_id:      String(gastoAEditar.proveedor_id ?? ''),
        descripcion_gasto: gastoAEditar.descripcion || '',
        monto:             String(det.monto_crudo ?? ''),
        metodo_pago:       det.metodo_pago || 'EFECTIVO',
        cuenta_id:         String(det.cuenta_id ?? ''),
        referencia:        det.referencia_cruda || '',
        imagen:            det.imagen ?? null,
      };
    }
    return {
      clasificacion: 'VARIABLE',
      tipo_gasto_id: '',
      concepto_id: '',
      proveedor_id: '',
      descripcion_gasto: '',
      monto: '',
      metodo_pago: 'EFECTIVO',
      cuenta_id: '',
      referencia: '',
      imagen: null,
    };
  }, [modo, gastoAEditar, catalogos.conceptos]);

  const {
    control, handleSubmit,
    formState: { errors, isValid },
    reset, setValue, watch, setError, clearErrors,
  } = useForm({
    mode: 'onChange',
    defaultValues: valoresIniciales,
  });

  const comprobanteUri = watch('imagen');
  const metodoPago = watch('metodo_pago');
  const montoActual = watch('monto');
  const descripcionActual = watch('descripcion_gasto');
  const tipoGastoIdSeleccionado = watch('tipo_gasto_id');

  const requiereBanco = ['TRANSFERENCIA', 'PAGO MOVIL'].includes(metodoPago);

  const equivalenteDolares = useMemo(() => {
    const montoBase = parseFloat(montoActual);
    const tasa = parseFloat(tasaDolar);
    if (!isNaN(montoBase) && !isNaN(tasa) && tasa > 0) {
      return (montoBase / tasa).toFixed(2);
    }
    return '0.00';
  }, [montoActual, tasaDolar]);

  useEffect(() => {
    (async () => {
      if (!permissionStatus?.granted) await requestPermission();
    })();
  }, [permissionStatus]);

  useEffect(() => {
    if (!requiereBanco) {
      clearErrors('imagen');
    }
  }, [requiereBanco, clearErrors]);

  const handleImagePick = async () => {
    try {
      if (!permissionStatus?.granted) {
        const permissionResult = await requestPermission();
        if (!permissionResult.granted) return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        aspect: [4, 5],
        quality: 0.4,
        base64: true
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        setValue('imagen', result.assets[0].uri, { shouldValidate: true });
        clearErrors('imagen');
      }
    } catch (error) {
      procesarErrorApi(error);
    }
  };

  const removeImage = () => setValue('imagen', null);

  const escanearComprobante = async (uri) => {
    if (!OCR_HABILITADO) return;
    if (!uri || !uri.startsWith('file://')) {
      Alert.alert(
        'Solo imágenes nuevas',
        'El escaneo automático solo está disponible para imágenes recién subidas en este formulario.'
      );
      return;
    }
    if (escanerCargando) return;

    setEscanerCargando(true);
    try {
      const filename = uri.split('/').pop() || 'comprobante.jpg';
      const match = /\.(\w+)$/.exec(filename);
      const type = match ? `image/${match[1]}` : 'image/jpeg';

      const formData = new FormData();
      formData.append('operacion', 'escanear_comprobante');
      formData.append('comprobante', { uri, name: filename, type });

      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'gastos' },
        timeout: 45000,
      });

      const json = respuesta.data;
      let camposRellenados = 0;

      if (json.monto) {
        setValue('monto', String(json.monto), { shouldValidate: true });
        camposRellenados++;
      }

      if (json.referencia) {
        setValue('referencia', String(json.referencia), { shouldValidate: true });
        camposRellenados++;
      }

      if (json.banco) {
        const jcb = String(json.banco).toLowerCase().trim();
        const cuentasCoincidentes = (catalogos.cuentas || []).filter(c => {
          const codigo = String(c.codigo || '').toLowerCase().trim();
          return codigo && (codigo.includes(jcb) || jcb.includes(codigo));
        });

        if (cuentasCoincidentes.length === 1) {
          setValue('cuenta_id', cuentasCoincidentes[0].id_cuenta, { shouldValidate: true });
          camposRellenados++;
        }
      }

      if (json.estatus) {
        Alert.alert(
          'Comprobante leído',
          'Se rellenaron los campos automáticamente. Revísalos antes de enviar.'
        );
      } else {
        Alert.alert(
          'Lectura parcial',
          `${json.mensaje || 'No se pudo leer todo.'} Se rellenaron ${camposRellenados} campo${camposRellenados !== 1 ? 's' : ''}. Revisa y completa lo que falte.`
        );
      }
    } catch (error) {
      procesarErrorApi(error);
    } finally {
      setEscanerCargando(false);
    }
  };

  const onSubmit = async (data) => {
    clearErrors();

    if (isSubmitting) return;
    setIsSubmitting(true);

    await new Promise(resolve => setTimeout(resolve, 50));
    try {
      const formData = new FormData();

      formData.append('operacion', esEdicion ? 'modificar_gasto' : 'registrar_gasto');
      formData.append('clasificacion', data.clasificacion);
      formData.append('descripcion_gasto', data.descripcion_gasto);
      formData.append('concepto_id', data.concepto_id);
      formData.append('proveedor_id', data.proveedor_id);
      formData.append('tasa_dolar', tasaDolar);

      if (esEdicion) {
        formData.append('id_gasto', gastoAEditar.id);
      }

      const fechaDetalle = esEdicion
        ? (gastoAEditar.detalles?.[0]?.fecha || new Date().toISOString().split('T')[0])
        : new Date().toISOString().split('T')[0];

      formData.append('fecha_detalle[0]', fechaDetalle);
      formData.append('monto[0]', data.monto);
      formData.append('metodo_pago[0]', data.metodo_pago);

      if (requiereBanco) {
        formData.append('cuenta_id[0]', data.cuenta_id);
        formData.append('referencia[0]', data.referencia);

        if (data.imagen) {
          let localUri = data.imagen;
          let filename = localUri.split('/').pop() || 'comprobante.jpg';
          let match = /\.(\w+)$/.exec(filename);
          let type = match ? `image/${match[1]}` : `image`;
          formData.append('imagen_0', { uri: localUri, name: filename, type });
        }
      }

      const proveedorObj = catalogos.proveedores.find(p => String(p.id_proveedor) === String(data.proveedor_id));
      const conceptoObj = (catalogos.conceptos || []).find(
        c => String(c.id_concepto) === String(data.concepto_id)
      );

      const datosVisuales = {
        fecha: fechaDetalle,
        monto: parseFloat(data.monto).toFixed(2),
        montoCrudo: data.monto,
        tipo: data.clasificacion || 'Variable',
        tipo_gasto: conceptoObj?.nombre_tipo_gasto || 'General',
        proveedor: proveedorObj
          ? (proveedorObj.nombre_proveedor || proveedorObj.nombre || 'No especificado')
          : 'No especificado',
        descripcion: data.descripcion_gasto,
        imagen: data.imagen,
      };

      if (esEdicion) {
        await dispatch(modificarGasto({
          id_gasto: gastoAEditar.id,
          formData,
          datosVisuales,
        })).unwrap();

        onClose(true);
      } else {
        await dispatch(crearGasto({ datosVisuales, formData })).unwrap();

        onClose(false);
      }
      setTimeout(() => reset(), 400);

    } catch (error) {
      procesarErrorApi(error, (status, mensaje, erroresFormulario) => {
        if (status === 400 && erroresFormulario) {
          Object.keys(erroresFormulario).forEach(campoServer => {
            let campoFrontend = campoServer;
            if (campoServer.startsWith('detalle_0_')) {
              campoFrontend = campoServer.replace('detalle_0_', '');
            }

            if (campoFrontend === 'metodo_pago') campoFrontend = 'metodo_pago';

            setError(campoFrontend, {
              type: 'server',
              message: erroresFormulario[campoServer][0]
            });
          });

          Alert.alert('Datos Inválidos', mensaje || 'Revise los campos marcados en rojo.');
          return true;
        }

        return false;
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = () => {
    onClose();
    setTimeout(() => reset(), 400);
  };

  const canSubmit = isValid && !isSubmitting && descripcionActual?.trim() && montoActual?.trim()
                    && (!requiereBanco || (watch('cuenta_id') && watch('referencia')));

  const onError = (errors) => {
    const primerCampoConError = Object.keys(errors)[0];
    const mensajeError = errors[primerCampoConError]?.message || 'Por favor, completa este campo correctamente.';

    const nombresCampos = {
      clasificacion: 'Clasificacion',
      tipo_gasto_id: 'Tipo de gasto',
      proveedor_id: 'Proveedor',
      descripcion_gasto: 'Descripcion del gasto',
      monto: 'Monto',
      metodo_pago: 'Metodo de pago',
      banco_id: 'Banco',
      cuenta_id: 'Cuenta del condominio',
      referencia: 'Referencia',
      imagen: 'Comprobante'
    };

    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;

    Alert.alert(
      "Información incompleta",
      `Error en ${nombreLegible}: ${mensajeError}`
    );
  };

  const opcionesTiposGasto = useMemo(() =>
    (catalogos.tipos_gasto || []).map(t => ({
      label: t.nombre_tipo_gasto,
      value: String(t.id_tipo_gasto),
    }))
  , [catalogos.tipos_gasto]);

  const opcionesConceptos = useMemo(() =>
    (catalogos.conceptos || [])
      .filter(c => String(c.id_tipo_gasto) === String(tipoGastoIdSeleccionado))
      .map(c => ({
        label: c.nombre_concepto,
        value: String(c.id_concepto),
      }))
  , [catalogos.conceptos, tipoGastoIdSeleccionado]);

  const opcionesProveedores = useMemo(() =>
    (catalogos.proveedores || []).map(p => ({
      label: `${p.nombre_proveedor} (${p.rif || 'Sin RIF'})`,
      value: String(p.id_proveedor),
    }))
  , [catalogos.proveedores]);

  const opcionesCuentas = useMemo(() =>
    (catalogos.cuentas || []).map(c => ({
      label: `${c.nombre_banco} · ${c.numero_cuenta}`,
      value: String(c.id_cuenta),
    }))
  , [catalogos.cuentas]);

  const handleTipoGastoChange = (nuevoTipoId) => {
    setValue('tipo_gasto_id', nuevoTipoId, { shouldValidate: true });
    setValue('concepto_id', '', { shouldValidate: true });
    clearErrors('concepto_id');
  };

  return {
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
  };
};