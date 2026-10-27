import { useState, useMemo, useEffect, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Alert } from 'react-native';
import { fetchPublicaciones, eliminarPublicacion } from '../store/slices/publicacionesSlice';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import { confirmarEliminacion, formatearMonto } from '../utils/confirmaciones';

export const useCarteleraVirtual = (colores) => {
  const dispatch = useDispatch();
  const { listaPublicaciones, cargando, error, hayMas, paginaActual } = useSelector(state => state.publicaciones);

  const [modalVisible, setModalVisible] = useState(false);
  const [modalEdicionVisible, setModalEdicionVisible] = useState(false);
  const [modalDetalleVisible, setModalDetalleVisible] = useState(false);

  const [publicacionSeleccionada, setPublicacionSeleccionada] = useState(null);
  const [publicacionAEditar, setPublicacionAEditar] = useState(null);

  const [cargandoMas, setCargandoMas] = useState(false);
  const [eliminando, setEliminando] = useState(false);

  const [fechaSeleccionada, setFechaSeleccionada] = useState('');

  const obtenerPublicaciones = (forzar = false) => {
    if (forzar) {
      dispatch(fetchPublicaciones({ pagina: 1, recargar: true }));
    } else if (listaPublicaciones.length === 0) {
      dispatch(fetchPublicaciones({ pagina: 1 }));
    }
  };

  const cargarMasPublicaciones = async () => {
    if (!cargando && !cargandoMas && hayMas && !fechaSeleccionada) {
      setCargandoMas(true);
      try {
        await dispatch(fetchPublicaciones({ pagina: paginaActual + 1 })).unwrap();
      } catch (err) {
        procesarErrorApi(err);
      } finally {
        setCargandoMas(false);
      }
    }
  };

  const markedDates = useMemo(() => {
    let dates = {};
    if (!listaPublicaciones) return dates;

    listaPublicaciones.forEach(post => {
      if (post.fecha) {
        const fechaFormateada = post.fecha.split(' ')[0];
        const tipo = post.tipo?.toLowerCase() || 'noticia';

        let dotColor = '#3498db';
        if (tipo === 'evento') dotColor = '#e74c3c';
        else if (tipo === 'aviso') dotColor = '#f39c12';

        if (!dates[fechaFormateada]) {
          dates[fechaFormateada] = { dots: [] };
        }

        const yaTieneEseTipo = dates[fechaFormateada].dots.some(dot => dot.key === tipo);
        if (!yaTieneEseTipo) {
          dates[fechaFormateada].dots.push({ key: tipo, color: dotColor });
        }
      }
    });

    if (fechaSeleccionada) {
      if (!dates[fechaSeleccionada]) dates[fechaSeleccionada] = { dots: [] };
      dates[fechaSeleccionada].selected = true;
      dates[fechaSeleccionada].selectedColor = colores?.primario || '#007BFF';
    }

    return dates;
  }, [listaPublicaciones, fechaSeleccionada, colores]);

  const listaPublicacionesMostrar = useMemo(() => {
    if (!fechaSeleccionada) return listaPublicaciones;

    return listaPublicaciones.filter(post => {
      if (!post.fecha) return false;
      const fechaFormateada = post.fecha.split(' ')[0];
      return fechaFormateada === fechaSeleccionada;
    });
  }, [listaPublicaciones, fechaSeleccionada]);

  // ============ MODAL NUEVA ============
  const abrirModalNuevaPublicacion = () => setModalVisible(true);
  const cerrarModalNuevaPublicacion = () => setModalVisible(false);

  // ============ MODAL DETALLE ============
  const abrirModalDetalle = useCallback((publicacion) => {
    setPublicacionSeleccionada(publicacion);
    setModalDetalleVisible(true);
  }, []);

  const cerrarModalDetalle = useCallback(() => {
    setPublicacionSeleccionada(null);
    setModalDetalleVisible(false);
  }, []);

  // ============ MODAL EDICIÓN ============
  const abrirModalEdicion = useCallback((publicacion) => {
    setPublicacionAEditar(publicacion);
    setPublicacionSeleccionada(null);
    setModalDetalleVisible(false);
    setModalEdicionVisible(true);
  }, []);

  const cerrarModalEdicion = useCallback((refrescar = false) => {
    setModalEdicionVisible(false);
    setPublicacionAEditar(null);
    if (refrescar) obtenerPublicaciones(true);
  }, []);

  // ============ ELIMINAR ============
  const handleEliminar = useCallback((publicacion) => {
    const tipoLabel = {
      evento: 'evento',
      aviso: 'aviso',
      noticia: 'noticia',
    }[publicacion.tipo?.toLowerCase()] || 'publicación';

    const lineas = [
      `Se eliminará ${tipoLabel === 'publicación' ? 'la publicación' : `el ${tipoLabel}`} "${publicacion.titulo}".`,
    ];

    if (publicacion.autor) {
      lineas.push(`Autor: ${publicacion.autor}.`);
    }

    lineas.push('Esta acción no se puede deshacer.');

    confirmarEliminacion({
      titulo: 'Eliminar publicación',
      mensaje: lineas.join('\n\n'),
      labelBoton: 'Sí, eliminar',
      onConfirm: async () => {
        setEliminando(true);
        try {
          await dispatch(eliminarPublicacion({ id_cartelera: publicacion.id })).unwrap();
          cerrarModalDetalle();
          obtenerPublicaciones(true);
        } catch (err) {
          procesarErrorApi(err);
        } finally {
          setEliminando(false);
        }
      },
    });
  }, [dispatch, cerrarModalDetalle]);

  useEffect(() => {
    obtenerPublicaciones();
  }, []);

  return {
    listaPublicacionesMostrar,
    markedDates,
    fechaSeleccionada,
    setFechaSeleccionada,
    cargando,
    error,
    cargandoMas,
    eliminando,
    obtenerPublicaciones,
    cargarMasPublicaciones,

    // modales
    modalVisible,
    modalDetalleVisible,
    modalEdicionVisible,
    publicacionSeleccionada,
    publicacionAEditar,

    // acciones
    abrirModalNuevaPublicacion,
    cerrarModalNuevaPublicacion,
    abrirModalDetalle,
    cerrarModalDetalle,
    abrirModalEdicion,
    cerrarModalEdicion,
    handleEliminar,
  };
};