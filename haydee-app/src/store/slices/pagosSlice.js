import { 
  createSlice, 
  createAsyncThunk, 
  isPending, 
  isRejected, 
  isFulfilled 
} from '@reduxjs/toolkit';
import clienteApi from '../../utils/clienteApi';

export const fetchPagos = createAsyncThunk(
  'pagos/fetchPagos',
  async ({ mes, anio} = {}, { getState, rejectWithValue }) => {
    try {

      const respuesta = await clienteApi.get('', {
        params: {
          endpoint: 'pagos',
          operacion: 'listar_pagos_mes',
          mes:mes,
          anio:anio
        }
      });

      const json = respuesta.data;
      
      if (json.estatus) {
        return json.datos.map(item => ({
          id: item.id_pago,
          estado: item.estado,
          fecha: item.ultima_fecha ? item.ultima_fecha.split(' ')[0] : 'Sin fecha',
          monto: `${parseFloat(item.monto_total || 0).toFixed(2)} Bs.`,
          mensualidad: item.periodos || 'Varias mensualidades',
          apartamento: item.apartamento || 'No asignado',
          tipo_pago: item.tipo_pago_predominante,
          banco: 'Ver detalles',
          referencia: 'Ver detalles',
          imagen: null 
        }));
      } else {
        return rejectWithValue(json.mensaje);
      }
    } catch (error) {
      const esErrorDeRed = !error.response && error.request;

      const errorPlano = {
        tipo: esErrorDeRed ? 'RED' : 'API_ERROR', 
        mensaje: error.response?.data?.mensaje || error.message || 'Error de conexión con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null 
      };
      
      return rejectWithValue(errorPlano);
    }
  }
);

export const fetchEstadoCuenta = createAsyncThunk(
  'pagos/fetchEstadoCuenta',
  async (_, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { endpoint: 'pagos', operacion: 'consultar_estado_cuenta' }
      });
      if (respuesta.data.estatus) {
        return respuesta.data.datos;
      } else {
        return rejectWithValue(respuesta.data.mensaje);
      }
    } catch (error) {
      const esErrorDeRed = !error.response && error.request;

      const errorPlano = {
        tipo: esErrorDeRed ? 'RED' : 'API_ERROR', 
        mensaje: error.response?.data?.mensaje || error.message || 'Error de conexión con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null 
      };
      return rejectWithValue(errorPlano);
    }
  }
);

export const registrarPagoServidor = createAsyncThunk(
  'pagos/registrarPagoServidor',
  async ({ datosVisuales, formData }, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'pagos' }
      });

      const json = respuesta.data;
      
      if (json.estatus) {
        return {
          ...datosVisuales,
          id: json.id 
        };
      } else {
        return rejectWithValue(json.mensaje || 'Error al registrar el pago');
      }
    } catch (error) {
      const esErrorDeRed = !error.response && error.request;

      const errorPlano = {
        tipo: esErrorDeRed ? 'RED' : 'API_ERROR', 
        mensaje: error.response?.data?.mensaje || error.message || 'Error de conexión con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null 
      };
      return rejectWithValue(errorPlano);
    }
  }
);

export const actualizarEstadoPagoServidor = createAsyncThunk(
  'pagos/actualizarEstado',
  async ({ id_pago, nuevoEstado }, { rejectWithValue }) => {
    try {
      // Usamos FormData para que PHP lo reciba correctamente en $_POST
      const formData = new FormData();
      formData.append('operacion', 'cambiar_estado_pago');
      formData.append('id_pago', id_pago);
      formData.append('estado', nuevoEstado);
      formData.append('_method', 'PUT');

      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'pagos' },
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      if (respuesta.data.estatus) {
        return { id: id_pago, estado: nuevoEstado };
      }
      
      return rejectWithValue(respuesta.data.mensaje);
    } catch (error) {
      const esErrorDeRed = !error.response && error.request;

      const errorPlano = {
        tipo: esErrorDeRed ? 'RED' : 'API_ERROR', 
        mensaje: error.response?.data?.mensaje || error.message || 'Error de conexión con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null 
      };
      return rejectWithValue(errorPlano);
    }
  }
);

const pagosSlice = createSlice({
  name: 'pagos',
  initialState: {
    listaPagos: [],
    listaDeudas: [],
    totalDeuda: 0,
    loadingDeudas: false,
    loading: false,
    error: null
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPagos.fulfilled, (state, action) => {
        state.loading = false;
        state.listaPagos = action.payload;
      })
      .addCase(registrarPagoServidor.fulfilled, (state, action) => {
        // Agregamos el nuevo pago al inicio de la lista
        state.listaPagos.unshift(action.payload);
      })
      .addCase(fetchEstadoCuenta.fulfilled, (state, action) => {
        state.loadingDeudas = false;
        state.listaDeudas = action.payload;
        // Calculamos el total de la deuda sumando los montos pendientes
        state.totalDeuda = action.payload.reduce((total, item) => total + parseFloat(item.pendiente), 0);
      })
      .addCase(actualizarEstadoPagoServidor.fulfilled, (state, action) => {
        // Solo si el backend responde con éxito, actualizamos la vista
        const pago = state.listaPagos.find(p => p.id === action.payload.id);
        if (pago) {
          pago.estado = action.payload.estado;
        }
      })
      .addMatcher(
        isPending(fetchPagos, fetchEstadoCuenta, actualizarEstadoPagoServidor),
        (state) => {
          state.loading = true;
          state.error = null;
        }
      )
      // Apaga el "loading" cuando cualquiera de estos termina con éxito
      .addMatcher(
        isFulfilled(fetchPagos, fetchEstadoCuenta, actualizarEstadoPagoServidor),
        (state) => {
          state.loading = false;
        }
      )
      // Atrapa los errores SOLO si provienen de estos thunks específicos
      .addMatcher(
        isRejected(fetchPagos, fetchEstadoCuenta, actualizarEstadoPagoServidor),
        (state, action) => {
          state.loading = false;
          
          if (action.payload && typeof action.payload === 'object' && action.payload.mensaje) {
            
            if (action.payload.tipo === 'RED' || action.payload.mensaje === 'Network Error') {
              state.error = 'No hay conexión a internet o el servidor no responde. Verifica tu señal.';
            } else {
              state.error = action.payload.mensaje;
            }
            
          } else if (typeof action.payload === 'string') {
            // Por si acaso llega como texto plano (en english por el axios)
            state.error = action.payload === 'Network Error' 
              ? 'No hay conexión a internet o el servidor no responde. Verifica tu señal.' 
              : action.payload;
          } else {
            state.error = 'Ocurrió un error de conexión o validación.';
          }
        }
      );
  }
});

export const { cambiarEstadoPago } = pagosSlice.actions;
export default pagosSlice.reducer;