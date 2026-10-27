import {
  createSlice,
  createAsyncThunk,
  isPending,
  isRejected,
  isFulfilled
} from '@reduxjs/toolkit';
import clienteApi from '../../utils/clienteApi';

export const fetchPublicaciones = createAsyncThunk(
  'publicaciones/fetchPublicaciones',
  async ({ pagina = 1, limite = 10, recargar = false } = {}, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.get('', {
        params: {
          endpoint: 'cartelera',
          operacion: 'consulta',
          pagina,
          limite
        }
      });

      const json = respuesta.data;

      if (json.estatus) {
        const URL_BASE = process.env.EXPO_PUBLIC_URL_BASE;

        const datosMapeados = json.datos.map(item => ({
          id: item.id_cartelera,
          titulo: item.titulo,
          descripcion: item.descripcion,
          fecha: item.fecha,
          tipo: item.prioridad == 1 ? 'aviso' : (item.prioridad == 2 ? 'evento' : 'noticia'),
          prioridad: item.prioridad,
          autor: item.nombre_usuario,
          imagen: item.imagen ? `http://192.168.1.39/condominios-haydee/haydee-web/recursos/img/cartelera_virtual/${item.imagen}` : null,
          imagen_nombre: item.imagen || null,
        }));

        return { datos: datosMapeados, recargar, pagina, limite };
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

export const crearPublicacion = createAsyncThunk(
  'publicaciones/crearPublicacion',
  async ({ datosVisuales, formData }, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'cartelera' }
      });

      const json = respuesta.data;

      if (json.estatus) {
        return {
          ...datosVisuales,
          id: json.lastId,
          fecha: new Date().toISOString().replace('T', ' ').split('.')[0]
        };
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

export const modificarPublicacion = createAsyncThunk(
  'publicaciones/modificarPublicacion',
  async ({ id_cartelera, formData, datosVisuales }, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'cartelera' }
      });

      const json = respuesta.data;
      if (json.estatus) {
        return { id: id_cartelera, ...datosVisuales };
      }
      return rejectWithValue(json.mensaje || 'Error al modificar la publicación');
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

export const eliminarPublicacion = createAsyncThunk(
  'publicaciones/eliminarPublicacion',
  async ({ id_cartelera }, { rejectWithValue }) => {
    try {
      const formData = new FormData();
      formData.append('operacion', 'eliminar_cartelera');
      formData.append('id_cartelera', id_cartelera);
      formData.append('_method', 'DELETE');

      const respuesta = await clienteApi.post('', formData, {
        params: { endpoint: 'cartelera' },
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      if (respuesta.data.estatus) {
        return { id: id_cartelera };
      }
      return rejectWithValue(respuesta.data.mensaje || 'Error al eliminar la publicación');
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

const publicacionesSlice = createSlice({
  name: 'publicaciones',
  initialState: {
    listaPublicaciones: [],
    cargando: false,
    error: null,
    paginaActual: 1,
    hayMas: true
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPublicaciones.fulfilled, (state, action) => {
        state.cargando = false;
        if (action.payload.recargar || action.payload.pagina === 1) {
          state.listaPublicaciones = action.payload.datos;
        } else {
          const idsExistentes = new Set(state.listaPublicaciones.map(p => p.id));
          const nuevos = action.payload.datos.filter(nuevo => !idsExistentes.has(nuevo.id));
          state.listaPublicaciones = [...state.listaPublicaciones, ...nuevos];
        }

        state.paginaActual = action.payload.pagina;
        state.hayMas = action.payload.datos.length === action.payload.limite;
      })
      .addCase(crearPublicacion.fulfilled, (state, action) => {
        state.listaPublicaciones.unshift(action.payload);
      })
      // modificar/eliminar → no tocamos la lista local; el hook refresca
      .addMatcher(
        isPending(fetchPublicaciones, crearPublicacion, modificarPublicacion, eliminarPublicacion),
        (state) => {
          state.cargando = true;   // <-- fix: antes decía state.loading
          state.error = null;
        }
      )
      .addMatcher(
        isFulfilled(fetchPublicaciones, crearPublicacion, modificarPublicacion, eliminarPublicacion),
        (state) => {
          state.cargando = false;
        }
      )
      .addMatcher(
        isRejected(fetchPublicaciones, crearPublicacion),
        (state, action) => {
          state.cargando = false;

          if (action.payload && typeof action.payload === 'object' && action.payload.mensaje) {
            if (action.payload.tipo === 'RED' || action.payload.mensaje === 'Network Error') {
              state.error = 'No hay conexión a internet o el servidor no responde. Verifica tu señal.';
            } else {
              state.error = action.payload.mensaje;
            }
          } else if (typeof action.payload === 'string') {
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

export default publicacionesSlice.reducer;