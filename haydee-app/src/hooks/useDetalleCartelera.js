import { useState, useEffect, useCallback } from 'react';
import clienteApi from '../utils/clienteApi';

const URL_BASE = process.env.EXPO_PUBLIC_URL_BASE;

export const useDetalleCartelera = (id_registro) => {
  const [publicacion, setPublicacion] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  const obtenerDetalle = useCallback(async () => {
    if (!id_registro) return;
    setCargando(true);
    setError(null);

    try {
      const respuesta = await clienteApi.get('', {
        params: { 
          endpoint: 'cartelera', 
          operacion: 'consultar_cartelera', 
          id_cartelera: id_registro 
        }
      });

      if (respuesta.data?.estatus && respuesta.data.datos) {
        const item = Array.isArray(respuesta.data.datos) ? respuesta.data.datos[0] : respuesta.data.datos;

        const publicacionMapeada = {
          id: item.id_cartelera,
          titulo: item.titulo,
          descripcion: item.descripcion,
          fecha: item.fecha, 
          tipo: item.prioridad == 1 ? 'aviso' : (item.prioridad == 2 ? 'evento' : 'noticia'),
          autor: item.nombre_usuario,
          imagen: item.imagen ? `${URL_BASE}/recursos/img/cartelera_virtual/${item.imagen}` : null
        };

        setPublicacion(publicacionMapeada);
      } else {
        throw new Error(respuesta.data?.mensaje || 'No se pudo cargar la publicación.');
      }
    } catch (err) {
      setError(err.message || 'Error de conexión con el servidor.');
    } finally {
      setCargando(false);
    }
  }, [id_registro]);

  useEffect(() => {
    obtenerDetalle();
  }, [obtenerDetalle]);

  return { publicacion, cargando, error, reintentar: obtenerDetalle };
};