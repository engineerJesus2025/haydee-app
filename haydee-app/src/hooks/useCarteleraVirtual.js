import { useState, useMemo, useEffect, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { fetchPublicaciones } from '../store/slices/publicacionesSlice';

// Recibimos los colores como parámetro
export const useCarteleraVirtual = (colores) => {
  const dispatch = useDispatch();
  const { listaPublicaciones, cargando, error, hayMas, paginaActual } = useSelector(state => state.publicaciones);

  const [modalVisible, setModalVisible] = useState(false);
  const [modalDetalleVisible, setModalDetalleVisible] = useState(false);
  const [publicacionSeleccionada, setPublicacionSeleccionada] = useState(null);
  
  // estado del calendario
  const [fechaSeleccionada, setFechaSeleccionada] = useState('');

  const [cargandoMas, setCargandoMas] = useState(false);

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
      } catch (error) {
        procesarErrorApi(error);
      } finally {
        setCargandoMas(false);
      }
    }
  };

  // lógica pesada de los puntos del calendario
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

  // Lógica de filtrado de la lista
  const listaPublicacionesMostrar = useMemo(() => {
    if (!fechaSeleccionada) return listaPublicaciones; 
    
    return listaPublicaciones.filter(post => {
      if (!post.fecha) return false;
      const fechaFormateada = post.fecha.split(' ')[0];
      return fechaFormateada === fechaSeleccionada;
    });
  }, [listaPublicaciones, fechaSeleccionada]);

  // Funciones de modales
  const abrirModalNuevaPublicacion = () => setModalVisible(true);
  const cerrarModalNuevaPublicacion = () => setModalVisible(false);

  const abrirModalDetalle = useCallback((publicacion) => {
    setPublicacionSeleccionada(publicacion);
    setModalDetalleVisible(true);
  }, []);

  const cerrarModalDetalle = useCallback(() => {
    setPublicacionSeleccionada(null);
    setModalDetalleVisible(false);
  }, []);

  const abrirModalEdicion = (publicacion) => {
    setPublicacionSeleccionada(publicacion);
    setModalEdicionVisible(true);
  };
  const cerrarModalEdicion = () => {
    setPublicacionSeleccionada(null);
    setModalEdicionVisible(false);
  };
  const handleGuardarEdicion = () => cerrarModalEdicion();

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
    obtenerPublicaciones,
    cargarMasPublicaciones,
    modalVisible,
    modalDetalleVisible,
    publicacionSeleccionada,
    abrirModalNuevaPublicacion,
    cerrarModalNuevaPublicacion,
    abrirModalDetalle,
    cerrarModalDetalle,
    handleGuardarEdicion
  };
};