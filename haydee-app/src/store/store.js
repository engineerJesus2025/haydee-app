import { configureStore } from '@reduxjs/toolkit';
import publicacionesReducer from '../store/slices/publicacionesSlice';
import temaReducer from '../store/slices/temaSlice';
import usuarioReducer from '../store/slices/usuarioSlice';
import pagosReducer from '../store/slices/pagosSlice'; 
import mensualidadesReducer from '../store/slices/mensualidadesSlice'; 
import gastosReducer from '../store/slices/gastosSlice'; 


export const store = configureStore({
  reducer: {
    publicaciones: publicacionesReducer,
    tema: temaReducer,
    usuario: usuarioReducer,
    pagos: pagosReducer,
    mensualidades: mensualidadesReducer,
    gastos: gastosReducer
  }
});