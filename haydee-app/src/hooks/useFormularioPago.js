import { useState, useEffect, useMemo } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch } from 'react-redux';
import { Alert } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { registrarPagoServidor } from '../store/slices/pagosSlice';
import clienteApi from '../utils/clienteApi';
import { usePermisos } from './usePermisos';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const useFormularioPago = (onClose) => {
  const dispatch = useDispatch();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [tasaDolar, setTasaDolar] = useState('1.00'); 
  const [bancosDisponibles, setBancosDisponibles] = useState([]);
  const [apartamentosDisponibles, setApartamentosDisponibles] = useState([]);
  const [mesesPendientes, setMesesPendientes] = useState([]);
  const [cargandoMensualidades, setCargandoMensualidades] = useState(false);

  const { esAdmin, usuario } = usePermisos();

  const { control, handleSubmit, formState: { errors, isValid }, reset, setValue, watch, setError, clearErrors } = useForm({
    mode: 'onChange',
    defaultValues: {
      apartamento_id: '',
      monto: '',
      referencia: '',
      banco_id: '',
      tipo_pago: 'TRANSFERENCIA',
      mensualidad_id: '',
      imagen: null,
      estado: 'PENDIENTE'
    }
  });

  const comprobanteUri = watch('imagen');
  const apartamentoSeleccionado = watch('apartamento_id'); 
  const tipoPagoSeleccionado = watch('tipo_pago');
  const montoIngresado = watch('monto'); 

  const requiereBanco = ['TRANSFERENCIA', 'PAGO MOVIL'].includes(tipoPagoSeleccionado);

  const equivalenteDolares = useMemo(() => {
    const montoBase = parseFloat(montoIngresado);
    const tasa = parseFloat(tasaDolar);
    if (!isNaN(montoBase) && !isNaN(tasa) && tasa > 0) return (montoBase / tasa).toFixed(2);
    return '0.00';
  }, [montoIngresado, tasaDolar]);

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
          const { bancos, apartamentos } = respuesta.data.datos;
          setBancosDisponibles(bancos);
          setApartamentosDisponibles(apartamentos);
          if (apartamentos.length === 1) {
            setValue('apartamento_id', apartamentos[0].id_apartamento, { shouldValidate: true });
          }
        }
      } catch (error) {
        const esErrorDeRed = 
          error.message === 'Network Error' || 
          error.code === 'ERR_NETWORK' ||
          (!error.response && error.request);

        if (!esErrorDeRed) {
          procesarErrorApi(error);
        }
      }
    };

    const leerTasaDeCacheLocal = async () => {
      try {
        const tasaGuardada = await AsyncStorage.getItem('tasa_dolar');
        if (tasaGuardada) setTasaDolar(tasaGuardada);
        else setTasaDolar('1.00');
      } catch (e) {
        setTasaDolar('1.00');
      }
    };

    leerTasaDeCacheLocal();
    cargarCatalogosIniciales();
  }, []);

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
            monto: d.pendiente
          }));
          setMesesPendientes(deudasFormateadas);
        } else {
           setMesesPendientes([]);
        }
      } catch (error) {
        const esErrorDeRed = 
          error.message === 'Network Error' || 
          error.code === 'ERR_NETWORK' ||
          (!error.response && error.request);

        if (!esErrorDeRed) {
          procesarErrorApi(error);
        }
        setMesesPendientes([]);
      } finally {
        setCargandoMensualidades(false);
      }
    };

    cargarDeudas();
    setValue('mensualidad_id', null); 
  }, [apartamentoSeleccionado]);

  useEffect(() => {
    if (!requiereBanco) clearErrors('imagen');
  }, [requiereBanco, clearErrors]);

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
  
  const onSubmit = async (data) => {
    clearErrors(); 
    if (isSubmitting) return;
    setIsSubmitting(true);

    await new Promise(resolve => setTimeout(resolve, 50)); 

    try {
      const formData = new FormData();
      formData.append('operacion', 'registrar_pago');
      formData.append('es_propietario', esAdmin ? '0' : '1');
      formData.append('apartamento_id', data.apartamento_id);
      formData.append('mensualidad_id', data.mensualidad_id);
      formData.append('observacion', 'Pago registrado desde la App');
      formData.append('estado', data.estado); 
      formData.append('tasa_dolar', tasaDolar);

      const hoy = new Date().toISOString().split('T')[0];
      formData.append('fecha_pago[0]', hoy);
      formData.append('monto[0]', data.monto);
      formData.append('tipo_pago[0]', data.tipo_pago);

      if (requiereBanco) {
        formData.append('referencia[0]', data.referencia);
        formData.append('banco_id[0]', data.banco_id);

        if (data.imagen) {
          const localUri = data.imagen;
          const filename = localUri.split('/').pop() || 'comprobante.jpg';
          const match = /\.(\w+)$/.exec(filename);
          const type = match ? `image/${match[1]}` : `image`;
          formData.append('imagen_0', { uri: localUri, name: filename, type });
        }
      }

      const aptoObj = apartamentosDisponibles.find(a => String(a.id_apartamento) === String(data.apartamento_id));
      const mesObj = mesesPendientes.find(m => String(m.id) === String(data.mensualidad_id));
      const nombreApartamento = aptoObj ? (aptoObj.nro_apartamento) : 'No asignado';
      const mesMensualidad = mesObj ? mesObj.mes.split(" ").join("/") : '';

      const datosVisuales = {
        monto: `${parseFloat(data.monto).toFixed(2)} Bs.`,
        montoCrudo: data.monto,
        estado: data.estado,
        fecha: hoy,
        mensualidad: mesMensualidad,
        apartamento: nombreApartamento,
        tipo_pago: data.tipo_pago,
        banco: 'N/A', 
        referencia: 'N/A',
        imagen: data.imagen
      };

      await dispatch(registrarPagoServidor({ datosVisuales, formData })).unwrap();
      onClose();
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

  const canSubmit = isValid && !isSubmitting && (!requiereBanco || (watch('banco_id') && watch('referencia')));

  const onError = (errors) => {
    const primerCampoConError = Object.keys(errors)[0];
    const mensajeError = errors[primerCampoConError]?.message || 'Por favor, completa este campo correctamente.';
    const nombresCampos = {
      apartamento_id: 'Apartamento',
      monto: 'Monto',
      referencia: 'Referencia',
      banco_id: 'Banco',
      tipo_pago: 'Tipo de pago',
      mensualidad_id: 'Mensualidad',
      estado: 'Estado del pago',
      imagen: 'Comprobante'
    };
    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;
    Alert.alert("Información incompleta", `Error en ${nombreLegible}: ${mensajeError}`);
  };

  return { 
    control, errors, comprobanteUri, isSubmitting, isValid: canSubmit,
    handleSubmit, handleImagePick, onSubmit, removeImage: () => setValue('imagen', null),
    bancosDisponibles, apartamentosDisponibles, mesesPendientes, requiereBanco,
    esAdmin, tasaDolar, equivalenteDolares, apartamentoSeleccionado, cargandoMensualidades, onError
  };
};