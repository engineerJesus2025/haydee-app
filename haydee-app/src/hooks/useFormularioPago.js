import { useState, useEffect, useMemo, useRef } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch } from 'react-redux';
import { Alert } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import {
  registrarPagoServidor,
  modificarPagoServidor,
} from '../store/slices/pagosSlice';
import clienteApi from '../utils/clienteApi';
import { usePermisos } from './usePermisos';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import AsyncStorage from '@react-native-async-storage/async-storage';

//    true = mostrar
export const OCR_HABILITADO = true;

export const useFormularioPago = (onClose, { modo = 'crear', pagoAEditar = null, visible  } = {}) => {
  const dispatch = useDispatch();
  const esEdicion = modo === 'editar';

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [tasaDolar, setTasaDolar] = useState('1.00');
  const [bancosDisponibles, setBancosDisponibles] = useState([]);
  const [cuentasDisponibles, setCuentasDisponibles] = useState([]);   // 👈 NUEVO
  const [apartamentosDisponibles, setApartamentosDisponibles] = useState([]);
  const [mesesPendientes, setMesesPendientes] = useState([]);
  const [cargandoMensualidades, setCargandoMensualidades] = useState(false);
  const [escanerCargando, setEscanerCargando] = useState(false);       // 👈 NUEVO

  const { esAdmin, usuario } = usePermisos();

  const {
    control, handleSubmit, formState: { errors, isValid },
    reset, setValue, watch, setError, clearErrors, trigger
  } = useForm({
    mode: 'onChange',
    defaultValues: {
      apartamento_id: '',
      monto: '',
      referencia: '',
      banco_id: '',
      cuenta_id: '',
      tipo_pago: 'TRANSFERENCIA',
      mensualidad_id: '',
      imagen: null,
      estado: 'PENDIENTE',
      observacion: '',
    }
  });

  const comprobanteUri = watch('imagen');
  const apartamentoSeleccionado = watch('apartamento_id');
  const tipoPagoSeleccionado = watch('tipo_pago');
  const montoIngresado = watch('monto');
  const mensualidadSeleccionada = watch('mensualidad_id');

  const pendienteMensualidadSeleccionada = useMemo(() => {
    if (!mensualidadSeleccionada) return null;
    const mes = mesesPendientes.find(m => String(m.id) === String(mensualidadSeleccionada));
    return mes ? (mes.pendiente ?? null) : null;
  }, [mensualidadSeleccionada, mesesPendientes]);

  const requiereBanco = ['TRANSFERENCIA', 'PAGO MOVIL'].includes(tipoPagoSeleccionado);
  const requiereCuenta = requiereBanco;

  const equivalenteDolares = useMemo(() => {
    const montoBase = parseFloat(montoIngresado);
    const tasa = parseFloat(tasaDolar);
    if (!isNaN(montoBase) && !isNaN(tasa) && tasa > 0) return (montoBase / tasa).toFixed(2);
    return '0.00';
  }, [montoIngresado, tasaDolar]);

  const validarMontoContraDeuda = (valor) => {
    if (esEdicion) return true;
    if (pendienteMensualidadSeleccionada === null) return true;
    const montoNum = parseFloat(valor);
    if (isNaN(montoNum) || montoNum <= 0) return true;
    if (Math.round(montoNum * 100) > Math.round(pendienteMensualidadSeleccionada * 100)) {
      return `El monto no puede superar la deuda pendiente (${pendienteMensualidadSeleccionada.toFixed(2)} Bs)`;
    }
    return true;
  };

  //  CARGA DE CATÁLOGOS 
  useEffect(() => {
    const cargarCatalogosIniciales = async () => {
      try {
        const respuesta = await clienteApi.get('', {
          params: {
            endpoint: 'pagos',
            operacion: 'obtener_catalogos_base',
            es_propietario: esAdmin ? 0 : 1,
            correo: usuario?.correo || ''
          }
        });

        if (respuesta.data.estatus) {
          const { bancos, apartamentos, cuentas } = respuesta.data.datos;
          setBancosDisponibles(bancos || []);
          setApartamentosDisponibles(apartamentos || []);
          setCuentasDisponibles(cuentas || []);

          if (!esEdicion && apartamentos?.length === 1) {
            setValue('apartamento_id', apartamentos[0].id_apartamento, { shouldValidate: true });
          }
        }
      } catch (error) {
        const esErrorDeRed =
          error.message === 'Network Error' ||
          error.code === 'ERR_NETWORK' ||
          (!error.response && error.request);
        if (!esErrorDeRed) procesarErrorApi(error);
      }
    };

    const leerTasaDeCacheLocal = async () => {
      try {
        const tasaGuardada = await AsyncStorage.getItem('tasa_dolar');
        setTasaDolar(tasaGuardada || '1.00');
      } catch (e) {
        setTasaDolar('1.00');
      }
    };

    leerTasaDeCacheLocal();
    cargarCatalogosIniciales();
  }, []);

  // CARGA DE MENSUALIDADES
  useEffect(() => {
    if (!apartamentoSeleccionado) {
      setMesesPendientes([]);
      return;
    }

    const cargarDeudas = async () => {
      setCargandoMensualidades(true);
      try {
        const resDeudas = await clienteApi.get('', {
          params: {
            endpoint: 'pagos',
            operacion: 'consultar_mensualidades',
            apartamento_id: apartamentoSeleccionado
          }
        });

        if (resDeudas.data.estatus) {
          const deudasFormateadas = resDeudas.data.datos.map(d => ({
            id: d.id_mensualidad,
            mes: `${d.mes} ${d.anio}`,
            monto: d.pendiente,
            pendiente: parseFloat(d.pendiente) || 0
          }));

          if (esEdicion && pagoAEditar?.mensualidad_id) {
            const yaEsta = deudasFormateadas.some(m => String(m.id) === String(pagoAEditar.mensualidad_id));
            if (!yaEsta) {
              const det = pagoAEditar.detalles?.[0] ?? {};
              deudasFormateadas.unshift({
                id: pagoAEditar.mensualidad_id,
                mes: pagoAEditar.mensualidad || 'Mensualidad actual',
                monto: det.monto_crudo,
                pendiente: parseFloat(det.monto_crudo) || 0,
              });
            }
          }

          setMesesPendientes(deudasFormateadas);
        } else {
          setMesesPendientes([]);
        }
      } catch (error) {
        const esErrorDeRed =
          error.message === 'Network Error' ||
          error.code === 'ERR_NETWORK' ||
          (!error.response && error.request);
        if (!esErrorDeRed) procesarErrorApi(error);
        setMesesPendientes([]);
      } finally {
        setCargandoMensualidades(false);
      }
    };

    cargarDeudas();
    if (!esEdicion) setValue('mensualidad_id', null);
  }, [apartamentoSeleccionado]);

  useEffect(() => {
    if (!requiereBanco) {
      clearErrors('imagen');
      clearErrors('banco_id');
      clearErrors('cuenta_id');
      clearErrors('referencia');
    }
  }, [requiereBanco, clearErrors]);

  useEffect(() => {
    if (watch('monto')) trigger('monto');
  }, [mensualidadSeleccionada, trigger]);

  // reset al abrir y al cerrar
  useEffect(() => {
    if (!visible) {
      reset({
        apartamento_id: '',
        monto: '',
        referencia: '',
        banco_id: '',
        cuenta_id: '',
        tipo_pago: 'TRANSFERENCIA',
        mensualidad_id: '',
        imagen: null,
        estado: 'PENDIENTE',
        observacion: '',
      });
      return;
    }

     if (esEdicion && pagoAEditar) {
      // Modal abierto en modo editar → precargar
      const det = pagoAEditar.detalles?.[0] ?? {};
      reset({
        apartamento_id: String(pagoAEditar.apartamento_id ?? ''),
        mensualidad_id: String(pagoAEditar.mensualidad_id ?? ''),
        monto: String(det.monto_crudo ?? ''),           // 👈 viene de det, no del root
        tipo_pago: det.tipo_pago ?? 'TRANSFERENCIA',
        banco_id: det.banco_id ?? '',
        cuenta_id: det.cuenta_id ?? '',
        referencia: det.referencia_cruda ?? '',
        imagen: det.imagen ?? null,                     // 👈 faltaba
        estado: pagoAEditar.estado ?? 'PENDIENTE',
        observacion: pagoAEditar.observacion ?? '',
      });
    } else {
      reset({
        apartamento_id: '',
        monto: '',
        referencia: '',
        banco_id: '',
        cuenta_id: '',
        tipo_pago: 'TRANSFERENCIA',
        mensualidad_id: '',
        imagen: null,
        estado: 'PENDIENTE',
        observacion: '',
      });
    }
  }, [visible, pagoAEditar?.id, esEdicion]);

  const handleImagePick = async () => {
    let result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [4, 5],
      quality: 0.4,
      base64: true
    });

    if (!result.canceled) {
      setValue('imagen', result.assets[0].uri, { shouldValidate: true });
      clearErrors('imagen');
    }
  };

  const removeImage = () => {
    setValue('imagen', null, { shouldValidate: true });
  };

  // OCR (preparado, feature flag)
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
        params: { endpoint: 'pagos' },
        timeout: 45000,
      });

      const json = respuesta.data;

      // Tanto en éxito como en lectura parcial, aplicamos lo que se pudo leer.
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
        const bancoEncontrado = bancosDisponibles.find(b => {
          const cb = (b.codigo || '').toLowerCase();
          const jcb = String(json.banco).toLowerCase();
          return cb && (jcb.includes(cb) || cb.includes(jcb));
        });
        if (bancoEncontrado) {
          setValue('banco_id', bancoEncontrado.id_banco, { shouldValidate: true });
          camposRellenados++;
        }
      }

      if (json.estatus) {
        Alert.alert('Comprobante leído', 'Se rellenaron los campos automáticamente. Revísalos antes de enviar.');
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
      formData.append('operacion', esEdicion ? 'modificar_pago' : 'registrar_pago');
      formData.append('es_propietario', esAdmin ? '0' : '1');

      formData.append('apartamento_id', data.apartamento_id);
      formData.append('mensualidad_id', data.mensualidad_id);

      const observacionFinal = (data.observacion || '').trim();

      if (esEdicion) {
        formData.append('id_pago', pagoAEditar.id);
        formData.append('observacion', observacionFinal || 'Pago editado desde la App');
      } else {
        formData.append('observacion', observacionFinal || 'Pago registrado desde la App');
      }

      formData.append('estado', data.estado);
      formData.append('tasa_dolar', tasaDolar);

      let fechaPago;
      if (esEdicion) {
        fechaPago = pagoAEditar?.detalles?.[0]?.fecha || new Date().toISOString().split('T')[0];
      } else {
        fechaPago = new Date().toISOString().split('T')[0];
      }
      formData.append('fecha_pago[0]', fechaPago);

      formData.append('monto[0]', data.monto);
      formData.append('tipo_pago[0]', data.tipo_pago);

      if (requiereBanco) {
        formData.append('referencia[0]', data.referencia);
        formData.append('banco_id[0]', data.banco_id);
        formData.append('cuenta_id[0]', data.cuenta_id);

        if (data.imagen) {
          if (data.imagen.startsWith('file://')) {
            const filename = data.imagen.split('/').pop() || 'comprobante.jpg';
            const match = /\.(\w+)$/.exec(filename);
            const type = match ? `image/${match[1]}` : 'image';
            formData.append('imagen_0', { uri: data.imagen, name: filename, type });
          } else if (esEdicion) {
            const nombreArchivo = pagoAEditar.detalles?.[0]?.imagen_nombre;
            if (nombreArchivo) {
              formData.append('imagen_existente[0]', nombreArchivo);
            }
          }
        }
      }

      const aptoObj = apartamentosDisponibles.find(a => String(a.id_apartamento) === String(data.apartamento_id));
      const mesObj = mesesPendientes.find(m => String(m.id) === String(data.mensualidad_id));
      const cuentaObj = cuentasDisponibles.find(c => String(c.id_cuenta) === String(data.cuenta_id));
      const nombreApartamento = aptoObj ? aptoObj.nro_apartamento : (pagoAEditar?.apartamento ?? 'No asignado');
      const mesMensualidad = mesObj ? mesObj.mes.split(' ').join('/') : (pagoAEditar?.mensualidad ?? '');

      const datosVisuales = {
        monto: `${parseFloat(data.monto).toFixed(2)} Bs.`,
        montoCrudo: data.monto,
        estado: data.estado,
        fecha: fechaPago,
        mensualidad: mesMensualidad,
        apartamento: nombreApartamento,
        tipo_pago: data.tipo_pago,
        banco: 'N/A',
        cuenta: cuentaObj?.numero_cuenta || 'N/A',
        referencia: data.referencia || 'N/A',
        imagen: data.imagen,
        observacion: observacionFinal
      };

      if (esEdicion) {
        await dispatch(modificarPagoServidor({
          id_pago: pagoAEditar.id,
          formData,
          datosVisuales,
        })).unwrap();
      } else {
        await dispatch(registrarPagoServidor({ datosVisuales, formData })).unwrap();
      }

      onClose(true);
      reset();
    } catch (error) {
      procesarErrorApi(error, (status, mensaje, erroresFormulario) => {
        if (status === 400 && erroresFormulario) {
          Object.keys(erroresFormulario).forEach(campoServer => {
            let campoFrontend = campoServer;
            if (campoServer.startsWith('detalle_0_')) {
              campoFrontend = campoServer.replace('detalle_0_', '');
            }
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

  const canSubmit =
    isValid &&
    !isSubmitting &&
    (!requiereBanco || (watch('banco_id') && watch('cuenta_id') && watch('referencia')));

  const onError = (errors) => {
    const primerCampoConError = Object.keys(errors)[0];
    const mensajeError = errors[primerCampoConError]?.message || 'Por favor, completa este campo correctamente.';
    const nombresCampos = {
      apartamento_id: 'Apartamento',
      monto: 'Monto',
      referencia: 'Referencia',
      banco_id: 'Banco',
      cuenta_id: 'Cuenta del condominio',
      tipo_pago: 'Tipo de pago',
      mensualidad_id: 'Mensualidad',
      estado: 'Estado del pago',
      imagen: 'Comprobante',
      observacion: "Observacion"
    };
    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;
    Alert.alert('Información incompleta', `Error en ${nombreLegible}: ${mensajeError}`);
  };

  return {
    control, errors, comprobanteUri, isSubmitting, isValid: canSubmit,
    handleSubmit, handleImagePick, removeImage, onSubmit,
    bancosDisponibles, cuentasDisponibles,
    apartamentosDisponibles, mesesPendientes,
    requiereBanco, requiereCuenta,
    esAdmin, tasaDolar, equivalenteDolares, apartamentoSeleccionado, cargandoMensualidades, onError,
    pendienteMensualidadSeleccionada, validarMontoContraDeuda,
    escanearComprobante, escanerCargando,
    OCR_HABILITADO,
  };
};