import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchMensualidades, fetchDetalleMensualidad } from '../store/slices/mensualidadesSlice';
import { useResumenFinanciero } from './useResumenFinanciero';
import { procesarErrorApi } from '../utils/gestorErroresUI';

export const useMensualidades = () => {
  const dispatch = useDispatch();
  const { listaMensualidades, loading: loadingLista, error } = useSelector(state => state.mensualidades);
  const { obtenerDatos: obtenerResumen, loading: loadingResumen, gastado, presupuestoTotal, recaudado } = useResumenFinanciero();

  const [modalVisible, setModalVisible] = useState(false);
  const [mensualidadSeleccionada, setMensualidadSeleccionada] = useState(null);
  const [cargandoDetalle, setCargandoDetalle] = useState(null);

  const obtenerMensualidades = (forzar = false) => {
    if (forzar || listaMensualidades.length === 0) {
      dispatch(fetchMensualidades());
    }
    obtenerResumen(forzar);
  };

  const manejarVerDetalles = async (mensualidad) => {
    try {
      let dataActualizada = mensualidad;
      
      // Si aún no hemos descargado la lista de apartamentos para este mes
      if (!mensualidad.apartamentos) {
        setCargandoDetalle(true);
        const result = await dispatch(fetchDetalleMensualidad({
          id: mensualidad.id,
          mes: mensualidad.mes_raw,
          anio: mensualidad.anio_raw
        })).unwrap();
        
        // Inyectamos la data fresca para que el modal reaccione inmediatamente
        dataActualizada = { ...mensualidad, apartamentos: result.apartamentos };
      }
      
      setMensualidadSeleccionada(dataActualizada);
      setModalVisible(true);
    } catch (error) {
      procesarErrorApi(error);
    } finally {
      setCargandoDetalle(null);
    }
  };

  useEffect(() => {
    obtenerMensualidades();
  }, []);

  return {
    listaMensualidades,
    loading: loadingLista || loadingResumen,
    gastado,
    presupuestoTotal,
    error,
    obtenerMensualidades,
    manejarVerDetalles,
    modalVisible,
    setModalVisible,
    mensualidadSeleccionada,
    cargandoDetalle,
    recaudado
  };
};