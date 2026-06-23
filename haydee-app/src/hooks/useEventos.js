import { useSelector } from 'react-redux';
import { parseFechaMySQL } from '../utils/dateUtils';

export const useEventos = () => {
  const publicaciones = useSelector(state => state.publicaciones.listaPublicaciones) || [];
  
  const eventos = publicaciones.filter(pub => pub.tipo?.toLowerCase() === 'evento');
  
  const eventosOrdenados = [...eventos].sort((a, b) => {
    if (!a.fecha || !b.fecha) return 0; 

    const fechaA = parseFechaMySQL(a.fecha);
    const fechaB = parseFechaMySQL(b.fecha);
    
    return fechaA - fechaB; 
  });
  
  const proximosEventos = eventosOrdenados.slice(0, 3);
  
  return { eventos: proximosEventos };
};