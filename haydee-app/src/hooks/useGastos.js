import { useState, useEffect, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Alert } from 'react-native';
import { fetchGastos, eliminarGasto } from '../store/slices/gastosSlice';
import clienteApi from '../utils/clienteApi';
import { procesarErrorApi } from '../utils/gestorErroresUI';
import { confirmarEliminacion, formatearMonto } from '../utils/confirmaciones';

const URL_BASE = process.env.EXPO_PUBLIC_URL_BASE;

export const useGastos = () => {
  const dispatch = useDispatch();

  const { listaGastos, totalGastadoMes, loading, error } = useSelector(state => state.gastos);

  const fechaActual = new Date();
  const [mesFiltro, setMesFiltro] = useState(fechaActual.getMonth() + 1);
  const [anioFiltro, setAnioFiltro] = useState(fechaActual.getFullYear());
  const [periodosDisponibles, setPeriodosDisponibles] = useState([]);

  const [modalDetalleVisible, setModalDetalleVisible] = useState(false);
  const [modalGastoVisible, setModalGastoVisible] = useState(false);
  const [modalEdicionVisible, setModalEdicionVisible] = useState(false);

  const [gastoSeleccionado, setGastoSeleccionado] = useState(null);
  const [gastoAEditar, setGastoAEditar] = useState(null);

  const [cargandoDetalle, setCargandoDetalle] = useState(false);
  const [eliminando, setEliminando] = useState(false);
  const [inicializando, setInicializando] = useState(true);

  const obtenerGastos = (forzar = false, mes = mesFiltro, anio = anioFiltro) => {
    if (forzar || listaGastos.length === 0) {
      dispatch(fetchGastos({ mes, anio }));
    }
  };

  const cargarPeriodos = useCallback(async () => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { endpoint: 'gastos', operacion: 'obtener_periodos' }
      });

      if (respuesta.data.estatus) {
        const periodosBD = respuesta.data.datos;
        setPeriodosDisponibles(periodosBD);

        if (periodosBD.length > 0) {
          const existeMesActual = periodosBD.some(
            p => Number(p.mes) === mesFiltro && Number(p.anio) === anioFiltro
          );

          if (!existeMesActual) {
            const ultimoPeriodo = periodosBD[0];
            setMesFiltro(Number(ultimoPeriodo.mes));
            setAnioFiltro(Number(ultimoPeriodo.anio));
            obtenerGastos(true, Number(ultimoPeriodo.mes), Number(ultimoPeriodo.anio));
            setInicializando(false);
            return;
          }
        }
      }
    } catch (err) {
      const esErrorDeRed =
        err.message === 'Network Error' ||
        err.code === 'ERR_NETWORK' ||
        (!err.response && err.request);
      if (!esErrorDeRed) procesarErrorApi(err);
    }

    setInicializando(false);
    obtenerGastos();
  }, [mesFiltro, anioFiltro]);

  const cambiarFiltroFecha = (nuevoMes, nuevoAnio) => {
    setMesFiltro(nuevoMes);
    setAnioFiltro(nuevoAnio);
    obtenerGastos(true, nuevoMes, nuevoAnio);
  };

  const abrirDetalles = async (gastoCabecera) => {
    if (cargandoDetalle) return;
    setCargandoDetalle(true);

    try {
      const respuesta = await clienteApi.get('', {
        params: {
          endpoint: 'gastos',
          operacion: 'consultar_gasto',
          id_gasto: gastoCabecera.id
        }
      });

      if (respuesta.data.estatus) {
        const dataBackend = respuesta.data.datos;   // { gasto: {...}, detalles: [...] }
        const cabecera = dataBackend.gasto || {};   // <-- antes se descartaba

        const detallesProcesados = (dataBackend.detalles || []).map(det => {
          let urlImagen = null;
          let nombreImagen = null;
          if (det.imagen && det.imagen !== 'default.png') {
            urlImagen = `${URL_BASE}/recursos/img/gastos/${det.imagen}`;
            nombreImagen = det.imagen;
          }
          return {
            id_detalle: det.id_detalle_gasto || det.id_detalle,
            fecha: det.fecha ? det.fecha.split(' ')[0] : null,   // solo YYYY-MM-DD
            monto: `${parseFloat(det.monto).toFixed(2)} Bs.`,
            monto_crudo: parseFloat(det.monto).toFixed(2),
            metodo_pago: det.metodo_pago,
            banco: det.nombre_cuenta || 'No aplica',
            cuenta_id: det.cuenta_id ?? '',
            referencia: det.referencia || 'No aplica',
            referencia_cruda: det.referencia || '',
            imagen: urlImagen,
            imagen_nombre: nombreImagen,
          };
        });

        setGastoSeleccionado({
          ...gastoCabecera,
          // Del backend (para edición)
          presupuesto_id: cabecera.presupuesto_id ?? '',
          concepto_id:    cabecera.concepto_id ?? '',
          proveedor_id:   cabecera.proveedor_id ?? '',
          clasificacion:  cabecera.clasificacion ?? gastoCabecera.tipo ?? 'VARIABLE',
          descripcion:    cabecera.descripcion_gasto ?? gastoCabecera.descripcion,
          observacion:    cabecera.descripcion_gasto ?? '',  // alias para ModalDetalles
          detalles:       detallesProcesados
        });

        setModalDetalleVisible(true);
      } else {
        procesarErrorApi({ tipo: 'SERVIDOR', status: 400, mensaje: respuesta.data.mensaje });
      }
    } catch (err) {
      procesarErrorApi(err);
    } finally {
      setCargandoDetalle(false);
    }
  };

  const cerrarDetalles = () => {
    setModalDetalleVisible(false);
    setGastoSeleccionado(null);
  };

  // ============ EDICIÓN ============
  const abrirEdicion = (gastoCompleto) => {
    setGastoAEditar(gastoCompleto);
    setGastoSeleccionado(null);
    setModalDetalleVisible(false);
    setModalEdicionVisible(true);
  };

  const cerrarEdicion = (refrescar = false) => {
    setModalEdicionVisible(false);
    setGastoAEditar(null);
    if (refrescar) obtenerGastos(true);
  };

  // ELIMINACIÓN
  const handleEliminar = (gasto) => {
    const monto = formatearMonto(gasto.monto);
    const descripcion = gasto.descripcion ? `"${gasto.descripcion}"` : 'seleccionado';
    const proveedor = gasto.proveedor ? ` al proveedor ${gasto.proveedor}` : '';
    const nComprobantes = gasto.detalles?.length || 0;

    const lineas = [
      `Se eliminará el gasto ${descripcion}${proveedor}${monto ? ` por ${monto}` : ''}.`,
    ];

    if (nComprobantes > 0) {
      lineas.push(
        `Se eliminarán también ${nComprobantes} detalle${nComprobantes !== 1 ? 's' : ''} adjunto${nComprobantes !== 1 ? 's' : ''}.`
      );
    }

    lineas.push('Esta acción no se puede deshacer.');

    confirmarEliminacion({
      titulo: 'Eliminar gasto',
      mensaje: lineas.join('\n\n'),
      labelBoton: 'Sí, eliminar',
      onConfirm: async () => {
        setEliminando(true);
        try {
          await dispatch(eliminarGasto({ id_gasto: gasto.id })).unwrap();
          cerrarDetalles();
          obtenerGastos(true);
        } catch (err) {
          procesarErrorApi(err);
        } finally {
          setEliminando(false);
        }
      },
    });
  };

  const abrirNuevoGasto = () => setModalGastoVisible(true);
  const cerrarNuevoGasto = () => setModalGastoVisible(false);

  useEffect(() => {
    if (typeof cargarPeriodos === 'function') {
      cargarPeriodos();
    }
  }, []);

  return {
    listaGastos,
    totalGastadoMes,
    loading,
    error,
    cargandoDetalle,
    eliminando,
    modalDetalleVisible,
    modalGastoVisible,
    modalEdicionVisible,
    gastoSeleccionado,
    gastoAEditar,
    mesFiltro,
    anioFiltro,
    cambiarFiltroFecha,
    obtenerGastos,
    abrirDetalles,
    cerrarDetalles,
    abrirEdicion,
    cerrarEdicion,
    handleEliminar,
    abrirNuevoGasto,
    cerrarNuevoGasto,
    periodosDisponibles,
    inicializando
  };
};