import { useSelector } from 'react-redux';

export const usePermisos = () => {
  const { user, isAuthenticated } = useSelector(state => state.usuario);
  const permisosDelUsuario = user?.permisos || [];

  const tienePermiso = (moduloRequerido, accionRequerida) => {
    if (!permisosDelUsuario.length) return false;
    return permisosDelUsuario.some(
      p => p.modulo === moduloRequerido && p.permiso === accionRequerida
    );
  };

  const rolNormalizado = user?.rol?.toLowerCase() || '';
  
  // Definimos de forma inmutable quiénes actúan con privilegios de Admin en la App
  const esAdmin = rolNormalizado === 'administrador' || rolNormalizado === 'administrador global' ;

  return {
    // Estado de Sesión y Datos
    isLogueado: isAuthenticated,
    usuario: user, 
    
    // Permisos de Vistas (Usados principalmente en Navigation.js)
    puedeVerGastos: tienePermiso('GESTIONAR_GASTOS', 'CONSULTAR'),
    puedeVerMensualidad: tienePermiso('GESTIONAR_MENSUALIDAD', 'CONSULTAR'),
    puedeVerCartelera: tienePermiso('GESTIONAR_CARTELERA_VIRTUAL', 'CONSULTAR'),

    // Permisos de Acción (Usados para ocultar/mostrar botones y modales)
    puedeRegistrarGasto: tienePermiso('GESTIONAR_GASTOS', 'REGISTRAR'),
    puedeRegistrarPago: tienePermiso('GESTIONAR_PAGOS', 'REGISTRAR'),
    // Asumimos que la acción de cambiar un estado de pago a Procesado/Rechazado requiere modificar:
    puedeAprobarPagos: tienePermiso('GESTIONAR_PAGOS', 'MODIFICAR'), 
    puedePublicarCartelera: tienePermiso('GESTIONAR_CARTELERA_VIRTUAL', 'REGISTRAR'),
    
    // Exportamos la función por si...
    tienePermiso,
    esAdmin
  };
};