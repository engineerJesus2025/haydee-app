import { 
  createSlice, 
  createAsyncThunk, 
  isPending, 
  isRejected, 
  isFulfilled 
} from '@reduxjs/toolkit';
import clienteApi from '../../utils/clienteApi';

// Thunk para consultar la lista general
export const fetchGastos = createAsyncThunk(
  'gastos/fetchGastos',
  async ({ mes, anio} = {}, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: {
          endpoint: 'gastos',
          operacion: 'listar_gastos_mes',
          mes:mes,
          anio:anio
        }
      });

      const json = respuesta.data;
      
      if (json.estatus) {
        return json.datos;
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

export const fetchCatalogosGastos = createAsyncThunk(
  'gastos/fetchCatalogos',
  async (_, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: { endpoint: 'gastos', operacion: 'obtener_catalogos' }
      });
      if (respuesta.data.estatus) {
        return respuesta.data.datos;
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

export const crearGasto = createAsyncThunk(
  'gastos/crearGasto',
  async ({ datosVisuales, formData }, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'gastos' }
      });

      const json = respuesta.data;
      
      if (json.estatus) {
        return {
          ...datosVisuales,
          id: json.id
        };
      } else {
        // Si hay errores de validación, los formateamos
        const errorMsg = json.mensaje || (json.errores ? Object.values(json.errores)[0] : 'Error al registrar');
        return rejectWithValue(errorMsg);
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

const gastosSlice = createSlice({
  name: 'gastos',
  initialState: {
    listaGastos: [],
    catalogos: { proveedores: [], bancos: [], solicitudes: [], tipos_gasto: [] },
    loading: false,
    error: null,
    totalGastadoMes: "0.00" 
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchGastos.fulfilled, (state, action) => {
        state.listaGastos = action.payload.map(item => ({
          id: item.id_gasto,
          fecha: item.ultima_fecha ? item.ultima_fecha.split(' ')[0] : 'Sin fecha',
          monto: parseFloat(item.monto_total || 0).toFixed(2), // Formato de moneda
          tipo: item.clasificacion || 'Variable', // Fijo o Variable
          tipo_gasto: item.tipo || 'General',     // Categoría
          proveedor: item.proveedor || 'No especificado',
          descripcion: item.descripcion_gasto,
          // Nota: La consulta general no trae la imagen, se pediría al abrir el detalle
          comprobante: null 
        }));

        state.totalGastadoMes = recalcularTotal(state.listaGastos);
      })
      .addCase(crearGasto.fulfilled, (state, action) => {
        // Agregamos el gasto a la lista visible
        state.listaGastos.unshift(action.payload);
        
        // Sumamos el monto crudo al Total del Mes
        state.totalGastadoMes = recalcularTotal(state.listaGastos);
      })
      .addCase(fetchCatalogosGastos.fulfilled, (state, action) => {
        state.catalogos = action.payload;
      })
      .addMatcher(
        isPending(fetchGastos, fetchCatalogosGastos),
        (state) => {
          state.loading = true;
          state.error = null;
        }
      )
      // Apaga el "loading" cuando cualquiera de estos termina con éxito
      .addMatcher(
        isFulfilled(fetchGastos, fetchCatalogosGastos),
        (state) => {
          state.loading = false;
        }
      )
      // Atrapa los errores SOLO si provienen de estos thunks específicos
      .addMatcher(
        isRejected(fetchGastos, fetchCatalogosGastos),
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

export default gastosSlice.reducer;

const recalcularTotal = (lista) => {
  const total = lista.reduce((sum, item) => {
    // Si viene de fetchGastos usa monto_total, si viene de crearGasto usa montoCrudo
    const valor = parseFloat(item.monto || item.montoCrudo || 0);
    return sum + valor;
  }, 0);
  return total.toFixed(2);
};