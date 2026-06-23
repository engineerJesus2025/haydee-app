import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import AsyncStorage from '@react-native-async-storage/async-storage';

// Thunk para leer el tema al iniciar la app
export const cargarTemaGlobal = createAsyncThunk(
  'tema/cargarTemaGlobal',
  async () => {
    const temaGuardado = await AsyncStorage.getItem('tema_modoOscuro');
    return temaGuardado === 'true'; // Convertimos el string a booleano
  }
);

// alternar el tema y guardarlo en el disco
export const cambiarTema = createAsyncThunk(
  'tema/cambiarTema',
  async (_, { getState }) => {
    const estadoActual = getState().tema.modoOscuro;
    const nuevoEstado = !estadoActual;
    
    await AsyncStorage.setItem('tema_modoOscuro', String(nuevoEstado));
    return nuevoEstado;
  }
);

const temaSlice = createSlice({
  name: 'tema',
  initialState: {
    modoOscuro: false
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(cargarTemaGlobal.fulfilled, (state, action) => {
        state.modoOscuro = action.payload;
      })
      .addCase(cambiarTema.fulfilled, (state, action) => {
        state.modoOscuro = action.payload;
      });
  }
});

export default temaSlice.reducer;