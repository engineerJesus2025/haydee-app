import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch, useSelector } from 'react-redux';
import { Alert } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { crearGasto, fetchCatalogosGastos } from '../store/slices/gastosSlice';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const useFormularioGasto = (onClose) => {
  const dispatch = useDispatch();
  
  // Traemos los catálogos de Redux
  const catalogos = useSelector(state => state.gastos.catalogos);
  
  const [permissionStatus, requestPermission] = ImagePicker.useMediaLibraryPermissions();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [tasaDolar, setTasaDolar] = useState('1.00');

  // Inicializamos los catálogos al abrir el modal (si no están cargados)
  useEffect(() => {
    const leerTasaDeCacheLocal = async () => {
      try {
        const tasaGuardada = await AsyncStorage.getItem('tasa_dolar');
        if (tasaGuardada) {
          setTasaDolar(tasaGuardada);
        } else {
          setTasaDolar('1.00'); // en caso de error crítico de hardware
        }
      } catch (e) {
        setTasaDolar('1.00');
      }
    };

    leerTasaDeCacheLocal();
    
    if (catalogos.tipos_gasto.length === 0) {
      dispatch(fetchCatalogosGastos());
    }
  }, [dispatch]);

  const {
    control,
    handleSubmit,
    formState: { errors, isValid },
    reset,
    setValue,
    watch,
    setError,
    clearErrors
  } = useForm({
    mode: 'onChange',
    defaultValues: {
      clasificacion: 'VARIABLE',
      tipo_gasto_id: '',
      proveedor_id: '',
      descripcion_gasto: '',
      monto: '',
      metodo_pago: 'EFECTIVO',
      banco_id: '',
      referencia: '',
      imagen: null
    }
  });

  const comprobanteUri = watch('imagen');
  const metodoPago = watch('metodo_pago');
  const requiereBanco = ['TRANSFERENCIA', 'PAGO MOVIL'].includes(metodoPago);

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
      // Verificamos permisos dinámicamente
      if (!permissionStatus?.granted) {
        const permissionResult = await requestPermission();
        if (!permissionResult.granted) return;
      }

      // Disparamos la galería optimizada
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

  useEffect(() => {
    if (!requiereBanco) {
      clearErrors('imagen');
    }
  }, [requiereBanco, clearErrors]);

  const removeImage = () => setValue('imagen', null);

  const onSubmit = async (data) => {
    clearErrors();

    /* if (requiereBanco && !data.imagen) {
      setError('imagen', { type: 'manual', message: 'Debes adjuntar la captura del recibo.' });
      onError({ imagen: { message: 'Debes adjuntar la captura del recibo.' } });
      return; 
    } */

    if (isSubmitting) return;
    setIsSubmitting(true);

    // Pausamos para que tenga tiempo de pintar el Spinner
    await new Promise(resolve => setTimeout(resolve, 50)); // Esto se llama THREAD YIELDING mi estimado
    try {
      const formData = new FormData();
      
      // -- CABECERA DEL GASTO --
      formData.append('operacion', 'registrar_gasto');
      formData.append('clasificacion', data.clasificacion);
      formData.append('descripcion_gasto', data.descripcion_gasto);
      formData.append('tipo_gasto_id', data.tipo_gasto_id); 
      formData.append('proveedor_id', data.proveedor_id);
      formData.append('tasa_dolar', tasaDolar);

      // -- DETALLES DEL GASTO (Renglones) --
      const fechaHoy = new Date().toISOString().split('T')[0];
      formData.append('fecha_detalle[0]', fechaHoy);
      formData.append('monto[0]', data.monto);
      formData.append('metodo_pago[0]', data.metodo_pago);

      // -- DATOS BANCARIOS (Si aplica) --
      if (requiereBanco) {
        formData.append('banco_id[0]', data.banco_id);
        formData.append('referencia[0]', data.referencia);
        
        if (data.imagen) {
          let localUri = data.imagen;
          let filename = localUri.split('/').pop() || 'comprobante.jpg';
          let match = /\.(\w+)$/.exec(filename);
          let type = match ? `image/${match[1]}` : `image`;
          formData.append('imagen_0', { uri: localUri, name: filename, type }); 
          // constructor de PHP usa 'imagen_0', 'imagen_1' para los archivos.
        }
      }

      // -- DATOS PARA OPTIMISTIC UI PAPA --
      const tipoGastoObj = catalogos.tipos_gasto.find(t => String(t.id_tipo_gasto) === String(data.tipo_gasto_id));
      const proveedorObj = catalogos.proveedores.find(p => String(p.id_proveedor) === String(data.proveedor_id));

      const datosVisuales = {
        fecha: fechaHoy,
        monto: parseFloat(data.monto).toFixed(2), 
        montoCrudo: data.monto,
        tipo: data.clasificacion || 'Variable',
        tipo_gasto: tipoGastoObj ? (tipoGastoObj.nombre_tipo_gasto || tipoGastoObj.nombre || 'General') : 'General',
        proveedor: proveedorObj ? (proveedorObj.nombre_proveedor || proveedorObj.nombre || 'No especificado') : 'No especificado',
        descripcion: data.descripcion_gasto,
        imagen: data.imagen
      };

      await dispatch(crearGasto({ datosVisuales, formData })).unwrap();

      onClose();
      setTimeout(() => reset(), 400);

    } catch (error) {
      console.log(error)
      procesarErrorApi(error, (status, mensaje, erroresFormulario) => {
        // Atrapamos el error 400 enviado por las nuevas reglas de gastos_api.php
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

  const descripcionActual = watch('descripcion_gasto');
  const montoActual = watch('monto');
  // Validamos extra que si requiere banco, la foto sea obligatoria
  const canSubmit = isValid && !isSubmitting && descripcionActual?.trim() && montoActual?.trim() 
                    && (!requiereBanco || (data => data.banco_id && data.referencia));

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
      referencia: 'Referencia',
      imagen: 'Comprobante'
    };
    
    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;

    Alert.alert(
      "Información incompleta",
      `Error en ${nombreLegible}: ${mensajeError}`
    );
  };

  return {
    control, errors, comprobanteUri, isSubmitting, canSubmit,
    handleSubmit, handleImagePick, removeImage, onSubmit, handleCancel,
    catalogos, requiereBanco, onError
  };
};