import { useEffect, useState, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { fetchPublicaciones } from '../store/slices/publicacionesSlice';
import { fetchEstadoCuenta } from '../store/slices/pagosSlice';

import { useResumenFinanciero } from './useResumenFinanciero';
import { useEventos } from './useEventos';
import { usePermisos } from './usePermisos';

import { registrarSuscripcionPush } from '../servicios/notificacionesPush';
import { marcarPushRegistrado } from '../store/slices/usuarioSlice';

export const useInicio = () => {
  const dispatch = useDispatch();

  const { deudaTotal, gastado, presupuestoTotal, loading: loadingFinanzas, error, obtenerDatos: obtenerFinanzas, recaudado } = useResumenFinanciero();
  const { eventos } = useEventos();
  const { puedeVerGastos, puedeVerMensualidad, puedeVerCartelera, esAdmin } = usePermisos();
  
  const { listaPublicaciones, loading: loadingCartelera, hayMas } = useSelector(state => state.publicaciones);
  const { totalDeuda: deudaPropietario, loading: loadingDeudaPersonal } = useSelector(state => state.pagos);

  const { user, pushRegistrado } = useSelector(state => state.usuario);

  const [refreshing, setRefreshing] = useState(false);

  const [modalDetalleVisible, setModalDetalleVisible] = useState(false);
  const [publicacionSeleccionada, setPublicacionSeleccionada] = useState(null);

  const cargarDatosInicio = useCallback(async (forzar = false) => {
    if (forzar) setRefreshing(true);
    
    // Todos pueden ver publicaciones
    if (puedeVerCartelera){
      dispatch(fetchPublicaciones({ pagina: 1, recargar: true }));
    }
    
    if (esAdmin || puedeVerMensualidad) {
      obtenerFinanzas(forzar); // El admin pide las finanzas globales
    } else {
      dispatch(fetchEstadoCuenta()); // El propietario pide su deuda personal
    }
    
    if (forzar) setRefreshing(false);
  }, [dispatch, obtenerFinanzas, puedeVerGastos, puedeVerMensualidad]);

  useEffect(() => {
    cargarDatosInicio();
  }, []);

  useEffect(() => {
    if (user && user.id_usuario && !pushRegistrado) {
      const registrar = async () => {
        try {
          await registrarSuscripcionPush(); 
          dispatch(marcarPushRegistrado());
        } catch (error) {
          console.warn("Fallo al registrar Push en useInicio:", error);
        }
      };
      
      registrar();
    }
  }, [user, pushRegistrado, dispatch]);

  const abrirModalDetalle = useCallback((publicacion) => {
    setPublicacionSeleccionada(publicacion);
    setModalDetalleVisible(true);
  }, []);

  const cerrarModalDetalle = useCallback(() => {
    setPublicacionSeleccionada(null);
    setModalDetalleVisible(false);
  }, []);

  // Para optimizar el scroll
  // Lo meti aqui porque las dimensiones dependen de un estado administrado por el hook
  // Sino estuviera en la screen
  const obtenerItemLayout = useCallback((dimensiones) => {
    // El hook solo opera la lógica de negocio y condicionales
    return (data, index) => {
      const alturaHeaderEventos = (eventos && eventos.length > 0) ? dimensiones.alturaEventos : 0;
      const alturaTotalHeader = dimensiones.alturaHeaderBase + alturaHeaderEventos;

      return {
        length: dimensiones.alturaItem,
        offset: (dimensiones.alturaItem * index) + alturaTotalHeader,
        index,
      };
    };
  }, [eventos]);

  return {
    deudaTotal: (esAdmin || puedeVerMensualidad) ? deudaTotal : deudaPropietario, 
    gastado, 
    presupuestoTotal, 
    loadingFinanzas: loadingFinanzas || loadingDeudaPersonal,
    error,
    eventos,
    listaPublicaciones, 
    loadingCartelera, 
    refreshing,
    cargarDatosInicio,
    obtenerItemLayout,
    modalDetalleVisible,
    publicacionSeleccionada,
    abrirModalDetalle,
    cerrarModalDetalle,
    puedeVerGastos,
    puedeVerMensualidad,
    recaudado,
    esAdmin
  };
};