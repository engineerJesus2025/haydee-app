import { 
  createSlice, 
  createAsyncThunk, 
  isPending, 
  isRejected, 
  isFulfilled 
} from '@reduxjs/toolkit';
import AsyncStorage from '@react-native-async-storage/async-storage';
import clienteApi from '../../utils/clienteApi';
import { criptografiaMovil } from '../../utils/criptografiaMovil';

export const loginUsuario = createAsyncThunk(
  'usuario/login',
  async (credenciales, { rejectWithValue }) => {
    try {
      const respuesta = await clienteApi.post('', 
        {
          correo: credenciales.correo,
          contra: credenciales.contra
        },
        {
          params: {
            endpoint: 'login'
          }
        }
      );

      const json = respuesta.data;
      if (json.estatus) {
        const { datos, token_jwt, refresh_token } = json; 
        await AsyncStorage.setItem('userToken', token_jwt);
        // Guardamos el refresh token para usarlo cuando el JWT muera
        if (refresh_token) {
           await AsyncStorage.setItem('refreshToken', refresh_token);
        }
        await AsyncStorage.setItem('userData', JSON.stringify(datos));
        return datos;
      }
      return rejectWithValue({ tipo: 'LOGICA', mensaje: json.mensaje });
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

export const cerrarSesionSegura = createAsyncThunk(
  'usuario/cerrarSesionSegura',
  async (_, { dispatch }) => {
    try {
      await clienteApi.post('', {}, { params: { endpoint: 'logout' } });
    } catch (error) {
      const esErrorDeRed = !error.response && error.request;

      const errorPlano = {
        tipo: esErrorDeRed ? 'RED' : 'API_ERROR', 
        mensaje: error.response?.data?.mensaje || error.message || 'Error de conexión con el servidor.',
        status: error.response?.status || 500,
        errores: error.response?.data?.errores || null 
      };
      return rejectWithValue(errorPlano);
    } finally {
      if (typeof criptografiaMovil.limpiarClaves === 'function') {
        criptografiaMovil.limpiarClaves();
      }
      
      dispatch(logout()); 
    }
  }
);

export const bootSilencioso = createAsyncThunk(
  'usuario/bootSilencioso',
  async (_, { dispatch, rejectWithValue }) => {
    try {
      const rawUserData = await AsyncStorage.getItem('userData');
      const refreshToken = await AsyncStorage.getItem('refreshToken');
      
      if (!rawUserData || !refreshToken) {
        return rejectWithValue("No hay sesión guardada");
      }

      const userData = JSON.parse(rawUserData);

      // Enviamos la petición. El interceptor generará una nueva clave AES automáticamente
      const respuesta = await clienteApi.post('', {
        operacion: 'refrescar_token',
        id_usuario: userData.id_usuario,
        token: refreshToken
      }, {
        params: { endpoint: 'refrescar' }
      });

      let datosRenovacion = respuesta.data;
      if (typeof datosRenovacion === 'string') datosRenovacion = JSON.parse(datosRenovacion);

      if (datosRenovacion?.estatus && datosRenovacion?.nuevo_token_jwt) {
        await AsyncStorage.setItem('userToken', datosRenovacion.nuevo_token_jwt);
        return userData; // El payload exitoso restaurará al usuario
      }
      
      throw new Error("Token revocado o inválido");
    } catch (error) {
      dispatch(logout()); // Limpiamos todo si algo falla
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

const usuarioSlice = createSlice({
  name: 'usuario',
  initialState: {
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null,
    pushRegistrado: false,
  },
  reducers: {
    restaurarSesion: (state, action) => {
      state.user = action.payload;
      state.isAuthenticated = true;
    },
    logout: (state) => {
      state.user = null;
      state.isAuthenticated = false;
      AsyncStorage.multiRemove(['userToken', 'userData', 'refreshToken']);
    },
    marcarPushRegistrado: (state) => {
      state.pushRegistrado = true;
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(loginUsuario.fulfilled, (state, action) => {
        state.isAuthenticated = true;
        state.user = action.payload;
      })
      .addCase(bootSilencioso.fulfilled, (state, action) => {
        state.isAuthenticated = true;
        state.user = action.payload;
      })
      .addCase(bootSilencioso.rejected, (state) => {
        state.loading = false;
      })
      .addMatcher(
        isPending(loginUsuario),
        (state) => {
          state.loading = true;
          state.error = null;
        }
      )
      // Apaga el "loading" cuando cualquiera de estos termina con éxito
      .addMatcher(
        isFulfilled(loginUsuario),
        (state) => {
          state.loading = false;
        }
      )
      // Atrapa los errores SOLO si provienen de estos thunks específicos
      .addMatcher(
        isRejected(loginUsuario),
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

export const { logout, restaurarSesion, marcarPushRegistrado } = usuarioSlice.actions;
export default usuarioSlice.reducer;