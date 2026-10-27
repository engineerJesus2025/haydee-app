import { useForm } from 'react-hook-form';
import { useDispatch } from 'react-redux';
import { useState, useEffect, useMemo } from 'react';
import * as ImagePicker from 'expo-image-picker';
import { Alert } from 'react-native';
import { crearPublicacion, modificarPublicacion } from '../store/slices/publicacionesSlice';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import { usePermisos } from './usePermisos';

export const useFormularioPublicaciones = (onClose, publicacionEditar = null) => {
  const dispatch = useDispatch();
  const [permissionStatus, requestPermission] = ImagePicker.useMediaLibraryPermissions();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { usuario } = usePermisos();

  const esEdicion = !!publicacionEditar;

  // ============ VALORES INICIALES (calculados una sola vez) ============
  const valoresIniciales = useMemo(() => {
    if (esEdicion) {
      return {
        titulo:      publicacionEditar.titulo || '',
        descripcion: publicacionEditar.descripcion || '',
        // El tipo ya viene mapeado ('aviso'/'evento'/'noticia') desde el slice
        tipo:        publicacionEditar.tipo || 'noticia',
        // Guardamos la URL de la imagen existente (http://...)
        imagen:      publicacionEditar.imagen || null,
      };
    }
    return {
      titulo: '',
      descripcion: '',
      imagen: null,
      tipo: 'aviso',
    };
  }, [esEdicion, publicacionEditar]);

  const {
    control,
    handleSubmit,
    formState: { errors, isValid },
    reset,
    setValue,
    watch,
    trigger,
    setError,
    clearErrors,
  } = useForm({
    mode: 'onChange',
    defaultValues: valoresIniciales,
  });

  const imageUri  = watch('imagen');
  const titulo    = watch('titulo');
  const descripcion = watch('descripcion');
  const tipo      = watch('tipo');

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
        aspect: [4, 3],
        quality: 0.4,
        base64: true,
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        setValue('imagen', result.assets[0].uri, { shouldValidate: true });
        clearErrors('imagen');
      }
    } catch (error) {
      procesarErrorApi(error);
    }
  };

  const removeImage = () => {
    setValue('imagen', null, { shouldValidate: true });
  };

  const onSubmit = async (data) => {
    if (isSubmitting) return;
    await new Promise(resolve => setTimeout(resolve, 50));
    setIsSubmitting(true);

    try {
      const formData = new FormData();
      formData.append('operacion', esEdicion ? 'modificar_cartelera' : 'registrar_cartelera');
      formData.append('titulo', data.titulo);
      formData.append('descripcion', data.descripcion);
      formData.append('usuario_id', usuario?.id_usuario);

      if (esEdicion) {
        formData.append('id_cartelera', publicacionEditar.id);
      }

      let prioridad = 3;
      if (data.tipo === 'aviso') prioridad = 1;
      else if (data.tipo === 'evento') prioridad = 2;
      formData.append('prioridad', prioridad);

      // ============ MANEJO DE IMAGEN ============
      const esImagenNueva = data.imagen && data.imagen.startsWith('file://');
      const fueEliminada = esEdicion && publicacionEditar.imagen && !data.imagen;

      if (esImagenNueva) {
        // Caso 1: nueva imagen
        const localUri = data.imagen;
        const filename = localUri.split('/').pop();
        const match = /\.(\w+)$/.exec(filename);
        const type = match ? `image/${match[1]}` : 'image';

        formData.append('imagen', {
          uri: localUri,
          name: filename,
          type: type,
        });
      } else if (fueEliminada) {
        // Caso 2: quitar la imagen existente sin reemplazarla
        formData.append('eliminar_imagen', '1');
      }
      // Caso 3: conservar la actual → no enviamos nada

      // ============ DATOS PARA OPTIMISTIC UI ============
      const datosVisuales = {
        titulo: data.titulo,
        descripcion: data.descripcion,
        tipo: data.tipo,
        autor: usuario?.nombre_completo || usuario?.nombre || 'Administrador',
        imagen: esImagenNueva ? data.imagen
              : (fueEliminada ? null : publicacionEditar?.imagen || null),
        imagen_nombre: esImagenNueva ? null : (fueEliminada ? null : publicacionEditar?.imagen_nombre || null),
      };

      if (esEdicion) {
        await dispatch(modificarPublicacion({
          id_cartelera: publicacionEditar.id,
          formData,
          datosVisuales,
        })).unwrap();
      } else {
        await dispatch(crearPublicacion({ datosVisuales, formData })).unwrap();
      }

      onClose(esEdicion); // true = refrescar
      setTimeout(() => reset(valoresIniciales), 400);

    } catch (error) {
      procesarErrorApi(error, (status, mensaje, erroresFormulario) => {
        if (status === 400 && erroresFormulario) {
          Object.keys(erroresFormulario).forEach(campo => {
            setError(campo, {
              type: 'server',
              message: erroresFormulario[campo][0]
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
    onClose(false);
    setTimeout(() => reset(valoresIniciales), 400);
  };

  const onError = (errs) => {
    const primerCampoConError = Object.keys(errs)[0];
    const mensajeError = errs[primerCampoConError]?.message || 'Por favor, completa este campo correctamente.';

    const nombresCampos = {
      titulo: 'Título',
      descripcion: 'Descripción',
      tipo: 'Tipo de Publicación'
    };

    const nombreLegible = nombresCampos[primerCampoConError] || primerCampoConError;

    Alert.alert(
      'Información incompleta',
      `Error en ${nombreLegible}: ${mensajeError}`
    );
  };

  const canSubmit = isValid && !isSubmitting && titulo?.trim() && descripcion?.trim() && tipo;

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
    onError,
  };
};