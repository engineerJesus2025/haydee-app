import { useForm } from 'react-hook-form'
import { useDispatch } from 'react-redux'
import { useState, useEffect } from 'react'
import * as ImagePicker from 'expo-image-picker'
import { Alert } from 'react-native'
import { fetchPublicaciones, crearPublicacion } from '../store/slices/publicacionesSlice';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import { usePermisos } from './usePermisos';

export const useFormularioPublicaciones = (onClose, publicacionEditar = null) => { 
  const dispatch = useDispatch()
  const [permissionStatus, requestPermission] = ImagePicker.useMediaLibraryPermissions();
  const [isSubmitting, setIsSubmitting] = useState(false)
  const { usuario } = usePermisos();
  
  const {
    control,
    handleSubmit,
    formState: { errors, isValid },
    reset,
    setValue,
    watch,
    trigger,
    clearErrors
  } = useForm({
    mode: 'onChange',
    defaultValues: {
      titulo: '',
      descripcion: '',
      imagen: null,
      tipo: 'aviso'
    }
  })

  const imageUri = watch('imagen')
  const titulo = watch('titulo')
  const descripcion = watch('descripcion')
  const tipo = watch('tipo')

  useEffect(() => {
    if (publicacionEditar) {
      setValue('titulo', publicacionEditar.titulo || '')
      setValue('descripcion', publicacionEditar.descripcion || '')
      setValue('imagen', publicacionEditar.imagen || null)
      setValue('tipo', publicacionEditar.tipo || '')
    } else {
      reset({
        titulo: '',
        descripcion: '',
        imagen: null,
        tipo: ''
      })
    }
  }, [publicacionEditar, setValue, reset])

  useEffect(() => {
    (async () => {
      if (!permissionStatus?.granted) {
        await requestPermission();
      }
    })();
  }, [permissionStatus, requestPermission]);

  const handleImagePick = async () => {
    try {
      if (!permissionStatus?.granted) {
        const permissionResult = await requestPermission();
        if (!permissionResult.granted) {
          Alert.alert('Permisos necesarios', 'Se necesitan permisos de galería para seleccionar una imagen.');
          return;
        }
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        aspect: [4, 3], // Formato horizontal para noticias en la cartelera
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
      clearErrors('imagen');
  }, [clearErrors]);

  const removeImage = () => {
    setValue('imagen', null, { shouldValidate: true })
  }

  const onSubmit = async (data) => {
    if (isSubmitting) return;

    // Pausamos para que tenga tiempo de pintar el Spinner
    await new Promise(resolve => setTimeout(resolve, 50)); // Esto se llama THREAD YIELDING mi estimado

    setIsSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('operacion', 'registrar_cartelera');
      formData.append('titulo', data.titulo);
      formData.append('descripcion', data.descripcion);
      formData.append('usuario_id', usuario?.id_usuario); // Temporal hasta usar Tokens de sesión

      let prioridad = 3; // Por defecto Noticia
      if (data.tipo === 'aviso') prioridad = 1;
      else if (data.tipo === 'evento') prioridad = 2;
      formData.append('prioridad', prioridad);

      // Empaquetar la imagen para React Native
      if (data.imagen) {
        let localUri = data.imagen;
        let filename = localUri.split('/').pop();

        let match = /\.(\w+)$/.exec(filename);
        let type = match ? `image/${match[1]}` : `image`;

        formData.append('imagen', {
          uri: localUri,
          name: filename,
          type: type
        });
      }

      const datosVisuales = {
        titulo: data.titulo,
        descripcion: data.descripcion,
        tipo: data.tipo,
        autor: usuario?.nombre_completo || usuario?.nombre || 'Administrador', 
        imagen: data.imagen
      };

      // Despachamos la acción al servidor y esperamos que termine (.unwrap() extrae el resultado o lanza el error)
      await dispatch(crearPublicacion({ datosVisuales, formData })).unwrap();

      // Cerramos modal y limpiamos
      onClose();
      setTimeout(() => {
        resetForm();
      }, 400);

    } catch (error) {
      procesarErrorApi(error, (status, mensaje, erroresFormulario) => {
        // Si es un error 400 y trae detalles por campo
        if (status === 400 && erroresFormulario) {
          Object.keys(erroresFormulario).forEach(campo => {
            setError(campo, {
              type: 'server',
              message: erroresFormulario[campo][0]
            });
          });
          
          //Feedback general
          Alert.alert('Datos Inválidos', mensaje || 'Revise los campos marcados en rojo.');
          
          return true;
        }
        
        return false; // Si es un 401, 403, 500, etc.
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const resetForm = () => {
    reset({
      titulo: '',
      descripcion: '',
      imagen: null,
      tipo: ''
    })
    setIsSubmitting(false)
  }

  const handleCancel = () => {
    onClose()
    setTimeout(() => {
      resetForm()
    }, 400)
  }

  const onError = (errors) => {
    const primerCampoConError = Object.keys(errors)[0];
    const mensajeError = errors[primerCampoConError]?.message || 'Por favor, completa este campo correctamente.';
    
    const nombresCampos = {
      titulo: 'Título',
      descripcion: 'Descripción',
      tipo: 'Tipo de Publicación'
    };
    
    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;

    Alert.alert(
      "Información incompleta",
      `Error en ${nombreLegible}: ${mensajeError}`
    );
  };

  const canSubmit = isValid && !isSubmitting && titulo.trim() && descripcion.trim() && tipo;

  return {
    control,
    errors,
    isValid,
    isSubmitting,
    canSubmit,
    imageUri,
    titulo,
    descripcion,
    handleSubmit,
    setValue,
    trigger,
    handleImagePick,
    removeImage,
    onSubmit,
    handleCancel,
    resetForm,
    onError
  }
}