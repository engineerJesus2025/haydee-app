import { useState, useEffect, useCallback } from 'react';
import clienteApi from '../utils/clienteApi';
import { formatearMesAnio } from '../utils/dateUtils';

const URL_BASE = process.env.EXPO_PUBLIC_URL_BASE;

export const useDetallePago = (id_registro) => {
  const [pago, setPago] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  const obtenerDetalle = useCallback(async () => {
    if (!id_registro) return;
    setCargando(true);
    setError(null);

    try {
      const respuesta = await clienteApi.get('', {
        params: { 
          endpoint: 'pagos', 
          operacion: 'consultar_pago', 
          id_pago: id_registro 
        }
      });

      if (respuesta.data?.estatus && respuesta.data.datos) {
        const dataBackend = respuesta.data.datos;
        
        const detallesProcesados = (dataBackend.detalles || []).map(det => {
          let urlImagen = null;
          if (det.imagen && det.imagen !== 'default.png') {
            urlImagen = `${URL_BASE}/recursos/img/pagos/${det.imagen}`;
          }
          return {
            id_detalle: det.id_detalle_pago,
            fecha: det.fecha,
            monto: `${parseFloat(det.monto).toFixed(2)} Bs.`,
            tipo_pago: det.tipo_pago,
            banco: det.nombre_banco || 'No aplica',
            referencia: det.referencia || 'No aplica',
            imagen: urlImagen
          };
        });

        const mensualidadCruda = dataBackend.mensualidad || '';
        const [mes, anio] = mensualidadCruda.includes('/') ? mensualidadCruda.split('/') : ['', ''];
        
        const pagoMapeado = {
          id: dataBackend.id_pago,
          estado: dataBackend.estado,
          fecha: dataBackend.fecha,
          monto: `${parseFloat(dataBackend.monto_total || 0).toFixed(2)} Bs.`,
          apartamento: `Nº ${dataBackend.nro_apartamento}`,
          mensualidad: (mes && anio) ? formatearMesAnio(mes, anio) : mensualidadCruda,
          observacion: dataBackend.observacion || 'Sin observaciones',
          monto_mensualidad: `${parseFloat(dataBackend.monto_mensualidad || 0).toFixed(2)} Bs.`,
          monto_abonado: `${parseFloat(dataBackend.monto_abonado || 0).toFixed(2)} Bs.`,
          detalles: detallesProcesados 
        };

        setPago(pagoMapeado);
      } else {
        throw new Error(respuesta.data?.mensaje || 'No se pudo cargar el recibo de pago.');
      }
    } catch (err) {
      setError(err.message || 'Error de conexión.');
    } finally {
      setCargando(false);
    }
  }, [id_registro]);

  useEffect(() => {
    obtenerDetalle();
  }, [obtenerDetalle]);

  return { pago, cargando, error, reintentar: obtenerDetalle };
};