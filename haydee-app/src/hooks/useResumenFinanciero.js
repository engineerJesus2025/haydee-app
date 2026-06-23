import { useCallback } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchResumenFinanciero } from '../store/slices/mensualidadesSlice';

export const useResumenFinanciero = () => {
  const dispatch = useDispatch();

  const { resumen, loading, error } = useSelector(state => state.mensualidades);
  const { deudaTotal, gastado, recaudado, presupuestoTotal } = resumen;

  // patrón Cache-First
  const obtenerDatos = useCallback(async (forzar = false) => {
    // o si los datos están en 0 (es la primera vez que abre la app) --- Pull-to-Refresh
    if (forzar || (deudaTotal === 0 && gastado === 0)) {
      dispatch(fetchResumenFinanciero());
    }
  }, [dispatch]);

  const cargarDatosInicio = useCallback(async (forzar = false) => {
    if (forzar || (deudaTotal === 0 && gastado === 0)) {
      dispatch(fetchResumenFinanciero());
    }
  }, [dispatch]);

  return { 
    deudaTotal, 
    gastado, 
    recaudado,
    presupuestoTotal, 
    loading, 
    error, 
    obtenerDatos, 
    refrescarResumen: () => obtenerDatos(true) // Alias semántico para forzar recarga
  };
};