import { Alert } from 'react-native';

/**
 * Confirmación unificada para acciones destructivas.
 * Mantiene el mismo look en toda la app y evita duplicar la estructura del Alert.
 */
export function confirmarEliminacion({
  titulo = 'Confirmar acción',
  mensaje,
  labelBoton = 'Eliminar',
  labelCancelar = 'Cancelar',
  onConfirm,
}) {
  if (typeof onConfirm !== 'function') return;

  Alert.alert(
    titulo,
    mensaje,
    [
      { text: labelCancelar, style: 'cancel' },
      { text: labelBoton, style: 'destructive', onPress: onConfirm },
    ],
    { cancelable: true },
  );
}

/** Agrega "Bs." solo si el string no lo tiene ya. Evita "150 Bs. Bs." */
export function formatearMonto(monto) {
  if (monto == null || monto === '') return null;
  const s = String(monto);
  return /\bBs\.?/.test(s) ? s : `${s} Bs.`;
}