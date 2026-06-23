import { 
  createSlice, 
  createAsyncThunk, 
  isPending, 
  isRejected, 
  isFulfilled 
} from '@reduxjs/toolkit';
import clienteApi from '../../utils/clienteApi';

// Historial de Mensualidades 
export const fetchMensualidades = createAsyncThunk(
  'mensualidades/fetchMensualidades',
  async (_, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { endpoint: 'mensualidades', operacion: 'consultarPorMeses' }
      });
      if (respuesta.data.estatus) return respuesta.data.datos;
      return rejectWithValue(respuesta.data.mensaje);
    } catch (error) {
      const errorPlano = {
        tipo: 'API_ERROR',
        mensaje: error.response?.data?.mensaje || error.message || 'Error de comunicación con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null
      };
      return rejectWithValue(errorPlano);
    }
  }
);

// Resumen Financiero 
export const fetchResumenFinanciero = createAsyncThunk(
  'mensualidades/fetchResumenFinanciero',
  async (_, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { endpoint: 'mensualidades', operacion: 'consultar_kpis' }
      });

      if (respuesta.data.estatus) return respuesta.data.datos;
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

export const fetchDetalleMensualidad = createAsyncThunk(
  'mensualidades/fetchDetalleMensualidad',
  async ({ id, mes, anio }, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { 
          endpoint: 'mensualidades', 
          operacion: 'consultar_mensualidad_apartamentos', 
          mes: mes,
          anio: anio 
        }
      });

      if (respuesta.data.estatus) {
        return { id, apartamentos: respuesta.data.datos };
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

const mensualidadesSlice = createSlice({
  name: 'mensualidades',
  initialState: {
    listaMensualidades: [],
    resumen: {
      deudaTotal: 0,
      gastado: 0,
      presupuestoTotal: 0
    },
    loading: false,
    error: null
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchMensualidades.fulfilled, (state, action) => {
        const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        state.listaMensualidades = action.payload.map(item => {
          const montoTotal = parseFloat(item.monto || 0);
          const pagado = parseFloat(item.pagado || 0);
          return {
            id: item.ids ? item.ids.split(',')[0] : Math.random().toString(),
            mes: `${nombresMeses[item.mes - 1]} ${item.anio}`,
            mes_raw: item.mes,  
            anio_raw: item.anio,
            total: `${montoTotal.toFixed(2)} Bs.`,
            restante: `${(montoTotal - pagado).toFixed(2)} Bs.`,
            tasa: item.tasa_dolar,
            apartamentos: null
          };
        });
      })
      .addCase(fetchDetalleMensualidad.fulfilled, (state, action) => {
         const { id, apartamentos } = action.payload;
         const mensualidad = state.listaMensualidades.find(m => m.id === id);
         if (mensualidad) {
           mensualidad.apartamentos = apartamentos;
         }
      })
      .addCase(fetchResumenFinanciero.fulfilled, (state, action) => {
        const deuda = parseFloat(action.payload.deuda_total || 0);
        const recaudado = parseFloat(action.payload.recaudado_mes || 0);
        const gastado = parseFloat(action.payload.gastado_mes || 0);

        state.resumen = {
          deudaTotal: deuda,
          recaudado: recaudado,
          gastado: gastado,
          presupuestoTotal: recaudado 
        };
      })
      .addMatcher(
        isPending(fetchMensualidades, fetchResumenFinanciero, fetchDetalleMensualidad),
        (state) => {
          state.loading = true;
          state.error = null;
        }
      )
      // Apaga el "loading" cuando cualquiera de estos termina con éxito
      .addMatcher(
        isFulfilled(fetchMensualidades, fetchResumenFinanciero, fetchDetalleMensualidad),
        (state) => {
          state.loading = false;
        }
      )
      // Atrapa los errores SOLO si provienen de estos thunks específicos
      .addMatcher(
        isRejected(fetchMensualidades, fetchResumenFinanciero),
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

export default mensualidadesSlice.reducer;